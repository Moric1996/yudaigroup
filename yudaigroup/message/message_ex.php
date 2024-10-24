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

$count = count($_FILES['uploadfile']['name']);
if($count > 10){
	$ybase->error("一度にアップできるファイルは10ファイルまでです");
}

$employee_name_list_nowall = $ybase->make_employee_list(1);

if($sel_allemp == 1){
	$sel_emp = array_keys($employee_name_list_nowall);
}elseif(!$sel_emp){
	$ybase->error("送信先を選んでください");
}
$uploaddir = "/home/yournet/yudai/message/";
if(!file_exists($uploaddir)){
	mkdir($uploaddir, 0775);
}

$no=0;
$filelist="";
$filenamelist="";
foreach((array)$_FILES['uploadfile']['name'] as $key2 => $val2){
	$ext = substr($val2,strrpos($val2,'.') + 1);
	$ext = strtolower($ext);
	$motofilename = substr($val2,0,strrpos($val2,'.'));
	$ransu = rand(1000, 9999);
	$dbfilename = "ms".time().$ransu.$key2;
//	$filename = "$uploaddir"."$motofilename"."."."$ext";
	$filename = "$uploaddir"."$dbfilename"."."."$ext";
	if($val2){
		$no ++;
//$body = $_FILES['uploadfile']['name']."\n".$_FILES['uploadfile']['tmp_name']."\n".$filename;
//mail("katsumata@yournet-jp.com","test","$body");
		if(!move_uploaded_file($_FILES['uploadfile']['tmp_name'][$key2],$filename)){
			$msg = "ファイルのアップロードに失敗しました。再度お試し下さい。ERROR_CODE:10985";
			$ybase->error("$msg");
		}
		if($filelist){
			$filelist .= ",";
			$filenamelist .= ",";
		}
		$filelist .= "\"".$filename."\"";
		$filenamelist .= "\"".$val2."\"";
	}
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
if(!$filelist){
	$filelist = 'null';
	$filenamelist = 'null';
}else{
	$filelist = "'{".$filelist."}'";
	$filenamelist = "'{".$filenamelist."}'";
}


$sql = "insert into message values($new_message_id,$ybase->my_employee_id,'$new_title','$new_mess',$filelist,$filenamelist,'now','1')";

$result = $ybase->sql($conn,$sql);

$subject = "新しいお知らせ(雄大ポータル)";


foreach($sel_emp as $key => $val){

	$sql = "insert into message_log values ($new_message_id,$val,'now','1')";
	$result = $ybase->sql($conn,$sql);

	$sql = "select a.employee_id,a.email,a.employee_name from employee_list as a,mail_send as b where a.employee_id = $val and a.employee_id = b.employee_id and b.cate = 1 and a.status = '1' and b.config = '1'";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
		list($q_employee_id,$q_email,$q_name) = pg_fetch_array($result,0);

		$body = "{$q_name}様\r\n\r\n雄大グループ業務管理ポータルに新しいお知らせが届いております。\r\n\r\n【{$new_title}】\r\n\r\n";
		$body .= "下記から確認してください。\r\n\r\nhttps://".$_SERVER['HTTP_HOST']."/yudaigroup/login.php?messageid=$new_message_id\r\n\r\n";
		$body .= "雄大グループ業務管理ポータル\r\n";

//		mail("$q_email","$subject","$body","From: yudai.system@yournet-jp.com");
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