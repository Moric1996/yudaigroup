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


$var_val = trim($var_val);

if(!preg_match("/^[0-9]+$/",$survey_set_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:121821");
}
if(!preg_match("/^[0-9]+$/",$shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:12822");
}
if(!preg_match("/^[0-9]+$/",$card_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:12823");
}
if(!$var_name){
	$ybase->error("パラメーターエラー。ERROR_CODE:12824");
}
if(!$colname){
	$ybase->error("パラメーターエラー。ERROR_CODE:12825");
}
$conn = $ybase->connect(2);

$var_val = trim($var_val);


switch ($colname){
	case "private":
		$sql = "update survey_card set $colname = '$var_val' where card_id = $card_id and survey_set_id = $survey_set_id";
		break;
	case "category":
		if($var_val == ""){
			$var_val = 'null';
		}
		$sql = "update survey_card set $colname = $var_val where card_id = $card_id and survey_set_id = $survey_set_id";
		break;
}


$result = $ybase->sql($conn,$sql);
if($result){
	print "OK";
}else{
	print "NG";
}

////////////////////////////////////////////////
?>