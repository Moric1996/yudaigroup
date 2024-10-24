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
$sql = "select pass,email from employee_list where employee_id = $ybase->my_employee_id and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	$ybase->error("アカウントが特定できません。ERROR_CODE:90001");
}
	list($q_pass,$q_email) = pg_fetch_array($result,0);

$ybase->title = "マイアカウント";

if(!$new_email_err){
	$new_email = $q_email;
}

$ybase->HTMLheader();

$ybase->ST_PRI .= $ybase->header_pri($ybase->title);

$ybase->ST_PRI .= <<<HTML
<div class="container">
<p></p>
<p class="text-center">マイアカウント管理</p>
<p></p>
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">登録情報変更</div>
<div class="card-body">
<form action="pass_ex.php" method="post">
<div class="text-center">
{$ybase->my_name} 様<br><br>
Eメールアドレス及びパスワード変更ができます。</div>
<p></p>
<div class="form-group row ">
<label for="basedataFormInput1" class="col-6 col-sm-3 offset-md-1">Eメールアドレス</label>
<div class="col-8 col-sm-4">
<input type="text" name="new_email" value="$new_email" class="form-control form-control-sm border-dark{$new_email_err}" id="basedataFormInput1" placeholder="">
<div class="invalid-feedback">
メールアドレスの形式が正しくありません
</div>
</div>
</div>

<div class="form-group row ">
<label for="basedataFormInput2" class="col-6 col-sm-3 offset-md-1">新しいパスワード</label>
<div class="col-8 col-sm-4">
<input type="password" name="new_pass" value="" class="form-control form-control-sm border-dark{$new_pass_err}" id="basedataFormInput2" placeholder="パスワード変更する場合のみ入力" pattern="^[a-zA-Z0-9]{6,20}$">
<div class="invalid-feedback">
文字数もしくは使用文字に問題があります
</div>
</div><small class="text-secondary">※半角英数字のみ 6文字以上20文字以内</small>
</div>

<div class="form-group row ">
<label for="basedataFormInput3" class="col-6 col-sm-3 offset-md-1">新しいパスワード再入力</label>
<div class="col-8 col-sm-4">
<input type="password" name="new_pass2" value="" class="form-control form-control-sm border-dark{$new_pass2_err}" id="basedataFormInput3" placeholder="パスワード変更する場合のみ入力" pattern="^[a-zA-Z0-9]{6,20}$">
<div class="invalid-feedback">
入力された新しいパスワードに相違があります
</div>
</div><small class="text-secondary">※半角英数字のみ 6文字以上20文字以内</small>
</div>

<p></p>

<button class="btn btn-secondary col-sm-2 offset-sm-4 border-dark" type="submit">変更</button>
<button class="btn btn-light col-sm-2 border-dark" type="reset">クリア</button>


</form>
</div>
</div>
</div>
<p></p>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>