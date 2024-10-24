<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

/////////////////////////////////////////

//$body = "var_name:$var_name\nvar_val:$var_val\nshop_id:$shop_id\ncategory:$category\nselyymm:$selyymm\npdca_id:$pdca_id\n";
//mail("katsumata@yournet-jp.com","test","$body");

$var_val = trim($var_val);

if(!preg_match("/^[0-9]+$/",$send_emp_id)){
	$ybase->error("パラメーターエラーです。ERROR_CODE:29501");
}
if(!preg_match("/^[0-9]+$/",$kind)){
	$ybase->error("パラメーターエラーです。ERROR_CODE:29501");
}
if(!$var_val){
	$ybase->error("パラメーターエラーです。ERROR_CODE:29502");
}
if(preg_match("/^[0-9]+$/",$last_chat_id)){
	$addsql = " and chat_id > $last_chat_id ";
}else{
	$addsql = "";
}

$var_val = addslashes($var_val);

$conn = $ybase->connect();


$sql = "select chat_id,s_flag,r_flag,mess,to_char(add_date,'YYYY/MM/DD HH24:MI:SS') from chatbox where send_id = {$ybase->chat_receive_employee_id} and receive_id = {$ybase->my_employee_id} and status = '1'{$addsql} and kind = $kind order by add_date";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	
	for($i=0;$i<$num;$i++){
		list($q_chat_id,$q_s_flag,$q_r_flag,$q_mess,$q_add_date) = pg_fetch_array($result,$i);
		$arr_data[$i]['chat_id'] = $q_chat_id;
		$arr_data[$i]['s_flag'] = $q_s_flag;
		$arr_data[$i]['r_flag'] = $q_r_flag;
		$arr_data[$i]['mess'] = $q_mess;
		$arr_data[$i]['add_date'] = $q_add_date;
	}
}
/////データ入力
$sql = "select nextval('chatbox_chat_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:29506");
}
$new_chat_id = pg_fetch_result($result,0,0);

$sql = "insert into chatbox (chat_id,kind,send_id,receive_id,s_flag,r_flag,mess,add_date,status) values ($new_chat_id,$kind,{$ybase->my_employee_id},{$ybase->chat_receive_employee_id},'1','1','$var_val','now','1')";
$result = $ybase->sql($conn,$sql);
///////
$arr_data['newchatid'] = $new_chat_id;
if($result){
	$json_data = json_encode($arr_data);
}else{
	$json_data = json_encode(array());
}
print "$json_data";

////////////////////////////////////////////////
?>