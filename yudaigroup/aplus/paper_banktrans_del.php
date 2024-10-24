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
	$ybase->error("パラメーターエラーす。ERROR_CODE:1341");
}
if(!preg_match("/^[0-9]+$/",$company_id)){
	$ybase->error("パラメーターエラーす。ERROR_CODE:1342");
}
/////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////

$conn = $ybase->connect(4);

$sql = "update paper_banktrans set status = '0' where company_id = $company_id and paper_banktrans_id = $paper_banktrans_id";

$result = $ybase->sql($conn,$sql);


$JUMPSCRIPT = "aplus/paper_banktrans_list.php";
header("Location: {$ybase->PATH}{$JUMPSCRIPT}");
exit;
////////////////////////////////////////////////
?>