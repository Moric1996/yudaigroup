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
$conn = $ybase->connect(2);
$sql = "select type from survey_card where card_id = $card_id and survey_set_id = $survey_set_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	$ybase->error("cardDBエラー。ERROR_CODE:12944");
}
$q_type = pg_fetch_result($result,0,0);
if(($q_type < 3) || ($q_type > 5)){
	$ybase->error("タイプエラー。ERROR_CODE:12945");
}

$sql = "select option_id from survey_option where survey_set_id = $survey_set_id and card_id = $card_id and other_flag <> '0' and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$ybase->error("作成済みエラー。ERROR_CODE:12946");
}

$sql = "select max(key),max(jun),count(*) from survey_option where survey_set_id = $survey_set_id and card_id = $card_id and other_flag = '0' and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	list($q_max_key,$q_max_jun,$q_cnt) = pg_fetch_array($result,0);
	$nx_key = $q_max_key + 1;
	$nx_jun = $q_max_jun + 1;
	$nx_cnt = $q_cnt + 1;
}else{
	$nx_key = 1;
	$nx_jun = 1;
	$nx_cnt = 1;
}

$sql = "insert into survey_option values (nextval('survey_option_id_seq'),$survey_set_id,$card_id,1,'1','99','その他',$nx_jun,null,'now','1')";
$result = $ybase->sql($conn,$sql);

$nx_jun += 1;

$sql = "insert into survey_option values (nextval('survey_option_id_seq'),$survey_set_id,$card_id,1,'2','other99','その他の場合',$nx_jun,null,'now','1')";
$result = $ybase->sql($conn,$sql);

if($result){
	print "OK";
}else{
	print "NG";
}

////////////////////////////////////////////////
?>