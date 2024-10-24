<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();


/////////////////////////////////////////

$conn = $ybase->connect();

if(!preg_match("/^[0-9]+$/",$consult_id)){
	$ybase->error("パラメーターエラー");
}
if(!preg_match("/^[0-9]+$/",$cgflag)){
	$ybase->error("パラメーターエラー");
}

$sql = "update consult set r_flag = '$cgflag' where consult_id = $consult_id";

$result = $ybase->sql($conn,$sql);


exit;


////////////////////////////////////////////////
?>