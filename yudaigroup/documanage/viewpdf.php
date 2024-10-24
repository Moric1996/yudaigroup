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
if(!preg_match("/^[0-9]+$/",$kind)){
	$ybase->error("パラメーターエラー");
}
if(!preg_match("/^[a-zA-Z0-9]+$/",$fn)){
	$ybase->error("パラメーターエラー");
}

$filename = "/home/yournet/yudai/documanage/".$kind."/".$fn.".pdf";

$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);

header("Content-Type: application/pdf");

echo $contents;
exit;


////////////////////////////////////////////////
?>