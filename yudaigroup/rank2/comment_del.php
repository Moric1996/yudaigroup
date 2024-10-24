<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

if(!preg_match("/^[0-9]+$/",$t_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20001");
}
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20002");
}
if(!preg_match("/^[0-9]+$/",$t_comment_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20003");
}
if(!preg_match("/^[0-9]+$/",$sec_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20004");
}



$param = "t_month=$t_month&t_shop_id=$t_shop_id&target_num=$target_num&sec_id=$sec_id";
/////////////////////////////////////////
$comment = trim($comment);

$conn = $ybase->connect();

//////////////////////////////////////////条件
$addsql = "shop_id = $sec_id";
/////////////////////////

$sql = "update  telecom2_comment set status = '0' where comment_id = $t_comment_id";
$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/comment_reg.php?{$param}");
exit;
////////////////////////////////////////////////
?>