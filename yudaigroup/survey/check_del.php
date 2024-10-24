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

$ybase = new ybase();
$ybase->session_get();
//$edit_f = 1;
/////////////////////////////////////////
if(!preg_match("/^[0-9]+$/",$survey_set_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:11211");
}

if(!preg_match("/^[0-9]+$/",$shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:11212");
}
if(!preg_match("/^[0-9]+$/",$t_answer_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:11213");
}
$param = "survey_set_id=$survey_set_id&shop_id=$shop_id";



$conn = $ybase->connect(2);
//アンケートの存在確認
$sql = "update survey_answer set status = '0' where answer_id = $t_answer_id and survey_set_id = $survey_set_id";
$result = $ybase->sql($conn,$sql);
$sql = "update survey_answer_part set status = '0' where answer_id = $t_answer_id and survey_set_id = $survey_set_id";
$result = $ybase->sql($conn,$sql);



header("Location: https://".$_SERVER['HTTP_HOST']."/yudaigroup/survey/ans_list.php?$param");
exit;


////////////////////////////////////////////////
?>