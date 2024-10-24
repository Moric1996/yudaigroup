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
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10922");
}

$conn = $ybase->connect();
$var_val = pg_escape_string($var_val);
$var_val = trim($var_val);
if(preg_match("/^([a-zA-Z_]+)([0-9]+)$/",$var_name,$str)){
	$colname = $str[1];
	$ino = $str[2];
}else{
	$colname = $var_name;
}

if($colname == "action"){
	if(!preg_match("/^[0-9]+$/",$target_ckaction_list_id)){
		$ybase->error("パラメーターエラー。ERROR_CODE:10923");
	}
	if(!preg_match("/^[0-9]+$/",$var_val)){
		$ybase->error("パラメーターエラー。ERROR_CODE:10924");
	}
	$sql = "update ck_check_action_list set action = $var_val where ckaction_list_id = $target_ckaction_list_id and section_id = '$target_shop_id' and status = '1'";
	$result = $ybase->sql($conn,$sql);
	$sql = "update ck_check_action set employee_id = $ybase->my_employee_id where ckaction_id = $target_ckaction_id and section_id = '$target_shop_id'";
	$result = $ybase->sql($conn,$sql);

}elseif($colname == "comm"){
	if(!preg_match("/^[0-9]+$/",$target_ckaction_list_id)){
		$ybase->error("パラメーターエラー。ERROR_CODE:10925");
	}
	$sql = "update ck_check_action_list set com = '$var_val' where ckaction_list_id = $target_ckaction_list_id and section_id = '$target_shop_id' and status = '1'";
	$result = $ybase->sql($conn,$sql);
}elseif($colname == "t_action_date"){
	if(!preg_match("/^[0-9]+$/",$t_ckaction_id)){
		$ybase->error("パラメーターエラー。ERROR_CODE:10926");
	}
	if(preg_match("/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/",$var_val)){
		$sql = "update ck_check_action set action_date = '$var_val' where ckaction_id = $t_ckaction_id and section_id = '$target_shop_id'";
		$result = $ybase->sql($conn,$sql);
	}
}elseif($colname == "addcom"){
	if(!preg_match("/^[0-9]+$/",$t_ckaction_id)){
		$ybase->error("パラメーターエラー。ERROR_CODE:10927");
	}
	$sql = "update ck_check_action set com = '$var_val' where ckaction_id = $t_ckaction_id and section_id = '$target_shop_id'";
	$result = $ybase->sql($conn,$sql);
}




if($result){
	print "OK";
}else{
	print "NG";
}

////////////////////////////////////////////////
?>