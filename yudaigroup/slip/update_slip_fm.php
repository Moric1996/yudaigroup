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
if(!preg_match("/^[0-9]+$/",$t_slip_id)){
	$ybase->error("パラメータエラー");
}

$n_yy = date("Y");
$n_mm = date("m");
$n_dd = date("d");
$conn = $ybase->connect(3);
$no_release_flag = $slip->no_release_check($ybase->my_employee_id);

if($no_release_flag){
	$release_sql = " and ((charge_emp = $ybase->my_employee_id) or (no_release = '0'))";
}else{
	$release_sql = "";
}

$sql = "select slip_type,to_char(month,'YYYY-MM'),to_char(action_date,'YYYY-MM-DD'),company_id,section_id,money,supplier,supplier_other,account,fee_st,contents,attach,charge_emp,last_accept_list_id,memo,to_char(pay_date,'YYYY-MM-DD'),add_date,up_date,status,no_release,credit_id from slip where slip_id = $t_slip_id and status > '0'{$release_sql}";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("対象書類がみつかりません");
}

list($q_slip_type,$q_month,$q_action_date,$q_company_id,$q_section_id,$q_money,$q_supplier,$q_supplier_other,$q_account,$q_fee_st,$q_contents,$q_attach,$q_charge_emp,$q_last_accept_list_id,$q_memo,$q_pay_date,$q_add_date,$q_up_date,$q_status,$q_no_release,$q_credit_id) = pg_fetch_array($result,$i);
$q_attach = trim($q_attach);
$q_contents = trim($q_contents);
$q_pay_date = trim($q_pay_date);
$q_fee_st = trim($q_fee_st);
$q_no_release = trim($q_no_release);
$q_supplier_other = trim($q_supplier_other);
$q_credit_id = trim($q_credit_id);
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
$ybase->title = "伝票修正";
$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("伝票修正");
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

</script>
HTML;

$ybase->ST_PRI .= <<<HTML
<div class="container">
<table class="table table-bordered table-sm mx-auto small text-center">
<tbody>
<tr>
<td class="table-active">
<a href="./newslip_fm.php">新規伝票作成</a>
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
<div class="card-header border-dark alert-info text-center">伝票修正 伝票番号[{$t_slip_id}]</div>
<div class="card-body">
<form action="update_slip_ex.php" method="post" enctype="multipart/form-data" id="form01">
<input type="hidden" name="t_slip_id" value="$t_slip_id">
<input type="hidden" name="t_slip_type" value="$q_slip_type">
<input type="hidden" name="sel_status" value="$sel_status">
<input type="hidden" name="sel_slip_type" value="$sel_slip_type">
<input type="hidden" name="sel_action_date_st" value="$sel_action_date_st">
<input type="hidden" name="sel_action_date_ed" value="$sel_action_date_ed">
<input type="hidden" name="sel_month_st" value="$sel_month_st">
<input type="hidden" name="sel_month_ed" value="$sel_month_ed">
<input type="hidden" name="sel_section_id" value="$sel_section_id">
<input type="hidden" name="sel_charge_emp" value="$sel_charge_emp">
<input type="hidden" name="sel_supplier" value="$sel_supplier">
<input type="hidden" name="sel_account" value="$sel_account">
<input type="hidden" name="sel_slip_id" value="$sel_slip_id">
<div class="text-center">
</div>

<div class="form-row ">
<div class="form-group col-6 col-sm-3">
<label>種別 <span class="text-danger">※</span></label>
<select name="slip_type" id="slip_type" class="form-control form-control-sm border-dark" disabled>
HTML;
foreach($slip_type_list as $key => $val){
if($key == $q_slip_type){
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
if($key == $q_section_id){
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
if($key == $q_charge_emp){
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
if($key == $q_credit_id){
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
<input type="date" name="target_date" value="$q_action_date" class="form-control form-control-sm border-dark" id="target_date" required>
</div>
<div class="form-group col-6 col-sm-3">
<label>対象月(〇月分) <span class="text-danger">※</span></label>
<input type="month" name="target_month" value="$q_month" class="form-control form-control-sm border-dark" id="target_month" required{$disabled4}>
</div>
<div class="form-group col-6 col-sm-3">
<label>相手先 <span class="text-danger">※</span></label>
<select name="supplier_id" id="supplier_id" class="form-control form-control-sm border-dark" required{$disabled4}>
<option value="">選択してください</option>
HTML;
foreach($slip->supplier_list as $key => $val){
if($key == $q_supplier){
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
<input type="text" name="supplier_other" value="$q_supplier_other" class="form-control form-control-sm border-dark" id="supplier_other"{$disabled4}>
</div>
</div>
<br>

<div class="form-row ">
<div class="form-group col-6 col-sm-3">
<label>税込金額 <span class="text-danger">※</span></label>
<input type="number" name="money" value="$q_money" class="form-control form-control-sm border-dark" id="money" pattern="^[0-9]+$" required>
</div>
<div class="form-group col-6 col-sm-3">
<label>仕訳科目 <span class="text-danger">※</span></label>
<select name="debit_code" id="debit_code" class="form-control form-control-sm border-dark" required{$disabled4}>
<option value="">選択してください</option>
HTML;
foreach($Journal_code_list as $key => $val){
if($key == $q_account){
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
if($key == $q_fee_st){
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
<input type="date" name="pay_date" value="$q_pay_date" class="form-control form-control-sm border-dark" id="pay_date" required{$disabled2}>
</div>

</div>

<br>

<div class="form-row ">
<div class="form-group col-12 col-sm-12">
<label>内容</label>
<textarea name="all_biko" class="form-control form-control-sm border-dark" id="all_biko">
$q_contents
</textarea>
</div>
</div>

HTML;
$d_attach = "";
foreach($q_attach_arr as $key => $val){
	$ext = substr($val,strrpos($val,'.') + 1);////////////////////
	$ext = strtolower($ext);////////////////////
	if($ext == 'pdf'){
		$pdffile = urlencode("https://yournet-jp.com/yudaigroup/slip/view.php?slip_id=$t_slip_id&attach_no=$key");
		$d_attach .= "<div class=\"col-3 col-sm-3\"><iframe width=\"300\" height=\"300\" src=\"../inc/pdfjs/web/viewer3.html?file=$pdffile\"></iframe>";
	}else{
		$d_attach .= "<div class=\"col-3 col-sm-3\"><iframe width=\"300\" height=\"300\" src=\"./slip_iframe.php?slip_id=$t_slip_id&attach_no=$key&ext=$ext\"></iframe>";
	}
	$d_attach .= "<br><input type=\"checkbox\" name=\"del_attach[$key]\" value=\"1\">削除</div>";
}
if(!$d_attach){
	$d_attach = "添付なし";
}
$ybase->ST_PRI .= <<<HTML

<label>添付ファイル</label>
<div class="form-row">
$d_attach




</div>
<p></p>
<div class="form-row">
<div class="form-group files col-sm-12">
<label>添付ファイル追加</label>

<input type="hidden" name="MAX_FILE_SIZE" value="50000000">
<input type="file" name="attachfile[]" multiple="multiple" class="form-control-file form-control-sm" id="attachfile" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
</div>
</div>

<div class="form-group form-check col-6 col-sm-6 offset-sm-4">
<input type="checkbox" name="no_release_flag" value="1" class="form-check-input border-dark" id="no_release_flag"{$no_release_check}><label class="form-check-label" for="no_release_flag">非公開(総務のみ閲覧可)とする</label>
</div>

<p></p>
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