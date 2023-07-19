<?php
$sub_menu = '915125';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

if ($_POST['act_button2'] == "일괄수정") {
    $post_bct_id_count = (isset($_POST['bct_id']) && is_array($_POST['bct_id'])) ? count($_POST['bct_id']) : 0;

    for ($i=0; $i<$post_bct_id_count; $i++)
    {
        $sql = " update {$g5['bom_category_table']}
                    set bct_name    = '".$_POST['bct_name'][$i]."'
                where bct_id = '".sql_real_escape_string($_POST['bct_id'][$i])."'
                    AND com_idx = '".$_SESSION['ss_com_idx']."'
        ";
        sql_query($sql,1);
    }
}

// bct_no번호 재지정
$c_sql = " UPDATE {$g5['bom_category_table']} AS t1
            JOIN (
                SELECT bct_id, 
                        ROW_NUMBER() OVER (ORDER BY bct_id ASC) AS row_num
                FROM {$g5['bom_category_table']}
            ) AS t2
            ON t1.bct_id = t2.bct_id
            SET t1.bct_no = t2.row_num;
";
sql_query($c_sql, 1);

//exit;
goto_url("./bom_category_list.php?$qstr");