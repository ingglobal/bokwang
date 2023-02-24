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
$oriArr = array();
// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);
$todate = G5_TIME_YMD;

//전체 엑셀 데이터를 담을 배열을 선언한다.
$catArr = array();
$caArr = array();
$itmArr = array();
$modBom = array();//update해야하는 상품
$addBom = array();//새로 추가해야 하는 상품
$c = 0;

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
            $rowData[0][1] = trim($rowData[0][1]); //1차카테고리
			$rowData[0][2] = trim($rowData[0][2]); //2차카테고리
			$rowData[0][3] = trim($rowData[0][3]); //3차카테고리
			$rowData[0][4] = trim($rowData[0][4]); //4차카테고리
			$rowData[0][5] = trim($rowData[0][5]); //품번
			$rowData[0][6] = trim($rowData[0][6]); //품명
			$rowData[0][25] = trim($rowData[0][25]); //외부라벨코드

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
                }
            }
            if(
				preg_match('/[A-Z]/',$rowData[0][1])
				&& 	preg_match('/[A-Z]{1,3}[\/]?[A-Z]{1,}/',$rowData[0][2])
				&& 	preg_match('/[\/가-힣ㄱ-ㅎㅏ-ㅣ_A-Z]+/',$rowData[0][3])
				&& 	preg_match('/[\/가-힣ㄱ-ㅎㅏ-ㅣ_A-Z]+/',$rowData[0][4])
				&& 	preg_match('/[A-Z\-0-9]+/',$rowData[0][5])
				&& 	preg_match('/[가-힣ㄱ-ㅎㅏ-ㅣ\/\_A-Z]+/',$rowData[0][6])
			){ //품번에 특정 규칙값이 있으면 이하 실행


                //고객처 재고 데이터가 있으면 따로 배열에 저장해라
                if($rowData[0][7]){
                    $gstArr[$rowData[0][5]] = $rowData[0][7];
                }

                foreach($dateArr as $id => $date){
                    if($rowData[0][$id]){
                        $ordArr[$date][$rowData[0][5]] = $rowData[0][$id];
                    }
                }


				$c++;
            }
            else if(
				preg_match('/[A-Z\-0-9]+/',$rowData[0][5]) && ( !$rowData[0][1] || !$rowData[0][2] || !$rowData[0][3] || !$rowData[0][4] || !$rowData[0][6] )
			){
				alert('['.$rowData[0][5].'] 품번의 카테고리 또는 품명에 누락이 있습니다.\\n한 번 더 확인하시고 수정하여 다시 시도 해 주세요.');
				break;
			}
        }
	}
} catch(exception $e) {
	echo $e;
    exit;
}


//89G70-CG980USY 나파그레이_최후석
/*
echo $com_idx."<br>";
echo $todate."<br>";
print_r2($dateArr);
echo 'pnoArr<br>';
print_r2($pnoArr);
echo 'gstArr<br>';
print_r2($gstArr);
echo 'ordArr<br>';
print_r2($ordArr);
exit;
*/

if(count($gstArr)){
    foreach($gstArr as $gk => $gv){
        $gbom = sql_fetch(" SELECT com_idx_customer,bom_idx FROM {$g5['bom_table']} WHERE bom_status NOT IN('delete','del','trash') AND bom_part_no = '{$gk}' ");
        $gst = sql_fetch(" SELECT gst_idx FROM {$g5['guest_stock_table']}
                            WHERE gst_date = '{$todate}'
                                AND gst_status NOT IN('delete','del','trash')
                                AND bom_idx = '{$gbom['bom_idx']}'
        ");
        if($gst['gst_idx']){
            $gsql = " UPDATE {$g5['guest_stock_table']} SET
                            gst_count = '{$gv}'
                            ,gst_date = '{$todate}'
                            ,gst_update_dt = '".G5_TIME_YMDHIS."'
                        WHERE gst_idx = '{$gst['gst_idx']}'
            ";
        }
        else{
            $gsql = " INSERT INTO {$g5['guest_stock_table']} SET
                        com_idx = '{$_SESSION['ss_com_idx']}'
                        ,com_idx_customer = '{$gbom['com_idx_customer']}'
                        ,bom_idx = '{$gbom['bom_idx']}'
                        ,gst_count = '{$gv}'
                        ,gst_date = '{$todate}'
                        ,gst_status = 'ok'
                        ,gst_reg_dt = '".G5_TIME_YMDHIS."'
                        ,gst_update_dt = '".G5_TIME_YMDHIS."'
            ";
        }

        //echo $gsql."<br>";
        sql_query($gsql,1);
    }

	//만약 고객체 테이블에 gst_count = 0 인것은 전부 삭제한다.
	$gst_del = " DELETE FROM {$g5['guest_stock_table']} WHERE gst_count = '0' ";
	sql_query($gst_del,1);
}
//exit;
foreach($ordArr as $ok => $ov){

    if(count($ov)){
        $ord_sql = " SELECT ord_idx FROM {$g5['order_table']} WHERE ord_status NOT IN('delete','del','trash','cancel') AND ord_date = '{$ok}' ";
        //echo $ord_sql."<br>";
        $ord = sql_fetch($ord_sql);
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
            if(!$bom['bom_idx']){
                alert('['.$ik.'] 품번의 상품이 BOM데이터에 등록되지 않았습니다.');
                break;
            }
            //수주별 가격 누적계산
            $ord_price += ($bom['bom_price'] * $iv);

            $ori = sql_fetch(" SELECT ori_idx FROM {$g5['order_item_table']} WHERE ord_idx = '{$ord_idx}' AND bom_idx = '{$bom['bom_idx']}' AND ori_status NOT IN('delete','del','trash') ");

            if(!$ori['ori_idx']){
                $isql = " INSERT INTO {$g5['order_item_table']} SET
                            com_idx = '{$_SESSION['ss_com_idx']}'
                            ,com_idx_customer = '{$bom['com_idx_customer']}'
                            ,ord_idx = '{$ord_idx}'
                            ,bom_idx = '{$bom['bom_idx']}'
                            ,ori_count = '{$iv}'
                            ,ori_price = '{$bom['bom_price']}'
                            ,ori_status = 'ok'
                            ,ori_reg_dt = '".G5_TIME_YMDHIS."'
                            ,ori_update_dt = '".G5_TIME_YMDHIS."'
                ";
                // echo $isql."<br>";
                sql_query($isql,1);
                $ori_idx = sql_insert_id();
            }else{
                $isql = " UPDATE {$g5['order_item_table']} SET
                            ori_count = '{$iv}'
                            ,ori_price = '{$bom['bom_price']}'
                            ,ori_update_dt = '".G5_TIME_YMDHIS."'
                        WHERE ori_idx = '{$ori['ori_idx']}'
                        AND ord_idx = '{$ord_idx}'
                        ";
                // echo $isql."<br>";
                sql_query($isql,1);
                $ori_idx = $ori['ori_idx'];
            }
            $oriArr[$ok][] = $ori_idx;
        }
        //누적 총합계 수주금액 업데이트
        sql_query(" UPDATE {$g5['order_table']} SET ord_price = '{$ord_price}' WHERE ord_idx = '{$ord_idx}' ");

        /*
        $del_where = (@sizeof($oriArr[$ok]))? " AND ori_idx NOT IN (".implode(",",$oriArr[$ok]).") ":"";
        $sql = "DELETE FROM {$g5['order_item_table']}  WHERE ord_idx='".$ord_idx."' {$del_where} ";
        //echo $sql."<br>";
        sql_query($sql,1);
        */
    }
}

// exit;
goto_url('./order_list.php?'.$qstr, false);
