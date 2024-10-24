<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

if(!preg_match("/^[0-9]+$/",$t_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20001");
}
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20002");
}
if(!preg_match("/^[0-9]+$/",$sec_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20003");
}

if($sec_id <= 1){
	$nextscript = "commentall_reg.php";
}else{
	$nextscript = "comment_reg.php";
}

$yy=substr($target_month,0,4);
$mm=substr($target_month,4,2);
$maxday = date("t",mktime(0,0,0,$mm,1,$yy));

$param = "t_month=$t_month&t_shop_id=$t_shop_id&target_num=$target_num&sec_id=$sec_id";
/////////////////////////////////////////
$comment = trim($comment);

if(!$comment){
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/comment_reg.php?{$param}");
exit;
}

$conn = $ybase->connect();

//////////////////////////////////////////条件
$addsql = "month = $target_month and shop_id = $target_shop_id";
/////////////////////////
$comment = addslashes($comment);

$sql = "select nextval('telecom_comment_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:23006");
}
$new_comment_id = pg_fetch_result($result,0,0);

$sql = "insert into telecom_comment (comment_id,shop_id,employee_id,comment,add_date,status) values ($new_comment_id,$sec_id,$ybase->my_employee_id,'$comment','now','1')";
$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$nextscript}?{$param}");
exit;
////////////////////////////////////////////////
?>