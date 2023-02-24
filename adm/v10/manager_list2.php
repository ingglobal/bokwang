<?php
$sub_menu = "990165";
include_once('./_common.php');

auth_check($auth[$sub_menu],'r');

$sql_common = " from {$g5['member_table']} ";

$where = array();
//$where[] = " mb_level >= 6 AND mb_2 > 0 ";   // 디폴트 검색조건
$where[] = " mb_level >= 6 AND mb_level <= 8 ";   // 디폴트 검색조건


// 관리자는 전부, 다른 사람들은 자기 이하만 노출
if($is_admin=='super') {
    $where[] = " (1) ";
}
// 삭제권한을 가진 사람
else if(!auth_check($auth[$sub_menu],'d',1)) {
    $where[] = " mb_level <= 8 ";
}
// 직원인 경우 (팀장이상 vs 팀원, 팀원은 자기 팀(한단계 위조직)만 보이고, 팀장 이상은 자기 조직 하부가 다 보임)
else {
    //print_r3(get_dept_idxs());
    if(get_dept_idxs()) {
        $where[] = " mb_2 IN (".get_dept_idxs().") ";
    }
}

// 법인접근 권한이 없으면 자기 법인만 조회 가능
if(!$member['mb_firm_yn']) {
    $where[] = " mb_4 = '".$member['mb_4']."' ";
}

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'mb_point' ) :
			$where[] = " {$sfl} >= '".trim($stx)."' ";
            break;
		case ( $sfl == 'mb_level' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		case ( $sfl == 'mb_hp' ) :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}


//-- 부서 검색이 있으면..
if($ser_trm_idxs) {
    $where[] = " mb_2 IN (".$ser_trm_idxs.") ";
}


// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "mb_datetime";
    $sod = "desc";
}

$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql,1);
$total_count = $row['cnt'];
//echo print_r3($sql);

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// 차단회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_intercept_date <> '' ";
$row = sql_fetch($sql);
$intercept_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '관리자관리';
include_once('./_head.php');


$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);
//echo $sql.'<br>';

$colspan = 16;

// 검색어 확장
$qstr .= $qstr.'&ser_trm_idxs='.$ser_trm_idxs;
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총회원수 </span><span class="ov_num"> <?php echo number_format($total_count) ?>명 </span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

<label for="sfl" class="sound_only">검색대상</label>
<?php if (!auth_check($auth[$sub_menu],'d',1) || $member['mb_1'] >= 6) { ?>
<select name="ser_trm_idxs" class="cp_field" title="부서선택">
	<option value="">전체부서</option>
	<?//=$department_select_options?>
    <?=get_dept_select($_GET['ser_trm_idxs'],$sub_menu,'select')?>
</select>
<script>$('select[name=ser_trm_idxs]').val("<?=$_GET['ser_trm_idxs']?>").attr('selected','selected');</script>
<?php } ?>
<select name="sfl" id="sfl">
    <option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>이름</option>
    <option value="mb_id"<?php echo get_selected($_GET['sfl'], "mb_id"); ?>>회원아이디</option>
    <option value="mb_nick"<?php echo get_selected($_GET['sfl'], "mb_nick"); ?>>닉네임</option>
    <option value="mb_level"<?php echo get_selected($_GET['sfl'], "mb_level"); ?>>권한</option>
    <option value="mb_email"<?php echo get_selected($_GET['sfl'], "mb_email"); ?>>E-MAIL</option>
    <option value="mb_tel"<?php echo get_selected($_GET['sfl'], "mb_tel"); ?>>전화번호</option>
    <option value="mb_hp"<?php echo get_selected($_GET['sfl'], "mb_hp"); ?>>휴대폰번호</option>
    <option value="mb_point"<?php echo get_selected($_GET['sfl'], "mb_point"); ?>>포인트</option>
    <option value="mb_datetime"<?php echo get_selected($_GET['sfl'], "mb_datetime"); ?>>가입일시</option>
    <option value="mb_ip"<?php echo get_selected($_GET['sfl'], "mb_ip"); ?>>IP</option>
    <option value="mb_recommend"<?php echo get_selected($_GET['sfl'], "mb_recommend"); ?>>고객아이디</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">

</form>

<div class="local_desc01 local_desc">
    <p>본 페이지에서는 사원관련 정보만 관리합니다. (추가적인 정보 수정이 필요한 경우 관리자에게 문의해 주세요.) </p>
    <p>(매출)고객아이디: 고객이 매출을 하게 되면 담당자(영업자)에게 수당이 할당됩니다. 고객아이디 변경(or추가)은 운영팀에 문의해 주세요.</p>
</div>


<form name="fmemberlist" id="fmemberlist" action="./manager_list_update.php" onsubmit="return fmemberlist_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" id="mb_list_chk" rowspan="2" style="display:<?php if(auth_check($auth[$sub_menu],'d',1)) echo 'none';?>">
            <label for="chkall" class="sound_only">회원 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col"><?php echo subject_sort_link('mb_name') ?>이름</a></th>
        <th scope="col"><?php echo subject_sort_link('mb_id') ?>아이디</a></th>
        <th scope="col"><?php echo subject_sort_link('mb_nick') ?>닉네임</a></th>
        <th scope="col" colspan="2" class="text_left padding_left_20"><?php echo subject_sort_link('mb_2') ?>소속</a></th>
        <th scope="col">법인</th>
        <th scope="col">휴대폰</th>
        <th scope="col"><?php echo subject_sort_link('mb_today_login', '', 'desc') ?>최종접속</a></th>
        <th scope="col">상태</th>
        <th scope="col" rowspan="2">관리</th>
    </tr>
    <tr>
        <th scope="col" colspan="3" class="text_left padding_left_20">추천인 (매출고객아이디)</th>
        <th scope="col" style="width:140px;"><?php echo subject_sort_link('mb_1', '', 'desc') ?>직책</a></th>
        <th scope="col" style="width:140px;"><?php echo subject_sort_link('mb_3', '', 'desc') ?>직급</a></th>
        <th scope="col"><?php echo subject_sort_link('mb_level', '', 'desc') ?>권한</a></th>
        <th scope="col">전화번호</th>
        <th scope="col"><?php echo subject_sort_link('mb_datetime', '', 'desc') ?>가입일</a></th>
        <th scope="col"><?php echo subject_sort_link('mb_point', '', 'desc') ?> 포인트</a></th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        //print_r2($row);
        //print_r2($g5['board']['setting2_name']);

        // 관리권한이 있으면 수정 (d 권한이 있거나 팀장이거나 자기것만)
        if (!auth_check($auth[$sub_menu],'d',1) || $member['mb_1']>=6 || $row['mb_id']==$member['mb_id']) {
            $s_mod = '<a href="./manager_form.php?'.$qstr.'&amp;w=u&amp;mb_id='.$row['mb_id'].'" class="btn btn_03">수정</a>';
        } else {
            $s_mod = '-';
        }

        $leave_date = $row['mb_leave_date'] ? $row['mb_leave_date'] : date('Ymd', G5_SERVER_TIME);
        $intercept_date = $row['mb_intercept_date'] ? $row['mb_intercept_date'] : date('Ymd', G5_SERVER_TIME);

        $mb_nick = get_sideview2($row['mb_id'], get_text($row['mb_nick']), $row['mb_email'], $row['mb_homepage']);

        $mb_id = $row['mb_id'];
        $leave_msg = '';
        $intercept_msg = '';
        $intercept_title = '';
        if ($row['mb_intercept_date']) {
            $mb_id = $mb_id;
            $intercept_msg = '<span class="mb_intercept_msg">차단됨</span>';
            $intercept_title = '차단해제';
        }
        if ($intercept_title == '')
            $intercept_title = '차단하기';

        $bg = 'bg'.($i%2);
        
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['mb_id'] ?>">
        <td headers="mb_list_chk" class="td_chk" rowspan="2" style="display:<?php if(auth_check($auth[$sub_menu],'d',1)) echo 'none';?>">
            <input type="hidden" name="mb_id[<?php echo $i ?>]" value="<?php echo $row['mb_id'] ?>" id="mb_id_<?php echo $i ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mb_name']); ?> <?php echo get_text($row['mb_nick']); ?>님</label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td headers="mb_list_name" class="td_mbname"><?php echo get_text($row['mb_name']); ?></td>
        <td headers="mb_list_id" class="td_name sv_use"><!-- 아이디 -->
            <?php
            if($member['mb_manager_account_yn'])
                echo '<a href="../auth_list.php?sfl=a.mb_id&stx='.$row['mb_id'].'" target="_blank">'.$mb_id.'</a>';
            else
                echo $mb_id;
            ?>
        </td>
        <td headers="mb_list_nick" class="td_name sv_use"><div><?php echo $mb_nick ?></div></td><!-- 닉네임 -->
        <td headers="mb_list_auth" colspan="2" class="td_mbstat text_left padding_left_20"><!-- 소속 -->
            <input type="hidden" name="mb_2_old[<?php echo $i ?>]" value="<?php echo $row['mb_2'] ?>" id="mb_2_old_<?php echo $i ?>">
            <?php  if(auth_check($auth[$sub_menu],'d',1)) { ?>
            <input type="hidden" name="mb_2[<?php echo $i ?>]" value="<?php echo $row['mb_2'] ?>" id="mb_2_<?php echo $i ?>" class="tbl_input full_input">
            <?php echo $g5['department_up_names'][$row['mb_2']] ?>
            <?php } else { ?>
			<select name="mb_2[<?php echo $i; ?>]" id="mb_2_<?=$row['mb_id']?>" style="width:245px;">
				<option value="">조직선택</option>
				<?//=$department_form_options?>
                <?=get_dept_select($row['mb_2'],$sub_menu,'form')?>
			</select>
			<script>$("select[id=mb_2_<?=$row['mb_id']?>]").val("<?=$row['mb_2']?>").attr("selected","selected");</script>
            <?php } ?>
        </td>
        <td headers="mb_list_grp" class="td_numsmall" style="white-space:nowrap;"><?php echo cut_str($g5['board']['setting2_name'][$row['mb_4']],6,'..') ?></td><!-- 법인 -->
        <td headers="mb_list_mobile" class="td_tel"><?php echo get_text($row['mb_hp']); ?></td>
        <td headers="mb_list_lastcall" class="td_date"><?php echo substr($row['mb_today_login'],2,8); ?></td>
        <td headers="mb_list_auth" class="td_mbstat">
            <?php
            if ($leave_msg || $intercept_msg) echo $leave_msg.' '.$intercept_msg;
            else echo "정상";
            ?>
        </td>
        <td headers="mb_list_mng" rowspan="2" class="td_mng td_mng_s">
			<?php echo $s_mod ?><!-- 수정 -->
		</td>
    </tr>
    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['mb_id'] ?>">
        <td headers="mb_list_recommend" colspan="3" class="td_recommend" style="text-align:left;"><!-- 고객아이디 -->
            <?php  if(auth_check($auth[$sub_menu],'d',1)) { ?>
            <input type="hidden" name="mb_recommend[<?php echo $i ?>]" value="<?php echo get_text($row['mb_recommend']) ?>" id="mb_recommend_<?php echo $i ?>" class="tbl_input full_input">
            <?php echo get_text($row['mb_recommend']) ?>
            <?php } else { ?>
            <input type="text" name="mb_recommend[<?php echo $i ?>]" value="<?php echo get_text($row['mb_recommend']) ?>" id="mb_recommend_<?php echo $i ?>" class="tbl_input full_input">
            <?php } ?>
        </td>
        <td headers="mb_list_1" class="td_1"><!-- 직책 -->
            <?php  if(auth_check($auth[$sub_menu],'d',1)) { ?>
            <input type="hidden" name="mb_1[<?php echo $i ?>]" value="<?php echo $row['mb_1'] ?>" id="mb_1_<?php echo $i ?>" class="tbl_input full_input">
            <?php echo $g5['set_mb_positions_value'][$row['mb_1']] ?>
            <?php } else { ?>
            <select name="mb_1[<?php echo $i; ?>]" id="mb_1_<?=$row['mb_id']?>" style="width:100px;">
				<option value="">직책선택</option>
                <?php echo get_set_options_select('set_mb_positions',1, $member['mb_1'], $row['mb_1'], $sub_menu) ?>
			</select>
			<script>$("select[id=mb_1_<?=$row['mb_id']?>]").val("<?=$row['mb_1']?>").attr("selected","selected");</script>
            <?php } ?>
        </td>
        <td headers="mb_list_3" class="td_3"><!-- 직급 -->
            <?php  if(auth_check($auth[$sub_menu],'d',1)) { ?>
            <input type="hidden" name="mb_3[<?php echo $i ?>]" value="<?php echo $row['mb_3'] ?>" id="mb_3_<?php echo $i ?>" class="tbl_input full_input">
            <?php echo $g5['set_mb_ranks_value'][$row['mb_3']] ?>
            <?php } else { ?>
			<select name="mb_3[<?php echo $i; ?>]" id="mb_3_<?=$row['mb_id']?>" style="width:100px;">
				<option value="">직급선택</option>
                <?php echo get_set_options_select('set_mb_ranks',1, $member['mb_3'], $row['mb_3'], $sub_menu) ?>
			</select>
			<script>$("select[id=mb_3_<?=$row['mb_id']?>]").val("<?=$row['mb_3']?>").attr("selected","selected");</script>
            <?php } ?>
        </td>
        <td headers="mb_list_auth" class="td_mbstat"><!-- 권한 -->
            <?php  if(auth_check($auth[$sub_menu],'d',1)) { ?>
            <input type="hidden" name="mb_level[<?php echo $i ?>]" value="<?php echo $row['mb_level'] ?>" id="mb_2_<?php echo $i ?>" class="tbl_input full_input">
            <?php echo $row['mb_level'] ?>
            <?php } else { 
            echo get_member_level_select("mb_level[$i]", 1, $member['mb_level'], $row['mb_level']);
            } ?>

        </td>
        <td headers="mb_list_tel" class="td_tel"><?php echo get_text($row['mb_tel']); ?></td>
        <td headers="mb_list_join" class="td_date"><?php echo substr($row['mb_datetime'],2,8); ?></td>
        <td headers="mb_list_point" class="td_num"><a href="../point_list.php?sfl=mb_id&amp;stx=<?php echo $row['mb_id'] ?>"><?php echo number_format($row['mb_point']) ?></a></td>

    </tr>

    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top" style="display:<?php if(auth_check($auth[$sub_menu],'d',1)) echo 'none';?>">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <?php if(!auth_check($auth[$sub_menu],'d',1)) { ?>
    <a href="./manager_form.php" id="member_add" class="btn btn_01">관리자추가</a>
    <?php } ?>

</div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

<script>
$(function() {
    // 마우스 hover 설정
    $(".tbl_head01 tbody tr").on({
        mouseenter: function () {
            //stuff to do on mouse enter
            //console.log($(this).attr('od_id')+' mouseenter');
            //$(this).find('td').css('background','red');
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#0b1938');
            
        },
        mouseleave: function () {
            //stuff to do on mouse leave
            //console.log($(this).attr('od_id')+' mouseleave');
            //$(this).find('td').css('background','unset');
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
        }    
    });
    

	// 부서 검색 추출, 해당 부서가 아닌 정보들은 숨김 (mb_level<8 이면서 팀장 이상인 경우)
	<?php if (!$member['mb_manager_account_yn']) { ?>
	var dept_array = [<?php echo get_dept_idxs();?>];
	$('select[name=ser_trm_idxs] option').each(function(e) {
		//alert( $(this).val() );
		if($(this).val() !='') {
			var this_option = $(this);
			var dept_option_array = $(this).val().split(',');
			dept_option_array.forEach( function (value) {
				//console.log( value + ' / ' + this_option.val() + ' / ' + this_option.text() );
				//console.log( dept_array.indexOf( parseInt(value) ) );
				//console.log( '---' );
				// 배열 안에 해당 값이 없으면 옵션값 숨김
				if( dept_array.indexOf( parseInt(value) ) == -1 ) {
					//console.log( this_option.val() );
					//console.log( '제거' );
					this_option.remove();
				}
			});
		}
	});
	<?php } ?>
});


function fmemberlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
