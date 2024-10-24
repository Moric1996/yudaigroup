<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();
$ttype_list[1] = "会計時間帯";
$ttype_list[2] = "オーダー時間帯";
//$edit_f = 1;
/////////////////////////////////////////
if(!$ttype){
	$ttype = 1;
}
$ybase->make_shop_list();
$ybase->shop_list['3001'] = "雄大ゴルフ熱函";	//雄大ゴルフ熱函
$ybase->shop_list['3002'] = "雄大ゴルフ清水町";	//雄大ゴルフ清水町

$ybase->make_employee_list("1");
if(!$shop_id){
if(array_key_exists((int)$ybase->my_section_id,$ybase->shop_list)){
	$shop_id = $ybase->my_section_id;
}else{
	$shop_id = 0;
}
}

$pos_shopno = $ybase->section_to_pos[$shop_id];
if(!$pos_shopno){
	$pos_shopno = 0;
}
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
if($free_class_id == ""){
	$free_class_id = 1;
}

$conn = $ybase->connect();

$ybase->title = "店舗分析-合計トランザクション";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#inshop_id,#inmonth,#ttype,#monthon,#free_class_id').change(function(){
		$("#form1").submit();
	});
});

</script>
<div class="container-fluid">

<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./ana_top.php?$param" role="button">戻る</a></div>

<div style="font-size:80%;margin:5px;">

<form action="ttotal_view.php" method="post" id="form1">
<select name="shop_id" id="inshop_id">
<option value="">選択してください</option>
HTML;
foreach($ybase->shop_list as $key => $val){
	if($shop_id == $key){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"{$addselect}>$val</option>
HTML;
}
if($monthon == 1){
	$monthon_checked = " checked";
	$selectdate = "<input type=\"month\" name=\"selyymm\" value=\"$selyymm\" id=\"inmonth\">";
}else{
	$monthon_checked = "";
	$selectdate = "<input type=\"date\" name=\"selyymmdd\" value=\"$selyymmdd\" id=\"inmonth\">";
}
$ybase->ST_PRI .= <<<HTML

</select>
$selectdate
<input type="checkbox" name="monthon" value="1" id="monthon"{$monthon_checked}>月表示　
HTML;

if($free_class_list){
$ybase->ST_PRI .= <<<HTML
<select name="free_class_id" id="free_class_id">
HTML;
foreach($free_class_list as $key => $val){
	if($free_class_id == $val){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$val"{$addselect}>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML

</select>
HTML;
}

$ybase->ST_PRI .= <<<HTML

</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th rowspan="2">日付</th>
<th rowspan="2">レシートNO</th>
<th rowspan="2">取引種別</th>
<th rowspan="2">VOID<br>レシートNO</th>
<th rowspan="2">扱者NO</th>
<th rowspan="2">担当者NO</th>
<th rowspan="2">元扱者NO</th>
<th rowspan="2">元担当者NO</th>
<th rowspan="2">会計種別</th>
<th rowspan="2">伝票NO</th>
<th rowspan="2">テーブルNO</th>
<th rowspan="2">伝票枝番</th>
<th rowspan="2">伝票一連NO</th>
<th colspan="2">オーダー時刻</th>
<th rowspan="2">会計日時</th>
<th rowspan="2">組数</th>
<th colspan="3">人数</th>
<th rowspan="2">フリー1<br>集計</th>
<th rowspan="2">フリー2<br>集計</th>
<th colspan="4">客数</th>
<th rowspan="2">お通し人数</th>
<th rowspan="2">控え番号</th>
<th rowspan="2">客層1</th>
<th rowspan="2">アイテム総点数</th>
<th colspan="2">店内飲食合計</th>
<th colspan="2">テイクアウト合計</th>
<th colspan="2">レストラン売上合計</th>
<th colspan="2">店頭売上合計</th>
<th colspan="2">売上計</th>
<th rowspan="2">割引券支払合計</th>
<th colspan="2">明細数</th>
<th rowspan="2">フロアNO</th>
</tr>
<tr align="center" bgcolor="#eacaca">
<th>新規</th>
<th>追加</th>
<th>POS入力</th>
<th>オーダー</th>
<th>会計</th>
<th>レストラン売上</th>
<th>店頭売上</th>
<th>売上高</th>
<th>収入計</th>
<th>金額</th>
<th>点数</th>
<th>金額</th>
<th>点数</th>
<th>金額</th>
<th>点数</th>
<th>金額</th>
<th>点数</th>
<th>金額</th>
<th>点数</th>
<th>商品/監査</th>
<th>支払</th>
</tr>

</thead>

<tbody>
HTML;
$g_sale=0;
$g_score=0;
$g_custom=0;
if($monthon){
	$date_sql = "between '{$selyymm}-01' and '{$selyymm}-{$maxday}'";
	$g_width = 2000;
}else{
	$date_sql = "= '$selyymmdd'";
	$g_width = 1000;
}
$sql = "select sale_date,pos_ttotal_id,receipt_no,transaction_type,void_receipt_no,handler_no,charger_no,old_handler_no,old_charger_no,accounting_type,slip_no,table_no,slip_branch_no,slip_series_no,new_order_time,add_order_time,pay_time,num_pair,pos_num,order_num,pay_num,free1_name,free2_name,restaurant_custom,front_custom,sale_custom,income_custom,through_num,copy_number,custom_base1,item_score,instore1_notax_price,instore1_notax_score,takeout1_notax_price,takeout1_notax_score,restaurant1_notax_price,restaurant1_notax_score,front1_notax_price,front1_notax_score,totalsale1_notax_price,totalsale1_notax_score,discount_notax_price,audit_item_num,pay_item_num,floor_no from pos_ttotal where pos_shopno = $pos_shopno and sale_date {$date_sql} and status = '1' order by sale_date,pos_ttotal_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$graphtxt = "";
$g_price = 0;
$g_score = 0;
for($i=0;$i<$num;$i++){
	list($q_sale_date,$q_pos_ttotal_id,$q_receipt_no,$q_transaction_type,$q_void_receipt_no,$q_handler_no,$q_charger_no,$q_old_handler_no,$q_old_charger_no,$q_accounting_type,$q_slip_no,$q_table_no,$q_slip_branch_no,$q_slip_series_no,$q_new_order_time,$q_add_order_time,$q_pay_time,$q_num_pair,$q_pos_num,$q_order_num,$q_pay_num,$q_free1_name,$q_free2_name,$q_restaurant_custom,$q_front_custom,$q_sale_custom,$q_income_custom,$q_through_num,$q_copy_number,$q_custom_base1,$q_item_score,$q_instore1_notax_price,$q_instore1_notax_score,$q_takeout1_notax_price,$q_takeout1_notax_score,$q_restaurant1_notax_price,$q_restaurant1_notax_score,$q_front1_notax_price,$q_front1_notax_score,$q_totalsale1_notax_price,$q_totalsale1_notax_score,$q_discount_notax_price,$q_audit_item_num,$q_pay_item_num,$q_floor_no) = pg_fetch_array($result,$i);
	$q_free1_name = str_replace("　"," ",$q_free1_name);
	$q_free2_name = str_replace("　"," ",$q_free2_name);
	$q_copy_number = str_replace("　"," ",$q_copy_number);
	$q_table_no = str_replace("　"," ",$q_table_no);
	$q_free1_name = trim($q_free1_name);
	$q_free2_name = trim($q_free2_name);
	$q_copy_number = trim($q_copy_number);
	$q_table_no = trim($q_table_no);

	$d_instore1_notax_price = number_format($q_instore1_notax_price);
	$d_takeout1_notax_price = number_format($q_takeout1_notax_price);
	$d_restaurant1_notax_price = number_format($q_restaurant1_notax_price);
	$d_front1_notax_price = number_format($q_front1_notax_price);
	$d_totalsale1_notax_price = number_format($q_totalsale1_notax_price);
	$d_discount_notax_price = number_format($q_discount_notax_price);
	if($q_new_order_time){
		$d_new_order_time = substr($q_new_order_time,0,4)."/".substr($q_new_order_time,4,2)."/".substr($q_new_order_time,6,2)." ".substr($q_new_order_time,8,2).":".substr($q_new_order_time,10,2).":".substr($q_new_order_time,12,2);
	}else{
		$d_new_order_time = "";
	}
	if($q_add_order_time){
		$d_add_order_time = substr($q_add_order_time,0,4)."/".substr($q_add_order_time,4,2)."/".substr($q_add_order_time,6,2)." ".substr($q_add_order_time,8,2).":".substr($q_add_order_time,10,2).":".substr($q_add_order_time,12,2);
	}else{
		$d_add_order_time = "";
	}
	if($q_pay_time){
		$d_pay_time = substr($q_pay_time,0,4)."/".substr($q_pay_time,4,2)."/".substr($q_pay_time,6,2)." ".substr($q_pay_time,8,2).":".substr($q_pay_time,10,2).":".substr($q_pay_time,12,2);
	}else{
		$d_pay_time = "";
	}

$ybase->ST_PRI .= <<<HTML
<tr>
HTML;
if($q_sale_date != $pre_sale_date){
	$d_day = substr($q_sale_date,8,2)."日";
}else{
	$d_day = "";
}
$pre_sale_date = $q_sale_date;
$ybase->ST_PRI .= <<<HTML
<td align="center">$d_day</td>
HTML;


$ybase->ST_PRI .= <<<HTML
<td align="right">$q_receipt_no</td>
<td align="right">$q_transaction_type</td>
<td align="right">$q_void_receipt_no</td>
<td align="right">$q_handler_no</td>
<td align="right">$q_charger_no</td>
<td align="right">$q_old_handler_no</td>
<td align="right">$q_old_charger_no</td>
<td align="right">$q_accounting_type</td>
<td align="right">$q_slip_no</td>
<td align="right">$q_table_no</td>
<td align="right">$q_slip_branch_no</td>
<td align="right">$q_slip_series_no</td>
<td align="right">$d_new_order_time</td>
<td align="right">$d_add_order_time</td>
<td align="right">$d_pay_time</td>
<td align="right">$q_num_pair</td>
<td align="right">$q_pos_num</td>
<td align="right">$q_order_num</td>
<td align="right">$q_pay_num</td>
<td align="center"><nobr>$q_free1_name</nobr></td>
<td align="center"><nobr>$q_free2_name</nobr></td>
<td align="right">$q_restaurant_custom</td>
<td align="right">$q_front_custom</td>
<td align="right">$q_sale_custom</td>
<td align="right">$q_income_custom</td>
<td align="right">$q_through_num</td>
<td align="right">$q_copy_number</td>
<td align="right">$q_custom_base1</td>
<td align="right">$q_item_score</td>
<td align="right">$d_instore1_notax_price</td>
<td align="right">$q_instore1_notax_score</td>
<td align="right">$d_takeout1_notax_price</td>
<td align="right">$q_takeout1_notax_score</td>
<td align="right">$d_restaurant1_notax_price</td>
<td align="right">$q_restaurant1_notax_score</td>
<td align="right">$d_front1_notax_price</td>
<td align="right">$q_front1_notax_score</td>
<td align="right">$d_totalsale1_notax_price</td>
<td align="right">$q_totalsale1_notax_score</td>
<td align="right">$d_discount_notax_price</td>
<td align="right">$q_audit_item_num</td>
<td align="right">$q_pay_item_num</td>
<td align="right">$q_floor_no</td>


</tr>

HTML;
$graphtxt .= "data.addRow(['{$d_day}$q_menu_name',$d_price]);\n";

}

$ybase->ST_PRI .= <<<HTML
</tbody>
</table>
</div>
</div>



</div>
<table width="100%">
<tr><td align="center">
<span id="target"></span>
</td></tr>
</table>
</div>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>