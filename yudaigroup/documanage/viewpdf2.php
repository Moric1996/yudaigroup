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
if(!preg_match("/^[0-9]+$/",$file_id)){
	$ybase->error("パラメーターエラー");
}

$conn = $ybase->connect();
$sql = "select kind,filename from docu_manage where file_id = $file_id and status = '1'";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num == 1){
	list($q_kind,$q_filename) = pg_fetch_array($result,0);
}


$filename = "/home/yournet/yudai/documanage/".$q_kind."/".$q_filename.".pdf";

$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);

header("Content-Type: application/pdf");

echo $contents;
exit;


////////////////////////////////////////////////
?>