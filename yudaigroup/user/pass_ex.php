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
$kara_check=0;
$update_sql="";
$conn = $ybase->connect();
$new_email = trim($new_email);
$new_pass = trim($new_pass);
$new_pass2 = trim($new_pass2);
if($new_email){
if(!preg_match("/^[a-zA-Z0-9\.\!\#\$\%\&\'\*\+\/\=\?\^_\`\{\|\}\~\-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/",$new_email)){
	$error_check=1;
	$new_email_err=" is-invalid";
}else{
	$update_sql.="email='$new_email'";
	$kara_check=1;
}
}
if($new_pass && $new_pass2){
if(!preg_match("/^[A-Za-z0-9]{6,20}$/i",$new_pass)){
	$error_check=1;
	$new_pass_err=" is-invalid";
}elseif($new_pass != $new_pass2){
	$error_check=1;
	$new_pass2_err=" is-invalid";
}else{
	if($update_sql){
		$update_sql .= ",";
	}
	$update_sql.="pass = crypt('$new_pass', gen_salt('md5'))";
	$kara_check=1;
}
}

$param = "new_email=$new_email&new_email_err=$new_email_err&new_pass_err=$new_pass_err&new_pass2_err=$new_pass2_err";

if(($error_check == 1)||($kara_check == 0)){
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/pass_cg.php?$param");
exit;
}

///////////////////////////////
$sql = "update employee_list set $update_sql where employee_id = $ybase->my_employee_id";

$result = $ybase->sql($conn,$sql);
$param="";

$_SESSION['my_email'] = "$new_email";


$ybase->title = "ユーザー管理-MYアカウント管理";

$ybase->HTMLheader();

$ybase->ST_PRI .= <<<HTML
<div class="container">

<p></p>
<p class="text-center">MYアカウント管理</p>

<p></p>
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">パスワード変更完了</div>
<div class="card-body">
<p></p>
<br><br>
<div class="text-center">
変更完了しました</div>



<br><br>
<p></p><div class="text-center">

<a href="../portal/index.php">TOPへ</a>
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