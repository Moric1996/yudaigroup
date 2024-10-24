<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
if(isset($_POST)){
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
}
if(isset($_GET)){
	foreach($_GET as $key => $value){
		${$key} = $value;
	}
}
include('../inc/ybase.inc');
include(dirname(__FILE__).'/../../camp/inc/campbase.inc');

$ybase = new ybase();
$campbase = new campbase();

$ybase->session_get();
if(!preg_match("/^[0-9]+$/",$regi_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:19201");
}
if(!preg_match("/^[0-9]+$/",$camp_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:19202");
}
if(!preg_match("/^[0-9]+$/",$cgt)){
	$ybase->error("パラメーターエラー。ERROR_CODE:192102");
}
$param = "camp_id=$camp_id&sel_prize_id=$sel_prize_id&sel_lot_stat=$sel_lot_stat&page=$page";
//$edit_f = 1;
/////////////////////////////////////////
if(($ybase->my_section_id == "003")||($ybase->my_position_class <= 40)){
	$delokflag="1";
}else{
	$delokflag="";
}
$conn = $ybase->connect(2);

$sql = "select prize_id,lot_stat,status from stamp_regist where camp_id = $camp_id and regist_id = $regi_id and status > '0'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("DBエラー。ERROR_CODE:19203");
}
	list($q_prize_id,$q_lot_stat,$q_status) = pg_fetch_array($result,0);

$sql = "update stamp_regist set lot_stat = $cgt where regist_id = $regi_id";
$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST']."/yudaigroup/camp/regi_list.php?$param");
exit;


////////////////////////////////////////////////
?>