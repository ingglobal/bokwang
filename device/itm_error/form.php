<?php
include_once('./_common.php');

// barcode exists. Find one record from db table and asign it to barcode here.
$sql = " SELECT * FROM {$g5['item_table']} WHERE itm_barcode != '' AND itm_status = 'ing' ORDER BY RAND() LIMIT 1 ";
$itm = sql_fetch($sql,1);

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

<form id="form01" action="./form2.php">
<h1>검수(불량체크) API</h1>
<section>
	<ul>
		<li>오븐기 통과 후 불량 제품 전달 API입니다.</li>
		<li>반환(return)값: itm_idx, itm_status</li>
	</ul>
</section>

<table>
	<tr><td>공통 토큰</td><td><input type="text" name="token" value="1099de5drf09"></td></tr>
</table>

<table>
	<caption>등록정보</caption>
	<tr><td>바코드</td><td><input type="text" name="itm_barcode" value="<?=$itm['itm_barcode']?>" style="width:370px;"></td></tr>
	<tr><td>불량코드</td><td><input type="text" name="itm_error_code" value="<?=$defect_type_array[rand(0,sizeof($defect_type_array)-1)]?>" style="width:370px;"></td></tr>
	<tr><td>날짜</td><td><input type="text" name="itm_date" value="<?=date("y.m.d",time())?>"></td></tr>
	<tr><td>시간</td><td><input type="text" name="itm_time" value="<?=date("H:i:s",time()-rand(0,86400))?>"></td></tr>
</table>

<hr>
<button type="submit">확인</button>
</form>


