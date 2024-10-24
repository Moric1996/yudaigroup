<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}

$yy=substr($target_month,0,4);
$mm=substr($target_month,4,2);
$maxday = date("t",mktime(0,0,0,$mm,1,$yy));

$param = "target_month=$target_month&target_shop_id=$target_shop_id";
/////////////////////////////////////////

$conn = $ybase->connect();

//////////////////////////////////////////条件
$addsql = "month = $target_month and shop_id = $target_shop_id";
/////////////////////////

$sql = "select nextval('telecom_group_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:23006");
}
$new_group_id = pg_fetch_result($result,0,0);
$sql = "insert into telecom_group (group_id,group_name,shop_id,month,add_date,status) values ($new_group_id,'',$target_shop_id,$target_month,'now','1')";
$result = $ybase->sql($conn,$sql);

//////項目も追加
$sql = "insert into telecom_group_const (group_const_id,shop_id,month,group_id,add_date,status) values (nextval('telecom_group_const_id_seq'),$target_shop_id,$target_month,$new_group_id,'now','1')";
$result = $ybase->sql($conn,$sql);


header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/set_team_fm.php?{$param}");
exit;
////////////////////////////////////////////////
?>