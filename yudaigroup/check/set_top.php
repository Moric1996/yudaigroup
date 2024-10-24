<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/check.inc');
include('./inc/check_list.inc');

$ybase = new ybase();
$check = new check();
$ybase->session_get();

/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$addsql = "month = $target_month and shop_id = $target_shop_id";
////////////////////

//////////////////////////////////////////
$ybase->title = "店舗チェック-設定";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("設定TOP");
$ybase->ST_PRI .= $check->check_menu("99");

$ybase->ST_PRI .= <<<HTML
<script type="text/javascript">
$(function(){
	$('input[type="month"]').change(function(){
		$("#Form1").submit();
	});
});

</script>

<div class="container">

<h5 style="text-align:center;">設定TOP</h5>
<p></p>
<br>
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-success btn-block" href="./set_item_fm.php?$param" role="button">カテゴリ設定</a></div>
</div>
<br>
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-success btn-block" href="./set_item2_fm.php?$param" role="button">チェック項目設定</a></div>
</div>
<br>
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-success btn-block" href="./set_shop_fm.php?$param" role="button">店舗別チェック項目設定</a></div>
</div>
<br>


HTML;


$ybase->ST_PRI .= <<<HTML

</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>