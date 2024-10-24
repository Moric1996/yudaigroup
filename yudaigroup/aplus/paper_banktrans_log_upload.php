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
if($ybase->my_company_id != 5){
	$ybase->allow_check($ybase->my_employee_id);
}
//$dbno = $ybase->my_dbno;
//$edit_f = 1;
/////////////////////////////////////////

$conn = $ybase->connect(4);


if(!isset($_FILES["jinjerfile"]['name'][0])){
	$msg = "ファイルのアップロードに失敗しました。再度お試し下さい。ERROR_CODE:108921";
	$ybase->error("$msg");
}
$handle = fopen($_FILES["jinjerfile"]['tmp_name'], "r");
$i=0;
while (($buffer[$i] = fgets($handle, 4096)) !== false) {
	$buffer[$i] = trim($buffer[$i]);
//	$buffer[$i] = mb_convert_encoding($buffer[$i],"UTF-8","jis");
	$i++;
}
fclose($handle);

$now_yy = date('Y');
$now_mm = date('m');
$now_dd = date('d');

$datakubun = substr($buffer[0],0,1);
if($datakubun == "1"){
	$MM = substr($buffer[0],54,2);
	$DD = substr($buffer[0],56,2);
	if(($MM > 9)&&($now_mm < 3)){
		$YY = $now_yy - 1;
	}else{
		$YY = $now_yy;
	}
	$furikaebi = "$YY"."-"."$MM"."-"."$DD";
}else{
	$msg = "データに問題があります。再度お試し下さい。ERROR_CODE:108922";
	$ybase->error("$msg");
}


$ybase->title = "口座振替(紙)データ取込結果";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);
$vavi_pri = $aplus->navi_head_pri();

$sql = "select paper_banktrans_log_id from paper_banktrans_log where trans_date = '$furikaebi' and status > '0'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
$ybase->ST_PRI .= <<<HTML
{$vavi_pri}

<p class="m-6"></p>

<div class="container">
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-success text-center">口座振替(紙)データ取込結果</div>
<div class="card-body">
振替日【{$furikaebi}】<br>
<div class="text-center text-danger">この振替日のデータは既に取り込み済みです。<br>{$num}件</div>
</div>
</div>

</div>

<p></p>
<p></p>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
exit;
}

$ybase->ST_PRI .= <<<HTML
{$vavi_pri}

<p class="m-6"></p>

<div class="container">
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-success text-center">口座振替(紙)データ取込結果</div>
<div class="card-body">
振替日【{$furikaebi}】<br>
<div class="text-center text-danger">取込完了</div>
<table border="1" class="" width="100%">
<tr bgcolor="#eeeeee">
<th>銀行番号</th>
<th>銀行名</th>
<th>支店番号</th>
<th>支店名</th>
<th>種目</th>
<th>口座番号</th>
<th>名義</th>
<th>金額</th>
<th>新規</th>
<th>顧客番号</th>
<th>結果</th>
</tr>
HTML;


foreach($buffer as $key => $val){
	$datakubun = substr($val,0,1);
	if($datakubun == "1"){
		continue;
	}elseif($datakubun == "9"){
		break;
	}elseif($datakubun == "8"){
		$yoteiken = number_format(substr($val,1,6));
		$yoteigou = number_format(substr($val,7,12));
		$sumiken = number_format(substr($val,19,6));
		$sumigou = number_format(substr($val,25,12));
		$funoken = number_format(substr($val,37,6));
		$funogou = number_format(substr($val,43,12));
		break;
	}elseif($datakubun == "2"){
		$d1 = substr($val,1,4);					//引落銀行番号
		$d2 = substr($val,5,15);				//引落銀行名
		$d2 = mb_convert_encoding($d2,"UTF-8","sjis-win");	//引落銀行名
		$d2 = trim($d2);
		$d3 = substr($val,20,3);				//引落支店番号
		$d4 = substr($val,23,15);				//引落支店名
		$d4 = mb_convert_encoding($d4,"UTF-8","sjis-win");	//引落支店名
		$d4 = trim($d4);
		$d5 = substr($val,38,4);				//予備C
		$d6 = substr($val,42,1);				//預金種目
		$d7 = substr($val,43,7);				//口座番号
		$d8 = substr($val,50,30);				//預金者名
		$d8 = mb_convert_encoding($d8,"UTF-8","sjis-win");	//預金者名
		$d8 = trim($d8);
		$d9 = substr($val,80,10);				//請求金額
		$d9 = intval($d9);
		$d10 = substr($val,90,1);				//新規コード
		$d11 = substr($val,91,6);				//委託者コード
		$d12 = substr($val,97,14);				//顧客番号
		$d13 = substr($val,111,1);				//振替結果コード
		$d13 = intval($d13);
		$customer_num = "$d11"."$d12";

		$sql = "select nextval('paper_banktrans_log_id_seq')";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$ybase->error("データベースエラーです。ERROR_CODE:21231");
		}
		$new_paper_banktrans_log_id = pg_fetch_result($result,0,0);

		$sql = "select company_id,original_id,email from company_list where customer_num = '$customer_num' and status = '1' and service_id = $SERVICE_ID";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if($num > 1){
			$ybase->error("会社データが重複している為、これ以上進めません。勝又まで連絡してください。ERORR_CODE:78652");
		}elseif($num == 1){
			list($q_company_id,$q_original_id,$q_email) = pg_fetch_array($result,0);

			$sql2 = "select claim_id,company_id,price_intax,comp_price from claim where company_id = $q_company_id and plan_date = '$furikaebi' and status = '1' order by claim_id";
			$result2 = $ybase->sql($conn,$sql2);
			$num2 = pg_num_rows($result2);
			if($num2){
				list($q_claim_id,$q_company_id,$q_price_intax,$q_comp_price) = pg_fetch_array($result2,0);
				if($d13 === 0){
					$done_comp_price = $d9;
					$done_status = "2";
				}else{
					$done_comp_price = 0;
					$done_status = "3";
				}
				$sql2 = "update claim set status='{$done_status}',paper_banktrans_log_id='$new_paper_banktrans_log_id',comp_price=$done_comp_price,comp_date='$furikaebi' where claim_id = $q_claim_id";
				$result2 = $ybase->sql($conn,$sql2);
				$in_claim_id = $q_claim_id;
			}else{
				$in_claim_id = 'null';
			}
			$in_company_id = $q_company_id;
		}else{
			$sql2 = "select nextval('company_list_id_seq')";
			$result2 = $ybase->sql($conn,$sql2);
			$num2 = pg_num_rows($result2);
			if(!$num2){
				$ybase->error("データベースエラーです。ERROR_CODE:21232");
			}
			$in_company_id = pg_fetch_result($result2,0,0);
			$company_name_kana = mb_convert_kana($d8, "KVC");
			$sql2 = "insert into company_list (company_id,service_id,original_id,customer_num,company_name,company_name_kana,email,add_date,status) values ($in_company_id,$SERVICE_ID,null,'$customer_num','$company_name_kana','$company_name_kana','','now','1')";
			$result2 = $ybase->sql($conn,$sql2);
			$in_claim_id = 'null';
		}
		$sql ="insert into paper_banktrans_log (paper_banktrans_log_id,company_id,trans_date,claim_id,bank_code,bank_kana,branch_code,branch_kana,type,account_number,account_name,money,new_flag,customer_num,trans_result,add_date,status) values ($new_paper_banktrans_log_id,$in_company_id,'$furikaebi',$in_claim_id,'$d1','$d2','$d3','$d4','$d6','$d7','$d8',$d9,'$d10','$customer_num','$d13','now','1')";
		$result = $ybase->sql($conn,$sql);
		if(preg_match("/^[0-9]+$/",$in_company_id)){
		}
		$sql ="select paper_banktrans_id from paper_banktrans where company_id = $in_company_id and status = '1'";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if($num){
			if($d13 === 0){
			$sql2 = "update paper_banktrans set new_flag='0' where company_id = $in_company_id and status = '1'";
			$result2 = $ybase->sql($conn,$sql2);
			}
		}else{
			$sql ="insert into paper_banktrans (paper_banktrans_id,company_id,bank_code,bank_kana,branch_code,branch_kana,type,account_number,account_name,new_flag,add_date,status) values (nextval('paper_banktrans_id_seq'),$in_company_id,'$d1','$d2','$d3','$d4','$d6','$d7','$d8','0','now','1')";
			$result = $ybase->sql($conn,$sql);
		}

	}
if($d13 != "0"){
	$bgcolor="#ffaaaa";
}else{
	$bgcolor="#ffffff";
}
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="$bgcolor">
<td>{$d1}</td>
<td>{$d2}</td>
<td>{$d3}</td>
<td>{$d4}</td>
<td>{$d6}</td>
<td>{$d7}</td>
<td>{$d8}</td>
<td align="right">{$d9}円</td>
<td>{$d10}</td>
<td>{$d12}</td>
<td>{$paper_banktrans_result_list[$d13]}</td>
</tr>

HTML;
}

$ybase->ST_PRI .= <<<HTML
</table></br></br>
<br>
<table class="table table-bordered m-6">
<tr bgcolor="#eeeeee" align="center">
<th>予定件数</th>
<th>予定合計金額</th>
<th>完了件数</th>
<th>完了合計金額</th>
<th>失敗件数</th>
<th>失敗合計金額</th>
</tr>
<tr align="right">
<td>{$yoteiken}件</td>
<td>{$yoteigou}円</td>
<td><b>{$sumiken}</b>件</td>
<td><b>{$sumigou}</b>円</td>
<td class="text-danger">{$funoken}件</td>
<td class="text-danger">{$funogou}円</td>
</tr>
</table></br></br>

</div>
</div>

</div>

<p></p>
<p></p>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>