<?php
$sub_menu = "920100";
include_once('./_common.php');

if( auth_check($auth[$sub_menu],"w",1) ) {
    alert('메뉴 접근 권한이 없습니다.');
}

$demo = 0;  // 데모모드 = 1

// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = $_FILES['file_excel']['tmp_name'];
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);

//전체 엑셀 데이터를 담을 배열을 선언한다.
$pnoArr = array(); //bom에 등록되지 않은 pno배열(등록해야함을 유도)
$gstkArr = array(); //guest_stock_array(고객처 재고 배열)
$dateArr = array(9 => '',10 => '',11 => '',12 => '',13 => '',14 => '',15 => '',16 => '',17 => '',18 => '',19 => '',20 => '',21 => '');
$ordArr = array();
$partsArr = array();
// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);
$todate = G5_TIME_YMD;
try {
    // 업로드한 PHP 파일을 읽어온다.
	$objPHPExcel = PHPExcel_IOFactory::load($filename);
	$sheetsCount = $objPHPExcel -> getSheetCount();

	// 시트Sheet별로 읽기
	for($i = 0; $i < $sheetsCount; $i++) {
        $objPHPExcel -> setActiveSheetIndex($i);
        $sheet = $objPHPExcel -> getActiveSheet();
        $highestRow = $sheet -> getHighestRow();   			           // 마지막 행
        $highestColumn = $sheet -> getHighestColumn();	// 마지막 컬럼
        // 한줄읽기
        for($row = 1; $row <= $highestRow; $row++) {
            //if($row > 41) break;
            // $rowData가 한줄의 데이터를 셀별로 배열처리 된다.
            $rowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);
            //echo $rowData[0][6] == '품명' ? '1' : '0';
            //echo $rowData[0][0] == 'TOTAL' ? '1' : '0';
            //preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date)
            if($rowData[0][6] == '품명'){
                foreach($dateArr as $idx => $idv){
                    $rowData[0][$idx] = PHPExcel_Style_NumberFormat :: toFormattedString ($rowData[0][$idx], PHPExcel_Style_NumberFormat :: FORMAT_DATE_YYYYMMDD2);
                    $dateArr[$idx] = $rowData[0][$idx];
                    if(!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$rowData[0][$idx])){
                        alert('날짜형식에 맞지 않습니다. 날짜데이터는 날짜서식으로 엑셀파일을 정확히 설정해 주세요.');
                    }
                    $ordArr[$rowData[0][$idx]] = array();
                }
                if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$dateArr[9])){
                    $todate = $dateArr[9];
                    //$gstArr[$todate] = array();
                }
            }
            else if(preg_match('/[-A-Z0-9]/',$rowData[0][5])){ //품번에 특정 규칙값이 있으면 이하 실행
                $pno_exist = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['bom_table']} WHERE com_idx = '{$_SESSION['ss_com_idx']}' AND bom_part_no = '{$rowData[0][5]}' AND bom_status NOT IN('delete','del','trash') ");
                //등록되지 않은 pno가 있으면 따로 배열에 저장해 놓고 BOM등록을 유도해라
                if(!$pno_exist['cnt']) $pnoArr[$rowData[0][5]] = $rowData[0][6];
                //고객처 재고 데이터가 있으면 따로 배열에 저장해라
                if($rowData[0][7]){
                    $gstArr[$rowData[0][5]] = $rowData[0][7];
                }
                
                foreach($dateArr as $id => $date){
                    if($rowData[0][$id]){
                        $ordArr[$date][$rowData[0][5]] = $rowData[0][$id];
                    }
                }
            }
        }
        if(count($pnoArr)){
            $pno_str = '';
            foreach($pnoArr as $pk=>$pv){
                $pno_str .= '['.$pk.'] '.$pv.'\n';
            }
            $pno_str .= '을 먼저 BOM에 등록해 주세요.';
            alert($pno_str);
        }
	}
} catch(exception $e) {
	echo $e;
    exit;
}


//89G70-CG980USY 나파그레이_최후석
echo $com_idx."<br>";
print_r2($dateArr);
echo 'pnoArr<br>';
print_r2($pnoArr);
echo 'gstArr<br>';
print_r2($gstArr);
echo 'ordArr<br>';
print_r2($ordArr);
exit;

if(count($gstArr)){
    foreach($gstArr as $gk => $gv){
        $gbom = sql_fetch(" SELECT com_idx_customer,bom_idx FROM {$g5['bom_table']} WHERE bom_status NOT IN('delete','del','trash') AND bom_part_no = '{$gk}' ");
        $gsql = " INSERT INTO {$g5['guest_stock_table']} (`com_idx`,`com_idx_customer`,`bom_idx`,`gst_count`,`gst_date`,`gst_status`,`gst_reg_dt`,`gst_update_dt`) VALUES (
            '{$_SESSION['ss_com_idx']}'
            ,'{$gbom['com_idx_customer']}'
            ,'{$gbom['bom_idx']}'
            ,'{$gv}'
            ,'{$todate}'
            ,'ok'
            ,'".G5_TIME_YMDHIS."'
            ,'".G5_TIME_YMDHIS."'
        ) 
        ON DUPLICATE KEY
        UPDATE
           gst_count = '{$gv}'
           ,gst_update_dt = '".G5_TIME_YMDHIS."'
        ";
        sql_query($gsql,1);
    }
}

foreach($ordArr as $ok => $ov){
    if(count($ov)){
        $ord = sql_fetch(" SELECT ord_idx FROM {$g5['order_table']} WHERE ord_status NOT IN('delete','del','trash','cancel') AND ord_date = '{$ok}' ");
        if(!$ord['ord_idx']){
            $osql = " INSERT INTO {$g5['order_table']} (`com_idx`,`ord_price`,`ord_ship_date`,`ord_status`,`ord_date`,`ord_reg_dt`,`ord_update_dt`) VALUES (
                '{$_SESSION['ss_com_idx']}'
                ,''
                ,''
                ,'ok'
                ,'{$ok}'
                ,'".G5_TIME_YMDHIS."'
                ,'".G5_TIME_YMDHIS."'
            )
            ";
            sql_query($osql,1);
            $ord_idx = sql_insert_id();
        }
        else{
            $ord_idx = $ord['ord_idx'];
        }

        $ord_price = 0;
        foreach($ov as $ik => $iv){
            $bom = sql_fetch(" SELECT bom_idx,com_idx_customer,bom_price FROM {$g5['bom_table']} WHERE bom_part_no = '{$ik}' AND bom_status NOT IN('delete','del','cancel','trash') ");
            //수주별 가격 누적계산
            $ord_price += ($bom['bom_price'] * $iv);

            $isql = " INSERT INTO {$g5['order_item_table']} (`com_idx`,`com_idx_customer`,`ord_idx`,`bom_idx`,`ori_count`,`ori_price`,`ori_status`,`ori_reg_dt`,`ori_update_dt`) VALUES (
                '{$_SESSION['ss_com_idx']}'
                ,'{$bom['com_idx_customer']}'
                ,'{$ord_idx}'
                ,'{$bom['bom_idx']}'
                ,'{$iv}'
                ,'{$bom['bom_price']}'
                ,'ok'
                ,'".G5_TIME_YMDHIS."'
                ,'".G5_TIME_YMDHIS."'
            )
            ON DUPLICATE KEY
            UPDATE
                ori_count = '{$iv}'
                ,ori_price = '{$bom['bom_price']}'
                ,ori_update_dt = '".G5_TIME_YMDHIS."'
            ";
            sql_query($isql,1);

            $partsArr[$ok][] = $bom['bom_idx'];
        }
        //누적 총합계 수주금액 업데이트
        sql_query(" UPDATE {$g5['order_table']} SET ord_price = '{$ord_price}' WHERE ord_idx = '{$ord_idx}' ");

        $del_where = (@sizeof($partsArr[$ok]))? " AND bom_idx NOT IN (".implode(",",$partsArr[$ok]).") ":"";
        $sql = "DELETE FROM {$g5['order_item_table']}  WHERE ord_idx='".$ord_idx."' {$del_where} ";
        //echo $sql."<br>";
        sql_query($sql,1);
    }
}
//exit;

goto_url('./order_list.php?'.$qstr, false);
