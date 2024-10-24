<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

//$kind = 1;
$company_id = 1;
/////////////////////////////////////////
$body = "nodeid:$nodeid\nnewtext:$newtext\n";
//mail("katsumata@yournet-jp.com","test","$body");
if(!preg_match("/^[0-9]+$/",$nodeid)){
	$ybase->error("error");
}
if(!$newtext){
	$ybase->error("error");
}
if(!$kind){
	$ybase->error("パラメーターエラー");
}

$conn = $ybase->connect();
$sql = "update docu_manage set displayname = '$newtext' where kind = $kind and company_id = $company_id and status = '1' and file_id = $nodeid";

$result = $ybase->sql($conn,$sql);

print "OK";
exit;

////////////////////////////////////////////////
?>