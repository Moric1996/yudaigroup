<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();
$ybase->make_employee_list();
$ybase->make_consult_receive_list();
$ybase->consult_cate_list = $ybase->make_common_category_list("1");
$ybase->consult_cate_list2 = $ybase->make_common_category_list();
$ybase->consult_tab_list = $ybase->make_common_tab_list("1");
$ybase->consult_tab_list2 = $ybase->make_common_tab_list();

$kensu=10;
$kensu2=10;
$cate = 2;

switch($select_tab){
	case 2:
		$tab_active1 = "";
		$tab_active2 = " active";
		$tab_active3 = "";
		break;
	case 3:
		$tab_active1 = "";
		$tab_active2 = "";
		$tab_active3 = " active";
		break;
	default:
		$tab_active1 = " active";
		$tab_active2 = "";
		$tab_active3 = "";
		break;
}

/////////////////////////////////////////

$conn = $ybase->connect();

$sql = "select email from employee_list where employee_id = $ybase->my_employee_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$q_email = pg_fetch_result($result,0,0);
	$q_email = trim($q_email);
}
if($q_email){
$sql = "select config from mail_send where employee_id = $ybase->my_employee_id and cate = $cate";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$q_config = pg_fetch_result($result,0,0);
}
	$disabled = "";
	$sendcheck_mess = "メールでの通知";
}else{
	$disabled = " disabled";
	$sendcheck_mess = "<a href=\"../user/pass_cg.php\">メールでの通知にはメールアドレスの登録が必要です</a>";
}
if($q_config == "1"){
	$send_checked = " checked";
}else{
	$send_checked = "";
}
$sql = "select consult_id from consult where send_id = $ybase->my_employee_id and type = '1' and status <> '0' order by parent_id desc,type,add_date";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$allpage = ceil($num / $kensu);
if(!$page){
	$page = 1;
}
$st = ($page - 1) * $kensu;
$end = $st + $kensu -1;
if($end > ($num - 1)){
	$end = $num - 1;
}
if(!$num){
	$q_content = "<br><div style=\"text-align:center;text-size:110%;\">{$ybase->my_name}さんが送信した相談・提案はありません</div><br>";
}else{
$all_parent_id = pg_fetch_all_columns($result,0);
$all_parent_id_list = implode(",", $all_parent_id);

$sql = "select consult_id,parent_id,type,send_id,return_id,r_flag,title,mess,to_char(add_date,'YYYY/MM/DD HH24:MI:SS'),status from consult where parent_id in ($all_parent_id_list) and status <> '0' order by parent_id desc,type,add_date";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

}

$sql2 = "select common_id,consult_id,campaney_id,category_id,employee_id,tabs,title,mess,to_char(add_date,'YYYY/MM/DD HH24:MI:SS'),status from common_info where status <> '0'";
$add_sql2 = "";
if(preg_match("/^[0-9]+$/",$sel_com_cate)){
	$add_sql2 .= " and category_id = $sel_com_cate";
}
if($sel_com_tabs){
$sel_com_tabs_sql = "";
	$i=0;
	foreach($sel_com_tabs as $key => $val){
		if($sel_com_tabs[$key] == "1"){
			if($i > 0){
				$sel_com_tabs_sql .= ",";
			}
			$sel_com_tabs_sql .= "$key";
			$i++;
		}
	}
	$add_sql2 .= " and tabs && array[".$sel_com_tabs_sql."]";
}
$sql2 .= $add_sql2." order by add_date desc";
$result2 = $ybase->sql($conn,$sql2);
$num2 = pg_num_rows($result2);
$allpage2 = ceil($num2 / $kensu2);
if(!$page2){
	$page2 = 1;
}
$st2 = ($page2 - 1) * $kensu2;
$end2 = $st2 + $kensu2;
if($end2 > ($num2)){
	$end2 = $num2;
}
if(!$num2){
	$q_content2 = "<br><div style=\"text-align:center;text-size:110%;\">該当する共有情報はありません</div><br>";
}else{
	$q_content2 = "";
}
if(!$select_tab){
if(!$num && !$num2){
	$select_tab = 2;
}elseif(!$num){
	$select_tab = 3;
}
}
switch($select_tab){
	case 2:
		$tab_active1 = "";
		$tab_active2 = " active";
		$tab_active3 = "";
		break;
	case 3:
		$tab_active1 = "";
		$tab_active2 = "";
		$tab_active3 = " active";
		break;
	default:
		$tab_active1 = " active";
		$tab_active2 = "";
		$tab_active3 = "";
		break;
}

/////////////////////////投稿完了後の表示
if($new_mess_sent == 1){
	$new_mess_dis = "<div style=\"text-align:center;text-size:110%;color:red;\">送信完了!</div>";
	$new_mess_sent = "";
}else{
	$new_mess_dis = "";
}
////////////////////////

$ybase->title = "相談・提案";
if($ybase->my_admin_auth == "1"){
	$addtitle = "　<a href=\"./consult_manage.php\" class=\"btn btn-secondary btn-sm\">「相談・提案」管理画面へ</a>";
}else{
	$addtitle = "";
}

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri($ybase->title.$addtitle);

$ybase->ST_PRI .= <<<HTML
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
<script>
$(function($) {
$('#form1_submit').click(function(){
	var title = $('#Input1').val();
	var mess = $('#Textarea1').val();
	if(title == ""){
		$("#titledisplay").css("display", "block");
		return false;
	}else{
		$("#titledisplay").css("display", "none");
	}
	if(mess == ""){
		$("#messdisplay").css("display", "block");
		return false;
	}else{
		$("#messdisplay").css("display", "none");
	}
    if(!confirm("ご確認ください\\r\\n\\r\\n【タイトル】\\r\\n" + title + '\\r\\n\\r\\n【内容】\\r\\n' + mess)){
        /* キャンセルの時の処理 */
        return false;
    }else{
        /*　OKの時の処理 */
	$('#newform1').submit();
    }
});

$('[id^=reform_display]').click(function(){
	var ii = $(this).val();
	if ($('#recard' + ii).css('display') == 'none') {
		$('#recard' + ii).css("display", "block");
		$(this).text('返信フォームを非表示');
	}else{
		$('#recard' + ii).css("display", "none");
		$(this).text('返信フォームを表示');
	}
});
	$('textarea').on('change keyup keydown paste cut', function(){
		var change_val = $(this).attr('rows');
		if ($(this).outerHeight() > this.scrollHeight){
			$(this).height(change_val);
		}
		while ($(this).outerHeight() < this.scrollHeight){
			$(this).height($(this).height() + 1);
		}
	});
	$("#sel_com_cate,[id^=comkeytab]").change(function(){

		$("#Filter_Form1").submit();
	});

});
</script>
<style>
input.nocheck {
	opacity: 0;
}
</style>

<div class="container">
<p></p>
<main class="p-3">
  <!-- 4個分のタブ -->
  <ul class="nav nav-tabs nav-pills">
    <li class="nav-item">
      <a href="#consult_tab1" class="nav-link{$tab_active1}" data-toggle="tab">「相談・提案」済一覧</a>
    </li>
    <li class="nav-item">
      <a href="#consult_tab2" class="nav-link{$tab_active2}" data-toggle="tab">新規「相談・提案」</a>
    </li>
    <li class="nav-item">
      <a href="#consult_tab3" class="nav-link{$tab_active3}" data-toggle="tab">共有情報</a>
    </li>
  </ul>

<!-- コンテンツ -->
<div class="tab-content">


<!-- TAB1 -->
<div id="consult_tab1" class="tab-pane{$tab_active1}">
<p></p>
$new_mess_dis
<p></p>
<p></p>

<div class="checkbox" style="text-align:right;">
<span style="font-size:80%;">$sendcheck_mess</span>
<input type="checkbox" data-toggle="toggle" data-onstyle="info" data-offstyle="light" data-size="small" id="msendtoggle"{$send_checked}{$disabled}>
</div>
<script type="text/javascript">
$(function($) {
	$('#msendtoggle').change(function() {
		var flag = $(this).prop('checked');
		$.ajax({
			type: "POST",
			url: "../ajax/mail_send_cg.php",
			data: {
				"my_employee_id":"{$ybase->my_employee_id}",
				"config":flag,
				"cate":$cate
			}
	        });
	});
});
</script>


<p class="h6 text-center">「相談・提案」済一覧</p>

<table class="table table-bordered table-hover table-striped table-sm" style="font-size:85%;">
  <thead>
    <tr align="center" class="table-secondary">
      <td scope="col">NO.</td>
      <td scope="col">内容</thd>
    </tr>
  </thead>
  <tbody>
HTML;
$tr_flag="";
$no=0;
for($i=0;$i<$num;$i++){
	list($q_consult_id,$q_parent_id,$q_type,$q_send_id,$q_return_id,$q_r_flag,$q_title,$q_mess,$q_add_date,$q_status) = pg_fetch_array($result,$i);
if($q_return_id == $ybase->my_employee_id){
	$your_id = $q_send_id;
	if($q_r_flag == "1"){
		$sql_up = "update consult set r_flag = '2' where consult_id = $q_consult_id and return_id = $ybase->my_employee_id";
		$result_up = $ybase->sql($conn,$sql_up);
	}
}else{
	$your_id = $q_return_id;
}
	$q_status = trim($q_status);
	$q_title = htmlspecialchars($q_title);
	$q_mess = trim($q_mess);
	$q_mess = ereg_replace("([\r|\n|\r\n]+)","<br>",$q_mess);
	if($q_type == "1"){

		if($tr_flag){
			$ybase->ST_PRI .= $add_ST_PRI;
			$ybase->ST_PRI .= "</td></tr>\n";
		}
		$tr_flag = "1";
		$no += 1;

$ybase->ST_PRI .= <<<HTML

<tr align="center" style="$style" id="trno{$q_consult_id}">
<td class="position-relative">
$no
</td>
<td class="position-relative">

<div class="card border border-dark" style="padding: 1px;margin-bottom:3px;">

<div class="card-body lead" style="padding: 2px 5px;background-color: #ddeedd;">
<div style="text-align:right;font-size:60%;margin:0 auto;"><span style="color:#ff5555">{$ybase->consult_rflag_list[$q_r_flag]}</span>【受信者】{$ybase->employee_name_list[$q_return_id]}
【日付】{$q_add_date}</div>
<div style="text-align:left;font-size:75%;margin:0 auto;">{$q_title}</div>
<hr style="margin: 0px;padding: 2px;">
<div style="font-size:75%;text-align:left;">{$q_mess}</div>
</div>
</div>

HTML;

}else{

if($q_send_id == $ybase->my_employee_id){
	if($q_r_flag == 1){
		$cr_flag = "未読";
	}else{
		$cr_flag = "既読";
	}
	$send_or_return = "[送信]{$cr_flag}";
}else{
	$send_or_return = "[受信]";
}
$ybase->ST_PRI .= <<<HTML

<div class="card border border-dark" style="padding: 2px 5px;margin:0px 0px 3px 50px;">
<div class="card-body bg-light lead" style="padding: 2px 5px;">
<div style="text-align:right;font-size:60%;margin:0 auto;">{$send_or_return}
【日付】{$q_add_date}</div>
<div style="text-align:left;font-size:75%;margin:0 auto;">{$q_title}</div>
<hr style="margin: 0px;padding: 2px;">
<div style="font-size:75%;text-align:left;">{$q_mess}</div>
</div>
</div>

HTML;

}

$add_ST_PRI = <<<HTML

<div style="text-align:right;">
<button type="button" class="btn btn-outline-secondary btn-sm" id="reform_display$i" value="$i" style="font-size:75%;">返信フォーム表示</button>
</div>

<div class="card border border-secondary" style="padding: 2px;margin: 5px 5px 5px 50px;font-size:80%;display:none;" id="recard$i">
<div class="card-body bg-light lead">
<form action="consult_re_ex.php" method="post" style="font-size:80%;" id="reform$i">
<input type="hidden" name="parent_id" value="$q_parent_id">
<input type="hidden" name="return_id" value="$your_id">
<span style="color:#ff0000;font-size:80%;display:none;" id="titledisplay">※タイトルを入力してください</span>
<div style="font-size:60%;text-align:left;margin-bottom: -7px;">
<label>【タイトル】</label>
</div>
<input type="text" name="new_retitle" class="form-control" id="reInput$i" value="Re:$q_title" maxlength="50" required>
<div style="font-size:60%;text-align:left;margin-bottom: -7px;">
<label>【返信内容】</label>
</div>
<textarea name="new_remess" class="form-control" rows="1" id="reTextarea$i" placeholder="" required></textarea>
<div style="text-align:right;">
<button type="submit" class="btn btn-outline-primary btn-sm" id="reform_submit$i">　返信　</button>
<button type="reset" class="btn btn-outline-primary btn-sm">クリア</button>
</div>
</form>
</div>
</div>

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
if($num){
	$ybase->ST_PRI .= $add_ST_PRI."</td></tr>";
}

$ybase->ST_PRI .= <<<HTML

</tbody>
</table>
$q_content
<div class="row">
<div class="col float-right">
<span class="float-right">
{$page}／{$allpage}
<a href="consult_top.php?page=$bf" class="btn btn-sm btn-outline-secondary{$addbfclass}">＜</a>
<a href="consult_top.php?page=$nx" class="btn btn-sm btn-outline-secondary{$addnxclass}">＞</a>
</span>
</div>
</div>
</div>

<!-- TAB2 -->
<div id="consult_tab2" class="tab-pane{$tab_active2}">
<div id="new_form">
<div class="card-body bg-light lead">
<div style="text-align:center;font-size:80%;margin:0 auto;">新規「相談・提案」</div><br>
<div style="font-size:70%;margin-left:50px;">仕事上の相談や業務上の提案等をここから受け付けています。<br>
※送信内容は担当者以外は見る事ができませんので、プライバシー等は保持されます。</div><br>
<div style="text-align:right;">現在の受付は「{$ybase->employee_name_list[$ybase->consult_receive_employee_id]}」さんです。</div>
<form action="consult_ex.php" method="post" style="font-size:80%;" id="newform1">
<input type="hidden" name="return_id" vaule="{$ybase->consult_receive_employee_id}">
<div class="form-group">
<label>【タイトル】<span style="color:#ff0000;font-size:80%;display:none;" id="titledisplay">※タイトルを入力してください</span></label>
<input type="text" name="new_title" class="form-control" id="Input1" placeholder="最大50字まで" maxlength="50" required>
</div>
<div class="form-group">
<label>【相談・提案内容】<span style="color:#ff0000;font-size:80%;display:none;" id="messdisplay">※相談・提案内容を入力してください</span></label>
<textarea name="new_mess" class="form-control" rows="5" id="Textarea1" required>
</textarea>
</div>
<button type="button" class="btn btn-outline-primary btn-sm" id="form1_submit"> 　送　信　 </button>
<button type="reset" class="btn btn-outline-primary btn-sm">クリア</button>
</form>
</div>
<hr>
</div>
</div>

<!-- TAB3 -->
<div id="consult_tab3" class="tab-pane{$tab_active3}">
<p></p>
<p class="h6 text-center">共有情報一覧</p>
<div style="font-size:75%;margin-left:50px;">過去の「相談・提案」の中から全社で共有できる情報をここでまとめてあります。<br>プライバシー情報は掲載しません。</div>
<p></p>
<div class="card border border-primary w-100 mx-auto">
<div class="text-left small">
《絞込み》

</div>
<div class="text-left small">
<table border="0" cellpadding="2" width="100%">
<tbody>
<form action="./consult_top.php" method="post" id="Filter_Form1">
<input type="hidden" name="select_tab" value="3">
<tr>
<td align="right">
<nobr>【カテゴリー】</nobr></td>
<td>
<select name="sel_com_cate" id="sel_com_cate">
<option value="">全て</option>
HTML;

foreach($ybase->consult_cate_list as $key => $val){
	if($sel_com_cate == $key){
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
<nobr>【　タ　ブ　】</nobr>
</td>
<td>
HTML;
$nxparam="";
foreach($ybase->consult_tab_list as $key => $val){
	if($sel_com_tabs[$key] == "1"){
		$addchecked = " checked";
		$bottoncss = " btn-success";
		$nxparam .= "&sel_com_tabs[{$key}]=1";
	}else{
		$addchecked = "";
		$bottoncss = " btn-outline-secondary";
	}
if(!$sel_com_tabs){
	$addchecked = " checked";
	$bottoncss = " btn-success";
}
$ybase->ST_PRI .= <<<HTML
<input type="checkbox" name="sel_com_tabs[$key]" id="comkeytab$key" class="nocheck" value="1"$addchecked><label for="comkeytab$key" class="btn{$bottoncss} btn-sm" id="comlabeltab$key" tabno="$key" style=\"font-size:60%;margin:1px;padding:1px;\">$val</label>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</td>

</tr>
</form>
</tbody>
</table>
</div>

</div>
<p></p>

<table class="table table-bordered table-hover table-striped table-sm" style="font-size:85%;">
  <thead>
    <tr align="center" class="table-secondary">
      <td scope="col">NO.</td>
      <td scope="col">内容</thd>
    </tr>
  </thead>
  <tbody>
HTML;
$tr_flag="";
$no=0;
for($i=$st2;$i<$end2;$i++){
	list($qc_common_id,$qc_consult_id,$qc_campaney_id,$qc_category_id,$qc_employee_id,$qc_tabs,$qc_title,$qc_mess,$qc_add_date,$qc_status) = pg_fetch_array($result2,$i);
	$qc_title = htmlspecialchars($qc_title);
	$qc_mess = trim($qc_mess);
	$qc_mess = ereg_replace("([\r|\n|\r\n]+)","<br>",$qc_mess);
	$no += 1;

$ybase->ST_PRI .= <<<HTML

<tr align="center">
<td class="position-relative">
$no
</td>
<td class="position-relative">

<div class="card border border-dark" style="padding: 1px;margin-bottom:3px;">

<div class="card-body lead" style="padding: 2px 5px;background-color: #fafafa;">
<div style="text-align:right;font-size:70%;margin:0 auto;">
<b>[{$ybase->consult_cate_list2[$qc_category_id]}]</b>
【日付】{$qc_add_date}
</div>
<div style="text-align:right;margin:0;">
HTML;
$letters = array('{','}');
$qc_tabs = str_replace($letters,"",$qc_tabs);
$tabs_list = "";
$tabs_arry = explode(",",$qc_tabs);
foreach($tabs_arry as $key => $val){
	$tabs_list .= "<botton class=\"btn btn-success btn-sm\" style=\"font-size:60%;margin:0;padding:0;\">{$ybase->consult_tab_list2[$val]}</botton>\n";
}
$ybase->ST_PRI .= <<<HTML
$tabs_list</div>
<div style="text-align:left;font-size:75%;margin:0 auto;">{$qc_title}</div>
<hr style="margin: 0px;padding: 2px;">
<div style="font-size:80%;text-align:left;">{$qc_mess}</div>
</div>
</div>
</td></tr>
HTML;



}



$bf2=$page2-1;
$nx2=$page2+1;
if($bf2 < 1){
	$addbfclass2=" disabled";
}else{
	$addbfclass2="";
}
if($nx2 > $allpage2){
	$addnxclass2=" disabled";
}else{
	$addnxclass2="";
}

$ybase->ST_PRI .= <<<HTML

</tbody>
</table>
$q_content2
<div class="row">
<div class="col float-right">
<span class="float-right">
{$page2}／{$allpage2}
<a href="consult_top.php?page2=$bf2&sel_com_cate=$sel_com_cate&select_tab=3{$nxparam}" class="btn btn-sm btn-outline-secondary{$addbfclass2}">＜</a>
<a href="consult_top.php?page2=$nx2&sel_com_cate=$sel_com_cate&select_tab=3{$nxparam}" class="btn btn-sm btn-outline-secondary{$addnxclass2}">＞</a>
</span>
</div>
</div>
</div>



</div>
</main>
<p></p>
</div>

<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>