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
if(!$kind){
	$ybase->error("パラメーターエラー");
}

$conn = $ybase->connect();

$sql = "select type,filename,ext from docu_manage where kind = $kind and company_id = $company_id and status = '1' and file_id = $nodeid";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	list($q_type,$q_filename,$q_ext) = pg_fetch_array($result,0);
}

$sql = "update docu_manage set status = '0' where kind = $kind and company_id = $company_id and status = '1' and file_id = $nodeid";
$result = $ybase->sql($conn,$sql);

if(($q_type == '2') && $q_filename){
$deletefile = "/home/yournet/yudai/documanage/"."$kind"."/"."$q_filename".".".$q_ext;

unlink($deletefile);
}
print "OK";
exit;

////////////////////////////////////////////////
?>