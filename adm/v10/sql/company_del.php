<?php
include_once('./_head.sub.php');
include_once(G5_USER_ADMIN_SQL_PATH.'/lib/functions.php');
?>
<div>
    <a id="btn_start" href="<?=G5_USER_ADMIN_SQL_URL?>/company_del.php?start=1">시작</a>
</div>
<?php
$demo = 0;  // 데모모드 = 1

$allData = array();
if($start){

?>
<div class="" style="padding:10px;">
	<span>
		작업 시작~~ <font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전 중간에 중지하지 마세요.
	</span><br><br>
	<span id="cont"></span>
</div>
<?php
} //if($start)
include_once('./_tail.sub.php');


if($start){
$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 10000; //백만분에 몇초간 쉴지 설정(20000/1000000=0.02)(10000/1000000=0.01)(5000/1000000=0.005)
$maxscreen = 50; // 몇건씩 화면에 보여줄건지 설정

flush();
ob_flush();


//$g5['company_table']
//$g5['bom_table'] com_idx, com_idx_provider, com_idx_customer

$sql = " SELECT com_idx,com_name FROM {$g5['company_table']} ";
$result = sql_query($sql,1);

$cnt = 0;

for($i=0;$row=sql_fetch_array($result);$i++){
    
    $sql1 = " SELECT COUNT(*) AS cnt FROM {$g5['bom_table']} 
                WHERE com_idx = '{$_SESSION['ss_com_idx']}'
                    AND ( com_idx_provider = '{$row['com_idx']}' OR com_idx_customer = '{$row['com_idx']}' ) ";

    $res = sql_fetch($sql1);

    if($res['cnt']){
        continue;
    }
    else{
        $sql2 = " DELETE FROM {$g5['company_table']} WHERE com_idx = '{$row['com_idx']}' ";
        sql_query($sql2,1);
    }
    
    $cnt++;

    echo "<script>document.all.cont.innerHTML += '(".$cnt.") ".$row['com_idx']." : ".$row['com_name']."- 삭제됨<br>';</script>\n";

    flush();
    ob_flush();
    ob_end_flush();
    usleep($sleepsec);

    //보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
    if($cnt % $countgap == 0){
        echo "<script>document.all.cont.innerHTML += '<br>';</script>\n";
    }

    //화면 정리! 부하를 줄임 (화면을 싹 지움)
    if($cnt % $maxscreen == 0){
        echo "<script>document.all.cont.innerHTML = '';</script>\n";
    }
}
?>
<script>
	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($i) ?>건 완료<br><br><font color=crimson><b>[끝]</b></font>";
</script>
<?php
} //if($start)