<?php
$sub_menu = "945110";
include_once('./_common.php');

if( auth_check($auth[$sub_menu],"w",1) ) {
    alert('메뉴 접근 권한이 없습니다.');
}

$demo = 0;  // 데모모드 = 1
//$xls = G5_USER_ADMIN_SQL_PATH.'/xls/material_input.xlsx';

// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = $_FILES['file_excel']['tmp_name'];//$xls
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);

// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);
//echo $filename;exit;
$up_date = G5_TIME_YMD;
$conArr = array();
try {
    // 업로드한 PHP 파일을 읽어온다.
	$objPHPExcel = PHPExcel_IOFactory::load($filename);
	$sheetsCount = $objPHPExcel -> getSheetCount();
	// 시트Sheet별로 읽기
	for($i = 0; $i < $sheetsCount; $i++) { //시트갯수만큼 루프
        $objPHPExcel -> setActiveSheetIndex($i);
        $sheet = $objPHPExcel -> getActiveSheet();
        $highestRow = $sheet -> getHighestRow();          // 마지막 행
        $highestColumn = $sheet -> getHighestColumn();    // 마지막 컬럼
        // 한줄씩 읽기
        for($row = 1; $row <= $highestRow; $row++) { //첫줄부터 루프
            if($row < 5) continue;
            // $rowData가 한줄의 데이터를 셀별로 배열처리 된다.
            $rowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);
            //날짜가 있는 셀에서 날짜만 추출한다.
            if($i == 0 && $row == 5){
                $up_date_sub = PHPExcel_Style_NumberFormat :: toFormattedString ($rowData[0][1], PHPExcel_Style_NumberFormat :: FORMAT_DATE_YYYYMMDD2);
                
                if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$up_date_sub) && $up_date_sub){
                    $up_date = $up_date_sub;
                }

                $mtrchk = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['material_table']} WHERE mtr_input_date = '{$up_date}' AND mtr_status NOT IN('delete','del','cancel','trash') ");
                if($mtrchk['cnt']){
                    alert('이미 등록된 데이터가 있습니다. 엑셀파일로는 제품의 최초 등록만 가능합니다.');
                    exit;
                } 
            } 
            //P/NO가 없는 줄은 루프는 건터뛴다.
            if(!$rowData[0][2] || $rowData[0][2] == '' || $rowData[0][2] == 'P/NO') continue; 
            if(!$rowData[0][5] 
                && !$rowData[0][6] 
                && !$rowData[0][7] 
                && !$rowData[0][8] 
                && !$rowData[0][9] 
                && !$rowData[0][10] 
                && !$rowData[0][11] 
                && !$rowData[0][12] 
                && !$rowData[0][13] 
                && !$rowData[0][14]) continue;
            // $rowData에 들어가는 값은 계속 초기화 되기때문에 값을 담을 새로운 배열을 선안하고 담는다.
            $conArr[$rowData[0][2]] = array(
                'com_name' => $rowData[0][1]
                ,'bom_name' => $rowData[0][3]
                ,'bom_price' => ceil($rowData[0][4])
                ,'times' => array()
                ,'total' => ($rowData[0][15]) ? $rowData[0][15] : 0
            );

            if($rowData[0][5]) $conArr[$rowData[0][2]]['times'][1] = (int)$rowData[0][5];
            if($rowData[0][6]) $conArr[$rowData[0][2]]['times'][2] = (int)$rowData[0][6];
            if($rowData[0][7]) $conArr[$rowData[0][2]]['times'][3] = (int)$rowData[0][7];
            if($rowData[0][8]) $conArr[$rowData[0][2]]['times'][4] = (int)$rowData[0][8];
            if($rowData[0][9]) $conArr[$rowData[0][2]]['times'][5] = (int)$rowData[0][9];
            if($rowData[0][10]) $conArr[$rowData[0][2]]['times'][6] = (int)$rowData[0][10];
            if($rowData[0][11]) $conArr[$rowData[0][2]]['times'][7] = (int)$rowData[0][11];
            if($rowData[0][12]) $conArr[$rowData[0][2]]['times'][8] = (int)$rowData[0][12];
            if($rowData[0][13]) $conArr[$rowData[0][2]]['times'][9] = (int)$rowData[0][13];
            if($rowData[0][14]) $conArr[$rowData[0][2]]['times'][10] = (int)$rowData[0][14];
        }
	}
} catch(exception $e) {
	echo $e;
}
echo $up_date."<br>";
print_r2($conArr);
exit;
$g5['title'] = '엑셀 업로드';
include_once('./_head.php');
echo $g5['container_sub_title'];
?>
<div class="" style="padding:10px;">
	<span>
		작업 시작~~ <font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전 중간에 중지하지 마세요.
	</span><br><br>
	<span id="cont"></span>
</div>
<?php
include_once ('./_tail.php');
?>

<?php
$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 20000;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 50; // 몇건씩 화면에 보여줄건지?

flush();
ob_flush();

//print_r3($conArr);
unset($i);
$idx=1;
//$up_date
/*
Array
(
    [C88750-J9100] => Array
        (
            [com_name] => 서연C&F
            [bom_name] => PAD_OS_고정_FRT
            [bom_price] => 5311
            [times] => Array
                (
                    [1] => 100
                    [2] => 200
                    [3] => 100
                    [4] => 400
                )

            [total] => 800
        )
)
$bom = sql_fetch(" SELECT bom_idx,bom_name,bom_type FROM {$g5['bom_table']} WHERE com_idx = '{$_SESSION['ss_com_idx']}' AND bom_part_no = '{$k}' AND bom_status NOT IN('delete','del','cancel','trash') ");
//$bom['bom_idx'];
$sql = " INSERT INTO {$g5['material_table']} (`com_idx`,`bom_idx`,`bom_part_no`,`mtr_name`,`mtr_type`,`mtr_price`,`mtr_times`,`mtr_status`,`mtr_input_date`,`mtr_reg_dt`,`mtr_update_dt`) VALUES ";
$i = 0;
*/
foreach($conArr as $k => $v){ 
    $bom = sql_fetch(" SELECT bom_idx,bom_name,bom_type,bom_price FROM {$g5['bom_table']} WHERE com_idx = '{$_SESSION['ss_com_idx']}' AND bom_part_no = '{$k}' AND bom_status NOT IN('delete','del','cancel','trash') ");
    $com = sql_fetch(" SELECT com_idx FROM {$g5['company_table']} WHERE com_idx_par = '{$_SESSION['ss_com_idx']}' AND com_name = '{$v['com_name']}' AND com_status NOT IN ('delete','del','cacel','trash') ");
    //해당 bom_idx 데이터가 없으면 새롭게 등록한다.
    if(!$bom['bom_idx']) {
        $bom = array();
        $bsql = " INSERT INTO {$g5['bom_table']} SET
            `com_idx` = '{$_SESSION['ss_com_idx']}'
            ,`com_idx_provider` = '{$com['com_idx']}'
            ,`bom_name` = '{$v['bom_name']}'
            ,`bom_part_no` = '{$k}'
            ,`bom_type` = 'material'
            ,`bom_price` =  '{$v['bom_price']}'
            ,`bom_status` = 'ok'
            ,`bom_reg_dt` = '".G5_TIME_YMDHIS."'
            ,`bom_update_dt` = '".G5_TIME_YMDHIS."'
        ";
        sql_query($bsql);
        $bom['bom_idx'] = sql_insert_id();
        $bom['bom_name'] = $v['bom_name'];
        $bom['bom_type'] = 'material';
        $bom['bom_price'] = $v['bom_price'];
    }
    //해당 bom_idx 데이터가 있으면 가격정보를 업데이트 한다.
    // else {
    //     $bsql = " UPDATE {$g5['bom_table']} SET bom_price = '{{$v['bom_price']}}' WHERE com_idx = '{$_SESSION['ss_com_idx']}' AND bom_part_no = '{$k}' ";
    //     sql_query($bsql);
    //     $bom['bom_price'] = $v['bom_price'];
    // }

    foreach($v['times'] as $tk => $tv){
        $sql = " INSERT INTO {$g5['material_table']} (`com_idx`,`bom_idx`,`bom_part_no`,`mtr_name`,`mtr_type`,`mtr_price`,`mtr_times`,`mtr_status`,`mtr_input_date`,`mtr_reg_dt`,`mtr_update_dt`) VALUES ";
        for($i=0;$i<$tv;$i++){
            $coma = ($i == 0) ? '' : ',';
            $sql .= $coma." ('{$_SESSION['ss_com_idx']}','{$bom['bom_idx']}','{$k}','{$bom['bom_name']}','{$bom['bom_type']}','{$bom['bom_price']}','{$tk}','stock','{$up_date}','".G5_TIME_YMDHIS."','".G5_TIME_YMDHIS."') ";
        }
        sql_query($sql,1);
        //echo "<script> document.all.cont.innerHTML += '".$sql."<br>' </script>\n";
    }
    echo "<script> document.all.cont.innerHTML += '[".$idx."] (".$k.") - ".$v['bom_name']." - (".count($v['times']).") ---->> 완료<br>' </script>\n";

    flush();
    ob_flush();
    ob_end_flush();
    usleep($sleepsec);

    //보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
    if($idx % $countgap == 0)
        echo "<script> document.all.cont.innerHTML += '<br>'; </script>\n";
    
    //화면 정리! 부하를 줄임 (화면 싹 지움)
    if($idx % $maxscreen == 0)
        echo "<script> document.all.cont.innerHTML += ''; </script>\n";
    
    if($idx < count($conArr)) $idx++;
}





// 관리자 디버깅 메시지
if( is_array($g5['debug_msg']) ) {
    for($i=0;$i<sizeof($g5['debug_msg']);$i++) {
        echo '<div class="debug_msg">'.$g5['debug_msg'][$i].'</div>';
    }
?>
    <script>
    $(function(){
        $("#container").prepend( $('.debug_msg') );
    });
    </script>
<?php
}
?>


<script>
	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($idx) ?>건 완료<br><br><font color=crimson><b>[끝]</b></font>";
</script>