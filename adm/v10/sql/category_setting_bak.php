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
					$catArr[$rowData[0][1]]['bct_id'] = '';
					$catArr[$rowData[0][1]]['sort'] = 0;
					$catArr[$rowData[0][1]]['subs'] = array();
					
				}
				if(gettype($catArr[$rowData[0][1]]['subs'][$rowData[0][2]]) != 'array'){
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]] = array();
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['bct_id'] = '';
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['sort'] = 0;
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'] = array();
					
				}
				if(gettype($catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]) != 'array'){
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]] = array();
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['bct_id'] = '';
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['sort'] = 0;
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'] = array();
					
				}
				if(gettype($catArr[$rowData[0][1]][$rowData[0][2]][$rowData[0][3]][$rowData[0][4]]) != 'array'){
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]] = array();
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['bct_id'] = '';
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['sort'] = 0;
					$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['sort2'] = 0;
					
					/*
					//$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['items'] = array();
					if(gettype($catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['items']) != 'array'){
						$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['items'] = array();
					}
					*/
				}
				
				/*
				array_push($catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['items'],array(
					'bom_part_no' => $rowData[0][5]
					,'bom_name' => $rowData[0][6]
					,'bom_ex_label' => $rowData[0][25]
					,'bom_sort' => $c
				));


				$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['items']['bom_part_no'] = $rowData[0][5];
				$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['items']['bom_name'] = $rowData[0][6];
				$catArr[$rowData[0][1]]['subs'][$rowData[0][2]]['subs'][$rowData[0][3]]['subs'][$rowData[0][4]]['items']['bom_ex_label'] = $rowData[0][25];
				*/
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

//업데이트 하는 BOM데이터의 bom_idx를 담는 배열(이 배열에 들어있는 요소를 제외한 완제품을 삭제할 것이다.)
$bomIdxArr = array();

//업데이트할 BOM_PART_NO 배열(실제 BOM레코드를 수정)
$modBom = array();
//추가할 BOM_PART_NO 배열(실제 BOM레코드를 추가)
$addBom = array();

$c1 = 0;
$n = 0;
$t = 0;//테스트번호
foreach($catArr as $c1k => $c1v){
	$catArr[$c1k]['bct_id'] = $idArr[$c1];
	$catArr[$c1k]['sort'] = $c1;
	//1차 카테고리 등록
	$c1_sql = " INSERT INTO {$g5['bom_category_table']} SET bct_id = '{$idArr[$c1]}', com_idx = '{$_SESSION['ss_com_idx']}', bct_name = '{$c1k}', bct_desc = '{$c1k}', bct_order = '{$c1}', bct_reg_dt = '".G5_TIME_YMDHIS."', bct_update_dt = '".G5_TIME_YMDHIS."' ";
	sql_query($c1_sql,1);
	$c2 = 0;
	foreach($c1v['subs'] as $c2k => $c2v){
		$catArr[$c1k]['subs'][$c2k]['bct_id'] = $catArr[$c1k]['bct_id'].$idArr[$c2];
		$catArr[$c1k]['subs'][$c2k]['sort'] = $c2;
		
		//2차 카테고리 등록
		$c2_sql = " INSERT INTO {$g5['bom_category_table']} SET bct_id = '".$catArr[$c1k]['bct_id'].$idArr[$c2]."', com_idx = '{$_SESSION['ss_com_idx']}', bct_name = '{$c2k}', bct_desc = '{$c2k}', bct_order = '{$c2}', bct_reg_dt = '".G5_TIME_YMDHIS."', bct_update_dt = '".G5_TIME_YMDHIS."' ";
		sql_query($c2_sql,1);
		
		$c3 = 0;
		foreach($c2v['subs'] as $c3k => $c3v){
			$catArr[$c1k]['subs'][$c2k]['subs'][$c3k]['bct_id'] = $catArr[$c1k]['subs'][$c2k]['bct_id'].$idArr[$c3];
			$catArr[$c1k]['subs'][$c2k]['subs'][$c3k]['sort'] = $c3;
			
			//3차 카테고리 등록
			$c3_sql = " INSERT INTO {$g5['bom_category_table']} SET bct_id = '".$catArr[$c1k]['subs'][$c2k]['bct_id'].$idArr[$c3]."', com_idx = '{$_SESSION['ss_com_idx']}', bct_name = '{$c3k}', bct_desc = '{$c3k}', bct_order = '{$c3}', bct_reg_dt = '".G5_TIME_YMDHIS."', bct_update_dt = '".G5_TIME_YMDHIS."' ";
			sql_query($c3_sql,1);
			
			$c4 = 0;
			foreach($c3v['subs'] as $c4k => $c4v){
				$catArr[$c1k]['subs'][$c2k]['subs'][$c3k]['subs'][$c4k]['bct_id'] = $catArr[$c1k]['subs'][$c2k]['subs'][$c3k]['bct_id'].$idArr[$c4];
				$catArr[$c1k]['subs'][$c2k]['subs'][$c3k]['subs'][$c4k]['sort'] = $c4;
				$catArr[$c1k]['subs'][$c2k]['subs'][$c3k]['subs'][$c4k]['sort2'] = $n;
				
				//4차 카테고리 등록
				$c4_sql = " INSERT INTO {$g5['bom_category_table']} SET bct_id = '".$catArr[$c1k]['subs'][$c2k]['subs'][$c3k]['bct_id'].$idArr[$c4]."', com_idx = '{$_SESSION['ss_com_idx']}', bct_name = '{$c4k}', bct_desc = '{$c4k}', bct_order = '{$n}', bct_reg_dt = '".G5_TIME_YMDHIS."', bct_update_dt = '".G5_TIME_YMDHIS."' ";
				sql_query($c4_sql,1);

				
				//삭제에서 제외할 bom_idx 추출해서 배열에 담기
				$bct_id = $catArr[$c1k]['subs'][$c2k]['subs'][$c3k]['subs'][$c4k]['bct_id'];
				$bom_part_no = $catArr[$c1k]['subs'][$c2k]['subs'][$c3k]['subs'][$c4k]['item']['bom_part_no'];
				$bom_name = $catArr[$c1k]['subs'][$c2k]['subs'][$c3k]['subs'][$c4k]['item']['bom_name'];
				$bom_ex_label = $catArr[$c1k]['subs'][$c2k]['subs'][$c3k]['subs'][$c4k]['item']['bom_ex_label'];
				$bom = sql_fetch(" SELECT bom_idx FROM {$g5['bom_table']} WHERE bom_part_no = '{$bom_part_no}' ");
				//array_push($bomIdxArr,$bom['bom_idx']);
				echo $bom_part_no."-".$bom_name."-".$bom['bom_idx']."<br>";
				if($bom['bom_idx']){
					$modBom[$bom_part_no]['bom_idx'] = $bom['bom_idx'];
					$modBom[$bom_part_no]['bct_id'] = $bct_id;
					$modBom[$bom_part_no]['bom_name'] = $bom_name;
					$modBom[$bom_part_no]['bom_part_no'] = $bom_part_no;
					$modBom[$bom_part_no]['bom_ex_label'] = $bom_ex_label;
				}
				else{
					$addBom[$bom_part_no]['bct_id'] = $bct_id;
					$addBom[$bom_part_no]['bom_name'] = $bom_name;
					$addBom[$bom_part_no]['bom_part_no'] = $bom_part_no;
					$addBom[$bom_part_no]['bom_ex_label'] = $bom_ex_label;
				}

				$c4++;
				$n++;
			}
			$c3++;
		}
		$c2++;
	}
	$c1++;
}

//print_r2($modBom);
//BOM데이터 수정
if(@sizeof($modBom)){
foreach($modBom as $k => $v){
	$sql_upd = " UPDATE {$g5['bom_table']} SET
					bct_id = '{$v['bct_id']}'
					,bom_name = '{$v['bom_name']}'
					,bom_mark = '{$bom_mark}'
					,bom_ex_label = '{$v['bom_ex_label']}'
					,bom_update_dt = '".G5_TIME_YMDHIS."'
				WHERE bom_idx = '{$v['bom_idx']}'
	";
	//echo $sql_upd."<br>";
	sql_query($sql_upd,1);
}
}

//print_r2($addBom);
//BOM데이터 추가
if(@sizeof($addBom)){
foreach($addBom as $k => $v){
	$trm_idx_line = ($v['bom_ex_label']) ? 2 : $g5['line_reverse']['1라인'];
	$sql_ins = " INSERT INTO {$g5['bom_table']} SET
					com_idx = '{$_SESSION['ss_com_idx']}'
					,com_idx_provider = '{$_SESSION['ss_com_idx']}'
					,bct_id = '{$v['bct_id']}'
					,bom_name = '{$v['bom_name']}'
					,bom_part_no = '{$k}'
					,bom_mark = '{$bom_mark}'
					,trm_idx_line = '{$trm_idx_line}'
					,bom_type = 'product'
					,bom_ex_label = '{$v['bom_ex_label']}'
					,bom_status = 'ok'
					,bom_reg_dt = '".G5_TIME_YMDHIS."'
					,bom_update_dt = '".G5_TIME_YMDHIS."'
	";
	//echo $sql_ins."<br>";
	sql_query($sql_ins,1);
}
}
// exit;
/*
if(count($bomIdxArr)){
	//잘못 등록된 상품 삭제하기
	$del_where1 = " AND bom_idx NOT IN (".implode(",",$bomIdxArr).") ";
	$sql1 = "DELETE FROM {$g5['bom_table']}  WHERE com_idx = '{$_SESSION['ss_com_idx']}' AND bom_type = 'product' {$del_where1} ";
	//echo $sql."<br>";
	sql_query($sql1,1);

	//자재와 잘못 연결된 완제품  삭제하기
	$del_where2 = " bom_idx NOT IN (".implode(",",$bomIdxArr).") ";
	$sql2 = "DELETE FROM {$g5['bom_item_table']}  WHERE {$del_where2} ";
	//echo $sql."<br>";
	sql_query($sql2,1);
}
*/

//print_r2($catArr);




include_once('./_tail.sub.php');

/*
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
*/
?>
<script>
	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($cnt) ?>건 완료<br><br><font color=crimson><b>[끝]</b></font>";
</script>
