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

include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/../../survey/inc/survbase.inc');
$ybase = new ybase();
$survbase = new survbase();
//$ybase->session_get();

////////////エラーチェック

//print "var_name:{$var_name}<br>";
//print "var_val:{$var_val}<br>";
//print "survey_set_id:{$survey_set_id}<br>";
//print "shop_id:{$shop_id}<br>";
//print "card_type:{$card_type}<br>";
//print "option_id:{$option_id}<br>";



if(!preg_match("/^[0-9]+$/",$survey_set_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:12841");
}
if(!preg_match("/^[0-9]+$/",$shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:12842");
}
if(!preg_match("/^[0-9]+$/",$card_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:12843");
}
if(!preg_match("/^[0-9]+$/",$torequire)){
	$ybase->error("パラメーターエラー。ERROR_CODE:12844");
}
$conn = $ybase->connect(2);



$sql = "update survey_card set require = $torequire where card_id = $card_id and survey_set_id = $survey_set_id and status = '1'";


$result = $ybase->sql($conn,$sql);
if($result){
	print "OK";
}else{
	print "NG";
}

////////////////////////////////////////////////
?>