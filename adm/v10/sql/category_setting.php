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
$bom_mark = 'A1';

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
$caArr = array();
$itmArr = array();
$modBom = array();//update해야하는 상품
$addBom = array();//새로 추가해야 하는 상품
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
			$rowData[0][1] = trim($rowData[0][1]); //1차카테고리
			$rowData[0][2] = trim($rowData[0][2]); //2차카테고리
			$rowData[0][3] = trim($rowData[0][3]); //3차카테고리
			$rowData[0][4] = trim($rowData[0][4]); //4차카테고리
			$rowData[0][5] = trim($rowData[0][5]); //품번
			$rowData[0][6] = trim($rowData[0][6]); //품명
			$rowData[0][25] = trim($rowData[0][25]); //외부라벨코드
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
				}

				if(gettype($catArr[$rowData[0][1]][$rowData[0][2]]) != 'array'){
					$catArr[$rowData[0][1]][$rowData[0][2]] = array();
					
				}

				if(gettype($catArr[$rowData[0][1]][$rowData[0][2]][$rowData[0][3]]) != 'array'){
					$catArr[$rowData[0][1]][$rowData[0][2]][$rowData[0][3]] = array();
				}
				

				if(gettype($catArr[$rowData[0][1]][$rowData[0][2]][$rowData[0][3]][$rowData[0][4]]) != 'array'){
					$catArr[$rowData[0][1]][$rowData[0][2]][$rowData[0][3]][$rowData[0][4]] = array();
				}

				$catArr[$rowData[0][1]][$rowData[0][2]][$rowData[0][3]][$rowData[0][4]] = array();

				array_push($itmArr,array(
					'c1' => $rowData[0][1]
					,'c2' => $rowData[0][2]
					,'c3' => $rowData[0][3]
					,'c4' => $rowData[0][4]
					,'bct_id' => ''
					,'bom_part_no' => $rowData[0][5]
					,'bom_name' => $rowData[0][6]
					,'bom_ex_label' => $rowData[0][25]
					,'bom_sort' => $c
				));

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

//print_r2($catArr);exit;
//unset($c);
if(count($catArr)){
	$c1 = 0;
	foreach($catArr as $c1k => $c1v){
		$caArr[$c1k]['sort'] = $c1;
		$caArr[$c1k]['bct_id'] = $idArr[$c1];
		$caArr[$c1k]['subs'] = array();
		//continue;
		if(count($c1v)){
			$c2 = 0;
			foreach($c1v as $c2k => $c2v){
				$caArr[$c1k]['subs'][$c2k]['sort'] = $c2;
				$caArr[$c1k]['subs'][$c2k]['bct_id'] = $caArr[$c1k]['bct_id'].$idArr[$c2];
				$caArr[$c1k]['subs'][$c2k]['subs'] = array();
				//continue;
				if(count($c2v)){
					$c3 = 0;
					foreach($c2v as $c3k => $c3v){
						$caArr[$c1k]['subs'][$c2k]['subs'][$c3k]['sort'] = $c3;
						$caArr[$c1k]['subs'][$c2k]['subs'][$c3k]['bct_id'] = $caArr[$c1k]['subs'][$c2k]['bct_id'].$idArr[$c3];
						$caArr[$c1k]['subs'][$c2k]['subs'][$c3k]['subs'] = array();
						//continue;
						if(count($c3v)){
							$c4 = 0;
							foreach($c3v as $c4k => $c4v){
								$caArr[$c1k]['subs'][$c2k]['subs'][$c3k]['subs'][$c4k]['sort'] = $c4;
								$caArr[$c1k]['subs'][$c2k]['subs'][$c3k]['subs'][$c4k]['bct_id'] = $caArr[$c1k]['subs'][$c2k]['subs'][$c3k]['bct_id'].$idArr[$c4];
								$c4++;
							}
						}
						$c3++;
					}
				}
				$c2++;
			}
		}
		$c1++;
	}
}


if(count($caArr)){
	//기존의 해당업체의 레코드를 전부 삭제한다.
	$all_del_sql = " DELETE FROM {$g5['bom_category_table']} WHERE com_idx = '".$_SESSION['ss_com_idx']."' ";
	// echo $all_del_sql."<br><br>";
	sql_query($all_del_sql,1);

	foreach($caArr as $c1k => $c1v){
		$c1_sql = " INSERT INTO {$g5['bom_category_table']} SET bct_id = '{$c1v['bct_id']}', com_idx = '{$_SESSION['ss_com_idx']}', bct_name = '{$c1k}', bct_desc = '{$c1k}', bct_order = '{$c1v['sort']}', bct_reg_dt = '".G5_TIME_YMDHIS."', bct_update_dt = '".G5_TIME_YMDHIS."' ";
		sql_query($c1_sql,1);
		foreach($c1v['subs'] as $c2k => $c2v){
			$c2_sql = " INSERT INTO {$g5['bom_category_table']} SET bct_id = '{$c2v['bct_id']}', com_idx = '{$_SESSION['ss_com_idx']}', bct_name = '{$c2k}', bct_desc = '{$c2k}', bct_order = '{$c2v['sort']}', bct_reg_dt = '".G5_TIME_YMDHIS."', bct_update_dt = '".G5_TIME_YMDHIS."' ";
			sql_query($c2_sql,1);
			foreach($c2v['subs'] as $c3k => $c3v){
				$c3_sql = " INSERT INTO {$g5['bom_category_table']} SET bct_id = '{$c3v['bct_id']}', com_idx = '{$_SESSION['ss_com_idx']}', bct_name = '{$c3k}', bct_desc = '{$c3k}', bct_order = '{$c3v['sort']}', bct_reg_dt = '".G5_TIME_YMDHIS."', bct_update_dt = '".G5_TIME_YMDHIS."' ";
				sql_query($c3_sql,1);
				foreach($c3v['subs'] as $c4k => $c4v){
					$c4_sql = " INSERT INTO {$g5['bom_category_table']} SET bct_id = '{$c4v['bct_id']}', com_idx = '{$_SESSION['ss_com_idx']}', bct_name = '{$c4k}', bct_desc = '{$c4k}', bct_order = '{$c4v['sort']}', bct_reg_dt = '".G5_TIME_YMDHIS."', bct_update_dt = '".G5_TIME_YMDHIS."' ";
					sql_query($c4_sql,1);
				}
			}
		}
	}
}

if(count($itmArr)){
	for($i=0;$i<count($itmArr);$i++){
		$itmArr[$i]['bct_id'] = $caArr[$itmArr[$i]['c1']]['subs'][$itmArr[$i]['c2']]['subs'][$itmArr[$i]['c3']]['subs'][$itmArr[$i]['c4']]['bct_id'];

		$bom = sql_fetch(" SELECT bom_idx FROM {$g5['bom_table']} WHERE com_idx = '{$_SESSION['ss_com_idx']}' AND bom_status NOT IN('delete','del','trash') AND bom_part_no = '{$itmArr[$i]['bom_part_no']}' ");

		if($bom['bom_idx']){
			array_push($modBom,array(
				'bom_idx' => $bom['bom_idx']
				,'bct_id' => $itmArr[$i]['bct_id']
				,'bom_part_no' => $itmArr[$i]['bom_part_no']
				,'bom_name' => $itmArr[$i]['bom_name']
				,'bom_ex_label' => $itmArr[$i]['bom_ex_label']
				,'bom_sort' => $itmArr[$i]['bom_sort']
			));
		}
		else {
			array_push($addBom,array(
				'bct_id' => $itmArr[$i]['bct_id']
				,'bom_part_no' => $itmArr[$i]['bom_part_no']
				,'bom_name' => $itmArr[$i]['bom_name']
				,'bom_ex_label' => $itmArr[$i]['bom_ex_label']
				,'bom_sort' => $itmArr[$i]['bom_sort']
			));
		}
	}
}
//print_r2($caArr);
// print_r2($itmArr);
// print_r2($modBom);
// print_r2($addBom);
// exit;
/*
Array
(
    [HR] => Array
        (
            [sort] => 1
            [bct_id] => 10
            [subs] => Array
                (
                    [H/REST] => Array
                        (
                            [sort] => 1
                            [bct_id] => 1010
                            [subs] => Array
                                (
                                    [다크브라운] => Array
                                        (
                                            [sort] => 1
                                            [bct_id] => 101010
                                            [subs] => Array
                                                (
                                                    [고정_FRT] => Array
                                                        (
                                                            [sort] => 1
                                                            [bct_id] => 10101010
*/
//print_r2($modBom | $addBom);
/*
Array
(
    [0] => Array
        (
            [bct_id] => 10101010
            [bom_part_no] => 88700-4F160RES
            [bom_name] => 다크브라운_고정_FRT
            [bom_ex_label] => 
            [bom_sort] => 0
        )
*/
//exit;




//BOM데이터 수정
if(@sizeof($modBom)){
foreach($modBom as $v){
	$sql_upd = " UPDATE {$g5['bom_table']} SET
					bct_id = '{$v['bct_id']}'
					,bom_name = '{$v['bom_name']}'
					,bom_mark = '{$bom_mark}'
					,bom_ex_label = '{$v['bom_ex_label']}'
					,bom_sort = '{$v['bom_sort']}'
					,bom_update_dt = '".G5_TIME_YMDHIS."'
				WHERE bom_idx = '{$v['bom_idx']}'
	";
	// echo $sql_upd."<br>";
	sql_query($sql_upd,1);
}
}


//BOM데이터 추가
if(@sizeof($addBom)){
foreach($addBom as $v){
	$trm_idx_line = ($v['bom_ex_label']) ? 2 : $g5['line_reverse']['1라인'];
	$sql_ins = " INSERT INTO {$g5['bom_table']} SET
					com_idx = '{$_SESSION['ss_com_idx']}'
					,com_idx_provider = '{$_SESSION['ss_com_idx']}'
					,bct_id = '{$v['bct_id']}'
					,bom_name = '{$v['bom_name']}'
					,bom_part_no = '{$v['bom_part_no']}'
					,bom_mark = '{$bom_mark}'
					,trm_idx_line = '{$trm_idx_line}'
					,bom_type = 'product'
					,bom_ex_label = '{$v['bom_ex_label']}'
					,bom_sort = '{$v['bom_sort']}'
					,bom_status = 'ok'
					,bom_reg_dt = '".G5_TIME_YMDHIS."'
					,bom_update_dt = '".G5_TIME_YMDHIS."'
	";
	// echo $sql_ins."<br>";
	sql_query($sql_ins,1);
}
}

include_once('./_tail.sub.php');

?>
<script>
	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($cnt) ?>건 완료<br><br><font color=crimson><b>[끝]</b></font>";
</script>
