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

$new_retitle = trim($new_retitle);
$new_retitle = addslashes($new_retitle);
$new_remess = trim($new_remess);
$new_remess = addslashes($new_remess);

if(!$new_retitle){
	$ybase->error("タイトルを入力してください");
}
if(!$new_remess){
	$ybase->error("投稿内容を入力してください");
}
if(!$parent_id){
	$ybase->error("パラメーターヘエラー");
}
if(!$return_id){
	$ybase->error("パラメーターヘエラー");
}

if($manage_flag == 1){
	$next_script = "consult_manage.php";
	$sql = "select consult_id from consult where consult_id = $parent_id and r_flag = '2'";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
		$sql = "update consult set r_flag = '3' where consult_id = $parent_id and r_flag = '2'";
		$result = $ybase->sql($conn,$sql);
	}
	$jump_param_name = "manag_reconsultid";
}else{
	$next_script = "consult_top.php";
	$jump_param_name = "reconsultid";
}



$sql = "select nextval('consult_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:23102");
}
$new_consult_id = pg_fetch_result($result,0,0);

$sql = "insert into consult values($new_consult_id,$ybase->my_company_id,$parent_id,'2',$ybase->my_employee_id,$return_id,'1','$new_retitle','$new_remess','now','1')";
$result = $ybase->sql($conn,$sql);

$subject = "相談・提案(雄大ポータル)への返信";
if($manage_flag == 1){
	$sql = "select a.employee_id,a.email,a.employee_name from employee_list as a,mail_send as b where a.employee_id = b.employee_id and b.cate = 2 and a.status = '1' and b.config = '1'";
}else{
	$sql = "select employee_id,email,employee_name from employee_list where employee_id = $return_id and status = '1'";
}

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	list($q_employee_id,$q_email,$q_name) = pg_fetch_array($result,$i);

	$body = "{$q_name}様\r\n\r\n雄大グループ業務管理ポータル「相談・提案」に返信が届いております。\r\n\r\n【{$new_retitle}】\r\n\r\n";
	$body .= "下記から確認してください。\r\n\r\nhttps://".$_SERVER['HTTP_HOST']."/yudaigroup/login.php?{$jump_param_name}=$new_consult_id\r\n\r\n";
	$body .= "雄大グループ業務管理ポータル\r\n";

mail("$q_email","$subject","$body","From: yudai.system@yournet-jp.com");

}

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$next_script}?new_remess_sent=1");
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