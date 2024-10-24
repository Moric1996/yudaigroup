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
	$ybase->error("パラメーターエラー。ERROR_CODE:12941");
}
if(!preg_match("/^[0-9]+$/",$shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:12942");
}
if(!preg_match("/^[0-9]+$/",$card_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:12943");
}
if(!preg_match("/^[0-9]+$/",$option_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:12946");
}
$conn = $ybase->connect(2);
$sql = "select other_flag from survey_option where option_id = $option_id and card_id = $card_id and survey_set_id = $survey_set_id and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	$ybase->error("{$num}cardDBエラー。ERROR_CODE:12944");
}
$q_other_flag = pg_fetch_result($result,0,0);
if($q_other_flag == '1'){
	$sql = "update survey_option set status = '0' where survey_set_id = $survey_set_id and card_id = $card_id and other_flag = '2' and status = '1'";
	$result = $ybase->sql($conn,$sql);
}
$sql = "update survey_option set status = '0' where option_id = $option_id and card_id = $card_id and survey_set_id = $survey_set_id and status = '1'";
$result = $ybase->sql($conn,$sql);


if($result){
	print "OK";
}else{
	print "NG";
}

////////////////////////////////////////////////
?>