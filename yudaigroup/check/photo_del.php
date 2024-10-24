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
$param = "t_shop_id=$t_shop_id&target_ckaction_id=$target_ckaction_id";
//////////////////////////////////////////
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー");
}
if(!preg_match("/^[0-9]+$/",$t_ckaction_list_id)){
	$ybase->error("パラメーターエラー");
}
if(!preg_match("/^[0-9]+$/",$pno)){
	$ybase->error("パラメーターエラー");
}
if(!$backscript){
	$backscript = "check_in.php";
}elseif($backscript == "photo_view.php"){
	$param = "t_shop_id={$t_shop_id}&t_ckaction_list_id={$t_ckaction_list_id}&pno=0&delflag=1";
}
$uploaddir = "/home/yournet/yudai/check/"."$t_shop_id"."/";

///////////////////////////////データ取得
$sql = "select photo from ck_check_action_list where ckaction_list_id = $t_ckaction_list_id and section_id = '$t_shop_id' and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データエラー");
}
list($q_photo) = pg_fetch_array($result,0);
$q_photo = trim($q_photo);
if($q_photo){
	$photo_arr = json_decode($q_photo,true);
	$filename = $photo_arr[$pno];
	unset($photo_arr[$pno]);
	array_values($photo_arr);
}else{
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$backscript}?{$param}#li{$t_ckaction_list_id}");
exit;
}

$photo_json=json_encode($photo_arr);

$sql = "update ck_check_action_list set photo = '$photo_json' where ckaction_list_id = $t_ckaction_list_id and section_id = '$t_shop_id'";
$result = $ybase->sql($conn,$sql);
unlink($filename);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$backscript}?{$param}#li{$t_ckaction_list_id}");
exit;

////////////////////////////////////////////////
?>