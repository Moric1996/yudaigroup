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

$json = file_get_contents('php://input');
// Converts json data into a PHP object 
$data = json_decode($json, true);



include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/slip.inc');

$ybase = new ybase();
$slip = new slip();


//$ybase->session_get();

$ybase->my_company_id = 5;

$ybase->make_yournet_employee_list("1");
$slip->supplier_make();

//※1234種別:slip_type 1=>出金伝票 ,2=>売上計上,3=>クレジット利用履歴,4=>感動体験 半角数字
//※1234会社ID:company_id 1=>雄大,5=>YOURNET 半角数字
//※1234部門:section_id 1=>本部,100=>コンシューマ事業部,200=>メディア事業部,300=>SaaS事業部 半角数字
//※1234担当者:charge_emp 10005=>勝又,YOURNET w or mを除く数字　半角数字
//※1234日付:target_date YYYY-MM-DD
//※123対象月:target_month YYYY-MM
//※123相手先:supplier_id 半角数字
//相手先その他:supplier_other 9999999=>その他 text
//※1234金額:money 半角数字
//※123仕訳科目:debit_code 400=>売上高 半角数字
//※1手数料持ち:fee_st 1=>弊社負担 ,2=>相手先負担 半角数字
//※1振込期限:pay_date YYYY-MM-DD
//内容:all_biko text
//添付ファイル:attachfile body=>ファイル本体 ext=>拡張子 0-9 base64,text
//※1手数料持ち:no_release_flag 0=>公開 ,1=>非公開 半角数字

$slip_type = $data['slip_type'];
$company_id = $data['company_id'];
$section_id = $data['section_id'];
$charge_emp = $data['charge_emp'];
$target_date = $data['target_date'];
$target_month = $data['target_month'];
$supplier_id = $data['supplier_id'];
$supplier_other = $data['supplier_other'];
$money = $data['money'];
$debit_code = $data['debit_code'];
$fee_st = $data['fee_st'];
$pay_date = $data['pay_date'];
$all_biko = $data['all_biko'];
$no_release_flag = $data['no_release_flag'];
$api_id = $data['api_id'];
$api_pass = $data['api_pass'];
$credit_id = $data['credit_id'];


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

if($api_id != 'yournetapi8987'){
	$slip->api_error('400',"api_idが正しくありません");
}
if($api_pass != 'kHDoji34gjsdHH'){
	$slip->api_error('400',"api_passが正しくありません");
}

/////////////////////////////////////////
if(!preg_match("/^[0-9]+$/i",$slip_type)){
	$slip->api_error('400',"種別が正しくありません");
}
if(!preg_match("/^[0-9]+$/i",$company_id)){
	$slip->api_error('400',"会社名が正しくありません");
}
if(!preg_match("/^[0-9]+$/i",$section_id)){
	$slip->api_error('400',"部門が正しくありません");
}
if(!preg_match("/^[0-9]+$/i",$charge_emp)){
	$slip->api_error('400',"担当が正しくありません");
}
if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/i",$target_date)){
	$slip->api_error('400',"日付が正しくありません,{$target_date}");
}
if(!preg_match("/^[0-9]{4}-[0-9]{2}$/i",$target_month)){
	$slip->api_error('400',"対象月が正しくありません,{$target_month}");
}else{
	$in_target_month = $target_month."-01";
}
if($slip_type == 4){
	$supplier_id = 3514;
}elseif(!preg_match("/^[0-9]+$/i",$supplier_id)){
	$slip->api_error('400',"相手先が正しくありません");
}
if(!preg_match("/^[\-0-9]+$/i",$money)){
	$slip->api_error('400',"金額は半角数字のみです");
}
if($slip_type == 4){
	$debit_code = 128;
}elseif(!preg_match("/^[0-9]+$/i",$debit_code)){
	$slip->api_error('400',"仕訳科目が正しくありません");
}
if($slip_type != 3){
	$credit_id = 'null';
}elseif(!preg_match("/^[0-9]+$/i",$credit_id)){
	$slip->api_error('400',"利用カードが正しくありません");
}

if($slip_type == 1){
	if(!preg_match("/^[0-9]+$/i",$fee_st)){
		$slip->api_error('400',"手数料持ちが正しくありません");
	}
	if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/i",$pay_date)){
		$slip->api_error('400',"振込期限が正しくありません");
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

$conn = $ybase->connect(3);

////////////////////////////////
$sql = "select nextval('slip_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$slip->api_error('400',"データベースエラーです。ERROR_CODE:23002");
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
foreach($data['attachfile']['body'] as $key => $val){
	$nn++;
	$ext = $data['attachfile']['ext'][$key];
	$filehead2 = $filehead."_"."$nn";
	$filehead0="$uploaddir"."/"."$filehead2";
	$filename=$filehead0.".".strtolower($ext);
	if($val){
		$dbinname[$nn] .= $filename;
		$decoded = base64_decode($val);
		file_put_contents($filename, $decoded);

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
$slip->api_success();
//print "OK";
exit;

////////////////////////////////////////////////
?>