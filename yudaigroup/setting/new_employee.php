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


$ybase->title = "ユーザー管理-新規ユーザーアカウント作成";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("管理");

$ybase->ST_PRI .= <<<HTML
<div class="container">
<p></p>
<a href="./user_list.php">従業員一覧へ</a>

<p></p>
<p class="text-center">新規ユーザーアカウント作成テスト</p>

<p></p>
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">新規ユーザーアカウント作成</div>
<div class="card-body">
<form action="new_employee_ex.php" method="post">
<div class="text-center">
システム利用者の新しいユーザーアカウントを作成します。
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
<div class="col-4 col-sm-2">
氏<input type="text" name="new_f_name" value="$new_f_name" class="form-control form-control-sm border-dark{$f_name_err}" id="f_name" required>
<div class="invalid-feedback">
使用文字に問題があります
</div>
</div>
<div class="col-4 col-sm-2">
名<input type="text" name="new_g_name" value="$new_g_name" class="form-control form-control-sm border-dark{$g_name_err}" id="g_name" required>
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
</div><small class="text-secondary">※ユーザー登録変更権限・全ページ閲覧編集権限</small>
</div>

<p></p>
<br>
<button class="btn btn-secondary col-sm-2 offset-sm-4 border-dark" type="submit">作成</button>
<button class="btn btn-light col-sm-2 border-dark" type="reset">クリア</button>


</form>
</div>
</div>
<p></p>


<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">PX2ファイルからアップロード</div>
<div class="card-body">
アップできるファイル形式<br>
項目名として、1行目に社員番号,社員氏名,氏名フリガナ,性別,生年月日,部課,役社員区分,入社日が入っているCSVファイルです。<br>
※項目名が1文字でも上記と違うと情報が取得できません。変更する場合は勝又まで連絡ください。<br>
※新しい部課は登録できませんので、勝又まで連絡ください。<br>
※社員番号、社員氏名は必須です。<br>
※項目の順番は関係ありません。<br>
※他の項目が入っていても問題ありません。(反映されるデータは社員番号,社員氏名,氏名フリガナ,性別,生年月日,部課,役社員区分,入社日です)<br>
※社員番号で個人の特定をしています。氏名が同じでも社員場合が違うと重複して登録されますのでご注意ください。<br><br><br>



<form action="./px_up2.php" method="post" enctype="multipart/form-data" id="form2">

<div class="form-row">
<div class="form-group col-sm-4 offset-sm-4">
 <input type="hidden" name="MAX_FILE_SIZE" value="50000000">
<input type="file" name="jinjerfile" class="form-control-file form-control-sm" id="jinjerfile">
</div>
<div class="text-right">

<button class="btn btn-primary btn-sm" type="submit">送信</button> <button class="btn btn-secondary btn-sm" type="reset">クリア</button>
</div>
</div>

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