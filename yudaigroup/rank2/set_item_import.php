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
$ybase->session_get();

if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10891");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10892");
}
if(!preg_match("/^[0-9]{4}\-[0-9]{2}$/",$import_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10893");
}
if(!preg_match("/^[0-9]+$/",$import_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10894");
}
$imyy=substr($import_month,0,4);
$immm=substr($import_month,5,2);

$import_month2 = "$imyy"."$immm";

$yy=substr($target_month,0,4);
$mm=substr($target_month,4,2);
$maxday = date("t",mktime(0,0,0,$mm,1,$yy));

$param = "target_month=$target_month&target_shop_id=$target_shop_id";
/////////////////////////////////////////

$conn = $ybase->connect();


//////////////////////////////////////////条件
$addsql = "month = $target_month and shop_id = $target_shop_id and status = '1'";
$motoaddsql = "month = $import_month2 and shop_id = $import_shop_id and status = '1'";
/////////////////////////
$sql = "select item_id from telecom2_item where {$motoaddsql}";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当するデータがありません");
}
$sql = "update telecom2_bigitem set status = '0' where {$addsql}";
$result = $ybase->sql($conn,$sql);

$sql = "update telecom2_item set status = '0' where {$addsql}";
$result = $ybase->sql($conn,$sql);

$sql = "update telecom2_action set status = '0' where {$addsql}";
$result = $ybase->sql($conn,$sql);

$sql = "update telecom2_goal_day set status = '0' where {$addsql}";
$result = $ybase->sql($conn,$sql);

$sql = "update telecom2_goal set status = '0' where {$addsql}";
$result = $ybase->sql($conn,$sql);

$sql = "update telecom2_goal_group set status = '0' where {$addsql}";
$result = $ybase->sql($conn,$sql);

$sql = "update telecom2_unitname set status = '0' where {$addsql}";
$result = $ybase->sql($conn,$sql);

	$sql = "insert into telecom2_bigitem (bigitem_id,bigitem_name,shop_id,month,order_num,add_date,status) select bigitem_id,bigitem_name,$target_shop_id,$target_month,order_num,'now',status from telecom2_bigitem where {$motoaddsql} order by bigitem_id";
	$result = $ybase->sql($conn,$sql);

	$sql = "insert into telecom2_item (item_id,item_name,shop_id,month,bigitem_id,score,order_num,add_date,status) select item_id,item_name,$target_shop_id,$target_month,bigitem_id,score,order_num,'now',status from telecom2_item where {$motoaddsql} order by item_id";
	$result = $ybase->sql($conn,$sql);

	$sql = "insert into telecom2_unitname (shop_id,month,item_id,u_name,add_date,status) select $target_shop_id,$target_month,item_id,u_name,'now',status from telecom2_unitname where {$motoaddsql} order by item_id";
	$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/set_item_fm.php?{$param}&confirm_f=no");
exit;
////////////////////////////////////////////////
?>