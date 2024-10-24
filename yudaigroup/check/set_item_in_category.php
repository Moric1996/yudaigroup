<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/check.inc');
include('./inc/check_list.inc');

$ybase = new ybase();
$ybase->session_get();


$param = "";
/////////////////////////////////////////

$conn = $ybase->connect();

//////////////////////////////////////////条件
$addsql = "";
/////////////////////////

$sql = "select nextval('ck_category_list_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:23016");
}
$new_category_id = pg_fetch_result($result,0,0);
$sql = "insert into ck_category_list values ($new_category_id,'','now','1')";
$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/set_item_fm.php?{$param}&confirm_f=no");
exit;
////////////////////////////////////////////////
?>