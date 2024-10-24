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

$ybase->title = "店舗分析-商品明細トランザクション";

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

<form action="titem_view.php" method="post" id="form1">
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
<th>日付</th>
<th>レシートNO</th>
<th>商品種別</th>
<th>処理種別1</th>
<th>処理種別2</th>
<th>商品コード</th>
<th>商品名称</th>
<th>伝票NO</th>
<th>テーブルNO</th>
<th>オーダー時刻</th>
<th>親メニュー</th>
<th>テイクアウト</th>
<th>ST1</th>
<th>ST2</th>
<th>ST3</th>
<th>ST4</th>
<th>単価</th>
<th>数量</th>
<th>残数量</th>
<th>合計金額</th>
<th>原単価</th>
<th>DP</th>
<th>GP</th>
<th>原単価</th>
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
$sql = "select sale_date,pos_titem_id,receipt_no,menu_kind,process_type1,process_type2,menu_code,menu_name,slip_no,table_no,order_time,parent_menu_name,takeout_flag,st1,st2,st3,st4,unit_prince,volume,remain_volume,total_price,unit_cost,linkdp_code,linkgp_code,tax_type from pos_titem where pos_shopno = $pos_shopno and sale_date {$date_sql} and status = '1' order by sale_date,pos_titem_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$graphtxt = "";
$g_price = 0;
$g_score = 0;
for($i=0;$i<$num;$i++){
	list($q_sale_date,$q_pos_titem_id,$q_receipt_no,$q_menu_kind,$q_process_type1,$q_process_type2,$q_menu_code,$q_menu_name,$q_slip_no,$q_table_no,$q_order_time,$q_parent_menu_name,$q_takeout_flag,$q_st1,$q_st2,$q_st3,$q_st4,$q_unit_prince,$q_volume,$q_remain_volume,$q_total_price,$q_unit_cost,$q_linkdp_code,$q_linkgp_code,$q_tax_type) = pg_fetch_array($result,$i);
	$q_menu_name = str_replace("　"," ",$q_menu_name);
	$q_parent_menu_name = str_replace("　"," ",$q_parent_menu_name);
	$q_menu_name = trim($q_menu_name);
	$q_parent_menu_name = trim($q_parent_menu_name);

	$d_unit_prince = number_format($q_unit_prince);
	$d_total_price = number_format($q_total_price);
	if($q_order_time){
		$d_order_time = substr($q_order_time,0,2).":".substr($q_order_time,2,2);
	}else{
		$d_order_time = "";
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
<td align="center">$q_menu_kind</td>
<td align="right">$q_process_type1</td>
<td align="right">$q_process_type2</td>
<td align="right">$q_menu_code</td>
<td align="center">$q_menu_name</td>
<td align="right">$q_slip_no</td>
<td align="right">$q_table_no</td>
<td align="center">$d_order_time</td>
<td align="center">$q_parent_menu_name</td>
<td align="center">$q_takeout_flag</td>
<td align="right">$q_st1</td>
<td align="right">$q_st2</td>
<td align="right">$q_st3</td>
<td align="right">$q_st4</td>
<td align="right">$d_unit_prince</td>
<td align="right">$q_volume</td>
<td align="right">$q_remain_volume</td>
<td align="right">$d_total_price</td>
<td align="right">$q_unit_cost</td>
<td align="right">$q_linkdp_code</td>
<td align="right">$q_linkgp_code</td>
<td align="right">$q_unit_cost</td>
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