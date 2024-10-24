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

$ybase->make_employee_list("1");
if($shop_id == "foodall"){
	$shop_id = 0;
	$foodall = 1;
}else{
	$foodall = "";
}

if($monthon){
if($selyymm){
	$yy = substr($selyymm,0,4);
	$mm = substr($selyymm,5,2);
}elseif($selyymmdd){
	$yy = substr($selyymmdd,0,4);
	$mm = substr($selyymmdd,5,2);
}
if((!$yy || !$mm) && !$t_member_no){
	$now_yy = date('Y');
	$now_mm = date('m');
	$now_dd = date('d');
	$yy = date('Y',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$mm = date('m',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$dd = date('d',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
}
if($yy && $mm){
	$maxday = date('t',mktime(0,0,0,$mm,1,$yy));
	$selyymm = "$yy-$mm";
}
}else{
if($selyymmdd){
	$yy = substr($selyymmdd,0,4);
	$mm = substr($selyymmdd,5,2);
	$dd = substr($selyymmdd,8,2);
}
if((!$yy || !$mm || !$dd) && !$t_member_no){
	$now_yy = date('Y');
	$now_mm = date('m');
	$now_dd = date('d');
	$yy = date('Y',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$mm = date('m',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$dd = date('d',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
}
if($yy && $mm && $dd){
	$selyymmdd = "$yy-$mm-$dd";
	$selyymm = "$yy-$mm";
}
}


$conn = $ybase->connect();

$ybase->title = "ポイントカード-ポイントログ確認";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);

$pm = "shop_id=$shop_id&selyymm=$selyymm&selyymmdd=$selyymmdd";

$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#inshop_id,#inmonth,#ttype,#monthon,#free_class_id,#t_member_no').change(function(){
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
      <a href="./mpoint_ttotal_pt.php?$pm" class="nav-link" data-toggle="tab">POS履歴突合せ</a>
    </li>
    <li class="nav-item">
      <a href="./mpoint_pt_view.php?$pm" class="nav-link active" data-toggle="tab">ポイントログ確認</a>
    </li>
    <li class="nav-item">
      <a href="mpoint_ttotal_view.php?$pm" class="nav-link" data-toggle="tab">POSログ確認</a>
    </li>
  </ul>
</div>

<div class="container-fluid">

<p></p>

<div style="font-size:80%;margin:5px;">

<form action="mpoint_pt_view.php" method="post" id="form1">
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
　会員番号<input type="text" name="t_member_no" value="$t_member_no" id="t_member_no">

</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th>利用日時</th>
<th>会員番号</th>
<th>店舗</th>
<th>売上金額</th>
<th>売上PT</th>
<th>倍PT</th>
<th>回数PT</th>
<th>特別PT</th>
<th>新規・任意PT</th>
<th>累計PT</th>
<th>交換PT</th>
<th>利用回数</th>
<th>前回利用日</th>
<th>CheckCode</th>
<th>該当ttotal_id</th>
<th>該当ttotal_id2</th>
</tr>

</thead>

<tbody>
HTML;
$g_sale=0;
$g_score=0;
$g_custom=0;
$w_flag = 0;
$add_sql = "";
if($shop_id){
	if($w_flag){
		$add_sql .= " and";
	}else{
		$add_sql .= " where";
		$w_flag = 1;
	}
	$add_sql .= " shop_id = $shop_id";
}elseif($foodall){
	if($w_flag){
		$add_sql .= " and";
	}else{
		$add_sql .= " where";
		$w_flag = 1;
	}
	$add_sql .= " ((shop_id between 1 and 299) or (shop_id between 400 and 999))";
}

if($monthon){
	if($selyymm){
		if($w_flag){
			$add_sql .= " and";
		}else{
			$add_sql .= " where";
			$w_flag = 1;
		}
		$add_sql .= " to_char(record_time,'YYYY-MM-DD') between '{$selyymm}-01' and '{$selyymm}-{$maxday}'";
	}
	$g_width = 2000;
}else{
	if($selyymmdd){
		if($w_flag){
			$add_sql .= " and";
		}else{
			$add_sql .= " where";
			$w_flag = 1;
		}
		$add_sql .= " to_char(record_time,'YYYY-MM-DD') = '$selyymmdd'";
	}
	$g_width = 1000;
}
if($t_member_no){
	if($w_flag){
		$add_sql .= " and";
	}else{
		$add_sql .= " where";
		$w_flag = 1;
	}
	$add_sql .= " member_no = '$t_member_no'";
}


$sql = "select ptlog_id,shop_id,devicecode,to_char(record_time,'YYYY/MM/DD HH24:MI'),member_no,sales,sale_point,w_point,count_point,special_point,free_point,total_point,exchange_point,use_count,last_use_date,add_date,check_code,pos_ttotal_id,other_pos_ttotal_id from magnet_ptcard_log {$add_sql} order by record_time";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$graphtxt = "";
$g_price = 0;
$g_score = 0;
$other_pos_ttotal_id_arr = array();
for($i=0;$i<$num;$i++){
	list($q_ptlog_id,$q_shop_id,$q_devicecode,$q_record_time,$q_member_no,$q_sales,$q_sale_point,$q_w_point,$q_count_point,$q_special_point,$q_free_point,$q_total_point,$q_exchange_point,$q_use_count,$q_last_use_date,$q_add_date,$q_check_code,$q_pos_ttotal_id,$q_other_pos_ttotal_id) = pg_fetch_array($result,$i);
	$q_other_pos_ttotal_id = str_replace("{","",$q_other_pos_ttotal_id);
	$q_other_pos_ttotal_id = str_replace("}","",$q_other_pos_ttotal_id);
	$other_pos_ttotal_id_arr = explode(",",$q_other_pos_ttotal_id);

	$d_sales = number_format($q_sales);
	$d_sale_point = number_format($q_sale_point);
	$d_w_point = number_format($q_w_point);
	$d_count_point = number_format($q_count_point);
	$d_special_point = number_format($q_special_point);
	$d_free_point = number_format($q_free_point);
	$d_total_point = number_format($q_total_point);
	$d_exchange_point = number_format($q_exchange_point);
	$pos_ids = "";
	foreach($other_pos_ttotal_id_arr as $key => $val){
		if($pos_ids){
			$pos_ids .= ",";
		}
		$pos_ids .= $val;
	}
if($t_member_no){
	$t_ymd = substr($q_record_time,0,4)."-".substr($q_record_time,5,2)."-".substr($q_record_time,8,2);
	$record_t_link = "<a href=\"./mpoint_ttotal_pt.php?shop_id=$q_shop_id&selyymm=$selyymm&selyymmdd=$t_ymd&t_member_no=$q_member_no\">$q_record_time</a>";
}else{
	$record_t_link = "$q_record_time";

}
$ybase->ST_PRI .= <<<HTML
<tr>
<td align="center">$record_t_link</td>
<td align="center"><a href="./mpoint_pt_view.php?shop_id=$shop_id&monthon=$monthon&selyymm=$selyymm&selyymmdd=$selyymmdd&t_member_no=$q_member_no">$q_member_no</a></td>
<td align="center">{$ybase->mpt_shop_list[$q_shop_id]}</td>
<td align="right">$d_sales</td>
<td align="right">$d_sale_point</td>
<td align="right">$d_w_point</td>
<td align="right">$d_count_point</td>
<td align="right">$d_special_point</td>
<td align="right">$d_free_point</td>
<td align="right">$d_total_point</td>
<td align="right">$d_exchange_point</td>
<td align="right">$q_use_count</td>
<td align="right">$q_last_use_date</td>
<td align="right">$q_check_code</td>
<td align="right">$q_pos_ttotal_id</td>
<td align="right">$pos_ids</td>


</tr>

HTML;

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