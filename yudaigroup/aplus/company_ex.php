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
if(!isset($original_id_err)){
	$original_id_err = '';
}
if(!isset($customer_num_err)){
	$customer_num_err = '';
}
if(!isset($company_name_err)){
	$company_name_err = '';
}
if(!isset($company_name_kana_err)){
	$company_name_kana_err = '';
}
if(!isset($email_err)){
	$email_err = '';
}

$ybase->session_get();

///////////////////////////////////////////////
if(!preg_match("/^[0-9]+$/",$company_id)){
	$ybase->error("パラメーターエラーす。ERROR_CODE:1242");
}
/////////////////////////////////////////


$n_Y = date("Y");
$n_M = date("n");
$n_D = date("j");

$company_name = trim($company_name);
$company_name_kana = trim($company_name_kana);
$company_name = str_replace("　"," ",$company_name);
$company_name_kana = str_replace("　"," ",$company_name_kana);
$original_id = trim($original_id);
$customer_num = trim($customer_num);
$email = trim($email);
$supplier_id = trim($supplier_id);
if(!$company_name){
	$error_check=1;
	$company_name_err=" is-invalid";
}else{
	$company_name_err=" is-valid";
}
if($original_id){
if(!preg_match("/^[a-zA-Z0-9\-_\.]+$/",$original_id)){
	$error_check=1;
	$original_id_err=" is-invalid";
}else{
	$original_id_err=" is-valid";
}
}
if(!preg_match("/^[a-zA-Z0-9]{20}$/",$customer_num)){
	$error_check=1;
	$customer_num_err=" is-invalid";
}else{
	$customer_num_err=" is-valid";
}

if($error_check == "1"){

$param = "company_id=$company_id&company_name=$company_name&company_name_kana=$company_name_kana&original_id=$original_id&customer_num=$customer_num&email=$email&supplier_id=$supplier_id&company_name_err=$company_name_err&company_name_kana_err=$company_name_kana_err&original_id_err=$original_id_err&customer_num_err=$customer_num_err&email_err=$email_err&error_check=$error_check";

$JUMPSCRIPT = "aplus/company_cg.php";
header("Location: {$ybase->PATH}{$JUMPSCRIPT}?$param");
exit;
}
/////////////////////////////////////////////////////////////////////////////////////////
if(!preg_match("/^[0-9]+$/",$supplier_id)){
	$supplier_id = "null";
}
if(!preg_match("/^[a-zA-Z0-9\-_\.]+$/",$original_id)){
	$original_id = "null";
}else{
	$original_id = "'$original_id'";
}

$conn = $ybase->connect(4);

$sql = "select company_id from company_list where customer_num = '$customer_num' and status = '1' and service_id = $SERVICE_ID and company_id <> $company_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	list($q_company_id) = pg_fetch_array($result,0);
	$ybase->error("その顧客番号の契約者は既に登録されています。:$q_company_id");
}
$sql = "select company_id from company_list where original_id = $original_id and status = '1' and service_id = $SERVICE_ID and company_id <> $company_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	list($q_company_id) = pg_fetch_array($result,0);
	$ybase->error("その識別IDの契約者は既に登録されています。:$q_company_id");
}


$company_name = pg_escape_string($company_name);
$company_name_kana = pg_escape_string($company_name_kana);

$sql = "update company_list set company_name='$company_name',company_name_kana='$company_name_kana',original_id=$original_id,customer_num='$customer_num',email='$email',supplier=$supplier_id where company_id = $company_id and status = '1'";

$result = $ybase->sql($conn,$sql);


$JUMPSCRIPT = "aplus/company_list.php";
header("Location: {$ybase->PATH}{$JUMPSCRIPT}");
exit;
////////////////////////////////////////////////
?>