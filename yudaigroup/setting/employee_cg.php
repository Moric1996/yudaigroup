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

if(!preg_match("/^[0-9]+$/",$cg_employee_id)){
	$ybase->error("パラメーターエラー");
}
if($ybase->my_admin_auth == "2"){
	$add_sql = " and company_id = {$ybase->my_company_id} and section_id in ({$ybase->section_group_list[$ybase->my_section_id]})";
}elseif($ybase->my_admin_auth == "3"){
	$add_sql = " and company_id = {$ybase->my_company_id} and section_id = '{$ybase->my_section_id}'";
}else{
	$add_sql = "";
}

$conn = $ybase->connect();
$sql = "select employee_id,employee_num,employee_name,kana_name,sex,company_id,section_id,employee_type,position_name,position_class,view_auth,edit_auth,admin_auth,email,add_date,status,nodel_flag from employee_list where employee_id = $cg_employee_id and status = '1'{$add_sql} order by employee_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("対象者がおりません");
}elseif($num > 1){
	$ybase->error("データに問題があります。管理者までお問い合わせください。");
}
if(!$error_check){
	list($new_employee_id,$new_employee_num,$new_employee_name,$new_kana_name,$new_sex,$new_company_id,$new_section_id,$new_employee_type,$new_position_name,$new_position_class,$new_view_auth,$new_edit_auth,$new_admin_auth,$new_email,$new_add_date,$new_status,$new_nodel_flag) = pg_fetch_array($result,0);
	$new_nodel_flag =trim($new_nodel_flag);
}
$ybase->title = "従業員管理 確認・変更";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("管理");

$ybase->ST_PRI .= <<<HTML
<div class="container">
<p></p>
<a href="./user_list.php?page=$page">従業員一覧へ</a>

<p></p>
<p class="text-center">従業員管理</p>

<p></p>
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">従業員データ確認・変更</div>
<div class="card-body">
<form action="employee_cg_ex.php" method="post" autocomplete="off">
<input type="hidden" name="cg_employee_id" value="$cg_employee_id">
<input type="hidden" name="page" value="$page">
<div class="text-center">
従業員データの変更
</div>


<p></p>
<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">社員番号 <span class="badge badge-danger">必須</span></label>
<div class="col-8 col-sm-4">
<input type="text" name="new_employee_num" value="$new_employee_num" class="form-control form-control-sm border-dark{$employee_num_err}" id="employee_num" pattern="^[a-zA-Z0-9]+$" required>
<div class="invalid-feedback">
使用文字に問題があるか、社員番号が既に使われています
</div>
</div><small class="text-secondary">※半角英数字のみ内</small>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">氏名 <span class="badge badge-danger">必須</span></label>
<div class="col-8 col-sm-4">
<input type="text" name="new_employee_name" value="$new_employee_name" class="form-control form-control-sm border-dark{$name_err}" id="employee_name" required>
<div class="invalid-feedback">
使用文字に問題があります
</div>
</div>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">氏名フリガナ</label>
<div class="col-8 col-sm-4">
<input type="text" name="new_kana_name" value="$new_kana_name" class="form-control form-control-sm border-dark{$kana_name_err}" id="kana_name">
<div class="invalid-feedback">
使用文字に問題があります
</div>
</div>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">性別 <span class="badge badge-danger">必須</span></label>
<div class="col-8 col-sm-4">
<select name="new_sex" id="sex" class="form-control form-control-sm border-dark{$sex_err}" required>
HTML;
foreach($ybase->sex_list as $key => $val){
if($key == $new_sex){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
<div class="invalid-feedback">
選択してください
</div>
</div>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">会社 <span class="badge badge-danger">必須</span></label>
<div class="col-8 col-sm-4">
<select name="new_company_id" id="company_id" class="form-control form-control-sm border-dark{$company_id_err}" required>
HTML;
foreach($ybase->company_list as $key => $val){
if($key == $new_company_id){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
<div class="invalid-feedback">
選択してください
</div>
</div>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">所属部署・店舗 <span class="badge badge-danger">必須</span></label>
<div class="col-8 col-sm-4">
<select name="new_section_id" id="section_id" class="form-control form-control-sm border-dark{$section_id_err}" required>
<option value="">選択してください</option>
HTML;
foreach($ybase->section_list as $key => $val){
if($key == $new_section_id){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
<div class="invalid-feedback">
選択してください
</div>
</div>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">雇用区分 <span class="badge badge-danger">必須</span></label>
<div class="col-8 col-sm-4">
<select name="new_employee_type" id="employee_type" class="form-control form-control-sm border-dark{$employee_type_err}" required>
<option value="">選択してください</option>
HTML;
foreach($ybase->employee_type_list as $key => $val){
if($key == $new_employee_type){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
<div class="invalid-feedback">
選択してください
</div>
</div>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">役職区分 <span class="badge badge-danger">必須</span></label>
<div class="col-8 col-sm-4">
<select name="new_position_class" id="position_class" class="form-control form-control-sm border-dark{$position_class_err}" required>
<option value="">選択してください</option>
HTML;
foreach($ybase->position_class_list as $key => $val){
if($key == $new_position_class){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
<div class="invalid-feedback">
選択してください
</div>
</div>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">役職名</label>
<div class="col-8 col-sm-4">
<input type="text" name="new_position_name" value="$new_position_name" class="form-control form-control-sm border-dark{$position_name_err}" id="position_name">
<div class="invalid-feedback">
使用文字に問題があります
</div>
</div>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">Eメールアドレス</label>
<div class="col-8 col-sm-4">
<input type="text" name="new_email" value="$new_email" class="form-control form-control-sm border-dark{$email_err}" id="email">
<div class="invalid-feedback">
メールアドレスの形式に問題があります
</div>
</div>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">管理者権限 <span class="badge badge-danger">必須</span></label>
<div class="col-8 col-sm-4">
<select name="new_admin_auth" id="admin_auth" class="form-control form-control-sm border-dark{$admin_auth_err}" required>
HTML;
foreach($ybase->admin_auth_list as $key => $val){
if(!$ybase->my_admin_auth){
	break;
}
if(($ybase->my_admin_auth == 3) && ($key < 3)){
	continue;
}
if(($ybase->my_admin_auth == 2) && ($key < 2)){
	continue;
}
if($key == $new_admin_auth){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
<div class="invalid-feedback">
選択してください
</div>
</div><small class="text-secondary">※従業員登録変更権限・全ページ閲覧編集権限</small>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">新しいパスワード</label>
<div class="col-8 col-sm-4">
<input type="password" name="new_pass" value="" class="form-control form-control-sm border-dark{$new_pass_err}" id="new_pass" placeholder="パスワードを変更する場合のみ入力" pattern="^[a-zA-Z0-9]{6,20}$" autocomplete="new-password">
<div class="invalid-feedback">
文字数もしくは使用文字に問題があります
</div>
</div><small class="text-secondary">※半角英数字のみ 6文字以上20文字以内</small>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">パスワード確認</label>
<div class="col-8 col-sm-4">
<input type="password" name="new_pass_conf" value="" class="form-control form-control-sm border-dark{$new_pass_conf_err}" id="new_pass_conf" placeholder="変更する場合、確認の為、同じパスワードを入力" pattern="^[a-zA-Z0-9]{6,20}$">
<div class="invalid-feedback">
入力した文字が違います
</div>
</div><small class="text-secondary">※半角英数字のみ 6文字以上20文字以内</small>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">在職状況 <span class="badge badge-danger">必須</span></label>
<div class="col-8 col-sm-4">
<select name="new_status" id="status" class="form-control form-control-sm border-dark{$status_err}" required>
HTML;
foreach($ybase->employee_status_list as $key => $val){
if($key == $new_status){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
<div class="invalid-feedback">
選択してください
</div>
</div>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">退職チェック対象</label>
<div class="col-8 col-sm-4">
<select name="new_nodel_flag" id="nodel_flag" class="form-control form-control-sm border-dark{$nodel_flag_err}">
HTML;
if($new_nodel_flag != 1){
	$new_nodel_flag = 0;
}
foreach($ybase->nodel_flag_list as $key => $val){
if($key == $new_nodel_flag){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
<div class="invalid-feedback">
選択してください
</div>
</div><small class="text-secondary">※PX2ファイルアップ時の退職チェック対象とするかどうか</small>
</div>


<p></p>
<br>
<button class="btn btn-secondary col-sm-2 offset-sm-4 border-dark" type="submit">変更</button>
<button class="btn btn-light col-sm-2 border-dark" type="reset">クリア</button>


</form>
</div>
</div>
<p></p>


</div>
<p></p>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>