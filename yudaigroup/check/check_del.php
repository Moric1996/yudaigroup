<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/check.inc');
include('./inc/check_list.inc');

$ybase = new ybase();
$check = new check();
$ybase->session_get();

$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;

$category_list = $check->category_make();
$item_list = $check->item_make();

$conn = $ybase->connect();
$param = "";
//////////////////////////////////////////
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー");
}
if(!preg_match("/^[0-9]+$/",$target_ckaction_id)){
	$ybase->error("パラメーターエラー");
}

if(!$backscript){
	$backscript = "check_in.php";
}

///////////////////////////////データ取得
$sql = "update ck_check_action_list set status = '0' where ckaction_id = $target_ckaction_id and section_id = '$t_shop_id'";
$result = $ybase->sql($conn,$sql);

$sql = "update ck_check_action set status = '0' where ckaction_id = $target_ckaction_id and section_id = '$t_shop_id'";
$result = $ybase->sql($conn,$sql);


header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$backscript}");
exit;

////////////////////////////////////////////////
?>