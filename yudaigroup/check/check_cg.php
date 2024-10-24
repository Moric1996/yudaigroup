<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

if(!preg_match("/^[0-9]+$/",$target_ckaction_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20011");
}
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20012");
}
if(!preg_match("/^[0-9]+$/",$stat)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20013");
}

$param = "t_shop_id=$t_shop_id&target_ckaction_id=$target_ckaction_id";
/////////////////////////////////////////

$conn = $ybase->connect();

//////////////////////////////////////////条件
$addsql = "ckaction_id = $target_ckaction_id and section_id = '$t_shop_id'";
/////////////////////////

$sql = "update ck_check_action set status = '$stat' where $addsql";
$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/check_in.php?{$param}");
exit;
////////////////////////////////////////////////
?>