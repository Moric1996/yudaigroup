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
/////////////////////////////////////////
foreach($slip_type_list as $key => $val){
	$slip->accept_list_make($key,0);
}

$kensu=50;
$tim=time();
/////////////////////////////////////////

$conn = $ybase->connect(3);

//////////////////////////////////////////条件
if($sel_status == ''){
	$sel_status = "1";
}
if($sel_slip_type == ''){
	$sel_slip_type = "1";
}
if($sel_action_date_st){
	$add_sql .= " and a.action_date >= '$sel_action_date_st'";
}
if($sel_action_date_ed){
	$add_sql .= " and a.action_date <= '$sel_action_date_ed'";
}
if($sel_month_st){
	$add_sql .= " and a.month >= '{$sel_month_st}-01'";
}
if($sel_month_ed){
	$add_sql .= " and a.month <= '{$sel_month_ed}-01'";
}
if(preg_match("/^[0-9]+$/",$sel_section_id)){
	$add_sql .= " and a.section_id = $sel_section_id";
}
if(preg_match("/^[0-9]+$/",$sel_charge_emp)){
	$add_sql .= " and a.charge_emp = $sel_charge_emp";
}
if(preg_match("/^[0-9]+$/",$sel_supplier)){
	$add_sql .= " and a.supplier = $sel_supplier";
}
if(preg_match("/^[0-9]+$/",$sel_account)){
	$add_sql .= " and a.account = $sel_account";
}
if(preg_match("/^[0-9]+$/",$sel_status)){
		$add_sql .= " and a.status = '$sel_status'";
}
if(preg_match("/^[0-9]+$/",$sel_slip_type)){
		$add_sql .= " and a.slip_type = '$sel_slip_type'";
}

$accept_sql = "";
$accept_param = "";
if($sel_accept){
	foreach($sel_accept as $key => $val){
		if($val == "1"){
			if($accept_sql){
				$accept_sql .= ",";
			}
			$accept_sql .= "$key";
			$accept_param .= "&sel_accept[{$key}]={$val}";
		}
	}
	$accept_sql = " and accept_list_id in (".$accept_sql.")";
}

$param = "sel_status=$sel_status&sel_slip_type=$sel_slip_type&sel_action_date_st=$sel_action_date_st&sel_action_date_ed=$sel_action_date_ed&sel_month_st=$sel_month_st&sel_month_ed=$sel_month_ed&sel_section_id=$sel_section_id&sel_charge_emp=$sel_charge_emp&sel_supplier=$sel_supplier&sel_account=$sel_account".$accept_param;
//////////////////////////////////////////
if($sel_accept){
	$sql = "select a.slip_id,a.slip_type,to_char(a.month,'YYYY/MM'),a.action_date,a.company_id,a.section_id,a.money,a.supplier,a.supplier_other,a.account,a.fee_st,a.contents,a.attach,a.charge_emp,a.last_accept_list_id,a.memo,a.pay_date,a.add_date,a.up_date,a.status,EXTRACT(EPOCH FROM a.pay_date - current_timestamp) / (60 * 60 * 24) from slip as a inner JOIN (select slip_id from accept_log where accept_status = 0{$accept_sql} and status = '1' group by slip_id) as b ON a.slip_id = b.slip_id  where a.status > '0'{$add_sql} order by a.slip_id desc";
}else{
	$sql = "select a.slip_id,a.slip_type,to_char(a.month,'YYYY/MM'),a.action_date,a.company_id,a.section_id,a.money,a.supplier,a.supplier_other,a.account,a.fee_st,a.contents,a.attach,a.charge_emp,a.last_accept_list_id,a.memo,a.pay_date,a.add_date,a.up_date,a.status,EXTRACT(EPOCH FROM a.pay_date - current_timestamp) / (60 * 60 * 24) from slip as a where a.status > '0'{$add_sql} order by a.slip_id desc";


}
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
//	$ybase->error("対象者がおりません");
}

$allpage = ceil($num / $kensu);
if(!$page){
	$page = 1;
}
$st = ($page - 1) * $kensu;
$end = $st + $kensu;
if($end > $num){
	$end = $num;
}
$ybase->employee_name_list[$ybase->my_employee_id] = str_replace("　"," ",$ybase->employee_name_list[$ybase->my_employee_id]);
list($myfname,$other) = explode(" ",$ybase->employee_name_list[$ybase->my_employee_id]);

$ybase->title = "伝票管理";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("伝票管理");

$ybase->ST_PRI .= <<<HTML
HTML;



$ybase->ST_PRI .= <<<HTML
<script src="./inc/slip_update.js?$tim"></script>
<script type="text/javascript">
$(function(){
	$('select,input').change(function(){
		var str = $("#filter_check").prop('checked');
		if(str == true){
			$("#filter_check").val("1");
		}else{
			$("#filter_check").val("0");
		}
		$("#Filter_Form1").submit();
	});
});
$(function(){
	$('textarea').on('change keyup keydown paste cut', function(){
		var change_val = $(this).attr('rows');
		if ($(this).outerHeight() > this.scrollHeight){
			$(this).height(change_val);
		}
		while ($(this).outerHeight() < this.scrollHeight){
			$(this).height($(this).height() + 1);
		}
	});
});
$(function(){
	$("[id^=delslip]").click(function(){
		if(!confirm('本当に削除しますか？')){
			return false;
		}else{
			location.href = $(this).attr('delhref');
		}
	});
});

</script>
<input type="hidden" name="my_name" id="my_name" value="{$myfname}">
<div class="container">
<table class="table table-bordered table-sm mx-auto small text-center">
<tbody>
<tr>
<td>
<a href="./newslip_fm.php">新規伝票作成</a>
</td>
<td class="table-active">
伝票管理
</td>
<td>
<a href="./insupplier_fm.php">取引先取込</a>
</td>

</tr>
</tbody>
</table>
<p></p>
{$new_add_emp_html}
<p class="text-center">伝票管理</p>


<div class="card border border-primary w-100 mx-auto">
<div class="text-left small">
《絞込み》

</div>
<div class="text-left small">
<form action="" method="post" id="Filter_Form1">

<div class="form-row ">
<div class="form-group col-8 col-sm-6">
<label>【　種　別　】</label>
<select name="sel_slip_type">
HTML;

foreach($slip_type_list as $key => $val){
	if($sel_slip_type === "$key"){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$addselect>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>

</div>

<div class="form-group col-8 col-sm-6">
<label>【　状　態　】</label>
<select name="sel_status">
<option value="a">全て</option>
HTML;

foreach($inorder_status_list as $key => $val){
	if($key == '0'){continue;}
	if($sel_status === "$key"){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$addselect>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>

</div>
</div>


<div class="form-row ">
<div class="form-group col-8 col-sm-6">
<label>【　日　付　】</label>
<nobr><input type="date" name="sel_action_date_st" value="$sel_action_date_st">～<input type="date" name="sel_action_date_ed" value="$sel_action_date_ed"></nobr>
</div>
<div class="form-group col-8 col-sm-6">
<label>【　対象月　】</label>
<nobr><input type="month" name="sel_month_st" value="$sel_month_st">～<input type="date" name="sel_month_ed" value="$sel_month_ed"></nobr>
</div>
</div>

<div class="form-row ">
<div class="form-group col-8 col-sm-6">
<label><nobr>【　部　門　】</nobr></label>
<select name="sel_section_id">
<option value="">全て</option>
HTML;

foreach($yournet_department_list as $key => $val){
	if($sel_section_id == $key){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$addselect>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</div>
<div class="form-group col-8 col-sm-6">
<label><nobr>【　担　当　】</nobr></label>
<select name="sel_charge_emp">
<option value="">全て</option>
HTML;

foreach($ybase->employee_name_list as $key => $val){
	if($sel_charge_emp == $key){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$addselect>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</div>
</div>

<div class="form-row ">
<div class="form-group col-8 col-sm-6">
<label><nobr>【　相手先　】</nobr></label>
<select name="sel_supplier">
<option value="">全て</option>
HTML;

foreach($slip->supplier_list as $key => $val){
	if($sel_supplier == $key){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$addselect>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</div>
<div class="form-group col-8 col-sm-6">
<label><nobr>【　科　目　】</nobr></label>
<select name="sel_account">
<option value="">全て</option>
HTML;

foreach($Journal_code_list as $key => $val){
	if($sel_account == $key){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$addselect>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</div>
</div>

<div class="form-row ">

HTML;

$sel_accept_list_html = "";
foreach($slip->accept_list[$sel_slip_type] as $key => $val){
	if($sel_accept[$key] == "1"){
		$checked = " checked";
	}else{
		$checked = "";
	}
	$sel_accept_list_html .= "<input type=\"checkbox\" name=\"sel_accept[$key]\" value=\"1\" id=\"sel_accept_{$key}\"{$checked}><label for=\"sel_accept_{$key}\">$val</label>　";



}

$ybase->ST_PRI .= <<<HTML
<div class="form-group col-8 col-sm-8">
<label><nobr>【　承認待ち絞込　】</nobr></label>
$sel_accept_list_html
</div>
</div>

HTML;

if($filter_check == 1){
	$addcheckf= " checked";
}else{
	$addcheckf= "";
}

$ybase->ST_PRI .= <<<HTML

<!-----<input type="checkbox" id="filter_check" name="filter_check" {$addcheckf}>絞込み条件を保存する------------></td></tr>
</form>

</div>

</div>


<p></p>
<table class="table table-bordered table-hover table-sm" style="font-size:78%;padding:0px;margin:0px;">
  <thead>
    <tr align="center" class="table-primary">
      <th scope="col">[種別]<br>伝票番号<br>状態</th>
      <th scope="col">
<div class="form-row">
<div class="form-group col-sm-6 my-0">
日付
</div>
<div class="form-group col-sm-6 my-0">
対象月
</div>
</div>
<div class="form-row ">
<div class="form-group col-sm-6 my-0">
<span class="mx-1">部門</span>
</div>
<div class="form-group col-sm-6 my-0">
<span class="mx-1">担当</span>
</div>
</div>
<div class="form-row ">
<div class="form-group col-sm-6 my-0">
<span class="mx-1">相手先</span>
</div>
<div class="form-group col-sm-6 my-0">
<span class="mx-1">科目</span>
</div>
</div>
<div class="form-row ">
<div class="form-group col-sm-12 my-0">
<span class="mx-1">内容</span>
</div>
</div>
<div class="form-row ">
<div class="form-group col-sm-12 my-0">
<hr style="padding:0px;margin:0px;">
<span class="mx-1">メモ</span>

</div>
</div>
</th>
      <th scope="col">金額</th>
      <th scope="col">振込期限<br>(手数料)</th>
      <th scope="col">添付</th>
    </tr>
  </thead>
  <tbody>
HTML;
for($i=$st;$i<$end;$i++){
	list($q_slip_id,$q_slip_type,$q_month,$q_action_date,$q_company_id,$q_section_id,$q_money,$q_supplier,$q_supplier_other,$q_account,$q_fee_st,$q_contents,$q_attach,$q_charge_emp,$q_last_accept_list_id,$q_memo,$q_pay_date,$q_add_date,$q_up_date,$q_status,$q_pay_limit) = pg_fetch_array($result,$i);
	$q_attach = trim($q_attach);
	$q_contents = trim($q_contents);
	$q_supplier_other = trim($q_supplier_other);
	if($q_attach){
		$q_attach_arr = json_decode($q_attach,true);
	}else{
		$q_attach_arr = array();
	}
	$no=$i+1;
	$d_money = number_format($q_money);
	if($q_supplier == 9999999){
		$d_supplier_name = $q_supplier_other;
	}else{
		$d_supplier_name = $slip->supplier_list[$q_supplier];
	}
	$q_fee_st = trim($q_fee_st);
	$d_pay_date = trim($q_pay_date);
	$q_pay_limit = ceil($q_pay_limit);
	$limit_bgcolor="";
	if(($q_status == "1") && ($q_slip_type == 1)){
		if($q_pay_limit < 3){
			$limit_class="h5 text-danger text-bold";
			$limit_bgcolor="#ffeeee";
		}elseif($q_pay_limit < 7){
			$limit_class="h6 text-bold";
		}else{
			$limit_class="";
		}
		$limit_alarm = "期限まで<br>あと<b class=\"{$limit_class}\">{$q_pay_limit}日</b>";
	}else{
		$limit_alarm = "";
	}
	$q_contents = ereg_replace("([\r|\n|\r\n]+)","<br>",$q_contents);


////////手続きの進行状況確認
	$sql2 = "select accept_log_id,accept_list_id,employee_id,accept_status,to_char(add_date,'MM/DD') from accept_log where slip_id = $q_slip_id and accept_status = 1 and status = '1' order by accept_list_id";
	$result2 = $ybase->sql($conn,$sql2);
	$num2 = pg_num_rows($result2);
	$accept_log_id_list = array();
	$accept_log_employee_list = array();
	$accept_log_status_list = array();
	$accept_log_date_list = array();
	for($ii=0;$ii<$num2;$ii++){
	list($l_accept_log_id,$l_accept_list_id,$l_employee_id,$l_accept_status,$l_add_date) = pg_fetch_array($result2,$ii);
		$accept_log_id_list[$l_accept_list_id] = $l_accept_log_id;
		$accept_log_employee_list[$l_accept_list_id] = $l_employee_id;
		$accept_log_status_list[$l_accept_list_id] = $l_accept_status;
		$accept_log_date_list[$l_accept_list_id] = $l_add_date;
	}


$ybase->ST_PRI .= <<<HTML

<tr bgcolor="$limit_bgcolor">
<td align="center">
[{$slip_type_list[$q_slip_type]}]<br>
$q_slip_id<br>
<br><br><br><br><br>
<b><span id="stat_$q_slip_id">$inorder_status_list[$q_status]</span></b>
<br><br><br><br><br><br>
<a href="#!" type="button" delhref="./slip_del.php?slip_id=$q_slip_id" class="btn btn-dark btn-sm" style="font-size:80%;" id="delslip_{$q_slip_id}">削除</a>
<br>

</td>
<td align="left">

<div class="form-row ">
<div class="form-group col-sm-6">
<span class="mx-1">{$q_action_date}</span>
</div>
<div class="form-group col-sm-6">
 {$q_month}月分
</div>
</div>
<div class="form-row ">
<div class="form-group col-sm-6">
<b style="font-size:70%;">【部門】</b>{$yournet_department_list[$q_section_id]}
</div>
<div class="form-group col-sm-6">
<b style="font-size:70%;">【担当】</b>{$ybase->employee_name_list[$q_charge_emp]}
</div>
</div>
<div class="form-row ">
<div class="form-group col-sm-6">
<b style="font-size:70%;">【相手先】</b>{$d_supplier_name}
</div>
<div class="form-group col-sm-6">
<b style="font-size:70%;">【科目】</b>{$Journal_code_list[$q_account]}
</div>
</div>
<div class="form-row ">
<div class="form-group col-sm-2">
<b style="font-size:70%;">【内容】</b>
</div>
<div class="form-group col-sm-10">
$q_contents
</div>
</div>
HTML;
$accept_html = "<table class=\"table table-borderless table-sm\" style=\"padding:0px;margin:0px;\" id=\"table_slip{$q_slip_id}\"><tr align=\"center\">";
$myaccept_count = count($slip->accept_list[$q_slip_type]);
foreach($slip->accept_list[$q_slip_type] as $key => $val){
	if(isset($slip->accept_employees_list[$q_slip_type][$key])){
		$employees_arr = json_decode($slip->accept_employees_list[$q_slip_type][$key],true);
	}else{
		$employees_arr = array();
	}
	if($employees_arr && (in_array($ybase->my_employee_id,$employees_arr))){//自分が可能かどうか
		$btn_disable = "";
	}else{
		$btn_disable = " disabled";
	}
	if($accept_log_id_list[$key]){//チェック済みの場合
		$aclog_employee_id = $accept_log_employee_list[$key];
		$ybase->employee_name_list[$aclog_employee_id] = str_replace("　"," ",$ybase->employee_name_list[$aclog_employee_id]);
		list($fname,$other) = explode(" ",$ybase->employee_name_list[$aclog_employee_id]);

		$accept_html .= "<td><button type=\"button\" class=\"btn btn-success btn-sm\" style=\"font-size:95%;\" id=\"acok_{$q_slip_id}_{$key}\" slip_id=\"$q_slip_id\" accept_list_id=\"$key\" myaccept_count=\"$myaccept_count\" value=\"2\"{$btn_disable}>$val</button><br>\n";
		$accept_html .= "<span id=\"datename_{$q_slip_id}_{$key}\" style=\"font-size:80%;\">{$accept_log_date_list[$key]} $fname</span></td>\n";

	}else{


		$accept_html .= "<td><button type=\"button\" class=\"btn btn-outline-secondary btn-sm\" style=\"font-size:95%;\" id=\"acok_{$q_slip_id}_{$key}\" slip_id=\"$q_slip_id\" accept_list_id=\"$key\" myaccept_count=\"$myaccept_count\" value=\"1\"{$btn_disable}>$val</button><br>\n";
		$accept_html .= "<span id=\"datename_{$q_slip_id}_{$key}\" style=\"font-size:80%;\"></span></td>\n";
	}
}
		$accept_html .= "</tr></table>\n";

$analysis_rows = intval(strlen($q_memo)/30) + 1;
$analysis_cr = substr_count($q_memo,"\n") + 1;
if($analysis_rows < $analysis_cr){
	$analysis_rows = $analysis_cr;
}
$ybase->ST_PRI .= <<<HTML
<hr style="padding:1px;margin:5px;">
<div class="text-center">
$accept_html
</div>
<div class="form-row ">
<div class="form-group col-sm-12">
<hr style="padding:1px;margin:5px;">
<b style="font-size:70%;">【メモ】</b><br>
<textarea name="memo_{$q_slip_id}" id="memo_{$q_slip_id}" slip_id="$q_slip_id" class="form-control" rows="$analysis_rows" {$indisabled}>
$q_memo
</textarea>
</div>
</div>
</td>
<td align="right">
<b>{$d_money}</b>
</td>
<td align="center">
{$q_pay_date}<br>
({$fee_st_list[$q_fee_st]})<br><br>$limit_alarm
</td>
<td align="center">
HTML;
$d_attach = "";
foreach($q_attach_arr as $key => $val){
	$d_attach .= "<iframe width=\"300\" height=\"300\" src=\"./slip_iframe.php?slip_id=$q_slip_id&attach_no=$key\"></iframe><br><a href=\"dl.php?slip_id=$q_slip_id&attach_no=$key\" type=\"button\" class=\"btn btn-secondary btn-sm\">ダウンロード</a><br>";
}
$ybase->ST_PRI .= <<<HTML
$d_attach
</td>
</tr>

HTML;


}

$bf=$page-1;
$nx=$page+1;
if($bf < 1){
	$addbfclass=" disabled";
}else{
	$addbfclass="";
}
if($nx > $allpage){
	$addnxclass=" disabled";
}else{
	$addnxclass="";
}

$ybase->ST_PRI .= <<<HTML

 </tbody>
</table>
<div class="row">
<div class="col float-right">
<span class="float-right">
{$page}／{$allpage}
<a href="slip_list.php?$param&page=$bf" class="btn btn-sm btn-outline-secondary{$addbfclass}">＜</a>
<a href="slip_list.php?$param&page=$nx" class="btn btn-sm btn-outline-secondary{$addnxclass}">＞</a>
</span>
</div>
</div>
</div>
<p></p>


HTML;

$k = 10 - $num;

if($k > 0){
for($i=0;$i<$k;$i++){
$ybase->ST_PRI .= "<br><br>";

}

}
$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>