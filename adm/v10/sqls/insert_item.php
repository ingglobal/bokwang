<?php
include_once('./_head.sub.php');
/*
오븐기1=46, 오븐기2=47, 오븐기3=48, 오븐기4=49
mms_idx=46, mms_idx=47, mms_idx=48, mms_idx=49
trm_idx_line=46, trm_idx_line=47, trm_idx_line=48, trm_idx_line=49
mmg = 14
-----------------------------------------------------------------
*/
$rnd_min = 0;
$rnd_max = 500;
$mmg_idx = 14;
$mms_idx = 48; //오븐기3
$itm_date = '2022-05-16';
//만들어야 하는 재고의 해당 통계날짜
$target_dates = array(
    '2022-06-01','2022-06-02','2022-06-03','2022-06-04','2022-06-06','2022-06-07','2022-06-08','2022-06-09','2022-06-10','2022-06-11','2022-06-13','2022-06-14','2022-06-15','2022-06-16','2022-06-17','2022-06-18','2022-06-20','2022-06-21','2022-06-22','2022-06-23','2022-06-24','2022-06-25','2022-06-27','2022-06-28','2022-06-29','2022-06-30','2022-07-01','2022-07-02','2022-07-04','2022-07-05','2022-07-06','2022-07-07','2022-07-08','2022-07-09','2022-07-11','2022-07-12','2022-07-13','2022-07-14','2022-07-15','2022-07-16'
);
//모든 재고데이터를 특정날짜와 동일하게 나오지 않게 루프를 랜덤하게 빼주기 위한 랜덤배열정의
$rand_nums = array();
foreach($target_dates as $v){
    array_push($rand_nums,mt_rand($rnd_min,$rnd_max));
}
// print_r2($rand_nums);
$call_sql = " SELECT *
FROM {$g5['item_table']}
    WHERE 
        mms_idx = '{$mms_idx}' 
        AND itm_status IN ('ing','finish') 
        AND itm_date = '{$itm_date}'
ORDER BY itm_reg_dt DESC
";
?>

<div class="sqls_box">
<div class="info">
    <ul>
        <li><span class="ttl">(설비그룹)mmg_idx : </span><span class="desc"><?=$mmg_idx?></span></li>
        <li><span class="ttl">(설비)mms_idx : </span><span class="desc"><?=$mms_idx?></span></li>
        <li><span class="ttl">(통계일)itm_date : </span><span class="desc"><?=$itm_date?></span></li>
    </ul>
    <div class="btn_box">
        <a href="<?=G5_USER_ADMIN_URL?>/sqls/insert_item.php?start=1" class="btn btn_02 btn_start">시작</a>
    </div>
</div>
<div class="con_box">
<div id="cont"></div>
<?php
if($start == 1){ //############################ 작업실행 : 시작 #################
$countgap = 10; //몇건씩 보낼지 설정
$sleepsec = 10000; //백만분에 몇초간 쉴지 설정(20000/1000000=0.02)(10000/1000000=0.01)(5000/1000000=0.005)
$maxscreen = 50; // 몇건씩 화면에 보여줄건지 설정

flush();
ob_flush();

$result = sql_query($call_sql,1);
$rows = array();
//print_r2($result);
//참고통계날짜의 데이터가 존재하면 실행
if($result->num_rows > $rnd_max){
for($k=0;$smp=sql_fetch_array($result);$k++){
    array_push($rows,$smp);
}

$tcnt = 0;
for($i=0;$i<count($target_dates);$i++){
    $loop = $result->num_rows - $rand_nums[$i]; //날짜별 생성갯수를 랜덤하게 offset한다 (루프숫자)
    $target_date = $target_dates[$i]; //2022-07-01
    $target_date_nobar = substr(preg_replace("/-/","",$target_date),2); //20220701
    // echo $target_date_nobar."<br>";
    // echo $loop."<br>";
    for($j=0;$j<count($rows);$j++){
        if($j+1 == $loop) break;

        $barcode = $target_date_nobar.substr($rows[$j]['itm_barcode'],6);
        $reg_dt = $target_date.substr($rows[$j]['itm_reg_dt'],10);
        $update_dt = $target_date.substr($rows[$j]['itm_update_dt'],10);

        $sql = " INSERT INTO {$g5['item_table']} SET
            com_idx = '{$rows[$j]['com_idx']}'
            ,imp_idx = '{$rows[$j]['imp_idx']}'
            ,mms_idx = '{$rows[$j]['mms_idx']}'
            ,ori_idx = '{$rows[$j]['ori_idx']}'
            ,bom_idx = '{$rows[$j]['bom_idx']}'
            ,oop_idx = '{$rows[$j]['oop_idx']}'
            ,shf_idx = '{$rows[$j]['shf_idx']}'
            ,bom_part_no = '{$rows[$j]['bom_part_no']}'
            ,itm_name = '{$rows[$j]['itm_name']}'
            ,itm_barcode = '{$barcode}'
            ,itm_com_barcode = '{$rows[$j]['itm_com_barcode']}'
            ,plt_idx = '{$rows[$j]['plt_idx']}'
            ,itm_price = '{$rows[$j]['itm_price']}'
            ,itm_defect = '{$rows[$j]['itm_defect']}'
            ,itm_defect_type = '{$rows[$j]['itm_defect_type']}'
            ,trm_idx_location = '{$rows[$j]['trm_idx_location']}'
            ,itm_shift = '{$rows[$j]['itm_shift']}'
            ,itm_rework = '{$rows[$j]['itm_rework']}'
            ,itm_delivery = '{$rows[$j]['itm_delivery']}'
            ,itm_history = '{$rows[$j]['itm_history']}'
            ,itm_status = '{$rows[$j]['itm_status']}'
            ,itm_date = '{$target_date}'
            ,itm_reg_dt = '{$reg_dt}'
            ,itm_update_dt = '{$update_dt}'
        ";

        sql_query($sql);

        $tcnt++;

        echo "<script>document.getElementById('cont').innerHTML += '".$tcnt." - 처리됨".$barcode.':'.$rows[$j]['bom_idx'].':'.$rows[$j]['itm_status']."<br>';</script>\n";

        flush();
        ob_flush();
        ob_end_flush();
        usleep($sleepsec);

        //보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
        if($tcnt % $countgap == 0){
            echo "<script>document.getElementById('cont').innerHTML += '<br>';</script>\n";
        }

        //화면 정리! 부하를 줄임 (화면을 싹 지움)
        if($tcnt % $maxscreen == 0){
            echo "<script>document.getElementById('cont').innerHTML = '';</script>\n";
        }
    }
}
?>
<script>
document.getElementById('cont').innerHTML += "<br><br>총 <?php echo number_format($tcnt); ?>건 완료<br><br><font color='crimson'><b>[끝]</b></font>";
</script>
<?php
}//if($result->num_rows)
} else { // ################################## 작업종료 : 종료 ##################
    echo "시작하기 전입니다.";
}
?>
</div><!--.con_box-->
</div><!--.sqls_box-->
<?php
include_once('./_tail.sub.php');