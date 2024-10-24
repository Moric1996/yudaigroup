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
$monthon = 1;
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
$maxday = date('t',mktime(0,0,0,$mm,$dd,$yy));
}

$conn = $ybase->connect();
/*
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
*/


$sql = "select pos_shopno,to_char(sale_date,'FMDD'),allsale_intax,dissale_intax from pos_ztotal_sales where pos_shopno = $pos_shopno and to_char(sale_date,'YYYY-MM') = '$selyymm' order by sale_date";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
for($i=0;$i<$num;$i++){
	list($q_pos_shopno,$q_sale_date_DD,$q_allsale_intax,$q_dissale_intax) = pg_fetch_array($result,$i);
	$allsale_intax_list[$q_sale_date_DD] = $q_allsale_intax;
	$dissale_intax_list[$q_sale_date_DD] = $q_dissale_intax;
}
$sql = "select to_char(record_time,'FMDD'),sum(sales),sum(sale_point),sum(w_point),sum(count_point),sum(special_point),sum(free_point),sum(total_point),sum(exchange_point) from magnet_ptcard_log where shop_id = $shop_id and to_char(record_time,'YYYY-MM') = '$selyymm' group by to_char(record_time,'FMDD') order by to_char(record_time,'FMDD')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
//print "$sql<br>";
for($i=0;$i<$num;$i++){
	list($q_record_time_DD,$q_sales,$q_sale_point,$q_w_point,$q_count_point,$q_special_point,$q_free_point,$q_total_point,$q_exchange_point) = pg_fetch_array($result,$i);
	$sales_list[$q_record_time_DD] = $q_sales;
	$sale_point_list[$q_record_time_DD] = $q_sale_point;
	$w_point_list[$q_record_time_DD] = $q_w_point;
	$count_point_list[$q_record_time_DD] = $q_count_point;
	$special_point_list[$q_record_time_DD] = $q_special_point;
	$free_point_list[$q_record_time_DD] = $q_free_point;
	$exchange_point_list[$q_record_time_DD] = $q_exchange_point;
}

$ybase->title = "ポイントカード管理-店舗別ポイント集計";

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
      <a href="./mpoint_sum.php?$pm" class="nav-link active" data-toggle="tab">店舗別ポイント集計</a>
    </li>
    <li class="nav-item">
      <a href="./mpoint_ttotal_pt.php?$pm" class="nav-link" data-toggle="tab">POS履歴突合せ</a>
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

<form action="mpoint_sum.php" method="post" id="form1">
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
<!---<input type="checkbox" name="monthon" value="1" id="monthon"{$monthon_checked}>月表示　----->
HTML;



$ybase->ST_PRI .= <<<HTML

</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th rowspan="2">日付</th>
<th colspan="2">POS データ</th>
<th>　</th>
<th colspan="7">POINT CARD データ</th>
</tr>
<tr align="center" bgcolor="#eacaca">
<th>税込売上</th>
<th>税込売上<br>(割引後)</th>
<th>　</th>
<th>売上金額</th>
<th>売上PT</th>
<th>倍PT</th>
<th>回数PT</th>
<th>特別PT</th>
<th>新規・任意PT</th>
<th>交換PT</th>
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


$allsale_sum = 0;
$dissale_sum = 0;
$sales_sum = 0;
$sale_point_sum = 0;
$w_point_sum = 0;
$count_point_sum = 0;
$special_point_sum = 0;
$free_point_sum = 0;
$exchange_point_sum = 0;

for($i=1;$i<=$maxday;$i++){
	$allsale_sum += $allsale_intax_list[$i];
	$dissale_sum += $dissale_intax_list[$i];
	$sales_sum += $sales_list[$i];
	$sale_point_sum += $sale_point_list[$i];
	$w_point_sum += $w_point_list[$i];
	$count_point_sum += $count_point_list[$i];
	$special_point_sum += $special_point_list[$i];
	$free_point_sum += $free_point_list[$i];
	$exchange_point_sum += $exchange_point_list[$i];

	$d_allsale_intax = number_format($allsale_intax_list[$i]);
	$d_dissale_intax = number_format($dissale_intax_list[$i]);
	$d_sales = number_format($sales_list[$i]);
	$d_sale_point = number_format($sale_point_list[$i]);
	$d_w_point = number_format($w_point_list[$i]);
	$d_count_point = number_format($count_point_list[$i]);
	$d_special_point = number_format($special_point_list[$i]);
	$d_free_point = number_format($free_point_list[$i]);
	$d_exchange_point = number_format($exchange_point_list[$i]);
if($dissale_intax_list[$i] < $sales_list[$i]){
	$notice_style = " style=\"color:red;\"";
}else{
	$notice_style = "";
}
$ybase->ST_PRI .= <<<HTML
<tr{$bgcolor}>
<td align="center">{$i}日</td>
<td align="right">$d_allsale_intax</td>
<td align="right">$d_dissale_intax</td>
<td align="right"></td>
<td align="right"{$notice_style}>$d_sales</td>
<td align="right">$d_sale_point</td>
<td align="right">$d_w_point</td>
<td align="right">$d_count_point</td>
<td align="right">$d_special_point</td>
<td align="right">$d_free_point</td>
<td align="right">$d_exchange_point</td>
</tr>

HTML;

}

$allsale_sum = number_format($allsale_sum);
$dissale_sum = number_format($dissale_sum);
$sales_sum = number_format($sales_sum);
$sale_point_sum = number_format($sale_point_sum);
$w_point_sum = number_format($w_point_sum);
$count_point_sum = number_format($count_point_sum);
$special_point_sum = number_format($special_point_sum);
$free_point_sum = number_format($free_point_sum);
$exchange_point_sum = number_format($exchange_point_sum);

$ybase->ST_PRI .= <<<HTML
<td align="right">計</td>
<td align="right">$allsale_sum</td>
<td align="right">$dissale_sum</td>
<td align="right"></td>
<td align="right">$sales_sum</td>
<td align="right">$sale_point_sum</td>
<td align="right">$w_point_sum</td>
<td align="right">$count_point_sum</td>
<td align="right">$special_point_sum</td>
<td align="right">$free_point_sum</td>
<td align="right">$exchange_point_sum</td>
</tr>

HTML;


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