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
//$edit_f = 1;
/////////////////////////////////////////
$aplus->company_list_make();

$monthon = 1;//月単位で


$now_yy = date('Y');
$now_mm = date('m');
$now_dd = date('d');
if(!isset($tar_month)){
	$tar_month = date('Y-m',mktime(0,0,0,$now_mm - 1,1,$now_yy));
}

$conn = $ybase->connect(4);
$add_sql = "";
if($tar_month){
	$YY = substr($tar_month,0,4);
	$MM = substr($tar_month,5,2);
	$start_day = date('Y-m-d',mktime(0,0,0,$MM,1,$YY));
	$end_day = date('Y-m-d',mktime(0,0,0,$MM + 1,0,$YY));

	$add_sql .= " and a.trans_date between '{$start_day}' and '{$end_day}'";
}
if(preg_match("/^[0-9]+$/",$tar_company_id)){
	$add_sql .= " and a.company_id = $tar_company_id";
}
$sql = "select a.paper_banktrans_log_id,a.company_id,to_char(a.trans_date,'YYYY/MM/DD'),a.claim_id,a.bank_code,a.bank_kana,a.branch_code,a.branch_kana,a.type,a.account_number,a.account_name,a.money,a.new_flag,a.customer_num,a.trans_result,to_char(a.add_date,'YYYY/MM/DD'),b.company_name,b.original_id from paper_banktrans_log as a left join company_list as b on a.company_id = b.company_id where a.status = '1'{$add_sql} order by a.paper_banktrans_log_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

$ybase->title = "口座振替(紙)結果一覧";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);

$vavi_pri = $aplus->navi_head_pri();

$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#tar_month,#tar_company_id').change(function(){
		$("#form1").submit();
	});
	$("[id^=delete]").click(function(){
		var dhref = $(this).attr('delhref');
		if(!confirm('契約者を削除していいですか？')){
		        return false;
		}else{
			location.href = dhref;
		}
	});
});
$(function(){
	$("#table1").tablesorter({
	       widgets: ['zebra']
	});
});

</script>
{$vavi_pri}


<div class="container-fluid">

<p></p>
<!--<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./ana_top.php?$param" role="button">戻る</a></div>-->

<div style="font-size:90%;">

<form action="paper_banktrans_log_list.php" method="post" id="form1">
対象月：<input type="month" name="tar_month" value="$tar_month" id="tar_month">
　対象契約者：<select name="tar_company_id" id="tar_company_id">
<option value="">全て</option>
HTML;

foreach($aplus->company_name_list as $key => $val){
	$val = trim($val);
	if(!$val){continue;}
	if($key == $tar_company_id){
		$selected = " selected";
	}else{
		$selected = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"{$selected}>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML

</select>
</form>

<p></p>

<div class="table-responsive mb-6">
<p class="h5 text-center">口座振替(紙)結果一覧</p>

<table class="table table-sm tablesorter tablesorter-blue" width="90%" id="table1">

<thead>
<tr align="center" bgcolor="#ffafaf">
<th></th>
<th>振替日</th>
<th>識別ID</th>
<th>契約者名</th>
<th>請求書番号</th>
<th>顧客番号</th>
<th>銀行コード</th>
<th>銀行名</th>
<th>支店コード</th>
<th>支店名</th>
<th>口座種類</th>
<th>口座番号</th>
<th>口座名義</th>
<th>金額</th>
<th>新規コード</th>
<th>結果</th>
</tr>
</thead>
<tbody>
HTML;
$k=0;
$all_price = 0;
$ok_price = 0;
$ng_price = 0;

for($i=0;$i<$num;$i++){
	list($q_paper_banktrans_log_id,$q_company_id,$q_trans_date,$q_claim_id,$q_bank_code,$q_bank_kana,$q_branch_code,$q_branch_kana,$q_type,$q_account_number,$q_account_name,$q_money,$q_new_flag,$q_customer_num,$q_trans_result,$q_add_date,$q_company_name,$q_original_id) = pg_fetch_array($result,$i);
	$q_company_id = trim($q_company_id);
	$q_trans_date = trim($q_trans_date);
	$q_claim_id = trim($q_claim_id);
	$q_bank_code = trim($q_bank_code);
	$q_bank_kana = trim($q_bank_kana);
	$q_branch_code = trim($q_branch_code);
	$q_branch_kana = trim($q_branch_kana);
	$q_type = trim($q_type);
	$q_account_number = trim($q_account_number);
	$q_account_name = trim($q_account_name);
	$q_money = trim($q_money);
	$q_new_flag = trim($q_new_flag);
	$q_customer_num = trim($q_customer_num);
	$q_trans_result = trim($q_trans_result);
	$q_company_name = trim($q_company_name);
	$q_original_id = trim($q_original_id);
	$k++;
	if($q_type){
		$d_type = $bank_type_list[$q_type];
	}else{
		$d_type = "";
	}
	$d_trans_result = $paper_banktrans_result_list[$q_trans_result];
	if($q_trans_result != 0){
		$d_trans_result = "<b>".$d_trans_result."</b>";
		$add_class = " class=\"text-danger\"";
	}else{
		$add_class = "";
	}
	if(!$q_new_flag){
		$q_new_flag = 0;
	}
	$d_money = number_format($q_money);
	$all_price += $q_money;
	if($q_trans_result != 0){
		$ng_price += $q_money;
	}else{
		$ok_price += $q_money;
	}

$ybase->ST_PRI .= <<<HTML
<tr{$add_class}>
<td align="center">$k</td>
<td align="center">{$q_trans_date}</td>
<td align="center">{$q_original_id}</td>
<td align="center">{$q_company_name}</td>
<td align="center">{$q_claim_id}</td>
<td align="center">{$q_customer_num}</td>
<td align="center">{$q_bank_code}</td>
<td align="center">{$q_bank_kana}</td>
<td align="center">{$q_branch_code}</td>
<td align="center">{$q_branch_kana}</td>
<td align="center">{$d_type}</td>
<td align="center">{$q_account_number}</td>
<td align="center">{$q_account_name}</td>
<td align="center">{$d_money}</td>
<td align="center">{$bank_new_list[$q_new_flag]}</td>
<td align="center">{$d_trans_result}</td>
</tr>

HTML;
}
$all_price = number_format($all_price);
$ok_price = number_format($ok_price);
$ng_price = number_format($ng_price);

$ybase->ST_PRI .= <<<HTML
</tbody>
<tr bgcolor="#bbbbbb">
<td align="center" colspan="13">計</td>
<td colspan="3" align="center">合計 {$all_price} (成功[{$ok_price}] 失敗[{$ng_price}])</td>
</tr>

</table>
</div>
</div>



</div>
<p class="m-6"></p>
HTML;

if(($tar_company_id == "")&&($tar_month)){

$ybase->ST_PRI .= <<<HTML
<div class="container">
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">FX4用ゼウス入金時資金外諸口振替処理用ファイルダウンロード</div>
<div class="card-body">

ここからダウンロードしたファイルをFX4の仕訳連携の「YOURNETゼウス入金反映システム」で読み込んでください。<br>
<br>入金のあった取引先の売掛を資金外諸口に移動して、資金外諸口の合計金額をゼウスの売掛に移動します。<br>ゼウスの入金の仕訳は別途入力が必要です。<br><br>
<form action="./paper_banktrans_zeus_dl.php" method="post" id="form3">
<input type="hidden" name="tar_month" value="$tar_month">
<div class="form-row">
<div class="form-group col-sm-2 offset-sm-2 text-right">
取引年月日(入金日以降の日付)
</div>
<div class="form-group col-sm-3">
<input type="date" name="target_date" class="form-control-file form-control-sm" id="target_date" required>
</div>
<div class="form-group col-sm-3">
<button class="btn btn-primary btn-sm" type="submit">ダウンロード</button>
</div>
</div>

</form>
</div>
</div>

</div>
<p class="m-6"></p>
HTML;
}
$ybase->ST_PRI .= <<<HTML
<div class="container">
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-success text-center">APLUS口座振替結果取込</div>
<div class="card-body">

ゼウス管理画面　口座振替決済 請求結果データ配信(APLUS)の処理完了データからダウンロードしたファイルをアップロードしてください。<br>
<br>該当する会社データが存在しない場合、自動で会社データも作成されます。<br><br>
<form action="./paper_banktrans_log_upload.php" method="post" enctype="multipart/form-data" id="form2">

<div class="form-row">
<div class="form-group col-sm-4 offset-sm-4">
 <input type="hidden" name="MAX_FILE_SIZE" value="50000000">
<input type="file" name="jinjerfile" class="form-control-file form-control-sm" id="jinjerfile" required>
</div>
<div class="text-right">

<button class="btn btn-primary btn-sm" type="submit">送信</button> <button class="btn btn-secondary btn-sm" type="reset">クリア</button>
</div>
</div>

</form>
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