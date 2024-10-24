<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

$kind = 1;
/////////////////////////////////////////

$conn = $ybase->connect();
$new_com_category = trim($new_com_category);

if(!$new_com_tab){
	$ybase->error("タブ名を入れてください");
}
$new_com_tab = addslashes($new_com_tab);

$sql = "select nextval('common_tab_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:23102");
}
$new_tab_id = pg_fetch_result($result,0,0);


$sql = "insert into common_tab values($new_tab_id,'$new_com_tab',$kind,'now','1');";
$result = $ybase->sql($conn,$sql);


header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/consult_manage.php?select_tab=$select_tab");
exit;


////////////////////////////////////////////////
?>