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
if(!preg_match("/^[0-9]+$/",$t_group_const_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10823");
}

$yy=substr($target_month,0,4);
$mm=substr($target_month,4,2);
$maxday = date("t",mktime(0,0,0,$mm,1,$yy));

$param = "target_month=$target_month&target_shop_id=$target_shop_id";
/////////////////////////////////////////

$conn = $ybase->connect();

//////////////////////////////////////////条件
$addsql = "month = $target_month and shop_id = $target_shop_id";
/////////////////////////
$sql = "select employee_id from telecom_group_const where {$addsql} and group_const_id = $t_group_const_id and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$q_employee_id = pg_fetch_result($result,0,0);
	$q_employee_id =trim($q_employee_id);
	if($q_employee_id){
	$sql = "select employee_id from telecom_group_const where {$addsql} and group_const_id <> $t_group_const_id and status = '1' and employee_id = $q_employee_id";
	$result = $ybase->sql($conn,$sql);
	$num2 = pg_num_rows($result);
	if($num2){
		$ybase->error("同じ構成員が他のチームにも登録されていますので削除できません。");
	}
	$sql = "update telecom_action set status = '0' where {$addsql} and employee_id = $q_employee_id and status = '1'";
	$result = $ybase->sql($conn,$sql);

	$sql = "update telecom_goal set status = '0' where {$addsql} and employee_id = $q_employee_id and status = '1'";
	$result = $ybase->sql($conn,$sql);

	$sql = "update telecom_group set leader_employee_id = null where shop_id = $target_shop_id and leader_employee_id = $q_employee_id and status = '1'";
	$result = $ybase->sql($conn,$sql);
	}
	$sql = "update telecom_group_const set status = '0' where {$addsql} and group_const_id = $t_group_const_id and status = '1'";
	$result = $ybase->sql($conn,$sql);
}

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/set_team_fm.php?{$param}");
exit;
////////////////////////////////////////////////
?>