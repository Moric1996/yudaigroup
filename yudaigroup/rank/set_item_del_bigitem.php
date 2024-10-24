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
if(!preg_match("/^[0-9]+$/",$t_bigitem_id)){
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
$sql = "select item_id from telecom_item where {$addsql} and bigitem_id = $t_bigitem_id and status = '1' order by item_id";
$result0 = $ybase->sql($conn,$sql);
$num = pg_num_rows($result0);
for($i=0;$i<$num;$i++){
	list($q_item_id) = pg_fetch_array($result0,$i);
$sql = "update telecom_item set status = '0' where {$addsql} and item_id = $q_item_id and status = '1'";
$result = $ybase->sql($conn,$sql);

$sql = "update telecom_action set status = '0' where {$addsql} and item_id = $q_item_id and status = '1'";
$result = $ybase->sql($conn,$sql);

$sql = "update telecom_goal_day set status = '0' where {$addsql} and item_id = $q_item_id and status = '1'";
$result = $ybase->sql($conn,$sql);

$sql = "update telecom_goal set status = '0' where {$addsql} and item_id = $q_item_id and status = '1'";
$result = $ybase->sql($conn,$sql);

$sql = "update telecom_goal_group set status = '0' where {$addsql} and item_id = $q_item_id and status = '1'";
$result = $ybase->sql($conn,$sql);

$sql = "update telecom_unitname set status = '0' where {$addsql} and item_id = $q_item_id and status = '1'";
$result = $ybase->sql($conn,$sql);

}
$sql = "update telecom_bigitem set status = '0' where {$addsql} and bigitem_id = $t_bigitem_id and status = '1'";
$result = $ybase->sql($conn,$sql);


header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/set_item_fm.php?{$param}&confirm_f=no");
exit;
////////////////////////////////////////////////
?>