<?php
$sub_menu = "915160";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'shift';
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
    ${$pre}['shf_period_type'] = 0;
    // ${$pre}['mms_idx'] = rand(1,4);
    ${$pre}[$pre.'_range_1'] = date("H:i:00").'~'.date("H:i:00",time()+43200);
    ${$pre}[$pre.'_target_1'] = 100;
    ${$pre}[$pre.'_start_dt'] = date("Y-m-d H:i:00");
    ${$pre}[$pre.'_end_dt'] = date("Y-m-d H:i:00",time()+86400*3);
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u' || $w == 'c') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

    ${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    // print_r3(${$pre});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
	$com = get_table_meta('company','com_idx',${$pre}['com_idx']);
    $mms = get_table_meta('mms','mms_idx',${$pre}['mms_idx']);

	// 관련 파일 추출
	$sql = "SELECT * FROM {$g5['file_table']} 
			WHERE fle_db_table = '".$pre."' AND fle_db_id = '".${$pre}[$pre.'_idx']."' ORDER BY fle_sort, fle_reg_dt DESC ";
	$rs = sql_query($sql,1);
//	echo $sql;
	for($i=0;$row=sql_fetch_array($rs);$i++) {
		${$pre}[$row['fle_type']][$row['fle_sort']]['file'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							'&nbsp;&nbsp;'.$row['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row['fle_path'].'/'.$row['fle_name']).'&file_name_orig='.$row['fle_name_orig'].'">파일다운로드</a>'
							.'&nbsp;&nbsp;<input type="checkbox" name="'.$row['fle_type'].'_del['.$row['fle_sort'].']" value="1"> 삭제'
							:'';
		${$pre}[$row['fle_type']][$row['fle_sort']]['fle_name'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							$row['fle_name'] : '' ;
		${$pre}[$row['fle_type']][$row['fle_sort']]['fle_path'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							$row['fle_path'] : '' ;
		${$pre}[$row['fle_type']][$row['fle_sort']]['exists'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							1 : 0 ;
	}
	
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_sex');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$html_title = ($w=='c')?'복제':$html_title; 
$g5['title'] = '작업구간 '.$html_title;
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

// 각 항목명 및 항목 설정값 정의, 형식: [항목명, required, 폭, 단위(개, 개월, 시, 분..), 설명, tr숨김, 한줄두항목여부]
$items1 = array(
    "com_idx"=>array("업체번호","required",60,0,'','none',0)
    ,"mms_idx"=>array("설비(iMMS)번호","required",60,0,'','',0)
    ,"shf_start_time"=>array("시작시간","required",130,0,'','',2)
    ,"shf_name"=>array("작업구간명","required",130,0,'','',0)
    ,"shf_end_time"=>array("종료시간","required",130,0,'','',2)
    ,"shf_end_nextday"=>array("종료시간익일여부","",60,0,'','',0)
    ,"shf_period_type"=>array("적용기간","required",130,0,'전체기간을 선택하면 해당 설비에 대하여 전체 기간 동안 적용됩니다. 기간을 선택하고 입력하면 전체 기간 상관없이 우선 적용됩니다.','',0)
    ,"shf_memo"=>array("메모","",70,0,'','',0)
);

?>

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
                        class="frm_input '.$items1[$k1][1].'" style="'.$form_width.'">'.$form_unit;
        }
        else if(preg_match("/_memo$/",$k1)) {
            $item_form = '<textarea name="'.$k1.'" id="'.$k1.'">'.${$pre}[$k1].'</textarea>';
        }
        else if(preg_match("/_date$/",$k1)) {

        }
        else if(preg_match("/_dt$/",$k1)) {

        }
        else if($k1=='mms_idx'){
            $item_form = '<input type="hidden" name="'.$k1.'" value="'.number_format(${$pre}[$k1]).'" '.$items1[$k1][1].'
                        class="frm_input '.$items1[$k1][1].'">';
            $item_form .= '<input type="text" name="mms_name" value="'.$mms['mms_name'].'" id="mms_name" required class="frm_input required" placeholder="설비명" style="width:200px;" readonly>';
            $item_form .= '<button type="button" class="btn btn_02" id="btn_mms">설비찾기</button>';
        }
        // 적용기간인 경우는 전체기간과 기간선택으로 나눔
        else if($k1=='shf_period_type') {
            // 전체기간
            if($shf['shf_period_type']) {
                $shf_period_1 = ' checked';
                $shf_period_type = 'hidden';
                $shf_span_display = 'display:none;';
            }
            else {
                ${'shf_period_'.$shf['shf_period_type']} = ' checked';
                $shf_period_type = 'text';
                $shf_span_display = 'display:;';
            }
            $item_form = '<input type="'.$shf_period_type.'" name="shf_start_dt" value="'.${$pre}['shf_start_dt'].'"
                    class="frm_input" style="'.$form_width.'">';
            $item_form .= ' <span class="span_wave" style="'.$shf_span_display.'">~</span> <input type="'.$shf_period_type.'" name="shf_end_dt" value="'.${$pre}['shf_end_dt'].'"
                    class="frm_input" style="'.$form_width.'">';
            $item_form .= ' <label id="'.$k1.'_0"><input type="radio" name="'.$k1.'" id="'.$k1.'_0" value="0" '.$shf_period_0.'> 기간선택</label>';
            $item_form .= ' <label id="'.$k1.'_1"><input type="radio" name="'.$k1.'" id="'.$k1.'_1" value="1" '.$shf_period_1.'> 전체기간</label>';
        }

        // 기종별 목표 설정
        if(preg_match("/shf_target_/",$k1) && $w!='') {
            $item_shf_no = substr($k1,-1);
            $item_btn = '<a href="javascript:" shf_idx = "'.$shf['shf_idx'].'" shf_no="'.$item_shf_no.'" class="btn btn_02 btn_item_target" style="margin-left:10px;">기종별목표</a>';
        }
        else {
            $item_btn = '';
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
                <?=$item_btn?>
            </td>
            <?php
            // 현재(두줄 항목)값이 2가 아닌 경우만 </tr>닫기
            if($items1[$k1][6]<=1) {
                echo '</tr>'.PHP_EOL;
            }
            ?>
        <?php
        // 이전값 저장
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
    $('input[name="shf_start_time"]').datetimepicker({
        datepicker:false,
        theme:'dark',
        format:'H:i'
    });

    // 기간선택, 전체기간
    $(document).on('click','input[name=shf_period_type]',function(e){
        // 기간선택
        if( $(this).val() == '0' ) {
            $('input[name=shf_start_dt]').attr('type','text').select().focus();
            $('input[name=shf_end_dt]').attr('type','text');
            $('.span_wave').show();
        }
        // 전체기간
        else {
            $('input[name=shf_start_dt]').attr('type','hidden');
            $('input[name=shf_end_dt]').attr('type','hidden');
            $('.span_wave').hide();
        }
    });

    // 설비찾기 버튼 클릭
	$("#btn_mms").click(function(e) {
		e.preventDefault();
		var url = g5_user_admin_url+"/mms_select.php?frm=fwrite&file_name=<?php echo $g5['file_name']?>";
		win_mms_select = window.open(url, "win_mms_select", "left=300,top=150,width=550,height=600,scrollbars=1");
        win_mms_select.focus();
	});

    $(document).on('click','.btn_item_target',function(e){
        var shf_idx = $(this).attr('shf_idx');
        var shf_no = $(this).attr('shf_no');
        // alert( shf_idx +'/'+ shf_no );
		var url = "./shift_item_goal_list.popup.php?file_name=<?=$g5['file_name']?>&shf_idx="+shf_idx+"&shf_no="+shf_no;
		win_item_goal = window.open(url, "win_item_goal", "left=300,top=150,width=550,height=600,scrollbars=1");
        win_item_goal.focus();
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
