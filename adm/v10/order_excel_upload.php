<?php
$sub_menu = "920100";
include_once('./_common.php');
include('./order_excel_upload_func.php');

if( auth_check($auth[$sub_menu],"w",1) ) {
    alert('메뉴 접근 권한이 없습니다.');
}

$demo = 0;  // 데모모드 = 1

$upload_file_name = $_FILES['file_excel']['name'];
$filename = $_FILES['file_excel']['tmp_name'];
// echo $upload_file_name."<br>";
// echo $filename."<br>";
// exit;
require_once G5_LIB_PATH.'/PhpSpreadsheet19/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet; 
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$file_type= pathinfo($upload_file_name, PATHINFO_EXTENSION);
if ($file_type =='xls') {
	$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();	
}
elseif ($file_type =='xlsx') {
	$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
}
else {
	echo '처리할 수 있는 엑셀 파일이 아닙니다';
	exit;
}

$kv = array(
    'B' => '분류1'
    ,'C' => '분류2'
    ,'D' => '분류3'
    ,'E' => '분류4'
    ,'F' => '품번'
    ,'G' => '품명'
    ,'H' => '고객사재고'
    ,'I' => 'TRIM IN'
    ,'J' => 'D0'
    ,'K' => 'D1'
    ,'L' => 'D2'
    ,'M' => 'D3'
    ,'N' => 'D4'
    ,'O' => 'D5'
    ,'P' => 'D6'
    ,'Q' => 'D7'
    ,'R' => 'D8'
    ,'S' => 'D9'
    ,'T' => 'D10'
    ,'U' => 'D11'
    ,'V' => 'D12'
);
$vk = array(
    '분류1' => 'B'
    ,'분류2' => 'C'
    ,'분류3' => 'D'
    ,'분류4' => 'E'
    ,'품번' => 'F'
    ,'품명' => 'G'
    ,'고객사재고' => 'H'
    ,'TRIM IN' => 'I'
    ,'D0' => 'J'
    ,'D1' => 'K'
    ,'D2' => 'L'
    ,'D3' => 'M'
    ,'D4' => 'N'
    ,'D5' => 'O'
    ,'D6' => 'P'
    ,'D7' => 'Q'
    ,'D8' => 'R'
    ,'D9' => 'S'
    ,'D10' => 'T'
    ,'D11' => 'U'
    ,'D12' => 'V'
);


//날짜데이터를 담을 배열을 선언한다.
$dayArr = array('J' => '','K' => '','L' => '','M' => '','N' => '','O' => '','P' => '','Q' => '','R' => '','S' => '','T' => '','U' => '','V' => '');
//날짜별 갯수를 담을 배열을 선언
$dcArr = array('J' => 0,'K' => 0,'L' => 0,'M' => 0,'N' => 0,'O' => 0,'P' => 0,'Q' => 0,'R' => 0,'S' => 0,'T' => 0,'U' => 0,'V' => 0);
$dtArr = array();
$ordArr = array();
// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);
$todate = G5_TIME_YMD;
$dc = 0;
foreach($dayArr as $dk => $dv){
    $dayArr[$dk] = get_dayAddDate($todate,$dc);
    $dc++;
}

$cntRow = 6;
// $upload_file=$_FILES['file_excel']['tmp_name'];
// $reader->setReadDataOnly(TRUE);
$spreadsheet = $reader->load($filename);	
$stCnt = $spreadsheet->getSheetCount();
// echo $sheetCount;
for ($i = 0; $i < $stCnt; $i++) {
    if($i == 0){
        $st = $spreadsheet->getSheet($i);
        $stData = $st->toArray(null, true, true, true);
        // echo $i.' ------------- <br>';
        // print_r2($stData);
        // $rowData[$i] = $stData;
        for($j=1;$j<=sizeof($stData);$j++){
            //엑셀파일의 6번째 라인부터 실제 데이터 이므로 그 전은 건너뛴다.
            if($j < $cntRow) continue;
            
            //해당 제품의 13일치 갯수가 1개도 존재하지 않으면 건너뛴다.
            // if(no_cnt_row($dcArr)) continue;

            if(preg_match("/[a-zA-Z0-9\-\_\/]/",$stData[$j][$vk['분류1']])
                && preg_match("/[a-zA-Z0-9\-\_\/]/",$stData[$j][$vk['분류2']])
                && preg_match("/[가-힣]/",$stData[$j][$vk['분류3']])
                && preg_match("/[a-zA-Z0-9\-\_\/]/",$stData[$j][$vk['분류4']])
                && preg_match("/[a-zA-Z0-9\-\_\/]/",$stData[$j][$vk['품번']])) 
            {
                $bom = sql_fetch(" SELECT * FROM {$g5['bom_table']} WHERE bom_part_no = '{$stData[$j][$vk['품번']]}' ");

                if(!$bom['bom_idx']) continue;

                $dtArr = array();
                foreach($dcArr as $dk => $dv){
                    $stData[$j][$dk] = trim($stData[$j][$dk]);
                    $stData[$j][$dk] = ($stData[$j][$dk] == '' || $stData[$j][$dk] == '0' || $stData[$j][$dk] == 0 || $stData[$j][$dk] == '-') ? 0 : $stData[$j][$dk];
                    $dcArr[$dk] = ($stData[$j][$dk])?$stData[$j][$dk]:0;
                    $dtArr[$dayArr[$dk]] = $dcArr[$dk]; //dtArr['2023-07-10'] = 100
                }


                
                
                //#########  고객사 재고 ###########
                //g5_1_guest_stock테이블에 오늘날짜에 해당 bom_idx가 존재하면 수정 없으면 추가
                //오늘날짜의 해당 bom_idx값의 레코드가 존재하는 확인
                $gst = sql_fetch(" SELECT gst_idx FROM {$g5['guest_stock_table']}
                                    WHERE gst_date = '{$todate}'
                                        AND gst_status NOT IN('delete','del','trash')
                                        AND bom_idx = '{$bom['bom_idx']}' ");
                
                if($stData[$j][$vk['고객사재고']]){
                    //이미 오늘날짜의 고객처재고로 등록된 해당 bom_idx의 레코드가 존재하면 수정
                    if($gst['gst_idx']){
                        $gsql = " UPDATE {$g5['guest_stock_table']} SET
                                        gst_count = '{$stData[$j][$vk['고객사재고']]}'
                                        ,gst_date = '{$todate}'
                                        ,gst_update_dt = '".G5_TIME_YMDHIS."'
                                    WHERE gst_idx = '{$gst['gst_idx']}'
                        ";
                        sql_query($gsql,1);
                        $gst_idx = $gst['gst_idx'];
                    }
                    //이미 오늘날짜의 고객처재고로 등록된 해당 bom_idx의 레코드가 없으면 등록
                    else {
                        $gsql = " INSERT INTO {$g5['guest_stock_table']} SET
                                    com_idx = '{$_SESSION['ss_com_idx']}'
                                    ,com_idx_customer = '{$bom['com_idx_customer']}'
                                    ,bom_idx = '{$bom['bom_idx']}'
                                    ,gst_count = '{$stData[$j][$vk['고객사재고']]}'
                                    ,gst_date = '{$todate}'
                                    ,gst_status = 'ok'
                                    ,gst_reg_dt = '".G5_TIME_YMDHIS."'
                                    ,gst_update_dt = '".G5_TIME_YMDHIS."'
                        ";
                        sql_query($gsql,1);
                        $gst_idx = sql_insert_id();
                    }
                } //if($stData[$j][$vk['고객사재고']])
                else { //고객사재고가 없고, gst_idx가 존재하면 삭제한다.
                    if($gst['gst_idx']){
                        $gsql = " DELETE FROM {$g5['guest_stock_table']} WHERE gst_idx = '{$gst['gst_idx']}'
                        ";
                        sql_query($gsql,1);
                    }
                }
                // print_r2($dtArr);
                // print_r2($bom);
                // print_r2($stData[$j]);
                //############ 수주데이터 작업 #######################
                //각 제품별로 13일치 날짜별 루프를 돌린다. $dtArr[vk]을 참조
                foreach($dtArr as $ord_date => $ord_cnt){
                    //해당날짜로 등록된 수주레코드가 있는지 확인
                    $ord_sql = " SELECT ord_idx FROM {$g5['order_table']} WHERE com_idx = '{$_SESSION['ss_com_idx']}' AND ord_status NOT IN('delete','del','trash','cancel') AND ord_date = '{$ord_date}' ";
                    $ord = sql_fetch($ord_sql);
                    $ord_idx = ($ord['ord_idx']) ? $ord['ord_idx'] : 0;

                    //해당 수주상품에 대한 ori_idx가 존재하는지 확인
                    $ori_sql = " SELECT ori_idx FROM {$g5['order_item_table']}
                                    WHERE ord_idx = '{$ord_idx}' 
                                        AND bom_idx = '{$bom['bom_idx']}'
                                        AND ori_status NOT IN('delete','del','trash')
                    ";
                    $ori = sql_fetch($ori_sql);
                    $ori_idx = ($ori['ori_idx']) ? $ori['ori_idx'] : 0;

                    //엑셀에 해당날짜에 카운트가 1개이상이면 @@@@@@@@@@@@@@@@@@@
                    if($ord_cnt){
                        //해당 ord_idx가 없으면 수주레코드부터 등록하고, ord_idx를 추출한다.
                        if( !$ord_idx ){
                            $osql = " INSERT INTO {$g5['order_table']} SET 
                                com_idx = '{$_SESSION['ss_com_idx']}'
                                , ord_price = ''
                                , ord_ship_date = ''
                                , ord_status = 'ok'
                                , ord_date = '{$ord_date}'
                                , ord_reg_dt = '".G5_TIME_YMDHIS."'
                                , ord_update_dt = '".G5_TIME_YMDHIS."'
                            ";
                            sql_query($osql,1);
                            $ord_idx = sql_insert_id();
                        }

                        //해당 ori_idx가 존재하면 업데이트
                        if($ori_idx) {
                            $orisql = " UPDATE {$g5['order_item_table']} SET
                                            ori_count = '{$ord_cnt}'
                                            , ori_price = '{$bom['bom_price']}'
                                            , ori_status = 'ok'
                                            , ori_update_dt = '".G5_TIME_YMDHIS."'
                                        WHERE ori_idx = '{$ori_idx}'
                            ";
                            sql_query($orisql,1);
                        }
                        //해당 ori_idx가 없으면 추가
                        else {
                            $orisql = " INSERT INTO {$g5['order_item_table']} SET
                                            com_idx = '{$_SESSION['ss_com_idx']}'
                                            , com_idx_customer = '{$bom['com_idx_customer']}'
                                            , ord_idx = '{$ord_idx}'
                                            , bom_idx = '{$bom['bom_idx']}'
                                            , ori_count = '{$ord_cnt}'
                                            , ori_price = '{$bom['bom_price']}'
                                            , ori_status = 'ok'
                                            , ori_reg_dt = '".G5_TIME_YMDHIS."'
                                            , ori_update_dt = '".G5_TIME_YMDHIS."'
                            ";
                            sql_query($orisql,1);
                            $ori_idx = sql_insert_id();
                        }
                    } //if($ord_cnt)
                    //엑셀에 해당날짜에 카운트가 0이면 @@@@@@@@@@@@@@@@@@
                    else {
                        //엑셀 해당날짜 카운트는 0인데.. ori_idx가 존재하면 삭제해라
                        if($ori_idx){
                            //수주상품 데이터를 삭제해라
                            $oridel = " DELETE FROM {$g5['order_item_table']} WHERE ord_idx = '{$ord['ord_idx']}' ";
                            sql_query($oridel,1);
                        }
                    }

                    if($ord_idx && !in_array($ord_idx,$ordArr)) array_push($ordArr, $ord_idx);
                } //foreach($dtArr as $ord_date => $ord_cnt)

            } //데이터가 존재하는 품목만 디비에 저장
        } //for($j=1;$j<=sizeof($stData);$j++)

        //$ordArr안에 저장되어 있는 ord_idx들을 루프로 돌면서 ori_price의 합계를 저장한다.
        foreach($ordArr as $o){
            $ri_sql = " SELECT SUM(ori_price) AS total_price
                        FROM {$g5['order_item_table']}
                            WHERE ori_status NOT IN ('delete', 'del', 'trash') 
                                AND ord_idx = '{$o}'
            
            ";
            $ori_res = sql_fetch($ri_sql);
            
            $rd_sql = " UPDATE {$g5['order_table']} 
                            SET ord_price = '{$ori_res['total_price']}'
                        WHERE ord_idx = '{$o}'
            ";
            sql_query($rd_sql,1);
        } //foreach($ordArr as $o)
    } //if($i == 0) 첫번째 시트만 실행
} //for ($i = 0; $i < $stCnt; $i++) 시트별루프
// print_r2(sizeof($rowData));
// print_r2($rowData[0]);
// print_r2($ordArr);
// exit;

goto_url('./order_list.php?'.$qstr, false);