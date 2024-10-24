<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

include('./inorder_list.inc');

/////////////////////////////////////////
if(!preg_match("/^[0-9]+$/",$t_order_id)){
	$ybase->error("パラメーターエラー");
}
if(!$flplace){
	$ybase->error("パラメーターエラー");
}
if(!$ext){
	$ybase->error("パラメーターエラー");
}
if(!preg_match("/^[0-9]+$/",$no)){
	$ybase->error("パラメーターエラー");
}

$filepath = $flplace;

$filename = "doc$t_order_id_$no.".$ext;
 
header('Content-Type: application/force-download');
 
header('Content-Length: '.filesize($filepath));
 
header('Content-Disposition: attachment; filename="'.$filename.'"');
 
readfile($filepath);


////////////////////////////////////////////////
?>