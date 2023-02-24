<?php
$sub_menu = "945115";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

// print_r2($_POST);
// exit;
auth_check($auth[$sub_menu], 'w');

check_admin_token();

if ($_POST['act_button'] == "선택수정") {

    foreach($_POST['chk'] as $itm_idx_v){
        if($_POST['itm_status'] == 'trash') {
            $history = " CONCAT(itm_history,'\n".$_POST['itm_status'][$itm_idx_v]." by ".$member['mb_name'].", ".G5_TIME_YMDHIS."') ";
        }
        else {
            $history = " CONCAT(itm_history,'\n".$_POST['itm_status'][$itm_idx_v]."|".G5_TIME_YMDHIS."') ";
        }
        $sql = " UPDATE {$g5['item_table']} SET
                    itm_status = '".$_POST['itm_status'][$itm_idx_v]."',
                    itm_history = ".$history.",
                    itm_update_dt = '".G5_TIME_YMDHIS."'
                WHERE itm_idx = '".$itm_idx_v."'
        ";
        // echo $sql.'<br>';
        sql_query($sql,1);
        /*
        완제품상태
        ing=생산중,finish=생산완료,delivery=출고완료,return=반품,refund=환불,scrap=폐기,trash=삭제,error_stitch=봉제불량,error_wrinkle=주름불량,error_fabric=원단불량,error_push=누름불량,error_pollution=오염불량,error_bottom=하단불량,error_etc=기타불량
        */

        /*
        자재상태
        waiting=가입고,stock=일반재고,repairing=수리중,repairstock=수리완료재고,scrap=폐기,predict=예측,ready=생산대기,ing=생산진행중,finish=사용완료,delivery=출고완료,return=반품,refund=환불,trash=삭제,error_product=제품불량
        (ing,finish,error_product,delivery,scrap)
        */

        $sync_status = array('ing','finish','error_product','delivery','scrap','trash');
        if(preg_match("/^error_/",$_POST['itm_status'][$itm_idx_v])){
            // 연결된 자재의 모든 상태값을 변경
            $sql = "UPDATE {$g5['material_table']} SET
                    mtr_status = 'error_product'
                    , mtr_history = CONCAT(mtr_history,'\nerror_product|".G5_TIME_YMDHIS."')
                    , mtr_update_dt = '".G5_TIME_YMDHIS."'
                WHERE itm_idx = '".$itm_idx_v."'
            ";
            // echo $sql.'<br>';
            sql_query($sql,1);
        }
        else {
            if(in_array($_POST['itm_status'][$itm_idx_v],$sync_status)){
                if($_POST['itm_status'][$itm_idx_v] == 'trash'){
                    $mtr_history = " CONCAT(mtr_history,'\n삭제 by ".$member['mb_name'].", ".G5_TIME_YMDHIS."') ";
                }
                else{
                    $mtr_history = " CONCAT(mtr_history,'\n".$_POST['itm_status'][$itm_idx_v]."|".G5_TIME_YMDHIS."') ";
                }
                // 연결된 자재의 모든 상태값을 변경
                $sql = "UPDATE {$g5['material_table']} SET
                        mtr_status = '".$_POST['itm_status'][$itm_idx_v]."'
                        , mtr_history = ".$mtr_history."
                        , mtr_update_dt = '".G5_TIME_YMDHIS."'
                    WHERE itm_idx = '".$itm_idx_v."'
                ";
                // echo $sql.'<br>';
                sql_query($sql,1);
            }
        }
    }
} else if ($_POST['act_button'] == "선택삭제") {
    foreach($_POST['chk'] as $itm_idx_v){
        $sql = " UPDATE {$g5['item_table']} SET
                    itm_status = 'trash'
                    , itm_history = CONCAT(itm_history,'\ntrash by ".$member['mb_name'].", ".G5_TIME_YMDHIS."')
                WHERE itm_idx = '".$itm_idx_v."'
        ";
        sql_query($sql,1);

        // 연결된 자재의 모든 상태값을 변경
        $sql = "UPDATE {$g5['material_table']} SET
                mtr_status = 'trash'
                , mtr_history = CONCAT(mtr_history,'\n삭제 by ".$member['mb_name'].", ".G5_TIME_YMDHIS."')
                , mtr_update_dt = '".G5_TIME_YMDHIS."'
            WHERE itm_idx = '".$itm_idx_v."'
        ";
        // echo $sql.'<br>';
        sql_query($sql,1);
    }
}

if ($msg)
    //echo '<script> alert("'.$msg.'"); </script>';
    alert($msg);

// exit;
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
goto_url('./item_list.php?'.$qstr);
?>
