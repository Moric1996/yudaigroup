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
$rank = new check();
$ybase->session_get();
if($t_month){
	$tar_month = substr($t_month,0,4)."-".substr($t_month,4,2);
}
$nowyy =date("Y");
$nowmm =date("m");
$nowdd =date("d");
if(!$tar_month){
	$t_month = date("Ym",mktime(0,0,0,$nowmm,$nowdd,$nowyy));
	$tar_month = date("Y-m",mktime(0,0,0,$nowmm,$nowdd,$nowyy));
	$yy = substr($tar_month,0,4);
	$mm = substr($tar_month,5,2);
}else{
	$yy = substr($tar_month,0,4);
	$mm = substr($tar_month,5,2);
	$t_month = date("Ym",mktime(0,0,0,$mm,1,$yy));
}

$param = "";
$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;

/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$addsql = "";
//////////////////////////////////////////確認

//////////////////////////////////////////
$ybase->title = "店舗チェック-TOP";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("店舗チェック-TOP");

$ybase->ST_PRI .= <<<HTML


<div class="container">
<h4 style="text-align:center;">店舗チェック-TOP</h4>
<p></p>
<br>


HTML;

$linkable = "";
$auth_edit = 1;

if($auth_edit == 1){
$ybase->ST_PRI .= <<<HTML
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-danger btn-block{$linkable}" href="./check_in.php?$param" role="button">店舗チェック入力</a></div>
</div>
<br>
HTML;
}

$ybase->ST_PRI .= <<<HTML
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block{$linkable}" href="./vw_monthly.php?$param" role="button">入力状況確認
</a></div>
</div>
<br>
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block{$linkable}" href="./vw_shop.php?$param" role="button">店舗別推移</a></div>
</div>
<br>
<!-----
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block disabled" href="./.php?$param" role="button">全店舗推移</a></div>
</div>
<br>
----->
HTML;
if($auth_edit == 1){
$ybase->ST_PRI .= <<<HTML

<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-success btn-block{$linkable}" href="./set_top.php?{$param}&target_month=$t_month&target_shop_id=$t_shop_id" role="button">設定TOP</a></div>
</div>
<br>
HTML;
}

$ybase->ST_PRI .= <<<HTML

</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>