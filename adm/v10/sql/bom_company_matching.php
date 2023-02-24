<?php
include_once('./_head.sub.php');
?>
<div class="" style="padding:10px;">
	<span>
		작업 시작~~ <font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전 중간에 중지하지 마세요.
	</span><br><br>
	<span id="cont"></span>
</div>
<?php
$demo = 0;  // 데모모드 = 1

// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = $_FILES['file_excel']['tmp_name'];
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);

//전체 엑셀 데이터를 담을 배열을 선언한다.
$allData = array();
try {
    // 업로드한 PHP 파일을 읽어온다.
	$objPHPExcel = PHPExcel_IOFactory::load($filename);
	$sheetsCount = $objPHPExcel -> getSheetCount();
	$cur_part_no = '';
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
            
            //$allData[$row] = $rowData[0];
			if ( !preg_match("/[가-힝]/",$rowData[0][1])
			&& ( preg_match("/[-a-zA-Z]/",$rowData[0][2])
			|| preg_match("/[-a-zA-Z]/",$rowData[0][9]) ) ) {
				//완제품이 있는 라인의 경우
				if($rowData[0][2]) {
					$cur_part_no = $rowData[0][2];
					$ar['cartype'] = $rowData[0][1];
					$ar['bom_part_no'] = $cur_part_no;
					$ar['bom_part_no_parent'] = $cur_part_no;
					$ar['bom_name'] = $rowData[0][3];
					$ar['bom_price'] = $rowData[0][4];
					$ar['bom_type'] = 'product';
					array_push($allData,$ar);
					unset($ar);
				}
				//자재품인 경우
				if($rowData[0][9]) {
					$ar['com_name'] = $rowData[0][8];
					$ar['bom_part_no'] = $rowData[0][9];
					$ar['bom_part_no_parent'] = $cur_part_no;
					$ar['bom_name'] = $rowData[0][10];
					$ar['bom_price'] = $rowData[0][11];
					$ar['bom_count'] = $rowData[0][12];
					$ar['bom_type'] = 'material';
					array_push($allData,$ar);
					unset($ar);
				}
			}
        }
	}
} catch(exception $e) {
	echo $e;
    exit;
}

// print_r2($allData);

include_once('./_tail.sub.php');

$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 20000;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 50; // 몇건씩 화면에 보여줄건지?

flush();
ob_flush();
$cnt = 0;
for($i=0;$i<sizeof($allData);$i++) {
	if($allData[$i]['bom_type'] == 'material'){
		$csql = " SELECT com_idx FROM {$g5['company_table']} WHERE com_name = '{$allData[$i]['com_name']}' AND com_status NOT IN('delete','del','trash') ";
		$com = sql_fetch($csql);
		if($com['com_idx']){
			$cnt++;

			$sql = " UPDATE {$g5['bom_table']} SET com_idx_provider = '{$com['com_idx']}' WHERE com_idx = '{$_SESSION['ss_com_idx']}' AND bom_part_no = '{$allData[$i]['bom_part_no']}' ";
			
			sql_query($sql,1);

			echo "<script> document.all.cont.innerHTML += '".$cnt."_".$allData[$i]['bom_part_no']."_".$com['com_idx']."<br>'; </script>\n";
			flush();
			ob_flush();
			ob_end_flush();
			usleep($sleepsec);
			// 보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
			if ($cnt % $countgap == 0)
			echo "<script> document.all.cont.innerHTML += '<br>'; </script>\n";
		
			// 화면 정리! 부하를 줄임 (화면 싹 지움)
			if ($cnt % $maxscreen == 0)
			echo "<script> document.all.cont.innerHTML = ''; </script>\n";
		}
	}
}
?>
<script>
	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($cnt) ?>건 완료<br><br><font color=crimson><b>[끝]</b></font>";
</script>