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
if(!preg_match("/^[0-9]+$/",$t_ckaction_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10926");
}
if(!preg_match("/^[0-9]+$/",$target_ckaction_list_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10923");
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

if(($colname == "reply") && $var_val){
	$sql = "select nextval('ck_reply_id_seq')";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$ybase->error("データベースエラーです。ERROR_CODE:78010");
	}
	$new_reply_id = pg_fetch_result($result,0,0);
	$sql = "insert into ck_reply (reply_id,ckaction_list_id,ckaction_id,section_id,reply,employee_id,add_date,up_date,status) values ($new_reply_id,$target_ckaction_list_id,$t_ckaction_id,'$target_shop_id','$var_val',$ybase->my_employee_id,'now','now','1')";
	$result = $ybase->sql($conn,$sql);
}

if($result){
	print "$new_reply_id";
}else{
	print "NG";
}

////////////////////////////////////////////////
?>