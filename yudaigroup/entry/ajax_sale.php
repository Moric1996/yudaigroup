<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
if(isset($_POST)){
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
}
if(isset($_GET)){
	foreach($_GET as $key => $value){
		${$key} = $value;
	}
}

include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();
/////////////////////////////////////////
$ybase->make_shop_list();
//print "var_name:{$var_name}<br>";
//print "var_val:{$var_val}<br>";
//print "shop_id:{$shop_id}<br>";
//print "colum:{$colum}<br>";
//print "day:{$day}<br>";
//print "selyymm:{$selyymm}<br>";

$var_val = trim($var_val);

if(!preg_match("/^[0-9]+$/",$shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:19121");
}
if(!preg_match("/^[A-Za-z0-9_]+$/",$colum)){
	$ybase->error("パラメーターエラー。ERROR_CODE:19122");
}
if(!preg_match("/^[0-9]+$/",$day)){
	$ybase->error("パラメーターエラー。ERROR_CODE:19123");
}
if(!preg_match("/^[0-9]{4}-[0-9]{2}$/",$selyymm)){
	$ybase->error("パラメーターエラー。ERROR_CODE:19124");
}
$sql_shop_id = $ybase->scraing_shop_list[$shop_id];
if(!preg_match("/^[0-9]{5}$/",$sql_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:19125");
}
$day = sprintf("%02d", $day);
$target_date = "$selyymm"."-"."$day"; 
if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/",$target_date)){
	$ybase->error("パラメーターエラー。ERROR_CODE:19126");
}
if(($colum == "work_time") && $var_val){
	if(!preg_match("/^[0-9]+\.?[0-9]{0,2}$/",$var_val)){
		$ybase->error("パラメーターエラー。ERROR_CODE:19127");
	}
}

if($var_val == ""){
	$value = "null";
}else{
	$value = $var_val;
}
$conn = $ybase->connect();

$sql = "select revenue from yudai_data_news where shop_id = '$sql_shop_id' and date = '$target_date'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

if(!$num){
	$sql = "insert into yudai_data_news (date,shop_id) values ('$target_date','$sql_shop_id')";
	$result = $ybase->sql($conn,$sql);
}

$sql = "update yudai_data_news set $colum = $value where shop_id = '$sql_shop_id' and date = '$target_date'";
$result = $ybase->sql($conn,$sql);

if($result){
	print "OK";
}else{
	print "NG";
}


////////////////////////////////////////////////
?>