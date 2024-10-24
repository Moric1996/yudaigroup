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
	$ybase->error("パラメーターエラー。ERROR_CODE:00111");
}
if(!preg_match("/^[a-zA-Z0-9]+$/i",$op)){
	$ybase->error("パラメーターエラー。ERROR_CODE:00112");
}
if(!preg_match("/^[a-zA-Z0-9]+$/i",$tp)){
	$ybase->error("パラメーターエラー。ERROR_CODE:00113");
}
if(!preg_match("/^[a-zA-Z0-9]+$/i",$dy)){
	$ybase->error("パラメーターエラー。ERROR_CODE:00114");
}

$ybase->title = "雄大グループ SYSTEM ポータル-パスワード変更";

$conn = $ybase->connect();

$sql = "select user_id from forget where forget_id = $fi and type = '$tp' and onetime_pass = '$op' and add_date > now() - interval '1 hour' and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	$ybase->error("有効期限が切れているかパラメーターに問題があります。最初からやり直してください。ERROR_CODE:00115");
}
$user_id = pg_fetch_result($result,0,0);

$tablename = "employee_list";



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
<div class="card-header border-dark alert-info text-center">パスワード変更</div>
<div class="card-body">
<form action="forget_pcg_ex.php" method="post">
<input type="hidden" name="fi" value="$fi">
<input type="hidden" name="op" value="$op">
<input type="hidden" name="tp" value="$tp">
<input type="hidden" name="dy" value="$dy">
<p></p>
<div class="text-center">新しいパスワードを設定してください。</div>
<p></p>

<div class="form-group row ">
<label for="basedataFormInput2" class="col-6 col-sm-4 offset-md-1">新しいパスワード</label>
<div class="col-8 col-sm-5">
<input type="password" name="new_pass" value="" class="form-control form-control-sm border-dark{$new_pass_err}" id="basedataFormInput2" placeholder="Enter a new password" pattern="^[a-zA-Z0-9]{6,20}$" required><small class="text-secondary">※半角英数字のみ 6文字以上20文字以内</small>
<div class="invalid-feedback">
文字数もしくは使用文字に問題があります
</div>
</div>
</div>

<div class="form-group row ">
<label for="basedataFormInput3" class="col-6 col-sm-4 offset-md-1">新しいパスワード再入力</label>
<div class="col-8 col-sm-5">
<input type="password" name="new_pass2" value="" class="form-control form-control-sm border-dark{$new_pass2_err}" id="basedataFormInput3" placeholder="Confirm your new password" pattern="^[a-zA-Z0-9]{6,20}$" required>
<div class="invalid-feedback">
入力された新しいパスワードに相違があります
</div>
</div>
</div>

<p></p>

<button class="btn btn-secondary col-sm-3 offset-sm-4 border-dark" type="submit">変更</button>

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