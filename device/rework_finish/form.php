<?php
include_once('./_common.php');

// barcode should be stored beforehand. Find one record from db table and asign it to barcode here.
$sql = " SELECT * FROM {$g5['item_table']} WHERE itm_barcode != '' AND itm_status = 'ing' ORDER BY RAND() LIMIT 1 ";
$itm = sql_fetch($sql,1);

// 외부 라벨 추출
if(strlen($itm['itm_barcode'])>40) {
	$itm['itm_barcodes'] = explode("_",$itm['itm_barcode']);
	// print_r2($itm['itm_barcodes']);
	$itm['itm_com_barcode'] = $itm['itm_barcodes'][3];
}
else {
	$itm['itm_status_code'] = 'finish';
}

// error_stitch=봉제불량,error_wrinkle=주름불량,error_fabric=원단불량,error_push=누름불량,error_pollution=오염불량,error_bottom=하단불량,error_etc=기타불량
$defect_type_array = array('error_stitch','error_wrinkle','error_fabric','error_push','error_pollution','error_bottom','error_etc');
?>
<style>
	form {padding:10px 100px 100px;}
	section {border:solid 1px #ddd;font-size:0.8em;}
	section ul {margin-left:-11px;}
	table {border:solid 1px #666;margin-top:10px;}
	table caption {text-align:left;background:#222;color:white;padding:7px;}
	tr td:first-child {width:120px;font-size:0.9em;}
	tr td input {border:solid 1px #ddd;padding:4px;}
    button {background:#37a7ff;padding:10px 110px;font-size:1.5em;border:none;border-radius:4px;cursor:pointer;}
</style>

<h1>리워크 완료 API</h1>
<form id="form01" action="./form2.php">
<section>
	<ul>
		<li>완제품 상태값과 함께 외부바코드를 입력하는 API입니다.</li>
		<li>외부바코드가 있으면 자동으로 완료상태입니다. 외부바코드가 없으면 완료코드에 finish 값을 넣어서 전송해 주셔야 합니다.</li>
		<li>반환(return)값: itm_idx, itm_status</li>
	</ul>
</section>

<table>
	<tr><td>공통 토큰</td><td><input type="text" name="token" value="1099de5drf09"></td></tr>
</table>

<table>
	<caption>등록정보</caption>
	<tr><td>바코드</td><td><input type="text" name="itm_barcode" value="<?=$itm['itm_barcode']?>" style="width:370px;"></td></tr>
	<tr><td>외부바코드</td><td><input type="text" name="itm_com_barcode" value="<?=$itm['itm_com_barcode']?>" style="width:370px;"></td></tr>
	<tr><td>완료코드</td><td><input type="text" name="itm_status_code" value="<?=$itm['itm_status_code']?>" style="width:370px;"></td></tr>
	<tr><td>날짜</td><td><input type="text" name="itm_date" value="<?=date("y.m.d",time())?>"></td></tr>
	<tr><td>시간</td><td><input type="text" name="itm_time" value="<?=date("H:i:s",time()-rand(0,86400))?>"></td></tr>
</table>

<hr>
<button type="submit">확인</button>
</form>
