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
$rank = new rank();
$ybase->session_get();

if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}
if(!preg_match("/^[0-9]+$/",$bigitem_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10823");
}
if(!$updn){
	$ybase->error("パラメーターエラー。ERROR_CODE:10824");
}
if(!$jump_script){
	$jump_script = "set_item_fm.php";
}
$yy=substr($target_month,0,4);
$mm=substr($target_month,4,2);

/////////////////////////////////////////

$conn = $ybase->connect();

$param = "target_month=$target_month&target_shop_id=$target_shop_id";
//////////////////////////////////////////条件
$addsql = "month = $target_month and shop_id = $target_shop_id";
//////////////////////////////////////////データ確認

///////////////////////////////////////項目
$sql = "select bigitem_id,order_num from telecom_bigitem where {$addsql} and bigitem_id = $bigitem_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	$ybase->error("データエラー。ERROR_CODE:15824");
}
list($q_bigitem_id,$q_order_num) = pg_fetch_array($result,0);
////////////////////////////
if($updn == "up"){
$sql = "select bigitem_id,order_num from telecom_bigitem where {$addsql} and order_num < $q_order_num and status = '1' and bigitem_id <> $bigitem_id order by order_num desc";
}elseif($updn == "dn"){
$sql = "select bigitem_id,order_num from telecom_bigitem where {$addsql} and order_num > $q_order_num and status = '1' and bigitem_id <> $bigitem_id order by order_num";
}else{
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$jump_script}?{$param}");
exit;
}
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$jump_script}?{$param}");
exit;
}

list($qcg_item_id,$qcg_order_num) = pg_fetch_array($result,0);

if(!$qcg_item_id){
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$jump_script}?{$param}");
exit;
}

$sql = "update telecom_bigitem set order_num = $q_order_num where {$addsql} and bigitem_id = $qcg_item_id";
$result = $ybase->sql($conn,$sql);

$sql = "update telecom_bigitem set order_num = $qcg_order_num where {$addsql} and bigitem_id = $bigitem_id";
$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$jump_script}?{$param}&confirm_f=no");
exit;


////////////////////////////////////////////////
?>