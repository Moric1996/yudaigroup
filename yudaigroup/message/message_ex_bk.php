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

$conn = $ybase->connect();

$new_title = trim($new_title);
$new_title = addslashes($new_title);
$new_mess = trim($new_mess);
$new_mess = addslashes($new_mess);

if(!$new_title){
	$ybase->error("タイトルを入力してください");
}
if(!$new_mess){
	$ybase->error("投稿内容を入力してください");
}
$sql = "select nextval('message_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:23002");
}
$new_message_id = pg_fetch_result($result,0,0);

if(!$target_company_id){
	$target_company_id = 'null';
}
if(!$target_section_list){
	$target_section_list = 'null';
}
if(!$target_employee_type_list){
	$target_employee_type_list = 'null';
}
if(!$target_position_class_list){
	$target_position_class_list = 'null';
}
if(!$target_employee_id_list){
	$target_employee_id_list = 'null';
}
$sql = "insert into message values($new_message_id,$ybase->my_employee_id,'$new_title','$new_mess',null,null,'now','1')";

$result = $ybase->sql($conn,$sql);

$subject = "新しいお知らせ(雄大ポータル)";

$sql = "select a.employee_id,a.email,a.employee_name from employee_list as a,mail_send as b where a.employee_id = b.employee_id and b.cate = 1 and a.status = '1' and b.config = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	for($i=0;$i<$num;$i++){
	list($q_employee_id,$q_email,$q_name) = pg_fetch_array($result,$i);

	$body = "{$q_name}様\r\n\r\n雄大グループ業務管理ポータルに新しいお知らせが届いております。\r\n\r\n【{$new_title}】\r\n\r\n";
	$body .= "下記から確認してください。\r\n\r\nhttps://".$_SERVER['HTTP_HOST']."/yudaigroup/login.php?messageid=$new_message_id\r\n\r\n";
	$body .= "雄大グループ業務管理ポータル\r\n";

mail("$q_email","$subject","$body","From: yudai.system@yournet-jp.com");
}
}

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/message_list.php?new_mess_sent=1");
exit;


/*
$ybase->title = "お知らせ";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("お知らせ");


$ybase->HTMLfooter();
$ybase->priout();
*/
////////////////////////////////////////////////
?>