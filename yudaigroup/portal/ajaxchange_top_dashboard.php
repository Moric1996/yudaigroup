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

$ybase = new ybase();

$sess_id = $ybase->session_get();


if(!preg_match("/^[0-9]+$/",$ybase->my_employee_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:121921");
}
//print "uiid:$var_check";
//print "sortp:$sortp";
//print "parentid:$parentid";
//print "senderid:$senderid";
//$body .= "senderid:$senderid\n";
//mail("katsumata@yournet-jp.com","dashboard","$body");
$conn = $ybase->connect();
$var_val = trim($var_val);
$sql = "select status from top_dashboard where employee_id = {$ybase->my_employee_id}";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

if($var_check == "true"){
	if($num){
		$sql = "update top_dashboard set status = '1',add_date = 'now' where employee_id = {$ybase->my_employee_id}";
	}else{
		$sql = "insert into top_dashboard values({$ybase->my_employee_id},'now','1')";
	}
	$result = $ybase->sql($conn,$sql);
}else{
	if($num){
		$sql = "delete from top_dashboard where employee_id = {$ybase->my_employee_id}";
	}else{
		$result = "OK";
	}
	$result = $ybase->sql($conn,$sql);
}

if($result){
	print "OK";
}else{
	print "NG";
}


////////////////////////////////////////////////
?>