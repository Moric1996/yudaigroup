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
if(!$company_id){
	$ybase->error("パラメーターエラー");
}
if(!$file_id){
	$ybase->error("パラメーターエラー");
}

$conn = $ybase->connect();
$sql = "select filename,kind,ext from docu_manage where file_id = $file_id and company_id = $company_id and status = '1' and type='2'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当なし:$sql");
}
list($q_filename,$q_kind,$q_ext) = pg_fetch_array($result,0);
if(!$ybase->mime_type[$q_ext]){
	$ybase->error("該当なし");
}
$filename = "/home/yournet/yudai/documanage/".$q_kind."/".$q_filename.".".$q_ext;

$size = filesize($filename);

$handle = fopen($filename, "r");
$contents = fread($handle, $size);
fclose($handle);
// コンテンツの識別子
$etag = md5($_SERVER["REQUEST_URI"]).$size;

$size2=$size-1; 
header("Content-Range: bytes 0-{$size2}/{$size}"); 
header("Content-Length: ".$size);
header("Content-Type: {$ybase->mime_type[$q_ext]}");

//header("Etag: \"{$etag}\"");

//if($size) echo fread($contents,$size);
echo $contents;
exit;




////////////////////////////////////////////////

?>