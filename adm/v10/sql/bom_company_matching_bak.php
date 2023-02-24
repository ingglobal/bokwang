<?php
include_once('./_head.sub.php');
?>
<a id="btn_start" href="<?=G5_USER_ADMIN_SQL_URL?>/company_insert.php?start=1">시작</a>
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
					$allData[$rowData[0][2]] = array();
					$allData[$rowData[0][2]]['cartype'] = $rowData[0][1];
					$allData[$rowData[0][2]]['bom_name'] = $rowData[0][3];
					$allData[$rowData[0][2]]['bom_price'] = $rowData[0][4];
					$allData[$rowData[0][2]]['bom_type'] = 'product';
					$allData[$rowData[0][2]]['bom_subs'] = array();
				}
				//자재품인 경우
				if($rowData[0][9]) {
					$allData[$cur_part_no]['bom_subs'][$rowData[0][9]]['com_name'] = $rowData[0][8];
					$allData[$cur_part_no]['bom_subs'][$rowData[0][9]]['bom_part_no_parent'] = $cur_part_no;
					$allData[$cur_part_no]['bom_subs'][$rowData[0][9]]['bom_name'] = $rowData[0][10];
					$allData[$cur_part_no]['bom_subs'][$rowData[0][9]]['bom_price'] = $rowData[0][11];
					$allData[$cur_part_no]['bom_subs'][$rowData[0][9]]['bom_count'] = $rowData[0][12];
					$allData[$cur_part_no]['bom_subs'][$rowData[0][9]]['bom_type'] = 'material';
				}
			}
        }
	}
} catch(exception $e) {
	echo $e;
    exit;
}

print_r2($allData);
?>



<?php
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

		echo $com['com_idx']."<br>";
		continue;
		if($com['com_idx']){
			$cnt++;
			$sql = " UPDATE {$g5['bom_table']} SET com_idx_provider = '{$com['com_idx']}' WHERE com_idx = '{$_SESSION['ss_com_idx']}' AND bom_part_no = '{$allData[$i]['bom_part_no']}' ";

			//sql_query($sql,1);

			echo "<script> document.all.con.innerHTML += '[".$cnt."]-".$allData[$i]['bom_part_no']." : ".$com['com_idx']."<br>'; </script>\n";

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

	echo "<script> document.all.con.innerHTML += '[".($i+1)."]-".$allData[$i]['bom_part_no']." : ".$com['com_idx']."<br>'; </script>\n";


	flush();
	ob_flush();
	ob_end_flush();
	usleep($sleepsec);
	// 보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
	if ($i % $countgap == 0)
	echo "<script> document.all.cont.innerHTML += '<br>'; </script>\n";

	// 화면 정리! 부하를 줄임 (화면 싹 지움)
	if ($i % $maxscreen == 0)
	echo "<script> document.all.cont.innerHTML = ''; </script>\n";
}
?>
<script>
	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($i) ?>건 완료<br><br><font color=crimson><b>[끝]</b></font>";
</script>
