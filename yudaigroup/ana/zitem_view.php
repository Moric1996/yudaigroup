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
/*
$sql = "select free_class from pos_zitem where pos_shopno = $pos_shopno and to_char(sale_date,'YYYY-MM') = '$selyymm' and status = '1' group by free_class order by free_class";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$free_class_list = pg_fetch_all_columns($result,0);
}
*/
$ybase->title = "店舗分析-商品別集計";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#inshop_id,#inmonth,#ttype,#monthon,#free_class_id,#sel_menu_code,#sel_menu_name').change(function(){
		$("#form1").submit();
	});
});

</script>
<div class="container-fluid">

<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./ana_top.php?$param" role="button">戻る</a></div>

<div style="font-size:80%;margin:5px;">

<form action="zitem_view.php" method="post" id="form1">
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
if($shop_id === 'all'){
	$allselected = " selected";
}else{
	$allselected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="all"{$allselected}>全店</option>
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


if($shop_id === 'all'){
	$tablehd_ad = "<th rowspan=\"2\">店舗名</th>";
}else{
	$tablehd_ad = "";
}

$ybase->ST_PRI .= <<<HTML
メニューコード<input type="text" name="sel_menu_code" value="$sel_menu_code" id="sel_menu_code">　
商品名称<input type="text" name="sel_menu_name" value="$sel_menu_name" id="sel_menu_name">　

</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
{$tablehd_ad}
<th rowspan="2">日付</th>
<th rowspan="2">メニューコード</th>
<th rowspan="2">商品名称</th>
<th rowspan="2">商品区分</th>
<th rowspan="2">親メニュー</th>
<th rowspan="2">単価</th>
<th rowspan="2">テイクアウト区分</th>
<th rowspan="2">原単価</th>
<th rowspan="2">ＧＰ名称</th>
<th rowspan="2">ＤＰ名称</th>
<th colspan="24">時間帯毎点数</th>
<th rowspan="2">計点数</th>
<th rowspan="2">計金額</th>
</tr>
<tr align="center" bgcolor="#eacaca">
HTML;
for($i=1;$i<=24;$i++){

$ybase->ST_PRI .= <<<HTML
<th>$i</th>
HTML;

}

$ybase->ST_PRI .= <<<HTML
</tr>
</thead>

<tbody>
HTML;
$g_sale=0;
$g_score=0;
$g_custom=0;
$date_sql = "";
if($monthon){
	$date_sql = "between '{$selyymm}-01' and '{$selyymm}-{$maxday}'";
	$g_width = 2000;
}else{
	$date_sql = "= '$selyymmdd'";
	$g_width = 1000;
}
if(preg_match("/^[0-9]+$/",$sel_menu_code)){
	$date_sql .= " and menu_code = '$sel_menu_code'";
}
if($sel_menu_name){
	$date_sql .= " and menu_name ~'$sel_menu_name'";
}

if($shop_id === 'all'){
	$sql_shop = "";
}else{
	$sql_shop = "pos_shopno = $pos_shopno and ";
}


$sql = "select pos_shopno,sale_date,menu_code,max(menu_name),max(menu_cate_name),max(parent_menu_name),max(unit_prince),max(takeout_class_name),max(unit_cost),max(gp_name),max(dp_name),sum(timezone01_score),sum(timezone02_score),sum(timezone03_score),sum(timezone04_score),sum(timezone05_score),sum(timezone06_score),sum(timezone07_score),sum(timezone08_score),sum(timezone09_score),sum(timezone10_score),sum(timezone11_score),sum(timezone12_score),sum(timezone13_score),sum(timezone14_score),sum(timezone15_score),sum(timezone16_score),sum(timezone17_score),sum(timezone18_score),sum(timezone19_score),sum(timezone20_score),sum(timezone21_score),sum(timezone22_score),sum(timezone23_score),sum(timezone24_score) from pos_zitem where {$sql_shop}sale_date {$date_sql} and status = '1' group by pos_shopno,sale_date,menu_code order by pos_shopno,sale_date,menu_code";






$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$graphtxt = "";
$g_sc = array();
$g_price = 0;
$g_score = 0;
for($i=0;$i<$num;$i++){
	list($q_pos_shopno,$q_sale_date,$q_menu_code,$q_menu_name,$q_menu_cate_name,$q_parent_menu_name,$q_unit_prince,$q_takeout_class_name,$q_unit_cost,$q_gp_name,$q_dp_name,$sc01,$sc02,$sc03,$sc04,$sc05,$sc06,$sc07,$sc08,$sc09,$sc10,$sc11,$sc12,$sc13,$sc14,$sc15,$sc16,$sc17,$sc18,$sc19,$sc20,$sc21,$sc22,$sc23,$sc24) = pg_fetch_array($result,$i);
	$q_menu_name = trim($q_menu_name);
	$q_menu_cate_name = trim($q_menu_cate_name);
	$q_parent_menu_name = trim($q_parent_menu_name);
	$q_takeout_class_name = trim($q_takeout_class_name);
	$q_gp_name = trim($q_gp_name);
	$q_dp_name = trim($q_dp_name);
	$d_totalsale1_notax_price = number_format($q_totalsale1_notax_price);
	$yokog=0;
	for($k=1;$k<=24;$k++){
		$vname = "sc".sprintf('%02d',$k);
		$g_sc[$k] += ${$vname};
		$yokog += ${$vname};
	}
	$price = $yokog * $q_unit_prince;
	$d_price = number_format($price);
	$g_price += $price;
	$g_score += $yokog;

$pos_shopno = $ybase->section_to_pos[$shop_id];

$ybase->ST_PRI .= <<<HTML
<tr>
HTML;
if($q_sale_date != $pre_sale_date){
	$d_day = substr($q_sale_date,8,2)."日";
}else{
	$d_day = "";
}
$pre_sale_date = $q_sale_date;

$shop_id_arrs = array_keys($ybase->section_to_pos,$q_pos_shopno);
$t_shop_id = $shop_id_arrs[0];
$t_shop_name = $ybase->shop_list[$t_shop_id];
if($shop_id === 'all'){
$ybase->ST_PRI .= <<<HTML
<td align="center">$t_shop_name</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
<td align="center">$d_day</td>
HTML;


$ybase->ST_PRI .= <<<HTML
<td align="center">$q_menu_code</td>
<td align="center">$q_menu_name</td>
<td align="center">$q_menu_cate_name</td>
<td align="center">$q_parent_menu_name</td>
<td align="right">$q_unit_prince</td>
<td align="center">$q_takeout_class_name</td>
<td align="right">$q_unit_cost</td>
<td align="center">$q_gp_name</td>
<td align="center">$q_dp_name</td>
HTML;
for($k=1;$k<=24;$k++){
	$vname = "sc".sprintf('%02d',$k);
	$dscore = ${$vname};
$ybase->ST_PRI .= <<<HTML
<td align="right">$dscore</td>
HTML;

}

$ybase->ST_PRI .= <<<HTML
<td align="right">$yokog</td>
<td align="right">$d_price</td>
</tr>
HTML;
$graphtxt .= "data.addRow(['{$d_day}$q_menu_name',$d_price]);\n";

}

$g_price = number_format($g_price);
$g_score = number_format($g_score);

if($shop_id === 'all'){
	$colspannum = 11;
}else{
	$colspannum = 10;
}
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="#dddddd">
<td colspan="$colspannum" align="center">合計</td>
HTML;
	for($k=1;$k<=24;$k++){
$ybase->ST_PRI .= <<<HTML
<td align="right">{$g_sc[$k]}</td>
HTML;
	}

$ybase->ST_PRI .= <<<HTML
<td align="right">$g_score</td>
<td align="right">$g_price</td>
</tr>
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