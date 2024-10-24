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
/////////////////////////////////////////
foreach($slip_type_list as $key => $val){
	$slip->accept_list_make($key,0);
}


$conn = $ybase->connect(3);

//////////////////////////////////////////条件
/////////////////////////
if(!preg_match("/^[0-9]+$/",$slip_id)){
	$ybase->error("パラメーターエラー");
}

$sql = "select attach from slip where slip_id = $slip_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	list($q_attach) = pg_fetch_array($result,0);
	$q_attach = trim($q_attach);
	$q_attach_arr = json_decode($q_attach,true);
	if($q_attach_arr){
		foreach($q_attach_arr as $key => $val){
			unlink("$val");
		}
	}
}

$sql = "update accept_log set status = '0' where slip_id = $slip_id and status = '1'";
$result = $ybase->sql($conn,$sql);

$sql = "update slip set status = '0' where slip_id = $slip_id";
$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/slip_list.php?{$param}");
exit;
////////////////////////////////////////////////
?>