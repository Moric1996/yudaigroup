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
$handle = fopen($flplace, "r");
$contents = fread($handle, filesize($flplace));
fclose($handle);

switch($ext) {
    case "gif": $ctype="image/gif"; break;
    case "png": $ctype="image/png"; break;
    case "jpeg":
    case "jpg": $ctype="image/jpeg"; break;
    case "svg": $ctype="image/svg+xml"; break;
    default:
}
header("Content-Type: $ctype");

echo $contents;
exit;



////////////////////////////////////////////////
?>