<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.sub.php');

add_javascript('<script src="'.G5_URL.'/device/monitors/js/pdfobject.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_URL.'/device/monitors/css/monitor.css">', 0);

if($monitor){
	$mpath = G5_PATH.'/device/monitors/m'.$monitor.'.php';//include_once(G5_PATH.'/device/monitor/m'.$monitor.'.php');
	include_once($mpath);
}else{
	echo "모니터 변수를 받지 못했습니다.";
}
?>

<script>
$(function(){
	//if($('ul li').length)
});
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>
