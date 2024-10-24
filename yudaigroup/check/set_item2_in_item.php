<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/check.inc');
include('./inc/check_list.inc');

$ybase = new ybase();
$ybase->session_get();


$param = "";
/////////////////////////////////////////

$conn = $ybase->connect();

//////////////////////////////////////////条件
$addsql = "";
/////////////////////////
if(!preg_match("/^[0-9]+$/",$category_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:23015");
}

$sql = "select nextval('ck_item_list_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:23016");
}
$new_item_id = pg_fetch_result($result,0,0);
$sql = "insert into ck_item_list values ($new_item_id,$category_id,'','now','1')";
$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/set_item2_fm.php?{$param}");
exit;
////////////////////////////////////////////////
?>