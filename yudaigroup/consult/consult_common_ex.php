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
$new_common_title = trim($new_common_title);
$new_common_mess = trim($new_common_mess);
if(!$new_common_title){
	$ybase->error("タイトルを入力してください");
}
if(!$new_common_mess){
	$ybase->error("投稿内容を入力してください");
}
if(!preg_match("/^[0-9]+$/",$new_common_category)){
	$ybase->error("カテゴリーを選択してください");
}

$new_common_title = addslashes($new_common_title);
$new_common_mess = addslashes($new_common_mess);

if(!preg_match("/^[0-9]+$/",$common_consult_id)){
	$common_consult_id = "null";
}
if($keytab){
$new_keytab = "'{";
	$i=0;
	foreach($keytab as $key => $val){
		if($i > 0){
			$new_keytab .= ",";
		}
		$new_keytab .= "$key";
		$i++;
	}
	$new_keytab .= "}'";
}else{
	$new_keytab = "null";
}

$sql = "select nextval('common_info_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:23002");
}
$new_common_id = pg_fetch_result($result,0,0);


$sql = "insert into common_info values($new_common_id,$common_consult_id,$ybase->my_company_id,$new_common_category,$ybase->my_employee_id,$new_keytab,'$new_common_title','$new_common_mess','now','1')";

$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/consult_manage.php?select_tab=3");
exit;


////////////////////////////////////////////////
?>