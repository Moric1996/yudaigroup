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
if(!$target_day){
	$nowday = date("j");
	$target_day = date("j",mktime(0,0,0,$MONTH,$nowday - 1,$YEAR));
	if($target_day < 1){
		$target_day = 1;
	}
}
$YEAR = substr($t_month,0,4);
$MONTH = intval(substr($t_month,4,2));
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

//////////////////////////////////////////条件
$param = "t_month=$t_month&t_shop_id=$t_shop_id";
$addsql = "month = $t_month and shop_id = $t_shop_id and status = '1'";
//////////////////////////////////////////
/////////////////////////グループリスト
$sql = "select group_id,group_name,allot from telecom2_group where {$addsql} and status = '1' order by group_id";
$result = $ybase->sql($conn,$sql);
$group_num = pg_num_rows($result);
if(!$group_num){
	$ybase->error("チームの設定がされていません。先にチームの設定をしてください。");
}
for($i=0;$i<$group_num;$i++){
	list($q_group_id,$q_group_name,$q_allot) = pg_fetch_array($result,$i);
	$group_name_lt[$q_group_id] = $q_group_name;
}
/////////////////////////グループ構成員リスト
$sql = "select group_const_id,group_id,employee_id from telecom2_group_const where {$addsql} and status = '1' order by group_id,group_const_id";
$result = $ybase->sql($conn,$sql);
$const_num = pg_num_rows($result);
if(!$const_num){
	$ybase->error("チームの設定がされていません。先にチームの設定をしてください。");
}
$group_cnt = array();
for($i=0;$i<$const_num;$i++){
	list($q_group_const_id,$q_group_id,$q_employee_id) = pg_fetch_array($result,$i);
	$group_emp_lt[$q_employee_id] = $q_group_id;
	$group_cnt[$q_group_id]++;
}
/////////////////////////配点リスト
$sql = "select item_id,score from telecom2_item where {$addsql} order by item_id";

$result = $ybase->sql($conn,$sql);
$item_num = pg_num_rows($result);
if(!$item_num){
	$ybase->error("項目の設定がされていません。先に項目の設定をしてください。");
}
for($i=0;$i<$item_num;$i++){
	list($q_item_id,$q_score) = pg_fetch_array($result,$i);
	$item_score_lt[$q_item_id] = $q_score;
}

///////////////////////////////////////

$ybase->title = "Y☆Judge-チーム実績(個別)";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("チーム実績(個別)");

$ybase->ST_PRI .= <<<HTML

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./rank_top.php?$param" role="button">Y☆JudgeTOPに戻る</a></div>
<p></p>
<h5 style="text-align:center;">【{$rank_section_name[$t_shop_id]} {$YEAR}年{$MONTH}月】日別ＭＹＰ</h5>

<p></p>
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
  </thead>
  <tbody>
HTML;
//目標
$group_goal_lt=array();
$sql = "select item_id,employee_id,goal_num from telecom2_goal where {$addsql} order by item_id,employee_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_employee_id,$q_goal_num) = pg_fetch_array($result3,$i);
	$group_goal_lt[$q_employee_id] += $q_goal_num * $item_score_lt[$q_item_id];
}
//当日実績
$group_action_lt=array();
$gokei_action_lt=array();
$sql = "select day,item_id,employee_id,action_num from telecom2_action where {$addsql} order by day,employee_id,item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_day,$q_itemid,$q_employee_id,$q_action_num) = pg_fetch_array($result3,$i);
	$group_action_lt[$q_day][$q_employee_id] += $q_action_num * $item_score_lt[$q_itemid];
	$gokei_action_lt[$q_employee_id] += $q_action_num * $item_score_lt[$q_itemid];
}
/////////////////////////////////////////////
arsort($gokei_action_lt);
$no=1;
$basescore="0";
$repeat = 1;
foreach($gokei_action_lt as $key => $val){
	if($basescore == $val){
		$rankno = $no - $repeat;
		$repeat++;
	}else{
		$rankno = $no;
		$repeat=1;
	}
	$basescore = $val;
	$no++;
	$gokei_action_rank[$key] = $rankno;
}
for($i=1;$i<=$maxday;$i++){
	$action_day = $group_action_lt[$i];
	if($action_day){
	arsort($action_day);
	}else{
		$action_day = array();
	}
	$no=1;
	$basescore="0";
	$repeat = 1;
	$group_action_rank=array();
	foreach($action_day as $key => $val){
		if($basescore == $val){
			$rankno = $no - $repeat;
			$repeat++;
		}else{
			$rankno = $no;
			$repeat=1;
		}
		$basescore = $val;
		$no++;
		$group_action_rank[$key] = $rankno;
}

$ybase->ST_PRI .= <<<HTML
<tr align="center">
<td bgcolor="#ff6666">{$i}日</td>
HTML;
$nn=0;
$baseno=0;
foreach($group_emp_lt as $key => $val){
if($baseno != $val){
	$nn++;
	$baseno = $val;
}
$ybase->ST_PRI .= <<<HTML
<td bgcolor="{$group_bgcolor[$nn]}">{$sec_employee_list[$key]}</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
<tr align="center">
<td bgcolor="#ffcccc">PT</td>
HTML;
foreach($group_emp_lt as $key => $val){
if(!$group_action_lt[$i][$key]){
	$aaa = "";
}else{
	$aaa = $group_action_lt[$i][$key];
}
$ybase->ST_PRI .= <<<HTML
<td>$aaa</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
<tr align="center">
<td bgcolor="#ffaaaa">順位</td>
HTML;
foreach($group_emp_lt as $key => $val){
$aaa = $group_action_rank[$key];
if(($aaa > 0)&&($aaa < 4)){
$ybase->ST_PRI .= <<<HTML
<td bgcolor="#ffeef6"><img src="./image/oukan{$aaa}.png" height="18" border="0"></td>
HTML;

}else{
$ybase->ST_PRI .= <<<HTML
<td bgcolor="{$rankbgcolor[$aaa]}">{$aaa}位</td>
HTML;
}
}
$ybase->ST_PRI .= <<<HTML
</tr>
HTML;
}

$ybase->ST_PRI .= <<<HTML
<tr align="center">
<td bgcolor="#ff3333">合計</td>
HTML;
foreach($group_emp_lt as $key => $val){
$ybase->ST_PRI .= <<<HTML
<td>{$sec_employee_list[$key]}</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
<tr align="center">
<td bgcolor="#ffaaaa">ポイント</td>
HTML;
foreach($group_emp_lt as $key => $val){
$ybase->ST_PRI .= <<<HTML
<td>{$gokei_action_lt[$key]}</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
<tr align="center">
<td bgcolor="#ff5555">順位</td>
HTML;
foreach($group_emp_lt as $key => $val){
$aaa = $gokei_action_rank[$key];
if(($aaa > 0)&&($aaa < 4)){
$ybase->ST_PRI .= <<<HTML
<td bgcolor="#ffeef6"><img src="./image/oukan{$aaa}.png" height="18" border="0"></td>
HTML;

}else{

$ybase->ST_PRI .= <<<HTML
<td bgcolor="{$rankbgcolor[$aaa]}">{$aaa}位</td>
HTML;
}
}
$ybase->ST_PRI .= <<<HTML
</tr>

 </tbody>
</table>
</div>
<p></p>

</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>