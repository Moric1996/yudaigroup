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

$conn = $ybase->connect();

if(!preg_match("/^[0-9]+$/",$admin_emp_id)){
	$ybase->error("パラメーターエラー");
}

$sql = "delete from consult_receive";
$result = $ybase->sql($conn,$sql);

$sql = "insert into consult_receive values($admin_emp_id,'now');";
$result = $ybase->sql($conn,$sql);


header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/consult_manage.php?select_tab=$select_tab");
exit;


////////////////////////////////////////////////
?>