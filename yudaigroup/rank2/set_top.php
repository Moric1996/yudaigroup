<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/rank_list.inc');

$ybase = new ybase();
$ybase->session_get();
if($target_month){
	$tar_month = substr($target_month,0,4)."-".substr($target_month,4,2);
}
if(!$tar_month){
	$nowyy =date("Y");
	$nowmm =date("m");
	$nowdd =date("d");
	$target_month = date("Ym",mktime(0,0,0,$nowmm + 1,$nowdd,$nowyy));
	$tar_month = date("Y-m",mktime(0,0,0,$nowmm + 1,$nowdd,$nowyy));
	$yy = substr($tar_month,0,4);
	$mm = substr($tar_month,5,2);
}else{
	$yy = substr($tar_month,0,4);
	$mm = substr($tar_month,5,2);
	$target_month = date("Ym",mktime(0,0,0,$mm,1,$yy));
}

if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}
if(!$t_shop_id){
	$t_shop_id = $target_shop_id;
}
$param = "target_month=$target_month&target_shop_id=$target_shop_id";
$param0 = "t_month=$t_month&t_shop_id=$t_shop_id";

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
$addsql = "month = $target_month and shop_id = $target_shop_id";
//////////////////////////////////////////項目確認
$sql = "select item_id from telecom2_item where {$addsql} and status = '1' and item_name <> '' and score is not null order by item_id";

$result = $ybase->sql($conn,$sql);
$item_num = pg_num_rows($result);
if(!$item_num){
	$item_set_notice = "<span style=\"color:red;\">未設定です。設定してください。</span>";
	$item_set_color = "danger";
}else{
	$item_set_notice = "設定済みです。変更ができます。";
	$item_set_color = "alert alert-secondary";
}

//////////////////////////////////////////グループ確認
$sql = "select group_id,group_name from telecom2_group where {$addsql} and status = '1' and group_name <> '' and leader_employee_id is not null order by group_id";
$result = $ybase->sql($conn,$sql);
$group_num = pg_num_rows($result);
$group_flag=1;
if(!$group_num){
	$group_flag=0;
}
$sql = "select group_id,count(*) from telecom2_group_const where {$addsql} and status = '1' and employee_id is not null group by group_id order by group_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($group_num != $num){
	$group_flag=0;
}
for($i=0;$i<$num;$i++){
	list($q_group_id,$q_group_cnt) = pg_fetch_array($result,$i);
	if(!$q_group_cnt){
		$group_flag=0;
	}
}
$sql = "select group_id from telecom2_group_const where {$addsql} and status = '1' and employee_id is null order by group_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$group_flag=0;
}
if(!$group_flag){
	$group_set_notice = "<span style=\"color:red;\">未設定です。設定してください。</span>";
	$group_set_color = "danger";
}else{
	$group_set_notice = "設定済みです。変更ができます。";
	$group_set_color = "alert alert-secondary";
}

//////////////////////////////////////////月間目標・グループ配分確認
$sql = "select item_id,group_id,goal_num from telecom2_goal_group where {$addsql} and status = '1' order by item_id,group_id";

$result = $ybase->sql($conn,$sql);
$group_goal_num = pg_num_rows($result);
$check_group_goal = $item_num * $group_num;
//////////////////// 不整合データの削除
if($group_goal_num > $check_group_goal){
	$sql = "update telecom2_goal_group set status = '0' where {$addsql} and status = '1' and item_id not in (select item_id from telecom2_item where {$addsql} and status = '1')";
	$result = $ybase->sql($conn,$sql);
	$sql = "update telecom2_goal_group set status = '0' where {$addsql} and status = '1' and group_id not in (select group_id from telecom2_group where {$addsql} and status = '1')";
	$result = $ybase->sql($conn,$sql);
	$sql = "select item_id,group_id,goal_num from telecom2_goal_group where {$addsql} and status = '1' order by item_id,group_id";
	$result = $ybase->sql($conn,$sql);
	$group_goal_num = pg_num_rows($result);
}
////////////////////
if($group_goal_num && ($group_goal_num == $check_group_goal)){
	$goal_group_set_notice = "設定済みです。変更ができます。";
	$goal_group_set_color = "alert alert-secondary";
}else{
	$goal_group_set_notice = "<span style=\"color:red;\">未設定です。設定してください。</span>";
	$goal_group_set_color = "danger";
}
//////////////////////////////////////////チーム個別配分確認
$sql = "select employee_id from telecom2_group_const where {$addsql} and status = '1' order by group_const_id";
$result = $ybase->sql($conn,$sql);
$emp_num = pg_num_rows($result);
$check_emp_goal = $item_num * $emp_num;

$sql = "select item_id,employee_id,goal_num from telecom2_goal where {$addsql} and status = '1' order by item_id,employee_id";
$result = $ybase->sql($conn,$sql);
$emp_goal_num = pg_num_rows($result);
////////////////////不整合データの削除
if($emp_goal_num > $check_emp_goal){
	$sql = "update telecom2_goal set status = '0' where {$addsql} and status = '1' and item_id not in (select item_id from telecom2_item where {$addsql} and status = '1')";
	$result = $ybase->sql($conn,$sql);
	$sql = "update telecom2_goal set status = '0' where {$addsql} and status = '1' and employee_id not in (select employee_id from telecom2_group_const where {$addsql} and status = '1')";
	$result = $ybase->sql($conn,$sql);
	$sql = "select item_id,employee_id,goal_num from telecom2_goal where {$addsql} and status = '1' order by item_id,employee_id";
	$result = $ybase->sql($conn,$sql);
	$emp_goal_num = pg_num_rows($result);
}
////////////////////
if($emp_goal_num && ($emp_goal_num == $check_emp_goal)){
	$goal_emp_set_notice = "設定済みです。変更ができます。";
	$goal_emp_set_color = "alert alert-secondary";
}else{
	$goal_emp_set_notice = "<span style=\"color:red;\">未設定です。設定してください。</span>";
	$goal_emp_set_color = "danger";
}
//////////////////////////////////////////日別目標確認
$yy=substr($target_month,0,4);
$mm=substr($target_month,4,2);
$maxday = date("t",mktime(0,0,0,$mm,1,$yy));
$sql = "select item_id,day,dgoal_num from telecom2_goal_day where {$addsql} and status = '1' order by item_id,day";
$result = $ybase->sql($conn,$sql);
$day_num = pg_num_rows($result);
$check_day_goal = $item_num * $maxday;
////////////////////不整合データの削除
if($day_num > $check_day_goal){
	$sql = "update telecom2_goal_day set status = '0' where {$addsql} and status = '1' and item_id not in (select item_id from telecom2_item where {$addsql} and status = '1')";
	$result = $ybase->sql($conn,$sql);
	$sql = "select item_id,day,dgoal_num from telecom2_goal_day where {$addsql} and status = '1' order by item_id,day";
	$result = $ybase->sql($conn,$sql);
	$day_num = pg_num_rows($result);
}
////////////////////

if($day_num && ($day_num == $check_day_goal)){
	$goal_day_set_notice = "設定済みです。変更ができます。";
	$goal_day_set_color = "alert alert-secondary";
}else{
	$goal_day_set_notice = "<span style=\"color:red;\">未設定です。設定してください。</span>";
	$goal_day_set_color = "danger";
}

//////////////////////////////////////////
$ybase->title = "Y☆Judge-設定";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("設定TOP");

$ybase->ST_PRI .= <<<HTML
<script type="text/javascript">
$(function(){
	$('input[type="month"]').change(function(){
		$("#Form1").submit();
	});
});

</script>

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./rank_top.php?$param0" role="button">Y☆JudgeTOPに戻る</a></div>
<h5 style="text-align:center;">{$rank_section_name[$target_shop_id]} 設定TOP</h5>
<form action="./set_top.php" method="post" id="Form1">
【対象年月】
<input type="month" name="tar_month" value="$tar_month">
<input type="hidden" name="target_shop_id" value="$target_shop_id">
</form>
<p></p>
{$yy}年{$mm}月の設定をします<br><br>
<div class="row">
<div class="col-sm"><a class="btn btn-{$item_set_color} btn-block" href="./set_item_fm.php?$param" role="button">項目設定</a></div>
<div class="col-sm-1">→</div>
<div class="col-sm">$item_set_notice</div>
</div>
<br>
<div class="row">
<div class="col-sm"><a class="btn btn-{$group_set_color} btn-block" href="./set_team_fm.php?$param" role="button">チーム設定</a></div>
<div class="col-sm-1">→</div>
<div class="col-sm">$group_set_notice</div>
</div>
<br>
<div class="row">
<div class="col-sm"><a class="btn btn-{$goal_group_set_color} btn-block" href="./set_teamallot_fm.php?$param" role="button">月間目標・チーム配分設定</a></div>
<div class="col-sm-1">→</div>
<div class="col-sm">$goal_group_set_notice</div>
</div>
<br>
<div class="row">
<div class="col-sm"><a class="btn btn-{$goal_emp_set_color} btn-block" href="./set_person_top.php?$param" role="button">個別配分設定</a></div>
<div class="col-sm-1">→</div>
<div class="col-sm">$goal_emp_set_notice</div>
</div>
<br>
<div class="row">
<div class="col-sm"><a class="btn btn-{$goal_day_set_color} btn-block" href="./set_daygoal_fm.php?$param" role="button">日別目標設定</a></div>
<div class="col-sm-1">→</div>
<div class="col-sm">$goal_day_set_notice</div>
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