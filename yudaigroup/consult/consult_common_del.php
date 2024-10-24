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

if(!preg_match("/^[0-9]+$/",$del_common_id)){
	$ybase->error("パラメーターエラー");
}

$sql = "update common_info set status = '0' where common_id = $del_common_id";

$result = $ybase->sql($conn,$sql);


header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/consult_manage.php?page2=$page2&sel_com_cate=$sel_com_cate&select_tab=$select_tab");
exit;


////////////////////////////////////////////////
?>