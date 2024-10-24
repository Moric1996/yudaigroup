<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
if(isset($_POST)){
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
}
if(isset($_GET)){
	foreach($_GET as $key => $value){
		${$key} = $value;
	}
}
include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/slip.inc');

$ybase = new ybase();
$slip = new slip();

$ybase->session_get();
$ybase->my_company_id = 5;

$ybase->make_yournet_employee_list("1");
$slip->supplier_make();

$slip_id = trim($slip_id);
$accept_list_id = trim($accept_list_id);

if(!preg_match("/^[0-9]+$/",$slip_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$accept_list_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}
if(!preg_match("/^[0-9]+$/",$myaccept_count)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10823");
}

$conn = $ybase->connect(3);

$sql = "update accept_log set status = '0' where slip_id = $slip_id and accept_list_id = $accept_list_id and status = '1'";
$result = $ybase->sql($conn,$sql);

$sql = "update slip set status = '1',up_date = 'now' where slip_id = $slip_id and status = '2'";
$result2 = $ybase->sql($conn,$sql);

$res_str="OK2";

if($result){
	print "$res_str";
}else{
	print "NG";
}

////////////////////////////////////////////////
?>