<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if(!function_exists('no_cnt_row')){
function no_cnt_row($arr) {  //13일치 값이 한 개도 없으면 건너띄어야 하므로
    $no_cnt = true;
    foreach($arr as $k => $v){
        if($v != '' && $v != 0 && $v != 0 && preg_match('/[0-9]/',$v)) $no_cnt = false;
    }

    return $no_cnt;
}
}