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

$targetshop = array(106,109,112);
if(isset($target_shop) && !isset($shop_id)){
	$shop_id = $target_shop;
}
if(isset($target_date) && !isset($selyymm)){
	$selyymm = substr($target_date,0,7);
}
$monthon = 1;
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
$maxday = date('t',mktime(0,0,0,$mm,1,$yy));

}
if($free_class_id == ""){
	$free_class_id = 1;
}

$conn = $ybase->connect();

$add_sql = " and dp_code in (2,3,4,5,6,7,8)";


$sql = "select sum(allsale_intax) from pos_ztotal_sales where pos_shopno = $pos_shopno and sale_date between '{$selyymm}-01' and '{$selyymm}-{$maxday}'";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$all_sale_intax = pg_fetch_result($result,0);
}

$sql = "select sum(timezone01_score + timezone02_score + timezone03_score + timezone04_score + timezone05_score + timezone06_score + timezone07_score + timezone08_score + timezone09_score + timezone10_score + timezone11_score + timezone12_score + timezone13_score + timezone14_score + timezone15_score + timezone16_score + timezone17_score + timezone18_score + timezone19_score + timezone20_score + timezone21_score + timezone22_score + timezone23_score + timezone24_score) from pos_zitem where pos_shopno = $pos_shopno and sale_date between '{$selyymm}-01' and '{$selyymm}-{$maxday}' and unit_prince > 0 and status = '1'";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$all_count0 = pg_fetch_result($result,0);
}

$sql = "select sum(timezone01_score + timezone02_score + timezone03_score + timezone04_score + timezone05_score + timezone06_score + timezone07_score + timezone08_score + timezone09_score + timezone10_score + timezone11_score + timezone12_score + timezone13_score + timezone14_score + timezone15_score + timezone16_score + timezone17_score + timezone18_score + timezone19_score + timezone20_score + timezone21_score + timezone22_score + timezone23_score + timezone24_score) from pos_zitem where pos_shopno = $pos_shopno and sale_date between '{$selyymm}-01' and '{$selyymm}-{$maxday}'{$add_sql} and unit_prince = 0 and status = '1'";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$all_count1 = pg_fetch_result($result,0);
}
$all_count = $all_count0 + $all_count1;

$ybase->title = "店舗分析-カルビ一丁食べ放題分析";

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

<div style="font-size:80%;margin:3px;">

<form action="zitem_view_nolimit.php" method="post" id="form1">
<select name="shop_id" id="inshop_id">
<option value="">選択してください</option>
HTML;
foreach($ybase->shop_list as $key => $val){
	if(!in_array($key,$targetshop)){
		continue;
	}
	if($shop_id == $key){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"{$addselect}>$val</option>
HTML;
}
//if($monthon == 1){
	$monthon_checked = " checked";
	$selectdate = "<input type=\"month\" name=\"selyymm\" value=\"$selyymm\" id=\"inmonth\">";
//}else{
//	$monthon_checked = "";
//	$selectdate = "<input type=\"date\" name=\"selyymmdd\" value=\"$selyymmdd\" id=\"inmonth\">";
//}
$ybase->ST_PRI .= <<<HTML

</select>
$selectdate
<!----<input type="checkbox" name="monthon" value="1" id="monthon"{$monthon_checked}>月表示　---->
<input type="hidden" name="monthon" value="1" id="monthon">
<input type="hidden" name="target_date" value="$target_date">
<input type="hidden" name="target_shop" value="$target_shop">
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
if($target_shop){
$ybase->ST_PRI .= <<<HTML

&nbsp;<a href="../manager_pdca/meeting_top.php?target_date=$target_date&target_shop=$target_shop" class="btn btn-info btn-sm">店長会議資料に戻る</a>
HTML;
}
$ybase->ST_PRI .= <<<HTML

</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th rowspan="2">MUNU<br>CODE</th>
<th rowspan="2">商品名称</th>
<th rowspan="2">単価</th>
<th rowspan="2">DP<br>CODE</th>
<th rowspan="2">ＤＰ名称</th>
<th colspan="24">時間帯毎点数</th>
<th rowspan="2">計点数</th>
<th rowspan="2">計金額</th>
<th rowspan="2">点数シェア</th>
<th rowspan="2">売上シェア</th>
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
if($monthon){
	$date_sql = "between '{$selyymm}-01' and '{$selyymm}-{$maxday}'";
	$g_width = 2000;
}else{
	$date_sql = "= '$selyymmdd'";
	$g_width = 1000;
}
$sql = "select dp_code,menu_code,max(menu_name),max(unit_prince),max(dp_name),sum(timezone01_score),sum(timezone02_score),sum(timezone03_score),sum(timezone04_score),sum(timezone05_score),sum(timezone06_score),sum(timezone07_score),sum(timezone08_score),sum(timezone09_score),sum(timezone10_score),sum(timezone11_score),sum(timezone12_score),sum(timezone13_score),sum(timezone14_score),sum(timezone15_score),sum(timezone16_score),sum(timezone17_score),sum(timezone18_score),sum(timezone19_score),sum(timezone20_score),sum(timezone21_score),sum(timezone22_score),sum(timezone23_score),sum(timezone24_score) from pos_zitem where pos_shopno = $pos_shopno and sale_date {$date_sql}{$add_sql} and status = '1' group by dp_code,menu_code order by dp_code,menu_code";






$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$graphtxt = "";
$g_sc = array();
$g_price = 0;
$g_score = 0;
for($i=0;$i<$num;$i++){
	list($q_gp_code,$q_menu_code,$q_menu_name,$q_unit_prince,$q_dp_name,$sc01,$sc02,$sc03,$sc04,$sc05,$sc06,$sc07,$sc08,$sc09,$sc10,$sc11,$sc12,$sc13,$sc14,$sc15,$sc16,$sc17,$sc18,$sc19,$sc20,$sc21,$sc22,$sc23,$sc24) = pg_fetch_array($result,$i);
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
	$d_rate = round(($price/$all_sale_intax)*100,2);
	$d_price = number_format($price);
	$u_rate = round(($yokog/$all_count)*100,2);
	$g_price += $price;
	$g_score += $yokog;
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
HTML;


$ybase->ST_PRI .= <<<HTML
<td align="center">$q_menu_code</td>
<td align="center"><nobr>$q_menu_name</nobr></td>
<td align="right">$q_unit_prince</td>
<td align="center">$q_gp_code</td>
<td align="center"><nobr>$q_dp_name</nobr></td>
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
<td align="right">{$u_rate}%</td>
<td align="right">{$d_rate}%</td>
</tr>
HTML;
$graphtxt .= "data.addRow(['{$d_day}$q_menu_name',$d_price]);\n";

}
if($all_sale_intax){
	$g_rate = round(($g_price/$all_sale_intax)*100,2);
}else{
	$g_rate = 0.00;
}
if($all_count){
	$c_rate = round(($g_score/$all_count)*100,2);
}else{
	$c_rate = 0.00;
}
$g_price = number_format($g_price);
$g_score = number_format($g_score);
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="#dddddd">
<td colspan="5" align="center">合計</td>
HTML;
	for($k=1;$k<=24;$k++){
$ybase->ST_PRI .= <<<HTML
<td align="right">{$g_sc[$k]}</td>
HTML;
	}
$all_sale_intax = number_format($all_sale_intax);
$ybase->ST_PRI .= <<<HTML
<td align="right">$g_score</td>
<td align="right">$g_price</td>
<td align="right">{$c_rate}%<br>全点数($all_count)</td>
<td align="right">{$g_rate}%<br>全売上($all_sale_intax)</td>
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
※全点数は無料の食べ放題以外の無料のものは除く
</div>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>