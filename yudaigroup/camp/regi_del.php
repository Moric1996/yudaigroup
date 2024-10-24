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
	$ybase->error("パラメーターエラー。ERROR_CODE:19001");
}
if(!preg_match("/^[0-9]+$/",$camp_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:19002");
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


$sql = "update stamp_regist set status = '0' where regist_id = $regi_id";
$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST']."/yudaigroup/camp/regi_list.php?$param");
exit;


////////////////////////////////////////////////
?>