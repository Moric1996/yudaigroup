<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();
$ybase->make_consult_receive_list();


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
if(!$return_id){
	$return_id = $ybase->consult_receive_employee_id;
}
$sql = "select nextval('consult_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:23002");
}
$new_consult_id = pg_fetch_result($result,0,0);


$sql = "insert into consult values($new_consult_id,$ybase->my_company_id,$new_consult_id,'1',$ybase->my_employee_id,$return_id,'1','$new_title','$new_mess','now','1')";

$result = $ybase->sql($conn,$sql);

$subject = "新しい相談・提案(雄大ポータル)";

$sql = "select employee_id,email,employee_name from employee_list where employee_id = $return_id and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	list($q_employee_id,$q_email,$q_name) = pg_fetch_array($result,$i);

	$body = "{$q_name}様\r\n\r\n雄大グループ業務管理ポータルに新しい相談・提案が届いております。\r\n\r\n【{$new_title}】\r\n\r\n";
	$body .= "下記から確認してください。\r\n\r\nhttps://".$_SERVER['HTTP_HOST']."/yudaigroup/login.php?consultid=$new_consult_id\r\n\r\n";
	$body .= "雄大グループ業務管理ポータル\r\n";

mail("$q_email","$subject","$body","From: yudai.system@yournet-jp.com");

}

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/consult_top.php?new_mess_sent=1");
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