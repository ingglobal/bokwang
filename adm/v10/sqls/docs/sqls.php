$sql_sum_chk = " SELECT itm_idx FROM {$g5['item_sum_table']} 
            WHERE itm_date = '{$target_date}'
                AND mms_idx = '{$rows[$j]['mms_idx']}'
                AND bom_part_no = '{$rows[$j]['bom_part_no']}'
                AND itm_status = '{$rows[$j]['itm_status']}'
        ";
        $sum_chk = sql_fetch($sql_sum_chk);
        if($sum_chk['itm_idx']){
            $sql_sum = " UPDATE {$g5['item_sum_table']} SET
                itm_count = itm_count + 1
                WHERE itm_idx = '{$sum_chk['itm_idx']}'
            ";
        }
        else{
            $sql_sum = " INSERT INTO {$g5['item_sum_table']} SET
                com_idx = '{$rows[$j]['com_idx']}'
                ,imp_idx = '{$rows[$j]['imp_idx']}'
                ,mms_idx = '{$rows[$j]['mms_idx']}'
                ,mmg_idx = '{$mmg_idx}'
                ,shf_idx = '{$rows[$j]['shf_idx']}'
                ,itm_shift = '{$rows[$j]['itm_shift']}'
                ,trm_idx_line = '{$rows[$j]['mms_idx']}'
                ,bom_idx = '{$rows[$j]['bom_idx']}'
                ,bom_part_no = '{$rows[$j]['bom_part_no']}'
                ,itm_price = '{$rows[$j]['itm_price']}'
                ,itm_count = '1'
                ,itm_status = '{$rows[$j]['itm_status']}'
                ,itm_date = '{$rows[$j]['itm_date']}'
            ";
        }
        sql_query($sql_sum);