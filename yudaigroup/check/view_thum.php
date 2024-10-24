<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/sbase.inc');

$sbase = new sbase();
$sbase->session_get();

$sbase->pm	= "applicant_id=$applicant_id";

if(!preg_match("/^[0-9]+$/",$applicant_id)){
	$sbase->error("パラメーターエラー。ERROR_CODE:17981");
}
if(!$colname){
	$sbase->error("パラメーターエラー。ERROR_CODE:17983");
}
if(!preg_match("/^[0-9]+$/",$no)){
	$sbase->error("パラメーターエラー。ERROR_CODE:17984");
}

///////////////////////////////データ取得

$conn = $sbase->connect();
$sql = "select applicant_id from personal_data where applicant_id = $applicant_id and school_id = $sbase->my_school_id and status <> '0'";
$result = $sbase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$sbase->error("パラメーターエラー。ERROR_CODE:17986");
}


$contents = $sbase->thumbnail_view($applicant_id,"$colname",$no,$kind);

$content_type = $sbase->content_type_list["png"];

header("Content-Type: $content_type");

echo $contents;
exit;

////////////////////////////////////////////////
?>