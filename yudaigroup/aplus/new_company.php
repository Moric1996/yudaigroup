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
/////////////////////////////////////////


$conn = $ybase->connect(4);

if($error_check == 1){
	$q_service_id = $service_id;
	$q_original_id = $original_id;
	$q_customer_num = $customer_num;
	$q_company_name = $company_name;
	$q_company_name_kana = $company_name_kana;
	$q_email = $email;
}

$ybase->title = "新規契約者登録";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri($ybase->title);
$vavi_pri = $aplus->navi_head_pri();

$ybase->ST_PRI .= <<<HTML
{$vavi_pri}

<div class="container">
<p></p>

<p></p>

<div class="card border border-dark mx-auto">
<div class="card-header border-dark bg-light text-center">新規契約者登録</div>
<div class="card-body">
<form action="new_company_ex.php" method="post">

<p></p>
<div class="form-group row ">
<label class="col-4 col-sm-4">契約者名 ※必須</label>
<div class="col-8 col-sm-4">
<input type="text" name="company_name" value="$q_company_name" class="form-control form-control-sm border-dark{$company_name_err}" id="company_name" required>
<div class="invalid-feedback">
入力してください
</div>
</div>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-4">契約者名カナ</label>
<div class="col-8 col-sm-4">
<input type="text" name="company_name_kana" value="$q_company_name_kana" class="form-control form-control-sm border-dark{$company_name_kana_err}" id="company_name_kana">
</div>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-4">識別ID(スクパス上のID)</label>
<div class="col-8 col-sm-4">
<input type="text" name="original_id" value="$q_original_id" class="form-control form-control-sm border-dark{$original_id_err}" id="original_id" pattern="^[a-zA-Z0-9_\-\.]+$">
<div class="invalid-feedback">
半角で入力してください
</div>
</div><small>※半角</small>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-4">顧客番号(口座振替申込書上の顧客番号)</label>
<div class="col-8 col-sm-4">
<input type="text" name="customer_num" value="$q_customer_num" class="form-control form-control-sm border-dark{$customer_num_err}" id="customer_num" pattern="^[a-zA-Z0-9_\-\.]+$">
<div class="invalid-feedback">
半角で入力してください
</div>
</div><small>※半角</small>
</div>

<div class="form-group row ">
<label class="col-4 col-sm-4">Eメール</label>
<div class="col-8 col-sm-4">
<input type="email" name="email" value="$q_email" class="form-control form-control-sm border-dark{$email_err}" id="email">
</div>
</div>

<p></p>


<p></p>
<br>
<button class="btn btn-info col-sm-2 offset-sm-5 border-dark" type="submit">登録</button>

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