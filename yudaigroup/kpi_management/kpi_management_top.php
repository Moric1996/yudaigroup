<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

//$edit_f = 1;
/////////////////////////////////////////

$conn = $ybase->connect();

$ybase->title = "KPI資料";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);

$ybase->ST_PRI .= <<<HTML
<script>
//画面高さに合わせてiframeをリサイズ
$(function(){
	var wH = $(window).height();
	var hH = wH - 50;

	$('#kpiiframe').css('height', hH + 'px');
});

</script>

<iframe src="./kpi_management_index_all.php" width="100%" height="100%" id="kpiiframe"></iframe>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>