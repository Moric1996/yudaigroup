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
if(!isset($shop_id)){
	$shop_id = '';
}
if(!isset($selyymm)){
	$selyymm = '';
}
if(!isset($selyymmdd)){
	$selyymmdd = '';
}
if(!isset($q_answer_count_arr)){
	$q_answer_count_arr = '';
}
if(!isset($yy)){
	$yy = '';
}
if(!isset($mm)){
	$mm = '';
}
if(!isset($param)){
	$param = '';
}
$ybase->session_get();

///////////////////////////////////////////////
//$dbno = $ybase->my_dbno;
//$edit_f = 1;
/////////////////////////////////////////

$now_yy = date('Y');
$now_mm = date('m');
$now_dd = date('d');

if(!isset($next_target_date)){
	$www = date('w',mktime(0,0,0,$now_mm,27,$now_yy));
	if($www == '0'){
		$YY = date('Y',mktime(0,0,0,$now_mm,28,$now_yy));
		$MM = date('m',mktime(0,0,0,$now_mm,28,$now_yy));
		$DD = date('d',mktime(0,0,0,$now_mm,28,$now_yy));
	}elseif($www == '6'){
		$YY = date('Y',mktime(0,0,0,$now_mm,29,$now_yy));
		$MM = date('m',mktime(0,0,0,$now_mm,29,$now_yy));
		$DD = date('d',mktime(0,0,0,$now_mm,29,$now_yy));
	}else{
		$YY = date('Y',mktime(0,0,0,$now_mm,27,$now_yy));
		$MM = date('m',mktime(0,0,0,$now_mm,27,$now_yy));
		$DD = date('d',mktime(0,0,0,$now_mm,27,$now_yy));
	}
	if(($MM == '04')&&($DD == '29')){
		$DD = 30;
	}
	$next_target_date = date('Y-m-d',mktime(0,0,0,$MM,$DD,$YY));
}
if(!isset($target_month)){
	$target_month = date('Y-m');
}
$csv_money = array();

if(isset($_FILES["claimfile"]['name'][0])){
	$handle = fopen($_FILES["claimfile"]['tmp_name'], "r");
	$i=0;
	while (($buffer[$i] = fgets($handle, 4096)) !== false) {
		$buffer[$i] = trim($buffer[$i]);
		$i++;
	}
	fclose($handle);
	foreach($buffer as $key => $val){
		$val = trim($val);
		if(!$val){continue;}
		list($ori_id,$money) = explode(",",$val);
		$ori_id = str_replace("\"","",$ori_id);
		$money = str_replace("\"","",$money);
		$csv_money[$ori_id] = $money;
	}
}





$conn = $ybase->connect(4);

if($monthon){
	$date_sql = "between '{$selyymm}-01' and '{$selyymm}-{$maxday}'";
	$g_width = 2000;
}else{
	$date_sql = "= '$selyymmdd'";
	$g_width = 1000;
}
$sql = "select a.paper_banktrans_id,a.company_id,a.bank_code,a.bank_kana,a.branch_code,a.branch_kana,a.type,a.account_number,a.account_name,a.new_flag,to_char(a.add_date,'YYYY/MM/DD'),a.status,b.company_name,b.original_id,b.customer_num from paper_banktrans as a left join company_list as b on a.company_id = b.company_id where a.status > '0' order by a.add_date desc";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

$ybase->title = "口座振替用登録データ一覧";

$ybase->HTMLheader();

$ybase->ST_PRI .= $ybase->header_pri($ybase->title);

$vavi_pri = $aplus->navi_head_pri();

$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$("#table1").tablesorter({
	       widgets: ['zebra']
	});
});
$(function(){
	$('#inshop_id,#inmonth,#ttype,#monthon,#free_class_id').change(function(){
		$("#form1").submit();
	});
	$('#all_ck').on("click",function(){
		$("[id^=ck_]").prop("checked", $(this).prop("checked"));
	});
	$("[id^=ck_]").on('change', function () {
		var companyid = $(this).attr('company_id');
		var idname = 'claim_money_' + companyid;
		var my_id = $(this).attr('id');
		if ($("#"+ my_id).prop("checked") == true) {
			$("#"+ idname).prop("disabled", false);
		} else {
			$("#"+ idname).prop("disabled", true);
		}
	});
	$("#all_ck").on('change', function () {
		if ($("#all_ck:checked").length > 0) {
			$("[id^=claim_money_]").prop("disabled", false);
		} else {
			$("[id^=claim_money_]").prop("disabled", true);
		}
	});
	$("[id^=delete]").click(function(){
		var dhref = $(this).attr('delhref');
		if(!confirm('契約者を削除していいですか？')){
		        return false;
		}else{
			location.href = dhref;
		}
	});
});
</script>
{$vavi_pri}


<div class="container-fluid w-75 p-3">

<p></p>
<!--<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./ana_top.php?$param" role="button">戻る</a></div>-->

<div style="font-size:90%;">

<p></p>

<p class="h5 text-center">口座振替用請求データ作成</p>

<form action="claim_make_ex.php" method="post" id="form_claim_make">
<table class="table table-sm tablesorter tablesorter-blue" width="90%" id="table1">

<thead>
<tr align="center" bgcolor="{$ybase->BASE_COLOR4}">
<th><input type="checkbox" name="all_ck" value="1" id="all_ck"></th>
<th></th>
<th>請求金額</th>
<th>識別ID</th>
<th>契約者名</th>
<th>顧客番号</th>
<th>銀行名</th>
<th>口座名義</th>
<th>新規コード</th>
<th>変更</th>
</tr>
</thead>
<tbody>
HTML;
$k=0;

for($i=0;$i<$num;$i++){
	list($q_paper_banktrans_id,$q_company_id,$q_bank_code,$q_bank_kana,$q_branch_code,$q_branch_kana,$q_type,$q_account_number,$q_account_name,$q_new_flag,$q_add_date,$q_status,$q_company_name,$q_original_id,$q_customer_num) = pg_fetch_array($result,$i);
	$q_bank_kana = trim($q_bank_kana);
	$q_branch_code = trim($q_branch_code);
	$q_branch_kana = trim($q_branch_kana);
	$q_type = trim($q_type);
	$q_account_number = trim($q_account_number);
	$q_account_name = trim($q_account_name);
	$q_new_flag = trim($q_new_flag);
	$q_customer_num = trim($q_customer_num);
	$q_original_id = trim($q_original_id);
	$q_company_name = trim($q_company_name);
	$k++;
	if($q_type){
		$d_type = $bank_type_list[$q_type];
	}else{
		$d_type = "";
	}
	if($q_status){
		$d_status = $paper_banktrans_status_list[$q_status];
	}else{
		$d_status = "";
	}
	if(!$q_new_flag){
		$q_new_flag = 0;
	}
if(isset($csv_money[$q_original_id])){
	$claim_money[$q_company_id] = $csv_money[$q_original_id];
}
$ybase->ST_PRI .= <<<HTML
<tr>
<td align="center"><input type="checkbox" name="tar_claim_list[]" value="$q_paper_banktrans_id" id="ck_$q_paper_banktrans_id" company_id="$q_company_id"></td>
<input type="hidden" name="campany_banktrans[$q_paper_banktrans_id]" value="$q_company_id">
<td align="center">$k</td>
<td align="center"><input class="form-control-sm" type="number" name="claim_money[$q_company_id]" value="{$claim_money[$q_company_id]}" id="claim_money_{$q_company_id}" style="width:60%;" disabled></td>
<td align="center">{$q_original_id}</td>
<td align="center">{$q_company_name}</td>
<td align="center">{$q_customer_num}</td>
<td align="center">{$q_bank_kana}</td>
<td align="center">{$q_account_name}</td>
<td align="center">{$bank_new_list[$q_new_flag]}</td>
<td align="center"><nobr><a class="btn btn-outline-secondary btn-sm p-1" href="./paper_banktrans_cg.php?company_id=$q_company_id&backscript=claim_make.php" id="cg{$q_company_id}" role="button">変更</a></nobr></td>
</tr>

HTML;
}

$ybase->ST_PRI .= <<<HTML
</tbody>
</table>

<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-3 text-right">対象月(〇月分)</label>
<div class="col-4 col-sm-2">
<input class="form-control form-control-sm border-dark" type="month" name="target_month" value="$target_month" id="target_month" required>
</div>
</div>
<div class="form-group row ">
<label class="col-4 col-sm-2 offset-md-3 text-right">口座振替日</label>
<div class="col-4 col-sm-2">
<input class="form-control form-control-sm border-dark" type="date" name="next_target_date" value="$next_target_date" id="next_target_date" required>
</div>
</div>

<button class="btn btn-sm col-sm-4 offset-sm-4 btn-info" type="submit" id="dlbtn">チェック付けた分の請求データ作成</button>

</form>


</div>



</div>
</div>

<p class="m-6"></p>

<div class="container">
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-success text-center">CSVからの金額反映</div>
<div class="card-body">

CSVデータ(識別ID,金額のフォーマット)から金額を反映できます。<br>
<form action="./claim_make.php" method="post" enctype="multipart/form-data" id="form2">

<div class="form-row">
<div class="form-group col-sm-4 offset-sm-4">
 <input type="hidden" name="MAX_FILE_SIZE" value="50000000">
<input type="file" name="claimfile" class="form-control-file form-control-sm" id="jinjerfile" required>
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
<p></p>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>