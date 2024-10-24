<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

if(!preg_match("/^[a-zA-Z0-9]+$/",$employee_id)){
	$ybase->error("パラメーターエラー");
}
if(!preg_match("/^[a-zA-Z0-9]+$/",$newpass)){
	$ybase->error("パラメーターエラー");
}

$conn = $ybase->connect();

///////////////////////////////
$sql = "update employee_list set pass = crypt('$newpass',gen_salt('md5')) where employee_id = $employee_id";
$result = $ybase->sql($conn,$sql);

////////////////////////////////////////////////
?>