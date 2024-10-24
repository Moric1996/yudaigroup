<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
mb_language("Japanese");
mb_internal_encoding("UTF-8");

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


///////////////////////////////データ取得
$cvs_text = "";

if(!isset($downloadck)){
	$ybase->error("選択されていません");
}


$systemcode = "1001";//連携システムコード,管理番号,取引年月日(YYYYMMDD),事業者登録番号,取引先名,取引金額,消費税等,品名,登録した記録項目(コード)


foreach($downloadck as $key => $val){
	$slip_id = intval($key / 100);
	$attach_no = substr($key,-2);
	$manage_no = "Y"."$key";

$sql = "select slip_type,to_char(month,'YYYY/MM'),to_char(action_date,'YYYYMMDD'),section_id,money,supplier,supplier_other,account,fee_st,contents,attach,charge_emp,to_char(pay_date,'YYYYMMDD') from slip where slip_id = $slip_id and company_id = $ybase->my_company_id and status > '0'";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データエラー。ERROR_CODE:17783");
}
list($q_slip_type,$q_month,$q_action_date,$q_section_id,$q_money,$q_supplier,$q_supplier_other,$q_account,$q_fee_st,$q_contents,$q_attach,$q_charge_emp,$q_pay_date) = pg_fetch_array($result,0);
	$q_attach_arr = json_decode($q_attach,true);
	if(!$q_attach_arr){
//		$ybase->error("データエラー。ERROR_CODE:17983");
	}
	if($q_supplier == 9999999){
		$comp_name = $q_supplier_other;
	}else{
		$comp_name = $slip->supplier_list[$q_supplier];
	}
	$comp_name = str_replace(" ","",$comp_name);
	$comp_name = str_replace("　","",$comp_name);
	$slip_type_name = $slip_type_list[$q_slip_type];
	$Journal_name = $Journal_code_list[$q_account];

	$cvs_text .= "$manage_no,,$systemcode,$q_action_date,,,\"$comp_name\",$q_money,,\"{$slip_type_name} {$Journal_name} {$q_month}月分 $q_contents\"\r\n";
}

$csv_body = mb_convert_encoding($cvs_text, 'sjis-win', 'UTF-8');

$filename = "evi".time().rand(1000,9999);
$filename .= ".csv";

$char_set = "charset=Shift_JIS";

$filesize = strlen($csv_body);
header("Content-Type: text/csv; {$char_set}");
header('X-Content-Type-Options: nosniff');
header("Content-Length: $filesize");
header("Content-Disposition: attachment; filename=$filename");
header('Connection: close');
while (ob_get_level()) { ob_end_clean(); }
echo $csv_body;
exit;



////////////////////////////////////////////////
?>