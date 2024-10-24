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
if($shop_id == "foodall"){
	$shop_id = 0;
	$foodall = 1;
}else{
	$foodall = "";
}
if(!$shop_id){
	$shop_id = 0;
}
$pos_shopno = $ybase->section_to_pos[$shop_id];
if(!$pos_shopno){
	$pos_shopno = 0;
}
if(!$person_how){
	$person_how = 1;
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

$sql = "select member_no,count(*),sum(sales),sum(sale_point),sum(w_point),sum(count_point),sum(special_point),sum(free_point),sum(exchange_point),max(last_use_date) from magnet_ptcard_log where to_char(record_time,'YYYY-MM') = '$selyymm'";

if($shop_id){
	$sql .= " and shop_id = $shop_id";
}elseif($foodall){
	$sql .= " and ((shop_id between 1 and 299) or (shop_id between 400 and 999))";
}
$d_color1 = "";
$d_color2 = "";
$d_color3 = "";
$d_color4 = "";
$d_color5 = "";
$base_style=" style=\"color:red\"";
switch($person_how){
	case 1:
		$sql .= " and sales > 0 group by member_no having count(*) > 2 order by count(*) desc";
		$d_color1=$base_style;
		break;
	case 2:
		$sql .= " and sales > 0 group by member_no having sum(sales) > 49999 order by sum(sales) desc";
		$d_color2=$base_style;
		break;
	case 3:
		$sql .= " group by member_no having sum(special_point) > 0 order by sum(special_point) desc";
		$d_color3=$base_style;
		break;
	case 4:
		$sql .= " group by member_no having sum(free_point) > 0 order by sum(free_point) desc";
		$d_color4=$base_style;
		break;
	case 5:
		$sql .= " group by member_no having sum(exchange_point) > 0 order by sum(exchange_point) desc";
		$d_color5=$base_style;
		break;

}

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
//print "$sql<br>";

$ybase->title = "ポイントカード管理-個人別集計";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);

$pm = "shop_id=$shop_id&selyymm=$selyymm&selyymmdd=$selyymmdd";
$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#inshop_id,#inmonth,#ttype,#monthon,#person_how').change(function(){
		$("#form1").submit();
	});
});

</script>

<div class="container">
  <ul class="nav nav-tabs nav-fill" id="myTab" style="font-size:70%;">
    <li class="nav-item">
      <a href="./mpoint_person_cnt.php?$pm" class="nav-link active" data-toggle="tab">個人別集計</a>
    </li>
    <li class="nav-item">
      <a href="./mpoint_sum.php?$pm" class="nav-link" data-toggle="tab">店舗別ポイント集計</a>
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

<form action="mpoint_person_cnt.php" method="post" id="form1">
<select name="shop_id" id="inshop_id">
<option value="">全店</option>
HTML;
if($foodall){
	$foodallselect = " selected";
}else{
	$foodallselect = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="foodall"{$foodallselect}>飲食店のみ全店</option>
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
　<select name="person_how" id="person_how">
HTML;
foreach($person_how_list as $key => $val){
	if("$person_how" == "$key"){
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
HTML;



$ybase->ST_PRI .= <<<HTML

</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th>会員番号</th>
<th>回数</th>
<th>売上金額計</th>
<th>売上PT計</th>
<th>倍PT計</th>
<th>回数PT計</th>
<th>特別PT計</th>
<th>新規・任意PT計</th>
<th>交換PT計</th>
<th>最終利用日</th>
</tr>

</thead>

<tbody>
HTML;



for($i=0;$i<$num;$i++){
	list($q_member_no,$q_cnt,$q_sales,$q_sale_point,$q_w_point,$q_count_point,$q_special_point,$q_free_point,$q_exchange_point,$q_last_date) = pg_fetch_array($result,$i);
	$d_cnt = number_format($q_cnt);
	$d_sales = number_format($q_sales);
	$d_sale_point = number_format($q_sale_point);
	$d_w_point = number_format($q_w_point);
	$d_count_point = number_format($q_count_point);
	$d_special_point = number_format($q_special_point);
	$d_free_point = number_format($q_free_point);
	$d_exchange_point = number_format($q_exchange_point);
$ybase->ST_PRI .= <<<HTML
<tr{$bgcolor}>
<td align="center"><a href="./mpoint_pt_view.php?shop_id=$shop_id&monthon=1&selyymm=$selyymm&t_member_no=$q_member_no" target="_blank">$q_member_no</td>
<td align="right"{$d_color1}>$d_cnt</td>
<td align="right"{$d_color2}>$d_sales</td>
<td align="right">$d_sale_point</td>
<td align="right">$d_w_point</td>
<td align="right">$d_count_point</td>
<td align="right"{$d_color3}>$d_special_point</td>
<td align="right"{$d_color4}>$d_free_point</td>
<td align="right"{$d_color5}>$d_exchange_point</td>
<td align="center">$q_last_date</td>
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