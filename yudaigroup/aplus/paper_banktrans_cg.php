<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
if(isset($_POST)){
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
}
if(isset($_GET)){
	foreach($_GET as $key => $value){
		${$key} = $value;
	}
}
///////////////////////////////////////////////////////////////
include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/aplus.inc');

$ybase = new ybase();
$aplus = new aplus();

////////////////////////////////////////////////define
if(!isset($error_check)){
	$error_check = '';
}
if(!isset($bank_code_err)){
	$bank_code_err = '';
}
if(!isset($branch_code_err)){
	$branch_code_err = '';
}
if(!isset($branch_kana_err)){
	$branch_kana_err = '';
}
if(!isset($bank_kana_err)){
	$bank_kana_err = '';
}
if(!isset($account_name_err)){
	$account_name_err = '';
}
if(!isset($account_number_err)){
	$account_number_err = '';
}
if(!isset($customer_num_err)){
	$customer_num_err = '';
}
if(!isset($type_err)){
	$type_err = '';
}
if(!isset($new_flag_err)){
	$new_flag_err = '';
}


$ybase->session_get();

///////////////////////////////////////////////
if(!preg_match("/^[0-9]+$/",$company_id)){
	$ybase->error("パラメーターエラーす。ERROR_CODE:1232");
}
/////////////////////////////////////////

$n_Y = date("Y");
$n_M = date("n");
$n_D = date("j");

$conn = $ybase->connect(4);

$sql = "select company_id,service_id,original_id,customer_num,company_name,company_name_kana,email from company_list where company_id = $company_id and status = '1'";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データがありません。ERROR_CODE:1231");
}
if($num > 1){
	$ybase->error("データが重複しています。ERROR_CODE:1241");
}
	list($q_company_id,$q_service_id,$q_original_id,$q_customer_num,$q_company_name,$q_company_name_kana,$q_email) = pg_fetch_array($result,0);
	$q_company_id = trim($q_company_id);
	$q_service_id = trim($q_service_id);
	$q_original_id = trim($q_original_id);
	$q_customer_num = trim($q_customer_num);
	$q_company_name = trim($q_company_name);
	$q_company_name_kana = trim($q_company_name_kana);
	$q_email = trim($q_email);

if(preg_match("/^[0-9]+$/",$paper_banktrans_id)){
	$add_sql = "and paper_banktrans_id = $paper_banktrans_id";
}else{
	$add_sql = "";
}
$sql = "select paper_banktrans_id,bank_code,bank_kana,branch_code,branch_kana,type,account_number,account_name,new_flag,to_char(add_date,'YYYY/MM/DD'),status from paper_banktrans where company_id = $company_id and status > '0'{$add_sql}";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num > 1){
	$ybase->error("データが重複しています。ERROR_CODE:1243");
}
if(!$num){
	if($action == "new"){
		$sql2 = "select nextval('paper_banktrans_id_seq')";
		$result2 = $ybase->sql($conn,$sql2);
		$num2 = pg_num_rows($result2);
		if(!$num2){
			$ybase->error("データベースエラーです。ERROR_CODE:21232");
		}
		$q_paper_banktrans_id = pg_fetch_result($result2,0,0);
		$sql2 ="insert into paper_banktrans (paper_banktrans_id,company_id,bank_code,bank_kana,branch_code,branch_kana,type,account_number,account_name,new_flag,add_date,status) values ($q_paper_banktrans_id,$company_id,'','','','','','','','1','now','1')";
		$result2 = $ybase->sql($conn,$sql2);
	}else{
		$ybase->error("データがありません。ERROR_CODE:1233");
	}
}else{
	list($q_paper_banktrans_id,$q_bank_code,$q_bank_kana,$q_branch_code,$q_branch_kana,$q_type,$q_account_number,$q_account_name,$q_new_flag,$q_add_date,$q_status) = pg_fetch_array($result,0);
	$q_bank_code = trim($q_bank_code);
	$q_bank_kana = trim($q_bank_kana);
	$q_branch_code = trim($q_branch_code);
	$q_branch_kana = trim($q_branch_kana);
	$q_type = trim($q_type);
	$q_account_number = trim($q_account_number);
	$q_account_name = trim($q_account_name);
	$q_new_flag = trim($q_new_flag);
	$q_add_date = trim($q_add_date);
	$q_status = trim($q_status);
}
	if(!$q_new_flag){
		$q_new_flag = 0;
	}

if($error_check == 1){
	$q_bank_code = $bank_code;
	$q_bank_kana = $bank_kana;
	$q_branch_code = $branch_code;
	$q_branch_kana = $branch_kana;
	$q_type = $type;
	$q_account_number = $account_number;
	$q_account_name = $account_name;
	$q_new_flag = $new_flag;
}

$ybase->title = "口座振替情報変更";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("口座振替情報変更");
$vavi_pri = $aplus->navi_head_pri();

$ybase->ST_PRI .= <<<HTML
{$vavi_pri}

<div class="container">
<p></p>

<p></p>

<div class="card border border-dark mx-auto">
<div class="card-header border-dark bg-light text-center">口座振替情報変更</div>
<div class="card-body">
<form action="paper_banktrans_ex.php" method="post">
<input type="hidden" name="paper_banktrans_id" value="$q_paper_banktrans_id">
<input type="hidden" name="company_id" value="$company_id">
<input type="hidden" name="backscript" value="$backscript">

<p></p>
<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">契約者名 </label>
<div class="col-8 col-sm-4">
$q_company_name
</div>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">識別ID </label>
<div class="col-8 col-sm-4">
$q_original_id
</div>
</div>


<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">顧客番号 </label>
<div class="col-8 col-sm-4">
$q_customer_num
</div>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">銀行コード</label>
<div class="col-8 col-sm-4">
<input type="text" name="bank_code" value="$q_bank_code" class="form-control form-control-sm border-dark{$bank_code_err}" id="bank_code" pattern="^[0-9]{4}$" maxlength="4" required>
<div class="invalid-feedback">
半角数字4桁で入力してください
</div>
</div><small>※半角数字4桁</small>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">銀行名</label>
<div class="col-8 col-sm-4">
<input type="text" name="bank_kana" value="$q_bank_kana" class="form-control form-control-sm border-dark{$bank_kana_err}" id="bank_kana" pattern="^[A-Z0-9\uFF66-\uFF9F\.,｢｣\(\)\/\-\s]{1,30}$" maxlength="15" required>
<div class="invalid-feedback">
半角カナで入力してください
</div>
</div><small>※半角カナ大英数字,.｢｣()/-(最大15字)</small>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">支店コード</label>
<div class="col-8 col-sm-4">
<input type="text" name="branch_code" value="$q_branch_code" class="form-control form-control-sm border-dark{$branch_code_err}" id="branch_code" pattern="^[0-9]{3}$" maxlength="3" required>
<div class="invalid-feedback">
半角3桁数字で入力してください
</div>
</div><small>※半角3桁数字</small>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">支店名</label>
<div class="col-8 col-sm-4">
<input type="text" name="branch_kana" value="$q_branch_kana" class="form-control form-control-sm border-dark{$branch_kana_err}" id="branch_kana" pattern="^[A-Z0-9\uFF66-\uFF9F\.,｢｣\(\)\/\-\s]{1,30}$" maxlength="15" required>
<div class="invalid-feedback">
使用文字に問題があります
</div>
</div><small>※半角カナ大英数字,.｢｣()/-(最大15字)</small>

</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">口座種類</label>
<div class="col-8 col-sm-4">
<select name="type" class="form-control form-control-sm border-dark{$type_err}" id="type" required>
<option value="">選択してください</option>
HTML;
foreach($bank_type_list as $key => $val){
if($key == $q_type){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"{$selected}>$val</option>
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
<label class="col-4 col-sm-2 offset-md-2">口座番号</label>
<div class="col-8 col-sm-4">
<input type="text" name="account_number" value="$q_account_number" class="form-control form-control-sm border-dark" id="account_number"  pattern="^[0-9]{7}$" maxlength="7" required>
<div class="invalid-feedback">
半角7桁数字で入力してください
</div>
</div><small>※半角7桁数字</small>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">口座名義</label>
<div class="col-8 col-sm-4">
<input type="text" name="account_name" value="$q_account_name" class="form-control form-control-sm border-dark{$account_name_err}" pattern="^[A-Z0-9\uFF66-\uFF9F\.,｢｣\(\)\/\-\s]{1,30}$" id="account_name" maxlength="30">
<div class="invalid-feedback">
使用文字に問題があります
</div>
</div><small>※半角カナ大英数字,.｢｣()/-(最大30字)</small>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-2">新規コード</label>
<div class="col-8 col-sm-4">
<select name="new_flag" class="form-control form-control-sm border-dark{$new_flag_err}" id="new_flag" required>
<option value="">選択してください</option>
HTML;
foreach($bank_new_list as $key => $val){
if($key == $q_new_flag){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"{$selected}>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
<div class="invalid-feedback">
選択してください
</div>
</div>
</div>
HTML;

$ybase->ST_PRI .= <<<HTML
<p></p>


<p></p>
<br>
<button class="btn btn-info col-sm-2 offset-sm-5 border-dark" type="submit">変更</button>

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