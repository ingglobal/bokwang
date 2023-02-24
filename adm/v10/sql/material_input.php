<?php
include_once('./_head.sub.php');
$xls = G5_USER_ADMIN_SQL_PATH.'/xls/material_input.xlsx';
//echo $xls;exit;
?>
<div>
    <a id="btn_start" href="<?=G5_USER_ADMIN_SQL_URL?>/material_input.php?start=1">시작</a>
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
                 
            }
        }
    } catch(exception $e) {
        echo $e;
        //exit;
    }
}


include_once('./_tail.sub.php');
?>