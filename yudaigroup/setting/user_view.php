<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/sbase.inc');

$sbase = new sbase();
$sbase->session_get();

include('./user_menu.inc');

/////////////////////////////////////////

if($sbase->my_sauthority != "1"){
	$sbase->error("権限がありません");
}
if(!preg_match("/^[0-9]+$/i",$t_suser_id)){
	$sbase->error("アカウント情報がありません。");
}

$conn = $sbase->connect();
$sql = "select account,password,user_name,user_name_en,user_email,authority,add_date from contractor_school_user where school_id = $sbase->my_school_id and suser_id = $t_suser_id and status = '1'";
$result = $sbase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	$sbase->error("アカウントが特定できません。ERROR_CODE:90001");
}
	list($q_account,$q_password,$q_user_name,$q_user_name_en,$q_user_email,$q_authority,$q_add_date) = pg_fetch_array($result,0);

if($error_check){
if($account){
	$q_account = $account;
}
if($user_name){
	$q_user_name = $user_name;
}
if($user_name_en){
	$q_user_name_en = $user_name_en;
}
if($user_email){
	$q_user_email = $user_email;
}
$msg = "<div class=\"text-center text-danger\">入力情報に問題があります</div>";
}elseif($dochange){
$msg = "<div class=\"text-center text-danger\">変更完了</div>";
}
if($passon == "1"){
$JS1_PRI = <<<HTML

		$("#blockBtn").hide();
HTML;
}else{
$JS1_PRI = <<<HTML

		$("#passinput1").hide();
		$("#passinput2").hide();
		$("#nonBtn").hide();
HTML;
}

$sbase->title = "ユーザー管理-ユーザーアカウント管理";

$sbase->HTMLheader();
$sbase->navi(8);

$sbase->ST_PRI .= <<<HTML
<script type="text/javascript">
<!--
$(function() {
$JS1_PRI
	$("#nonBtn").click(function() {
// 非表示に設定
		$("#passinput1").hide();
		$("#passinput2").hide();
		$("#nonBtn").hide();
		$("#blockBtn").show();
	});
	$("#blockBtn").click(function() {
// 表示に設定
		$("#passinput1").show();
		$("#passinput2").show();
		$("#blockBtn").hide();
		$("#nonBtn").show();
	});
});
//-->
</script>

<div class="container">
$MENU_PRI

<p></p>
<p class="text-center">ユーザーアカウント管理</p>

<p></p>
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">ユーザーアカウント変更</div>
<div class="card-body">
<form action="user_cg_ex.php" method="post">
<input type="hidden" name="t_suser_id" value="$t_suser_id">
<div class="text-center">
ユーザーアカウント情報を変更します。
</div>

$msg
<p></p>
<div class="form-group row ">
<label for="basedataFormInput1" class="col-6 col-sm-3 offset-md-2">ログイン用アカウント名</label>
<div class="col-8 col-sm-3">
<input type="text" name="account" value="$q_account" class="form-control form-control-sm border-dark{$account_err}" id="basedataFormInput1" pattern="^[a-zA-Z0-9]{6,20}$" required>
<div class="invalid-feedback">
文字数もしくは使用文字に問題があるか、アカウント名が既に使われています
</div>
</div><small class="text-secondary">※半角英数字のみ 6文字以上20文字以内</small>
</div>


<div class="form-group row ">
<label for="basedataFormInput2" class="col-6 col-sm-3 offset-md-2">Eメールアドレス</label>
<div class="col-8 col-sm-3">
<input type="text" name="user_email" value="$q_user_email" class="form-control form-control-sm border-dark{$user_email_err}" id="basedataFormInput2" required>
<div class="invalid-feedback">
メールアドレスの形式に問題があります
</div>
</div>
</div>

<div class="form-group row ">
<label for="basedataFormInput3" class="col-6 col-sm-3 offset-md-2">氏　名</label>
<div class="col-8 col-sm-3">
<input type="text" name="user_name" value="$q_user_name" class="form-control form-control-sm border-dark{$user_name_err}" id="basedataFormInput3" required>
</div>
</div>

<div class="form-group row ">
<label for="basedataFormInput4" class="col-6 col-sm-3 offset-md-2">氏　名 (英語表記)</label>
<div class="col-8 col-sm-3">
<input type="text" name="user_name_en" value="$q_user_name_en" class="form-control form-control-sm border-dark{$user_name_en_err}" id="basedataFormInput4">
<div class="invalid-feedback">
半角英数字以外は使えません
</div>
</div>
</div>

<div class="form-group row" id="nonBtn"><label class="col-6 col-sm-3 offset-md-2">
<button type="button" class="btn btn-outline-primary btn-sm">パスワードは変更しない</button></label></div>
<div class="form-group row" id="blockBtn"><label class="col-6 col-sm-3 offset-md-2">
<button type="button" class="btn btn-outline-primary btn-sm">パスワードも変更する</button></label></div>

<div class="form-group row" id="passinput1">
<label for="basedataFormInput10" class="col-6 col-sm-3 offset-md-2">パスワード</label>
<div class="col-8 col-sm-3">
<input type="password" name="new_pass" value="$new_pass" class="form-control form-control-sm border-dark{$pass_err}" id="basedataFormInput10" pattern="^[a-zA-Z0-9]{6,20}$">
<div class="invalid-feedback">
文字数もしくは使用文字に問題があります
</div>
</div><small class="text-secondary">※半角英数字のみ 6文字以上20文字以内</small>
</div>

<div class="form-group row " id="passinput2">
<label for="basedataFormInput11" class="col-6 col-sm-3 offset-md-2">パスワード再入力</label>
<div class="col-8 col-sm-3">
<input type="password" name="new_pass2" value="$new_pass2" class="form-control form-control-sm border-dark{$pass2_err}" id="basedataFormInput11" pattern="^[a-zA-Z0-9]{6,20}$">
<div class="invalid-feedback">
パスワードに違いがあります
</div>
</div><small class="text-secondary">※半角英数字のみ 6文字以上20文字以内</small>
</div>


<p></p>
<br>
<button class="btn btn-secondary col-sm-2 offset-sm-4 border-dark" type="submit">変更</button>
<button class="btn btn-light col-sm-2 border-dark" type="reset">クリア</button>


</form>
</div>
</div>
</div>
<p></p>
HTML;

$sbase->HTMLfooter();
$sbase->priout();
////////////////////////////////////////////////
?>