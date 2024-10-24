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
//if(preg_match("/^g_goal_num([0-9]+)$/",$var_name)){
//	print "OK";
//	exit;
//}

$var_val = trim($var_val);
$var_val = preg_replace('/　/', ' ', $var_val);
mb_language("Ja");
mb_internal_encoding("utf-8");
if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}
if(!preg_match("/^[0-9]+$/",$target_employee_id2)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10824");
}
if(!preg_match("/^[0-9]+$/",$target_item_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10825");
}
if(!$var_name){
	$ybase->error("パラメーターエラー。ERROR_CODE:10823");
}

if(preg_match("/^goal_num_([0-9]+)_([0-9]+)$/",$var_name,$str)){
	$colname = "goal_num";
	$ino1 = $str[1];
	$ino2 = $str[2];
}else{
	$colname = $var_name;
}


$conn = $ybase->connect();

$var_val = trim($var_val);
$var_val = addslashes($var_val);

$add_sql = " month = $target_month and shop_id = $target_shop_id and item_id = $target_item_id and employee_id = $target_employee_id2 and status = '1'";

switch ($colname){
	case "goal_num":
		$tablename = "telecom2_goal";
		$var_val = mb_convert_kana($var_val, "n");
		if($var_val == ''){
			$value = "null";
		}else{
			$value = "$var_val";
		}
		$sql = "select goal_num from $tablename where{$add_sql}";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$sql = "insert into $tablename (shop_id,item_id,month,employee_id,goal_num,add_date,status) values ($target_shop_id,$target_item_id,$target_month,$target_employee_id2,$value,'now','1')";
			$result = $ybase->sql($conn,$sql);
		}
		break;
}


$sql = "update $tablename set $colname = $value where{$add_sql}";
$result = $ybase->sql($conn,$sql);
if($result){
	print "OK";
}else{
	print "NG";
}

////////////////////////////////////////////////
?>