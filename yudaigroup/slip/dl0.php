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

$sql = "select attach,to_char(month,'YYYYMM'),money,supplier from slip where slip_id = $slip_id and company_id = $ybase->my_company_id and status > '0'";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データエラー。ERROR_CODE:17983");
}
list($q_attach,$q_month,$q_money,$q_supplier) = pg_fetch_array($result,0);
	$q_attach_arr = json_decode($q_attach,true);
	if(!$q_attach_arr){
		$ybase->error("データエラー。ERROR_CODE:17983");
	}
	$comp_name = $slip->supplier_list[$q_supplier];
	$comp_name = str_replace(" ","",$comp_name);
	$comp_name = str_replace("　","",$comp_name);

	$filename = $q_attach_arr[$attach_no];
	$ext = substr($filename,strrpos($filename,'.') + 1);
	$data = file_get_contents($filename);
//	$ContentType = $ybase->mime_type[$ext];
//header("Content-Type: $ContentType");

$flsize = strlen($data);


$filename = "slip_{$q_month}_{$comp_name}_{$slip_id}{$attach_no}".".".$ext;
 
header('Content-Type: application/force-download');
 
header("Content-Length: $flsize");
 
header('Content-Disposition: attachment; filename="'.$filename.'"');
 
echo $data;
exit;


////////////////////////////////////////////////
?>