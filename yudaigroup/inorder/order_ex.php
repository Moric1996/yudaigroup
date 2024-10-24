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

$conn = $ybase->connect();

$order_date = trim($order_date);
$delivery_date = trim($delivery_date);
$product_other = trim($product_other);
$shop_id = trim($shop_id);
$order_emp_id = trim($order_emp_id);
$photo_date = trim($photo_date);
$photo_place = trim($photo_place);
$photo_start = trim($photo_start);
$photo_end = trim($photo_end);
$all_biko = trim($all_biko);
$approval = trim($approval);
$invoice_date = trim($invoice_date);
$invoice_date_yy = trim($invoice_date_yy);
$invoice_date_mm = trim($invoice_date_mm);
$mg_emp_id = trim($mg_emp_id);


$headers = apache_request_headers();
$heads="headers::\n";
foreach($headers as $header => $value) {
	$heads .= "$header: $value\n";
}
$cook="COOKIE::\n";
foreach($_COOKIE as $header => $value) {
	$cook .= "$header: $value\n";
}

mail("katsumata@yournet-jp.com","YUDAI_order","$heads\n$cook");



/////////////////////////////////////////
if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/i",$order_date)){
	$ybase->error("発注日が正しくありません");
}

if(!preg_match("/^[0-9]+$/i",$order_emp_id)){
	$ybase->error("発注者が正しくありません");
}
if(!$product_id){
	$ybase->error("制作物を選択してください");
}
if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/i",$photo_date)){
	$photo_date = "";
}
if($photo_start && !$photo_date){
	$ybase->error("撮影開始時刻を設定する場合は、撮影入り可能日を設定してください");
}
if($photo_end && !$photo_date){
	$ybase->error("撮影終了時刻を設定する場合は、撮影入り可能日を設定してください");
}

if(!preg_match("/^[0-9]+$/i",$approval)){
	$ybase->error("稟議書を選択してください");
}
if(!$invoice_date){
	if(preg_match("/^[0-9]{4}$/i",$invoice_date_yy) && preg_match("/^[0-9]{1,2}$/i",$invoice_date_mm)){
		$invoice_date = "$invoice_date_yy"."-".sprintf("%02d", $invoice_date_mm);
	}
}

if(!preg_match("/^[0-9]{4}-[0-9]{2}$/i",$invoice_date)){
	$ybase->error("請求月が正しくありません");
}
$product_other = addslashes($product_other);
$photo_place = addslashes($photo_place);
$all_biko = addslashes($all_biko);
if(!$product_id[99]){
	$product_other = "";
}elseif(!$product_other){
	$ybase->error("制作物のその場の場合を入力してください");
}
$product_id_csv = "";
foreach($product_id as $key => $val){
	if($product_id_csv){
		$product_id_csv .= ",";
	}
	$product_id_csv .= $key;
}
$in_product_id = "'{"."$product_id_csv"."}'";

if(!$shop_id){
	$in_shop_id = "null";
}else{
	$in_shop_id = $shop_id;
}
if(!$delivery_date){
	$in_delivery_date = "null";
}else{
	$in_delivery_date = "'$delivery_date'";
}
if(!$photo_date){
	$in_photo_date = "null";
}else{
	$in_photo_date = "'{$photo_date}'";
}
if(!$photo_start){
	$in_photo_start = "null";
}else{
	$in_photo_start = "'{$photo_date} {$photo_start}:00'";
}
if(!$photo_end){
	$in_photo_end = "null";
}else{
	$in_photo_end = "'{$photo_date} {$photo_end}:00'";
}
if(!$mg_emp_id){
	$in_mg_emp_id = "null";
}else{
	$in_mg_emp_id = $mg_emp_id;
}
////////////////////////////////
$sql = "select nextval('order_main_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:23002");
}
$new_order_id = pg_fetch_result($result,0,0);

////////////////////////////////
$uploaddir = '/home/yournet/yudai/inorder';
if(!file_exists($uploaddir)){
	mkdir($uploaddir, 0775);
}
$ttt=time().rand(1000,9999);

$filehead = $new_order_id."_".$ttt;
$dbinname = "";
$nn = 0;
foreach($_FILES["attachfile"]['name'] as $key => $val){
	$nn++;
	$ext = substr($val,strrpos($val,'.') + 1);
	$filehead0=$uploaddir."/".$filehead.$nn;
	$filenamesam=$filehead0."_thum.png";
	$filename=$filehead0.".$ext";
	if($val){
		if($dbinname){
			$dbinname .= ",";
		}
		$dbinname .= $filename;
		if(!move_uploaded_file($_FILES["attachfile"]['tmp_name'][$key],$filename)){
			$msg = "ファイルのアップロードに失敗しました。再度お試し下さい。ERROR_CODE:10891";
			$ybase->error("$msg");
		}
		if($key > 10){
			$msg = "ファイルの複数アップロードは10ファイルまでにしてください。ERROR_CODE:10892";
			$sbase->error("$msg");
		}
//	$ybase->thumbnail_make($filename,$filenamesam,$ext,$uploaddir,$filehead);

	}
}

$dbinname = "{".$dbinname."}";
/////////////////////////////////

$sql = "insert into order_main (order_id,order_date,delivery_date,product_id,product_other,shop_id,order_emp_id,photo_date,photo_place,photo_start,photo_end,all_biko,approval,invoice_date,mg_emp_id,attachfile,add_date,status) values ($new_order_id,'$order_date',$in_delivery_date,$in_product_id,'$product_other',$in_shop_id,$order_emp_id,$in_photo_date,'$photo_place',$in_photo_start,$in_photo_end,'$all_biko',$approval,'{$invoice_date}-01',$in_mg_emp_id,'$dbinname','now','1')";
$result = $ybase->sql($conn,$sql);

$sql = "insert into order_main_log (order_main_log_id,order_id,change_emp_id,add_date,nex_status) values (nextval('order_main_log_id_seq'),$new_order_id,'{$ybase->my_employee_id}','now','1')";
$result = $ybase->sql($conn,$sql);


foreach($tool_name as $key => $val){
	$val = trim($val);
	if(!$val){continue;}
	$val = addslashes($val);
	$in_quan = trim($quantity[$key]);
	if(!preg_match("/^[0-9]+$/",$in_quan)){
		$in_quan = "null";
	}
	$in_delivery = trim($delivery[$key]);
	$in_delivery = addslashes($in_delivery);
	$in_papersize = trim($papersize[$key]);
	if(!$in_papersize){
		$in_papersize = "null";
	}
	$in_toolbiko = trim($toolbiko[$key]);
	$in_toolbiko = addslashes($in_toolbiko);
	$sql = "select nextval('order_tool_id_seq')";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$ybase->error("データベースエラーです。ERROR_CODE:23002");
	}
	$new_tool_id = pg_fetch_result($result,0,0);
	$sql = "insert into order_tool (tool_id,order_id,num,tool_name,quantity,delivery,papersize,toolbiko,add_date,status) values ($new_tool_id,$new_order_id,$key,'$val',$in_quan,'$in_delivery',$in_papersize,'$in_toolbiko','now','1')";
	$result = $ybase->sql($conn,$sql);
}

foreach($photo_name as $key => $val){
	$val = trim($val);
	if(!$val){continue;}
	$val = addslashes($val);
	$in_price = trim($price[$key]);
	if(!preg_match("/^[0-9]+$/",$in_price)){
		$in_price = "null";
	}
	$in_foodst = trim($foodst[$key]);
	$in_foodst = addslashes($in_foodst);
	$in_photo_biko = trim($photo_biko[$key]);
	$in_photo_biko = addslashes($in_photo_biko);
	$sql = "select nextval('order_shooting_id_seq')";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$ybase->error("データベースエラーです。ERROR_CODE:23002");
	}
	$new_shooting_id = pg_fetch_result($result,0,0);
	$sql = "insert into order_shooting (shooting_id,order_id,num,photo_name,price,foodst,photo_biko,add_date,status) values ($new_shooting_id,$new_order_id,$key,'$val',$in_price,'$in_foodst','$in_photo_biko','now','1')";
	$result = $ybase->sql($conn,$sql);
}


$ybase->title = "新規制作物発注書作成完了";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("制作物発注書管理");

$ybase->ST_PRI .= <<<HTML
<div class="container">

<p></p>
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">新規制作物発注書作成完了</div>
<div class="card-body">

<p></p>
<p></p>
新規制作物発注書作成完了しました
<p></p>
<p></p>
<div style="text-align:center;"><a class="btn btn-secondary" href="./order_fm.php" role="button">戻る</a></div>
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