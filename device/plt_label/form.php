<?php
include_once('./_common.php');

$bar_date = date("ymd");
$bar_no = rand(111,999);
$bar_prefix = $bar_date.'_'.$bar_no;
// echo $bar_prefix;

// barcode should be stored beforehand. Find one record from db table and asign it to barcode here.
$sql = " SELECT * FROM {$g5['item_table']} WHERE itm_barcode != '' AND plt_idx = 0 AND itm_status = 'finish' ORDER BY RAND() LIMIT 1 ";
// echo $sql.'<br>';
$itm = sql_fetch($sql,1);
if(!$itm['itm_idx']) {
	echo '출하 처리할 제품이 없습니다.';
}

$plt_count_array = array(50,80,100);
$plt_btn_type_array = array('print','out','print','out','print','out','cancel');
$plt_count = $plt_count_array[rand(0,sizeof($plt_count_array)-1)];
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
<h1>빠레트라벨링 API</h1>
<section>
	<ul>
		<li>빠레트 바코드를 출력하는 API입니다.</li>
		<li>한 파레트에 2가지 상품종류가 들어갈 수도 있습니다. <b style="color:red;"><?=$bar_prefix?></b> 부분을 동일하게 넣어주시면 되겠습니다.</li>
		<li>버튼상태값: 출력(print)/출하처리(out)/출하취소(cancel)</li>
		<li>반환(return)값: itm_idx, itm_status</li>
	</ul>
</section>

<table>
	<tr><td>공통 토큰</td><td><input type="text" name="token" value="1099de5drf09"></td></tr>
</table>

<table>
	<caption>등록정보</caption>
	<tr><td>PLT바코드</td><td><input type="text" name="plt_barcode" value="<?=$bar_prefix.'_'.$itm['bom_part_no'].'_'.$plt_count?>" style="width:370px;"></td></tr>
	<tr><td>버튼상태</td><td><input type="text" name="plt_btn_type" value="<?=$plt_btn_type_array[rand(0,sizeof($plt_btn_type_array)-1)]?>"></td></tr>
	<tr><td>날짜</td><td><input type="text" name="plt_date" value="<?=date("y.m.d",time())?>"></td></tr>
	<tr><td>시간</td><td><input type="text" name="plt_time" value="<?=date("H:i:s",time()-rand(0,86400))?>"></td></tr>
</table>

<hr>
<button type="submit">확인</button>
</form>


