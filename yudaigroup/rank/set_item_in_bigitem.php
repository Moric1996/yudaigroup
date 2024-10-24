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
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
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
$sql = "select order_num from telecom_bigitem where {$addsql} and status = '1' and order_num <> 999 order by order_num desc";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$last_order_num = 0;
}else{
	$last_order_num = pg_fetch_result($result,0,0);
}
$next_order_num = $last_order_num + 1;

$sql = "select nextval('telecom_bigitem_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:23006");
}
$new_bigitme_id = pg_fetch_result($result,0,0);
$sql = "insert into telecom_bigitem (bigitem_id,bigitem_name,shop_id,month,order_num,add_date,status) values ($new_bigitme_id,'',$target_shop_id,$target_month,$next_order_num,'now','1')";
$result = $ybase->sql($conn,$sql);

//////項目も追加
$sql = "select order_num from telecom_item where {$addsql} and bigitem_id = $new_bigitme_id and status = '1' order by order_num desc";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$last_order_num = 0;
}else{
	$last_order_num = pg_fetch_result($result,0,0);
}
$next_order_num = $last_order_num + 1;
$sql = "insert into telecom_item (item_id,item_name,shop_id,month,bigitem_id,score,order_num,add_date,status) values (nextval('telecom_item_id_seq'),'',$target_shop_id,$target_month,$new_bigitme_id,0,$next_order_num,'now','1')";
$result = $ybase->sql($conn,$sql);


header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/set_item_fm.php?{$param}&confirm_f=no");
exit;
////////////////////////////////////////////////
?>