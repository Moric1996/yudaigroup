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
$ybase->shop_list['3001'] = "雄大ゴルフ熱函";	//雄大ゴルフ熱函
$ybase->shop_list['3002'] = "雄大ゴルフ清水町";	//雄大ゴルフ清水町
$ybase->make_employee_list();
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
if($selyymm){
	$yy = substr($selyymm,0,4);
	$mm = substr($selyymm,5,2);
	$tt = date('t',mktime(0,0,0,$mm,1,$yy));
}
if(!$yy || !$mm){
	$now_yy = date('Y');
	$now_mm = date('m');
	$now_dd = date('d');
	$yy = date('Y',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$mm = date('m',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$tt = date('t',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
}
$s_yymm = date("Y-m-d",mktime(0,0,0,$mm,1,$yy));
$e_yymm = date("Y-m-d",mktime(0,0,0,$mm,$tt,$yy));
$bf_yymm = date("Y-m-d",mktime(0,0,0,$mm,0,$yy));
$selyymm = "$yy-$mm";

$param = "shop_id=$shop_id&selyymm=$selyymm";

$conn = $ybase->connect();

$ybase->title = "店舗集計-割引集計";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#inshop_id,#inmonth').change(function(){
		$("#form1").submit();
	});
});


$(function(){
	$('a[delhref]').click(function(){
		var tday = $(this).attr('tarday');
		if(!confirm(tday + '日を確認済みしますか？')){
			return false;
		}else{
			location.href = $(this).attr('delhref');
		}
	});
});

</script>
<div class="container-fluid">

<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./ana_top.php?$param" role="button">戻る</a></div>
<div style="font-size:100%;margin:5px;">

<form action="ztotal_discount.php" method="post" id="form1">
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
<input type="month" name="selyymm" value="$selyymm" id="inmonth">
</form>
<p></p>

<div class="table-responsive">
HTML;

$sql = "select to_char(sale_date,'FMDD'),pos_ztotal_discount_id,ztotal_no,dis_item_name,dis_price,dis_count,dis_sheet from pos_ztotal_discount where pos_shopno = $pos_shopno and sale_date between '$s_yymm' and '$e_yymm' and status = '1' and dis_price <> 0 order by sale_date,ztotal_no";


//print $sql;
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$arr_item_name = array();
$all_price = array();
$all_count = array();
$all_sheet = array();
$col_all_price = array();
$col_all_count = array();
$col_all_sheet = array();
$arr_no = array();
for($i=0;$i<$num;$i++){
	list($q_sale_day,$q_id,$q_no,$q_item_name,$q_price,$q_count,$q_sheet) = pg_fetch_array($result,$i);
//	push_array($arr_no,$q_no);
	$arr_id[$q_sale_day][$q_no] = $q_id;
	$q_item_name = str_replace("　"," ",$q_item_name);
	$arr_item_name[$q_no] = trim($q_item_name);
	$arr_price[$q_sale_day][$q_no] = $q_price;
	$arr_count[$q_sale_day][$q_no] = $q_count;
	$arr_sheet[$q_sale_day][$q_no] = $q_sheet;
	$all_price[$q_no] += $q_price;
	$all_count[$q_no] += $q_count;
	$all_sheet[$q_no] += $q_sheet;
	$col_all_price[$q_sale_day] += $q_price;
	$col_all_count[$q_sale_day] += $q_count;
	$col_all_sheet[$q_sale_day] += $q_sheet;
}
ksort($arr_item_name);
$cols = count($arr_item_name);

$order = array("　", " ");

$ybase->ST_PRI .= <<<HTML
<table class="table table-sm table-bordered table-striped" id="table1" style="font-size:80%;">

<thead>
<tr align="center" bgcolor="#eacaca">
<th rowspan="2">日付</th>
HTML;
foreach($arr_item_name as $key => $val){
$val = str_replace($order,"<br>",$val);
$val = str_replace("<br><br>","<br>",$val);
$ybase->ST_PRI .= <<<HTML
<th colspan="3">$val</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
<th colspan="3">計</th>
</tr>
<tr align="center" bgcolor="#eacaca">
HTML;
foreach($arr_item_name as $key => $val){
$ybase->ST_PRI .= <<<HTML
<th width="50">金<br>額</th>
<th width="50">回<br>数</th>
<th width="50">枚<br>数</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
<th width="50">金<br>額</th>
<th width="50">回<br>数</th>
<th width="50">枚<br>数</th>
</tr>
</thead>

<tbody>
HTML;

$allall_price = 0;
$allall_count = 0;
$allall_sheet = 0;

for($i=1;$i<=$tt;$i++){
$ybase->ST_PRI .= <<<HTML
<tr>
<td align="right"><b>$i</b></td>
HTML;
foreach($arr_item_name as $key => $val){
	$form_price = number_format($arr_price[$i][$key]);
	$form_count = number_format($arr_count[$i][$key]);
	$form_sheet = number_format($arr_sheet[$i][$key]);

$ybase->ST_PRI .= <<<HTML
<td align="right"><spna style="color:#000055;">$form_price</span></td>
<td align="right"><spna style="color:#000000;">$form_count</span></td>
<td align="right"><spna style="color:#550000;">$form_sheet</span></td>
HTML;
}
	$allall_price += $col_all_price[$i];
	$allall_count += $col_all_count[$i];
	$allall_sheet += $col_all_sheet[$i];
	$form_price = number_format($col_all_price[$i]);
	$form_count = number_format($col_all_count[$i]);
	$form_sheet = number_format($col_all_sheet[$i]);

$ybase->ST_PRI .= <<<HTML
<td align="right"><spna style="color:#000055;"><b>$form_price</b></span></td>
<td align="right"><spna style="color:#000000;"><b>$form_count</b></span></td>
<td align="right"><spna style="color:#550000;"><b>$form_sheet</b></span></td>
</tr>
HTML;

}

$ybase->ST_PRI .= <<<HTML
<tr bgcolor="#dddddd">
<td>計</td>
HTML;
foreach($arr_item_name as $key => $val){
	$form_price = number_format($all_price[$key]);
	$form_count = number_format($all_count[$key]);
	$form_sheet = number_format($all_sheet[$key]);

$ybase->ST_PRI .= <<<HTML
<td align="right"><spna style="color:#000055;"><b>$form_price</b></span></td>
<td align="right"><spna style="color:#000000;"><b>$form_count</b></span></td>
<td align="right"><spna style="color:#550000;"><b>$form_sheet</b></span></td>
HTML;
}

	$form_price = number_format($allall_price);
	$form_count = number_format($allall_count);
	$form_sheet = number_format($allall_sheet);
$ybase->ST_PRI .= <<<HTML
<td align="right"><spna style="color:#000055;"><b>$form_price</b></span></td>
<td align="right"><spna style="color:#000000;"><b>$form_count</b></span></td>
<td align="right"><spna style="color:#550000;"><b>$form_sheet</b></span></td>
</tr>
</tbody>
</table>
</div>
</div>



</div>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>