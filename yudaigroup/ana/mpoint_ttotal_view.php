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

$ybase->title = "ポイントカード-POSログ確認";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);

$pm = "shop_id=$shop_id&selyymm=$selyymm&selyymmdd=$selyymmdd";

$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#inshop_id,#inmonth,#ttype,#monthon,#free_class_id').change(function(){
		$("#form1").submit();
	});
});

</script>

<div class="container">
  <ul class="nav nav-tabs nav-fill" id="myTab" style="font-size:70%;">
    <li class="nav-item">
      <a href="./mpoint_person_cnt.php?$pm" class="nav-link" data-toggle="tab">個人別集計</a>
    </li>
    <li class="nav-item">
      <a href="./mpoint_sum.php?$pm" class="nav-link" data-toggle="tab">店舗別ポイント集計</a>
    </li>
    <li class="nav-item">
      <a href="./mpoint_ttotal_pt.php?$pm" class="nav-link" data-toggle="tab">POS履歴突合せ</a>
    </li>
    <li class="nav-item">
      <a href="./mpoint_pt_view.php?$pm" class="nav-link" data-toggle="tab">ポイントログ確認</a>
    </li>
    <li class="nav-item">
      <a href="mpoint_ttotal_view.php?$pm" class="nav-link active" data-toggle="tab">POSログ確認</a>
    </li>
  </ul>
</div>

<div class="container-fluid">

<p></p>

<div style="font-size:80%;margin:5px;">

<form action="mpoint_ttotal_view.php" method="post" id="form1">
<select name="shop_id" id="inshop_id">
<option value="">選択してください</option>
HTML;
foreach($ybase->mpt_shop_list as $key => $val){
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
<th>日付</th>
<th>ID</th>
<th>レシートNO</th>
<th>取引種別</th>
<th>VOID<br>レシートNO</th>
<th>扱者NO</th>
<th>担当者NO</th>
<th>会計種別</th>
<th>伝票一連NO</th>
<th>会計日時</th>
<th>客数</th>
<th>税抜売上計</th>
<th>税込売上1計</th>
<th>税込売上2計</th>
<th>税込収入1計</th>
<th>税込収入2計</th>
<th>税込支払計</th>
<th>外税込売上計</th>
<th>割引税抜</th>
<th>割引税込</th>
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
$sql = "select sale_date,pos_ttotal_id,receipt_no,transaction_type,void_receipt_no,handler_no,charger_no,accounting_type,slip_series_no,pay_time,sale_custom,totalsale1_notax_price,totalsale2_intax_price,totalsale4_intax_price,income2_intax_price,income4_intax_price,total_pay2_intax_price,totalsale5_outtax_price,discount_price,discount_notax_price from pos_ttotal where pos_shopno = $pos_shopno and sale_date {$date_sql} and status = '1' order by sale_date,pos_ttotal_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$graphtxt = "";
$g_price = 0;
$g_score = 0;
for($i=0;$i<$num;$i++){
	list($q_sale_date,$q_pos_ttotal_id,$q_receipt_no,$q_transaction_type,$q_void_receipt_no,$q_handler_no,$q_charger_no,$q_accounting_type,$q_slip_series_no,$q_pay_time,$q_sale_custom,$q_totalsale1_notax_price,$q_totalsale2_intax_price,$q_totalsale4_intax_price,$q_income2_intax_price,$q_income4_intax_price,$q_total_pay2_intax_price,$q_totalsale5_outtax_price,$q_discount_price,$q_discount_notax_price) = pg_fetch_array($result,$i);
	$q_free1_name = str_replace("　"," ",$q_free1_name);
	$q_free2_name = str_replace("　"," ",$q_free2_name);
	$q_copy_number = str_replace("　"," ",$q_copy_number);
	$q_table_no = str_replace("　"," ",$q_table_no);
	$q_free1_name = trim($q_free1_name);
	$q_free2_name = trim($q_free2_name);
	$q_copy_number = trim($q_copy_number);
	$q_table_no = trim($q_table_no);

	$d_totalsale2_intax_price = number_format($q_totalsale2_intax_price);
	$d_totalsale4_intax_price = number_format($q_totalsale4_intax_price);
	$d_income2_intax_price = number_format($q_income2_intax_price);
	$d_income4_intax_price = number_format($q_income4_intax_price);
	$d_total_pay2_intax_price = number_format($q_total_pay2_intax_price);
	$d_totalsale5_outtax_price = number_format($q_totalsale5_outtax_price);
	$d_discount_price = number_format($q_discount_price);
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
<td align="right">$q_pos_ttotal_id</td>
<td align="right">$q_receipt_no</td>
<td align="right">$q_transaction_type</td>
<td align="right">$q_void_receipt_no</td>
<td align="right">$q_handler_no</td>
<td align="right">$q_charger_no</td>
<td align="right">$q_accounting_type</td>
<td align="right">$q_slip_series_no</td>
<td align="right">$d_pay_time</td>
<td align="right">$q_sale_custom</td>
<td align="right">$d_totalsale1_notax_price</td>
<td align="right">$d_totalsale2_intax_price</td>
<td align="right">$d_totalsale4_intax_price</td>
<td align="right">$d_income2_intax_price</td>
<td align="right">$d_income4_intax_price</td>
<td align="right">$d_total_pay2_intax_price</td>
<td align="right">$d_totalsale5_outtax_price</td>
<td align="right">$d_discount_notax_price</td>
<td align="right">$d_discount_price</td>
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