<?php
$sub_menu = "930105";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '생산실행계획';
// include_once('./_top_menu_orp.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];
$sql_common = " FROM {$g5['order_practice_table']} AS orp
    LEFT JOIN {$g5['order_out_practice_table']} AS oop ON orp.orp_idx = oop.orp_idx
    LEFT JOIN {$g5['order_out_table']} AS oro ON oop.oro_idx = oro.oro_idx
"; 
/*
$sql_common = " FROM {$g5['order_practice_table']} AS orp
    LEFT JOIN {$g5['order_out_practice_table']} AS oop USING(orp_idx)
    LEFT JOIN {$g5['order_out_table']} AS oro USING(oro_idx)
"; 
*/

$where = array();
// 디폴트 검색조건 (used 제외)
$where[] = " orp_status NOT IN ('delete','trash') AND orp.com_idx = '".$_SESSION['ss_com_idx']."' ";

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'ori_idx' || $sfl == 'itm_idx' || $sfl == 'orp.oro_idx' || $sfl == 'oro.com_idx_customer' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		case ( $sfl == 'bct_id' ) :
			$where[] = " {$sfl} LIKE '".trim($stx)."%' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "orp.orp_idx";
    $sod = "desc";
}

$sql_order = " ORDER BY {$sst} {$sod} ";
$sql_group = " GROUP BY oop.orp_idx ";
$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT *,
        SUM(oop.oop_count) AS orp_count
        {$sql_common} {$sql_search} {$sql_group} {$sql_order}
        LIMIT {$from_record}, {$rows}
";
// print_r3($sql);//exit;
$result = sql_query($sql,1);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
?>
<style>
.td_bom_name {text-align:left !important;}
.td_orp_part_no, .td_com_name, .td_orp_maker
,.td_orp_items, .td_orp_items_title {text-align:left !important;}
.span_orp_price {margin-left:20px;}
.span_orp_price b, .span_bit_count b {color:#737132;font-weight:normal;}
#modal01 table ol {padding-right: 20px;text-indent: -12px;padding-left: 12px;}
#modal01 form {overflow:hidden;}
.ui-dialog .ui-dialog-titlebar-close span {
    display: unset;
    margin: -8px 0 0 -8px;
}
.div_product_detail {margin-top:-5px;font-size:0.8em;}
.span_oop_count {margin-left:10px;color:yellow;}
.span_oro_date_plan {margin-left:10px;}
.span_oro_date_plan:before {content:'~';}

.sch_label{position:relative;}
.sch_label span{position:absolute;top:-23px;left:0px;z-index:2;}
.sch_label .data_blank{position:absolute;top:3px;right:-18px;z-index:2;font-size:1.1em;cursor:pointer;}
.slt_label{position:relative;}
.slt_label span{position:absolute;top:-23px;left:0px;z-index:2;}
.slt_label .data_blank{position:absolute;top:3px;right:-18px;z-index:2;font-size:1.1em;cursor:pointer;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="customer_name"<?php echo get_selected($_GET['sfl'], "customer_name"); ?>>거래처</option>
        <option value="bom_name"<?php echo get_selected($_GET['sfl'], "bom_name"); ?>>품명</option>
        <option value="com_idx_customer"<?php echo get_selected($_GET['sfl'], "com_idx_customer"); ?>>거래처번호</option>
        <option value="oro_idx"<?php echo get_selected($_GET['sfl'], "oro_idx"); ?>>출하번호</option>
        <option value="ord_idx"<?php echo get_selected($_GET['sfl'], "ord_idx"); ?>>수주번호</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
    <label for="orp_start_date" class="sch_label">
        <span>시작일<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
        <input type="text" name="orp_start_date" value="<?php echo $orp_start_date ?>" id="orp_start_date" readonly class="frm_input readonly" placeholder="시작일" style="width:100px;" autocomplete="off">
    </label>
    <label for="orp_done_date" class="sch_label">
        <span>완료일<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
        <input type="text" name="orp_done_date" value="<?php echo $orp_done_date ?>" id="orp_done_date" readonly class="frm_input readonly" placeholder="완료일" style="width:100px;" autocomplete="off">
    </label>
    <input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>지시수량에 필요한 자재가 부족한 경우 <span class="color_red">빨간색</span>으로 표시됩니다. 자재 창고위치에 따라 현장 오차가 있을 수 있으므로 반드시 확인하시고 진행하세요.</p>
    <p>상태값이 '확정'인 경우는 관련 정보를 수정하지 마세요. (지시수량, 공정, 라인 정보 등..)</p>
    <p>'생산수량' 항목의 값은 생산이 진행중일 때 표시됩니다.</p>
</div>

<div class="select_input">
    <h3>선택목록 데이터일괄 입력</h3>
    <p style="padding:30px 0 20px">
        <label for="" class="slt_label">
            <span>라인<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
            <select id="o_line">
                <option value="">-라인선택-</option>
                <?=$line_form_options?>
            </select>
        </label>
        <label for="" class="slt_label">
            <span>출하처<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
            <input type="hidden" id="com_idx_shipto" value="">
            <input type="text" id="com_name2" value="" add="./customer_shipto_select.php?file_name=<?php echo $g5['file_name']?>" class="frm_input readonly" readonly autocomplete="off">
        </label>
        <label for="" class="slt_label">
            <span>상태<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
            <select name="o_status" id="o_status">
                <option value="">-선택-</option>
                <?=$g5['set_oro_status_value_options']?>
            </select>
        </label>
        <input type="button" id="slt_input" onclick="slet_input(document.getElementById('form01'));" value="선택항목 일괄입력" class="btn btn_02">
    </p>
</div>
<script>
$('.data_blank').on('click',function(e){
    e.preventDefault();
    //$(this).parent().siblings('input').val('');
    var obj = $(this).parent().next();
    if(obj.prop("tagName") == 'INPUT'){
        if(obj.attr('type') == 'hidden'){
            obj.val('');
            obj.siblings('input').val('');
        }else if(obj.attr('type') == 'text'){
            obj.val('');
        }
    }else if(obj.prop("tagName") == 'SELECT'){
        obj.val('');
    }
});
</script>
<form name="form01" id="form01" action="./order_practice_list_update.php" onsubmit="return form01_submit(this);" method="post">
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
        <th scope="col" id="orp_list_chk">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">지시번호</th>
        <th scope="col">라인</th>
        <th scope="col">시작일</th>
        <th scope="col">완료일</th>
        <th scope="col">생산품</th>
        <th scope="col">자재</th>
        <th scope="col">지시수량</th>
        <th scope="col">실시간생산</th>
        <th scope="col">수주/출하</th>
        <th scope="col">상태</th>
        <th scope="col">관리</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // 생산제품 & 자재재고 계산(함수) ------------------------
        $ar['orp_idx'] = $row['orp_idx'];
        //print_r3($ar);
        //continue;
        $row['stock'] = material_stock_check($ar);  // 실행계획 기준(orp_idx)
        unset($ar);
        // print_r2($row['stock']);
        $orp_cnt = 0;
        if($row['stock']['products']) {
            $orp_cnt = sizeof($row['stock']['products']);
            for ($j=0; $j<sizeof($row['stock']['products']); $j++) {
                $row['products'][] = '<div>'.$row['stock']['products'][$j]['bom_name']
                                    .'<div class="div_product_detail">'
                                        .'<span class="span_com_idx_customer">'.$g5['customer'][$row['stock']['products'][$j]['com_idx_customer']]['com_name'].'</span>'
                                        .'<span class="span_oop_count">'.$row['stock']['products'][$j]['oop_count'].'</span>'
                                        .'<span class="span_oro_date_plan">'.$row['stock']['products'][$j]['oro_date_plan'].'</span>'
                                    .'</div>'
                                    .'</div>';
            }
        }
        $row['multi_oop'] = ($orp_cnt > 1) ? true : false;
        // 자재 재고
        // echo $row['stock']['stock_predict'].'<br>';
        $row['mtr_stock_class'] = ($row['stock']['stock_predict']<0)? 'span_stock_lack':'';  // 음수인 경우
        $row['mtr_stock_text'] = !$row['stock']['stock_predict'] ? '할당전'
                                    : '<span class="span_stock_predict '.$row['mtr_stock_class'].'">'.$row['stock']['stock_predict'].'</span>';

        // 자재종류(필요시 표시)
        if(is_array($row['stock']['materials'])) {
            $row['mtr_stock_detail'] = implode(" ",$row['stock']['materials']);
        }
        // // 생산제품 & 자재재고 계산(함수) ------------------------

        // 생산수량 (생산중이면 생산수량 표시)
        $row['orp_output'] = '생산대기';

        $s_mod = '<a href="./order_practice_form.php?'.$qstr.'&amp;w=u&amp;orp_idx='.$row['orp_idx'].'" class="btn btn_03">수정</a>';
        $s_copy = '<a href="./order_practice_form.php?'.$qstr.'&w=c&orp_idx='.$row['orp_idx'].'" class="btn btn_03" style="margin-right:5px;">복제</a>';

        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['orp_idx'] ?>">
        <td class="td_chk">
            <input type="hidden" name="orp_idx[<?php echo $i ?>]" value="<?php echo $row['orp_idx'] ?>" id="orp_idx_<?php echo $i ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['orp_name']); ?> <?php echo get_text($row['orp_nick']); ?>님</label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_orp_order_no">
            <input type="text" name="orp_order_no[<?=$row['orp_idx']?>]" value="<?=$row['orp_order_no']?>" class="tbl_input orp_order_no_<?=$row['orp_idx']?>" style="width:100px;">
        </td><!-- 지시번호 -->
        <td class="td_orp_operation_line"><!-- 공정/라인 -->
            <input type="hidden" name="trm_idx_line[<?=$row['orp_idx']?>]" value="<?=$row['trm_idx_line']?>" class="trm_idx_line_<?=$row['orp_idx']?>">
            <input type="text" value="<?=$g5['line_name'][$row['trm_idx_line']]?>" readonly class="tbl_input orp_operation_line_<?=$row['orp_idx']?>" style="width:60px;">
        </td>
        <td class="td_orp_start_date"><!-- 시작일 -->
            <?php $row['orp_start_date'] = ($row['orp_start_date']=='0000-00-00'||!$row['orp_start_date'])?'':$row['orp_start_date']; ?>
            <input type="text" name="orp_start_date[<?=$row['orp_idx']?>]" value="<?=$row['orp_start_date']?>" readonly class="tbl_input orp_start_date_<?=$row['orp_idx']?>" style="width:80px;">
        </td>
        <td class="td_orp_done_date"><!-- 완료일 -->
            <?php $row['orp_done_date'] = ($row['orp_done_date']=='0000-00-00'||!$row['orp_done_date'])?'':$row['orp_done_date']; ?>
            <input type="text" name="orp_done_date[<?=$row['orp_idx']?>]" value="<?=$row['orp_done_date']?>" readonly class="tbl_input orp_done_date_<?=$row['orp_idx']?>" style="width:80px;">
        </td>
        <td class="td_bom_name"><!-- 제품(수량) -->
            <?=(is_array($row['products']))?implode(" ",$row['products']):''?></span>
        </td>
        <td class="td_stock"><!-- 자재 -->
            <?=$row['mtr_stock_text']?>
        </td>
        <td class="td_orp_count"><!-- 지시수량 -->
            <input type="text" name="orp_count[<?=$row['orp_idx'] ?>]" value="<?=$row['orp_count']?>" class="tbl_input sit_amt orp_count_<?=$row['orp_idx']?>" style="width:50px;">
        </td>
        <td class="td_orp_output"><?=$row['orp_output']?></td><!-- 실시간생산 -->
        <td class="td_ord_idx"><!-- 수주/출하번호 -->
            <a href="?sfl=oro.ord_idx&stx=<?=$row['ord_idx']?>"><?=$row['ord_idx']?></a>
            -
            <a href="?sfl=orp.oro_idx&stx=<?=$row['oro_idx']?>"><?=$row['oro_idx']?></a>
        </td>
        <td class="td_orp_status"><?=$g5['set_orp_status_value'][$row['orp_status']]?></td><!-- 상태 -->
        <td class="td_mng">
			<?=$s_copy?>
			<?=$s_mod?>
		</td>
    </tr>
    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan='20' class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <?php if (!auth_check($auth[$sub_menu],'d')) { ?>
       <a href="javascript:" id="btn_excel_upload" class="btn btn_02" style="margin-right:50px;">엑셀등록</a>
    <?php } ?>
    <?php if (!auth_check($auth[$sub_menu],'w')) { ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <a href="./order_practice_form.php" id="member_add" class="btn btn_01">추가하기</a>
    <?php } ?>

</div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

<div id="modal01" title="엑셀 파일 업로드" style="display:none;">
    <form name="form02" id="form02" action="./material_excel_upload.php" onsubmit="return form02_submit(this);" method="post" enctype="multipart/form-data">
        <table>
        <tbody>
        <tr>
            <td style="line-height:130%;padding:10px 0;">
                <ol>
                    <li>엑셀은 97-2003통합문서만 등록가능합니다. (*.xls파일로 저장)</li>
                    <li>엑셀은 하단에 탭으로 여러개 있으면 등록 안 됩니다. (한개의 독립 문서이어야 합니다.)</li>
                </ol>
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <input type="file" name="file_excel" onfocus="this.blur()">
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <button type="submit" class="btn btn_01">확인</button>
            </td>
        </tr>
        </tbody>
        </table>
    </form>
</div>


<script>
// 엑셀등록 버튼
$( "#btn_excel_upload" ).on( "click", function() {
    $( "#modal01" ).dialog( "open" );
});
$( "#modal01" ).dialog({
    autoOpen: false
    , position: { my: "right-10 top-10", of: "#btn_excel_upload"}
});


// 마우스 hover 설정
$(".tbl_head01 tbody tr").on({
    mouseenter: function () {
        $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#0b1938');
        
    },
    mouseleave: function () {
        $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
    }    
});

// 가격 입력 쉼표 처리
$(document).on( 'keyup','input[name^=orp_price], input[name^=orp_count], input[name^=orp_lead_time]',function(e) {
    if(!isNaN($(this).val().replace(/,/g,'')))
        $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
});

// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}
    

function form01_submit(f)
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

function form02_submit(f) {
    if (!f.file_excel.value) {
        alert('엑셀 파일(.xls)을 입력하세요.');
        return false;
    }
    else if (!f.file_excel.value.match(/\.xls$|\.xlsx$/i) && f.file_excel.value) {
        alert('엑셀 파일만 업로드 가능합니다.');
        return false;
    }

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
