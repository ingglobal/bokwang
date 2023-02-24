<?php
include_once('./_common.php');
// 인스타 로그인후 인스타쪽 피드 배열을 받아서 올스타에 업데이트하는 폼을 임시로 구현한 페이지입니다.
// 실제로는 member_log.php가 크롤링 서버에서 정보를 받아서 저장합니다.
// 파일명이 실제로는 member_feed_log.php가 더 적합할 수도..

include(G5_PATH.'/head.sub.php');

$arr = $_REQUEST;
?>
<style>
    #hd_login_msg {display:none;}
    table tr td {border:solid 1px #ddd;padding:10px;}
    button {background:#ff8b37;padding:10px 20px;font-size:1.5em;border-radius:4px;}
    #btn_submit {cursor:pointer;}
</style>

<form id="form02" action="./index.php">

<table>
	<tr><td style="background:#aaa;">JSON BODY (실제로 넘어가는 JSON object)</td></tr>
	<tr>
        <td>
            <?=json_encode($arr);?>
        </td>
    </tr>
	<tr><td style="background:#aaa;">배열값으로 보면 이렇습니다.</td></tr>
	<tr>
        <td style="background:#f3f3f3;">
            <?=print_r2($arr);?>
        </td>
    </tr>
</table>
    
<hr>
<button type="submit" id="btn_submit">등록하기</button>
</form>
<div id="result" style="margin-top:20px;">


<script>
$(document).on('click','#btn_submit',function(e) {
    e.preventDefault();
    $.ajax({
        url:'./index.php',
        type:'post',
        data : "<?=addslashes(json_encode($arr));?>",
        dataType:'json',
        timeout:10000, 
        beforeSend:function(){
            $('#btn_submit').attr("disabled", true);
        },
        success:function(res){
//            var items;
//            for(items in res) { alert(items +': '+ res[items]); }
            if(res.meta.code>200) {
                alert(res.meta.message);
            }
            else {
                $('#btn_submit').attr("disabled", false);
                alert('데이터 입력 성공, 하단에 표시됩니다.\n(요소검사쪽에서 결과를 확인하실 수도 있습니다.)');
                $('#result').text(JSON.stringify(res.meta));
            }
            console.log(res);
        },
        error:function(xmlRequest) {
            alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText 
            + ' \n\rresponseText: ' + xmlRequest.responseText);
        }
    });
});
</script>


<?php
include(G5_PATH.'/tail.sub.php');
?>