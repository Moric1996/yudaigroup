<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

include('./inorder_list.inc');

if(!preg_match("/^[0-9]+$/",$t_order_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!$flplace){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}

$param = "t_order_id=$t_order_id";
/////////////////////////////////////////

$conn = $ybase->connect();

//////////////////////////////////////////条件
/////////////////////////

$sql = "select array_to_json(attachfile) from order_main where order_id = $t_order_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	$ybase->error("データがありません");
}
list($q_attachfile) = pg_fetch_array($result,0);
$attachfile = json_decode($q_attachfile);
$tar_arr = array("$flplace");
$new_arr = array_diff($attachfile,$tar_arr);
$value = implode("','", $new_arr);
$new_attach = "ARRAY['".$value."']";

if(!$value){
	$sql = "update order_main set attachfile = null where order_id = $t_order_id";
}else{
	$sql = "update order_main set attachfile = $new_attach where order_id = $t_order_id";
}
$result = $ybase->sql($conn,$sql);
unlink("$flplace");
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/order_vw.php?{$param}");
exit;
////////////////////////////////////////////////
?>