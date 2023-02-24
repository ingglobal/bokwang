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
$allData = array();
$headArr = array();
$contArr = array();
$tailArr = array();
$gstkArr = array(); //guest_stock_array(고객처 재고 배열)
$dateArr = array(9 => '',10 => '',11 => '',12 => '',13 => '',14 => '',15 => '',16 => '',17 => '',18 => '',19 => '',20 => '',21 => '');
$ordArr = array();
// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);

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
            if($rowData[0][6] == '품명'){
                foreach($dateArr as $idx => $idv){
                    $rowData[0][$idx] = PHPExcel_Style_NumberFormat :: toFormattedString ($rowData[0][$idx], PHPExcel_Style_NumberFormat :: FORMAT_DATE_YYYYMMDD2);
                    $dateArr[$idx] = $rowData[0][$idx];
                }
            }

            $allData[$row] = $rowData[0];
            //if($rowData[0][0] == 'TOTAL') break;
        }
	}

} catch(exception $e) {
	echo $e;
    exit;
}

$ccnt = 0;
$today_date = '';
foreach($allData as $av){
    //print_r2($av);
    if($av[6] == '품명'){ //$av[5] == '품번' 또는 $av[6] == '품명' 또는 $av[8] == '고객처재고'
        $headArr = $av;
        $today_date = $av[9];
        foreach($dateArr as $dtk => $dtv)
            $ordArr[$headArr[$dtk]] = array();
    }
    else {
        if(preg_match('/[A-Z0-9]{4,}-[A-Z0-9]{4,}/',$av[5])){
            //각 날짜별 수량을 해당 배열에 등록
            foreach($dateArr as $dak => $dav){
                $ordArr[$dav][$av[5]]['name'] = $av[6];
                $ordArr[$dav][$av[5]]['cnt'] = $av[$dak];
            }
            //고객사 재고현황 배열등록
            $gstkArr[$av[5]]['date'] = $today_date;
            $gstkArr[$av[5]]['cnt'] = $av[8];
        }
    }
}
/*
echo $com_idx."<br>";
print_r2($dateArr);
echo 'headArr<br>';
print_r2($headArr);
*/
/*
echo 'ordArr<br>';
print_r2($ordArr);
echo 'gstArr<br>';
print_r2($gstkArr);
exit;
*/
//고객사 재고가 등록된 배열을 기반으로 고객사재고 DB에 등록
foreach($gstkArr as $pno => $pvl){
    $b_sql = " SELECT bom_idx, com_idx, com_idx_customer FROM {$g5['bom_table']} WHERE com_idx = '{$_SESSION['ss_com_idx']}' AND bom_part_no = '{$pno}' ";
    $b_row = sql_fetch($b_sql);
    //$pno : P/NO파트넘버 데이터
    //$pvl['date'] = gst_date에 저장할 확인날짜 데이터
    //$pvl['cnt'] = gst_count 해당날짜(당일)의 고객처재고량

    // 확인날짜의 P/NO로 등록된 재고량 레코드가 등록되어 있는지 확인하고
    $s_sql = " SELECT COUNT(*) AS cnt {$g5['guest_stock_table']} WHERE gst_date = '{$pvl['date']}' AND bom_idx = '{$b_row['bom_idx']}' AND com_idx = '{$_SESSION['ss_com_idx']}' ";
    $s_row = sql_fetch($s_sql);

    //기존 등록된 레코드가 있으면 업데이트
    if($s_row['cnt']){
        $u_sql = " UPDATE {$g5['guest_stock_table']} SET
                gst_count = '{$pvl['cnt']}'
            WHERE gst_date = '{$pvl['date']}'
                AND bom_idx = '{$b_row['bom_idx']}'
                AND com_idx = '{$_SESSION['ss_com_idx']}'
        ";
        sql_query($u_sql,1);
    }
    //기존 레코드가 없으면 새롭게 등록
    else{
        $i_sql = " INSERT {$g5['guest_stock_table']} SET
            com_idx = '{$_SESSION['ss_com_idx']}'
            ,com_idx_customer = '{$b_row['com_idx_customer']}'
            ,bom_idx = '{$b_row['bom_idx']}'
            ,gst_count = '{$pvl['cnt']}'
            ,gst_date = '{$pvl['date']}'
            ,gst_status = 'ok'
            ,gst_reg_dt = '".G5_TIME_YMDHIS."'
            ,gst_update_dt = '".G5_TIME_YMDHIS."'
        ";
        sql_query($i_sql,1);
    }
}
//exit;

foreach($ordArr as $ok => $ov){
    //모든행의 카운트가 0인 날짜는 건너띄어라
    $tcnt = 0;
    foreach($ov as $pno => $pnc){
        $tcnt += $pnc['cnt'];
    }
    if(!$tcnt) continue;
    //$ok = [2021-08-19]

    //해당 날짜가 등록 되어 있는지 확인
    $osql = sql_fetch(" SELECT ord_idx FROM {$g5['order_table']} WHERE ord_date = '{$ok}' AND ord_status NOT IN('delete','del','cancel','trash') ");
    $ord_idx = $osql['ord_idx'];


    //기존데이터 업데이트
    if($ord_idx){
        //우선 오늘날짜의 상품목록의 주문만 업데이트한다.
        //$ov = array('88700-4F160RES' => array('name'=>'고정FRT','cnt'=>630),'88700-4F150RES' => array('name'=>'고정RR','cnt'=>730),....)
        $total_price = 0;
        foreach($ov as $pno => $pnc){
            //카운트가 있으면 등록 및 업데이트를 해야한다.
            if($pnc['cnt']){
                //pno에 해당하는 등록된 bom_idx가 있는지 확인하자
                $bsql = sql_fetch(" SELECT bom_idx,bom_idx_customer,bom_price FROM {$g5['bom_table']} WHERE bom_part_no = '{$pno}' AND bom_status NOT IN('delete','del','cancel','trash')  ");
                $bom_idx = $bsql['bom_idx'];
                $ori_price = $bsql['bom_price'];
                $total_price += ($pnc['cnt'] * $ori_price);
                //기존 bom_idx가 없으면 bom_idx를 먼저 등록
                if(!$bom_idx){
                    $bsql = " INSERT into {$g5['bom_table']} SET
                                com_idx = '{$com_idx}',
                                com_idx_customer = '',
                                bom_name = '{$pnc['name']}',
                                bom_type = 'product',
                                bom_status = 'pending',
                                bom_reg_dt = '".G5_TIME_YMDHIS."',
                                bom_update_dt = '".G5_TIME_YMDHIS."'
                    ";
                    sql_query($bsql,1);
                    $bom_idx = sql_insert_id();
                }
                //ord_item이 기존 ori_idx 데이터가 있는지 확인
                $otsql = sql_fetch(" SELECT ori_idx,com_idx_customer FROM {$g5['order_item_table']} WHERE ord_idx = '{$ord_idx}' AND bom_idx = '{$bom_idx}' AND ori_status NOT IN('delete','del','cancel','trash') ");
                $ori_idx = $otsql['ori_idx'];
                //기존 ori_idx 데이터가 있으면 업데이트
                if($ori_idx){
                    $sql_it = " UPDATE {$g5['order_item_table']} SET
                            com_idx = '{$com_idx}',
                            com_idx_customer = '{$otsql['com_idx_customer']}',
                            ord_idx = '{$ord_idx}',
                            bom_idx = '{$bom_idx}',
                            ori_count = '{$pnc['cnt']}',
                            ori_price = '{$ori_price}',
                            ori_status = 'ok',
                            ori_update_dt = '".G5_TIME_YMDHIS."'
                        WHERE ori_idx = '{$ori_idx}'
                    ";
                    sql_query($sql_it,1);
                }
                //기존 ori_idx 데이터가 없으면 새롭게 등록
                else{
                    $sql_it = " INSERT into {$g5['order_item_table']} SET
                            com_idx = '{$com_idx}',
                            com_idx_customer = '{$bsql['com_idx_customer']}',
                            ord_idx = '{$ord_idx}',
                            bom_idx = '{$bom_idx}',
                            ori_count = '{$pnc['cnt']}',
                            ori_price = '{$ori_price}',
                            ori_status = 'ok',
                            ori_reg_dt = '".G5_TIME_YMDHIS."',
                            ori_update_dt = '".G5_TIME_YMDHIS."'
                    ";
                    sql_query($sql_it,1);
                    $ori_idx = sql_insert_id();
                }
            }
            //카운트가 없으면 기존 데이터가 있는지 찾아보고 삭제(trash)처리 해야 한다.
            else{
                //pno에 해당하는 등록된 bom_idx가 있는지 확인하자
                $bsql = sql_fetch(" SELECT bom_idx,bom_price FROM {$g5['bom_table']} WHERE bom_part_no = '{$pno}' AND bom_status NOT IN('delete','del','cancel','trash')  ");
                $bom_idx = $bsql['bom_idx'];
                //기존 bom_idx가 없으면 bom_idx를 먼저 등록
                if(!$bom_idx){
                    $bsql = " INSERT into {$g5['bom_table']} SET
                                com_idx = '{$com_idx}',
                                com_idx_customer = '',
                                bom_name = '{$pnc['name']}',
                                bom_type = 'product',
                                bom_status = 'pending',
                                bom_reg_dt = '".G5_TIME_YMDHIS."',
                                bom_update_dt = '".G5_TIME_YMDHIS."'
                    ";
                    sql_query($bsql,1);
                    $bom_idx = sql_insert_id();
                }
                //ord_item이 기존 ori_idx 데이터가 있는지 확인
                $otsql = sql_fetch(" SELECT ori_idx FROM {$g5['order_item_table']} WHERE ord_idx = '{$ord_idx}' AND bom_idx = '{$bom_idx}' AND ori_status NOT IN('delete','del','cancel','trash') ");
                $ori_idx = $otsql['ori_idx'];
                //기존 ori_idx 데이터가 trash상태로 업데이트
                order_item_trash($ori_idx);
            }
        }
        //최종 total_price를 ord_idx에 업데이트
        $osql = sql_query(" UPDATE {$g5['order_table']} SET ord_price = '{$total_price}' WHERE ord_idx = '{$ord_idx}' ");
    }
    //신규등록
    else{
        $sql = " INSERT into {$g5['order_table']} SET
                    com_idx = '{$com_idx}',
                    com_idx_customer = '',
                    ord_price = '',
                    ord_ship_date = '',
                    ord_status = 'ok',
                    ord_date = '{$ok}',
                    ord_reg_dt = '".G5_TIME_YMDHIS."',
                    ord_update_dt = '".G5_TIME_YMDHIS."'
        ";

        sql_query($sql,1);
        $ord_idx = sql_insert_id();

        //우선 오늘날짜의 상품목록의 주문만 등록한다.
        //$ov = array('88700-4F160RES' => array('name'=>'고정FRT','cnt'=>630),'88700-4F150RES' => array('name'=>'고정RR','cnt'=>730),....)
        $total_price = 0;
        foreach($ov as $pno => $pnc){
            if($pnc['cnt']){
                //pno에 해당하는 등록된 bom_idx가 있는지 확인하자
                $bsql = sql_fetch(" SELECT bom_idx,com_idx_customer,bom_price FROM {$g5['bom_table']} WHERE bom_part_no = '{$pno}' AND bom_status NOT IN('delete','del','cancel','trash')  ");
                $bom_idx = $bsql['bom_idx'];
                $ori_price = $bsql['bom_price'];
                $total_price += ($pnc['cnt'] * $ori_price);
                //기존 bom_idx가 없으면 bom_idx를 먼저 등록
                if(!$bom_idx){
                    $bsql = " INSERT into {$g5['bom_table']} SET
                                com_idx = '{$com_idx}',
                                com_idx_customer = '',
                                bom_name = '{$pnc['name']}',
                                bom_type = 'product',
                                bom_status = 'pending',
                                bom_reg_dt = '".G5_TIME_YMDHIS."',
                                bom_update_dt = '".G5_TIME_YMDHIS."'
                    ";
                    sql_query($bsql,1);
                    $bom_idx = sql_insert_id();
                }

                $sql_it = " INSERT into {$g5['order_item_table']} SET
                        com_idx = '{$com_idx}',
                        com_idx_customer = '{$bsql['com_idx_customer']}',
                        ord_idx = '{$ord_idx}',
                        bom_idx = '{$bom_idx}',
                        ori_count = '{$pnc['cnt']}',
                        ori_price = '{$ori_price}',
                        ori_status = 'ok',
                        ori_reg_dt = '".G5_TIME_YMDHIS."',
                        ori_update_dt = '".G5_TIME_YMDHIS."'
                ";
                sql_query($sql_it,1);
                //$ori_idx = sql_insert_id();
            }
        }
        //최종 total_price를 ord_idx에 업데이트
        $osql = sql_query(" UPDATE {$g5['order_table']} SET ord_price = '{$total_price}' WHERE ord_idx = '{$ord_idx}' ");
    }
}

goto_url('./order_list.php?'.$qstr, false);
