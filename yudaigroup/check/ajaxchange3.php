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
//print "target_shop_id:{$target_shop_id}<br>";
//print "target_ckaction_list_id:{$target_ckaction_list_id}<br>";
//print "target_item_id:{$target_item_id}<br>";
//print "target_employee_id:{$target_employee_id}<br>";


$var_val = trim($var_val);
$var_val = preg_replace('/　/', ' ', $var_val);
mb_language("Ja");
mb_internal_encoding("utf-8");
$var_val = mb_convert_kana($var_val, "n");

$conn = $ybase->connect();
$var_val = addslashes($var_val);
$var_val = trim($var_val);
if(preg_match("/^([a-zA-Z_]+)([0-9]+)$/",$var_name,$str)){
	$colname = $str[1];
	$ino = $str[2];
}else{
	$colname = $var_name;
}

if($colname == "itemname"){
	if(!preg_match("/^[0-9]+$/",$ino)){
		$ybase->error("パラメーターエラー。ERROR_CODE:10943");
	}
	$sql = "update ck_item_list set item_name = '$var_val' where item_id = $ino and status = '1'";
	$result = $ybase->sql($conn,$sql);
}

if($result){
	print "OK";
}else{
	print "NG";
}

////////////////////////////////////////////////
?>