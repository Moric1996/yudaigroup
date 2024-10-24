<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

mb_language("Ja");
mb_internal_encoding("utf-8");
////////////エラーチェック

$error_flag = 0;
$space = array();
if(!isset($del_data_id)){
	$del_data_id = array();
}
if(!isset($no_target)){
	$no_target = array();
}
//////////////////////////////////////////////////
$conn = $ybase->connect();

$ck_array = array_intersect($del_data_id,$no_target);

if($ck_array){
	$ybase->error("退職対象欄と今後除く欄同時にチェックはできません");
}

foreach($no_target as $key => $val){
	$sql = "update employee_list set nodel_flag = 1 where employee_id = $val";
	$result = $ybase->sql($conn,$sql);
}

foreach($del_data_id as $key => $val){
	$sql = "update employee_list set status = '2' where employee_id = $val";
	$result = $ybase->sql($conn,$sql);
}


header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/px_ck.php");
exit;


////////////////////////////////////////////////
?>