<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/mpt_list.inc');

$ybase = new ybase();
$ybase->session_get();
/////////////////////////////////////////
$DCOLOR = "#ff9988";
$ybase->make_shop_list();

$ybase->make_employee_list("1");

if(!$shop_id){
	$shop_id = 0;
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

$conn = $ybase->connect();

$sql = "select operator_code,max(employee_name) from pos_zdealer where pos_shopno = $pos_shopno and to_char(sale_date,'YYYY-MM') = '$selyymm' group by operator_code";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
for($i=0;$i<$num;$i++){
	list($q_operator_code,$q_employee_name) = pg_fetch_array($result,$i);
	$q_employee_name = trim($q_employee_name);
	$operator_code_list[$q_operator_code] = $q_employee_name;
}

$sql = "select order_emp_code,max(employee_name) from pos_zfuse where pos_shopno = $pos_shopno and to_char(sale_date,'YYYY-MM') = '$selyymm' group by order_emp_code";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
for($i=0;$i<$num;$i++){
	list($q_order_emp_code,$q_employee_name) = pg_fetch_array($result,$i);
	$q_employee_name = trim($q_employee_name);
	$order_emp_code_list[$q_order_emp_code] = $q_employee_name;
}


$ybase->title = "ポイントカード管理-POS履歴突合せ";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);

$pm = "shop_id=$shop_id&selyymm=$selyymm&selyymmdd=$selyymmdd";

$ybase->ST_PRI .= <<<HTML
<style type="text/css">
a.member_t{color:red;}
</style>
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
      <a href="./mpoint_ttotal_pt.php?$pm" class="nav-link active" data-toggle="tab">POS履歴突合せ</a>
    </li>
    <li class="nav-item">
      <a href="./mpoint_pt_view.php?$pm" class="nav-link" data-toggle="tab">ポイントログ確認</a>
    </li>
    <li class="nav-item">
      <a href="mpoint_ttotal_view.php?$pm" class="nav-link" data-toggle="tab">POSログ確認</a>
    </li>
  </ul>
</div>


<div class="container-fluid">

<p></p>

<div style="font-size:80%;margin:5px;">

<form action="mpoint_ttotal_pt.php" method="post" id="form1">
<select name="shop_id" id="inshop_id">
<option value="">選択してください</option>
HTML;
foreach($ybase->mpt_shop_list as $key => $val){
	if("$shop_id" == "$key"){
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



$ybase->ST_PRI .= <<<HTML

</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th rowspan="2">日付</th>
<th colspan="11" align="center">POS データ</th>
<th>　</th>
<th colspan="7" align="center">POINT CARD データ</th>
<th rowspan="2">CheckCode</th>
<th rowspan="2">該当ttotal_id</th>
<th rowspan="2">該当ttotal_id2</th>
</tr>
<tr align="center" bgcolor="#eacaca">
<th>ID</th>
<th>レシートNO</th>
<th>扱者NO</th>
<th>担当者NO</th>
<th>会計種別</th>
<th>伝票一連NO</th>
<th>会計日時</th>
<th>客数</th>
<th>税込売上1計</th>
<th>税込支払計</th>
<th>割引税込</th>
<th>　</th>
<th>利用日時</th>
<th>会員番号</th>
<th>売上金額</th>
<th>売上PT</th>
<th>累計PT</th>
<th>利用回数</th>
<th>前回利用日</th>
</tr>

</thead>

<tbody>
HTML;
$g_sale=0;
$g_score=0;
$g_custom=0;
if($monthon){
	$date_sql0 = "to_char(record_time,'YYYY-MM') = '$selyymm'";
	$date_sql = "to_char(b.record_time,'YYYY-MM') = '$selyymm'";
	$sdate = substr($selyymm,0,4).substr($selyymm,5,2);
	$g_width = 2000;
}else{
	$date_sql0 = "to_char(record_time,'YYYY-MM-DD') = '$selyymmdd'";
	$date_sql = "to_char(b.record_time,'YYYY-MM-DD') = '$selyymmdd'";
	$sdate = substr($selyymmdd,0,4).substr($selyymmdd,5,2).substr($selyymmdd,8,2);
	$g_width = 1000;
}


$sql = "select ptlog_id,to_char(record_time,'YYYY/MM/DD HH24:MI'),member_no,sales,sale_point,total_point,use_count,last_use_date,check_code,pos_ttotal_id,other_pos_ttotal_id from magnet_ptcard_log where shop_id = $shop_id and {$date_sql0} and sales > 0 and pos_ttotal_id is null order by record_time";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$r_times_arr = array();
for($i=0;$i<$num;$i++){
	list($ptlog_id_arr[$i],$record_time_arr[$i],$member_no_arr[$i],$sales_arr[$i],$sale_point_arr[$i],$total_point_arr[$i],$use_count_arr[$i],$last_use_date_arr[$i],$check_code_arr[$i],$pos_ttotal_id_arr[$i],$other_pos_ttotal_id_arr[$i]) = pg_fetch_array($result,$i);
	$qyy= substr($record_time_arr[$i],0,4);
	$qmm= substr($record_time_arr[$i],5,2);
	$qdd= substr($record_time_arr[$i],8,2);
	$qhh= substr($record_time_arr[$i],11,2);
	$qmi= substr($record_time_arr[$i],14,2);
	$r_times_arr[$i] = mktime($qhh,$qmi,0,$qmm,$qdd,$qyy);
}

$sql = "select a.sale_date,a.pos_ttotal_id,a.receipt_no,a.handler_no,a.charger_no,a.accounting_type,a.slip_series_no,a.pay_time,a.sale_custom,a.totalsale2_intax_price,a.total_pay2_intax_price,a.discount_price,b.ptlog_id,to_char(b.record_time,'YYYY/MM/DD HH24:MI'),b.member_no,b.sales,b.sale_point,b.total_point,b.use_count,b.last_use_date,b.check_code,b.pos_ttotal_id,b.other_pos_ttotal_id from pos_ttotal as a LEFT JOIN magnet_ptcard_log as b ON a.pos_ttotal_id = b.pos_ttotal_id where a.pos_shopno = $pos_shopno and a.pay_time~'^$sdate' and a.transaction_type = 0 and a.status = '1' order by a.pay_time";



//$sql = "select a.sale_date,a.pos_ttotal_id,a.receipt_no,a.handler_no,a.charger_no,a.accounting_type,a.slip_series_no,a.pay_time,a.sale_custom,a.totalsale2_intax_price,a.total_pay2_intax_price,a.discount_price,b.ptlog_id,to_char(b.record_time,'YYYY/MM/DD HH24:MI'),b.member_no,b.sales,b.sale_point,b.total_point,b.use_count,b.last_use_date,b.check_code,b.pos_ttotal_id,b.other_pos_ttotal_id from pos_ttotal as a  FULL JOIN magnet_ptcard_log as b ON a.pos_ttotal_id = b.pos_ttotal_id where (a.pos_shopno = $pos_shopno or a.pos_shopno is null ) and (a.pay_time~'^$sdate' or a.pay_time is null) and (a.transaction_type = 0 or a.transaction_type is null) and (a.status = '1' or a.status is null) and (b.shop_id = $shop_id or b.shop_id is null) and ({$date_sql} or b.record_time is null) and (b.sales > 0 or b.sales is null) order by a.pay_time,b.record_time";
//print $sql;
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
//print "$sql";
$graphtxt = "";
$g_price = 0;
$g_score = 0;
$kk=0;
for($i=0;$i<$num;$i++){
	list($q_sale_date,$q_pos_ttotal_id,$q_receipt_no,$q_handler_no,$q_charger_no,$q_accounting_type,$q_slip_series_no,$q_pay_time,$q_sale_custom,$q_totalsale2_intax_price,$q_total_pay2_intax_price,$q_discount_price,$q_ptlog_id,$q_record_time,$q_member_no,$q_sales,$q_sale_point,$q_total_point,$q_use_count,$q_last_use_date,$q_check_code,$q_pos_ttotal_id2,$q_other_pos_ttotal_id) = pg_fetch_array($result,$i);
	$d_totalsale2_intax_price = number_format($q_totalsale2_intax_price);
	$d_total_pay2_intax_price = number_format($q_total_pay2_intax_price);
	$d_totalsale1_notax_price = number_format($q_totalsale1_notax_price);
	$d_discount_price = number_format($q_discount_price);

	$qyy= substr($q_pay_time,0,4);
	$qmm= substr($q_pay_time,4,2);
	$qdd= substr($q_pay_time,6,2);
	$qhh= substr($q_pay_time,8,2);
	$qmi= substr($q_pay_time,10,2);
	$qss= substr($q_pay_time,12,2);

	if($q_pay_time){
		$d_pay_time = "$qyy"."/"."$qmm"."/"."$qdd"." "."$qhh".":"."$qmi".":"."$qss";
	}else{
		$d_pay_time = "";
	}
	$paytimestamp = mktime($qhh,$qmi,$qss,$qmm,$qdd,$qyy);
	$q_other_pos_ttotal_id = str_replace("{","",$q_other_pos_ttotal_id);
	$q_other_pos_ttotal_id = str_replace("}","",$q_other_pos_ttotal_id);
	$q_other_pos_ttotal_id_arr = explode(",",$q_other_pos_ttotal_id);

	$d_sales = number_format($q_sales);
	$d_sale_point = number_format($q_sale_point);
	$d_total_point = number_format($q_total_point);
	$pos_ids = "";
	foreach($q_other_pos_ttotal_id_arr as $key => $val){
		if($pos_ids){
			$pos_ids .= ",";
		}
		$pos_ids .= $val;
	}
	if(!$d_sales){$d_sales="";}
	if(!$d_sale_point){$d_sale_point="";}
	if(!$d_total_point){$d_total_point="";}

	switch($q_check_code){
		case 1:
			$bgcolor = " bgcolor=\"#ccccdd\"";
			break;
		case 2:
			$bgcolor = " bgcolor=\"#ccdddd\"";
			break;
		case 3:
			$bgcolor = " bgcolor=\"#ddccdd\"";
			break;
		case 4:
			$bgcolor = " bgcolor=\"#ddcccc\"";
			break;
		case 5:
			$bgcolor = " bgcolor=\"#ddddcc\"";
			break;
		case 6:
			$bgcolor = " bgcolor=\"#c1cfdd\"";
			break;

		default:
			$bgcolor = "";
			break;
	}


$ybase->ST_PRI .= <<<HTML
<tr{$bgcolor}>
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
if($t_member_no == $q_member_no){
	$member_style=" class=\"member_t\"";
}else{
	$member_style="";
}
$ybase->ST_PRI .= <<<HTML
<td align="right">$q_pos_ttotal_id</td>
<td align="right">$q_receipt_no</td>
<td align="right">$q_handler_no({$operator_code_list[$q_handler_no]})</td>
<td align="right">$q_charger_no({$order_emp_code_list[$q_charger_no]})</td>
<td align="right">$q_accounting_type</td>
<td align="right">$q_slip_series_no</td>
<td align="right">$d_pay_time</td>
<td align="right">$q_sale_custom</td>
<td align="right">$d_totalsale2_intax_price</td>
<td align="right"><b>$d_total_pay2_intax_price</b></td>
<td align="right">$d_discount_price</td>
<td align="right"></td>
<td align="center">$q_record_time</td>
<td align="center"><a href="./mpoint_pt_view.php?t_member_no=$q_member_no" target="_blank"{$member_style}>$q_member_no</a></td>
<td align="right"><b>$d_sales</b></td>
<td align="right">$d_sale_point</td>
<td align="right">$d_total_point</td>
<td align="right">$q_use_count</td>
<td align="right">$q_last_use_date</td>
<td align="right">{$check_code_list[$q_check_code]}</td>
<td align="right">$q_pos_ttotal_id2</td>
<td align="right">$pos_ids</td>
</tr>

HTML;
if(($r_times_arr[$kk] >= $paytimestamp) || (($i == 0) && ($r_times_arr[$kk] < $paytimestamp))){

	$nxi = $i + 1;
	if($nxi < $num){
		$next_q_pay_time = pg_fetch_result($result,$nxi,7);
		$qyy= substr($next_q_pay_time,0,4);
		$qmm= substr($next_q_pay_time,4,2);
		$qdd= substr($next_q_pay_time,6,2);
		$qhh= substr($next_q_pay_time,8,2);
		$qmi= substr($next_q_pay_time,10,2);
		$qss= substr($next_q_pay_time,12,2);
		$next_paytimestamp = mktime($qhh,$qmi,$qss,$qmm,$qdd,$qyy);

	}else{
		$next_paytimestamp = 0;
	}
	foreach($r_times_arr as $key0 => $val0){
		if($key0 < $kk){
			continue;
		}
	if($r_times_arr[$kk] >= $next_paytimestamp){
			continue;
	}
	$pos_ids2 = "";
	$other_pos_ttotal_id_arr[$kk] = str_replace("{","",$other_pos_ttotal_id_arr[$kk]);
	$other_pos_ttotal_id_arr[$kk] = str_replace("}","",$other_pos_ttotal_id_arr[$kk]);
	$other_pos_ttotal_id_arr_arr = explode(",",$other_pos_ttotal_id_arr[$kk]);

	foreach($other_pos_ttotal_id_arr_arr as $key => $val){
		if($pos_ids2){
			$pos_ids2 .= ",";
		}
		$pos_ids2 .= $val;
	}
	$d_sales = number_format($sales_arr[$kk]);
	$d_sale_point = number_format($sale_point_arr[$kk]);
	$d_total_point = number_format($total_point_arr[$kk]);
if($t_member_no == $member_no_arr[$kk]){
	$member_style=" class=\"member_t\"";
}else{
	$member_style="";
}

$ybase->ST_PRI .= <<<HTML
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="center" bgcolor="{$DCOLOR}">$record_time_arr[$kk]</td>
<td align="center" bgcolor="{$DCOLOR}"><a href="./mpoint_pt_view.php?t_member_no={$member_no_arr[$kk]}" target="_blank"{$member_style}>$member_no_arr[$kk]</a></td>
<td align="right" bgcolor="{$DCOLOR}"><b>$d_sales</b></td>
<td align="right" bgcolor="{$DCOLOR}">$d_sale_point</td>
<td align="right" bgcolor="{$DCOLOR}">$d_total_point</td>
<td align="right" bgcolor="{$DCOLOR}">$use_count_arr[$kk]</td>
<td align="right" bgcolor="{$DCOLOR}">$last_use_date_arr[$kk]</td>
<td align="right" bgcolor="{$DCOLOR}">$check_code_arr[$kk]</td>
<td align="right">$pos_ttotal_id_arr[$kk]</td>
<td align="right">$pos_ids2</td>
</tr>

HTML;

$kk++;
	}
}

}

foreach($r_times_arr as $key0 => $val0){
	if($key0 < $kk){
		continue;
	}

	$pos_ids2 = "";
	$other_pos_ttotal_id_arr[$key0] = str_replace("{","",$other_pos_ttotal_id_arr[$key0]);
	$other_pos_ttotal_id_arr[$key0] = str_replace("}","",$other_pos_ttotal_id_arr[$key0]);
	$other_pos_ttotal_id_arr_arr = explode(",",$other_pos_ttotal_id_arr[$key0]);

	foreach($other_pos_ttotal_id_arr_arr as $key => $val){
		if($pos_ids2){
			$pos_ids2 .= ",";
		}
		$pos_ids2 .= $val;
	}
	$d_sales = number_format($sales_arr[$key0]);
	$d_sale_point = number_format($sale_point_arr[$key0]);
	$d_total_point = number_format($total_point_arr[$key0]);
if($t_member_no == $member_no_arr[$key0]){
	$member_style=" class=\"member_t\"";
}else{
	$member_style="";
}

$ybase->ST_PRI .= <<<HTML
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="right"></td>
<td align="center" bgcolor="{$DCOLOR}">$record_time_arr[$key0]</td>
<td align="center" bgcolor="{$DCOLOR}"><a href="./mpoint_pt_view.php?t_member_no={$member_no_arr[$key0]}" target="_blank"{$member_style}>$member_no_arr[$key0]</a></td>
<td align="right" bgcolor="{$DCOLOR}"><b>$d_sales</b></td>
<td align="right" bgcolor="{$DCOLOR}">$d_sale_point</td>
<td align="right" bgcolor="{$DCOLOR}">$d_total_point</td>
<td align="right" bgcolor="{$DCOLOR}">$use_count_arr[$key0]</td>
<td align="right" bgcolor="{$DCOLOR}">$last_use_date_arr[$key0]</td>
<td align="right" bgcolor="{$DCOLOR}">$check_code_arr[$key0]</td>
<td align="right">$pos_ttotal_id_arr[$key0]</td>
<td align="right">$pos_ids2</td>
</tr>

HTML;

}

$ybase->ST_PRI .= <<<HTML
</tbody>
</table>
</div>
</div>



</div>

</div>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>