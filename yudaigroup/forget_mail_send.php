<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('./inc/ybase.inc');

$ybase = new ybase();
$ybase->title = "雄大グループ SYSTEM ポータル-パスワードを忘れた方";
$error_check=0;

if(!preg_match("/^[a-zA-Z0-9\.\!\#\$\%\&\'\*\+\/\=\?\^_\`\{\|\}\~\-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/",$email)){
	$error_check=1;
	$email_err=" is-invalid";
}
$conn = $ybase->connect();

$sql = "select employee_id from employee_list where email='$email' and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	$error_check=1;
	$email_err=" is-invalid";
}else{
	$employee_id = pg_fetch_result($result,0,0);
}
if($error_check == 1){
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/forget.php?{$param}&error_check=$error_check&email_err=$email_err&email=$email");
exit;
}
$basechar = time().rand(1000,9999);
$onepass = base_convert($basechar, 10, 36); 
$dy = rand(100000,999999);

$sql = "select nextval('forget_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:00101");
}
$new_forget_id = pg_fetch_result($result,0,0);

$sql = "insert into forget values ($new_forget_id,'s',$employee_id,'$onepass','now','1')";
$result = $ybase->sql($conn,$sql);

$url = "https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/forget_pcg.php?fi=$new_forget_id&op=$onepass&tp=s&dy=$dy";

$body ="雄大グループ SYSTEM ポータル\n\nパスワードを変更します。\n下記よりパスワードを変更してログインしてください。\n\n$url\n\nYUDAI GROUP SYSTEM";
$to = $email;
$from = "katsumata@yournet-jp.com";
$subject = "YUDAI GROUP SYSTEM";

mail("$to","$subject","$body","From: $from");

$ybase->HTMLheader();

$ybase->ST_PRI .= <<<HTML
<header>
<nav class="navbar navbar-expand-md navbar-dark" style="background-color: #63baa8;">
<a class="navbar-brand" href="{$ybase->PATH}login.php">雄大グループ SYSTEM ポータル</a>

</nav>
</header>
<br>
<br>
<br>
<br>
<br>
<p></p>
<p></p>
<p></p>

<p></p>
<div class="card border border-dark mx-auto" style="max-width: 40rem;">
<div class="card-body">
<form action="login.php" method="post">
メールを送信しました。受信したメールからパスワードを設定し直してください。


<p></p>

<button class="btn btn-secondary col-sm-4 offset-sm-4 border-dark" type="submit">ログインページへ</button>

</form>
</div>
</div>
<p></p>
<br>
<br>
<br>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>