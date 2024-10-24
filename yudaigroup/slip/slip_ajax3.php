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

if(!preg_match("/^[0-9]+$/",$slip_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
$var_val = trim($var_val);
pg_escape_string($var_val);

$conn = $ybase->connect(3);

$sql = "update slip set memo = '$var_val ',up_date = 'now' where slip_id = $slip_id";
$result = $ybase->sql($conn,$sql);

$res_str="OK";

if($result){
	print "$res_str";
}else{
	print "NG";
}

////////////////////////////////////////////////
?>