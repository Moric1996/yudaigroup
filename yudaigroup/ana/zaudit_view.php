<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include_once(dirname(__FILE__).'/inc/code_list.inc');

$ybase = new ybase();
$ybase->session_get();
//$edit_f = 1;
/////////////////////////////////////////

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
}
$conn = $ybase->connect();

$ybase->title = "店舗分析-オーダー担当者集計";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#inshop_id,#inmonth,#ttype,#monthon').change(function(){
		$("#form1").submit();
	});
});

</script>
<div class="container-fluid">

<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./ana_top.php?$param" role="button">戻る</a></div>

<div style="font-size:80%;margin:5px;">

<form action="zaudit_view.php" method="post" id="form1">
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


</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th>日付</th>
<th>監査区分名称</th>
<th>レシートNO</th>
<th>伝票NO</th>
<th>担当者</th>
<th>扱者</th>
<th>オーダー/会計時刻</th>
<th>合計金額</th>
<th>元担当者</th>
<th>元扱者</th>
<th>取消日時</th>
<th>メニューコード</th>
<th>商品名称</th>
<th>商品区分</th>
<th>単価</th>
<th>数量</th>
</tr>
</thead>

<tbody>
HTML;
$g_count=0;
$g_custom=0;
$g_price=0;

if($monthon){
	$date_sql = "between '{$selyymm}-01' and '{$selyymm}-{$maxday}'";
	$g_width = 2000;
}else{
	$date_sql = "= '$selyymmdd'";
	$g_width = 1000;
}
$sql = "select sale_date,pos_zaudit_id,audit_name,receipt_no,slip_no,charger_name,handler_name,check_time,sum_price,old_charger_name,old_handler_name,reason_code,void_receipt_no,void_time,menu_code,menu_name,menu_cate_name,unit_prince,volume from pos_zaudit where pos_shopno = $pos_shopno and sale_date {$date_sql} and status = '1' order by sale_date,pos_zaudit_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$graphtxt = "";

for($i=0;$i<$num;$i++){
	list($q_sale_date,$q_pos_zaudit_id,$q_audit_name,$q_receipt_no,$q_slip_no,$q_charger_name,$q_handler_name,$q_check_time,$q_sum_price,$q_old_charger_name,$q_old_handler_name,$q_reason_code,$q_void_receipt_no,$q_void_time,$q_menu_code,$q_menu_name,$q_menu_cate_name,$q_unit_prince,$q_volume) = pg_fetch_array($result,$i);
	$q_audit_name = str_replace("　"," ",$q_audit_name);
	$q_charger_name = str_replace("　"," ",$q_charger_name);
	$q_audit_name = str_replace("　"," ",$q_handler_name);
	$q_old_charger_name = str_replace("　"," ",$q_old_charger_name);
	$q_old_handler_name = str_replace("　"," ",$q_old_handler_name);
	$q_audit_name = trim($q_audit_name);
	$q_charger_name = trim($q_charger_name);
	$q_handler_name = trim($q_handler_name);
	$q_old_charger_name = trim($q_old_charger_name);
	$q_old_handler_name = trim($q_old_handler_name);
	$q_menu_name = trim($q_menu_name);
	$q_menu_cate_name = trim($q_menu_cate_name);
	$d_sum_price = number_format($q_sum_price);
	$d_unit_prince = number_format($q_unit_prince);
	if($q_check_time){
		$d_check_time = substr($q_check_time,0,4)."/".substr($q_check_time,4,2)."/".substr($q_check_time,6,2)." ".substr($q_check_time,8,2).":".substr($q_check_time,10,2);
	}else{
		$d_check_time = "";
	}
	if($q_void_time){
		$d_void_time = substr($q_void_time,0,4)."/".substr($q_void_time,4,2)."/".substr($q_void_time,6,2)." ".substr($q_void_time,8,2).":".substr($q_void_time,10,2);
	}else{
		$d_void_time = "";
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
<td align="center">$q_audit_name</td>
<td align="right">$q_receipt_no</td>
<td align="right">$q_slip_no</td>
<td align="right">$q_charger_name</td>
<td align="right">$q_handler_name</td>
<td align="right">$d_check_time</td>
<td align="right">$q_sum_price</td>
<td align="right">$q_old_charger_name</td>
<td align="right">$q_old_handler_name</td>
<td align="right">$d_void_time</td>
<td align="right">$q_menu_code</td>
<td align="right">$q_menu_name</td>
<td align="right">$q_menu_cate_name</td>
<td align="right">$q_unit_prince</td>
<td align="right">$q_volume</td>
</tr>
HTML;
$graphtxt .= "data.addRow(['{$d_day}{$q_employee_name}',$d_order_price]);\n";

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