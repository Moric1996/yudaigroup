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
if(!preg_match("/^[0-9]+$/",$t_group_id)){
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

$sql = "select group_const_id,employee_id from telecom2_group_const where {$addsql} and group_id = $t_group_id and status = '1'";
$result0 = $ybase->sql($conn,$sql);
$num0 = pg_num_rows($result0);
for($i=0;$i<$num0;$i++){
	list($q_group_const_id,$q_employee_id) = pg_fetch_array($result0,$i);
	$q_employee_id =trim($q_employee_id);
	if($q_employee_id){
	$sql = "select employee_id from telecom2_group_const where {$addsql} and group_const_id <> $q_group_const_id and status = '1' and employee_id = $q_employee_id";
	$result2 = $ybase->sql($conn,$sql);
	$num2 = pg_num_rows($result2);
	if($num2){
		$ybase->error("同じ構成員が他のチームにも登録されていますので削除できません。");
	}
	$sql = "update telecom2_action set status = '0' where {$addsql} and employee_id = $q_employee_id and status = '1'";
	$result = $ybase->sql($conn,$sql);

	$sql = "update telecom2_goal set status = '0' where {$addsql} and employee_id = $q_employee_id and status = '1'";
	$result = $ybase->sql($conn,$sql);

	$sql = "update telecom2_group set leader_employee_id = null where shop_id = $target_shop_id and leader_employee_id = $q_employee_id and status = '1'";
	$result = $ybase->sql($conn,$sql);
	}
}
$sql = "update telecom2_group_const set status = '0' where {$addsql} and group_id = $t_group_id and status = '1'";
$result = $ybase->sql($conn,$sql);

$sql = "update telecom2_goal_group set status = '0' where {$addsql} and group_id = $t_group_id and status = '1'";
$result = $ybase->sql($conn,$sql);

$sql = "update telecom2_group set status = '0' where {$addsql} and group_id = $t_group_id and status = '1'";
$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/set_team_fm.php?{$param}");
exit;
////////////////////////////////////////////////
?>