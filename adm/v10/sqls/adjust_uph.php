<?php
include_once('./_head.sub.php');
/*
오븐기1=46, 오븐기2=47, 오븐기3=48, 오븐기4=49
preg_match($pt_num,$mms_idx) 
preg_match($pt_date,$itm_date) 
preg_match($pt_dt,$itm_start_dt) 
preg_match($pt_dt,$itm_end_dt) 
*/
$pt_num = "/\d/";
$pt_date = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/";
$pt_dt = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s(0[1-9]|1[0-9]|2[0-3])\:(0[1-9]|[1-5][0-9])\:(0[1-9]|[1-5][0-9])$/";

$adj_mms = ($mms_idx && preg_match($pt_num,$mms_idx)) ? trim($mms_idx) : 48;
$adj_date = ($itm_date && preg_match($pt_date,$itm_date)) ? trim($itm_date) : '2022-07-16';

$res_sql = " SELECT itm_date
    ,MIN(itm_reg_dt) AS min_dt
    ,MAX(itm_reg_dt) AS max_dt
    ,COUNT(itm_idx) AS cnt
    ,TIMESTAMPDIFF(HOUR, MIN(itm_reg_dt), MAX(itm_reg_dt)) AS hours
    ,FORMAT((COUNT(itm_idx) / TIMESTAMPDIFF(HOUR, MIN(itm_reg_dt), MAX(itm_reg_dt))),2) AS uph
FROM {$g5['item_table']} 
    WHERE itm_date = '{$adj_date}'
        AND mms_idx = '{$adj_mms}'
        AND itm_status IN('ing','finish')
    GROUP BY itm_date
";
$res = sql_fetch($res_sql);

// print_r2($res);
$res_start_dt = ($res['min_dt']) ? $res['min_dt'] : $res['itm_date'].' 00:00:00';
$res_end_dt = ($res['max_dt']) ? $res['max_dt'] : $res['itm_date'].' 23:59:59';
$res_cnt = ($res['cnt']) ? $res['cnt'] : 0;
$res_hours = ($res['hours']) ? $res['hours'] : 1;
$res_uph = ($res['uph']) ? $res['uph'] : 0;


$adj_start_dt = ($itm_start_dt && preg_match($pt_dt,$itm_start_dt)) ? trim($itm_start_dt) : $res_start_dt;
$adj_end_dt = ($itm_end_dt && preg_match($pt_dt,$itm_end_dt)) ? trim($itm_end_dt) : $res_end_dt;

$hours = two_days_num($adj_start_dt,$adj_end_dt,'h');

$adj_cnt = $res_cnt;
$adj_hours = $hours;
$adj_uph = number_format(($adj_hours) ? $adj_cnt / $adj_hours : 0,2,'.','');

?>

<div class="sqls_box">
<?php include('./adjust_form.php'); ?>
<div style="border:1px solid #fff;"></div>
<div class="sqls_list">
    <div class="dv_li">
        <h2>시작시간 조정</h2>
        <p>
        <?php
        $sql_start = " UPDATE {$g5['item_table']}  
                        SET itm_reg_dt = '{$adj_start_dt}' 
                    WHERE itm_date = '{$adj_date}' 
                        AND mms_idx = '{$adj_mms}' 
                        AND itm_status IN('ing','finish') 
                        AND itm_reg_dt < '{$adj_start_dt}'
        ";
        echo nl2br($sql_start);
        ?>
        </p>
    </div>
    <div class="dv_li">
        <h2>종료시간 조정</h2>
        <p>
        <?php
        $sql_end = " UPDATE {$g5['item_table']}  
                SET itm_reg_dt = '{$adj_end_dt}' 
            WHERE itm_date = '{$adj_date}' 
                AND mms_idx = '{$adj_mms}' 
                AND itm_status IN('ing','finish') 
                AND itm_reg_dt > '{$adj_end_dt}'
        ";
        echo nl2br($sql_end);
        ?>
        </p>
    </div>
</div>
</div><!--.sqls_box-->
<?php
include_once('./_tail.sub.php');