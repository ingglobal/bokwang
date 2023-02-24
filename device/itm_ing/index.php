<?php
// 크롬 요소검사 열고 확인하면 되겠습니다.
// print_r2 안 쓰고 print_r로 확인하는 게 좋습니다.
header('Content-Type: application/json; charset=UTF-8');
include_once('./_common.php');

if(!isset($config['cf_line1_bom_idx'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_line1_bom_idx` VARCHAR(255) NOT NULL AFTER `cf_recaptcha_secret_key`,
                    ADD `cf_line2_bom_idx` VARCHAR(255) NOT NULL AFTER `cf_line1_bom_idx`,
                    ADD `cf_line3_bom_idx` VARCHAR(255) NOT NULL AFTER `cf_line2_bom_idx`,
                    ADD `cf_line4_bom_idx` VARCHAR(255) NOT NULL AFTER `cf_line3_bom_idx` ", true);
}


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
else if($getData[0]['bom_part_no']) {

    $arr = $getData[0];

    $arr['itm_status'] = 'ing';
    $arr['itm_timestamp'] = strtotime(preg_replace('/\./','-',$arr['itm_date'])." ".$arr['itm_time']); // 1639579897
    $arr['itm_dt'] = date("Y-m-d H:i:s",$arr['itm_timestamp']);   // 2021-10-10 10:11:11

    // $table_name = 'g5_1_item_'.$arr['mms_idx'];  // 향후 테이블 분리가 필요하면..
    $table_name = 'g5_1_item';

    // checkout db table exists and create if not exists.
    $sql = "SELECT EXISTS (
                SELECT 1 FROM Information_schema.tables
                WHERE TABLE_SCHEMA = '".G5_MYSQL_DB."'
                AND TABLE_NAME = '".$table_name."'
            ) AS flag
    ";
    // echo $sql.'<br>';
    $tb1 = sql_fetch($sql,1);
    if(!$tb1['flag']) {
        $file = file('./sql_write.sql');
        $file = get_db_create_replace($file);
        $sql = implode("\n", $file);
        $source = array('/__TABLE_NAME__/', '/;/');
        $target = array($table_name, '');
        $sql = preg_replace($source, $target, $sql);
        sql_query($sql, FALSE);
    }

    $oop = get_table_meta('order_out_practice','oop_idx',$arr['oop_idx']);
    $orp = get_table_meta('order_practice','orp_idx',$oop['orp_idx']);
    $bom = get_table_meta('bom','bom_idx',$oop['bom_idx']);

    // 외부 라벨 추출
    $bcArr = explode('_',$arr['itm_barcode']);
    //if(strlen($arr['itm_barcode'])>40) {
    if(count($bcArr) >= 4) {
        $arr['itm_barcodes'] = explode("_",$arr['itm_barcode']);
        // print_r2($arr['itm_barcodes']);
        //$arr['itm_com_barcode'] = $arr['itm_barcodes'][3];
        $arr['itm_com_barcode'] = $bcArr[3];
    }

    //구간재설정
    $ingArr = item_shif_date_return($arr['itm_dt']);

    // 히스토리 / status|통계일|등록일
    $arr['itm_history'] = $arr['itm_status'].'|'.$ingArr['workday'].'|'.$ingArr['shift'].'|'.G5_TIME_YMDHIS;

    // 공통요소
    $sql_common = " com_idx = '".$g5['setting']['set_com_idx']."'
                    , imp_idx = '".$arr['imp_idx']."'
                    , mms_idx = '".$arr['mms_idx']."'
                    , bom_idx = '".$oop['bom_idx']."'
                    , oop_idx = '".$oop['oop_idx']."'
                    , bom_part_no = '".$arr['bom_part_no']."'
                    , itm_name = '".addslashes($bom['bom_name'])."'
                    , itm_barcode = '".$arr['itm_barcode']."'
                    , itm_com_barcode = '".$arr['itm_com_barcode']."'
                    , itm_lot = '".$arr['itm_lot']."'
                    , itm_price = '".$bom['bom_price']."'
                    , trm_idx_location = '".$arr['trm_idx_location']."'
                    , itm_shift = '".$ingArr['shift']."'
                    , itm_rework = '0'
                    , itm_date = '".$ingArr['workday']."'
                    , itm_history = '".$arr['itm_history']."'
                    , itm_status = '".$arr['itm_status']."'
    ";

    // 중복체크
    $sql_dta = "   SELECT itm_idx FROM {$table_name}
                    WHERE itm_barcode = '".$arr['itm_barcode']."'
    ";
    //echo $sql_dta.'<br>';
    $itm = sql_fetch($sql_dta,1);
    // 정보 업데이트
    if($itm['itm_idx']) {
        $sql = "UPDATE {$table_name} SET
                    {$sql_common}
					, itm_history = CONCAT(itm_history,'\n".$arr['itm_status']."|".$ingArr['workday']."|".$ingArr['shift']."|".G5_TIME_YMDHIS."')
                    , itm_update_dt = '".G5_TIME_YMDHIS."'
                WHERE itm_idx = '".$itm['itm_idx']."'
        ";
        sql_query($sql,1);
        $result_arr['code'] = 200;
        $result_arr['message'] = "Updated OK!";

    }
    // 정보 입력
    else{

        //print_r2($shif);
        $sql = "INSERT INTO {$table_name} SET
                    {$sql_common}
                    , itm_reg_dt = '".$arr['itm_dt']."'
                    , itm_update_dt = '".$arr['itm_dt']."'
        ";
        sql_query($sql,1);
        $itm['itm_idx'] = sql_insert_id();
        $result_arr['code'] = 200;
        $result_arr['message'] = "Inserted OK!";

        if($config['cf_line'.$arr['trm_idx_location'].'_bom_idx'] != $oop['bom_idx']){
            $sqlc = " UPDATE {$g5['config_table']} SET cf_line".$arr['trm_idx_location']."_bom_idx = '".$oop['bom_idx']."' ";
            sql_query($sqlc, false);
        }
    }
    // echo $sql.'<br>';
    $result_arr['itm_idx'] = $itm['itm_idx'];   // 고유번호
    $result_arr['itm_status'] = $arr['itm_status'];   // 상태값


    // 자재 리스트 (재고포함)
    $sql = "SELECT bom.bom_idx, com_idx_customer, bom.bom_name, bom_part_no, bom_price, bom_status, bom_min_cnt
                , bit1.bit_idx, bit1.bom_idx_child, bit1.bit_reply, bit1.bit_count
                , COUNT(bit2.bit_idx) AS group_count
            FROM {$g5['bom_item_table']} AS bit1
                JOIN {$g5['bom_item_table']} AS bit2
                LEFT JOIN {$g5['bom_table']} AS bom ON bom.bom_idx = bit2.bom_idx_child
            WHERE bit1.bom_idx = '".$oop['bom_idx']."' AND bit2.bom_idx = '".$oop['bom_idx']."'
                AND bit1.bit_num = bit2.bit_num
                AND bit2.bit_reply LIKE CONCAT(bit1.bit_reply,'%')
            GROUP BY bit1.bit_num, bit1.bit_reply
            ORDER BY bit1.bit_num DESC, bit1.bit_reply
    ";
    // print_r2($sql);
    $result = sql_query($sql,1);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // print_r2($row);
        $ar['bom_name'] = $row['bom_name'];
        $ar['bom_part_no'] = $row['bom_part_no'];
        $ar['bom_min_cnt'] = $row['bom_min_cnt'];

        // 현재고
        $sql1 = "   SELECT COUNT(mtr_idx) AS cnt FROM {$g5['material_table']}
                    WHERE bom_part_no = '".$row['bom_part_no']."'
                        AND mtr_status IN ('pending','stock','ready')
        ";
        $row1 = sql_fetch($sql1,1);
        $ar['itm_stock'] = $row1['cnt'];

        $list[] = $ar;
    }
    $result_arr['list'] = $list;

    // Statistics process / This is the first input, so you have to treet this directly once.
    unset($ar);
    $ar['com_idx'] = $bom['com_idx'];
    $ar['itm_date'] = $ingArr['workday'];
    $ar['mms_idx'] = $arr['mms_idx'];
    $ar['trm_idx_line'] = $orp['trm_idx_line'];
    $ar['itm_shift'] = $ingArr['shift'];
    $ar['bom_idx'] = $oop['bom_idx'];
    $ar['itm_status'] = $arr['itm_status'];
    update_item_sum($ar);
    // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_db_table ='".$ar['itm_date']."', mta_db_id ='10', mta_key ='itm_ing', mta_value = '".json_encode($ar)."' ");
    unset($ar);
    // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_db_table ='".$arr['itm_flag']."', mta_db_id ='10', mta_key ='itm_ing', mta_value = '".addslashes($arr)."' ");
    //sql_query(" INSERT INTO {$g5['meta_table']} SET mta_db_table ='".$ar['itm_date']."', mta_db_id ='10', mta_key ='itm_ing', mta_value = '".json_encode($arr)."' ");

}
else {
	$result_arr = array("code"=>599,"message"=>"error");
}

//exit;
//echo json_encode($arr);
echo json_encode( array('meta'=>$result_arr) );
?>
