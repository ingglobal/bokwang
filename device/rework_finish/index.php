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
else if($getData[0]['itm_barcode']) {

    $arr = $getData[0];

    $arr['itm_status'] = $arr['itm_status_code'];
    $arr['itm_timestamp'] = strtotime(preg_replace('/\./','-',$arr['itm_date'])." ".$arr['itm_time']); // 1639579897
    $arr['itm_dt'] = date("Y-m-d H:i:s",$arr['itm_timestamp']);   // 2021-10-10 10:11:11
    // $table_name = 'g5_1_item_'.$arr['mms_idx'];  // 향후 테이블 분리가 필요하면..
    $table_name = 'g5_1_item';

    $sql = " SELECT * FROM {$g5['item_table']} WHERE itm_barcode = '".$arr['itm_barcode']."' ";
    $itm = sql_fetch($sql);
    if(!$itm['itm_idx']) {
        $result_arr = array("code"=>490,"message"=>"item not exists.");
    }
    else if($itm['itm_com_barcode']!=$arr['itm_com_barcode']) {
        $result_arr = array("code"=>480,"message"=>"item barcode not matched.");
    }
    else {
        // 히스토리
        // $arr['itm_history'] = $itm['itm_history'].'\n'.$arr['itm_status'].'|'.G5_TIME_YMDHIS;
        $arr['itm_status'] = 'finish';

        //구간재설정
        $ingArr = item_shif_date_return($arr['itm_dt']);


        $sql = "UPDATE {$table_name} SET
                    itm_history = CONCAT(itm_history,'\n".$arr['itm_status']."|".$ingArr['workday']."|".$ingArr['shift']."|".G5_TIME_YMDHIS."')
                    , itm_shift = '".$ingArr['shift']."'
                    , itm_rework = '1'
                    , itm_date = '".$ingArr['workday']."'
                    , itm_status = '".$arr['itm_status']."'
                    , itm_update_dt = '".G5_TIME_YMDHIS."'
                WHERE itm_idx = '".$itm['itm_idx']."'
        ";
        // echo $sql.'<br>';
        sql_query($sql,1);
        $result_arr['code'] = 200;
        $result_arr['message'] = "Updated OK!";

        $result_arr['itm_idx'] = $itm['itm_idx'];   // 고유번호
        $result_arr['itm_status'] = $arr['itm_status'];   // 상태값


        // 연결된 자재의 모든 상태값을 변경
        $sql = "UPDATE {$g5['material_table']} SET
                    mtr_status = '".$arr['itm_status']."'
                    , mtr_history = CONCAT(mtr_history,'\n".$arr['itm_status']."|".G5_TIME_YMDHIS."')
                    , mtr_update_dt = '".G5_TIME_YMDHIS."'
                WHERE itm_idx = '".$itm['itm_idx']."'
        ";
        // echo $sql.'<br>';
        sql_query($sql,1);

        // update statistics for two days which are the changed day as well as the pervious statistics day.
        update_item_sum_by_status($itm['itm_idx']);

    }
}
else {
	$result_arr = array("code"=>599,"message"=>"error");
}

//exit;
//echo json_encode($arr);
echo json_encode( array('meta'=>$result_arr) );
?>