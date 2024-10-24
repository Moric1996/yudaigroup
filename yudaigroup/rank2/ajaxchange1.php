<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

////////////エラーチェック

//print "var_name:{$var_name}<br>";
//print "var_val:{$var_val}<br>";
//print "target_month:{$target_month}<br>";
//print "target_shop_id:{$target_shop_id}<br>";
//print "target_day:{$target_day}<br>";
//print "target_item_id:{$target_item_id}<br>";
//print "target_employee_id:{$target_employee_id}<br>";


$var_val = trim($var_val);
$var_val = preg_replace('/　/', ' ', $var_val);
mb_language("Ja");
mb_internal_encoding("utf-8");
$var_val = mb_convert_kana($var_val, "n");
if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}
if(!preg_match("/^[0-9]+$/",$target_day)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10823");
}
if(!preg_match("/^[0-9]+$/",$target_item_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10824");
}
if(!preg_match("/^[0-9]+$/",$target_employee_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10825");
}

$conn = $ybase->connect();
$var_val = addslashes($var_val);

$colname = "action_num";
if($var_val == ""){
	$value = "null";
}else{
	$value = $var_val;
}
$sql = "select action_num from telecom2_action where month = $target_month and shop_id = $target_shop_id and day = $target_day and employee_id = $target_employee_id and item_id = $target_item_id and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$sql = "insert into telecom2_action (shop_id,item_id,month,day,employee_id,action_num,add_date,status) values ($target_shop_id,$target_item_id,$target_month,$target_day,$target_employee_id,$value,'now','1')";
}else{
	$sql = "update telecom2_action set $colname = $value where month = $target_month and shop_id = $target_shop_id and day = $target_day and employee_id = $target_employee_id and item_id = $target_item_id and status = '1'";
}

$result = $ybase->sql($conn,$sql);
if($result){
	print "OK";
}else{
	print "NG";
}

////////////////////////////////////////////////
?>