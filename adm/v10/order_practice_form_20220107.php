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

.tbl_frm01:after {display:block;visibility:hidden;clear:both;content:'';}
.div_wrapper {display:inline-block;background:#1e2531;width:49.5%;}
.dd {min-width: 100%;}
.div_title {background:#000204;padding:15px;}
.div_title .bom_title {color:#00ffe7;font-weight:bold;}
.bom_detail:before {content:"(";margin-left:10px;}
.bom_detail:after {content:")";}
#del-item {margin-top:-6px;}
#nestable3 {padding:10px 20px;min-height:616px;}
.div_bom_list {min-height:600px;padding:10px 20px;}
#frame_bom_list {width:100%;min-height:600px;}
.empty_table {background:#1e2531;}
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
    <tr>
        <th scope="row">작업지시번호</th>
        <td>
            <input type="text" name="orp_order_no" id="orp_order_no" required readonly class="frm_input required readonly" style="width:150px;" value="<?=${$pre}['orp_order_no']?>">
        </td>
        <th scope="row">상태</th>
        <td>
            <select name="<?=$pre?>_status" id="<?=$pre?>_status">
            <?=$g5['set_orp_status_options']?>
            </select>
            <script>$('select[name="<?=$pre?>_status"]').val('<?=${$pre}[$pre.'_status']?>');</script>
        </td>
    </tr>
	<tr>
        <th scope="row">라인설비</th>
		<td>
            <select name="trm_idx_line" id="trm_idx_line">
                <option value="">라인선택</option>
                <?=$line_form_options?>
            </select>
            <script>$('select[name="trm_idx_line').val('<?=${$pre}['trm_idx_line']?>');</script>
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
        $ar['readonly'] = 'readonly';
        $ar['required'] = 'required';
        $ar['value'] = ${$pre}[$ar['id']];
        echo create_td_input($ar);
        unset($ar);
        ?>
        <?php
        $ar['id'] = 'orp_done_date';
        $ar['name'] = '생산종료일';
        $ar['type'] = 'input';
        $ar['width'] = '80px';
        $ar['readonly'] = 'readonly';
        $ar['required'] = 'required';
        $ar['value'] = ${$pre}[$ar['id']];
        echo create_td_input($ar);
        unset($ar);
        ?>
    </tr>
    <tr>
        <?php
        $ar['id'] = 'bom_memo';
        $ar['name'] = '메모';
        $ar['type'] = 'textarea';
        $ar['value'] = ${$pre}[$ar['id']];
        $ar['colspan'] = 3;
        echo create_tr_input($ar);
        unset($ar);
        ?> 
    </tr>
</tbody>
</table>
</div>

<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script src="<?=G5_USER_ADMIN_JS_URL?>/nestable/jquery.nestable.js"></script>
<script>
$(function() {
    $("input[name$=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

        
    // 생산자
	$("#btn_member").click(function(e) {
		e.preventDefault();
        var href = $(this).attr('href');
		winMember = window.open(href, "winMember", "left=300,top=150,width=550,height=600,scrollbars=1");
        winMember.focus();
	});

});

//###################################### 상품목록 #########################
var listNodeName = 'ol';
var itemNodeName = 'li';
var listClass = 'dd-list';
var contentClass = 'dd3-content';
var includeContent = false;
var naviLastId = <?=$total_count?>;    // 항목수

$(document).ready(function() {
    // activate Nestable for navi
    $('#nestable3').nestable({
        group: 10,
        contentCallback: function(item) {
            var content = item.content || '' ? item.content : item.id;
            content += '';

            return content;
        },
        maxDepth: 0,
        itemClass:'dd-item dd3-item',
        handleClass:'dd-handle dd3-handle',
        contentNodeName: 'div',
        contentClass: 'dd3-content',
        callback: function(l, e, p) {
            printOutput();
        },
        itemRenderer: function(item_attrs, content, children, options, item) {
            var item_attrs_string = $.map(item_attrs, function(value, key) {
                return ' ' + key + '="' + value + '"';
            }).join(' ');

            var html = '<' + options.itemNodeName + item_attrs_string + '>';
            html += '<' + options.handleNodeName + ' class="' + options.handleClass + '">';
            html += '</' + options.handleNodeName + '>';
            html += content;
            html += '</' + options.contentNodeName + '>';
            html += children;
            html += '</' + options.itemNodeName + '>';

            return html;
        }
    });

    // 초기값 입력
    printOutput();

});


// 변경 내용 출력
var printOutput = function() {
    // output initial serialised data
    var result_array = list_update( $("#nestable3").find(listNodeName).first() );
    resultOutput(result_array,'nestable3-output')
};
// 변경 내용 출력 함수
var resultOutput = function(arr, obj) {
    result_text = ((window.JSON)) ? window.JSON.stringify(arr) : 'JSON browser support required for this demo.';
    $('#'+obj).val( result_text );
};

// Serialized output 내용 업데이트 함수
function list_update(obj) {
    var array = [],
        items = obj.children(itemNodeName);
        
    items.each(function() {
        var li = $(this),
            item = $.extend({}, li.data()),
            sub = li.children(listNodeName);
            
        // depth 속성 추가
        var li_depth = li.parents('.dd-list').length - 1;
        item.depth = li_depth;
        item.bom_name = li.find('.'+contentClass).first().find('span.bom_name').text();
        item.bom_idx_child = li.find('.'+contentClass).first().attr('bom_idx_child');
        item.bit_count = li.find('.'+contentClass).first().find('span.bit_count').text();
        item.ori_price = li.find('.'+contentClass).first().find('span.bom_price').attr('price');
        // item.bit_2 = li.find('.'+contentClass).first().attr('bit_2');
    
        if (includeContent) {
            var content = li.find('.' + contentClass).html();
    
            if (content) {
                item.content = content;
            }
        }
    
        if (sub.length) {
            item.children = list_update(sub);
        }
        array.push(item);
    });
    return array;
}


// 내용 수정
$(document).on('click','.dd3-content',function(e){
    e.stopPropagation();
    // 안에 input 박스가 존재하면 input 벗겨내고
    if( $(this).find('input').length ) {
        //console.log('있다.');
        var this_value = $(this).find('input').val();
        $(this).find('span').html( this_value );
        
        printOutput();
    }
    // 아니면 input 박스 추가해서 내용 변경할 수 있도록 한다.
    else {
        //console.log('없다.');
        var this_value = $(this).find('span.bit_count').text();
        $(this).find('span.bit_count').html('<input type="" name="" value="'+this_value+'" class="dd3-content-input">');
        $(this).find('span input').select().focus();
    }
    
});

// 내용수정 input 클릭하면 div 에 영향을 주지 않게 stopPropagation
$(document).on('click','.dd3-content input',function(e){
    e.stopPropagation();
});

// 내용수정 input 키보드를 누르면 1이상의 숫자만 입력하도록
$(document).on('keyup','.dd3-content input',function(e){
    e.stopPropagation();
    var ask = e.keyCode;
    if((ask < 48 || ask > 57) && (ask < 96 || ask > 105)){ //숫자,백스페이,좌우방향이 아닌 키를 입력했다면 무조건 1 입력
        if(ask != 8 && ask != 37 && ask != 39) $(this).val('1');
    }
});

// input박스 Blur or keyup 되면 현재값 입력
$(document).on('blur','.dd3-content input',function(e){
    e.stopPropagation();
    if($(this).val() == '' || $(this).val() == null || $(this).val() == undefined || $(this).val() == '0') $(this).val('1');
    var this_value = $(this).val();
    $(this).closest('span').html( this_value );
    totalCalculatePrice();
    printOutput();
});

// 항목 삭제 클릭
$(document).on('click','.dd3-content .add_items img',function(e){
    e.preventDefault();
    e.stopPropagation();
    var this_id = $(this).closest('.dd-item').attr('data-id');
//    var this_bit_idx = $(this).closest('.dd-item').attr('data-bit_idx');
    var this_subject = $(this).closest('.dd3-content').find('span:first').text();

    if( $(this).hasClass('btn_remove') ) {
        // if(confirm('해당 항목을 삭제하시겠습니까?\n수정하신 후 [확인] 버튼을 클릭해 주셔야 최종 적용됩니다.')) {
            $('#nestable3').nestable('remove', this_id);
        // }
        totalCalculatePrice();
        if($('#nestable3 .dd-list').children().length == 0){
            $('#nestable3 .dd-list').html('<li class="empty_table">구성품이 없습니다.</li>');
        }
        printOutput();
    }
    
});


// 초기화 클릭
$(document).on('click','#del-item',function(e){
    e.preventDefault();
    if(confirm('전체 항목을 삭제하시겠습니까?\n수정하신 후 [확인] 버튼을 클릭해 주셔야 최종 적용됩니다.')) {
        $('.dd-item').each(function(i,v){
            $('#nestable3').nestable('remove', $(this).attr('data-id'));
        });
        $('#nestable3 .dd-list').html('<li class="empty_table">구성품이 없습니다.</li>');
        naviLastId = 0; // id 초기화
        totalCalculatePrice();
        printOutput();
    }    
});

// 항목추가 함수
function add_item(bom_idx, bom_name, bom_part_no, com_name, bom_price, bom_price2) {
    if($('#nestable3 li .dd3-content .add_items .bom_part_no:contains('+bom_part_no+')').length > 0){
        alert('같은 상품을 올릴수 없습니다. 올라간 상품의 수량(갯수)로 조정하세요.');
        return;
    }

    var li_dom ='<div class="dd3-content" bom_idx_child="'+bom_idx+'">'
                +'  <span class="bom_name">'+bom_name+'</span>'
                +'  <div class="add_items">'
                +'      <span class="bom_part_no">'+bom_part_no+'</span>'
                +'      <span class="bom_price" price="'+bom_price2+'">'+bom_price+'원</span>'
                +'      <span class="span_count"><span class="bit_count">1</span>개</span>'
                +'      <img src="https://icongr.am/clarity/times.svg?size=30&color=444444" class="btn_remove" title="삭제">'
                +'  </div>'
                +'</div>';

    var newItem = {
        "id": ++naviLastId,
        "content": li_dom
    };
    $('#nestable3').nestable('add', newItem);
    totalCalculatePrice();
    printOutput();

    // 항목이 한개 이상이면 empty_table 제거
    if( $('.dd-item').length > 0 ) {
        $('.empty_table').remove();
    }
}

//thousand_comma()
function totalCalculatePrice(){
    var item_list = $('#nestable3 .dd-item .dd3-content .add_items');
    var totalprice = 0;
    if(item_list.length > 0){
        item_list.each(function(){
            var soge = Number($(this).find('.bom_price').attr('price')) * Number($(this).find('.span_count').find('.bit_count').text());
            totalprice += soge;
        });
    }
    $('#ord_price').val(thousand_comma(totalprice));
    //console.log(thousand_comma(totalprice));
}
//################################### //상품목록 종료 ######################################



// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}

function form01_submit(f) {
    // 폼에 input 박스가 한개라도 있으면 안 된다.
    // input 처리를 하고 return false
    if( $('.dd3-content input').length ) {
        //alert('수정하시던 작업이 있습니다.\n작업을 마무리해 주세요.');
        var this_value = $('.dd3-content input').val();
        $('.dd3-content input').closest('span').html( this_value );
        printOutput();
        return false;
    }

    if(!$('#nestable3 .dd-list .dd-item').length){
        alert('적어도 상품 한 개 이상은 등록해야 합니다.');
        return false;
    }

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
