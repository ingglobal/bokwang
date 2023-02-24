<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$gap = $g5['setting']['set_monitor_time'];
$reload = $g5['setting']['set_monitor_reload'];
$bom_idx = $config['cf_line'.$line.'_bom_idx'];//1464,464
//echo $line;
//echo $monitor;
//echo $bom_idx."<br>";
$bom = sql_fetch(" SELECT bct_id FROM {$g5['bom_table']} WHERE bom_idx = '{$bom_idx}' ");
$bct_id = $bom['bct_id'];
$bct_len = strlen($bct_id);
$bct_cnt = $bct_len / 2;
$cat1 = '';
$cat2 = '';
$cat3 = '';
$cat4 = '';
//$bct_arr = array();
if($bct_id){
    for($i=0;$i<$bct_cnt;$i++){
        ${'cat'.($i+1)} = substr($bct_id,0,(($i+1)*2));
        //array_push($bct_arr,substr($bct_id,0,(($i+1)*2)));
    }
}
//최종 이미지 검출 쿼리 결과를 담을 변수 미리 선언
$res;

// 1차 카테고리 이미지를 확인한다.
$c1sql = " SELECT * FROM {$g5['file_table']} WHERE fle_db_table = 'bom_category' AND fle_type = 'file{$monitor}' AND fle_db_id = '{$cat1}' ORDER BY fle_idx ";
$c1res = sql_query($c1sql,1);
if($c1res->num_rows) $res = $c1res;
// 2차 카테고리 이미지를 확인한다.
$c2sql = " SELECT * FROM {$g5['file_table']} WHERE fle_db_table = 'bom_category' AND fle_type = 'file{$monitor}' AND fle_db_id = '{$cat2}' ORDER BY fle_idx ";
$c2res = sql_query($c2sql,1);
if($c2res->num_rows) $res = $c2res;
// 3차 카테고리 이미지를 확인한다.
$c3sql = " SELECT * FROM {$g5['file_table']} WHERE fle_db_table = 'bom_category' AND fle_type = 'file{$monitor}' AND fle_db_id = '{$cat3}' ORDER BY fle_idx ";
$c3res = sql_query($c3sql,1);
if($c3res->num_rows) $res = $c3res;
// 4차 카테고리 이미지를 확인한다.
$c4sql = " SELECT * FROM {$g5['file_table']} WHERE fle_db_table = 'bom_category' AND fle_type = 'file{$monitor}' AND fle_db_id = '{$cat4}' ORDER BY fle_idx ";
$c4res = sql_query($c4sql,1);
if($c4res->num_rows) $res = $c4res;
// bom객체의 이미지를 확인한다.
$bsql = " SELECT * FROM {$g5['file_table']} WHERE fle_db_table = 'bom' AND fle_type = 'bomf{$monitor}' AND fle_db_id = '{$bom_idx}' ORDER BY fle_idx ";
$bres = sql_query($bsql,1);
if($bres->num_rows) $res = $bres;

//print_r2($res);
$lst = '';
$no_url = G5_URL.'/device/monitors/img/comming_soon.jpg';
if($res->num_rows){
    $lst .= '<ul>'.PHP_EOL;
    for($i=0;$row=sql_fetch_array($res);$i++){
        //print_r2($row);
        $img_path = G5_PATH.'/'.$row['fle_path'].'/'.$row['fle_name'];
        if(is_file($img_path)){
            $img_url = G5_URL.'/'.$row['fle_path'].'/'.$row['fle_name'];
        }
        else{
            $img_url = $no_url;
        }
        $lst .= '<li no="'.$i.'" id="m'.$i.'" class="img_lst">'.PHP_EOL;
        $lst .= '<img src="'.$img_url.'">'.PHP_EOL;
        $lst .= '</li>'.PHP_EOL;
    }
    $lst .= '</ul>'.PHP_EOL;
}
else{
    $lst .= '<ul>'.PHP_EOL;
    $lst .= '<li no="'.$i.'" id="m'.$i.'" class="img_lst">'.PHP_EOL;
    $lst .= '<img src="'.$no_url.'">'.PHP_EOL;
    $lst .= '</li>'.PHP_EOL;
}
echo $lst;
if($res->num_rows > 1){
?>
<script>
var idx = 0;
var len = $('.img_lst').length - 1;
$(function(){
    setInterval(function(){
        $('.img_lst').hide();
        $('.img_lst').eq(idx).show();
        if(idx == len) idx = 0;
        else idx++;
    },<?=$gap?>);   
});
</script>
<?php } ?>
<script>
setTimeout(function(){
	location.reload();
},<?=$reload?>);
</script>
