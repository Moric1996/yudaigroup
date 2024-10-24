<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
if(isset($_POST)){
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
}
if(isset($_GET)){
	foreach($_GET as $key => $value){
		${$key} = $value;
	}
}

include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();
//$edit_f = 1;
/////////////////////////////////////////
if(!$ttype){
	$ttype = 1;
}
$ybase->make_shop_list();
//$ybase->shop_list['3001'] = "雄大ゴルフ熱函";	//雄大ゴルフ熱函
//$ybase->shop_list['3002'] = "雄大ゴルフ清水町";	//雄大ゴルフ清水町

$ybase->make_employee_list("1");
if(!$shop_id){
if(array_key_exists((int)$ybase->my_section_id,$ybase->shop_list)){
	$shop_id = $ybase->my_section_id;
}else{
	$shop_id = 0;
}
}
$d_flag = "";
if($ybase->my_position_class == 40){
	if(!preg_match("/$shop_id/",$ybase->section_group_list[$ybase->my_section_id])){
		$d_flag = 1;
	}
}elseif($ybase->my_position_class > 40){
	if($ybase->my_section_id != $shop_id){
		$d_flag = 1;
	}
}
if($ybase->my_employee_type > 3){
	$d_flag = "1";
}

if($ybase->my_section_id == '003'){
		$d_flag = "";
}
if($ybase->my_section_id == '204'){
	if($shop_id == '206'){
		$d_flag = "";
	}
}

if($d_flag){
	$disbaled = " disabled";
}else{
	$disbaled = "";
}
if($selyymm){
	$yy = substr($selyymm,0,4);
	$mm = substr($selyymm,5,2);
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
$s_date = date('Y-m-d',mktime(0,0,0,$mm,1,$yy));
$e_date = date('Y-m-d',mktime(0,0,0,$mm,$maxday,$yy));

$selyymm = "$yy-$mm";
$conn = $ybase->connect();

$ybase->title = "売上等日次データ入力";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);



$ybase->ST_PRI .= <<<HTML
<script src="./js/sale_update.js?101"></script>
<script>
$(function(){
	$('#in_shop_id,#inmonth').change(function(){
		$("#form1").submit();
	});
});

</script>
<div class="container">

<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="../dailyperfom/index.php?$param" role="button">戻る</a></div>

<div style="font-size:100%;margin:5px;">
<form action="set_sale.php" method="post" id="form1">
<select name="shop_id" id="in_shop_id">
<option value="">選択してください</option>
HTML;
foreach($ybase->shop_list as $key => $val){
	if("$shop_id" == "$key"){
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

$selectdate = "<input type=\"month\" name=\"selyymm\" value=\"$selyymm\" id=\"inmonth\">";
$ybase->ST_PRI .= <<<HTML
</select>　

{$selectdate}
　

</form>

<p></p>

<div class="table-responsive">
※労働時間の分表記は百分率で記入。[例:12時間30分の場合→(12.50)]<br>
<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#caeaca">
<th>日付</th>
<th>予算(税抜)</th>
<th>売上(税抜)</th>
<th>客数</th>
<th>労働時間</th>
<th>割引金額(税抜)</th>
</tr>
</thead>
<tbody>
HTML;

$sql_shop_id = $ybase->scraing_shop_list[$shop_id];
$sql = "select to_char(date,'FMDD'),budget,revenue,customers_num,work_time,discount_ticket from yudai_data_news where shop_id = '$sql_shop_id' and date between '$s_date' and '$e_date' order by shop_id,date";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

for($i=0;$i<$num;$i++){
	list($q_DD,$q_budget,$q_revenue,$q_customers_num,$q_work_time,$q_discount_ticket) = pg_fetch_array($result,$i);
	$a_budget[$q_DD] = $q_budget;
	$a_revenue[$q_DD] = $q_revenue;
	$a_customers_num[$q_DD] = $q_customers_num;
	$a_work_time[$q_DD] = $q_work_time;
	$a_discount_ticket[$q_DD] = $q_discount_ticket;
}
for($i=1;$i<=$maxday;$i++){

$ybase->ST_PRI .= <<<HTML
<tr align="center"><td>$i</td>
<td><input type="text" id="budget_{$i}" name="budget_{$i}" value="{$a_budget[$i]}" colum="budget" day="$i"{$disbaled}></td>
<td><input type="text" id="revenue_{$i}" name="revenue_{$i}" value="{$a_revenue[$i]}" colum="revenue" day="$i"{$disbaled}></td>
<td><input type="text" id="customers_num_{$i}" name="customers_num_{$i}" value="{$a_customers_num[$i]}" colum="customers_num" day="$i"{$disbaled}></td>
<td><input type="text" id="work_time_{$i}" name="work_time_{$i}" value="{$a_work_time[$i]}" colum="work_time" day="$i"{$disbaled}></td>
<td><input type="text" id="discount_ticket_{$i}" name="discount_ticket_{$i}" value="{$a_discount_ticket[$i]}" colum="discount_ticket" day="$i"{$disbaled}></td>
</tr>
HTML;
}


$ybase->ST_PRI .= <<<HTML

</tbody>
</table>

</div>
</div>

</div>

<p></p>
<br>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>