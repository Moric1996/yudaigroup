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
mb_language("Japanese");
mb_internal_encoding("UTF-8");

///////////////////////////////////////////////////////////////
include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/aplus.inc');
include(dirname(__FILE__).'/../slip/inc/slip.inc');

$ybase = new ybase();
$aplus = new aplus();
$slip = new slip();

////////////////////////////////////////////////define
if(!isset($shop_id)){
	$shop_id = '';
}
if(!isset($selyymm)){
	$selyymm = '';
}
if(!isset($selyymmdd)){
	$selyymmdd = '';
}
if(!isset($q_answer_count_arr)){
	$q_answer_count_arr = '';
}
if(!isset($yy)){
	$yy = '';
}
if(!isset($mm)){
	$mm = '';
}
if(!isset($param)){
	$param = '';
}
$ybase->session_get();

///////////////////////////////////////////////
if(!preg_match("/^[0-9]{4}\-[0-9]{2}$/",$tar_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:1442");
}
if(!preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/",$target_date)){
	$ybase->error("パラメーターエラー。ERROR_CODE:1443");
}
///////////////////////////////////////////////
//$edit_f = 1;
/////////////////////////////////////////
$aplus->company_list_make();
$slip->supplier_make();

$conn = $ybase->connect(4);
$add_sql = "";
$YY = substr($tar_month,0,4);
$MM = substr($tar_month,5,2);
$start_day = date('Y-m-d',mktime(0,0,0,$MM,1,$YY));
$end_day = date('Y-m-d',mktime(0,0,0,$MM + 1,0,$YY));
$add_sql .= " and a.trans_date between '{$start_day}' and '{$end_day}'";

$sql = "select a.paper_banktrans_log_id,a.company_id,to_char(a.trans_date,'YYYYMMDD'),a.claim_id,a.money,b.company_name,b.original_id,b.supplier from paper_banktrans_log as a left join company_list as b on a.company_id = b.company_id where a.status = '1' and a.trans_result = '0'{$add_sql} order by a.paper_banktrans_log_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

$transdate = substr($target_date,0,4).substr($target_date,5,2).substr($target_date,8,2);

$k=0;
$all_price = 0;
$csv_body = "";
for($i=0;$i<$num;$i++){
	list($q_paper_banktrans_log_id,$q_company_id,$q_trans_date,$q_claim_id,$q_money,$q_company_name,$q_original_id,$q_supplier) = pg_fetch_array($result,$i);
	$q_company_id = trim($q_company_id);
	$q_trans_date = trim($q_trans_date);
	$q_claim_id = trim($q_claim_id);
	$q_money = trim($q_money);
	$q_company_name = trim($q_company_name);
	$q_original_id = trim($q_original_id);
	$q_supplier = trim($q_supplier);
	$k++;
	$all_price += $q_money;
	$fx4_code = $slip->supplier_code_list[$q_supplier];
	if(!$fx4_code){
		$ybase->error("FX4用コードがない取引先があります。伝票管理の「取引先取込」からFX4の取引先データを取り込んでください。{$q_company_name}");
	}
	$text = "取引先振替スクパス{$MM}月口座振替分";
	$text = mb_convert_encoding($text, 'sjis-win', 'UTF-8');

//取引年月日	課税区部	借方勘定科目コード	借方補助科目コード	貸方勘定科目コード	貸方補助科目コード	取引金額	取引先コード	元帳摘要	部門コード

	$csv_body .= "1{$q_paper_banktrans_log_id},$transdate,0,9992,,1122,,{$q_money},{$fx4_code},\"{$text}\",300\r\n";

}

	$text = "取引先振替スクパス{$MM}月口座振替分";
	$text = mb_convert_encoding($text, 'sjis-win', 'UTF-8');
//457 zeusコード
//	$csv_body .= "1{$transdate},{$transdate},0,1122,,9992,,{$all_price},457,\"{$text}\",300\r\n";

$char_set = "charset=Shift_JIS";

$filename = "schpass_banktrans_tofx_".date("Ymd").time().".csv";
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