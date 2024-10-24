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
	$ybase->error("パラメーターエラー。ERROR_CODE:13041");
}
if(!preg_match("/^[0-9]+$/",$shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:13042");
}
if(!preg_match("/^[0-9]+$/",$card_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:13043");
}
$var_val=trim($var_val);
if(!preg_match("/^[0-9]+$/",$var_val)){
	$ybase->error("パラメーターエラー。ERROR_CODE:13047");
}
if(!$ybase->survey_type_list[$var_val]){
	$ybase->error("パラメーターエラー。ERROR_CODE:13048");
}

$conn = $ybase->connect(2);
$sql = "select type from survey_card where card_id = $card_id and survey_set_id = $survey_set_id and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	$ybase->error("cardDBエラー。ERROR_CODE:13044");
}
$q_type = pg_fetch_result($result,0,0);

switch ($var_val){
	case 3:
	case 4:
	case 5:
	case 6:
		switch ($q_type){
			case 3:
			case 4:
			case 5:
			case 6:
				$sql = "update survey_card set type = '$var_val' where card_id = $card_id and survey_set_id = $survey_set_id and status = '1'";
				$result = $ybase->sql($conn,$sql);
				break;
			case 1:
			case 2:
			case 9:
			case 10:
			case 11:
			case 12:
			case 99:
				$sql = "update survey_option set status = '0' where survey_set_id = $survey_set_id and card_id = $card_id and status = '1'";
				$result = $ybase->sql($conn,$sql);
				$sql = "select nextval('survey_option_id_seq')";
				$result = $ybase->sql($conn,$sql);
				$num = pg_num_rows($result);
				if(!$num){
					$ybase->error("newoptionDBエラー。ERROR_CODE:13049");
				}
				$new_option_id = pg_fetch_result($result,0,0);
				$sql = "insert into survey_option values ($new_option_id,$survey_set_id,$card_id,1,'0','1','選択肢1',1,null,'now','1')";
				$result = $ybase->sql($conn,$sql);
				$sql = "update survey_card set type = '$var_val' where card_id = $card_id and survey_set_id = $survey_set_id and status = '1'";
				$result = $ybase->sql($conn,$sql);
				break;
		}
		break;
	case 1:
	case 2:
	case 9:
	case 10:
	case 11:
	case 12:
	case 99:
		$sql = "update survey_option set status = '0' where survey_set_id = $survey_set_id and card_id = $card_id and status = '1'";
		$result = $ybase->sql($conn,$sql);
		$sql = "select nextval('survey_option_id_seq')";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$ybase->error("newoptionDBエラー。ERROR_CODE:13050");
		}
		$new_option_id = pg_fetch_result($result,0,0);
		$sql = "insert into survey_option values ($new_option_id,$survey_set_id,$card_id,1,'0','1','',1,null,'now','1')";
		$result = $ybase->sql($conn,$sql);
		$sql = "update survey_card set type = '$var_val' where card_id = $card_id and survey_set_id = $survey_set_id and status = '1'";
		$result = $ybase->sql($conn,$sql);
		break;
}


if($result){
	print "OK";
}else{
	print "NG";
}

////////////////////////////////////////////////
?>