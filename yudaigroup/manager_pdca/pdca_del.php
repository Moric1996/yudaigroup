<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

//$edit_f = 1;
/////////////////////////////////////////
if(!preg_match("/^[0-9]+$/",$del_pdca_id)){
	$ybase->error("パラメーターエラーです。ERROR_CODE:29101");
}
if($cate){
	$add="#tuika{$cate}";
}
$param = "shop_id=$shop_id&selyymm=$selyymm&edit_f=$edit_f&$add";
$conn = $ybase->connect();
$sql = "update manager_pdca set status = '0' where pdca_id = $del_pdca_id";
$result = $ybase->sql($conn,$sql);


header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/pdca_top.php?$param");
exit;



////////////////////////////////////////////////
?>