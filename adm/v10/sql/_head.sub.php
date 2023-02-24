<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.sub.php');
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_SQL_URL.'/css/sql.css">', 0);
add_javascript('<script src="'.G5_USER_ADMIN_SQL_URL.'/js/sql.js"></script>', 0);
?>
<div id="sql_head">
    <a class="<?=(($g5['file_name'] == 'index')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>">SQL홈</a>
    <a class="" href="<?=G5_USER_ADMIN_URL?>">관리자홈</a>
    <a class="<?=(($g5['file_name'] == 'excel_upload')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/excel_upload.php">엑셀업로드</a>
    <a class="<?=(($g5['file_name'] == 'read_excel_on_server')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/read_excel_on_server.php">서버엑셀읽기</a>
    <a class="<?=(($g5['file_name'] == 'company_del')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/company_del.php">미소속업체제거</a>
    <a class="<?=(($g5['file_name'] == 'bom_product_exlabel')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/bom_product_exlabel.php">완제품 외부라벨</a>
    <!--a class="<?php ;//(($g5['file_name'] == 'company_insert')?'focus':'')?>" href="<?php ;//G5_USER_ADMIN_SQL_URL?>/company_insert.php">회사등록</a-->
    <a class="<?=(($g5['file_name'] == 'material_input')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/material_input.php">자재등록</a>
</div>
<div id="sql_container">
