<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

//$edit_f = 1;
/////////////////////////////////////////

$ybase->make_shop_list();
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

$conn = $ybase->connect();
$sql = "select sum(pay_price) from pos_ttend where pos_shopno = $pos_shopno and sale_date = '$selyymmdd' and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	list($all_price) = pg_fetch_array($result,0);
}else{
	$all_price = 0;
}

$receiptlist="";
$sql = "select distinct receipt_no from pos_ttend where pos_shopno = $pos_shopno and sale_date = '$selyymmdd' and status = '1' and media_type = 3 and type_detail in (12,13,14,18,19,20)";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
for($i=0;$i<$num;$i++){
	list($q_receipt_no) = pg_fetch_array($result,$i);
	if($receiptlist){
		$receiptlist .= ",";
	}
	$receiptlist .= "$q_receipt_no";
}

if(!$receiptlist){
	$receiptlist = -1;
}


$ybase->title = "店舗分析-GOtoEAT";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#inshop_id,#inmonth').change(function(){
		$("#form1").submit();
	});
});

</script>
<div class="container">

<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./ana_top.php?$param" role="button">戻る</a></div>
<div style="font-size:100%;margin:5px;">

<form action="gotoeat.php" method="post" id="form1">
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

$ybase->ST_PRI .= <<<HTML

</select>
<input type="date" name="selyymmdd" value="$selyymmdd" id="inmonth">
</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th>メディア名称</th>
<th>明細種別</th>
<th>金額</th>
<th>回数</th>
</tr>
</thead>

<tbody>
HTML;
$sql = "select media_type,type_detail,max(media_name),sum(pay_price),max(note_item_name),count(*) from pos_ttend where pos_shopno = $pos_shopno and sale_date = '$selyymmdd' and status = '1' and receipt_no in ($receiptlist) group by media_type,type_detail";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$graphtxt = "";
$sum_goto=0;
$sum_price = 0;
$sum_count = 0;
for($i=0;$i<$num;$i++){
	list($q_media_type,$q_type_detail,$q_media_name,$q_pay_price,$q_note_item_name,$q_count) = pg_fetch_array($result,$i);
	if(($q_media_type == 2)||($q_media_type == 3)){
		$type_detail_name = $ybase->media_detail_list[$q_type_detail];
		if((($q_type_detail >= 12)&&($q_type_detail <= 14))||(($q_type_detail >= 18)&&($q_type_detail <= 20))){
			$sum_goto += $q_pay_price;
		}
	}elseif(($q_media_type == 11)||($q_media_type == 12)){
		$q_note_item_name = trim($q_note_item_name);
		$type_detail_name = "$q_note_item_name";
	}else{
		$type_detail_name = "";
	}
	$sum_price += $q_pay_price;
	$sum_count += $q_count;
	$q_media_name = trim($q_media_name);
	$num_price = number_format($q_pay_price);
	$num_count = number_format($q_count);
$ybase->ST_PRI .= <<<HTML
<tr>
<td>$q_media_name</td>
<td>$type_detail_name</td>
<td align="right">$num_price</td>
<td align="right">$num_count</td>
</tr>
HTML;
//$graphtxt .= "data.addRow(['$q_pay_item_name',$q_pay_price]);\n";

}
if($all_price){
$rate = round($sum_price/$all_price*100, 2);
}
$num_all_price = number_format($all_price);
$num_price = number_format($sum_price);
$num_count = number_format($sum_count);
$ybase->ST_PRI .= <<<HTML
</tbody>
<tr bgcolor="#cdcdcd">
<td>合計</td>
<td></td>
<td align="right">$num_price</td>
<td align="right">$num_count</td>
</tr>
<tr>
<td>効果</td>
<td colspan="3" align="right">
全売上<b>{$num_all_price}円</b>のうちGOtoEATによる売上<b>{$num_price}円</b>  
(<b>{$rate}%</b>)

</td>
</tr>
</table>
※金額は税込みで割引も含みます
</div>
</div>
<div id="target"></div>



</div>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>