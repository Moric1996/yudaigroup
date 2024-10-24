<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();
$ybase->make_employee_list();

/////////////////////////////////////////

if(!$message_id){
	$ybase->error("パラメーターエラーです。ERROR_CODE:21102");
}
if($fileno==""){
	$ybase->error("パラメーターエラーです。ERROR_CODE:21103");
}



$conn = $ybase->connect();


$sql = "select attachment,attachmentname from message where message_id = $message_id and status = '1'";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	$ybase->error("データエラーです。ERROR_CODE:21104");
}
list($q_attachment,$q_attachmentname) = pg_fetch_array($result,0);

$q_attachment = str_replace("{","",$q_attachment);
$q_attachment = str_replace("}","",$q_attachment);
$q_attachmentname = str_replace("{","",$q_attachmentname);
$q_attachmentname = str_replace("}","",$q_attachmentname);
$arr_file = explode(",",$q_attachment);
$arr_filename = explode(",",$q_attachmentname);
$filename = $arr_file[$fileno];
$dlname = $arr_filename[$fileno];
if (!is_readable($filename)) {
	$ybase->error("ファイルエラーです。ERROR_CODE:21105");
}
$mimeType = 'application/octet-stream';

header('Content-Type: ' . $mimeType);

header('X-Content-Type-Options: nosniff');

header('Content-Length: ' . filesize($filename));

header('Content-Disposition: attachment; filename="' . basename($dlname) . '"');

header('Connection: close');

while (ob_get_level()) { ob_end_clean(); }

readfile($filename);

exit;

////////////////////////



////////////////////////////////////////////////
?>