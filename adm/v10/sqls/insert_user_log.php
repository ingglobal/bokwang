<?php
include('./_common.php');
/*
'910100' : 마스터대시보드
'915110' : 사원정보
'915120' : 거래처정보
'915125' : 제품카테고리
'915130' : 제품사양(BOM)
'915165' : 공제시간설정
'920100' : 수주관리
'920110' : 출하관리
'930100' : 생산계획(제품별)
'945115' : 완제품제고관리
*/
$menus = array(
    '920100'
    ,'920110'
    ,'930100'
    ,'945115'
);

// array_rand($menus,1);
/*
$mbs = array(
    'kbw'
    ,'kyh'
    ,'rjs'
    ,'rhy'
    ,'khs'
    ,'hmj'
    ,'ldw'
    ,'lyc'
    ,'ktw'
    ,'kes'
);
*/
$mbs = array(
    'kbw'
    ,'kyh'
    ,'rjs'
    ,'rhy'
    ,'khs'
);
// array_rand($mbs,1);

$types = array(
    '등록'
    ,'수정'
    ,'검색'
);
// $types[array_rand($types,1)];
/*
2022-01-05 00:00:00 부터  2022-07-14 00:00:00 까지
------------------------------------------------
FROM_UNIXTIME(FLOOR(unix_timestamp('2022-01-05 00:00:00')+(RAND()*(unix_timestamp('2022-07-14 00:00:00')-unix_timestamp('2022-01-05 00:00:00')))))

*/

//위의 정보를 다시 epcs DB서버 g5_5_user_log테이블에 저장한다.
/*
$sql = " INSERT INTO g5_tblname SET
        mb_id = 'aaa',
        usl_menu_cd = '1111',
        usl_type = 'ok',
        usl_reg_dt = FROM_UNIXTIME(FLOOR(unix_timestamp('2022-01-05 00:00:00')+(RAND()*(unix_timestamp('2022-07-14 00:00:00')-unix_timestamp('2022-01-05 00:00:00')))))
";
*/

$insert_strs = '';
for($i=0;$i<600;$i++){
    $insert_strs .= ($insert_strs === '') ? " ( '".$mbs[array_rand($mbs,1)]."','".$menus[array_rand($menus,1)]."','".$types[array_rand($types,1)]."', FROM_UNIXTIME(FLOOR(unix_timestamp('2022-01-05 00:00:00')+(RAND()*(unix_timestamp('2022-07-14 00:00:00')-unix_timestamp('2022-01-05 00:00:00'))))) ) " : " ,( '".$mbs[array_rand($mbs,1)]."','".$menus[array_rand($menus,1)]."','".$types[array_rand($types,1)]."', FROM_UNIXTIME(FLOOR(unix_timestamp('2022-01-05 00:00:00')+(RAND()*(unix_timestamp('2022-07-14 00:00:00')-unix_timestamp('2022-01-05 00:00:00'))))) ) ";
}

$sql = " INSERT INTO {$g5['user_log_table']} 
        ( mb_id, usl_menu_cd, usl_type, usl_reg_dt )
        VALUES
        {$insert_strs}
";

print_r2($sql);exit;
// sql_query($sql,1);