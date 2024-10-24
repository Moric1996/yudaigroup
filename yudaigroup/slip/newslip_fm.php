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
include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/slip.inc');

$ybase = new ybase();
$slip = new slip();

$ybase->session_get();
$ybase->my_company_id = 5;

$ybase->make_yournet_employee_list("1");
$slip->supplier_make();
$slip->my_supplier_up($ybase->my_employee_id);
$slip->supplier_list[9999999] = "その他";
/////////////////////////////////////////
$no_release_check = "";

if(preg_match("/^[0-9]+$/",$cp_slip_id)){
	$conn = $ybase->connect(3);
	$sql = "select slip_type,to_char(month,'YYYY-MM'),to_char(action_date,'YYYY-MM'),to_char(action_date,'YYYY-MM-DD'),company_id,section_id,money,supplier,supplier_other,account,fee_st,contents,attach,charge_emp,last_accept_list_id,memo,to_char(pay_date,'YYYY-MM-DD'),add_date,up_date,status,no_release,credit_id from slip where slip_id = $cp_slip_id and status > '0'";

	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
		list($q_slip_type,$q_month,$q_action_date_yymm,$q_action_date,$q_company_id,$q_section_id,$q_money,$q_supplier,$q_supplier_other,$q_account,$q_fee_st,$q_contents,$q_attach,$q_charge_emp,$q_last_accept_list_id,$q_memo,$q_pay_date,$q_add_date,$q_up_date,$q_status,$q_no_release,$q_credit_id) = pg_fetch_array($result,$i);
		$q_attach = trim($q_attach);
		$q_contents = trim($q_contents);
		$q_pay_date = trim($q_pay_date);
		$q_fee_st = trim($q_fee_st);
		$q_account = trim($q_account);
		$q_no_release = trim($q_no_release);
		$q_supplier_other = trim($q_supplier_other);
		$q_credit_id = trim($q_credit_id);
		$q_memo = trim($q_memo);
		if($q_attach){
			$q_attach_arr = json_decode($q_attach,true);
		}else{
			$q_attach_arr = array();
		}
		switch($q_slip_type){
			case 2:
				$disabled2 = " disabled";
				$disabled3 = " disabled";
				$disabled4 = "";
				break;
			case 3:
				$disabled2 = " disabled";
				$disabled3 = "";
				$disabled4 = "";
				break;
			case 4:
				$disabled2 = " disabled";
				$disabled3 = " disabled";
				$disabled4 = " disabled";
				break;
			default:
				$disabled2 = "";
				$disabled3 = " disabled";
				$disabled4 = "";
				break;
		}
		if($q_no_release == "1"){
			$no_release_check = " checked";
		}else{
			$no_release_check = "";
		}
	}
	$slip_type = $q_slip_type;
	$section_id = $q_section_id;
	$credit_id = $q_credit_id;
	$supplier_id = $q_supplier;
	$supplier_other = $q_supplier_other;
	$money = $q_money;
	$debit_code = $q_account;
	$fee_st = $q_fee_st;
	$all_biko = $q_contents;
}else{
	$disabled2 = "";
	$disabled3 = " disabled";
	$disabled4 = "";
}

if(!$charge_emp){
	$charge_emp = $ybase->my_employee_id;
}
if(!$section_id && $my_department_id[$ybase->my_employee_id]){
	$section_id = $my_department_id[$ybase->my_employee_id];
}


$n_yy = date("Y");
$n_mm = date("m");
$n_dd = date("d");
if(!$target_date){
	$target_date = date("Y-m-d");
}
if(!$target_month){
	if($q_month == $q_action_date_yymm){
		$target_month = date("Y-m");
	}else{
		$target_month = date("Y-m",mktime(0,0,0,$n_mm - 1,1,$n_yy));
	}
}
if(!$target_month_now){
	$target_month_now = date("Y-m",mktime(0,0,0,$n_mm,1,$n_yy));
}
if(!$pay_date){
	$pay_date = date("Y-m-d",mktime(0,0,0,$n_mm + 1,0,$n_yy));
}

$ybase->title = "新規伝票作成";
$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("伝票管理");
$ybase->ST_PRI .= <<<HTML
<link rel="stylesheet" href="./inc/fileupload.css?4">
<link rel="stylesheet" type="text/css" href="https://yournet-jp.com/yudaigroup/inc/easyui/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="https://yournet-jp.com/yudaigroup/inc/easyui/themes/icon.css">
<script type="text/javascript" src="https://yournet-jp.com/yudaigroup/inc/easyui/jquery.easyui.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js"></script>
<script>
$(document).ready(function () {
	$('#supplier_id').select2();
});

$(function(){
	$("textarea").attr("rows", 2).on("input", e => {
		$(e.target).height(0).innerHeight(e.target.scrollHeight);
	});
});
$(function() {
	$('#slip_type').change(function() {
	var select_val = $(this).val();
	var debit_code_val = $('#debit_code').val();
	if(select_val == 1){
		if(debit_code_val == ""){
			$('#debit_code').val('101');
		}
		$('#fee_st').prop("disabled", false);
		$('#pay_date').prop("disabled", false);
		$('#target_month').prop("readonly", false);
		$('#debit_code').prop("disabled", false);
		$('#supplier_id').prop("disabled", false);
		$('#supplier_other').prop("disabled", false);
		$('#credit_id').prop("disabled", true);
	}else if(select_val == 2){
		if(debit_code_val == ""){
			$('#debit_code').val('400');
		}
		$('#fee_st').prop("disabled", true);
		$('#pay_date').prop("disabled", true);
		$('#target_month').prop("readonly", false);
		$('#debit_code').prop("disabled", false);
		$('#supplier_id').prop("disabled", false);
		$('#supplier_other').prop("disabled", false);
		$('#credit_id').prop("disabled", true);
	}else if(select_val == 3){
		$('#fee_st').prop("disabled", true);
		$('#pay_date').prop("disabled", true);
		$('#target_month').prop("readonly", false);
		$('#debit_code').prop("disabled", false);
		$('#supplier_id').prop("disabled", false);
		$('#supplier_other').prop("disabled", false);
		$('#credit_id').prop("disabled", false);
	}else if(select_val == 4){
		$('#target_month').val('{$target_month_now}');
		$('#target_month').prop("readonly", true);
		$('#fee_st').prop("disabled", true);
		$('#pay_date').prop("disabled", true);
		$('#debit_code').prop("disabled", true);
		$('#supplier_id').prop("disabled", true);
		$('#supplier_other').prop("disabled", true);
		$('#credit_id').prop("disabled", true);
	}
	});
});
</script>
HTML;

$ybase->ST_PRI .= <<<HTML
<div class="container">
<table class="table table-bordered table-sm mx-auto small text-center">
<tbody>
<tr>
<td class="table-active">
新規伝票作成
</td>
<td>
<a href="./slip_list.php">伝票管理</a>
</td>
<td>
<a href="./insupplier_fm.php">取引先取込</a>
</td>
</tr>
</tbody>
</table>

<p></p>
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">新規伝票作成</div>
<div class="card-body">
<form action="newslip_ex.php" method="post" enctype="multipart/form-data" id="form01">
<input type="hidden" name="company_id" value="{$ybase->my_company_id}">
<div class="text-center">
</div>

<div class="form-row ">
<div class="form-group col-6 col-sm-3">
<label>種別 <span class="text-danger">※</span></label>
<select name="slip_type" id="slip_type" class="form-control form-control-sm border-dark" required>
HTML;
foreach($slip_type_list as $key => $val){
if($key == $slip_type){
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
</div>
<div class="form-group col-6 col-sm-3">
<label>部門 <span class="text-danger">※</span></label>
<select name="section_id" id="section_id" class="form-control form-control-sm border-dark" required>
<option value="">選択してください</option>
HTML;
foreach($yournet_department_list as $key => $val){
if($key == $section_id){
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
</div>

<div class="form-group col-6 col-sm-3">
<label>担当 <span class="text-danger">※</span></label>
<select name="charge_emp" id="charge_emp" class="form-control form-control-sm border-dark" required>
HTML;
foreach($ybase->employee_name_list as $key => $val){
if($key == $charge_emp){
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
</div>

<div class="form-group col-6 col-sm-3">
<label>利用カード <span class="text-danger">※</span></label>
<select name="credit_id" id="credit_id" class="form-control form-control-sm border-dark" required{$disabled3}>
<option value="">選択してください</option>
HTML;
foreach($creditcart_list as $key => $val){
if($key == $credit_id){
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
</div>

</div>
<br>
<div class="form-row ">
<div class="form-group col-6 col-sm-3">
<label>日付 <span class="text-danger">※</span></label>
<input type="date" name="target_date" value="$target_date" class="form-control form-control-sm border-dark" id="target_date" required>
</div>
<div class="form-group col-6 col-sm-3">
<label>対象月(〇月分) <span class="text-danger">※</span></label>
<input type="month" name="target_month" value="$target_month" class="form-control form-control-sm border-dark" id="target_month" required{$disabled4}>
</div>
<div class="form-group col-6 col-sm-3">
<label>相手先 <span class="text-danger">※</span></label>
<select name="supplier_id" id="supplier_id" class="form-control form-control-sm border-dark" required{$disabled4}>
<option value="">選択してください</option>
HTML;
foreach($slip->supplier_list as $key => $val){
if($key == $supplier_id){
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
</div>
<div class="form-group col-6 col-sm-3">
<label>その他の場合</label>
<input type="text" name="supplier_other" value="$supplier_other" class="form-control form-control-sm border-dark" id="supplier_other"{$disabled4}>
</div>
</div>
<br>

<div class="form-row ">
<div class="form-group col-6 col-sm-3">
<label>税込金額 <span class="text-danger">※</span></label>
<input type="number" name="money" value="$money" class="form-control form-control-sm border-dark" id="money" pattern="^[0-9]+$" required>
</div>
<div class="form-group col-6 col-sm-3">
<label>仕訳科目 <span class="text-danger">※</span></label>
<select name="debit_code" id="debit_code" class="form-control form-control-sm border-dark" required{$disabled4}>
<option value="">選択してください</option>
HTML;
foreach($Journal_code_list as $key => $val){
if($key == $debit_code){
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
</div>

<div class="form-group col-6 col-sm-3">
<label>手数料持ち <span class="text-danger">※</span></label>
<select name="fee_st" id="fee_st" class="form-control form-control-sm border-dark"{$disabled2}>
HTML;
foreach($fee_st_list as $key => $val){
if($key == $fee_st){
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
</div>
<div class="form-group col-6 col-sm-3">
<label>振込期限 <span class="text-danger">※</span></label>
<input type="date" name="pay_date" value="$pay_date" class="form-control form-control-sm border-dark" id="pay_date" required{$disabled2}>
</div>

</div>

<br>

<div class="form-row ">
<div class="form-group col-12 col-sm-12">
<label>内容</label>
<textarea name="all_biko" class="form-control form-control-sm border-dark" id="all_biko">
$all_biko
</textarea>
</div>
</div>
<div class="form-row">
<div class="form-group files col-sm-12">
<label>添付ファイル</label>
<input type="hidden" name="MAX_FILE_SIZE" value="50000000">
<input type="file" name="attachfile[]" multiple="multiple" class="form-control-file form-control-sm" id="attachfile" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
</div>
</div>

<div class="form-group form-check col-6 col-sm-6 offset-sm-4">
<input type="checkbox" name="no_release_flag" value="1" class="form-check-input border-dark" id="no_release_flag"{$no_release_check}><label class="form-check-label" for="no_release_flag">非公開(総務のみ閲覧可)とする</label>
</div>

<p></p>
<button class="btn btn-secondary col-sm-2 offset-sm-4 border-dark" type="submit">登録</button>
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