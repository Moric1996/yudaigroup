<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

$cate = 1;//お知らせ
/////////////////////////////////////////

$conn = $ybase->connect();


if($my_employee_id != $ybase->my_employee_id){
	$ybase->error("パラメーターエラー");
}
if(!$config){
	$ybase->error("パラメーターエラー");
}
if($config == "true"){
	$flag = "1";
}else{
	$flag = "0";
}
$sql = "select config from mail_send where employee_id = $ybase->my_employee_id and cate = $cate";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$sql = "insert into mail_send values($ybase->my_employee_id,$cate,'$flag','now')";
}else{
	$sql = "update mail_send set config = '$flag',up_date = 'now' where employee_id = $ybase->my_employee_id and cate = $cate";
}
$result = $ybase->sql($conn,$sql);

exit;

////////////////////////////////////////////////
?>