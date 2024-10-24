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
if(!preg_match("/^[0-9]+$/",$survey_set_id)){
	$survbase->error("パラメーターエラー。ERROR_CODE:12401");
}
if(!preg_match("/^[0-9]+$/",$shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:12412");
}
if(!preg_match("/^[0-9]+$/",$card_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:12413");
}
if(!preg_match("/^[0-9]+$/",$torequire)){
	$ybase->error("パラメーターエラー。ERROR_CODE:12414");
}
$param = "survey_set_id=$survey_set_id";
$conn = $ybase->connect(2);



//アンケートの存在確認
$sql = "update survey_card set require = $torequire where card_id = $card_id and survey_set_id = $survey_set_id and status = '1'";
$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST']."/yudaigroup/survey/q_fm.php?$param#!");
exit;


////////////////////////////////////////////////
?>