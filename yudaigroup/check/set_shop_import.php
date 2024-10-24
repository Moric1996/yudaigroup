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
$check = new check();
$ybase->session_get();


$param = "t_shop_id=$t_shop_id";
/////////////////////////////////////////

$conn = $ybase->connect();

//////////////////////////////////////////条件
$addsql = "";
/////////////////////////
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:23916");
}
if(!preg_match("/^[0-9]+$/",$import_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:23917");
}

$sql = "select ckset_id from ck_check_set where section_id = '$import_shop_id' and last_flag = 1 and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/set_shop_fm.php?{$param}");
	exit;
}
$import_ckset_id = pg_fetch_result($result,0,0);

$sql = "select nextval('ck_check_set_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:23916");
}
$new_ckset_id = pg_fetch_result($result,0,0);

$sql = "insert into ck_check_set (ckset_id,section_id,subject_list,last_flag,add_date,status,allot_list) select $new_ckset_id,'$t_shop_id',subject_list,1,'now','1',allot_list from ck_check_set where ckset_id = $import_ckset_id";

$result = $ybase->sql($conn,$sql);

$sql = "update ck_check_set set last_flag = 0 where section_id = '$t_shop_id' and ckset_id <> $new_ckset_id and status = '1'";
$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/set_shop_fm.php?{$param}");
exit;
////////////////////////////////////////////////
?>