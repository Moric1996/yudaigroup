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



if($ybase->my_admin_auth != "1"){
	$ybase->error("権限がありません");

}
$kensu=10;
$kensu2=10;

switch($select_tab){
	case 2:
		$tab_active1 = "";
		$tab_active2 = " active";
		$tab_active3 = "";
		$tab_active4 = "";
		break;
	case 3:
		$tab_active1 = "";
		$tab_active2 = "";
		$tab_active3 = " active";
		$tab_active4 = "";
		break;
	case 4:
		$tab_active1 = "";
		$tab_active2 = "";
		$tab_active3 = "";
		$tab_active4 = " active";
		break;
	default:
		$tab_active1 = " active";
		$tab_active2 = "";
		$tab_active3 = "";
		$tab_active4 = "";
		break;
}

/////////////////////////////////////////

$conn = $ybase->connect();

if($common_consult_id){
$sql = "select title,mess from consult where consult_id = $common_consult_id and status <> '0'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	list($new_common_title,$new_common_mess) = pg_fetch_array($result,0);
}
}

$sql = "select email from employee_list where employee_id = $ybase->my_employee_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$q_email = pg_fetch_result($result,0,0);
	$q_email = trim($q_email);
}
if(!$q_email){
	$sendcheck_mess = "<a href=\"../user/pass_cg.php\">メールでの通知にはメールアドレスの登録が必要です</a>";
}

$sql = "select consult_id from consult where return_id = $ybase->my_employee_id and type = '1' and status <> '0' order by parent_id desc,type,add_date";
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
	$q_content = "<br><div style=\"text-align:center;text-size:110%;\">{$ybase->my_name}さんが受けた相談・提案はありません</div><br>";
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

/////////////////////////投稿完了後の表示
if($new_mess_sent == 1){
	$new_mess_dis = "<div style=\"text-align:center;text-size:110%;color:red;\">送信完了!</div>";
	$new_mess_sent = "";
}else{
	$new_mess_dis = "";
}
////////////////////////

$ybase->title = "相談・提案(管理画面)";
$addtitle = "　<a href=\"./consult_top.php\" class=\"btn btn-secondary btn-sm\">一般「相談・提案」画面へ</a>";

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
		$('#flagcard' + ii).css("display", "none");
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

$('[id^=flag_change]').click(function(){
	var iii = $(this).val();
	if ($('#flagcard' + iii).css('display') == 'none') {
		$('#flagcard' + iii).css("display", "block");
		$(this).text('対応状況変更隠す');
		$('#recard' + iii).css("display", "none");
	}else{
		$('#flagcard' + iii).css("display", "none");
		$(this).text('対応状況変更');
	}
});
$('[id^=change_val]').click(function(){
	var iiii = $(this).val();
	var totext = $(this).text();
	var cgflag = $(this).attr('cgvalue');
	var consult_id = $(this).attr('consult_id');
	$('#disflag' + iiii).text(totext);
	$('#flagcard' + iiii).css("display", "none");
	$('#flag_change' + iiii).text('対応状況変更');
	$.ajax('ajaxrflagchange.php',
	{
	type: 'post',
	data: {
		consult_id: $(this).attr('consult_id'),
		cgflag: $(this).attr('cgvalue')
	},
	dataType: 'text'
	})
});
$('[id^=labeltab]').click(function(){
	var tabnumber = $(this).attr('tabno');
	if ($('#keytab' + tabnumber).prop('checked')) {
		$(this).removeClass('btn-success');
		$(this).addClass('btn-outline-secondary');
	}else{
		$(this).removeClass('btn-outline-secondary');
		$(this).addClass('btn-success');
	}
});
$("#sel_com_cate,[id^=comkeytab]").change(function(){
	$("#Filter_Form1").submit();
});
$('[id^=common_del]').click(function(){
	var del_title = $(this).attr('title');
	var url = $(this).attr('delhref');
	if(!confirm(del_title + 'を本当に削除しますか？')){
		return false;
	}else{
		location.href = url;
    }
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
      <a href="#consult_tab1" class="nav-link{$tab_active1}" data-toggle="tab">「相談・提案」管理</a>
    </li>
    <li class="nav-item">
      <a href="#consult_tab2" class="nav-link{$tab_active2}" data-toggle="tab">新規共有情報投稿</a>
    </li>
    <li class="nav-item">
      <a href="#consult_tab3" class="nav-link{$tab_active3}" data-toggle="tab">共有情報管理</a>
    </li>
    <li class="nav-item">
      <a href="#consult_tab4" class="nav-link{$tab_active4}" data-toggle="tab">設定管理</a>
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

</div>
<p class="h6 text-center">「相談・提案」管理</p>

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
		$q_r_flag = "2";
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

<div class="card-body lead" style="padding: 2px 5px;background-color: #ddddee;">
<div style="text-align:right;font-size:60%;margin:0 auto;"><span style="color:#ff5555" id="disflag$no">{$ybase->consult_rflag_list[$q_r_flag]}</span>【送信者】{$ybase->employee_name_list[$q_send_id]}
【日付】{$q_add_date}</div>
<div style="text-align:left;font-size:75%;margin:0 auto;">{$q_title}</div>
<hr style="margin: 0px;padding: 2px;">
<div style="font-size:75%;text-align:left;">{$q_mess}</div>
</div>
</div>

HTML;

}else{

if($q_return_id == $ybase->my_employee_id){
	$send_or_return = "[受信]";
}else{
	if($q_r_flag == 1){
		$cr_flag = "未読";
	}else{
		$cr_flag = "既読";
	}
	$send_or_return = "[送信]{$cr_flag}";
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
<button type="button" class="btn btn-outline-secondary btn-sm" id="reform_display$i" value="$i" style="font-size:75%;color:#111111;">返信フォーム表示</button> 
<button type="button" class="btn btn-outline-secondary btn-sm" id="flag_change$no" value="$no" style="font-size:75%;color:#111111;">対応状況変更</button> 
<a href="./consult_manage.php?select_tab=2&common_consult_id=$q_parent_id" type="button" class="btn btn-outline-secondary btn-sm" id="common_info$no" value="$i" style="font-size:75%;color:#111111;">共有情報へ</a>
</div>

<div class="card border border-secondary" style="padding: 2px;margin: 5px 5px 5px 50px;font-size:80%;display:none;" id="recard$i">
<div class="card-body bg-light lead">
<form action="consult_re_ex.php" method="post" style="font-size:80%;" id="reform$i">
<input type="hidden" name="manage_flag" value="1">
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

<div class="card border border-secondary" style="padding: 1px;margin: 5px 5px 5px 5px;font-size:80%;display:none;" id="flagcard$no">
<div class="card-body bg-light lead">
<div style="text-align:center;">
変更する対応状態を選択してください。<br>
HTML;
foreach($ybase->consult_rflag_list as $key => $val){
if($key < 2){continue;}
$add_ST_PRI .= <<<HTML
<button type="button" class="btn btn-info btn-sm" id="change_val$i$key" value="$no" cgvalue="$key" consult_id="$q_parent_id" style="font-size:75%;">$val</button> 
HTML;

}

$add_ST_PRI .= <<<HTML

</div>
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
<div style="text-align:center;font-size:80%;margin:0 auto;">新規共有情報投稿</div><br>
<div style="font-size:70%;margin-left:50px;">「相談・提案」の中から全社で共有する情報の場合はここから投稿してください。<br>
投稿した内容は全従業員で共有されます。</div><br>
<form action="consult_common_ex.php" method="post" style="font-size:80%;" id="commonform1">
<input type="hidden" name="common_consult_id" vaule="$common_consult_id">
<div class="form-group">
<label>【タイトル】</label>
<input type="text" name="new_common_title" value="$new_common_title" class="form-control" id="Input1" placeholder="最大50字まで" maxlength="50" required>
</div>
<div class="form-group">
<label for="Select1">【カテゴリー】</label>
<select name="new_common_category" class="form-control" id="Select1" required>
<option value="">選択してください</option>
HTML;
foreach($ybase->consult_cate_list as $key => $val){
if($key == $new_common_cate){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML

<option value="$key"$selected>$val</option>

HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</div>
<div class="form-group">
<label>【共有内容】</label>
<textarea name="new_common_mess" class="form-control" rows="5" id="Textarea1" required>$new_common_mess
</textarea>
</div>
<label>【タブ選択(検索キーワードとして)】</label>
<div class="card border border-secondary" style="padding: 2px;margin: 5px 5px 5px 5px;font-size:80%;" id="tabcard1">
<div class="card-body bg-light lead" style="padding: 5px;">

<div class="form-group">
HTML;
foreach($ybase->consult_tab_list as $key => $val){
if($keytab[$key] == "1"){
	$checked = " checked";
	$bottoncss = " btn-success";
}else{
	$checked = "";
	$bottoncss = " btn-outline-secondary";
}

$ybase->ST_PRI .= <<<HTML
<input type="checkbox" name="keytab[$key]" id="keytab$key" class="nocheck" value="1"$checked><label for="keytab$key" class="btn{$bottoncss} btn-sm" id="labeltab$key" tabno="$key">$val</label>

HTML;
}
$ybase->ST_PRI .= <<<HTML

</div>
</div>
</div>

<br>
<button type="submit" class="btn btn-outline-primary btn-sm" id="commonform1_submit"> 　送　信　 </button>
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
<form action="./consult_manage.php" method="post" id="Filter_Form1">
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
<td align="right" valign="top">
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
<input type="checkbox" name="sel_com_tabs[$key]" id="comkeytab$key" class="nocheck" value="1"$addchecked><label for="comkeytab$key" class="btn{$bottoncss} btn-sm" id="comlabeltab$key" style=\"font-size:60%;margin:1px;padding:1px;\">$val</label>
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

<div style="text-align:right;">
<a href="#" delhref="./consult_common_del.php?page2=$page2&sel_com_cate=$sel_com_cate&select_tab=3{$nxparam}&del_common_id=$qc_common_id" type="button" class="btn btn-outline-secondary btn-sm" id="common_del$qc_common_id" title="$qc_title" style="font-size:70%;color:#111111;">削除</a>
</div>

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

<!-- TAB4 -->
<div id="consult_tab4" class="tab-pane{$tab_active4}">

<p></p>
<div class="card border border-info w-100 mx-auto" style="padding:2px;">
【「相談・提案」受付担当者管理】
<hr style="margin: 0px;padding: 2px;">
<div class="text-left small">
　従業員からの「相談・提案」を受ける担当者を設定します。
</div>
<p></p>
　現在の担当者は {$ybase->employee_name_list[$ybase->consult_receive_employee_id]} さんです。
<p></p>
<div class="text-left small">
<form action="./consult_return_cg.php" method="post" id="recg_Form1">
<input type="hidden" name="select_tab" value="4">
　《担当者》
<select name="admin_emp_id" id="admin_emp_id">
HTML;
$ybase->employee_name_list = array();
$ybase->make_employee_list("1","1");

foreach($ybase->employee_name_list as $key => $val){
	if($ybase->consult_receive_employee_id == $key){
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
<input type="submit" value="変更">
</form>
</div>
<p></p>
</div>
<p></p>

<div class="card border border-info w-100 mx-auto" style="padding:2px;">
【共有情報「カテゴリー」設定管理】
<hr style="margin: 0px;padding: 2px;">
<div class="text-left small">
　共有情報の「カテゴリー」を設定します。<br>
</div>
<p></p>
<div class="text-left small" style="margin-left:10px;">
《現在のカテゴリー一覧》
<table border="1">
HTML;

foreach($ybase->consult_cate_list as $key => $val){
$ybase->ST_PRI .= <<<HTML
<tr><td>$val</td>
<td><button type="button" class="btn btn-secondary btn-sm" id="com_cate_del$key" value="$key" style="font-size:75%;">削除</button></td></tr>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</table>
<p></p>
《カテゴリー新規追加》
<form action="./consult_category_in.php" method="post">
<input type="hidden" name="select_tab" value="4">
<input type="text" name="new_com_category" size="40" maxlength="50" required>
<input type="submit" value="追加">
</form>
<p></p>
</div>
<p></p>
</div>
<p></p>

<div class="card border border-info w-100 mx-auto" style="padding:2px;">
【共有情報「タブ(キーワード)」設定管理】
<hr style="margin: 0px;padding: 2px;">
<div class="text-left small">
　共有情報の「タブ」を設定します。<br>
</div>
<p></p>
<div class="text-left small" style="margin-left:10px;">
《現在のタブ一覧》
<table border="1">
HTML;

foreach($ybase->consult_tab_list as $key => $val){
$ybase->ST_PRI .= <<<HTML
<tr><td>$val</td>
<td><button type="button" class="btn btn-secondary btn-sm" id="com_tab_del$key" value="$key" style="font-size:75%;">削除</button></td></tr>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</table>
<p></p>
《タブ新規追加》
<form action="./consult_tab_in.php" method="post">
<input type="hidden" name="select_tab" value="4">
<input type="text" name="new_com_tab" size="30" maxlength="40" required>
<input type="submit" value="追加">
</form>
<p></p>
</div>
<p></p>
</div>
<p></p>


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