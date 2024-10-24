<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

include('./inorder_list.inc');

if(!preg_match("/^[0-9]+$/",$t_order_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$now_st)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}
if(!preg_match("/^[0-9]+$/",$nex_st)){
	if(!preg_match("/^[0-9]+$/",$step_st)){
		$ybase->error("パラメーターエラー。ERROR_CODE:10823");
	}
}

$param = "t_order_id=$t_order_id";
/////////////////////////////////////////

$conn = $ybase->connect();

//////////////////////////////////////////条件
/////////////////////////
$nex_st = intval($nex_st);
$sql = "select status from order_main where order_id = $t_order_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	$ybase->error("データがありません");
}
	list($q_status) = pg_fetch_array($result,0);
if(preg_match("/^99([0-9]{2})$/",$q_status,$str)){
	$now_status = 99;
	$change_status = intval($str[1]);
}else{
	$now_status = $q_status;
}

if(($nex_st == 99)&&($now_status != 99)){
	$ch_st = "99".sprintf("%02d",$now_status);
	$sql = "update order_main set status = '$ch_st' where order_id = $t_order_id";
	$log_status = "99";
}elseif(($nex_st != 99) && ($now_status == 99) && ($change_status == $nex_st)){
	$sql = "update order_main set status = '$change_status' where order_id = $t_order_id";
	$log_status = $change_status;
}

if(preg_match("/^[0-9]+$/",$step_st)){
	$sql = "update order_main set status = '$step_st' where order_id = $t_order_id";
	$log_status = $step_st;
}
if($sql){
	$result = $ybase->sql($conn,$sql);
	$sql = "insert into order_main_log (order_main_log_id,order_id,change_emp_id,add_date,nex_status) values (nextval('order_main_log_id_seq'),$t_order_id,'{$ybase->my_employee_id}','now','{$log_status}')";
	$result = $ybase->sql($conn,$sql);
}


if($step_st == '0'){
	$jumpscpt = "order_list.php";
}else{
	$jumpscpt = "order_vw.php";
}

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$jumpscpt}?{$param}");
exit;
////////////////////////////////////////////////
?>