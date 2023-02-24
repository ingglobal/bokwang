<?php
$sub_menu = "930105";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'order_practice';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&sca='.$sca.'&ser_bom_type='.$ser_bom_type; // 추가로 확장해서 넘겨야 할 변수들

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김

    ${$pre}[$pre.'_count'] = 1;
    ${$pre}[$pre.'_start_date'] = G5_TIME_YMD;
    ${$pre}[$pre.'_status'] = 'pending';
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
    // print_r3(${$pre});
    $bom = get_table_meta('bom','bom_idx',${$pre}['bom_idx']);    // BOM
    $shf = get_table_meta('shift','shf_idx',${$pre}['shf_idx']);    // 작업구간
    $mb1 = get_table_meta('member','mb_id',${$pre}['mb_id']);    // 생산자

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_field');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = '실행계획 '.$html_title;
include_once ('./_head.php');
?>
<style>
.span_oop_count {margin-left:20px;color:yellow;}
.span_com_idx_customer {margin-left:30px;}
.span_oro_date_plan {margin-left:20px;}
.div_oop_count {display:inline-block;float:right;color:yellow;}
</style>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">
<input type="hidden" name="sca" value="<?php echo $sca ?>">
<input type="hidden" name="ser_bom_type" value="<?php echo $ser_bom_type ?>">

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>생산지시수량을 확인하시고 입력해 주세요. 지시수량은 출하수량보다 큰 값이면 안 됩니다.</p>
    <p>확정된 실행계획은 수정할 수 없습니다. 생산에 사용할 자재가 할당되어 있기 때문에 변경하게 되면 재고 수량에 혼란이 생길 수 있습니다.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4" style="width:15%;">
		<col style="width:35%;">
		<col class="grid_4" style="width:15%;">
		<col style="width:35%;">
    </colgroup>
	<tbody>
        <?php
    $ar['id'] = 'orp_order_no';
    $ar['name'] = '작업지시번호';
    $ar['type'] = 'input';
    $ar['width'] = '130px';
    $ar['colspan'] = 3;
    $ar['value'] = ${$pre}[$ar['id']];
    echo create_tr_input($ar);
    unset($ar);
    ?>
	<tr> 
        <th scope="row">공정</th>
		<td>
            <select name="trm_idx_operation" id="trm_idx_operation">
                <option value="">공정선택</option>
                <?=$operation_form_options?>
            </select>
            <script>$('select[name="trm_idx_operation').val('<?=${$pre}['trm_idx_operation']?>');</script>
		</td>
        <th scope="row">라인</th>
		<td>
            <select name="trm_idx_line" id="trm_idx_line">
                <option value="">라인선택</option>
                <?=$line_form_options?>
            </select>
            <script>$('select[name="trm_idx_line').val('<?=${$pre}['trm_idx_line']?>');</script>
		</td>
    </tr>
	<tr> 
        <th scope="row">작업구간</th>
		<td>
            <input type="hidden" name="shf_idx" value="<?=${$pre}['shf_idx']?>">
			<input type="text" name="shf_name" value="<?php echo $shf['shf_name'] ?>" id="shf_name" class="frm_input" readonly>
            <a href="./shift_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_shift">찾기</a>
		</td>
        <th scope="row">생산자</th>
		<td>
            <input type="hidden" name="mb_id" id="mb_id" value="<?=${$pre}['mb_id']?>">
			<input type="text" name="mb_name" id="mb_name" value="<?php echo $mb1['mb_name'] ?>" id="mb_name" class="frm_input" readonly>
            <a href="./member_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_member">찾기</a>
		</td>
    </tr>
    <tr>
        <?php
        $ar['id'] = 'orp_start_date';
        $ar['name'] = '생산시작일';
        $ar['type'] = 'input';
        $ar['width'] = '80px';
        $ar['value'] = ${$pre}[$ar['id']];
        echo create_td_input($ar);
        unset($ar);
        ?>
        <?php
        $ar['id'] = 'orp_end_date';
        $ar['name'] = '생산종료일';
        $ar['type'] = 'input';
        $ar['width'] = '80px';
        $ar['value'] = ${$pre}[$ar['id']];
        echo create_td_input($ar);
        unset($ar);
        ?>
    </tr>
    <?php
    $ar['id'] = 'bom_memo';
    $ar['name'] = '메모';
    $ar['type'] = 'textarea';
    $ar['value'] = ${$pre}[$ar['id']];
    $ar['colspan'] = 3;
    echo create_tr_input($ar);
    unset($ar);
    ?>
    <tr>
        <th scope="row">상태</th>
        <td colspan="3">
            <select name="<?=$pre?>_status" id="<?=$pre?>_status">
            <?=$g5['set_orp_status_options']?>
            </select>
            <script>$('select[name="<?=$pre?>_status"]').val('<?=${$pre}[$pre.'_status']?>');</script>
        </td>
    </tr>
    <tr>
        <th scope="row">출하정보/지시수량</th>
        <td colspan="3" style="padding:15px 10px;">
            <?php
            $sql = "SELECT *
                    FROM {$g5['order_out_practice_table']} AS oop
                        LEFT JOIN {$g5['bom_table']} AS bom ON bom.bom_idx = oop.bom_idx
                        LEFT JOIN {$g5['order_out_table']} AS oro ON oro.oro_idx = oop.oro_idx
                    WHERE orp_idx = '".$orp['orp_idx']."'
            ";
            // echo $sql.'<br>';
            $rs = sql_query($sql,1);
            for($i=0;$row=sql_fetch_array($rs);$i++) {
                // 생산제품 (팝오버 형태로 내용을 보여주기 위한 변수)
                $products[] = '<div class="div_product_detail">'.$row['bom_name']
                                .'<input type="text" name="oop_chk[]" value="'.$i.'">'
                                .'<input type="text" name="oop_idx[]" value="'.$row['oop_idx'].'">'
                                .'<span class="span_com_idx_customer">납품처: '.$g5['customer'][$row['com_idx_customer']]['com_name'].'</span>'
                                .'<span class="span_oro_date_plan">출하예정: '.$row['oro_date_plan'].'</span>'
                                .'<span class="span_oop_count">수량: '.$row['oop_count'].'</span>'
                                .'<div class="div_oop_count">생산지시수량: <input type="text" name="oop_count[]" value="'.$row['oop_count'].'"></div>'
                            .'</div>';

            }
            // print_r2($products);
            ?>
            <?=(is_array($products))?implode(" ",$products):''?></span>
        </td>
    </tr>
</tbody>
</table>
</div>

<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
    $(function() {
        $("input[name$=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
        
        // 작업구간
        $("#btn_shift").click(function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            winShift = window.open(href, "winShift", "left=300,top=150,width=550,height=600,scrollbars=1");
            winShift.focus();
        });
        
    // 생산자
	$("#btn_member").click(function(e) {
		e.preventDefault();
        var href = $(this).attr('href');
		winMember = window.open(href, "winMember", "left=300,top=150,width=550,height=600,scrollbars=1");
        winMember.focus();
	});

});

// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}

function form01_submit(f) {

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
