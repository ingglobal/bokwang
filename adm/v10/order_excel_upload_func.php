<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if(!function_exists('excel_time_convert')){
function excel_time_convert($time) {  //45563 날짜값을 날짜포맷변경
    $t = ( $time - 25568 ) * 86400-60*60*9;  // 25569 : -1 daty 
    $t = round($t*10)/10;
    $t = date('Y-m-d',$t);
    return $t;
}
}