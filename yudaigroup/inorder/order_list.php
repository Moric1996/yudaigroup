<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

include('./inorder_list.inc');

$kensu=50;
$tim=time();

/////////////////////////////////////////

$conn = $ybase->connect();

$ybase->make_employee_list(1);
$now_employee_name_list = $ybase->employee_name_list;
$ybase->make_employee_list();


//////////////////////////////////////////条件

if($sel_order_date_st){
	$add_sql .= " and order_date >= '$sel_order_date_st'";
}
if($sel_order_date_ed){
	$add_sql .= " and order_date <= '$sel_order_date_ed'";
}
if($sel_delivery_date_st){
	$add_sql .= " and delivery_date >= '$sel_delivery_date_st'";
}
if($sel_delivery_date_ed){
	$add_sql .= " and delivery_date <= '$sel_delivery_date_ed'";
}
if($sel_delivery_plan_date_st){
	$add_sql .= " and delivery_plan_date >= '$sel_delivery_plan_date_st'";
}
if($sel_delivery_plan_date_ed){
	$add_sql .= " and delivery_plan_date <= '$sel_delivery_plan_date_ed'";
}
if($sel_photo_date_st){
	$add_sql .= " and photo_date >= '$sel_photo_date_st'";
}
if($sel_photo_date_ed){
	$add_sql .= " and photo_date <= '$sel_photo_date_ed'";
}
if(preg_match("/^[0-9]+$/",$sel_section_id)){
	$add_sql .= " and shop_id = $sel_section_id";
}
if(preg_match("/^[0-9]+$/",$sel_order_emp)){
	$add_sql .= " and order_emp_id = $sel_order_emp";
}
if(preg_match("/^[0-9]+$/",$sel_sales_emp)){
	$add_sql .= " and sales_emp_id = $sel_sales_emp";
}
if(preg_match("/^[0-9]+$/",$sel_status)){
	if($sel_status == 99){
		$add_sql .= " and status ~'$sel_status'";
	}else{
		$add_sql .= " and status = '$sel_status'";
	}
}

$param = "sel_order_date_st=$sel_order_date_st&sel_order_date_ed=$sel_order_date_ed&sel_delivery_date_st=$sel_delivery_date_st&sel_delivery_date_ed=$sel_delivery_date_ed&sel_photo_date_st=$sel_photo_date_st&sel_photo_date_ed=$sel_photo_date_ed&sel_section_id=$sel_section_id&sel_order_emp=$sel_order_emp&sel_status=$sel_status&sel_delivery_plan_date_st=$sel_delivery_plan_date_st&sel_delivery_plan_date_ed=$sel_delivery_plan_date_ed&sel_sales_emp=$sel_sales_emp";
//////////////////////////////////////////

$sql = "select order_id,to_char(order_date,'YYYY/MM/DD'),to_char(delivery_date,'YYYY/MM/DD'),array_to_json(product_id),product_other,shop_id,order_emp_id,to_char(photo_date,'YYYY/MM/DD'),photo_place,to_char(photo_start,'HH24:MI'),to_char(photo_end,'HH24:MI'),all_biko,approval,to_char(invoice_date,'YYYY/MM'),mg_emp_id,array_to_json(attachfile),add_date,status,to_char(delivery_plan_date,'YYYY/MM/DD'),sales_emp_id from order_main where status > '0'{$add_sql} order by order_id desc";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
//	$ybase->error("対象者がおりません");
}

$allpage = ceil($num / $kensu);
if(!$page){
	$page = 1;
}
$st = ($page - 1) * $kensu;
$end = $st + $kensu;
if($end > $num){
	$end = $num;
}


$ybase->title = "制作物発注書管理";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("制作物発注書管理");

$ybase->ST_PRI .= <<<HTML
HTML;


$ybase->ST_PRI .= <<<HTML
<script type="text/javascript">
$(function(){
	$('select,input').change(function(){
		var str = $("#filter_check").prop('checked');
		if(str == true){
			$("#filter_check").val("1");
		}else{
			$("#filter_check").val("0");
		}
		$("#Filter_Form1").submit();
	});
});

</script>
<div class="container">
<table class="table table-bordered table-sm mx-auto small text-center">
<tbody>
<tr>
<td>
<a href="./order_fm.php">新規制作物発注書作成</a>
</td>
<td class="table-active">
制作物発注管理
</td>
</tr>
</tbody>
</table>
<p></p>
{$new_add_emp_html}
<p class="text-center">制作物発注書管理</p>


<div class="card border border-primary w-100 mx-auto">
<div class="text-left small">
《絞込み》

</div>
<div class="text-left small">
<form action="" method="post" id="Filter_Form1">

<div class="form-row ">
<div class="form-group col-8 col-sm-6">
<label>【　発　注　日　】</label>
<nobr><input type="date" name="sel_order_date_st" value="$sel_order_date_st">～<input type="date" name="sel_order_date_ed" value="$sel_order_date_ed"></nobr>
</div>
<div class="form-group col-8 col-sm-6">
<label><nobr>【　店　舗　】</nobr></label>
<select name="sel_section_id">
<option value="">全て</option>
HTML;

foreach($ybase->section_list as $key => $val){
	if($sel_section_id == $key){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$addselect>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</div>
</div>

<div class="form-row ">
<div class="form-group col-8 col-sm-6">
<label><nobr>【　納品希望日　】</nobr></label>

<nobr><input type="date" name="sel_delivery_date_st" value="$sel_delivery_date_st">～<input type="date" name="sel_delivery_date_ed" value="$sel_delivery_date_ed"></nobr>
</div>
<div class="form-group col-8 col-sm-6">
<label><nobr>【　発注者　】</nobr></label>

<select name="sel_order_emp">
<option value="">全て</option>
HTML;

foreach($now_employee_name_list as $key => $val){
	if($sel_order_emp == $key){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$addselect>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</div>
</div>

<div class="form-row ">
<div class="form-group col-8 col-sm-6">
<label><nobr>【　納品予定日　】</nobr></label>

<nobr><input type="date" name="sel_delivery_plan_date_st" value="$sel_delivery_plan_date_st">～<input type="date" name="sel_delivery_plan_date_ed" value="$sel_delivery_plan_date_ed"></nobr>
</div>
<div class="form-group col-8 col-sm-6">
<label><nobr>【　担当者　】</nobr></label>

<select name="sel_sales_emp">
<option value="">全て</option>
HTML;

foreach($now_employee_name_list as $key => $val){
	if(!array_key_exists($key,$order_mg_emp_list)){
		continue;
	}
	if($sel_sales_emp == $key){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$addselect>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</div>
</div>

<div class="form-row ">
<div class="form-group col-8 col-sm-6">
<label><nobr>【撮影入り可能日】</nobr></label>
<nobr><input type="date" name="sel_photo_date_st" value="$sel_photo_date_st">～<input type="date" name="sel_photo_date_ed" value="$sel_photo_date_ed"></nobr>
</div>
<div class="form-group col-8 col-sm-6">
<label>【　状　態　】</label>
<select name="sel_status">
<option value="">全て</option>
HTML;

foreach($inorder_status_list as $key => $val){
	if($key == '0'){continue;}
	if($sel_status === "$key"){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$addselect>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>

</div>
</div>
HTML;

if($filter_check == 1){
	$addcheckf= " checked";
}else{
	$addcheckf= "";
}

$ybase->ST_PRI .= <<<HTML

<!-----<input type="checkbox" id="filter_check" name="filter_check" {$addcheckf}>絞込み条件を保存する------------></td></tr>
</form>

</div>

</div>


<p></p>
<table class="table table-bordered table-hover table-sm" style="font-size:80%;">
  <thead>
    <tr align="center" class="table-primary">
      <th scope="col">NO.</th>
      <th scope="col">発注番号</th>
      <th scope="col">発注日</th>
      <th scope="col">納品希望日</th>
      <th scope="col">納品予定日</th>
      <th scope="col">店舗</th>
      <th scope="col">発注者</th>
      <th scope="col">担当者</th>
      <th scope="col">制作物</th>
      <th scope="col">撮影入り可能日</th>
      <th scope="col">状態</th>
      <th scope="col">確認</th>
    </tr>
  </thead>
  <tbody>
HTML;

for($i=$st;$i<$end;$i++){
	list($q_order_id,$q_order_date,$q_delivery_date,$q_product_id,$q_product_other,$q_shop_id,$q_order_emp_id,$q_photo_date,$q_photo_place,$q_photo_start,$q_photo_end,$q_all_biko,$q_approval,$q_invoice_date,$q_mg_emp_id,$q_attachfile,$q_add_date,$q_status,$q_delivery_plan_date,$q_sales_emp_id) = pg_fetch_array($result,$i);
	$q_product_id = json_decode($q_product_id);
	$q_attachfile = json_decode($q_attachfile);
	$q_shop_id = sprintf("%03d",$q_shop_id);
	$no=$i+1;
if(preg_match("/^99([0-9]{2})$/",$q_status,$str)){
	$q_status = 99;
	$past_st = $str[1];
}

$ybase->ST_PRI .= <<<HTML

<tr align="center">
<td>
$no
</td>
<td>
$q_order_id
</td>
<td>
$q_order_date
</td>
<td>
$q_delivery_date
</td>
<td>
$q_delivery_plan_date
</td>
<td>
{$ybase->section_list[$q_shop_id]}
</td>
<td>
{$ybase->employee_name_list[$q_order_emp_id]}
</td>
<td>
{$ybase->employee_name_list[$q_sales_emp_id]}
</td>
<td>
HTML;
foreach($q_product_id as $key => $val){
$ybase->ST_PRI .= <<<HTML
$product_list[$val] 
HTML;
}
$ybase->ST_PRI .= <<<HTML
</td>
<td>
$q_photo_date
</td>
<td>
{$inorder_status_list2[$q_status]}
</td>
<td>
<a class="btn btn-outline-info btn-sm" href="./order_vw.php?t_order_id=$q_order_id&page=$page&$param" role="button">確認</a>
</td>
</tr>

HTML;


}



$bf=$page-1;
$nx=$page+1;
if($bf < 1){
	$addbfclass=" disabled";
}else{
	$addbfclass="";
}
if($nx > $allpage){
	$addnxclass=" disabled";
}else{
	$addnxclass="";
}

$ybase->ST_PRI .= <<<HTML

 </tbody>
</table>
<div class="row">
<div class="col float-right">
<span class="float-right">
{$page}／{$allpage}
<a href="order_list.php?$param&page=$bf" class="btn btn-sm btn-outline-secondary{$addbfclass}">＜</a>
<a href="order_list.php?$param&page=$nx" class="btn btn-sm btn-outline-secondary{$addnxclass}">＞</a>
</span>
</div>
</div>
</div>
<p></p>


HTML;

$k = 10 - $num;

if($k > 0){
for($i=0;$i<$k;$i++){
$ybase->ST_PRI .= "<br><br>";

}

}
$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>