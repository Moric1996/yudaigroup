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

$ybase->make_employee_list("1");

/////////////////////////////////////////
if(!$shop_id){
	$shop_id = $ybase->my_section_id;
}
if(!$order_emp_id){
	$order_emp_id = $ybase->my_employee_id;
}
if(!$order_date){
	$order_date = date("Y-m-d");
}
$ybase->title = "新規制作物発注書作成";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("制作物発注書管理");
$ybase->ST_PRI .= <<<HTML
HTML;

$ybase->ST_PRI .= <<<HTML
<div class="container">
<table class="table table-bordered table-sm mx-auto small text-center">
<tbody>
<tr>
<td class="table-active">
新規制作物発注書作成
</td>
<td>
<a href="./order_list.php">制作物発注管理</a>
</td>
</tr>
</tbody>
</table>

<p></p>
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">新規制作物発注書作成</div>
<div class="card-body">
<form action="order_ex.php" method="post" enctype="multipart/form-data">

<datalist id="deliverylist">
HTML;
foreach($delivery_list as $key => $val){
$ybase->ST_PRI .= <<<HTML
<option value="$val">$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</datalist>
<datalist id="productlist">
HTML;
foreach($product_list as $key => $val){
if($key == 99){break;}
$ybase->ST_PRI .= <<<HTML
<option value="$val">$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</datalist>
<datalist id="foodstatuslist">
HTML;
foreach($foodstatus_list as $key => $val){
if($key == 99){break;}
$ybase->ST_PRI .= <<<HTML
<option value="$val">$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</datalist>
<datalist id="sectionlist">
HTML;
foreach($ybase->section_list as $key => $val){
if($shop_id == $key){
	$my_section_name = $val;
}
$ybase->ST_PRI .= <<<HTML
<option value="$val">$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</datalist>

<div class="text-center">
</div>

<div class="form-row ">
<div class="form-group col-6 col-sm-3">
<label>発注日 <span class="badge badge-danger">必須</span></label>
<input type="date" name="order_date" value="$order_date" class="form-control form-control-sm border-dark" id="order_date" required>
</div>
<div class="form-group col-6 col-sm-3">
<label>納品希望日</label>
<input type="date" name="delivery_date" value="$delivery_date" class="form-control form-control-sm border-dark" id="delivery_date">
</div>
<div class="form-group col-6 col-sm-3">
<label>店舗</label>
<select name="shop_id" id="shop_id" class="form-control form-control-sm border-dark">
<option value="">選択してください</option>
HTML;
foreach($ybase->section_list as $key => $val){
if($key == $shop_id){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</div>
<div class="form-group col-6 col-sm-3">
<label>発注者 <span class="badge badge-danger">必須</span></label>
<select name="order_emp_id" id="order_emp_id" class="form-control form-control-sm border-dark">
HTML;
foreach($ybase->employee_name_list as $key => $val){
if($key == $order_emp_id){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</div>
</div>


<label>制作物 <span class="badge badge-danger">必須</span></label>
<div class="form-row ">
<div class="form-group col-12 col-sm-12">
HTML;
foreach($product_list as $key => $val){
if($product_id[$Key] == 1){
	$checked = " checked";
}else{
	$checked = "";
}
$ybase->ST_PRI .= <<<HTML
<div class="form-check  form-check-inline">
<input type="checkbox" name="product_id[$key]" value="1" class="form-check-input" id="product_id$key"{$checked}>
<label class="form-check-label" for="product_id$key">$val</label>
</div>
HTML;
}
$ybase->ST_PRI .= <<<HTML
<nobr>(その他の場合<input type="text" name="product_other" value="{$product_other}" size="20" id="product_other">)</nobr>
</div>
</div>


<div class="table-responsive">
<table class="table table-bordered table-sm text-nowrap">
<thead class="thead-light">
<tr align="center">
<th>　ツール名　</th>
<th>　発注数量　</th>
<th>納品先</th>
<th>サイズ</th>
<th>　備考(色数他)　</th>
</tr>
</thead>

</tbody>
HTML;
for($i=1;$i<7;$i++){

$ybase->ST_PRI .= <<<HTML
<tr>
<td><input type="text" name="tool_name[$i]" value="{$tool_name[$i]}" list="productlist" class="form-control form-control-sm" id="tool_name$i" style="width:100%;"></td>
<td><input type="number" name="quantity[$i]" value="{$quantity[$i]}" id="quantity$i" style="width:80%;">枚</td>
<td>
<input type="text" name="delivery[$i]" value="{$delivery[$i]}" list="deliverylist" class="form-control form-control-sm" id="delivery$i" style="width:100%;">
</td>
<td>
<select name="papersize[$i]" id="papersize$i">
<option value=""></option>
HTML;
foreach($papersize_list as $key => $val){
if($key == $order_emp_id){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</td>
<td>
<input type="text" name="toolbiko[$i]" value="{$toolbiko[$i]}" class="form-control form-control-sm" id="toolbiko$i">
</td>
</tr>
HTML;
}
$ybase->ST_PRI .= <<<HTML

</tbody>
</table>
</div>


<div class="table-responsive">
<table class="table table-bordered table-sm text-nowrap">
<thead class="thead-light">
<tr align="center">
<th>撮影予定商品名</th>
<th>税抜価格</th>
<th>食材の状態</th>
<th>備考欄</th>
</tr>
</thead>

</tbody>
HTML;
for($i=1;$i<9;$i++){
$ybase->ST_PRI .= <<<HTML

<tr>
<td><input type="text" name="photo_name[$i]" value="{$photo_name[$i]}" class="form-control form-control-sm" id="photo_name$i"></td>
<td><input type="number" name="price[$i]" value="{$price[$i]}" class="form-control form-control-sm" id="price$i"></td>
<td><input type="text" name="foodst[$i]" value="{$foodst[$i]}" class="form-control form-control-sm" list="foodstatuslist" id="foodst$i"></td>
<td><input type="text" name="photo_biko[$i]" value="{$photo_biko[$i]}" class="form-control form-control-sm" id="photo_biko$i"></td>
</tr>

HTML;
}

if(!$photo_place){
	$photo_place = $my_section_name;
}
$ybase->ST_PRI .= <<<HTML

</tbody>
</table>

</div>


<div class="form-row ">
<div class="form-group col-6 col-sm-3">
<label>撮影入り可能日</label>
<input type="date" name="photo_date" value="$photo_date" class="form-control form-control-sm border-dark" id="photo_date">
</div>
<div class="form-group col-6 col-sm-3">
<label>撮影場所</label>
<input type="text" name="photo_place" value="$photo_place" class="form-control form-control-sm border-dark" id="photo_place" list="sectionlist">

</div>
<div class="form-group col-6 col-sm-3">
<label>撮影開始時刻</label>
<input type="time" name="photo_start" value="$photo_start" class="form-control form-control-sm border-dark" id="photo_start">
</div>
<div class="form-group col-6 col-sm-3">
<label>終了時刻</label>
<input type="time" name="photo_end" value="$photo_end" class="form-control form-control-sm border-dark" id="photo_end">
</div>

</div>


<div class="form-row ">
<div class="form-group col-12 col-sm-12">
<label>備考</label>
<textarea name="all_biko" class="form-control form-control-sm border-dark" id="all_biko">
$all_biko
</textarea>
</div>
</div>

<div class="form-row ">
<div class="form-group col-6 col-sm-6">
<label>稟議書 <span class="badge badge-danger">必須</span></label>
<select name="approval" id="approval" class="form-control form-control-sm border-dark" required>
<option value="">選択してください</option>
HTML;
foreach($approval_list as $key => $val){
if($key == $approval){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</div>
<div class="form-group col-6 col-sm-6">
<label>請求月 <span class="badge badge-danger">必須</span></label>
<input type="month" name="invoice_date" value="$invoice_date" class="form-control form-control-sm border-dark" id="invoice_date" required>
</div>
</div>

<div class="card border border-dark mx-auto">
<div class="card-body">

<div class="form-row ">
<div class="form-group col-12 col-sm-12">
<label>作成にあたり全て担当マネージャーの承認が必要になります。</label><br>

本件の制作に関しては
<select name="mg_emp_id">
<option value="">選択してください</option>
HTML;
foreach($ybase->employee_name_list as $key => $val){
if($key == $mg_emp_id){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
MGの承認を得ています。
</div>
</div>
</div>
</div>


<div class="form-row">
<div class="form-group col-sm-12">
<label>添付ファイル</label>
<input type="hidden" name="MAX_FILE_SIZE" value="50000000">
<input type="file" name="attachfile[]" multiple="multiple" class="form-control-file form-control-sm" id="attachfile">
</div>
</div>



<p></p>
<br>
<button class="btn btn-secondary col-sm-2 offset-sm-4 border-dark" type="submit">送信</button>
<button class="btn btn-light col-sm-2 border-dark" type="reset">クリア</button>


</form>
</div>
</div>
<p></p>


</div>
<p></p>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>