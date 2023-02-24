<?php
include_once('./_head.sub.php');
$xls = G5_USER_ADMIN_SQL_PATH.'/xls/bom_product_exlabel.xlsx';
//echo $xls;exit;
?>
<div>
    <a id="btn_start" href="<?=G5_USER_ADMIN_SQL_URL?>/bom_product_exlabel.php?start=1">시작</a>
</div>
<?php
$demo = 0;  // 데모모드 = 1

// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = $xls;//$_FILES['file_excel']['tmp_name'];
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);

// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);

//전체 엑셀 데이터를 담을 배열을 선언한다.
$updArr = array();
$insArr = array();
$cols = array('cat1'=>1,'cat2'=>2,'cat3'=>3,'cat4'=>4,'pno'=>5,'pnm'=>6,'pno2'=>24,'exlb'=>25);
$initrow = 6;
$tblc = $g5['bom_category_table'];
$tblb = $g5['bom_table'];
if($start){
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
            for($j = $initrow; $j <= $highestRow; $j++) {
                //if($row > 41) break;
                // $rowData가 한줄의 데이터를 셀별로 배열처리 된다.
                $rowData = $sheet -> rangeToArray("A" . $j . ":" . $highestColumn . $j, NULL, TRUE, FALSE);
                //if(!$rowData[0][$cols['pno']] && !$rowData[0][$cols['pnm']]) continue;
                $cat1 = trim($rowData[0][$cols['cat1']]);
                $cat2 = trim($rowData[0][$cols['cat2']]);
                $cat3 = trim($rowData[0][$cols['cat3']]);
                $cat4 = trim($rowData[0][$cols['cat4']]);
                $pno = trim($rowData[0][$cols['pno']]);
                $pnm = trim($rowData[0][$cols['pnm']]);
                $exlb = trim($rowData[0][$cols['exlb']]);
                $cat = '';
                if($cat1){
                    $cat1_sql = " SELECT bct_id FROM {$tblc} WHERE CHAR_LENGTH(bct_id) = 2 AND bct_name = '{$cat1}' ";
                    $cat1_cd = sql_fetch($cat1_sql);
                    $cat = $cat1_cd['bct_id'];
                    if($cat2){
                        $cat2_sql = " SELECT bct_id FROM {$tblc} WHERE CHAR_LENGTH(bct_id) = 4 AND bct_id LIKE '{$cat1_cd['bct_id']}%' AND bct_name = '{$cat2}' ";
                        $cat2_cd = sql_fetch($cat2_sql);
                        $cat = $cat2_cd['bct_id'];
                        if($cat3){
                            $cat3_sql = " SELECT bct_id FROM {$tblc} WHERE CHAR_LENGTH(bct_id) = 6 AND bct_id LIKE '{$cat2_cd['bct_id']}%' AND bct_name = '{$cat3}' ";
                            $cat3_cd = sql_fetch($cat3_sql);
                            $cat = $cat3_cd['bct_id'];
                            if($cat4){
                                $cat4_sql = " SELECT bct_id FROM {$tblc} WHERE CHAR_LENGTH(bct_id) = 8 AND bct_id LIKE '{$cat}%' AND bct_name = '{$cat4}' ";
                                $cat4_cd = sql_fetch($cat4_sql);
                                $cat = $cat4_cd['bct_id'];
                            }
                        }
                    }
                }
                //echo $j.'-'.$cat1.':'.$cat2.':'.$cat3.':'.$cat4.'=>'.$cat."<br>";
                if(preg_match("/[A-Z0-9]{3,}-[A-Z0-9]{3,}/",$pno)){
                    $arr = array(
                        'cat1'=>$cat1
                        ,'cat2'=>$cat2
                        ,'cat3'=>$cat3
                        ,'cat4'=>$cat4
                        ,'cat'=>$cat
                        ,'pnm'=>$pnm
                        ,'exlb'=>$exlb
                    );
                    //array_push($conts,$arr);
                    //$conts[trim($rowData[0][$cols['pno']])] = $arr;
                    $sql = " SELECT COUNT(*) AS cnt FROM {$tblb} WHERE bom_part_no = '{$pno}' AND bom_status NOT IN('delete','del','cancel','trash') ";
                    $bom = sql_fetch($sql);
                    //echo $sql."<br>";
                    if($bom['cnt']){
                        //echo $pno."-수정<br>";
                        $updArr[$pno] = $arr;
                    }
                    else{
                        //echo $pno."-등록<br>";
                        $insArr[$pno] = $arr;
                    }
                }
            }
        }

    } catch(exception $e) {
        echo $e;
        //exit;
    }
    /*
    $updArr,$insArr(
        [88700-J9100PUR] => Array
        (
            [cat1] => OSPE
            [cat2] => H/REST
            [cat3] => 블랙쌍침블루
            [cat4] => 고정_FRT
            [cat] => 11101b10
            [pnm] => 블랙쌍침블루_고정_FRT
            [exlb] => 1J9100PUR
        )
    )
    echo 'updArr<br>';
    print_r2($updArr);        
    
    echo 'insArr<br>';
    print_r2($insArr);
    */
    
    if(count($updArr)){
        $i = 0;
        foreach($updArr as $k => $v){
            $sql = " UPDATE {$tblb} SET
                    bct_id = '{$v['cat']}'
                    ,bom_name = '{$v['pnm']}'
                    ,bom_ex_label = '{$v['exlb']}'
                    WHERE bom_part_no = '{$k}'
            ";
            sql_query($sql,1);
            $i++;
        }
    }
    
    
    if(count($insArr)){
        $i = 0;
        $ins_sql = " INSERT {$tblb} (`com_idx`,`bct_id`,`bom_name`,`bom_part_no`,`bom_type`,`bom_status`,`bom_reg_dt`,`bom_update_dt`) VALUES ";
        foreach($insArr as $k => $v){
            $ins_sql .= ($i == 0) ? '' : ',';
            $ins_sql .= "
                ('{$_SESSION['ss_com_idx']}','{$v['cat']}','{$v['pnm']}','{$k}','product','pending','".G5_TIME_YMDHIS."','".G5_TIME_YMDHIS."')
            ";
            $i++;
        }
        //echo $ins_sql;
        sql_query($ins_sql,1);
    }
    echo "완료!";
    
}
else{
    echo '[시작]버튼을 눌러야 실행됩니다.';
}
?>



<?php
include_once('./_tail.sub.php');