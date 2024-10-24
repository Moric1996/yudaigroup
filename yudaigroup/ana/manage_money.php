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

$ybase->title = "店舗-レジ現金出納";

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
<div class="container">

<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./ana_top.php?$param" role="button">戻る</a></div>
<div style="font-size:100%;margin:5px;">

<form action="manage_money.php" method="post" id="form1">
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

<table class="table table-sm table-bordered table-striped" id="table1" style="font-size:80%;">

<thead>
<tr align="center" bgcolor="#eacaca">
<th rowspan="2">日付</th>
<th rowspan="2" width="80">前日<br>レジ残</th>
<th rowspan="2" width="80">釣銭<br>準備金</th>
<th rowspan="2" width="80">当日銀行<br>入金</th>
<th rowspan="2" width="80">当日<br>スタート<br>レジ残</th>
<th rowspan="2" width="80">当日売上<br>(税込)</th>
<th colspan="5" width="80">内訳(税込)</th>
<th rowspan="2" width="80">繰越<br>レジ残<br>(理論)</th>
<th rowspan="2" width="80">過不足</th>
<th rowspan="2" width="80">繰越<br>レジ残<br>(実際)</th>
<th rowspan="2" width="80">残高確認者</th>
<th rowspan="2" width="80">チェック者</th>
</tr>
<tr align="center" bgcolor="#eacaca">
<th width="80">クレジット</th>
<th width="80">値引</th>
<th width="80">金券</th>
<th width="80">他掛</th>
<th width="80">現金</th>
</tr>
</thead>

<tbody>
HTML;
//前月末日
$sql = "select to_char(sale_date,'FMDD'),allsale_intax,change_reserve,pos_cash,onhand_cash,over_short,bank_deposit,next_change_reserve,discount_intax from pos_ztotal_sales where pos_shopno = $pos_shopno and sale_date = '$bf_yymm' and status = '1' order by sale_date";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

for($i=0;$i<$num;$i++){
	list($q_sale_day,$q_allsale_intax,$q_change_reserve,$q_pos_cash,$q_onhand_cash,$q_over_short,$q_bank_deposit,$q_next_change_reserve,$q_discount_intax) = pg_fetch_array($result,$i);
	$arr_allsale_intax[0] = $q_allsale_intax;
	$arr_change_reserve[0] = $q_change_reserve;
	$arr_pos_cash[0] = $q_pos_cash;
	$arr_onhand_cash[0] = $q_onhand_cash;
	$arr_over_short[0] = $q_over_short;
	$arr_bank_deposit[0] = $q_bank_deposit;
	$arr_next_change_reserve[0] = $q_next_change_reserve;
	$arr_discount_intax[0] = $q_discount_intax;
}


$sql = "select to_char(sale_date,'FMDD'),allsale_intax,change_reserve,pos_cash,onhand_cash,over_short,bank_deposit,next_change_reserve,discount_intax from pos_ztotal_sales where pos_shopno = $pos_shopno and sale_date between '$s_yymm' and '$e_yymm' and status = '1' order by sale_date";

//print $sql;
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

for($i=0;$i<$num;$i++){
	list($q_sale_day,$q_allsale_intax,$q_change_reserve,$q_pos_cash,$q_onhand_cash,$q_over_short,$q_bank_deposit,$q_next_change_reserve,$q_discount_intax) = pg_fetch_array($result,$i);
	$arr_allsale_intax[$q_sale_day] = $q_allsale_intax;
	$arr_change_reserve[$q_sale_day] = $q_change_reserve;
	$arr_pos_cash[$q_sale_day] = $q_pos_cash;
	$arr_onhand_cash[$q_sale_day] = $q_onhand_cash;
	$arr_over_short[$q_sale_day] = $q_over_short;
	$arr_bank_deposit[$q_sale_day] = $q_bank_deposit;
	$arr_next_change_reserve[$q_sale_day] = $q_next_change_reserve;
	$arr_discount_intax[$q_sale_day] = $q_discount_intax;
}
///クレジット
$sql = "select to_char(sale_date,'FMDD'),pay_price from pos_ztotal_pay where pos_shopno = $pos_shopno and sale_date between '$s_yymm' and '$e_yymm' and ztotal_no = 116 and status = '1' order by sale_date";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

for($i=0;$i<$num;$i++){
	list($q_sale_day,$q_pay_price) = pg_fetch_array($result,$i);
	$arr_q_pay_credit[$q_sale_day] = $q_pay_price;
}
//現金
$sql = "select to_char(sale_date,'FMDD'),pay_price from pos_ztotal_pay where pos_shopno = $pos_shopno and sale_date between '$s_yymm' and '$e_yymm' and ztotal_no = 112 and status = '1' order by sale_date";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

for($i=0;$i<$num;$i++){
	list($q_sale_day,$q_pay_price) = pg_fetch_array($result,$i);
	$arr_q_pay_money[$q_sale_day] = $q_pay_price;
}
//金券
$sql = "select to_char(sale_date,'FMDD'),pay_price from pos_ztotal_pay where pos_shopno = $pos_shopno and sale_date between '$s_yymm' and '$e_yymm' and ztotal_no = 99 and status = '1' order by sale_date";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

for($i=0;$i<$num;$i++){
	list($q_sale_day,$q_pay_price) = pg_fetch_array($result,$i);
	$arr_q_pay_note[$q_sale_day] = $q_pay_price;
}
//他掛
$sql = "select to_char(sale_date,'FMDD'),sum(pay_price) from pos_ztotal_pay where pos_shopno = $pos_shopno and sale_date between '$s_yymm' and '$e_yymm' and ztotal_no between 113 and 131 and ztotal_no <> 116 and status = '1' group by to_char(sale_date,'FMDD') order by to_char(sale_date,'FMDD')";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

for($i=0;$i<$num;$i++){
	list($q_sale_day,$q_pay_price) = pg_fetch_array($result,$i);
	$arr_q_pay_rece[$q_sale_day] = $q_pay_price;
}

//チェック確認
$sql = "select to_char(sale_date,'FMDD'),manage_money_check_id,checktype,employee_id,to_char(add_date,'YYYY-MM-DD') from manage_money_check where pos_shopno = $pos_shopno and sale_date between '$s_yymm' and '$e_yymm' and status = '1' order by to_char(sale_date,'FMDD'),checktype";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

for($i=0;$i<$num;$i++){
	list($q_sale_day,$q_manage_money_check_id,$q_checktype,$q_check_employee_id,$q_add_date) = pg_fetch_array($result,$i);
	if($q_checktype == 1){
		$arr_check_employee_id1[$q_sale_day] = $q_check_employee_id;
		$arr_check_add_date1[$q_sale_day] = $q_add_date;
	}elseif($q_checktype == 2){
		$arr_check_employee_id2[$q_sale_day] = $q_check_employee_id;
		$arr_check_add_date2[$q_sale_day] = $q_add_date;
	}
}


$all_bank = 0;
$all_sale = 0;
$all_credit = 0;
$all_discount = 0;
$all_note = 0;
$all_rece = 0;
$all_money = 0;
$all_over_short = 0;


for($i=1;$i<=$tt;$i++){
	$k = $i - 1;
//前日
	$form_bf_pos_cash = number_format($arr_onhand_cash[$k]);
	$form_bf_bank_deposit = number_format($arr_bank_deposit[$k]);
//当日
	$form_allsale_intax = number_format($arr_allsale_intax[$i]);
	$form_change_reserve = number_format($arr_change_reserve[$i]);
	$form_pos_cash = number_format($arr_pos_cash[$i]);
	$form_onhand_cash = number_format($arr_onhand_cash[$i]);
	$form_over_short = number_format($arr_over_short[$i]);
	$form_bank_deposit = number_format($arr_bank_deposit[$i]);
	$form_next_change_reserve = number_format($arr_next_change_reserve[$i]);
	$form_discount_intax = number_format($arr_discount_intax[$i]);
	$form_q_pay_credit = number_format($arr_q_pay_credit[$i]);
	$form_q_pay_money = number_format($arr_q_pay_money[$i]);
	$form_q_pay_note = number_format($arr_q_pay_note[$i]);
	$form_q_pay_rece = number_format($arr_q_pay_rece[$i]);
	if(!$form_change_reserve){
		$form_change_reserve = number_format($arr_next_change_reserve[$k]);
	}else{
		
	}

	$logic_pos_cash = $arr_change_reserve[$i] + $arr_q_pay_money[$i];
	$form_logic_pos_cash = number_format($logic_pos_cash);

	$all_bank += $arr_bank_deposit[$k];
	$all_sale += $arr_allsale_intax[$i];
	$all_credit += $arr_q_pay_credit[$i];
	$all_discount += $arr_discount_intax[$i];
	$all_note += $arr_q_pay_note[$i];
	$all_rece += $arr_q_pay_rece[$i];
	$all_money += $arr_q_pay_money[$i];
	$all_over_short += $arr_over_short[$i];
	$upyymmdd = date("Y-m-d",mktime(0,0,0,$mm,$i,$yy));

if($arr_check_employee_id1[$i]){
	$emp_id = $arr_check_employee_id1[$i];
	$checkbutton1 = "<div style=\"font-size:70%;padding:0px;margin:-3px;font-style:italic;\">".$ybase->employee_name_list[$emp_id]."<br>".$arr_check_add_date1[$i]."</div>";
}elseif($ybase->my_employee_id){
	$checkbutton1 = "<a class=\"btn btn-outline-info btn-sm\" delhref=\"./manage_money_update.php?$param&check_type=1&check_emp={$ybase->my_employee_id}&upyymmdd=$upyymmdd\" role=\"button\" style=\"font-size:90%;padding:0px 10px;\" tarday=\"$i\">確認</a>";
}else{
	$checkbutton1 = "";
}
if($arr_check_employee_id2[$i]){
	$emp_id = $arr_check_employee_id2[$i];
	$checkbutton2 = "<div style=\"font-size:70%;padding:0px;margin:-3px;font-style:italic;\">".$ybase->employee_name_list[$emp_id]."<br>".$arr_check_add_date2[$i]."</div>";
}elseif($ybase->my_employee_id && ($ybase->my_position_class <= 70)){
	$checkbutton2 = "<a class=\"btn btn-outline-danger btn-sm\" delhref=\"./manage_money_update.php?$param&check_type=2&check_emp={$ybase->my_employee_id}&upyymmdd=$upyymmdd\" role=\"button\" style=\"font-size:90%;padding:0px 10px;\" tarday=\"$i\">確認</a>";
}else{
	$checkbutton2 = "";
}





$ybase->ST_PRI .= <<<HTML
<tr>
<td align="right">$i</td>
<td align="right">$form_bf_pos_cash</td>
<td align="right">$form_change_reserve</td>
<td align="right">$form_bf_bank_deposit</td>
<td align="right">$form_change_reserve</td>
<td align="right">$form_allsale_intax</td>
<td align="right">$form_q_pay_credit</td>
<td align="right">$form_discount_intax</td>
<td align="right">$form_q_pay_note</td>
<td align="right">$form_q_pay_rece</td>
<td align="right">$form_q_pay_money</td>
<td align="right">$form_pos_cash</td>
<td align="right">$form_over_short</td>
<td align="right">$form_onhand_cash</td>
<td align="center">$checkbutton1</td>
<td align="center">$checkbutton2</td>
</tr>
HTML;

}
	$all_bank = number_format($all_bank);
	$all_sale = number_format($all_sale);
	$all_credit = number_format($all_credit);
	$all_discount = number_format($all_discount);
	$all_note = number_format($all_note);
	$all_rece = number_format($all_rece);
	$all_money = number_format($all_money);
	$all_over_short = number_format($all_over_short);

$ybase->ST_PRI .= <<<HTML
<tr bgcolor="#dddddd">
<td>計</td>
<td align="right"></td>
<td align="right"></td>
<td align="right">$all_bank</td>
<td align="right"></td>
<td align="right">$all_sale</td>
<td align="right">$all_credit</td>
<td align="right">$all_discount</td>
<td align="right">$all_note</td>
<td align="right">$all_rece</td>
<td align="right">$all_money</td>
<td align="right"></td>
<td align="right">$all_over_short</td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
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