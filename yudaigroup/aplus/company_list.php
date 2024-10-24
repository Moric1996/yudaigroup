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
include(dirname(__FILE__).'/../slip/inc/slip.inc');

$ybase = new ybase();
$aplus = new aplus();
$slip = new slip();

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
$slip->supplier_make();

///////////////////////////////////////////////
//$dbno = $ybase->my_dbno;
//$edit_f = 1;
/////////////////////////////////////////

$yudolist[0]="無し";
$yudolist[1]="有り";
$yudolist[2]="有り";
$yudolist[3]="有り";
$yudolist[4]="有り";

$status_list[1] = "通常";
$status_list[2] = "未登録";

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
$sql = "select a.company_id,a.service_id,a.original_id,a.customer_num,a.company_name,a.company_name_kana,a.email,a.supplier,to_char(a.add_date,'YYYY/MM/DD'),a.status,b.paper_banktrans_id from company_list as a left join paper_banktrans as b on a.company_id = b.company_id and b.status > '0' where a.status = '1' and a.service_id = $SERVICE_ID order by to_number(a.original_id,'9999999') NULLS LAST,a.add_date desc";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

$ybase->title = "口座振替用契約者一覧";

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
<p class="h5 text-center">口座振替用契約者一覧</p>

<table class="table table-sm tablesorter tablesorter-blue" width="90%" id="table1">

<thead>
<tr align="center" bgcolor="{$ybase->BASE_COLOR4}">
<th></th>
<th>識別ID</th>
<th>契約者名</th>
<th>契約者名カナ</th>
<th>FX4取引先</th>
<th>顧客番号</th>
<th>Eメール</th>
<th>登録日</th>
<th>変更</th>
<th>口座</th>
<th>削除</th>
</tr>
</thead>
<tbody>
HTML;
$k=0;

for($i=0;$i<$num;$i++){
	list($q_company_id,$q_service_id,$q_original_id,$q_customer_num,$q_company_name,$q_company_name_kana,$q_email,$q_supplier,$q_add_date,$q_status,$q_paper_banktrans_id) = pg_fetch_array($result,$i);
	$q_company_id = trim($q_company_id);
	$q_service_id = trim($q_service_id);
	$q_original_id = trim($q_original_id);
	$q_customer_num = trim($q_customer_num);
	$q_company_name = trim($q_company_name);
	$q_company_name_kana = trim($q_company_name_kana);
	$q_email = trim($q_email);
	$q_supplier = trim($q_supplier);
	$q_paper_banktrans_id = trim($q_paper_banktrans_id);
	$k++;
	if($q_paper_banktrans_id){
		$add_bank = "変更";
		$action = "";
	}else{
		$add_bank = "追加";
		$action = "new";
	}

$ybase->ST_PRI .= <<<HTML
<tr>
<td align="center">$k</td>
<td align="center">{$q_original_id}</td>
<td align="center">{$q_company_name}</td>
<td align="center">{$q_company_name_kana}</td>
<td align="center">{$slip->supplier_list[$q_supplier]}</td>
<td align="center">{$q_customer_num}</td>
<td align="center">{$q_email}</td>
<td align="center">{$q_add_date}</td>
<td align="center"><a class="btn btn-outline-secondary btn-sm p-1" href="./company_cg.php?company_id=$q_company_id" id="cg{$q_company_id}" role="button">変更</a></td>
<td align="center"><a class="btn btn-outline-secondary btn-sm p-1" href="./paper_banktrans_cg.php?company_id=$q_company_id&paper_banktrans_id=$q_paper_banktrans_id&backscript=company_list.php&action=$action" id="new_paper_banktrans_{$q_company_id}" role="button">{$add_bank}</a></td>
<td align="center"><a class="btn btn-outline-secondary btn-sm p-1" href="#" delhref="./company_del.php?company_id=$q_company_id" id="delete{$q_company_id}" role="button">削除</a></td>
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