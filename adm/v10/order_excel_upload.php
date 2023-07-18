<?php
$sub_menu = "920100";
include_once('./_common.php');
include('./order_excel_upload_func.php');

if( auth_check($auth[$sub_menu],"w",1) ) {
    alert('메뉴 접근 권한이 없습니다.');
}

$demo = 0;  // 데모모드 = 1

$upload_file_name = $_FILES['file_excel']['name'];
$filename = $_FILES['file_excel']['tmp_name'];
// echo $upload_file_name."<br>";
// echo $filename."<br>";
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

$kv = array(
    'B' => '카테고리#1'
    ,'C' => '카테고리#2'
    ,'D' => '카테고리#3'
    ,'E' => '카테고리#4'
    ,'F' => '품번'
    ,'G' => '품명'
    ,'H' => '고객사재고'
    ,'I' => 'TRIM IN'
    ,'J' => 'D0'
    ,'K' => 'D1'
    ,'L' => 'D2'
    ,'M' => 'D3'
    ,'N' => 'D4'
    ,'O' => 'D5'
    ,'P' => 'D6'
    ,'Q' => 'D7'
    ,'R' => 'D8'
    ,'S' => 'D9'
    ,'T' => 'D10'
    ,'U' => 'D11'
    ,'V' => 'D12'
);
$vk = array(
    '카테고리#1' => 'B'
    ,'카테고리#2' => 'C'
    ,'카테고리#3' => 'D'
    ,'카테고리#4' => 'E'
    ,'품번' => 'F'
    ,'품명' => 'G'
    ,'고객사재고' => 'H'
    ,'TRIM IN' => 'I'
    ,'D0' => 'J'
    ,'D1' => 'K'
    ,'D2' => 'L'
    ,'D3' => 'M'
    ,'D4' => 'N'
    ,'D5' => 'O'
    ,'D6' => 'P'
    ,'D7' => 'Q'
    ,'D8' => 'R'
    ,'D9' => 'S'
    ,'D10' => 'T'
    ,'D11' => 'U'
    ,'D12' => 'V'
);

// $upload_file=$_FILES['file_excel']['tmp_name'];
// $reader->setReadDataOnly(TRUE);
$spreadsheet = $reader->load($filename);	
$sheetCount = $spreadsheet->getSheetCount();
// echo $sheetCount;
for ($i = 0; $i < $sheetCount; $i++) {
    $sheet = $spreadsheet->getSheet($i);
    $sheetData = $sheet->toArray(null, true, true, true);
    // echo $i.' ------------- <br>';
    // print_r2($sheetData);
    if($sheetData[$i][])
    // $rowData[$i] = $sheetData;
}
// print_r2(sizeof($rowData));
// print_r2($rowData[0]);
exit;

//전체 엑셀 데이터를 담을 배열을 선언한다.
$pnoArr = array(); //bom에 등록되지 않은 pno배열(등록해야함을 유도)
$gstkArr = array(); //guest_stock_array(고객처 재고 배열)
$gidxArr = array();
$dateArr = array('J' => '','K' => '','L' => '','M' => '','N' => '','O' => '','P' => '','Q' => '','R' => '','S' => '','T' => '','U' => '','V' => '');
$ordArr = array();
// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);
$todate = G5_TIME_YMD;

//전체 엑셀 데이터를 담을 배열을 선언한다.
$catArr = array();
$caArr = array();
$itmArr = array();
$modBom = array();//update해야하는 상품
$addBom = array();//새로 추가해야 하는 상품
$c = 0;
$nameRow = 5;
$contRow = 6;
// print_r2($rowData[0]);
// for($row=1; $row <= sizeof($rowData[0]); $row++) {
//     if($row < $nameRow) continue;
//     else if($row == $nameRow){
//         foreach($dateArr as $dk => $dv){
//             $dateArr[$dk] = $rowData[0][$row][$dk];
//         }
//     } else{
//         // print_r2($rowData[0][$row]);

//     }
// }
// print_r2($dateArr);