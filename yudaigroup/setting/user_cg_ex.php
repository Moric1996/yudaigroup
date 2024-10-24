<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/sbase.inc');

$sbase = new sbase();
$sbase->session_get();

$sbase->pm	= "t_suser_id=$t_suser_id";
$error_check=0;
$conn = $sbase->connect();

if(!preg_match("/^[0-9]+$/i",$t_suser_id)){
	$sbase->error("アカウント情報がありません。");
}

$user_name = preg_replace('/　/', ' ', $user_name);
$user_name_en = preg_replace('/　/', ' ', $user_name_en);
$account = trim($account);
$user_email = trim($user_email);
$user_name = trim($user_name);
$user_name_en = trim($user_name_en);
if(!preg_match("/^[A-Za-z0-9]{6,20}$/i",$account)){
	$error_check=1;
	$account_err=" is-invalid";
}else{
	$sql = "select suser_id from contractor_school_user where account='$account' and status = '1' and suser_id <> $t_suser_id";
	$result = $sbase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
		$error_check=1;
		$account_err=" is-invalid";
	}else{
		$account_err=" is-valid";
	}
}
if(!preg_match("/^[a-zA-Z0-9\.\!\#\$\%\&\'\*\+\/\=\?\^_\`\{\|\}\~\-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/",$user_email)){
	$error_check=1;
	$user_email_err=" is-invalid";
}else{
	$sql = "select suser_id from contractor_school_user where user_email='$user_email' and status = '1' and suser_id <> $t_suser_id";
	$result = $sbase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
		$error_check=1;
		$user_email_err=" is-invalid";
	}else{
		$user_email_err=" is-valid";
	}
}
if(!$user_name){
	$error_check=1;
	$user_name_err=" is-invalid";
}else{
	$user_name_err=" is-valid";
}
if(!preg_match("/^[A-Za-z0-9\s]+$/i",$user_name_en)){
	$error_check=1;
	$user_name_en_err=" is-invalid";
}else{
	$user_name_en_err=" is-valid";
}
$param = "t_suser_id=$t_suser_id&account=$account&user_email=$user_email&user_name=$user_name&user_name_en=$user_name_en&account_err=$account_err&user_email_err=$user_email_err&user_name_err=$user_name_err&user_name_en_err=$user_name_en_err";
$addsql = "";
if($new_pass || $new_pass2){
	if(!preg_match("/^[A-Za-z0-9]{6,20}$/i",$new_pass)){
		$error_check=1;
		$pass_err=" is-invalid";
	}
	if($new_pass != $new_pass2){
		$error_check=1;
		$pass2_err=" is-invalid";
	}

	$param .= "&passon=1&pass_err=$pass_err&pass2_err=$pass2_err";
	$addsql = ",password=crypt('$new_pass', password)";
}

if($error_check == 1){
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/user_view.php?{$param}&error_check=$error_check");
exit;
}

///////////////////////////////

$sql = "update contractor_school_user set account='$account',user_name='$user_name',user_name_en='$user_name_en',user_email='$user_email'{$addsql} where school_id = $sbase->my_school_id and suser_id = $t_suser_id and status = '1'";
$result = $sbase->sql($conn,$sql);
$param="t_suser_id=$t_suser_id&dochange=ok";
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/user_view.php?$param");
exit;

////////////////////////////////////////////////
?>