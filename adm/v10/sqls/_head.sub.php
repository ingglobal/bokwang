<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.sub.php');
include_once('./lib/sqls_function.php');
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/sqls/css/sqls.css">', 0);
@add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/sqls/css/'.$g5['file_name'].'.css">', 0);
add_javascript('<script src="'.G5_USER_ADMIN_URL.'/sqls/js/sqls.js"></script>', 0);
@add_javascript('<script src="'.G5_USER_ADMIN_URL.'/sqls/js/'.$g5['file_name'].'.js"></script>', 0);
$sqls_url = G5_USER_ADMIN_URL.'/sqls';
$sqls_path = G5_USER_ADMIN_PATH.'/sqls';
?>
<div id="sqls_head">
    <a class="<?=(($g5['file_name'] == 'index')?'focus':'')?>" href="<?=$sqls_url?>">SQL홈</a>
    <a class="" href="<?=G5_USER_ADMIN_URL?>">관리자홈</a>
    <a class="<?=(($g5['file_name'] == 'adjust_uph')?'focus':'')?>" href="<?=$sqls_url?>/adjust_uph.php">UPH조정쿼리</a>
    <a class="<?=(($g5['file_name'] == 'insert_item')?'focus':'')?>" href="<?=$sqls_url?>/insert_item.php">+ITEMs+</a>
</div>
<div id="sqls_container">
