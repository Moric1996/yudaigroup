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
///////////////////////////////////////////////////////////////
mb_language("Japanese");
mb_internal_encoding("UTF-8");

include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/aplus.inc');

$ybase = new ybase();
$aplus = new aplus();

////////////////////////////////////////////////define
if(!isset($tar_month)){
	$tar_month = '';
}
if(!isset($tar_company_id)){
	$tar_company_id = '';
}
if(!isset($tar_claim_list)){
	$tar_claim_list = array();
}
if(!isset($param)){
	$param = '';
}
$ybase->session_get();

///////////////////////////////////////////////
/////////////////////////////////////////

if(!$tar_claim_list){
	$ybase->error("対象請求書にチェックを入れてください");
}

$conn = $ybase->connect(4);
$add_sql = "";

$claim_list = implode(",",$tar_claim_list);


$sql = "select plan_date from claim where claim_id in ($claim_list) group by plan_date";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	$ybase->error("口座振替日が全て同じではありません");
}

$sql = "select claim_id,company_id,price_intax,plan_date,paper_banktrans_id,status from claim where claim_id in ($claim_list) order by claim_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$k=0;
$amount=0;
$csv_body = "";

for($i=0;$i<$num;$i++){
	list($q_claim_id,$q_company_id,$q_price_intax,$q_plan_date,$q_paper_banktrans_id,$q_status) = pg_fetch_array($result,$i);
	$q_company_id = trim($q_company_id);
	$q_price_intax = trim($q_price_intax);
	$k++;
	$amount += $q_price_intax;

		$sql2 = "select a.paper_banktrans_id,a.bank_code,a.bank_kana,a.branch_code,a.branch_kana,a.type,a.account_number,a.account_name,a.new_flag,b.customer_num from paper_banktrans as a left join company_list as b on a.company_id = b.company_id where a.paper_banktrans_id = $q_paper_banktrans_id and a.company_id = $q_company_id and a.status > '0'";
		$result2 = $ybase->sql($conn,$sql2);
		$num2 = pg_num_rows($result2);
		if(!$num2){
			$ybase->error("銀行口座データがありません。{$q_company_id}");
		}
		list($q_paper_banktrans_id,$q_bank_code,$q_bank_kana,$q_branch_code,$q_branch_kana,$q_type,$q_account_number,$q_account_name,$q_new_flag,$q_customer_num) = pg_fetch_array($result2,0);
		$num2 = pg_num_rows($result2);
		$q_bank_code = trim($q_bank_code);
		$q_bank_kana = trim($q_bank_kana);
		$q_branch_code = trim($q_branch_code);
		$q_branch_kana = trim($q_branch_kana);
		$q_type = trim($q_type);
		$q_account_number = trim($q_account_number);
		$q_account_name = trim($q_account_name);
		$q_new_flag = trim($q_new_flag);
		$q_customer_num = trim($q_customer_num);
		if(!$q_customer_num){
			$ybase->error("顧客番号データがありません。{$q_company_id}");
		}
		$q_bank_code = sprintf("%04d",$q_bank_code);
		$bank_len = mb_strlen($q_bank_kana);
		$bank_sp = 15 - $bank_len;
		for($ii=1;$ii<=$bank_sp;$ii++){
			$q_bank_kana .= " ";
		}
		$q_branch_code = sprintf("%03d",$q_branch_code);
		$branch_len = mb_strlen($q_branch_kana);
		$branch_sp = 15 - $branch_len;
		for($ii=1;$ii<=$branch_sp;$ii++){
			$q_branch_kana .= " ";
		}
		$q_account_number = sprintf("%07d",$q_account_number);
		$account_len = mb_strlen($q_account_name);
		$account_sp = 30 - $account_len;
		for($ii=1;$ii<=$account_sp;$ii++){
			$q_account_name .= " ";
		}
		$q_price_intax = sprintf("%010d",$q_price_intax);

		$data_record = "2"."$q_bank_code"."$q_bank_kana"."$q_branch_code"."$q_branch_kana"."    "."$q_type"."$q_account_number"."$q_account_name"."$q_price_intax"."$q_new_flag"."$q_customer_num"."0        
";
//		$data_record = mb_convert_encoding($data_record, 'IBM1047', 'UTF-8');
		$data_record = mb_convert_encoding($data_record, 'sjis-win', 'UTF-8');
		$csv_body .= $data_record;
	
	if($q_status == '9'){
		$sql2 = "update claim set status = '1' where claim_id = $q_claim_id";
		$result2 = $ybase->sql($conn,$sql2);

	}
}
	$plan_date_dd = substr($q_plan_date,8,2);
	if($plan_date_dd > 27){
		$plan_date_dd = "27";
	}
	$drawal_date = substr($q_plan_date,5,2)."$plan_date_dd";
	$head_record = "19113404838300ｶ)ﾕｱﾈﾂﾄ                                 ".$drawal_date."                                                              
";

	$head_record = mb_convert_encoding($head_record, 'sjis-win', 'UTF-8');

	$kensu = sprintf("%06d",$k);
	$amount = sprintf("%012d",$amount);
	$trailer_record = "8"."$kensu"."$amount"."000000000000000000000000000000000000                                                                 
";
	$trailer_record = mb_convert_encoding($trailer_record, 'sjis-win', 'UTF-8');
	$end_record = "9                                                                                                                       
";
	$end_record = mb_convert_encoding($end_record, 'sjis-win', 'UTF-8');
	$csv_body = $head_record.$csv_body.$trailer_record.$end_record;

	$char_set = "charset=Shift_JIS";

$filename = "revuen_claim_".date("Ymd").time().".csv";
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