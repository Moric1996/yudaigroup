<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('./inc/ybase.inc');

$ybase = new ybase();
$error_check=0;

if(!preg_match("/^[0-9]+$/",$fi)){
	$ybase->error("パラメーターエラー。ERROR_CODE:00121");
}
if(!preg_match("/^[a-zA-Z0-9]+$/i",$op)){
	$ybase->error("パラメーターエラー。ERROR_CODE:00122");
}
if(!preg_match("/^[a-zA-Z0-9]+$/i",$tp)){
	$ybase->error("パラメーターエラー。ERROR_CODE:00123");
}
if(!preg_match("/^[a-zA-Z0-9]+$/i",$dy)){
	$ybase->error("パラメーターエラー。ERROR_CODE:00124");
}

$ybase->title = "雄大グループ SYSTEM ポータル-パスワード変更";

$conn = $ybase->connect();

$sql = "select user_id from forget where forget_id = $fi and type = '$tp' and onetime_pass = '$op' and add_date > now() - interval '1 hour' and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	$ybase->error("有効期限が切れているかパラメーターに問題があります。最初からやり直してください。ERROR_CODE:00125");
}
$user_id = pg_fetch_result($result,0,0);

$tablename = "employee_list";

$error_check=0;

if(!preg_match("/^[A-Za-z0-9]{6,20}$/i",$new_pass)){
	$error_check=1;
	$new_pass_err=" is-invalid";
}
if($new_pass != $new_pass2){
	$error_check=1;
	$new_pass2_err=" is-invalid";
}


$param = "fi=$fi&op=$op&tp=$tp&dy=$dy&new_pass_err=$new_pass_err&new_pass2_err=$new_pass2_err";

if($error_check == 1){
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/forget_pcg.php?$param");
exit;
}

$sql = "update $tablename set pass = crypt('$new_pass', gen_salt('md5')) where employee_id = $user_id";
$result = $ybase->sql($conn,$sql);


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
<div class="card-header border-dark alert-info text-center">パスワード変更完了</div>
<div class="card-body">
<form action="login.php" method="post">
パスワードを変更しました。


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