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
$new_com_category = trim($new_com_category);

if(!$new_com_category){
	$ybase->error("カテゴリー名を入れてください");
}
$new_com_category = addslashes($new_com_category);

$sql = "select nextval('common_category_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:23102");
}
$new_category_id = pg_fetch_result($result,0,0);
if($new_category_id == 99){
	$sql = "select nextval('common_category_id_seq')";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$ybase->error("データベースエラーです。ERROR_CODE:23102");
	}
	$new_category_id = pg_fetch_result($result,0,0);
}

$sql = "insert into common_category values($new_category_id,'$new_com_category','now','1');";
$result = $ybase->sql($conn,$sql);


header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/consult_manage.php?select_tab=$select_tab");
exit;


////////////////////////////////////////////////
?>