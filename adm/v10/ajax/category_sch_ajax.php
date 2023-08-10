<?php
include_once('./_common.php');
/*
Array
(
    [bct_id] => 10
    [bct_name] => HR
)
Array
(
    [bct_id] => 11
    [bct_name] => OSPE
)
Array
(
    [bct_id] => 12
    [bct_name] => TMPE
)
Array
(
    [bct_id] => 13
    [bct_name] => CN7
)
Array
(
    [bct_id] => 14
    [bct_name] => PD
)
Array
(
    [bct_id] => 15
    [bct_name] => QX
)
Array
(
    [bct_id] => 16
    [bct_name] => US4
)
Array
(
    [bct_id] => 17
    [bct_name] => IKPE
)
Array
(
    [bct_id] => 18
    [bct_name] => RJ
)
Array
(
    [bct_id] => 19
    [bct_name] => HI
)
Array
(
    [bct_id] => 1a
    [bct_name] => JX1
)
Array
(
    [bct_id] => 1b
    [bct_name] => RS4
)
Array
(
    [bct_id] => 1c
    [bct_name] => JK1
)
Array
(
    [bct_id] => 2c
    [bct_name] => MX5
)
*/

$bct_level = 0;
//$bct_id가 없으면 1차 분류만 추출해라
if(!$bct_id){
    $where_str = " AND bct_id REGEXP '^.{2}$' ";
    $bct_level = 1;
}
//$bct_id가 있으면 그 하위 분류를 추출해라
else{
    //카테고리#1 $bct_id가 넘어오면 그것의 2차분류를 반환
    if(strlen($bct_id) == 2){
        $where_str = " AND bct_id LIKE '{$bct_id}%' AND bct_id REGEXP '^.{4}$' "; 
        $bct_level = 2;
    }
    //카테고리#2 $bct_id가 넘어오면 그것의 3차분류를 반환
    else if(strlen($bct_id) == 4){
        $where_str = " AND bct_id LIKE '{$bct_id}%' AND bct_id REGEXP '^.{6}$' ";
        $bct_level = 3; 
    }
    //카테고리#3 $bct_id가 넘어오면 그것의 4차분류를 반환
    else if(strlen($bct_id) == 6){
        $where_str = " AND bct_id LIKE '{$bct_id}%' AND bct_id REGEXP '^.{8}$' ";
        $bct_level = 4; 
    }
}

$sql = " SELECT bct_id, bct_name FROM {$g5['bom_category_table']} WHERE (1)
            {$where_str}
        ORDER BY bct_no
";

$res = sql_query($sql,1);

$menu_tag = '<option value="">::'.$bct_level.'차분류선택::</option>';
if($res->num_rows){
    for($i=0;$row=sql_fetch_array($res);$i++){
        // print_r2($row);
        $menu_tag .= '<option value="'.$row['bct_id'].'">'.$row['bct_name'].'</option>';
    }
}

echo $menu_tag;