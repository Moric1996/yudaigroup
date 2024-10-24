<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

$ybase->pm	= "";
$error_check=0;
$conn = $ybase->connect();

$new_kana_name = preg_replace('/　/', ' ', $new_kana_name);
$new_employee_num = trim($new_employee_num);
if(preg_match("/^[0-9]+$/i",$new_employee_num)){
$new_employee_num = sprintf("%04d",$new_employee_num);
}
$new_f_name = trim($new_f_name);
$new_g_name = trim($new_g_name);
$new_kana_name = trim($new_kana_name);
$new_position_name = trim($new_position_name);
$new_email = trim($new_email);
if(!preg_match("/^[A-Za-z0-9]+$/i",$new_employee_num)){
	$error_check=1;
	$employee_num_err=" is-invalid";
}else{
	$sql = "select employee_id from employee_list where employee_num='$new_employee_num' and status = '1'";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
		$error_check=1;
		$employee_num_err=" is-invalid";
	}else{
		$employee_num_err=" is-valid";
	}
}
if($new_email){
if(!preg_match("/^[a-zA-Z0-9\.\!\#\$\%\&\'\*\+\/\=\?\^_\`\{\|\}\~\-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/",$new_email)){
	$error_check=1;
	$email_err=" is-invalid";
}else{
	$sql = "select employee_id from employee_list where email='$new_email' and status = '1'";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
		$error_check=1;
		$email_err=" is-invalid";
	}else{
		$email_err=" is-valid";
	}
}
}
if(!$new_f_name){
	$error_check=1;
	$f_name_err=" is-invalid";
}else{
	$f_name_err=" is-valid";
}
if(!$new_g_name){
	$error_check=1;
	$g_name_err=" is-invalid";
}else{
	$g_name_err=" is-valid";
}
if(!preg_match("/^[mw]{1}$/i",$new_sex)){
	$error_check=1;
	$sex_err=" is-invalid";
}else{
	$sex_err=" is-valid";
}
if(!preg_match("/^[0-9]+$/i",$new_company_id)){
	$error_check=1;
	$company_id_err=" is-invalid";
}else{
	$company_id_err=" is-valid";
}
if(!preg_match("/^[0-9]+$/i",$new_section_id)){
	$error_check=1;
	$section_id_err=" is-invalid";
}else{
	$section_id_err=" is-valid";
}
if(!preg_match("/^[0-9]+$/i",$new_employee_type)){
	$error_check=1;
	$employee_type_err=" is-invalid";
}else{
	$employee_type_err=" is-valid";
}
if(!preg_match("/^[0-9]+$/i",$new_position_class)){
	$error_check=1;
	$position_class_err=" is-invalid";
}else{
	$position_class_err=" is-valid";
}
if(!preg_match("/^[0-9]+$/i",$new_admin_auth)){
	$error_check=1;
	$admin_auth_err=" is-invalid";
}else{
	$admin_auth_err=" is-valid";
}






$param = "new_employee_num=$new_employee_num&new_f_name=$new_f_name&new_g_name=$new_g_name&new_kana_name=$new_kana_name&new_sex=$new_sex&new_company_id=$new_company_id&new_section_id=$new_section_id&new_employee_type=$new_employee_type&new_position_name=$new_position_name&new_position_class=$new_position_class&new_admin_auth=$new_admin_auth&new_email=$new_email&employee_num_err=$employee_num_err&f_name_err=$f_name_err&g_name_err=$g_name_err&kana_name_err=$kana_name_err&sex_err=$sex_err&company_id_err=$company_id_err&section_id_err=$section_id_err&employee_type_err=$employee_type_err&position_name_err=$position_name_err&position_class_err=$position_class_err&admin_auth_err=$admin_auth_err&email_err=$email_err";

if($error_check == 1){
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/new_employee.php?$param");
exit;
}

///////////////////////////////

$sql = "select nextval('employee_list_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:13002");
}
$new_employee_id = pg_fetch_result($result,0,0);

$sql = "insert into employee_list (employee_id,employee_num,employee_name,kana_name,sex,company_id,section_id,employee_type,position_name,position_class,admin_auth,email,pass,add_date,status) values ($new_employee_id,'$new_employee_num','$new_f_name $new_g_name','$new_kana_name','$new_sex',$new_company_id,'$new_section_id',$new_employee_type,'$new_position_name',$new_position_class,$new_admin_auth,'$new_email','18da54','now','1')";
$result = $ybase->sql($conn,$sql);

$sql = "update employee_list set pass = crypt('$new_pass',gen_salt('md5')) where employee_id = $new_employee_id";
$result = $ybase->sql($conn,$sql);

$ybase->title = "ユーザー管理-新規ユーザーアカウント作成";

$ybase->HTMLheader();

$ybase->ST_PRI .= <<<HTML
<div class="container">

<p></p>
<p class="text-center">新規ユーザーアカウント作成</p>

<p></p>
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">ユーザーアカウント作成完了</div>
<div class="card-body">
<p></p>
<br><br>
<div class="text-center">
新しいアカウントの作成を完了しました</div>



<br><br>
<p></p><div class="text-center">

<a href="./new_employee.php">新規ユーザーアカウント作成TOPへ</a>
</div>
</div>
</div>
</div>
<p></p>
HTML;

$ybase->HTMLfooter();
$ybase->priout();


////////////////////////////////////////////////
?>