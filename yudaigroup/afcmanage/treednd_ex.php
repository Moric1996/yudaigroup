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
$company_id = 5;
/////////////////////////////////////////
if(!preg_match("/^[0-9]+$/",$sourceid)){
	$ybase->error("error");
}
if(!preg_match("/^[0-9]+$/",$targetid)){
	$ybase->error("error");
}
if(!preg_match("/^[0-9a-zA-Z]+$/",$droppoint)){
	$ybase->error("error");
}
if(!$kind){
	$ybase->error("パラメーターエラー");
}
$body = "sourceid:$sourceid\ntargetid:$targetid\ndroppoint:$droppoint";
//mail("katsumata@yournet-jp.com","test","$body");


print "OK";
exit;

////////////////////////////////////////////////
?>