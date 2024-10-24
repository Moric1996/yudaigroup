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
$tablerate = $ybase->mbscale(5);

if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}
if(!preg_match("/^[0-9]+$/",$target_group_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10823");
}
if(!$jump_script){
	$jump_script = "set_top.php";
}
$yy=substr($target_month,0,4);
$mm=substr($target_month,4,2);

/////////////////////////////////////////

$conn = $ybase->connect();

$param = "target_month=$target_month&target_shop_id=$target_shop_id";
//////////////////////////////////////////条件
$addsql = "month = $target_month and shop_id = $target_shop_id";
//////////////////////////////////////////データ確認
$group_emp_lt = array();
$item_name_lt = array();
/////////////////////////グループ構成員リスト
$sql = "select group_const_id,employee_id from telecom_group_const where {$addsql} and group_id = $target_group_id and status = '1' order by group_const_id";
$result = $ybase->sql($conn,$sql);
$const_num = pg_num_rows($result);
for($i=0;$i<$const_num;$i++){
	list($q_group_const_id,$q_employee_id) = pg_fetch_array($result,$i);
	$group_emp_lt[$q_group_const_id] = $q_employee_id;
}
if(!$group_emp_lt){
	$group_emp_lt = array(0);
}
$emplist = implode(",", $group_emp_lt);

///////////////////////////////////////項目
$sql = "select item_id,item_name from telecom_item where {$addsql} and status = '1' order by item_id";

$result = $ybase->sql($conn,$sql);
$item_num = pg_num_rows($result);
for($i=0;$i<$item_num;$i++){
	list($q_item_id,$q_item_name) = pg_fetch_array($result,$i);
	$item_name_lt[$q_item_id] = $q_item_name;
}
////////////////////////////
$sql = "select item_id,employee_id,goal_num from telecom_goal where {$addsql} and employee_id in ($emplist) and status = '1' order by item_id,employee_id";
$result = $ybase->sql($conn,$sql);
$goal_num = pg_num_rows($result);
for($i=0;$i<$goal_num;$i++){
	list($q_item_id,$q_employee_id,$q_goal_num) = pg_fetch_array($result,$i);
	$q_goal_num = trim($q_goal_num);
	if($q_goal_num == ""){
		$sql2 = "update telecom_goal set goal_num = 0 where {$addsql} and status = '1' and item_id = $q_item_id and employee_id = $q_employee_id";
		$result2 = $ybase->sql($conn,$sql2);
		$q_goal_num = 0;
	}
	$goal_num_lt[$q_item_id][$q_employee_id] = $q_goal_num;
}
$kensu = $const_num * $item_num;
if($kensu != $goal_num){
foreach($group_emp_lt as $gkey => $gval){
	foreach($item_name_lt as $ikey => $ival){
		if(!preg_match("/^[0-9]+$/",$goal_num_lt[$ikey][$gval])){
			$sql = "insert into telecom_goal (shop_id,item_id,month,employee_id,goal_num,add_date,status) values ($target_shop_id,$ikey,$target_month,$gval,0,'now','1')";
			$result = $ybase->sql($conn,$sql);
		}
	}
}
}
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$jump_script}?{$param}");
exit;


////////////////////////////////////////////////
?>