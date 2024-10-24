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

$ybase->title = "決起大会アンケート管理";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);

$ybase->ST_PRI .= <<<HTML
<script>
//画面高さに合わせてiframeをリサイズ
$(function(){
	var wH = $(window).height();
	var hH = wH - 50;

	$('#meetingiframe').css('height', hH + 'px');
});

</script>

<iframe src="./kanri/kanri.php" width="100%" height="100%" id="meetingiframe"></iframe>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>