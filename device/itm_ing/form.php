<?php
include_once('./_common.php');
// 인스타 로그인후 인스타쪽 피드 배열을 받아서 올스타에 업데이트하는 폼을 임시로 구현한 페이지입니다.
// 실제로는 member_log.php가 크롤링 서버에서 정보를 받아서 저장합니다.
// 파일명이 실제로는 member_feed_log.php가 더 적합할 수도..

// 출하-실행계획 RANDOM 추출
$sql = " SELECT oop_idx,bom_idx FROM {$g5['order_out_practice_table']}
	WHERE oop_status NOT IN ('trash','delete')
		ORDER BY RAND() LIMIT 1
";
$oop = sql_fetch($sql,1);
$bom = get_table('bom','bom_idx', $oop['bom_idx']);
// print_r2($bom);

$bar_date = date("ymd");
$bar_no = rand(11111,99999);
$bar_prefix = $bar_date.'_'.$bar_no;
// echo $bar_prefix;

// $com_idx_array = array(9999,67,66,65,64,10000);
$barcode_array = array($bar_prefix.'_'.$bom['bom_part_no'].'_19204DERH00011530',$bar_prefix.'_'.$bom['bom_part_no']);
$mms_idx_array = array(46,47,48,49);

// print_r2(item_shif_date_return("2021-12-15 23:51:37"));
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
<h1>생산시작 API</h1>
<?php

?>
<section>
	<ul>
		<li>바코드 출력과 동시에 통신하는 API입니다.</li>
		<li>g5_1_item 테이블에 새로운 record가 생성됩니다.(상태값=ing) / 관련자재(g5_1_meterial)들의 상태값들도 함께 변경됩니다.(상태값=ing) </li>
		<li>재발행인 경우도 같은 값으로 던져주시면 됩니다.</li>
		<li>반환(return)값: 자재재고 리스트, itm_idx, itm_status</li>
	</ul>
</section>

<table>
	<tr><td>공통 토큰</td><td><input type="text" name="token" value="1099de5drf09"></td></tr>
</table>

<table>
	<caption>생산시작</caption>
	<!-- <tr><td>업체 idx</td><td><input type="text" name="com_idx" value="8"></td></tr> -->
	<tr><td>IMP idx</td><td><input type="text" name="imp_idx" value="<?=rand(1,16)?>"></td></tr>
	<tr><td>MMS idx</td><td><input type="text" name="mms_idx" value="<?=$mms_idx_array[rand(0,sizeof($mms_idx_array)-1)]?>"></td></tr>
	<tr><td>실행계획고유번호</td><td><input type="text" name="oop_idx" value="<?=$oop['oop_idx']?>"></td></tr>
	<tr><td>LOT</td><td><input type="text" name="itm_lot" value="<?=rand(1,4)?>"></td></tr>
	<tr><td>파트넘버</td><td><input type="text" name="bom_part_no" value="<?=$bom['bom_part_no']?>"></td></tr>
	<tr><td>바코드</td><td><input type="text" name="itm_barcode" value="<?=$barcode_array[rand(0,sizeof($barcode_array)-1)]?>" style="width:370px;"></td></tr>
	<tr><td>위치</td><td><input type="text" name="trm_idx_location" value="<?=rand(1,4)?>"></td></tr>
	<tr><td>날짜</td><td><input type="text" name="itm_date" value="<?=date("y.m.d",time())?>"></td></tr>
	<tr><td>시간</td><td><input type="text" name="itm_time" value="<?=date("H:i:s",time()-rand(0,86400))?>"></td></tr>
	<tr><td>플래그</td><td><input type="text" name="itm_flag" value="test"></td></tr>
	<!-- <tr><td>메시지</td><td><input type="text" name="itm_message" value="에러코드입니다."></td></tr> -->
</table>
<?php
//구간재설정
// print_r2($g5['set_itm_shift_value']);
// $time = rand(strtotime('2021-12-01 00:00:00'),strtotime(G5_TIME_YMD.' 23:59:59'));
// $server_time = $time;
// $time_ymdhis = date('Y-m-d H:i:s', $server_time);
// echo $time_ymdhis."<br>";
// $shif = item_shif_date_return($time_ymdhis);
// print_r2($shif);
?>
<hr>
<button type="submit">확인</button>
</form>
