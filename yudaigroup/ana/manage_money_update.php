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

$ybase->make_shop_list();
$ybase->shop_list['3001'] = "雄大ゴルフ熱函";	//雄大ゴルフ熱函
$ybase->shop_list['3002'] = "雄大ゴルフ清水町";	//雄大ゴルフ清水町
$ybase->make_employee_list();
if(!preg_match("/^[0-9]+$/",$shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:00821");
}

$pos_shopno = $ybase->section_to_pos[$shop_id];
if(!$pos_shopno){
	$ybase->error("パラメーターエラー。ERROR_CODE:00822");
}
if(!preg_match("/^[0-9]{4}\-[0-9]{2}$/",$selyymm)){
	$ybase->error("パラメーターエラー。ERROR_CODE:00823");
}
if(!preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/",$upyymmdd)){
	$ybase->error("パラメーターエラー。ERROR_CODE:00824");
}
if(!preg_match("/^[12]$/",$check_type)){
	$ybase->error("パラメーターエラー。ERROR_CODE:00825");
}
if($check_emp != $ybase->my_employee_id){
	$ybase->error("パラメーターエラー。ERROR_CODE:00826");
}

$yy = substr($selyymm,0,4);
$mm = substr($selyymm,5,2);
$tt = date('t',mktime(0,0,0,$mm,1,$yy));

$param = "shop_id=$shop_id&selyymm=$selyymm";

$conn = $ybase->connect();

$sql = "insert into manage_money_check (manage_money_check_id,pos_shopno,sale_date,checktype,employee_id,add_date,status) values (nextval('manage_money_check_id_seq'),$pos_shopno,'$upyymmdd',$check_type,$check_emp,'now','1')";

$result = $ybase->sql($conn,$sql);



header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/manage_money.php?$param");
exit;


////////////////////////////////////////////////
?>