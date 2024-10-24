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
if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}
if(!$var_name){
	$ybase->error("パラメーターエラー。ERROR_CODE:10823");
}


if(preg_match("/^([a-zA-Z_]+)([0-9]+)$/",$var_name,$str)){
	$colname = $str[1];
	$ino = $str[2];
}else{
	$colname = $var_name;
}


$conn = $ybase->connect();

$var_val = trim($var_val);
$var_val = addslashes($var_val);

switch ($colname){
	case "group_name":
		$tablename = "telecom2_group";
		$ino_colname = "group_id";
		$value = "'$var_val'";
		break;
	case "leader_employee_id":
		if($var_val){
		$sql = "select employee_id from telecom2_group_const where month = $target_month and shop_id = $target_shop_id and group_id = $ino and status = '1' and group_const_id = $var_val";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if($num){
		$q_employee_id = pg_fetch_result($result,0,0);
		$q_employee_id = trim($q_employee_id);
		}
		}
		$tablename = "telecom2_group";
		$ino_colname = "group_id";
		$var_val = mb_convert_kana($var_val, "n");
		if($q_employee_id == ''){
			$value = "null";
		}else{
			$value = "$q_employee_id";
		}
		break;
	case "allot":
		$tablename = "telecom2_group";
		$ino_colname = "group_id";
		$var_val = mb_convert_kana($var_val, "n");
		if($var_val == ''){
			$value = "null";
		}else{
			$value = "$var_val";
		}
		break;
	case "employee_id":
		$sql = "select employee_id from telecom2_group_const where month = $target_month and shop_id = $target_shop_id and group_const_id = $ino and status = '1'";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if($num == 1){
			$pre_employee_id = pg_fetch_result($result,0,0);
			$pre_employee_id = trim($pre_employee_id);
			if($var_val && $target_group_id && $pre_employee_id){
				$sql = "update telecom2_group set leader_employee_id = $var_val where month = $target_month and shop_id = $target_shop_id and leader_employee_id = $pre_employee_id and group_id = $target_group_id and status = '1'";
				$result = $ybase->sql($conn,$sql);
			}
		}
		$tablename = "telecom2_group_const";
		$ino_colname = "group_const_id";
		$var_val = mb_convert_kana($var_val, "n");
		if($var_val == ''){
			$value = "null";
		}else{
			$value = "$var_val";
		}
		break;
	case "short_name":
		$tablename = "telecom2_group_const";
		$ino_colname = "group_const_id";
		$value = "'$var_val'";
		break;
}


$sql = "update $tablename set $colname = $value where month = $target_month and shop_id = $target_shop_id and $ino_colname = $ino and status = '1'";
$result = $ybase->sql($conn,$sql);
if($result){
	print "OK";
}else{
	print "NG";
}

////////////////////////////////////////////////
?>