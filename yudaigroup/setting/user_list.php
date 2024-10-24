<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

if($ybase->my_admin_auth == "0"){
	$ybase->error("このページへの権限がありません。");
}
	$new_add_emp_html = "<a href=\"./new_employee.php\">従業員新規追加</a>\n";


$kensu=50;
$tim=time();

/////////////////////////////////////////
$subject = rawurlencode("雄大グループ業務管理ポータルログイン情報お知らせ");
$url1 = rawurlencode("https://yournet-jp.com/yudaigroup/login.php?accountid=");
$url2 = rawurlencode("&passwd=");
$url3 = rawurlencode("&dmm=$tim");


$mbody0 = rawurlencode("雄大グループ業務管理ポータルサイトへのログイン情報のお知らせです。\r\n\r\n");
$mbody1 = rawurlencode("【社員番号】");
$mbody2 = rawurlencode("\r\n【仮パスワード】");
$mbody3 = rawurlencode("\r\n\r\n下記URLからログイン後、Eメールアドレスの登録とパスワードの変更をしてください。\r\n\r\nhttps://yournet-jp.com/yudaigroup/login.php?accountid=");
$mbody4 = rawurlencode("&passwd=");
$mbody5 = rawurlencode("&dmm=$tim\r\n\r\n※メールアドレスは「yournet-jp.com」のドメインからメールを受け取れる設定にしておいてください。\r\n\r\n上記URLの《QRコード》は↓から\r\n\r\nhttps://yournet-jp.com/yudaigroup/qr.php?d={$url1}");
$mbody6 = rawurlencode("$url2");
$mbody7 = rawurlencode("\r\n\r\n");

$conn = $ybase->connect();

if($ybase->my_admin_auth == "2"){
	$add_sql = " and company_id = {$ybase->my_company_id} and section_id in ({$ybase->section_group_list[$ybase->my_section_id]})";
}elseif($ybase->my_admin_auth == "3"){
	$add_sql = " and company_id = {$ybase->my_company_id} and section_id = '{$ybase->my_section_id}'";
}else{
	$add_sql = "";
}
if(($ybase->my_section_id == "001")||($ybase->my_section_id == "002")){
	$add_sql = "";
}
if(($sel_sex == "")&&(isset($_COOKIE[cookie_sel_sex]))){
	$sel_sex = $_COOKIE[cookie_sel_sex];
}
if(($sel_company_id == "")&&(isset($_COOKIE[cookie_sel_company_id]))){
	$sel_company_id = $_COOKIE[cookie_sel_company_id];
}
if(($sel_section_id == "")&&(isset($_COOKIE[cookie_sel_section_id]))){
	$sel_section_id = $_COOKIE[cookie_sel_section_id];
}
if(($sel_employee_type == "")&&(isset($_COOKIE[cookie_sel_employee_type]))){
	$sel_employee_type = $_COOKIE[cookie_sel_employee_type];
}
if(($sel_position_class == "")&&(isset($_COOKIE[cookie_sel_position_class]))){
	$sel_position_class = $_COOKIE[cookie_sel_position_class];
}

//////////////////////////////////////////条件
if(preg_match("/^[mw]$/",$sel_sex)){
	$add_sql .= " and sex = '$sel_sex'";
	setcookie("cookie_sel_sex","$sel_sex", time() + 3600, "/yudaigroup/setting/");
}else{
	setcookie("cookie_sel_sex","", time() + 3600, "/yudaigroup/setting/");
}
if($sel_company_id && preg_match("/^[0-9]+$/",$sel_company_id)){
	$add_sql .= " and company_id = '$sel_company_id'";
	setcookie("cookie_sel_company_id","$sel_company_id", time() + 3600, "/yudaigroup/setting/");
}else{
	setcookie("cookie_sel_company_id","", time() + 3600, "/yudaigroup/setting/");
}
if($sel_section_id && preg_match("/^[0-9]+$/",$sel_section_id)){
	$add_sql .= " and section_id = '$sel_section_id'";
	setcookie("cookie_sel_section_id","$sel_section_id", time() + 3600, "/yudaigroup/setting/");
}else{
	setcookie("cookie_sel_section_id","", time() + 3600, "/yudaigroup/setting/");
}
if($sel_employee_type && preg_match("/^[0-9]+$/",$sel_employee_type)){
	$add_sql .= " and employee_type = $sel_employee_type";
	setcookie("cookie_sel_employee_type","$sel_employee_type", time() + 3600, "/yudaigroup/setting/");
}else{
	setcookie("cookie_sel_employee_type","", time() + 3600, "/yudaigroup/setting/");
}
if($sel_position_class && preg_match("/^[0-9]+$/",$sel_position_class)){
	$add_sql .= " and position_class = $sel_position_class";
	setcookie("cookie_sel_position_class","$sel_position_class", time() + 3600, "/yudaigroup/setting/");
}else{
	setcookie("cookie_sel_position_class","", time() + 3600, "/yudaigroup/setting/");
}
if($in_name){
	$add_sql .= " and employee_name~'$in_name'";
}
if($in_employee_num){
	$add_sql .= " and employee_num ='$in_employee_num'";
}


//////////////////////////////////////////

$sql = "select employee_id,employee_num,employee_name,sex,company_id,section_id,employee_type,position_name,position_class,view_auth,edit_auth,admin_auth,email,add_date,status from employee_list where status = '1'{$add_sql} order by employee_num";

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


$ybase->title = "従業員管理-ユーザーアカウント管理";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("管理");


$ybase->ST_PRI .= <<<HTML
<script type="text/javascript">
$(function () {
	$('.maillink').on('click', function (event) {
		var empnum = $(this).attr('id');
		var empid = $(this).attr('value');
		var empname = $(this).attr('name');
		var letters = 'abcdefghijklmnopqrstuvwxyz';
		var numbers = '0123456789';
		var string  = letters + letters.toUpperCase() + numbers;
		var len = 8;
		var passwd='';
		for (var i = 0; i < len; i++) {
		passwd += string.charAt(Math.floor(Math.random() * string.length));
		}
		var email = '';
		var subject = '$subject';
		var emailBody = '$mbody0' + empname + '$mbody1' + empnum + '$mbody2' + passwd + '$mbody3' + empnum + '$mbody4' + passwd + '$mbody5' + empnum + '$mbody6' + passwd + '$mbody7';
		$.ajax({
			type: "POST",
			url: "pass_cg_ex.php",
			data: "employee_id=" + empid + "&newpass="+ passwd
		});
		window.location = 'mailto:' + '?subject=' + subject + '&body=' +   emailBody;
	});
});
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

</script>
<div class="container-fluid w-75">
<p></p>
{$new_add_emp_html}
<p class="text-center">従業員管理</p>


<div class="card border border-primary w-100 mx-auto">
<div class="text-left small">
《絞込み》

</div>
<div class="text-left small">
<table border="0" cellpadding="2" width="70%">
<tbody>
<form action="" method="post" id="Filter_Form1">
<tr>
<td align="right">
【 性　別 】</td>
<td>
<select name="sel_sex">
<option value="0">全て</option>
HTML;

foreach($ybase->sex_list as $key => $val){
	if($sel_sex == $key){
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
</td>
<td align="right">
【 会　社 】
</td>
<td>
<select name="sel_company_id">
<option value="0">全て</option>
HTML;

foreach($ybase->company_list as $key => $val){
	if($sel_company_id == $key){
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
</td>
<td align="right">
<nobr>【所属部署・店舗】</nobr>
</td>
<td colspan="2">
<select name="sel_section_id">
<option value="0">全て</option>
HTML;

foreach($ybase->section_list as $key => $val){
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
</td>

</tr>
<tr>
<td align="right">
<nobr>【雇用区分】</nobr>
</td>
<td>
<select name="sel_employee_type">
<option value="0">全て</option>
HTML;

foreach($ybase->employee_type_list as $key => $val){
	if($sel_employee_type == $key){
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
</td>
<td align="right">
<nobr>【役職区分】</nobr>
</td>
<td width="200">
<select name="sel_position_class">
<option value="0">全て</option>
HTML;

foreach($ybase->position_class_list as $key => $val){
	if($sel_position_class == $key){
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
</td>
<td align="right">
</td>
<td>
</td>

</tr>
<tr>
<td align="right">
<nobr>【社員番号】</nobr></td>
<td>
<input type="text" name="in_employee_num" value="$in_employee_num" size="20">
</td>
<td align="right">
【 氏　名 】
</td>
<td>
<input type="text" name="in_name" value="$in_name" size="20">
</td>
<td>
</td>
<td>
</td>

</tr>
<tr><td colspan="6" align="right">
HTML;

if($filter_check == 1){
	$addcheckf= " checked";
}else{
	$addcheckf= "";
}

$ybase->ST_PRI .= <<<HTML

<!-----<input type="checkbox" id="filter_check" name="filter_check" {$addcheckf}>絞込み条件を保存する------------></td></tr>
</form>
</tbody>
</table>
</div>

</div>


<p></p>
<small>※「初期通知」の「メール送信」で新規登録者用の社員番号,パスワードを送るメールが立ち上がります。クリックする度にパスワードが変わりますので注意。</small>
<table class="table table-bordered table-hover table-sm" style="font-size:85%;">
  <thead>
    <tr align="center" class="table-primary">
      <th scope="col">NO.</th>
      <th scope="col">社員番号</th>
      <th scope="col">氏名</th>
      <th scope="col">性別</th>
      <th scope="col">会社</th>
      <th scope="col">所属部署・店舗</th>
      <th scope="col">雇用区分</th>
      <th scope="col">肩書</th>
      <th scope="col">役職区分</th>
      <th scope="col">管理者権限</th>
      <th scope="col">変更</th>
      <th scope="col">初期通知</th>
    </tr>
  </thead>
  <tbody>
HTML;

for($i=$st;$i<$end;$i++){
	list($q_employee_id,$q_employee_num,$q_name,$q_sex,$q_company_id,$q_section_id,$q_employee_type,$q_position_name,$q_position_class,$q_view_auth,$q_edit_auth,$q_admin_auth,$q_email,$q_add_date,$q_status) = pg_fetch_array($result,$i);
$q_email = trim($q_email);
$no=$i+1;
$urlname = rawurlencode("{$q_name} 様\r\n\r\n");
$ybase->ST_PRI .= <<<HTML

<tr align="center">
<td>
$no
</td>
<td>
$q_employee_num
</td>
<td>
<nobr>$q_name</nobr>
</td>
<td>
{$ybase->sex_list[$q_sex]}
</td>
<td>
<nobr>{$ybase->company_list[$q_company_id]}</nobr>
</td>
<td>
{$ybase->section_list[$q_section_id]}
</td>
<td>
{$ybase->employee_type_list[$q_employee_type]}
</td>
<td>
$q_position_name
</td>
<td>
{$ybase->position_class_list[$q_position_class]}
</td>
<td>
{$ybase->admin_auth_list[$q_admin_auth]}
</td>
<td>
<a class="btn btn-outline-info btn-sm" href="./employee_cg.php?cg_employee_id=$q_employee_id&page=$page" role="button">変更</a>
</td>
<td>
HTML;
if($q_email){
$ybase->ST_PRI .= <<<HTML
メール登録済
HTML;
}else{
$ybase->ST_PRI .= <<<HTML
<a href="#" class="maillink" id="$q_employee_num" value="$q_employee_id" name="$urlname">メール送信</a>
HTML;
}
$ybase->ST_PRI .= <<<HTML
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
<a href="user_list.php?page=$bf" class="btn btn-sm btn-outline-secondary{$addbfclass}">＜</a>
<a href="user_list.php?page=$nx" class="btn btn-sm btn-outline-secondary{$addnxclass}">＞</a>
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