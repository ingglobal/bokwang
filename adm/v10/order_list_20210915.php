<?php
$sub_menu = "920100";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');
$g5['title'] = '수주목록';
include_once('./_head.php');
echo $g5['container_sub_title'];
/*
if(!$com_idx)
    alert_close('업체 정보가 존재하지 않습니다.');
$com = get_table_meta('company','com_idx',$com_idx);
*/
$sql_common = " FROM {$g5['order_table']} AS ord
                 LEFT JOIN {$g5['company_table']} AS com ON ord.com_idx = com.com_idx
";

$where = array();
$where[] = " ord_status NOT IN ('trash','delete','del','cancel') AND ord.com_idx = '".$_SESSION['ss_com_idx']."' ";   // 디폴트 검색조건

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'mb_hp' ) :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}
//검색어가 없는 디폴트 상태에서는 오늘날짜에서 13일간의 목록을 보여준다.
/*else{
    //$d_to_day = get_dayAddDate(G5_TIME_YMD,13);
    //$where[] = " ord_date >= '".G5_TIME_YMD."' AND ord_date <= '".$d_to_day."' ";
}*/

if($ord_date_from && !$ord_date_to){
    $ord_date_to = get_dayAddDate($ord_date_from,13);
    $where[] = " ord.ord_date >= '".$ord_date_from."' AND ord.ord_date <= '".$ord_date_to."' ";
}
else if(!$ord_date_from && $ord_date_to){
    $ord_date_from = get_dayAddDate($ord_date_to,-13);
    $where[] = " ord.ord_date >= '".$ord_date_from."' AND ord.ord_date <= '".$ord_date_to."' ";
}
else if($ord_date_from && $ord_date_to && $ord_date_from != $ord_date_to){
    $where[] = " ord.ord_date >= '".$ord_date_from."' AND ord.ord_date <= '".$ord_date_to."' ";
}
else{
    $where[] = " ord.ord_date >= '".G5_TIME_YMD."' AND ord.ord_date <= '".G5_TIME_YMD."' ";
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "ord_date";
    $sod = ""; //"DESC";
}

$sql_order = " order by {$sst} {$sod} ";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">기본목록</a>';


$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "SELECT * {$sql_common} {$sql_search} {$sql_order} ";
//print_r3($sql);
$result = sql_query($sql);

?>
<style>
.td_bom_name {text-align:left !important;width:270px;}
.td_bom_part_no, .td_com_name, .td_bom_maker
,.td_bom_items, .td_bom_items_title {text-align:left !important;}
.span_bom_price {margin-left:20px;}
.span_ori_count:before {content:'×';}
.td_bom_items {color:#818181 !important;}
.span_bom_part_no {margin-left:10px;}
.span_bom_price b, .span_ori_count b {color:#737132;font-weight:normal;}
.div_item {font-size:0.9em;line-height:140%;}

.sch_label{position:relative;}
.sch_label span{position:absolute;top:-23px;left:5px;z-index:2;}
.sch_label .date_blank{position:absolute;top:-21px;right:5px;z-index:2;font-size:1.1em;cursor:pointer;}
.slt_label{position:relative;}
.slt_label span{position:absolute;top:-23px;left:5px;z-index:2;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="bom_name"<?php echo get_selected($_GET['sfl'], "bom_name"); ?>>품명</option>
        <option value="com_idx_customer"<?php echo get_selected($_GET['sfl'], "com_idx_customer"); ?>>거래처번호</option>
        <option value="bom_maker"<?php echo get_selected($_GET['sfl'], "bom_maker"); ?>>메이커</option>
        <option value="bom_memo"<?php echo get_selected($_GET['sfl'], "bom_idx"); ?>>메모</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
    <label for="ord_date_from" class="sch_label">
        <span>수주일(부터)</span>
        <i class="fa fa-times date_blank" aria-hidden="true"></i>
        <input type="text" name="ord_date_from" value="<?php echo $ord_date_from ?>" id="ord_date_from" readonly class="frm_input readonly" placeholder="수주일(부터)" style="width:100px;" autocomplete="off">
    </label>
    ~
    <label for="ord_date_to" class="sch_label">
        <span>수주일(까지)</span>
        <i class="fa fa-times date_blank" aria-hidden="true"></i>
        <input type="text" name="ord_date_to" value="<?php echo $ord_date_to ?>" id="ord_date_to" readonly class="frm_input readonly" placeholder="수주일(까지)" style="width:100px;" autocomplete="off">
    </label>
    <input type="submit" class="btn_submit" value="검색">
</form>
<script>
$('.date_blank').on('click',function(e){
    e.preventDefault();
    $(this).siblings('input').val('');
});
</script>
<div class="local_desc01 local_desc" style="display:no ne;">
    <p>제품개수에 <span style="color:red;">빨간색 깜빡임</span>은 출하데이터가 존재하지 않거나 출하데이터의 갯수와 일치하지 않다는 의미 입니다.(갯수를 맞춰 주셔야 합니다.)</p>
    <p>제품명에 <span style="color:orange;">주황색 깜빡임</span>은 해당 BOM데이터에 가격과 카테고리 설정이 안되어 있다는 의미입니다.(해당 BOM페이지로 이동하여 설정완료 해 주세요.)</p>
</div>


<form name="form01" id="form01" action="./order_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" id="bom_list_chk">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col"><?php echo subject_sort_link('ord_idx') ?>번호</a></th>
        <th scope="col">수주금액</th>
        <th scope="col">제품</th>
        <th scope="col">출하</th>
        <!--th scope="col">실행계획</th-->
        <th scope="col">수주일</th>
        <th scope="col">수주상태</th>
        <th scope="col">관리</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
    <?php
    //print_r2($result);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $csql = sql_fetch(" SELECT com_name FROM {$g5['company_table']} WHERE com_idx = '{$row['com_idx_customer']}' ");
        $row['com_name_customer'] = $csql['com_name'];

        // 출하 건수
        $sql2 = " SELECT COUNT(oro_idx) AS cnt FROM {$g5['order_out_table']} WHERE ord_idx = '".$row['ord_idx']."' ";
        $row['oro'] = sql_fetch($sql2,1);

        // 제품목록
        $sql1 = "SELECT bom.bom_idx, bom.bct_id, bom.bom_name, bom_part_no, bom_price, bom_status
                    , ori.ori_idx, ori.ori_count
                FROM {$g5['order_item_table']} AS ori
                    LEFT JOIN {$g5['bom_table']} AS bom ON bom.bom_idx = ori.bom_idx
                WHERE ori.ord_idx = '".$row['ord_idx']."' AND ori.ori_status NOT IN('trash','delete','del','cancel')
                ORDER BY ori_reg_dt
        ";
        // print_r3($sql1);
        $rs1 = sql_query($sql1,1);
        $out_flag = true;
        for ($j=0; $row1=sql_fetch_array($rs1); $j++) {
            // print_r2($row1);
			$otq_sql = " SELECT SUM(oro_count) AS ous FROM {$g5['order_out_table']} WHERE ord_idx = '{$row['ord_idx']}' AND ori_idx = '{$row1['ori_idx']}' AND oro_status NOT IN('trash','delete','del','cancel') ";
			//echo $otq_sql."<br>";
            $otq = sql_fetch($otq_sql);
			$out_cnt = ($otq['ous']) ? $otq['ous'] : 0;
			//echo $out_cnt;
			$cnt_blick = ($out_cnt != $row1['ori_count']) ? 'txt_redblink' : '';
			
            if(!$row1['bom_price']) $out_flag = false;
            //$bom_mod = ($row1['bom_price'] && $row1['bct_id']) ? $row1['nbsp'].$row1['bom_name'] : '<a href="'.G5_USER_ADMIN_URL.'/bom_form.php?w=u&bom_idx='.$row1['bom_idx'].'" target="_blank" class="txt_orangeblink">'.$row1['nbsp'].$row1['bom_name'].'</a>';
            $bom_mod = ($row1['bom_price']) ? $row1['nbsp'].$row1['bom_name'] : '<a href="'.G5_USER_ADMIN_URL.'/bom_form.php?w=u&bom_idx='.$row1['bom_idx'].'" target="_blank" class="txt_orangeblink">'.$row1['nbsp'].$row1['bom_name'].'</a>';

            $row['item_list'][] = '<div class="div_item">
                                        <span class="span_bom_name">'.$bom_mod.'('.$row1['ori_idx'].')</span>
                                        <span class="span_bom_part_no">'.$row1['bom_part_no'].'</span>
                                        <span class="span_bom_price"><b>'.number_format($row1['bom_price']).'</b>원</span>
                                        <span class="span_ori_count"><b><span class="'.$cnt_blick.'">'.$row1['ori_count'].'</span></b>개</span>
                                    </div>';
        }
		
		$oro_url = '';
        $oro_btn = '';
        if($row['oro']['cnt']){
            $oro_url = './order_out_list.php?sfl=oro.ord_idx&stx='.$row['ord_idx'];
            $oro_btn = $row['oro']['cnt'].'건';
        }
        else {
            if($out_flag){
                $oro_url = './order_out_create.php?w=&ord_idx='.$row['ord_idx'];
                $oro_btn = '<spn style="color:orange;">출하생성</span>';
            }
            else{
                $oro_url = 'javascript:';
                $oro_btn = '<spn style="color:red;">일괄출하<br>불가</span>';
            }
        }

        //$s_item = '<a href="./order_item.php?'.$qstr.'&amp;ord_idx='.$row['ord_idx'].'" class="btn btn_03">상품</a>';
        $s_mod = '<a href="./order_form.php?'.$qstr.'&amp;w=u&amp;ord_idx='.$row['ord_idx'].'" class="btn btn_03">수정</a>';
		
        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['ord_idx'] ?>">
        <td class="td_chk">
            <input type="hidden" name="ord_idx[<?php echo $i ?>]" value="<?php echo $row['ord_idx'] ?>" id="ord_idx_<?php echo $i ?>">
            <label for="chk_<?php echo $i; ?>">
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
            </label>
        </td>
        <td class="td_num"><?php echo $row['ord_idx']; ?></td>
        <td class="td_ord_price" style="text-align:right;"><?=number_format($row['ord_price'])?></td><!-- 수주금액 -->
        <td class="td_com_name"><!-- 제품 -->
            <?=implode(" ",$row['item_list'])?>
        </td>
        <td class="td_ord_ship_date"><a href="<?=$oro_url?>"><?=$oro_btn?></a></td><!-- 출하 -->
        <!--td class="td_practice_cnt">
            
        </td-->
        <td class="td_ord_reg_dt"><?=substr($row['ord_date'],0,10)?></td><!-- 수주일 -->
        <td class="td_ord_status"><?=$g5['set_ord_status_value'][$row['ord_status']]?></td><!-- 수주상태 -->
        <td class="td_mng">
			<?=$s_mod?>
		</td>
    </tr>
    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan='9' class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <?php if (!auth_check($auth[$sub_menu],'d')) { ?>
       <a href="javascript:" id="btn_excel_upload" class="btn btn_02" style="margin-right:50px;">엑셀등록</a>
    <?php } ?>
    <?php if (!auth_check($auth[$sub_menu],'w')) { ?>
    <!--input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02"-->
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <a href="./order_form.php" id="member_add" class="btn btn_01">추가하기</a>
    <?php } ?>

</div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
<!--//http://localhost/epcs/adm/v10/order_excel_upload_test.php-->
<div id="modal01" title="엑셀 파일 업로드" style="display:none;">
    <form name="form02" id="form02" action="./order_excel_upload.php" onsubmit="return form02_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="com_idx" value="<?=$_SESSION['ss_com_idx']?>">
    
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
        <!--tr>
            <td>
                <p>거래처</p>
                <input type="hidden" name="com_idx_customer">
                <input type="text" name="com_name" id="btn_customer" url="./customer_select.php?file_name=<?php echo $g5['file_name']?>" required readonly class="frm_input required readonly">
            </td>
        </tr-->
        <tr>
            <td style="padding:15px 5px;">
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
$("input[name*=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
// 엑셀등록 모달에서 거래처찾기 버튼 클릭
$("#btn_customer").click(function(e) {
    e.preventDefault();
    var href = $(this).attr('url');
    winCustomerSelect = window.open(href, "winCustomerSelect", "left=300,top=150,width=550,height=600,scrollbars=1");
    winCustomerSelect.focus();
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
$(document).on( 'keyup','input[name^=bom_price], input[name^=bom_count], input[name^=bom_lead_time]',function(e) {
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
        if (!confirm("선택한 항목(들)을 정말 삭제 하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.")) {
			return false;
		}
		else {
			$('input[name="w"]').val('d');
		}
    }

    return true;
}
</script>

<?php
include_once('./_tail.php');

