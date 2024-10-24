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

if(!preg_match("/^[0-9]+$/",$del_message_id)){
	$ybase->error("パラメーターエラー");
}

$sql = "update message set status = '0' where message_id = $del_message_id";

$result = $ybase->sql($conn,$sql);


header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/message_list.php?page=$page");
exit;


////////////////////////////////////////////////
?>