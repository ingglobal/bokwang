<?php
$sub_menu = "915130";
include_once('./_common.php');
include('./bom_excel_upload_func.php');
if( auth_check($auth[$sub_menu],"w",1) ) {
    alert('메뉴 접근 권한이 없습니다.');
}

$demo = 0;  // 데모모드 = 1

// print_r2($_REQUEST);
// print_r2($_FILES);
// exit;

/*
$kv = array(
    'B' => '카테고리#1'
    ,'C' => '카테고리#2'
    ,'D' => '카테고리#3'
    ,'E' => '카테고리#4'
    ,'F' => '외부바코드정보'
    ,'G' => 'P/NO'
    ,'H' => 'P/NAME'
    ,'I' => '매출단가'
    ,'J' => '재료비'
    ,'K' => '부 가'
    ,'L' => '비 율'
    ,'M' => '업 체'
    ,'N' => 'P/NO'
    ,'O' => 'P/NAME'
    ,'P' => '단 가'
    ,'Q' => 'Usage'
);
$vk = array(
    '카테고리#1' => 'B'
    '카테고리#2' => 'C'
    '카테고리#3' => 'D'
    '카테고리#4' => 'E'
    '외부바코드정보' => 'F'
    ,'P/NO' => 'G'
    ,'P/NAME' => 'H'
    ,'매출단가' => 'I'
    ,'재료비' => 'J'
    ,'부 가' => 'K'
    ,'비 율' => 'L'
    ,'업 체' => 'M'
    ,'P/NO' => 'N'
    ,'P/NAME' => 'O'
    ,'단 가' => 'P'
    ,'Usage' => 'Q'
);
*/


$upload_file_name = $_FILES['file_excel']['name'];
$upload_file = $_FILES['file_excel']['tmp_name'];
// echo $upload_file_name."<br>";
// echo $upload_file."<br>";
// exit;
require_once G5_LIB_PATH.'/PhpSpreadsheet19/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet; 
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$file_type= pathinfo($upload_file_name, PATHINFO_EXTENSION);
if ($file_type =='xls') {
	$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();	
}
elseif ($file_type =='xlsx') {
	$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
}
else {
	echo '처리할 수 있는 엑셀 파일이 아닙니다';
	exit;
}

// $upload_file=$_FILES['file_excel']['tmp_name'];
// $reader->setReadDataOnly(TRUE);
$spreadsheet = $reader->load($upload_file);	
$sheetCount = $spreadsheet->getSheetCount();
for ($i = 0; $i < $sheetCount; $i++) {
    $sheet = $spreadsheet->getSheet($i);
    $sheetData = $sheet->toArray(null, true, true, true);
    // echo $i.' ------------- <br>';
    // print_r2($sheetData);
    $allData[$i] = $sheetData;
}
// print_r2(sizeof($allData));
// print_r2($allData[0]);
// exit;

$g5['title'] = '엑셀 업로드';
include_once('./_top_menu_shift.php');
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

$time1 = '0800';
$time2 = '1930';

$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 20000;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 50; // 몇건씩 화면에 보여줄건지?

flush();
ob_flush();
//preg_replace('/[^a-zA-Z0-9가-힣\-\_\/]/','',$tstr);

$i = 0;
for($i=0;$i<=sizeof($allData[0]);$i++) {
    // print_r2($allData[0][$i]);
    // continue;
    //초기화
    unset($arr);

    //파트넘버가 존재해야 함
    //해당 라인만 추출
    // echo 'ok<br>';
    if(!preg_match("/[가-힝]/",$allData[0][$i]['B'])
            && ( preg_match("/[-a-zA-Z]/",$allData[0][$i]['G']) 
                || preg_match("/[-a-zA-Z]/",$allData[0][$i]['N']) ) ) {
        // 한줄에 두개 상품이 있는 경우가 있으므로 제품을 배열로 분리
        // 쟈재인 경우 (자재는 항상 존재하므로 배열 0번에 배치시킴)
        // echo 'in<br>';
        if($allData[0][$i]['M']) {
            // echo 'i<br>';
            $arr[$i][0]['com_name'] = trim($allData[0][$i]['M']);
            $arr[$i][0]['bom_part_no'] = trim($allData[0][$i]['N']);
            $arr[$i][0]['bom_part_no_parent'] = trim($allData[0][$i]['G']);
            $arr[$i][0]['bom_name'] = trim($allData[0][$i]['O']);
            $arr[$i][0]['bom_price'] = trim($allData[0][$i]['P']);
            $arr[$i][0]['bit_count'] = trim($allData[0][$i]['Q']);
            $arr[$i][0]['bom_type'] = 'material';
        }
        //완제품인 경우, 배열1번
        if($allData[0][$i]['C']) {
            $arr[$i][1]['bct_id'] = '';
            $arr[$i][1]['cat1'] = preg_replace('/[^a-zA-Z0-9가-힣\-\_\/]/','',trim($allData[0][$i]['B']));
            $c1 = sql_fetch(" SELECT bct_id FROM {$g5['bom_category_table']} WHERE bct_name = '{$arr[$i][1]['cat1']}' AND bct_id REGEXP '^.{2}$' LIMIT 1 ");
            $arr[$i][1]['bct_id'] = ($c1['bct_id']) ? $c1['bct_id'] : '';

            $arr[$i][1]['cat2'] = preg_replace('/[^a-zA-Z0-9가-힣\-\_\/]/','',trim($allData[0][$i]['C']));
            $c2 = sql_fetch(" SELECT bct_id FROM {$g5['bom_category_table']} WHERE bct_name = '{$arr[$i][1]['cat2']}' AND bct_id REGEXP '^.{4}$' AND bct_id LIKE '{$c1['bct_id']}%' LIMIT 1 ");
            $arr[$i][1]['bct_id'] = ($c2['bct_id']) ? $c2['bct_id'] : $c1['bct_id'];

            $arr[$i][1]['cat3'] = preg_replace('/[^a-zA-Z0-9가-힣\-\_\/]/','',trim($allData[0][$i]['D']));
            $c3 = sql_fetch(" SELECT bct_id FROM {$g5['bom_category_table']} WHERE bct_name = '{$arr[$i][1]['cat3']}' AND bct_id REGEXP '^.{6}$' AND bct_id LIKE '{$c2['bct_id']}%' LIMIT 1 ");
            $arr[$i][1]['bct_id'] = ($c3['bct_id']) ? $c3['bct_id'] : $c2['bct_id'];

            $arr[$i][1]['cat4'] = preg_replace('/[^a-zA-Z0-9가-힣\-\_\/]/','',trim($allData[0][$i]['E']));
            $c4 = sql_fetch(" SELECT bct_id FROM {$g5['bom_category_table']} WHERE bct_name = '{$arr[$i][1]['cat4']}' AND bct_id REGEXP '^.{8}$' AND bct_id LIKE '{$c3['bct_id']}%' LIMIT 1 ");
            $arr[$i][1]['bct_id'] = ($c4['bct_id']) ? $c4['bct_id'] : $c3['bct_id'];

            $arr[$i][1]['bom_ex_label'] = trim($allData[0][$i]['F']);
            $arr[$i][1]['bom_part_no'] = trim($allData[0][$i]['G']);
            $arr[$i][1]['bom_part_no_parent'] = trim($allData[0][$i]['G']);
            $arr[$i][1]['bom_name'] = trim($allData[0][$i]['H']);
            $arr[$i][1]['bom_price'] = trim($allData[0][$i]['I']);
            $arr[$i][1]['bom_type'] = 'product';
        }
    }
    else
        continue;
    // print_r2($arr[$i]);
    // continue;
    // 완제품 있으면 먼저 생성 (부모 코드가 있어야 함)
    if(is_array($arr[$i][1])) {
        // 엑셀 삭제 처리
        // print_r2($bom_childs[$bom_par]);
        $ar['bom_idx'] = $bom_par;
        $ar['bom_child_arr'] = $bom_childs[$bom_par];
        func_delete_bom_item($ar);
        unset($ar);

        // print_r2('parent');
        $ar = $arr[$i][1];
        $bom_par = func_update_bom($ar);
        unset($ar);

        $idx = -1; // 자재 일련번호(bit_num)
    }

    // print_r2('child');
    $ar = $arr[$i][0];
    $bom_child = func_update_bom($ar);
    unset($ar);

    // print_r2($bom_par);  // 부모 bom_idx
    $bom_childs[$bom_par][] = $bom_child;    

    // // 자재인 경우는 bom_item 테이블을 구성해야 함
    if($arr[$i][0]['bom_type']=='material') {
        // print_r3('자제..');

        // bom_item_table 데이터 입력 =======================
        $sql_common = " bit_count = '".$arr[$i][0]['bit_count']."'
                        , bit_num = '".$idx."'
                        , bit_reply = ''
                        , bit_update_dt = '".G5_TIME_YMDHIS."'
        ";

        // 동일날짜 값이 있으면 업데이트, 아니면 입력
        $sql = "SELECT bit_idx FROM {$g5['bom_item_table']} 
                WHERE bom_idx = '".$bom_par."'
                    AND bom_idx_child = '".$bom_child."'
        ";
        // print_r3($sql);
        // print_r3('--------------');
        $bit = sql_fetch($sql,1);
        if(!$bit['bit_idx']) {
            $sql = "INSERT INTO {$g5['bom_item_table']} SET
                        {$sql_common}
                        , bom_idx = '".$bom_par."'
                        , bom_idx_child = '".$bom_child."'                                    
                        , bit_reg_dt = '".G5_TIME_YMDHIS."'
            ";
            if(!$demo) {
                sql_query($sql,1);
            }
        }
        else {
            $sql = "UPDATE {$g5['bom_item_table']} SET
                        {$sql_common}
                    WHERE bit_idx = '".$bit['bit_idx']."'
            ";
            if(!$demo) {sql_query($sql,1);}
        }
        if($demo) {print_r2($sql);}
        // print_r3($sql);
        // bom_item_table 데이터 입력 =======================

    }
    $idx--; // 자재 일련번호 증가(음수)

    

    // 메시지 보임
    if($arr[$i][0]['bom_part_no']) {
        echo "<script> document.all.cont.innerHTML += '".$i.". ".$arr[$i][0]['bom_part_no']."(".$arr[$i][0]['bom_name'].") 가격: ".@number_format($arr[$i][0]['bom_price'])." ----------->> 완료 (완제품은 안 보임)<br>'; </script>\n";
    }
                



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
// exit;
// 엑셀 삭제 처리, for문 전체 돌고 나서 마지막 처리
// print_r2($bom_childs[$bom_par]);
$ar['bom_idx'] = $bom_par;
$ar['bom_child_arr'] = $bom_childs[$bom_par];
func_delete_bom_item($ar);
unset($ar);

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
    var goto_url = "<?=G5_USER_ADMIN_URL?>/bom_list.php";
	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($i) ?>건 완료<br><br><font color=crimson><b>[끝]</b></font>";
    location.href = goto_url;
</script>