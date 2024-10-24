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

$conn = $ybase->connect();

$ybase->make_employee_list("");
$param = "t_order_id=$t_order_id&page=$page&sel_order_date_st=$sel_order_date_st&sel_order_date_ed=$sel_order_date_ed&sel_delivery_date_st=$sel_delivery_date_st&sel_delivery_date_ed=$sel_delivery_date_ed&sel_photo_date_st=$sel_photo_date_st&sel_photo_date_ed=$sel_photo_date_ed&sel_section_id=$sel_section_id&sel_order_emp=$sel_order_emp&sel_status=$sel_status";

//編集モード

if($edit_md == "1"){
	$disable = "";
	$require = " <span class=\"badge badge-danger\">必須</span>";
	$editbtn = "<a class=\"btn btn-info btn-sm\" href=\"./order_vw.php?$param&edit_md=\" role=\"button\">閲覧モード</a>";
}else{
	$disable = " disabled";
	$require = "";
	$editbtn = "<a class=\"btn btn-info btn-sm\" href=\"./order_vw.php?$param&edit_md=1\" role=\"button\">編集モード</a>";
}

if($ybase->my_position_class && ($ybase->my_position_class < 41)){
	$del_able = "";
}else{
	$del_able = " disabled";
}

/////////////////////////////////////////
if(!preg_match("/^[0-9]+$/",$t_order_id)){
	$ybase->error("パラメーターエラー");
}

$sql = "select to_char(order_date,'YYYY-MM-DD'),to_char(delivery_date,'YYYY-MM-DD'),array_to_json(product_id),product_other,shop_id,order_emp_id,to_char(photo_date,'YYYY-MM-DD'),photo_place,to_char(photo_start,'HH24:MI'),to_char(photo_end,'HH24:MI'),all_biko,approval,to_char(invoice_date,'YYYY-MM'),mg_emp_id,array_to_json(attachfile),add_date,status,delivery_plan_date,sales_emp_id from order_main where order_id = $t_order_id and status > '0'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	$ybase->error("データがありません");
}
	list($order_date,$delivery_date,$product_id,$product_other,$shop_id,$order_emp_id,$photo_date,$photo_place,$photo_start,$photo_end,$all_biko,$approval,$invoice_date,$mg_emp_id,$attachfile,$add_date,$status,$delivery_plan_date,$sales_emp_id) = pg_fetch_array($result,0);
	$sales_emp_id = trim($sales_emp_id);
	$delivery_plan_date = trim($delivery_plan_date);
	$product_id = json_decode($product_id);
	$attachfile = json_decode($attachfile);
	if(!$product_id){
		$product_id =array();
	}
	if(!$attachfile){
		$attachfile =array();
	}
	$shop_id = sprintf("%03d",$shop_id);
if(preg_match("/^99([0-9]{2})$/",$status,$str)){
	$status = 99;
	$past_st = $str[1];
	$editbtn = "";
}
if($status == 10){
	$editbtn = "";
}


$sql = "select tool_id,num,tool_name,quantity,delivery,papersize,toolbiko,tool_emp_id,laminate from order_tool where order_id = $t_order_id and status > '0' order by num";
$result = $ybase->sql($conn,$sql);
$tool_num = pg_num_rows($result);
for($i=0;$i<$tool_num;$i++){
	list($q_tool_id,$q_num,$q_tool_name,$q_quantity,$q_delivery,$q_papersize,$q_toolbiko,$q_tool_emp_id,$q_laminate) = pg_fetch_array($result,$i);
	$tool_id[$q_num] = $q_tool_id;
	$tool_name[$q_num] = $q_tool_name;
	$quantity[$q_num] = $q_quantity;
	$delivery[$q_num] = $q_delivery;
	$papersize[$q_num] = $q_papersize;
	$toolbiko[$q_num] = $q_toolbiko;
	$tool_emp_id[$q_num] = $q_tool_emp_id;
	$laminate[$q_num] = $q_laminate;
}

$sql = "select shooting_id,num,photo_name,price,foodst,photo_biko from order_shooting where order_id = $t_order_id and status > '0' order by num";
$result = $ybase->sql($conn,$sql);
$shooting_num = pg_num_rows($result);
for($i=0;$i<$shooting_num;$i++){
	list($q_shooting_id,$q_num,$q_photo_name,$q_price,$q_foodst,$q_photo_biko) = pg_fetch_array($result,$i);
	$shooting_id[$q_num] = $q_shooting_id;
	$photo_name[$q_num] = $q_photo_name;
	$price[$q_num] = $q_price;
	$foodst[$q_num] = $q_foodst;
	$photo_biko[$q_num] = $q_photo_biko;
}
$ybase->title = "制作物発注書確認";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("制作物発注書確認");
$ybase->ST_PRI .= <<<HTML
HTML;

$ybase->ST_PRI .= <<<HTML
<script src="./order_update.js?$time"></script>
<script>
$(function(){
	$("[id^=deletebtn]").click(function(){
		if(!confirm('本当に削除しますか？')){
			return false;
		}else{
			location.href = $(this).attr('delhref');
		}
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
<td>
<a href="./order_list.php?$param">制作物発注管理に戻る</a>
</td>
</tr>
</tbody>
</table>

<p></p>
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">制作物発注書確認</div>
<div class="card-body">
<div class="text-right">$editbtn</div>
<datalist id="deliverylist">
HTML;
foreach($ybase->section_list as $key => $val){
if($key == "001"){continue;}
if($key == "100"){continue;}
if($key == "107"){continue;}
if($key == "201"){continue;}
if($key == "301"){continue;}
if($key == "304"){continue;}
if($key == "310"){continue;}
if($key > 1000){continue;}
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
<label class="font-weight-bold">発注日{$require}</label>
<input type="date" name="order_date" value="$order_date" class="form-control form-control-sm border-dark" id="order_date" required{$disable}>
</div>
<div class="form-group col-6 col-sm-3">
<label class="font-weight-bold">納品希望日</label>
<input type="date" name="delivery_date" value="$delivery_date" class="form-control form-control-sm border-dark" id="delivery_date"{$disable}>
</div>
<div class="form-group col-6 col-sm-3">
<label class="font-weight-bold">店舗</label>
<select name="shop_id" id="shop_id" class="form-control form-control-sm border-dark"{$disable}>
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
<label class="font-weight-bold">発注者{$require}</label>
<select name="order_emp_id" id="order_emp_id" class="form-control form-control-sm border-dark"{$disable}>
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


<label class="font-weight-bold">制作物{$require}</label>
<div class="form-row ">
<div class="form-group col-12 col-sm-12">
HTML;
foreach($product_list as $key => $val){
if(in_array($key,$product_id)){
	$checked = " checked";
}else{
	$checked = "";
}
$ybase->ST_PRI .= <<<HTML
<div class="form-check  form-check-inline">
<input type="checkbox" name="product_id[$key]" value="1" class="form-check-input" id="product_id$key"{$checked}{$disable}>
<label class="form-check-label" for="product_id$key">$val</label>
</div>
HTML;
}
$ybase->ST_PRI .= <<<HTML
<nobr>(その他の場合<input type="text" name="product_other" value="{$product_other}" size="30" id="product_other"{$disable}>)</nobr>
</div>
</div>


<div class="table-responsive">
<table class="table table-bordered table-sm">
<thead bgcolor="#99bbee">
<tr>
<th>ツール名</th>
<th>発注数量</th>
<th>納品先</th>
<th>サイズ</th>
<th>ラミネート</th>
<th>個別担当者</th>
</tr>
</thead>

</tbody>
HTML;
if(($edit_md == "1")&&($tool_num < 6)){
	$tool_num = 6;
}
for($i=1;$i<=$tool_num;$i++){

$ybase->ST_PRI .= <<<HTML
<tr>
<td><input type="text" name="tool_name[$i]" value="{$tool_name[$i]}" list="productlist" class="form-control form-control-sm" tool_id="{$tool_id[$i]}" id="tool_name$i"{$disable}></td>
<td><input type="number" name="quantity[$i]" value="{$quantity[$i]}" size="5" tool_id="{$tool_id[$i]}" id="quantity$i"{$disable}>枚</td>
<td>
<input type="text" name="delivery[$i]" value="{$delivery[$i]}" list="deliverylist" class="form-control form-control-sm" tool_id="{$tool_id[$i]}" id="delivery$i"{$disable}>
</td>
<td>
<select name="papersize[$i]" tool_id="{$tool_id[$i]}" id="papersize$i"{$disable}>
<option value=""></option>
HTML;
foreach($papersize_list as $key => $val){
if($key == $papersize[$i]){
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
<select name="laminate[$i]" tool_id="{$tool_id[$i]}" id="laminate$i"{$disable}>
HTML;
if(!$laminate[$i]){
$ybase->ST_PRI .= <<<HTML
<option value=""></option>
HTML;
}
foreach($laminate_list as $key => $val){
if($key == $laminate[$i]){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>$val</option>
HTML;
}
if($tool_name[$i]){
	$miketu = "未決定";
}else{
	$miketu = "";
}
$ybase->ST_PRI .= <<<HTML
</select>
</td>
<td>
<select name="tool_emp_id[$i]" tool_id="{$tool_id[$i]}" id="tool_emp_id$i"{$disable}>
<option value="">$miketu</option>

HTML;
foreach($ybase->employee_name_list as $key => $val){
	if(!array_key_exists($key,$order_mg_emp_list)){
		continue;
	}
if($key == $tool_emp_id[$i]){
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

<!----<input type="text" name="toolbiko[$i]" value="{$toolbiko[$i]}" class="form-control form-control-sm" tool_id="{$tool_id[$i]}" id="toolbiko$i"{$disable}>--->
</td>
</tr>
HTML;
}
$ybase->ST_PRI .= <<<HTML

</tbody>
</table>
</div>


<div class="table-responsive">
<table class="table table-bordered table-sm">
<thead bgcolor="#99bbee">
<tr>
<th>撮影予定商品名</th>
<th>税抜価格</th>
<th>食材の状態</th>
<th>備考欄</th>
</tr>
</thead>

</tbody>
HTML;
if(($edit_md == "1")&&($shooting_num < 8)){
	$shooting_num = 8;
}
for($i=1;$i<=$shooting_num;$i++){
$ybase->ST_PRI .= <<<HTML

<tr>
<td><input type="text" name="photo_name[$i]" value="{$photo_name[$i]}" class="form-control form-control-sm" shooting_id="{$shooting_id[$i]}" id="photo_name$i"{$disable}></td>
<td><input type="number" name="price[$i]" value="{$price[$i]}" class="form-control form-control-sm" shooting_id="{$shooting_id[$i]}" id="price$i"{$disable}></td>
<td><input type="text" name="foodst[$i]" value="{$foodst[$i]}" class="form-control form-control-sm" shooting_id="{$shooting_id[$i]}" list="foodstatuslist" id="foodst$i"{$disable}></td>
<td><input type="text" name="photo_biko[$i]" value="{$photo_biko[$i]}" class="form-control form-control-sm" shooting_id="{$shooting_id[$i]}" id="photo_biko$i"{$disable}></td>
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
<label class="font-weight-bold">撮影入り可能日</label>
<input type="date" name="photo_date" value="$photo_date" class="form-control form-control-sm border-dark" id="photo_date"{$disable}>
</div>
<div class="form-group col-6 col-sm-3">
<label class="font-weight-bold">撮影場所</label>
<input type="text" name="photo_place" value="$photo_place" class="form-control form-control-sm border-dark" id="photo_place" list="sectionlist"{$disable}>

</div>
<div class="form-group col-6 col-sm-3">
<label class="font-weight-bold">撮影開始時刻</label>
<input type="time" name="photo_start" value="$photo_start" class="form-control form-control-sm border-dark" id="photo_start"{$disable}>
</div>
<div class="form-group col-6 col-sm-3">
<label class="font-weight-bold">終了時刻</label>
<input type="time" name="photo_end" value="$photo_end" class="form-control form-control-sm border-dark" id="photo_end"{$disable}>
</div>

</div>


<div class="form-row ">
<div class="form-group col-12 col-sm-12">
<label class="font-weight-bold">備考</label>
<textarea name="all_biko" class="form-control form-control-sm border-dark" id="all_biko"{$disable}>
$all_biko
</textarea>
</div>
</div>

<div class="form-row ">
<div class="form-group col-6 col-sm-6">
<label class="font-weight-bold">稟議書{$require}</label>
<select name="approval" id="approval" class="form-control form-control-sm border-dark" required{$disable}>
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
<label class="font-weight-bold">請求月{$require}</label>
HTML;
$yy_st=2021;
$yy_nw = date("Y");
$yy_ed = $yy_nw + 5;
if($ybase->browser_type == "safari"){
	$invoice_date_yy = substr($invoice_date,0,4);
	$invoice_date_mm = substr($invoice_date,5,2);

$ybase->ST_PRI .= <<<HTML
<table border="0">
<tr><td>
<select name="invoice_date_yy" id="invoice_date_yy" class="form-control form-control-sm" required{$disable}>
HTML;
for($k=$yy_st;$k<=$yy_ed;$k++){
	if($k==$invoice_date_yy){
		$selected = " selected";
	}else{
		$selected = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$k"$selected>$k</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</td>
<td>年</td>
<td>
<select name="invoice_date_mm" id="invoice_date_mm" class="form-control form-control-sm" required{$disable}>
HTML;
for($k=1;$k<=12;$k++){
	if($k==$invoice_date_mm){
		$selected = " selected";
	}else{
		$selected = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$k"$selected>$k</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>

</td>
<td>月</td>
</tr>
</table>
HTML;
}else{
$ybase->ST_PRI .= <<<HTML
<input type="month" name="invoice_date" value="$invoice_date" class="form-control form-control-sm border-dark" id="invoice_date" required{$disable}>
HTML;
}
$ybase->ST_PRI .= <<<HTML


</div>
</div>

<div class="card border border-dark mx-auto">
<div class="card-body">

<div class="form-row ">
<div class="form-group col-12 col-sm-12">
<label>作成にあたり全て担当マネージャーの承認が必要になります。</label><br>

本件の制作に関しては
<select name="mg_emp_id"{$disable}>
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

<p></p>
<label class="font-weight-bold">添付ファイル</label>
<div class="form-row">
HTML;
foreach($attachfile as $key => $val){
	$ext = substr($val,strrpos($val,'.') + 1);
	$ext = strtolower($ext);
if(($ext == png) || ($ext == gif) || ($ext == jpg) || ($ext == jpeg)){
$ybase->ST_PRI .= <<<HTML
<div class="form-group col-sm-4">
<img src="./order_imgvw.php?t_order_id=$t_order_id&flplace=$val&ext=$ext" alt="" class="img-thumbnail img-fluid"><br>
<a href="./order_dl.php?t_order_id=$t_order_id&flplace=$val&ext=$ext&no=$key" class="btn btn-outline-dark btn-sm">ダウンロード</a>
<a delhref="./order_attac_del.php?t_order_id=$t_order_id&flplace=$val&ext=$ext&no=$key" class="btn btn-secondary btn-sm{$disable}" id="deletebtnat{$key}">削除</a>
</div>

HTML;
}else{
$ybase->ST_PRI .= <<<HTML
<div class="form-group col-sm-4">
イメージ外ファイル<br>
<a href="./order_dl.php?t_order_id=$t_order_id&flplace=$val&ext=$ext&no=$key" class="btn btn-outline-dark btn-sm">ダウンロード</a>
<a delhref="./order_attac_del.php?t_order_id=$t_order_id&flplace=$val&ext=$ext&no=$key" class="btn btn-secondary btn-sm{$disable}" id="deletebtnat{$key}">削除</a>
</div>
HTML;
}

}
$ybase->ST_PRI .= <<<HTML
</div>
<label>ファイル追加</label>
<div class="form-row">
<form action="order_attac_add.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="t_order_id" value="$t_order_id">
<input type="hidden" name="MAX_FILE_SIZE" value="50000000">
<table border="0">
<tr><td>
<input type="file" name="attachfile[]" multiple="multiple" class="form-control-file form-control-sm" id="attachfile"{$disable}>
</td><td>
<button class="btn btn-secondary  btn-sm" type="submit"{$disable}>追加</button>
</td></tr>
</table>
</form>
</div>
<p></p>
HTML;
if($sales_emp_id){
	$sales_emp_id_disable = $disable;
}else{
	$sales_emp_id_disable = "";
}
if($delivery_plan_date){
	$delivery_plan_date_disable = $disable;
}else{
	$delivery_plan_date_disable = "";
}
if(($ybase->my_section_id != '001')&&($ybase->my_section_id != '003')){
	$sales_emp_id_disable = "disabled";
	$delivery_plan_date_disable = "disabled";
}
if($ybase->my_position_class > 50){
//	$sales_emp_id_disable = "disabled";
//	$delivery_plan_date_disable = "disabled";
}
$ybase->ST_PRI .= <<<HTML
<div class="form-row ">
<div class="form-group col-6 col-sm-3">
<label class="font-weight-bold">納品予定日</label>
<input type="date" name="delivery_plan_date" value="$delivery_plan_date" class="form-control form-control-sm border-dark" id="delivery_plan_date"{$delivery_plan_date_disable}>
</div>

<div class="form-group col-6 col-sm-3">
<label class="font-weight-bold">担当者</label>
<select name="sales_emp_id" id="sales_emp_id" class="form-control form-control-sm border-dark"{$sales_emp_id_disable}>
<option value="">未決定</option>

HTML;
foreach($ybase->employee_name_list as $key => $val){
	if(!array_key_exists($key,$order_mg_emp_list)){
		continue;
	}
if($key == $sales_emp_id){
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

<p></p>



HTML;
$check_nxfg=0;
foreach($inorder_status_list as $key => $val){
	if($check_nxfg == 1){
		$step_st = $key;
		break;
	}
	if($key == $status){
		$check_nxfg = 1;
	}else{
		$check_nxfg = 0;
	}
}
if($status == 99){
	$now_st = 99;
	$nex_st = intval($past_st);
	$holdtext = "保留を解除する";
	$nex_btn = "";
}else{
	$now_st = $status;
	$nex_st = 99;
	$holdtext = "保留にする";
//	$step_st = $status + 1;
	if($step_st > 6){
		$step_st = 7;
	}
	$nex_btn = "<button class=\"btn btn-secondary col-sm-3 border-dark\" type=\"submit\">{$inorder_status_list[$step_st]}にする</button>";
}


if($status == 7){
	$nex_btn = "";
}
if((($ybase->my_section_id == '001')||($ybase->my_section_id == '003')) && !$disable){
$ybase->ST_PRI .= <<<HTML
<div class="card card-body border-light alert-danger text-center">
<div class="form-group col-sm-3 align-self-center">
<select name="change_status" id="change_status" class="form-control form-control border-dark">
HTML;
foreach($inorder_status_list as $key => $val){
if(!$key){continue;}
if($key > 98){continue;}
if($key == $status){
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
</div></div>
HTML;

}else{
$ybase->ST_PRI .= <<<HTML

<div class="card card-body border-light alert-danger text-center"><h5>{$inorder_status_list[$status]}</h5></div>
HTML;
}


$ybase->ST_PRI .= <<<HTML

<p></p>
<br>

<form action="./order_cg.php" method="post" id="Form3">
<input type="hidden" name="t_order_id" value="$t_order_id" id="t_order_id">
<input type="hidden" name="now_st" value="$now_st">
<input type="hidden" name="step_st" value="$step_st" id="step_st">
<div class="text-center">
$nex_btn
</div>
</form>
<br>
<form action="./order_cg.php" method="post" id="Form4">
<input type="hidden" name="t_order_id" value="$t_order_id">
<input type="hidden" name="now_st" value="$now_st">
<input type="hidden" name="nex_st" value="$nex_st" id="nex_st">
<div class="text-center">
<button class="btn btn-light col-sm-3 border-dark" type="submit">$holdtext</button>
</div>
</form>
<br>

<div class="text-right">
<button delhref="./order_cg.php?t_order_id=$t_order_id&now_st=$now_st&step_st=0" class="btn btn-danger col-sm-1 border-dark" id="deletebtnorder"{$del_able}>削除</button>
</div>
<br>
<div class="card border border-dark mx-auto">
<div class="card-body">
<div style="font-size:90%;">【状態変更履歴】</div>
HTML;

$sql = "select order_main_log_id,change_emp_id,to_char(add_date,'YYYY/MM/DD HH24:MI:SS'),nex_status from order_main_log where order_id = $t_order_id order by add_date desc";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
for($i=0;$i<$num;$i++){
	list($q_order_main_log_id,$q_change_emp_id,$q_add_date,$q_nex_status) = pg_fetch_array($result,$i);
	if($q_nex_status == 1){
		$d_sta = "制作物発注書新規作成";
	}else{
		$d_sta = $inorder_status_list[$q_nex_status]."に変更";
	}
$ybase->ST_PRI .= <<<HTML
<div style="font-size:80%;">{$q_add_date}　{$ybase->employee_name_list[$q_change_emp_id]}　{$d_sta}</div>
HTML;
}


$ybase->ST_PRI .= <<<HTML



</div>
</div>


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