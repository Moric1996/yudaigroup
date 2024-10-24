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
//print "target_check:{$target_check}<br>";


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
	case "bigitem_name":
		$tablename = "telecom_bigitem";
		$ino_colname = "bigitem_id";
		$value = "'$var_val'";
		break;
	case "item_name":
		$tablename = "telecom_item";
		$ino_colname = "item_id";
		$value = "'$var_val'";
		break;
	case "noinput":
		$tablename = "telecom_item";
		$ino_colname = "item_id";
		if($target_check == "true"){
			$value = "'1'";
		}else{
			$value = "''";
		}
		break;
	case "score":
		$tablename = "telecom_item";
		$ino_colname = "item_id";
		$var_val = mb_convert_kana($var_val, "n");
		if($var_val == ''){
			$value = "null";
		}else{
			$value = "$var_val";
		}
		break;
	case "u_name":
		$tablename = "telecom_unitname";
		$ino_colname = "item_id";
		if($var_val == '件'){
			$sql = "delete from $tablename where month = $target_month and shop_id = $target_shop_id and $ino_colname = $ino";
			$result = $ybase->sql($conn,$sql);
			$value = "'$var_val'";
		}else{
			$sql = "insert into $tablename values($target_shop_id,$target_month,$ino,'$var_val','now','1')";
			$result = $ybase->sql($conn,$sql);
			$value = "'$var_val'";
		}
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