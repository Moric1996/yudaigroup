<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
if(isset($_POST)){
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
}
if(isset($_GET)){
	foreach($_GET as $key => $value){
		${$key} = $value;
	}
}
include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/slip.inc');

$ybase = new ybase();
$slip = new slip();
$ybase->session_get();

$ybase->my_company_id = 5;

$ybase->make_yournet_employee_list("1");
$slip->supplier_make();

$conn = $ybase->connect(3);

if(!preg_match("/^[0-9]+$/",$slip_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:17981");
}
if(!preg_match("/^[0-9]+$/",$attach_no)){
	$ybase->error("パラメーターエラー。ERROR_CODE:17980");
}

///////////////////////////////データ取得

$sql = "select attach from slip where slip_id = $slip_id and company_id = $ybase->my_company_id and status > '0'";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データエラー。ERROR_CODE:17983");
}
list($q_attach) = pg_fetch_array($result,0);
	$q_attach_arr = json_decode($q_attach,true);
	if(!$q_attach_arr){
		$ybase->error("データエラー。ERROR_CODE:17983");
	}
	$filename = $q_attach_arr[$attach_no];
	$thum_file = substr($filename,0,strrpos($filename,'.'))."_thum.png";
	$data = file_get_contents($thum_file);

header("Content-Type: image/png");

echo $data;
exit;

////////////////////////////////////////////////
?>