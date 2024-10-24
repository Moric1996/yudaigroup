<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}

$yy=substr($target_month,0,4);
$mm=substr($target_month,4,2);
$maxday = date("t",mktime(0,0,0,$mm,1,$yy));
if(!$jump_script){
	$jump_script = "set_top.php";
}

$param = "target_month=$target_month&target_shop_id=$target_shop_id";
/////////////////////////////////////////

$conn = $ybase->connect();

//////////////////////////////////////////条件
$addsql = "month = $target_month and shop_id = $target_shop_id";
/////////////////////////
$sql = "select employee_id from telecom2_group_const where {$addsql} and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$jump_script}?{$param}");
exit;
}
$all_employee_list = pg_fetch_all_columns($result);

$sql = "select employee_id from telecom2_action where {$addsql} and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$action_employee_list = pg_fetch_all_columns($result);
	$diff_arr = array_diff($action_employee_list, $all_employee_list);
	foreach($diff_arr as $key => $val){
		$sql = "update telecom2_action set status = '0' where {$addsql} and employee_id = $val and status = '1'";
		$result = $ybase->sql($conn,$sql);
	}
}

$sql = "select employee_id from telecom2_goal where {$addsql} and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$goal_employee_list = pg_fetch_all_columns($result);
	$diff_arr = array_diff($goal_employee_list, $all_employee_list);
	foreach($diff_arr as $key => $val){
		$sql = "update telecom2_goal set status = '0' where {$addsql} and employee_id = $val and status = '1'";
		$result = $ybase->sql($conn,$sql);
	}
}


header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$jump_script}?{$param}");
exit;
////////////////////////////////////////////////
?>