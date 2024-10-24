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
if(!isset($tar_month)){
	$tar_month = '';
}
if(!isset($tar_company_id)){
	$tar_company_id = '';
}
if(!isset($tar_paymethod)){
	$tar_paymethod = '';
}
if(!isset($param)){
	$param = '';
}
$ybase->session_get();
if($ybase->my_company_id != 5){
	$ybase->allow_check($ybase->my_employee_id);
}

///////////////////////////////////////////////
$aplus->company_list_make();
/////////////////////////////////////////

if(!preg_match("/^[0-9]{4}\-[0-9]{2}$/",$tar_month) && !preg_match("/^[0-9]+$/",$tar_company_id)){
	$tar_month = date('Y-m');
}

$conn = $ybase->connect(4);
$add_sql = "";

if(preg_match("/^[0-9]{4}\-[0-9]{2}$/",$tar_month)){
	$add_sql .= " and to_char(a.target_date,'YYYY-MM') = '{$tar_month}'";
}
if(preg_match("/^[0-9]+$/",$tar_company_id)){
	$add_sql .= " and a.company_id = '{$tar_company_id}'";
}

$sql = "select a.claim_id,a.claim_date,to_char(a.target_date,'YYYY/MM'),a.company_id,a.price_notax,a.price_intax,a.plan_date,a.remark,a.paper_banktrans_log_id,a.comp_price,to_char(a.comp_date,'YYYY/MM/DD'),to_char(a.add_date,'YYYY/MM/DD'),a.status,b.company_name,b.original_id from claim as a left join company_list as b on a.company_id = b.company_id  where a.status > '0'{$add_sql} order by a.target_date,a.plan_date,a.claim_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

$ybase->title = "請求データ一覧";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);

$vavi_pri = $aplus->navi_head_pri();

$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
$(function(){
	$("#form_claim_dl").tablesorter({
	       widgets: ['zebra']
	});
});
	$('#tar_month,#tar_company_id,#tar_paymethod').change(function(){
		$("#form1").submit();
	});
	$("[id^=delete]").click(function(){
		var dhref = $(this).attr('delhref');
		if(!confirm('請求データを削除していいですか？')){
		        return false;
		}else{
			location.href = dhref;
		}
	});
	$('#all_ck').on("click",function(){
		$("[id^=ck_]").prop("checked", $(this).prop("checked"));
	});
	$("[id^=ck_],#all_ck").on('change', function () {
		if ($("[id^=ck_]:checked").length > 0) {
			$("#dlbtn").prop("disabled", false);
		} else {
			$("#dlbtn").prop("disabled", true);
		}
	});
});
</script>
{$vavi_pri}


<div class="container-fluid">

<p></p>
<!--<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./ana_top.php?$param" role="button">戻る</a></div>-->

<div style="font-size:90%;">
<form action="claim_list.php" method="post" id="form1">
対象月：<input type="month" name="tar_month" value="$tar_month" id="tar_month">
　対象契約者：<select name="tar_company_id" id="tar_company_id">
<option value="">全て</option>
HTML;
foreach($aplus->company_name_list as $key => $val){
	$val = trim($val);
	if(!$val){continue;}
	if($key == $tar_company_id){
		$selected = " selected";
	}else{
		$selected = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"{$selected}>$val($key)</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML

</select>
</form>

<p></p>

<div class="table-responsive">
<p class="h5 text-center">請求データ一覧</p>
<form action="claim_dl.php" method="post" id="form_claim_dl">
<table class="table table-sm tablesorter tablesorter-blue" width="90%" id="table1">

<thead>
<tr align="center" bgcolor="{$ybase->BASE_COLOR4}">
<th><input type="checkbox" name="all_ck" value="1" id="all_ck"></th>
<th></th>
<th>対象月</th>
<th>識別ID</th>
<th>契約者</th>
<th>予定日</th>
<th>金額</th>
<th>備考</th>
<th>入金ID</th>
<th>入金金額</th>
<th>入金日</th>
<th>状態</th>
<th>削除</th>
</tr>
</thead>
<tbody>
HTML;
$k=0;
$allcnt = 0;
$allmoney = 0;
$allinmoney = 0;
for($i=0;$i<$num;$i++){
	list($q_claim_id,$q_claim_date,$q_target_date,$q_company_id,$q_price_notax,$q_price_intax,$q_plan_date,$q_remark,$q_paper_banktrans_log_id,$q_comp_price,$q_comp_date,$q_add_date,$q_status,$q_company_name,$q_original_id) = pg_fetch_array($result,$i);
	$q_paper_banktrans_log_id = trim($q_paper_banktrans_log_id);
	$q_claim_date = trim($q_claim_date);
	$q_target_date = trim($q_target_date);
	$q_price_notax = trim($q_price_notax);
	$q_price_intax = trim($q_price_intax);
	$q_plan_date = trim($q_plan_date);
	$q_remark = trim($q_remark);
	$q_paper_banktrans_log_id = trim($q_paper_banktrans_log_id);
	$q_comp_price = trim($q_comp_price);
	$q_comp_date = trim($q_comp_date);
	$q_company_name = trim($q_company_name);
	$q_original_id = trim($q_original_id);
	$allcnt ++;
	$allmoney += $q_price_intax;
	$allinmoney += $q_comp_price;
	$k++;
	$q_price_intax = number_format($q_price_intax);
	$q_comp_price = number_format($q_comp_price);


$ybase->ST_PRI .= <<<HTML
<tr>
<td align="center"><input type="checkbox" name="tar_claim_list[]" value="$q_claim_id" id="ck_$q_claim_id"></td>
<td align="center">{$k}</td>
<td align="center">{$q_target_date}</td>
<td align="center">{$q_original_id}</td>
<td align="center">{$q_company_name}</td>
<td align="center">{$q_plan_date}</td>
<td align="center">{$q_price_intax}</td>
<td align="center">{$q_remark}</td>
<td align="center">{$q_paper_banktrans_log_id}</td>
<td align="center">{$q_comp_price}</td>
<td align="center">{$q_comp_date}</td>
<td align="center">{$claim_status_list[$q_status]}</td>
<td align="center"><a class="btn btn-outline-secondary btn-sm p-1" href="#" delhref="./claim_del.php?t_claim_id=$q_claim_id&tar_month=$tar_month&tar_company_id=$tar_company_id&tar_paymethod=$tar_paymethod" id="delete{$q_claim_id}" role="button">削除</a></td>
</tr>

HTML;
}
$allmoney = number_format($allmoney);
$allinmoney = number_format($allinmoney);

$ybase->ST_PRI .= <<<HTML

<tr bgcolor="#444444">
<td align="center">計</td>
<td align="center">全{$allcnt}件</td>
<td align="center" colspan="4"></td>
<td align="center">{$allmoney}</td>
<td align="center" colspan="2"></td>
<td align="center">{$allinmoney}</td>
<td align="center" colspan="3"></td>
</tr>






</tbody>
</table>
<button class="btn btn-sm col-sm-3 offset-sm-9 btn-outline-dark" type="submit" id="dlbtn" disabled>決済用CSV(全銀フォーマット)ダウンロード</button>

</form>
</div>
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