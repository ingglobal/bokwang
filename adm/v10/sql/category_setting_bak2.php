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
$demo = 6;  // 데모모드 = 1

// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = $_FILES['file_excel']['tmp_name'];
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);

//이번 업데이트를 구별하는 식별문자
$bom_mark = 'A';

$ids = ['0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
$idArr = array();
for($i=0;$i<count($ids);$i++){
	if($i == 0) continue;
	for($j=0;$j<count($ids);$j++){
		//echo $ids[$i].$ids[$j]."<br>";
		array_push($idArr,$ids[$i].$ids[$j]);
	}
}
//전체 엑셀 데이터를 담을 배열을 선언한다.
$catArr = array();
$c = 0;

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
        for($row = 0; $row < $highestRow; $row++) {
            //if($row > 41) break;
            // $rowData가 한줄의 데이터를 셀별로 배열처리 된다.
            $rowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);
			$rowData[0][1] = trim($rowData[0][1]);
			$rowData[0][2] = trim($rowData[0][2]);
			$rowData[0][3] = trim($rowData[0][3]);
			$rowData[0][4] = trim($rowData[0][4]);
			$rowData[0][5] = trim($rowData[0][5]);
			$rowData[0][6] = trim($rowData[0][6]);
			$rowData[0][25] = trim($rowData[0][25]);
			if(
				preg_match('/[A-Z]/',$rowData[0][1])
				&& 	preg_match('/[A-Z]{1,3}[\/]?[A-Z]{1,}/',$rowData[0][2])
				&& 	preg_match('/[\/가-힣ㄱ-ㅎㅏ-ㅣ_A-Z]+/',$rowData[0][3])
				&& 	preg_match('/[\/가-힣ㄱ-ㅎㅏ-ㅣ_A-Z]+/',$rowData[0][4])
				&& 	preg_match('/[A-Z\-0-9]+/',$rowData[0][5])
				&& 	preg_match('/[가-힣ㄱ-ㅎㅏ-ㅣ\/\_A-Z]+/',$rowData[0][6])
			){
				if(gettype($catArr[$rowData[0][1]]) != 'array'){
					$catArr[$rowData[0][1]] = array();
					$catArr[$rowData[0][1]]['sort'] = count($catArr);
					$catArr[$rowData[0][1]]['subs'] = array();
				}else{
					$catArr[$rowData[0][1]]['sort'] = count($catArr);
				}
				if(gettype($catArr[$rowData[0][1]]['subs'][$rowData[0][2]]) != 'array'){
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]] = array();
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['sort'] = count($catArr[$rowData[0][1]]['subs']);
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'] = array();
					
				}else{
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['sort'] = count($catArr[$rowData[0][1]]['subs']);
				}
				if(gettype($catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]) != 'array'){
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]] = array();
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['sort'] = count($catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs']);
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'] = array();
					
				}else{
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['sort'] = count($catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs']);
				}
				if(gettype($catArr[$rowData[0][1]][$rowData[0][2]][$rowData[0][3]][$rowData[0][4]]) != 'array'){
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]] = array();
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['sort'] = count($catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs']);
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['items'] = array();
					
				}else{
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['sort'] = count($catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs']);
				}
				/*
				if(gettype($catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['items']) == 'array'){
					array_push($catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['items'],array(
						'bom_part_no' => $rowData[0][5]
						,'bom_name' => $rowData[0][6]
						,'bom_ex_label' => $rowData[0][25]
						,'bom_sort' => $c
					));
				}
				*/

				$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['items'][] = array(
					'bom_part_no' => $rowData[0][5]
					,'bom_name' => $rowData[0][6]
					,'bom_ex_label' => $rowData[0][25]
					,'bom_sort' => $c
				);


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
// exit;
print_r2($catArr);
exit;
//기존의 해당업체의 레코드를 전부 삭제한다.
$all_del_sql = " DELETE FROM {$g5['bom_category_table']} WHERE com_idx = '".$_SESSION['ss_com_idx']."' ";
sql_query($all_del_sql,1);



include_once('./_tail.sub.php');

?>
<script>
	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($cnt) ?>건 완료<br><br><font color=crimson><b>[끝]</b></font>";
</script>
