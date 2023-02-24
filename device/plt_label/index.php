<?php
// 크롬 요소검사 열고 확인하면 되겠습니다.
// print_r2 안 쓰고 print_r로 확인하는 게 좋습니다.
header('Content-Type: application/json; charset=UTF-8');
include_once('./_common.php');

//print_r2($_REQUEST);exit;
//echo $_REQUEST['shf_type'][0];
$rawBody = file_get_contents("php://input"); // 본문을 불러옴
$getData = array(json_decode($rawBody,true)); // 데이터를 변수에 넣고
// print_r2($getData);
// echo $getData[0]['imp_idx'];
// exit;

// 토큰 비교
if(!check_token1($getData[0]['token'])) {
	$result_arr = array("code"=>499,"message"=>"token error");
}
else if($getData[0]['plt_barcode']) {

    $arr = $getData[0];

    // 버튼상태값: 출력(print)/출하처리(out)/출하취소(cancel)
    $arr['btn_array'] = array("print"=>"finish","out"=>"delivery","cancel"=>"finish");
    $arr['plt_status'] = $arr['btn_array'][$arr['plt_btn_type']];
    $arr['plt_timestamp'] = strtotime(preg_replace('/\./','-',$arr['plt_date'])." ".$arr['plt_time']); // 1639579897
    $arr['plt_dt'] = date("Y-m-d H:i:s",$arr['plt_timestamp']);   // 2021-10-10 10:11:11

	// sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='insert', mta_value = '".json_encode($arr)."' ");

    // 취소인 경우
    if($arr['plt_btn_type']=='cancel') {

        $sql = "SELECT plt_idx FROM {$g5['pallet_table']}
                WHERE plt_barcode = '".$arr['plt_barcode']."'
        ";
        //echo $sql.'<br>';
        $plt = sql_fetch($sql,1);
        if($plt['plt_idx']) {
            // 이전에 설정했던 제품(item) 설정 모두 초기화
            $ar['plt_idx'] = $plt['plt_idx'];
            pallet_item_init($ar);
            unset($ar);

            // 파레트 레코드 삭제
            $sql = " DELETE FROM {$g5['pallet_table']} WHERE plt_idx = '".$arr['plt_idx']."' ";
            // echo $sql.'<br>';
            sql_query($sql,1);

        }
        $result_arr['code'] = 200;
        $result_arr['message'] = "Canceled OK!";

    }
    // 취소가 아닌 경우
    else {

        // 바코드 분리
        $arr['plt_barcodes'] = explode("_",$arr['plt_barcode']);
        // print_r2($arr['plt_barcodes']);
        $arr['plt_barcode_count'] = $arr['plt_barcodes'][0].'_'.$arr['plt_barcodes'][1].'_';
        $arr['plt_barcode_part_no'] = $arr['plt_barcodes'][0].'_'.$arr['plt_barcodes'][1].'_'.$arr['plt_barcodes'][2].'_';
        $arr['plt_part_no'] = $arr['plt_barcodes'][2];
        $arr['plt_count'] = $arr['plt_barcodes'][3];

        // bom 정보 추출
        $sql = " SELECT * FROM {$g5['bom_table']} WHERE bom_part_no = '".$arr['plt_part_no']."' ";
        $bom = sql_fetch($sql);

        // 공통요소
        $sql_common = " com_idx = '".$g5['setting']['set_com_idx']."'
                        , bom_idx = '".$bom['bom_idx']."'
                        , bom_part_no = '".$arr['plt_part_no']."'
                        , plt_barcode = '".$arr['plt_barcode']."'
                        , plt_count = '".$arr['plt_count']."'
                        , plt_status = '".$arr['plt_status']."'
                        , plt_update_dt = '".G5_TIME_YMDHIS."'
        ";

        // 파레트 중복체크  LIKE '211018_001_%'
        $sql = "SELECT plt_idx FROM {$g5['pallet_table']}
                WHERE plt_barcode LIKE '".$arr['plt_barcode_count']."%'
                ORDER BY plt_idx LIMIT 1
        ";
        //echo $sql.'<br>';
        $plt = sql_fetch($sql,1);
        // 정보 업데이트(same pallet), 파레트는 같지만 다른 part_no 일 수 있음
        if($plt['plt_idx']) {

            // 중복체크(same part_no)  LIKE '211018_001_C89460-CG930SIT_%'
            $sql = "SELECT plt_idx FROM {$g5['pallet_table']}
                    WHERE plt_barcode LIKE '".$arr['plt_barcode_part_no']."%'
            ";
            //echo $sql_dta.'<br>';
            $plt2 = sql_fetch($sql,1);
            // 정보 업데이트, if part_no is also same, All you need is just update.
            if($plt2['plt_idx']) {
                $pt_history = "\nplt_history-{$arr['plt_status']}|".G5_TIME_YMDHIS;
                $sql = "UPDATE {$g5['pallet_table']} SET
                            {$sql_common}
                            , plt_history = CONCAT('$pt_history')
                        WHERE plt_idx = '".$plt2['plt_idx']."'
                ";
                sql_query($sql,1);
                $plt_idx = $plt['plt_idx'];
                $result_arr['code'] = 200;
                $result_arr['message'] = "Updated OK!";

                // 이전에 설정했던 제품(item) 설정 모두 초기화, 갯수가 많아지든 작아지든 관계없이 이전 연결설정들을 일단 초기화
                $ar['plt_idx'] = $plt_idx;
                pallet_item_init($ar);
                unset($ar);

            }
            // 정보 입력, same pallet, new part_no
            else {
                // 부모 idx
                $arr['plt_idx_parent'] = $plt['plt_idx'];
                $pt_history = "plt_history-{$arr['plt_status']}|".$arr['plt_dt'];
                $sql = "INSERT INTO {$g5['pallet_table']} SET
                        {$sql_common}
                        , plt_idx_parent = '".$arr['plt_idx_parent']."'
                        , plt_history = '".$pt_history."'
                        , plt_reg_dt = '".$arr['plt_dt']."'
                ";
                sql_query($sql,1);
                $plt_idx = sql_insert_id();
                $result_arr['code'] = 200;
                $result_arr['message'] = "Inserted OK!";
            }
            // echo $sql.'<br>';
            $result_arr['plt_idx'] = $plt_idx;   // 고유번호
            $result_arr['plt_status'] = $arr['plt_status'];   // 상태값

        }
        // 정보 입력 (new pallet, new part_no)
        else {
            $pt_history = "plt_history-{$arr['plt_status']}|".$arr['plt_dt'];
            $sql = "INSERT INTO {$g5['pallet_table']} SET
                        {$sql_common}
                        , plt_history = '".$pt_history."'
                        , plt_reg_dt = '".$arr['plt_dt']."'
            ";
            sql_query($sql,1);
            $plt_idx = sql_insert_id();

            // 부모 idx update
            sql_query(" UPDATE {$g5['pallet_table']} SET plt_idx_parent = '".$plt_idx."' WHERE plt_idx = '".$plt_idx."' ");

            $result_arr['code'] = 200;
            $result_arr['message'] = "Inserted OK!";
        }
        // echo $sql.'<br>';
        $result_arr['plt_idx'] = $plt_idx;   // 고유번호
        $result_arr['plt_status'] = $arr['plt_status'];   // 상태값


        // 제품(item) 처리, 앞에서부터 차례대로 처리
        $sql = "SELECT * FROM {$g5['item_table']}
                WHERE bom_part_no = '".$arr['plt_part_no']."' AND itm_status = 'finish' ORDER BY itm_idx LIMIT ".$arr['plt_count'];
        $rs = sql_query($sql,1);
        // echo $sql.'<br>';
        for ($i=0; $row=sql_fetch_array($rs); $i++) {

            // 제품 & 자재들 상태 전체 변경
            $ar['itm_status'] = $arr['plt_status'];
            $ar['itm_idx'] = $row['itm_idx'];
            $ar['plt_idx'] = $plt_idx;
            update_itm_delivery($ar);
            unset($ar);

        }

    }   // // 취소가 아닌 경우

}
else {
	$result_arr = array("code"=>599,"message"=>"error");
}

//exit;
//echo json_encode($arr);
echo json_encode( array('meta'=>$result_arr) );
?>
