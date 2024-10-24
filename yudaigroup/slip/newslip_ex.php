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

$conn = $ybase->connect(3);

$slip_type = trim($slip_type);
$company_id = trim($company_id);
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
if(!preg_match("/^[0-9]+$/i",$slip_type)){
	$ybase->error("種別が正しくありません");
}
if(!preg_match("/^[0-9]+$/i",$company_id)){
	$ybase->error("会社名が正しくありません");
}
if(!preg_match("/^[0-9]+$/i",$section_id)){
	$ybase->error("部門が正しくありません");
}
if(!preg_match("/^[0-9]+$/i",$charge_emp)){
	$ybase->error("担当が正しくありません");
}
if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/i",$target_date)){
	$ybase->error("日付が正しくありません,{$target_date}");
}
if(!preg_match("/^[0-9]{4}-[0-9]{2}$/i",$target_month)){
	$ybase->error("対象月が正しくありません,{$target_month}");
}else{
	$in_target_month = $target_month."-01";
}
if($slip_type == 4){
	$supplier_id = 3514;
}elseif(!preg_match("/^[0-9]+$/i",$supplier_id)){
	$ybase->error("相手先が正しくありません");
}
if(!preg_match("/^[\-0-9]+$/i",$money)){
	$ybase->error("金額は半角数字のみです");
}
if($slip_type == 4){
	$debit_code = 128;
}elseif(!preg_match("/^[0-9]+$/i",$debit_code)){
	$ybase->error("仕訳科目が正しくありません");
}
if($slip_type != 3){
	$credit_id = 'null';
}elseif(!preg_match("/^[0-9]+$/i",$credit_id)){
	$ybase->error("利用カードが正しくありません");
}

if($slip_type == 1){
	if(!preg_match("/^[0-9]+$/i",$fee_st)){
		$ybase->error("手数料持ちが正しくありません");
	}
	if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/i",$pay_date)){
		$ybase->error("振込期限が正しくありません");
	}else{
		$in_pay_date = "'".$pay_date."'";
	}
}else{
	$fee_st = "null";
	$in_pay_date = "null";
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
$sql = "select nextval('slip_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:23002");
}
$new_slip_id = pg_fetch_result($result,0,0);

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
if($dbinname){
	$json_dbinname = json_encode($dbinname);
	$json_dbinname = "'".$json_dbinname."'";
}else{
	$json_dbinname = 'null';
}
/////////////////////////////////

$sql = "insert into slip (slip_id,slip_type,month,action_date,company_id,section_id,money,supplier,supplier_other,account,fee_st,contents,attach,charge_emp,last_accept_list_id,memo,pay_date,add_date,up_date,status,no_release,credit_id) values ($new_slip_id,$slip_type,'$in_target_month','$target_date',$company_id,$section_id,$money,$supplier_id,'$supplier_other',$debit_code,$fee_st,'$all_biko',$json_dbinname,$charge_emp,null,'',$in_pay_date,'now','now','1','$no_release_flag',$credit_id)";
$result = $ybase->sql($conn,$sql);

$sql = "update accept_log set status = '0' where slip_id = $new_slip_id and status = '1'";
$result = $ybase->sql($conn,$sql);

$sql = "select accept_list_id,accept_type,accept_name,send_employees from accept_list where company_id = {$ybase->my_company_id} and slip_type = $slip_type and section_id = 0 and status = '1' order by accept_jun,accept_list_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$check_flg = 0;
for($i=0;$i<$num;$i++){
	list($q_accept_list_id,$q_accept_type,$q_accept_name,$q_send_employees) = pg_fetch_array($result,$i);
	if(!$check_flg){
		$q_send_employees = trim($q_send_employees);
		if($q_send_employees){
			$q_send_employees_arr = json_decode($q_send_employees,true);
			foreach($q_send_employees_arr as $key => $val){
				$sql2 = "insert into accept_log (accept_log_id,slip_id,accept_list_id,to_employee_id,send_flg,employee_id,accept_status,add_date,status) values (nextval('accept_log_id_seq'),$new_slip_id,$q_accept_list_id,$val,'0',null,0,'now','1')";
				$result2 = $ybase->sql($conn,$sql2);
				$check_flg = 1;
			}
		}else{
			$q_send_employees_arr = array();
			$sql2 = "insert into accept_log (accept_log_id,slip_id,accept_list_id,to_employee_id,send_flg,employee_id,accept_status,add_date,status) values (nextval('accept_log_id_seq'),$new_slip_id,$q_accept_list_id,null,'2',null,0,'now','1')";
			$result2 = $ybase->sql($conn,$sql2);
		}
	}
}

$ybase->title = "新規伝票作成完了";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("新規伝票管理");

$ybase->ST_PRI .= <<<HTML
<div class="container">
<table class="table table-bordered table-sm mx-auto small text-center">
<tbody>
<tr>
<td class="table-active">
新規伝票作成
</td>
<td>
<a href="./slip_list.php">伝票管理</a>
</td>
<td>
<a href="./insupplier_fm.php">取引先取込</a>
</td>
</tr>
</tbody>
</table>
<p></p>
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">新規伝票作成完了</div>
<div class="card-body">

<p></p>
<p></p>
完了しました
<p></p>
<p></p>
<div style="text-align:center;"><a class="btn btn-secondary" href="./newslip_fm.php" role="button">戻る</a></div>
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