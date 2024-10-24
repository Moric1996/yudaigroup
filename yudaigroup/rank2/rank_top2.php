<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/rank.inc');
include('./inc/rank_list.inc');

require_once('../../TCPDF/tcpdf.php');
 
// TCPDFインスタンスを作成
$orientation = 'Landscape'; // 用紙の向き
$unit = 'mm'; // 単位
$format = 'A4'; // 用紙フォーマット
$unicode = true; // ドキュメントテキストがUnicodeの場合にTRUEとする
$encoding = 'UTF-8'; // 文字コード
$diskcache = false; // ディスクキャッシュを使うかどうか
$tcpdf = new TCPDF($orientation, $unit, $format, $unicode, $encoding, $diskcache);

$tcpdf->AddPage();
$tcpdf->SetFont("kozminproregular", "", 10);


$ybase = new ybase();
$rank = new rank();
$ybase->session_get();
if($rank_section_list[$ybase->my_section_id]){
	$my_rank_section = $rank_section_list[$ybase->my_section_id];
}else{
	$my_rank_section = 302;
}
if(!$t_shop_id){
	$t_shop_id = $my_rank_section;
}
if($t_shop_id == $my_rank_section){
	$auth_edit = 1;
}else{
	$auth_edit = 0;
}
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

if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}
$param = "t_month=$t_month&t_shop_id=$t_shop_id";

/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
$bcolor[0]="#ffdddd";
$bcolor[1]="#ddffdd";
$bcolor[2]="#ddddff";
$bcolor[3]="#ffddaa";
$bcolor[4]="#ffffdd";
$bcolor[5]="#ddffff";
//////////////////////////////////////////条件
$addsql = "month = $t_month and shop_id = $t_shop_id";
//////////////////////////////////////////確認
$this_month = date("Ym",mktime(0,0,0,$nowmm,$nowdd,$nowyy));
$leader_check_this = $rank->check_group_set($t_shop_id,$this_month,$ybase->my_employee_id);
$last_month = date("Ym",mktime(0,0,0,$nowmm - 1,$nowdd,$nowyy));
$leader_check_last = $rank->check_group_set($t_shop_id,$last_month,$ybase->my_employee_id);
$next_month = date("Ym",mktime(0,0,0,$nowmm + 1,$nowdd,$nowyy));
$leader_check_next = $rank->check_group_set($t_shop_id,$next_month,$ybase->my_employee_id);

//////////////////////////////////////////
$ybase->title = "Y☆Judge-TOP";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("Y☆Judge-TOP");

$ybase->ST_PRI .= <<<HTML
<style>
@media print {
    // 印刷したくない領域
    .hidden-print {
        display: none;
    }
    // 印刷したい領域
    .content-print{
        top:0 !IMPORTANT;
        left:0 !IMPORTANT;
        width:172mm !IMPORTANT;
        height:251mm !IMPORTANT;
    }
}
</style>
<script type="text/javascript">
$(function(){
	$('input[type="month"],select').change(function(){
		$("#Form1").submit();
	});
});

</script>
<div class="container">
<h4 style="text-align:center;">Y☆Judge-TOP</h4>
<p></p>
<button id="print_btn" type="button" onclick="window.print(); return false;">印刷</button>
<form action="./rank_top.php" method="post" id="Form1">

<div class="row">
<div class="col-sm-8 offset-sm-2" style="text-align:center;">
HTML;
$check = $rank->check_position($ybase->my_position_class);
if($check){
	$auth_edit = 1;
}

$ybase->ST_PRI .= <<<HTML
【対象店舗】<select name="t_shop_id">
HTML;
foreach($rank_section_name as $key => $val){
if($key == $t_shop_id){
	$selected = " selected";
}else{
	$selected = "";
}

$ybase->ST_PRI .= <<<HTML
<option value="$key"{$selected}>$val</option>
HTML;

}
$ybase->ST_PRI .= <<<HTML
</select>
HTML;

$ybase->ST_PRI .= <<<HTML
</div>
</div>
<p></p>

<div class="row">
<div class="col-sm-8 offset-sm-2" style="text-align:center;">
【対象年月】<input type="month" name="tar_month" value="$tar_month">
</div>
</div>
</form>
<p></p>
HTML;
if($leader_check_last){
$ybase->ST_PRI .= <<<HTML
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-danger btn-block" href="./set_personal_fm.php?target_month=$last_month&target_shop_id=$t_shop_id&target_group_id=$leader_check_last" role="button">※{$last_month}のチーム配分を設定※</a></div>
</div>
<br>
HTML;
}
if($leader_check_this){
$ybase->ST_PRI .= <<<HTML
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-danger btn-block" href="./set_personal_fm.php?target_month=$this_month&target_shop_id=$t_shop_id&target_group_id=$leader_check_this" role="button">※{$this_month}のチーム配分を設定※</a></div>
</div>
<br>
HTML;
}
if($leader_check_next){
$ybase->ST_PRI .= <<<HTML
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-danger btn-block" href="./set_personal_fm.php?target_month=$next_month&target_shop_id=$t_shop_id&target_group_id=$leader_check_next" role="button">※{$next_month}のチーム配分を設定※</a></div>
</div>
<br>
HTML;
}
if($auth_edit == 1){
$ybase->ST_PRI .= <<<HTML
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-danger btn-block" href="./person_in.php?$param" role="button">個別実績入力</a></div>
</div>
<br>
HTML;
}

$ybase->ST_PRI .= <<<HTML
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block" href="./totalday1.php?$param" role="button">日別集計表（全体・個別）
</a></div>
</div>
<br>
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block" href="./daily_achieve.php?$param" role="button">日別実績</a></div>
</div>
<br>
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block" href="./personal_achieve.php?$param" role="button">個別達成確認</a></div>
</div>
<br>
<!--
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block" href="./group_rank.php?$param" role="button">チーム実績</a></div>
</div>
<br>
-->
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block" href="./group_rank2.php?$param" role="button">チーム実績ランク順</a></div>
</div>
<br>
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block" href="./myp_rank.php?$param" role="button">日別MYP</a></div>
</div>
<br>
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block" href="./tunagu_reg.php?$param" role="button">TUNAG投稿用</a></div>
</div>
<br>
HTML;

if($auth_edit == 1){
$ybase->ST_PRI .= <<<HTML

<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-success btn-block" href="./set_top.php?{$param}&target_month=$t_month&target_shop_id=$t_shop_id" role="button">設定TOP</a></div>
</div>
<br>
HTML;
}


$ybase->ST_PRI .= <<<HTML

</div>
<p></p>


HTML;

$ybase->HTMLfooter();

/*
$tcpdf->writeHTML($ybase->ST_PRI);
$fileName = 'sample.pdf';
$tcpdf->Output("$fileName ");
$pdfData = $tcpdf->Output(rawurlencode($fileName), 'S');

// ブラウザにそのまま表示
header('Content-Type: application/pdf');
header("Content-Disposition: inline; filename*=UTF-8''".rawurlencode($fileName));
echo $pdfData;
*/


$ybase->priout();
////////////////////////////////////////////////
?>