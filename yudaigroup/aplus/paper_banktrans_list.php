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

$monthon = 1;//月単位で


if($monthon){
if($selyymm){
	$yy = substr($selyymm,0,4);
	$mm = substr($selyymm,5,2);
}elseif($selyymmdd){
	$yy = substr($selyymmdd,0,4);
	$mm = substr($selyymmdd,5,2);
}
if(!$yy || !$mm){
	$now_yy = date('Y');
	$now_mm = date('m');
	$now_dd = date('d');
	$yy = date('Y',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$mm = date('m',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$dd = date('d',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
}
$maxday = date('t',mktime(0,0,0,$mm,1,$yy));
$selyymm = "$yy-$mm";
}else{
if($selyymmdd){
	$yy = substr($selyymmdd,0,4);
	$mm = substr($selyymmdd,5,2);
	$dd = substr($selyymmdd,8,2);
}
if(!$yy || !$mm || !$dd){
	$now_yy = date('Y');
	$now_mm = date('m');
	$now_dd = date('d');
	$yy = date('Y',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$mm = date('m',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$dd = date('d',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
}
$selyymmdd = "$yy-$mm-$dd";
$selyymm = "$yy-$mm";
}
$delokflag="1";
$conn = $ybase->connect(4);

if($monthon){
	$date_sql = "between '{$selyymm}-01' and '{$selyymm}-{$maxday}'";
	$g_width = 2000;
}else{
	$date_sql = "= '$selyymmdd'";
	$g_width = 1000;
}
$sql = "select a.paper_banktrans_id,a.company_id,a.bank_code,a.bank_kana,a.branch_code,a.branch_kana,a.type,a.account_number,a.account_name,a.new_flag,to_char(a.add_date,'YYYY/MM/DD'),a.status,b.company_name,b.original_id,b.customer_num from paper_banktrans as a join company_list as b on a.company_id = b.company_id where a.status > '0' order by to_number(b.original_id,'9999999') NULLS LAST,a.add_date desc";

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


<div class="container-fluid">

<p></p>
<!--<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./ana_top.php?$param" role="button">戻る</a></div>-->

<div style="font-size:90%;">

<p></p>

<div class="table-responsive">
<p class="h5 text-center">口座振替用登録データ一覧</p>

<table class="table table-sm tablesorter tablesorter-blue" width="90%" id="table1">

<thead>
<tr align="center" bgcolor="{$ybase->BASE_COLOR4}">
<th></th>
<th>識別ID</th>
<th>契約者名</th>
<th>顧客番号</th>
<th>銀行コード</th>
<th>銀行名</th>
<th>支店コード</th>
<th>支店名</th>
<th>口座種類</th>
<th>口座番号</th>
<th>口座名義</th>
<th>新規コード</th>
<th>状態</th>
<th>登録日</th>
<th>変更</th>
<th>削除</th>
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
$ybase->ST_PRI .= <<<HTML
<tr>
<td align="center">$k</td>
<td align="center">{$q_original_id}</td>
<td align="center">{$q_company_name}</td>
<td align="center">{$q_customer_num}</td>
<td align="center">{$q_bank_code}</td>
<td align="center">{$q_bank_kana}</td>
<td align="center">{$q_branch_code}</td>
<td align="center">{$q_branch_kana}</td>
<td align="center">{$d_type}</td>
<td align="center">{$q_account_number}</td>
<td align="center">{$q_account_name}</td>
<td align="center">{$bank_new_list[$q_new_flag]}</td>
<td align="center">{$d_status}</td>
<td align="center">{$q_add_date}</td>
<td align="center"><a class="btn btn-outline-secondary btn-sm p-1" href="./paper_banktrans_cg.php?company_id=$q_company_id" id="cg{$q_company_id}" role="button">変更</a></td>
<td align="center"><a class="btn btn-outline-secondary btn-sm p-1" href="#" delhref="./paper_banktrans_del.php?paper_banktrans_id=$q_paper_banktrans_id&company_id=$q_company_id" id="delete{$q_company_id}" role="button">削除</a></td>
</tr>

HTML;
}

$ybase->ST_PRI .= <<<HTML
</tbody>
</table>
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