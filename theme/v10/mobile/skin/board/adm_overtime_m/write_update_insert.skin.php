<?php
$insert_str = "
    wr_subject = '$wr_subject',
    wr_content = '$wr_content',
    wr_datetime = '".G5_TIME_YMDHIS."',
    wr_last = '".G5_TIME_YMDHIS."',
    wr_ip = '{$_SERVER['REMOTE_ADDR']}',
    wr_prj_idx = '$wr_prj_idx',
    wr_mb_part = '$wr_mb_part',
    wr_mb_id_worker = '$wr_mb_id_worker',
    wr_mb_id_approver = '$wr_mb_id_approver',
    wr_work_dt = '".G5_TIME_YMDHIS."',
    wr_hour_count = '$wr_hour_count',
    wr_hour_price = '$wr_hour_price',
    wr_total_price = '$wr_total_price',
    wr_work_type = '$wr_work_type',
    wr_apply_status = '$wr_apply_status'
";
if(!$insert_str || $insert_str == "" || $insert_str == " ")
    alert('등록(INSERT)을 위한 쿼리명령이 완성되지 않습니다.');
else
    $sql .= $insert_str;