<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/sbase.inc');

$sbase = new sbase();
$sbase->session_get();

if($sbase->my_sauthority != "1"){
	$sbase->error("権限がありません");
}

$sbase->pm	= "";

if(!preg_match("/^[0-9]+$/i",$t_suser_id)){
	$sbase->error("パラメーターエラーです。ERROR_CODE:13005");
}

$conn = $sbase->connect();

$sql = "update contractor_school_user set status = '0' where suser_id = $t_suser_id and school_id = $sbase->my_school_id";
$result = $sbase->sql($conn,$sql);


///////////////////////////////

$param="dochange=ok";
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/user_list.php?$param");
exit;

////////////////////////////////////////////////
?>