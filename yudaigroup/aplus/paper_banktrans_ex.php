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
if(!isset($reflect_trans)){
	$reflect_trans = '';
}

$ybase->session_get();

///////////////////////////////////////////////
if(!preg_match("/^[0-9]+$/",$paper_banktrans_id)){
	$ybase->error("パラメーターエラーす。ERROR_CODE:1241");
}
if(!preg_match("/^[0-9]+$/",$company_id)){
	$ybase->error("パラメーターエラーす。ERROR_CODE:1242");
}
/////////////////////////////////////////


$n_Y = date("Y");
$n_M = date("n");
$n_D = date("j");

$bank_code = trim($bank_code);
$bank_kana = trim($bank_kana);
$bank_kana = str_replace("　"," ",$bank_kana);
$branch_code = trim($branch_code);
$branch_kana = trim($branch_kana);
$branch_kana = str_replace("　"," ",$branch_kana);
$type = trim($type);
$account_number = trim($account_number);
$account_name = trim($account_name);
$account_name = str_replace("　"," ",$account_name);
$new_flag = trim($new_flag);

if(!preg_match("/^[0-9]{4}$/",$bank_code)){
	$error_check=1;
	$bank_code_err=" is-invalid";
}else{
	$bank_code_err=" is-valid";
}
if(!preg_match("/^[0-9]{3}$/",$branch_code)){
	$error_check=1;
	$branch_code_err=" is-invalid";
}else{
	$branch_code_err=" is-valid";
}
if(!preg_match("/^[0-9]{1}$/",$type)){
	$error_check=1;
	$type_err=" is-invalid";
}else{
	$type_err=" is-valid";
}
if(!preg_match("/^[0-9]{1}$/",$new_flag)){
	$error_check=1;
	$new_flag_err=" is-invalid";
}else{
	$new_flag_err=" is-valid";
}
if(!preg_match("/^[0-9]{7}$/",$account_number)){
	$error_check=1;
	$account_number_err=" is-invalid";
}else{
	$account_number_err=" is-valid";
}

if(!preg_match("/^[A-Z0-9ｱ-ﾝﾞﾟ｢｣\(\)\/\-\.,\s]+$/u",$bank_kana)){
	$error_check=1;
	$bank_kana_err=" is-invalid";
}else{
	$bank_kana_err=" is-valid";
}
if(!preg_match("/^[A-Z0-9ｱ-ﾝﾞﾟ\｢\｣\(\)\/\-\.,\s]+$/u",$branch_kana)){
	$error_check=1;
	$branch_kana_err=" is-invalid";
}else{
	$branch_kana_err=" is-valid";
}
if(!preg_match("/^[A-Z0-9ｱ-ﾝﾞﾟ｢｣\(\)\/\-\.,\s]+$/u",$account_name)){
	$error_check=1;
	$account_name_err=" is-invalid";
}else{
	$account_name_err=" is-valid";
}

if($error_check == "1"){

$param = "paper_banktrans_id=$paper_banktrans_id&company_id=$company_id&bank_code=$bank_code&bank_kana=$bank_kana&branch_code=$branch_code&branch_kana=$branch_kana&type=$type&account_number=$account_number&account_name=$account_name&new_flag=$new_flag&bank_code_err=$bank_code_err&bank_kana_err=$bank_kana_err&branch_code_err=$branch_code_err&branch_kana_err=$branch_kana_err&type_err=$type_err&account_number_err=$account_number_err&account_name_err=$account_name_err&new_flag_err=$new_flag_err&error_check=$error_check&backscript=$backscript";

$JUMPSCRIPT = "aplus/paper_banktrans_cg.php";
header("Location: {$ybase->PATH}{$JUMPSCRIPT}?$param");
exit;
}
/////////////////////////////////////////////////////////////////////////////////////////

$conn = $ybase->connect(4);
$bank_kana = str_replace("ｰ","-",$bank_kana);
$branch_kana = str_replace("ｰ","-",$branch_kana);
$account_name = str_replace("ｰ","-",$account_name);

$bank_kana = pg_escape_string($bank_kana);
$branch_kana = pg_escape_string($branch_kana);
$account_name = pg_escape_string($account_name);

$sql = "update paper_banktrans set bank_code='$bank_code',bank_kana='$bank_kana',branch_code='$branch_code',branch_kana='$branch_kana',type='$type',account_number='$account_number',account_name='$account_name',new_flag='$new_flag',add_date='now' where company_id = $company_id and paper_banktrans_id = $paper_banktrans_id";

$result = $ybase->sql($conn,$sql);

if($backscript){
	$JUMPSCRIPT = "aplus/{$backscript}";
}else{
	$JUMPSCRIPT = "aplus/paper_banktrans_list.php";
}
header("Location: {$ybase->PATH}{$JUMPSCRIPT}");
exit;
////////////////////////////////////////////////
?>