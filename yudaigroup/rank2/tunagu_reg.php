<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/rank.inc');
include('./inc/rank_list.inc');

$ybase = new ybase();
$rank = new rank();
$ybase->session_get();
$tablerate = $ybase->mbscale(7);

if(!preg_match("/^[0-9]+$/",$t_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20001");
}
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20002");
}
$rank->unitname_make($t_shop_id,$t_month);
$target_month = $t_month;
$YEAR = substr($target_month,0,4);
$MONTH = intval(substr($target_month,4,2));

if(!$target_day){
	$nowday = date("j");
	$target_day = date("j",mktime(0,0,0,$MONTH,$nowday - 1,$YEAR));
	$target_month = date("Ym",mktime(0,0,0,$MONTH,$nowday - 1,$YEAR));
}else{
	$target_day = date("j",mktime(0,0,0,$MONTH,$target_day,$YEAR));
	$target_month = date("Ym",mktime(0,0,0,$MONTH,$target_day,$YEAR));
}
$YEAR = substr($target_month,0,4);
$MONTH = intval(substr($target_month,4,2));
$maxday = date("t",mktime(0,0,0,$MONTH,$target_day,$YEAR));
$nissin = round($target_day/$maxday*100);

$bf = $target_day - 1;
$nx = $target_day + 1;
if($bf < 1){
	$bf_disable=" disabled";
}else{
	$bf_disable="";
}
if($nx > $maxday){
	$nx_disable=" disabled";
}else{
	$nx_disable="";
}

$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;
foreach($sec_employee_list as $key => $val){
if(strlen($val) > 9){
	list($aa,$bb) = explode(" ",$val);
	$sec_employee_list[$key] = $aa;
}
}

/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}

$rankbgcolor[1]="#dab300";
$rankbgcolor[2]="#bec1c3";
$rankbgcolor[3]="#c08d5e";

//////////////////////////////////////////条件
$param = "t_month=$t_month&t_shop_id=$t_shop_id";
$addsql = "month = $target_month and shop_id = $t_shop_id and status = '1'";
//////////////////////////////////////////

///////////////////////////////////////

$ybase->title = "Y☆Rank-TUNAGU投稿用";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("TUNAGU投稿用");

$ybase->ST_PRI .= <<<HTML

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./rank_top.php?$param" role="button">Y☆RankTOPに戻る</a></div>
<p></p>
<h5 style="text-align:center;">【{$rank_section_name[$t_shop_id]} {$YEAR}年{$MONTH}月】TUNAGU投稿用</h5>

<p></p>
<b> {$target_day}日</b>
<span style="text-align:center;">
<a href="tunagu_reg.php?$param&target_day=$bf" class="btn btn-sm btn-outline-secondary{$bf_disable}">前日</a>
<a href="tunagu_reg.php?$param&target_day=$nx" class="btn btn-sm btn-outline-secondary{$nx_disable}">翌日</a>
</span>
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
  </thead>
  <tbody>
<tr><td>
<div>
【来客数】<input type="tel" name="visit_num" value="" id="visit_num" size="6">組←{$target_day}日の組数を入力してください
</div>
HTML;


//item名
$item_lt=array();
$sql = "select item_id,item_name from telecom2_item where {$addsql} order by order_num";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_item_name) = pg_fetch_array($result3,$i);
	$item_lt[$q_item_id] = $q_item_name;
}

//目標
$group_goal_lt=array();
$sql = "select item_id,sum(goal_num) from telecom2_goal_group where {$addsql} group by item_id order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_goal_num) = pg_fetch_array($result3,$i);
	$group_goal_lt[$q_item_id] = $q_goal_num;
}
//当日実績
for($i=0;$i<200;$i++){
	$action_day[$i]=0;
}
$sql = "select item_id,sum(action_num) from telecom2_action where {$addsql} and day = $target_day group by item_id order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_action_num) = pg_fetch_array($result3,$i);
	$action_day[$q_item_id] = $q_action_num;
}
//当日まで実績
for($i=0;$i<200;$i++){
	$action_to[$i]=0;
}
$sql = "select item_id,sum(action_num) from telecom2_action where {$addsql} and day <= $target_day group by item_id order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_action_num) = pg_fetch_array($result3,$i);
	$action_to[$q_item_id] = $q_action_num;
}
/////////////////////////////////////////////
$contents="1.総販(当日実績／月間累計/月間目標）
{$action_day[143]}台/{$action_to[143]}台/{$group_goal_lt[143]}台

2.HS新規(当日実績／月間累計/月間目標）
{$action_day[145]}台/{$action_to[145]}台/{$group_goal_lt[145]}台

3.MNP(当日実績／月間累計/月間目標）
{$action_day[146]}台/{$action_to[146]}台/{$group_goal_lt[146]}台

4.マイグレ(当日実績／月間累計/月間目標）
{$action_day[149]}台/{$action_to[149]}台/{$group_goal_lt[149]}台

5.店舗目標（当日／累計／月目標）
【 ドコモ光 】{$action_day[159]}件/{$action_to[159]}件/{$group_goal_lt[159]}件
【  SLS総販 】{$action_day[171]}件/{$action_to[171]}件/{$group_goal_lt[171]}件
【 DDX総販 】{$action_day[170]}件/{$action_to[170]}件/{$group_goal_lt[170]}件
【スマホ教室】{$action_day[181]}件/{$action_to[181]}件/{$group_goal_lt[181]}件

【 来客数  】";



$ybase->ST_PRI .= <<<HTML
<script type="text/javascript">
$(function($) {
	var str = $("#tunagu_r").text();
	$('body').data('orignaltxt',str);
	$('#visit_num').keyup(function() {
		var in_num  = $(this).val();
		var moto = $('body').data('orignaltxt');
		var cg_text = moto + in_num + '組';
		$("#tunagu_r").text(cg_text);
	});
});
</script>

<textarea name="tunagu_r" id="tunagu_r" cols="45" rows="20">
$contents</textarea>
<p></p>
<p></p>
</td></tr>
</tbody>
</table>
<p></p>
項目名が合致しているか確認してください。<br>
※項目の内容が違う場合はユアネット勝又までご連絡ください。
<div>
総販 → <b>{$item_lt[143]}</b>
</div>
<div>
HS新規 → <b>{$item_lt[145]}</b>
</div>
<div>
MNP → <b>{$item_lt[146]}</b>
</div>
<div>
マイグレ → <b>{$item_lt[149]}</b>
</div>
<div>
ドコモ光 → <b>{$item_lt[159]}</b>
</div>
<div>
SLS総販 → <b>{$item_lt[171]}</b>
</div>
<div>
DDX総販 → <b>{$item_lt[170]}</b>
</div>
<div>
スマホ教室 → <b>{$item_lt[181]}</b>
</div>


</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>