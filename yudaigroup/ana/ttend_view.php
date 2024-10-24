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

$ybase->title = "店舗分析-支払明細トランザクション";

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

<form action="ttend_view.php" method="post" id="form1">
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
<th>レシート<br>NO</th>
<th>メディア<br>種別</th>
<th>メディア<br>名称</th>
<th>処理<br>種別1</th>
<th>処理<br>種別2</th>
<th>明細<br>種別</th>
<th>券種<br>コード</th>
<th>券名称</th>
<th>発行店番</th>
<th>値引/割引<br>種別</th>
<th>値引/割引<br>ステータス</th>
<th>値割引対象額</th>
<th>単価</th>
<th>枚数</th>
<th>残枚数</th>
<th>預かり<br>金額</th>
<th>支払<br>金額</th>
<th>釣銭(差額)<br>金額</th>
<th>釣銭(差額)<br>種別</th>
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
$sql = "select pos_ttend_id,sale_date,receipt_no,media_type, media_name,type1,type2,type_detail,note_code,note_item_name,pub_pos_shopno,discount_type,discount_status,discount_target_price,unit_prince,pay_sheet,remnant_sheet,deposit_price,pay_price,change_price,change_type from pos_ttend where pos_shopno = $pos_shopno and sale_date {$date_sql} and status = '1' order by sale_date,pos_ttend_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$graphtxt = "";
$g_price = 0;
$g_score = 0;
for($i=0;$i<$num;$i++){
	list($q_pos_ttend_id,$q_sale_date,$q_receipt_no,$q_media_type,$q_media_name,$q_type1,$q_type2,$q_type_detail,$q_note_code,$q_note_item_name,$q_pub_pos_shopno,$q_discount_type,$q_discount_status,$q_discount_target_price,$q_unit_prince,$q_pay_sheet,$q_remnant_sheet,$q_deposit_price,$q_pay_price,$q_change_price,$q_change_type) = pg_fetch_array($result,$i);
	$q_media_name = str_replace("　"," ",$q_media_name);
	$q_note_item_name = str_replace("　"," ",$q_note_item_name);
	$q_media_name = trim($q_media_name);
	$q_note_item_name = trim($q_note_item_name);

	$d_discount_target_price = number_format($q_discount_target_price);
	$d_unit_prince = number_format($q_unit_prince);
	$d_deposit_price = number_format($q_deposit_price);
	$d_pay_price = number_format($q_pay_price);
	$d_change_price = number_format($q_change_price);


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
<td align="right">$q_media_type</td>
<td align="right">$q_media_name</td>
<td align="right">$q_type1</td>
<td align="right">$q_type2</td>
<td align="right">$q_type_detail</td>
<td align="right">$q_note_code</td>
<td align="right">$q_note_item_name</td>
<td align="right">$q_pub_pos_shopno</td>
<td align="right">$q_discount_type</td>
<td align="right">$q_discount_status</td>
<td align="right">$d_discount_target_price</td>
<td align="right">$d_unit_prince</td>
<td align="right">$q_pay_sheet</td>
<td align="right">$q_remnant_sheet</td>
<td align="right">$d_deposit_price</td>
<td align="right">$d_pay_price</td>
<td align="right">$d_change_price</td>
<td align="right">$q_change_type</td>


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