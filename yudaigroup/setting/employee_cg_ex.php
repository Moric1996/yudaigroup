<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

if(!preg_match("/^[0-9]+$/",$cg_employee_id)){
	$ybase->error("パラメーターエラー");
}

$ybase->pm	= "";
$error_check=0;
$conn = $ybase->connect();

$new_kana_name = preg_replace('/　/', ' ', $new_kana_name);
$new_employee_num = trim($new_employee_num);
if(preg_match("/^[0-9]+$/i",$new_employee_num)){
	$new_employee_num = sprintf("%04d",$new_employee_num);
}
$new_employee_name = trim($new_employee_name);
$new_kana_name = trim($new_kana_name);
$new_position_name = trim($new_position_name);
$new_email = trim($new_email);
if(!preg_match("/^[A-Za-z0-9]+$/i",$new_employee_num)){
	$error_check=1;
	$employee_num_err=" is-invalid";
}else{
	$sql = "select employee_id from employee_list where employee_num='$new_employee_num' and employee_id <> $cg_employee_id and status = '1'";
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
	$sql = "select employee_id from employee_list where email='$new_email' and employee_id <> $cg_employee_id and status = '1'";
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
if(!$new_employee_name){
	$error_check=1;
	$employee_name_err=" is-invalid";
}else{
	$employee_name_err=" is-valid";
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
if($new_pass){
	if(!preg_match("/^[A-Za-z0-9]{6,20}$/i",$new_pass)){
		$error_check=1;
		$new_pass_err=" is-invalid";
	}else{
		$new_pass_err=" is-valid";
	if($new_pass != $new_pass_conf){
		$error_check=1;
		$new_pass_conf_err=" is-invalid";
	}else{
		$new_pass_conf_err=" is-valid";
		$new_pass2 = $new_pass;
	}
	}
}
if(!preg_match("/^[0-9]+$/i",$new_status)){
	$error_check=1;
	$status_err=" is-invalid";
}else{
	$status_err=" is-valid";
}
if($new_nodel_flag != 1){
	$new_nodel_flag = 0;
}

$param = "cg_employee_id=$cg_employee_id&new_employee_num=$new_employee_num&new_employee_name=$new_employee_name&new_kana_name=$new_kana_name&new_sex=$new_sex&new_company_id=$new_company_id&new_section_id=$new_section_id&new_employee_type=$new_employee_type&new_position_name=$new_position_name&new_position_class=$new_position_class&new_admin_auth=$new_admin_auth&new_email=$new_email&new_status=$new_status&new_nodel_flag=$new_nodel_flag&employee_num_err=$employee_num_err&employee_name_err=$employee_name_err&kana_name_err=$kana_name_err&sex_err=$sex_err&company_id_err=$company_id_err&section_id_err=$section_id_err&employee_type_err=$employee_type_err&position_name_err=$position_name_err&position_class_err=$position_class_err&admin_auth_err=$admin_auth_err&email_err=$email_err&new_pass_err=$new_pass_err&new_pass_conf_err=$new_pass_conf_err&status_err=$status_err";

if($error_check == 1){
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/employee_cg.php?$param");
exit;
}

///////////////////////////////

$sql = "update employee_list set employee_num='$new_employee_num',employee_name='$new_employee_name',kana_name='$new_kana_name',sex='$new_sex',company_id=$new_company_id,section_id='$new_section_id',employee_type=$new_employee_type,position_name='$new_position_name',position_class=$new_position_class,admin_auth=$new_admin_auth,email='$new_email',status='$new_status',nodel_flag = $new_nodel_flag where employee_id = $cg_employee_id";
$result = $ybase->sql($conn,$sql);

if($new_pass2){
$sql = "update employee_list set pass = crypt('$new_pass2',gen_salt('md5')) where employee_id = $cg_employee_id";
$result = $ybase->sql($conn,$sql);
}
$ybase->title = "従業員管理-従業員データ変更";

$ybase->HTMLheader();

$ybase->ST_PRI .= <<<HTML
<div class="container">

<p></p>
<p class="text-center">従業員管理</p>

<p></p>
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">従業員データ変更完了</div>
<div class="card-body">
<p></p>
<br><br>
<div class="text-center">
{$new_employee_name}さんの情報を変更しました</div>



<br><br>
<p></p><div class="text-center">

<a href="./user_list.php?page=$page">従業員一覧へ</a>
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