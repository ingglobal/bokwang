<?php
$sub_menu = "915165";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'offwork';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&ser_mms_idx='.$ser_mms_idx; // 추가로 확장해서 넘겨야 할 변수들

// print_r3($member);
// print_r3($_SESSION);

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    ${$pre}['com_idx'] = $_SESSION['ss_com_idx'];
    ${$pre}['mms_idx'] = 0;
    ${$pre}['off_period_type'] = 1;
    // ${$pre}['mms_idx'] = rand(1,4);
    ${$pre}[$pre.'_start_time'] = G5_SERVER_TIME;
    ${$pre}[$pre.'_end_time'] = G5_SERVER_TIME+3600*3;
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u' || $w == 'c') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
	$com = get_table_meta('company','com_idx',${$pre}['com_idx']);
    $mms = get_table_meta('mms','mms_idx',${$pre}['mms_idx']);

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// 날짜 표현
${$pre}[$pre.'_start_date'] = date("Y-m-d",${$pre}[$pre.'_start_time']);
${$pre}[$pre.'_end_date'] = date("Y-m-d",${$pre}[$pre.'_end_time']);
${$pre}[$pre.'_start_his'] = date("H:i:s",${$pre}[$pre.'_start_time']);
${$pre}[$pre.'_end_his'] = date("H:i:s",${$pre}[$pre.'_end_time']);

// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_gender');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$html_title = ($w=='c')?'복제':$html_title; 
$g5['title'] = '공제시간 '.$html_title;
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

// 각 항목명 및 항목 설정값 정의, 형식: 항목명, required, 폭, 단위(개, 개월, 시, 분..), 설명, tr숨김, 한줄두항목여부
$items1 = array(
    "com_idx"=>array("업체번호","required",60,0,'','',2)
    ,"mms_idx"=>array("설비번호","required",60,0,'','',0)
    ,"off_name"=>array("공제시간명칭","",250,'','','',0)
    ,"off_period_type"=>array("적용기간","required",75,0,'전체기간을 선택하면 해당 설비에 대하여 전체 기간 동안 적용됩니다. 기간을 선택하고 입력하면 전체 기간 상관없이 우선 적용됩니다.','',0)
    ,"off_start_time"=>array("시작시간","required",70,0,'시작시간은 17:00:00와 같이 입력하세요.','',2)
    ,"off_end_time"=>array("종료시간","",70,0,'종료시간은 23:59:59와 같이 끝단위까지 모두 입력하세요.','',0)
    ,"off_memo"=>array("메모","",70,0,'','',0)
);
?>
<style>
.frm_date {width:75px;}
</style>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">
<input type="hidden" name="ser_mms_idx" value="<?php echo $ser_mms_idx ?>">

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>각종 고유번호(설비번호, IMP번호..)들은 내부적으로 다른 데이타베이스 연동을 통해서 정보를 가지고 오게 됩니다.</p>
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
    <tr><!-- 첫줄은 무조건 출력 -->
    <?php
    // 폼 생성 (폼형태에 따른 다른 구조)
    $skips = array($pre.'_idx',$pre.'_reg_dt',$pre.'_update_dt');
    foreach($items1 as $k1 => $v1) {
        if(in_array($k1,$skips)) {continue;}
//        echo $k1.'<br>';
//        print_r2($items1[$k1]).'<br>';
        // 폭
        $form_width = ($items1[$k1][2]) ? 'width:'.$items1[$k1][2].'px' : '';
        // 단위
        $form_unit = ($items1[$k1][3]) ? ' '.$items1[$k1][3] : '';
        // 설명
        $form_help = ($items1[$k1][4]) ? ' '.help($items1[$k1][4]) : '';
        // tr 숨김
        $form_none = ($items1[$k1][5]) ? 'display:'.$items1[$k1][5] : '';
        // 한줄 두항목
        $form_span = (!$items1[$k1][6]) ? ' colspan="3"' : '';

        $item_name = $items1[$k1][0];
        // 기본적인 폼 구조 먼저 정의
        $item_form = '<input type="text" name="'.$k1.'" value="'.${$pre}[$k1].'" '.$items1[$k1][1].'
                        class="frm_input '.$items1[$k1][1].'" style="'.$form_width.'">'.$form_unit;

        // 폼이 다른 구조를 가질 때 재정의
        if(preg_match("/_price$/",$k1)) {
            $item_form = '<input type="text" name="'.$k1.'" value="'.number_format(${$pre}[$k1]).'" '.$items1[$k1][1].'
                        class="frm_input '.$items1[$k1][1].'" style="'.$form_width.'"> '.$form_unit;
        }
        else if(preg_match("/_memo$/",$k1)) {
            $item_form = '<textarea name="'.$k1.'" id="'.$k1.'">'.${$pre}[$k1].'</textarea>';
        }
        else if(preg_match("/_date$/",$k1)) {

        }
        else if(preg_match("/_dt$/",$k1)) {

        }
        // 설비번호인 경우는 전체적용과 개별설비로 나눔
        else if($k1=='mms_idx') {
            if($off['mms_idx']) {
                $mms_idx_1 = ' checked';
                $mms_idx_type = 'text';
            }
            else {
                ${'mms_idx_'.$off['mms_idx']} = ' checked';
                $mms_idx_type = 'hidden';
            }
            $item_form = '<input type="'.$mms_idx_type.'" name="'.$k1.'" value="'.${$pre}[$k1].'" '.$items1[$k1][1].'
                    class="frm_input '.$items1[$k1][1].'" style="'.$form_width.'">';
            $item_form .= ' <label id="'.$k1.'_1"><input type="radio" name="'.$k1.'_radio" id="'.$k1.'_1" value="1" '.$mms_idx_1.'> 설비선택</label>';
            $item_form .= ' <label id="'.$k1.'_0"><input type="radio" name="'.$k1.'_radio" id="'.$k1.'_0" value="0" '.$mms_idx_0.'> 전체설비</label>';
        }
        // 적용기간인 경우는 전체기간과 기간선택으로 나눔
        else if($k1=='off_period_type') {
            // 전체기간
            if($off['off_period_type']) {
                $off_period_1 = ' checked';
            }
            else {
                $off_period_0 = ' checked';
            }
            $item_form = ' <label id="'.$k1.'_0"><input type="radio" name="'.$k1.'" id="'.$k1.'_0" value="0" '.$off_period_0.'> 기간선택</label>';
            $item_form .= ' <label id="'.$k1.'_1"><input type="radio" name="'.$k1.'" id="'.$k1.'_1" value="1" '.$off_period_1.'> 전체기간</label>';
        }
        // 시작시간
        else if($k1=='off_start_time') {
            // 전체기간
            if($off['off_period_type']) {
                $off_period_type = 'hidden';
                $off_span_display = 'display:none;';
            }
            else {
                $off_period_type = 'text';
                $off_span_display = 'display:;';
                ${$pre}['off_start_his'] = date("H:i:s",${$pre}['off_start_time']);
            }
            $item_form = '<input type="'.$off_period_type.'" name="off_start_date" value="'.${$pre}['off_start_date'].'"
                    class="frm_input frm_date">';
            $item_form .= ' <input type="text" name="off_start_his" value="'.${$pre}['off_start_his'].'"
                    class="frm_input" style="'.$form_width.'" placeholder="HH:MM:SS">';
        }
        // 종료시간
        else if($k1=='off_end_time') {
            // 전체기간
            if($off['off_period_type']) {
                $off_period_type = 'hidden';
                $off_span_display = 'display:none;';
            }
            else {
                $off_period_type = 'text';
                $off_span_display = 'display:;';
                ${$pre}['off_end_his'] = date("H:i:s",${$pre}['off_end_time']);
            }
            $item_form = '<input type="'.$off_period_type.'" name="off_end_date" value="'.${$pre}['off_end_date'].'"
                    class="frm_input frm_date">';
            $item_form .= ' <input type="text" name="off_end_his" value="'.${$pre}['off_end_his'].'"
                    class="frm_input" style="'.$form_width.'" placeholder="HH:MM:SS">';
        }

        // 이전(두줄 항목)값이 2인 경우 <tr>열지 않고 td 바로 연결
        if($span_old<=1) {
            echo '<tr style="'.$form_none.'">';
        }
        ?>
            <th scope="row"><?=$item_name?></th>
            <td <?=$form_span?>>
                <?=$form_help?>
                <?=$item_form?>
            </td>
            <?php
            // 현재(두줄 항목)값이 2가 아닌 경우만 </tr>닫기
            if($items1[$k1][6]<=1) {
                echo '</tr>'.PHP_EOL;
            }
            ?>
        <?php
        // 이전값 저장 (2=한줄에 두개 항목을 넣는다는 의미다.)
        $span_old = $items1[$k1][6];
    }
    ?>
    </tr>
	<tr style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
		<th scope="row"><label for="com_status">상태</label></th>
		<td colspan="3">
			<?php echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
			<select name="<?=$pre?>_status" id="<?=$pre?>_status"
				<?php if (auth_check($auth[$sub_menu],"d",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
				<?=$g5['set_status_options']?>
			</select>
			<script>$('select[name="<?=$pre?>_status"]').val('<?=${$pre}[$pre.'_status']?>');</script>
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
    // 기간선택, 전체기간
    $(document).on('click','input[name=off_period_type]',function(e){
        // 기간선택
        if( $(this).val() == '0' ) {
            // $('input[name=off_start_date]').attr('type','text').select().focus();
            $('input[name=off_start_date]').attr('type','text');
            $('input[name=off_end_date]').attr('type','text');
            $('.span_wave').show();
        }
        // 전체기간
        else {
            $('input[name=off_start_date]').attr('type','hidden');
            $('input[name=off_end_date]').attr('type','hidden');
            $('.span_wave').hide();
        }
    });

    // 설비선택, 전체설비
    $(document).on('click','input[name=mms_idx_radio]',function(e){
        if( $(this).val() == '0' ) {
            $('input[name=mms_idx]').attr('old_value',$('input[name=mms_idx]').val()).val('0').attr('type','hidden');
        }
        else {
            $('input[name=mms_idx]').val($('input[name=mms_idx]').attr('old_value')).attr('type','text').select().focus();
        }
    });

    $("input[name$=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

});

function form01_submit(f) {
    // 교대시간 체크

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
