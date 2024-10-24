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
include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/slip.inc');

$ybase = new ybase();
$slip = new slip();
$ybase->session_get();

$ybase->my_company_id = 5;

$ybase->make_yournet_employee_list("1");
$slip->supplier_make();

if(!preg_match("/^[0-9]+$/",$t_slip_id)){
	$ybase->error("パラメータエラー");
}
if(!preg_match("/^[0-9]+$/",$t_slip_type)){
	$ybase->error("パラメータエラー");
}

$conn = $ybase->connect(3);

$section_id = trim($section_id);
$charge_emp = trim($charge_emp);

$supplier_id = trim($supplier_id);
$supplier_other = trim($supplier_other);
$money = trim($money);
$debit_code = trim($debit_code);
$fee_st = trim($fee_st);
$all_biko = trim($all_biko);
$credit_id = trim($credit_id);

/*
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
*/


/////////////////////////////////////////

if(!preg_match("/^[0-9]+$/i",$section_id)){
	$ybase->error("部門が正しくありません");
}
if(!preg_match("/^[0-9]+$/i",$charge_emp)){
	$ybase->error("担当が正しくありません");
}
if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/i",$target_date)){
	$ybase->error("日付が正しくありません,{$target_date}");
}
if(!preg_match("/^[\-0-9]+$/i",$money)){
	$ybase->error("金額は半角数字のみです");
}

if($t_slip_type != 4){
	if(!preg_match("/^[0-9]{4}-[0-9]{2}$/i",$target_month)){
		$ybase->error("対象月が正しくありません");
	}else{
		$in_target_month = $target_month."-01";
	}
	if(!preg_match("/^[0-9]+$/i",$supplier_id)){
		$ybase->error("相手先が正しくありません");
	}
	if(!preg_match("/^[0-9]+$/i",$debit_code)){
		$ybase->error("仕訳科目が正しくありません");
	}
}
if($t_slip_type != 3){
	$credit_id = 'null';
}elseif(!preg_match("/^[0-9]+$/i",$credit_id)){
	$ybase->error("利用カードが正しくありません");
}

if($t_slip_type == 1){
	if(!preg_match("/^[0-9]+$/i",$fee_st)){
		$ybase->error("手数料持ちが正しくありません");
	}
	if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/i",$pay_date)){
		$ybase->error("振込期限が正しくありません");
	}else{
		$in_pay_date = "'".$pay_date."'";
	}
}
if($no_release_flag != "1"){
	$no_release_flag = "0";
}

if($supplier_id != 9999999){
	$supplier_other = "";
}
$supplier_other = pg_escape_string($supplier_other);
$all_biko = pg_escape_string($all_biko);

////////////////////////////////
$uploaddir = '/home/yournet/yudai/slip';
if(!file_exists($uploaddir)){
	mkdir($uploaddir, 0775);
}
$ttt=time().rand(1000,9999);

$filehead = $new_slip_id."_".$ttt;
$dbinname = array();
$nn = 0;
foreach($_FILES["attachfile"]['name'] as $key => $val){
	$nn++;
	$ext = substr($val,strrpos($val,'.') + 1);
	$filehead2 = $filehead."_"."$nn";
	$filehead0="$uploaddir"."/"."$filehead2";
	$filenamesam=$filehead0."_thum.png";
	$filename=$filehead0.".".strtolower($ext);
	if($val){
		$dbinname[$nn] .= $filename;
		if(!move_uploaded_file($_FILES["attachfile"]['tmp_name'][$key],$filename)){
			$msg = "ファイルのアップロードに失敗しました。再度お試し下さい。ERROR_CODE:10891";
			$ybase->error("$msg");
		}
		if($key > 10){
			$msg = "ファイルの複数アップロードは10ファイルまでにしてください。ERROR_CODE:10892";
			$sbase->error("$msg");
		}
//	$ybase->thumbnail_make($filename,$filenamesam,$ext,$uploaddir,$filehead2,500);

	}
}
/////////////////////////////////
if(isset($del_attach)){
	$sql = "select attach from slip where slip_id = $t_slip_id";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$ybase->error("対象書類がみつかりません");
	}
	list($q_attach) = pg_fetch_array($result,0);
	$q_attach = trim($q_attach);
	if($q_attach){
		$q_attach_arr = json_decode($q_attach,true);
	}else{
		$q_attach_arr = array();
	}
	foreach($del_attach as $key => $val){
		$delfile = $q_attach_arr[$key];
		unlink("$delfile");
	}
	$in_attach_arr = array_diff_key($q_attach_arr, $del_attach);
	if($in_attach_arr){
		$json_in_attach_arr = json_encode($in_attach_arr);
		$json_in_attach_arr = "'".$json_in_attach_arr."'";
	}else{
		$json_in_attach_arr = 'null';
	}
	$sql = "update slip set attach = $json_in_attach_arr where slip_id = $t_slip_id";
	$result = $ybase->sql($conn,$sql);
}
if($dbinname){
	$sql = "select attach from slip where slip_id = $t_slip_id";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$ybase->error("対象書類がみつかりません");
	}
	list($q_attach) = pg_fetch_array($result,0);
	$q_attach = trim($q_attach);
	if($q_attach){
		$q_attach_arr = json_decode($q_attach,true);
	}else{
		$q_attach_arr = array();
	}
	$in_attach_arr = array_merge($q_attach_arr, $dbinname);
	if($in_attach_arr){
		$json_in_attach_arr = json_encode($in_attach_arr);
		$json_in_attach_arr = "'".$json_in_attach_arr."'";
	}else{
		$json_in_attach_arr = 'null';
	}
	$sql = "update slip set attach = $json_in_attach_arr where slip_id = $t_slip_id";
	$result = $ybase->sql($conn,$sql);
}
$add_sql = "section_id=$section_id,charge_emp=$charge_emp,action_date='$target_date',money=$money,contents='$all_biko',no_release='$no_release_flag'";
if($t_slip_type != 4){
	$add_sql .= ",month='$in_target_month',supplier=$supplier_id,supplier_other='$supplier_other',account=$debit_code";
}
if($t_slip_type == 1){
	$add_sql .= ",fee_st=$fee_st,pay_date=$in_pay_date";
}
if($t_slip_type == 3){
	$add_sql .= ",credit_id=$credit_id";
}

$sql = "update slip set {$add_sql} where slip_id = $t_slip_id";
$result = $ybase->sql($conn,$sql);

$param = "sel_status=$sel_status&sel_slip_type=$sel_slip_type&sel_action_date_st=$sel_action_date_st&sel_action_date_ed=$sel_action_date_ed&sel_month_st=$sel_month_st&sel_month_ed=$sel_month_ed&sel_section_id=$sel_section_id&sel_charge_emp=$sel_charge_emp&sel_supplier=$sel_supplier&sel_account=$sel_account&sel_slip_id=$sel_slip_id";

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/slip_list.php?{$param}");
exit;

////////////////////////////////////////////////
?>