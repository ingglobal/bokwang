<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<p class="desc_p">오븐기1=46, 오븐기2=47, 오븐기3=48, 오븐기4=49</p>
<form class="sqls_frm">
    <div class="input_box">
        <span class="ttl">mms_idx : </span>
        <span class="inp">
            <input type="text" name="mms_idx" value="<?=$adj_mms?>" placeholder="설비번호" class="frm_input">
        </span>
    </div>
    <div class="input_box">
        <span class="ttl">itm_date : </span>
        <span class="inp">
            <input type="text" name="itm_date" value="<?=$adj_date?>" placeholder="통계일" class="frm_input">
        </span>
    </div>
    <div class="input_box">
        <span class="ttl">itm_start_dt : </span>
        <span class="inp">
            <input type="text" name="itm_start_dt" value="<?=$adj_start_dt?>" placeholder="최초등록일시" class="frm_input">
        </span>
    </div>
    <div class="input_box">
        <span class="ttl">itm_end_dt : </span>
        <span class="inp">
            <input type="text" name="itm_end_dt" value="<?=$adj_end_dt?>" placeholder="마지막등록일시" class="frm_input">
        </span>
    </div>
    <div class="input_box" style="padding:2px 0;">
        <span class="ttl">itm_cnt : </span>
        <?=$adj_cnt?>&nbsp;개
    </div>
    <div class="input_box" style="padding:2px 0;">
        <span class="ttl">itm_hours : </span>
        <?=$adj_hours?>&nbsp;시간
    </div>
    <div class="input_box" style="padding:2px 0;">
        <span class="ttl">itm_uph : </span>
        <?=$adj_uph?>&nbsp;u/h
    </div>
    <div class="input_box" style="padding:10px;">
        <button type="submit" class="btn btn_01">확인</button>
    </div>
</form>