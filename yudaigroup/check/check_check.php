<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/check.inc');
include('./inc/check_list.inc');

$ybase = new ybase();
$check = new check();
$ybase->session_get();

$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;

$category_list = $check->category_make();
$item_list = $check->item_make();
$ybase->make_now_section_list();
/////////////////////////////////////////
if(!$ybase->my_position_class || ($ybase->my_position_class > 40)){
	$check->protect = 1;
	if(!$t_shop_id){
		$t_shop_id = $ybase->my_section_id;
	}
	if(($t_shop_id < 100) || ($t_shop_id > 999)){
		$t_shop_id = "";
	}
}

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$param = "t_shop_id=$t_shop_id";
$addsql = "section_id = $t_shop_id";
//////////////////////////////////////////
if($t_shop_id){
if($target_ckaction_id){
	$sql = "select ckaction_id,ckset_id,employee_id,action_date,add_date,status,com from ck_check_action where ckaction_id = $target_ckaction_id and section_id = '$t_shop_id' and status > '0'";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$ybase->error("該当するデータがありません");
	}
	list($target_ckaction_id,$q_ckset_id,$q_employee_id,$q_action_date,$q_add_date,$q_status,$q_addcom) = pg_fetch_array($result,0);

	if($q_status == "2"){
		$ybase->error("該当日のシートはまだ完了していません");
	}elseif($q_status != "1"){
		$ybase->error("データに問題があります。管理者までご連絡ください。");
	}
	
}else{
	$sql = "select ckaction_id,ckset_id,employee_id,action_date,add_date,status,com from ck_check_action where section_id = '$t_shop_id' and status = '1' order by add_date desc limit 1";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
	list($target_ckaction_id,$q_ckset_id,$q_employee_id,$q_action_date,$q_add_date,$q_status,$q_addcom) = pg_fetch_array($result,0);
//		$ybase->error("該当するデータがありません。ERROR_CODE:78008");
	}else{
		$target_ckaction_id = 0;
	}
}
$tar_employee_name = "入力:[".$ybase->employee_name_list[$q_employee_id]."]";

$subject_list_arr = $check->check_set_make($t_shop_id,$q_ckset_id);
$subject_list_cate_arr = $check->check_set_item_make($subject_list_arr);

$delscript = <<<HTML

$(function(){
	$("[id^=photoa]").longpress(function (e) {
		var delurl = $(this).attr('delhref');

		if(!confirm('画像を本当に削除しますか？')){
			return false;
		}else{
			location.href = delurl;
		}
	},
        function () {
		var newurl = $(this).attr('newhref');
		var name = 'new photo window';
		window.open(newurl, name,'width=1000,height=800');
		return false;
        },
		1500
	);
});

HTML;
/////////////////////データ取得
$sql = "select ckaction_list_id,item_id,action,com,photo from ck_check_action_list where ckaction_id = $target_ckaction_id and section_id = '$t_shop_id' and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
for($i=0;$i<$num;$i++){
	list($q_ckaction_list_id,$q_item_id,$q_action,$q_com,$q_photo) = pg_fetch_array($result,$i);
	$q_action = trim($q_action);
	$q_com = trim($q_com);
	$q_photo = trim($q_photo);
	if($q_photo){
		$photo_arr = json_decode($q_photo,true);
		$no=count($photo_arr);
	}else{
		$photo_arr = array();
		$no=0;
	}
	$t_ckaction_list_id[$q_item_id] = $q_ckaction_list_id;
	$t_action_num[$q_item_id] = $q_action;
	$t_action_com[$q_item_id] = $q_com;
	$t_action_photo[$q_item_id] = $no;
	$t_action_photo_json[$q_item_id] = $q_photo;
}
//////////////////
/////////////////////前回データ取得
$sql = "select ckaction_id from ck_check_action where ckaction_id < $target_ckaction_id and section_id = '$t_shop_id' and status = '1' order by ckaction_id desc limit 2";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$bf_ckaction_id = pg_fetch_result($result,0,0);
	$sql = "select ckaction_list_id,item_id,action,com from ck_check_action_list where ckaction_id = $bf_ckaction_id and section_id = '$t_shop_id' and status = '1'";
	$result2 = $ybase->sql($conn,$sql);
	$num2 = pg_num_rows($result2);
	for($i=0;$i<$num2;$i++){
		list($q_ckaction_list_id,$q_item_id,$q_action,$q_com) = pg_fetch_array($result2,$i);
		$q_action = trim($q_action);
		$q_com = trim($q_com);
		$bf_action_num[$q_item_id] = $q_action;
		$bf_action_com[$q_item_id] = $q_com;
	}
	$bf_disable="";
	if($num > 1){
	$bf2_ckaction_id = pg_fetch_result($result,1,0);
	$sql = "select ckaction_list_id,item_id,action,com from ck_check_action_list where ckaction_id = $bf2_ckaction_id and section_id = '$t_shop_id' and status = '1'";
	$result2 = $ybase->sql($conn,$sql);
	$num2 = pg_num_rows($result2);
	for($i=0;$i<$num2;$i++){
		list($q_ckaction_list_id,$q_item_id,$q_action,$q_com) = pg_fetch_array($result2,$i);
		$q_action = trim($q_action);
		$q_com = trim($q_com);
		$bf2_action_num[$q_item_id] = $q_action;
		$bf2_action_com[$q_item_id] = $q_com;
	}
	}
}else{
	$bf_disable=" disabled";
}
/////////次データあるか
$sql = "select ckaction_id from ck_check_action where ckaction_id > $target_ckaction_id and section_id = '$t_shop_id' and status = '1' order by ckaction_id limit 1";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$nx_ckaction_id = pg_fetch_result($result,0,0);
	$nx_disable="";
}else{
		$nx_disable=" disabled";
}
	$linkdisabled="";
}else{
	$bf_disable=" disabled";
	$nx_disable=" disabled";
	$indisabled=" disabled";
	$linkdisabled=" disabled";
	foreach($category_list as $key => $val){
		$subject_list_cate_arr[$key]=array();
	}
}
$jsallotarr = "0";
foreach($category_list as $key => $val){
	if(!isset($subject_list_cate_arr[$key])){
		$subject_list_cate_arr[$key] = array();
	}
	foreach($subject_list_cate_arr[$key] as $key2 => $val2){
		$jsallotarr .= ",".$check->allot_by_subject[$key2];
	}
}
//////////////////リプライ
$repcom = array();

if($target_ckaction_id){
$sql = "select reply_id,ckaction_list_id,reply,employee_id,to_char(add_date,'YYYY/MM/DD HH24:MI'),to_char(up_date,'YYYY/MM/DD HH24:MI') from ck_reply where ckaction_id = $target_ckaction_id and section_id = '$t_shop_id' and status = '1' order by ckaction_list_id,up_date";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
		for($i=0;$i<$num;$i++){
			list($q_reply_id,$rep_ckaction_list_id,$rep_reply,$rep_employee_id,$rep_add_date,$rep_up_date) = pg_fetch_array($result,$i);
			$repcom[$rep_ckaction_list_id] .= "[$rep_add_date ".$ybase->employee_name_list[$rep_employee_id]."] ".$rep_reply;
			if($rep_employee_id == $ybase->my_employee_id){
				$repcom[$rep_ckaction_list_id] .= " <a href=\"#!\" id=\"repdel{$q_reply_id}\" reply_id=\"$q_reply_id\" class=\"badge badge-danger\" style=\"color:#ffffff;font-weight:100;\">削除</a>";
			}
			$repcom[$rep_ckaction_list_id] .= "<br>";
		}
	}
}
//////////////////ログ確認&insert
$LOG_PRN="";

if($target_ckaction_id){
$sql = "select viewlog_id,employee_id,to_char(add_date,'YYYY/MM/DD HH24:MI'),to_char(up_date,'YYYY/MM/DD HH24:MI'),status from ck_viewlog where ckaction_id = $target_ckaction_id and section_id = '$t_shop_id' and status = '1' order by up_date desc";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
		for($i=0;$i<$num;$i++){
			list($log_viewlog_id,$log_employee_id,$log_add_date,$log_up_date,$log_status) = pg_fetch_array($result,$i);
			$LOG_PRN .= "<tr align=\"center\"><td>".$ybase->employee_name_list[$log_employee_id]."</td><td>$log_add_date</td><td>$log_up_date</td></tr>\n";
		}
		$sql = "update ck_viewlog set up_date = 'now' where viewlog_id = $log_viewlog_id";
	}else{
		$sql = "insert into ck_viewlog(viewlog_id,ckaction_id,section_id,employee_id,add_date,up_date,status) values (nextval('ck_viewlog_id_seq'),$target_ckaction_id,'$t_shop_id',$ybase->my_employee_id ,'now','now','1')";
	}
	$result = $ybase->sql($conn,$sql);
}
////////////////////

$ybase->title = "店舗チェック";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("店舗チェック確認");
$ybase->ST_PRI .= $check->check_menu("7");

$ybase->ST_PRI .= <<<HTML
<script type="text/javascript" src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script type="text/javascript" src="./js/jquery.longpress.js"></script>
<script type="text/javascript">
$(function(){
	$('select').change(function(){
		$("#Form1").submit();
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
	$('[id^=repiclink]').click(function(){
		var ckid = $(this).attr('ckaction_list_id');
		if ($('#repspan' + ckid).css('display') == 'none') {
			$('#repspan' + ckid).css("display", "block");
			$('#repicon' + ckid).removeClass("ui-icon-comment");
			$('#repicon' + ckid).addClass("ui-icon-triangle-1-n");
		}else{
			$('#repspan' + ckid).css("display", "none");
			$('#repicon' + ckid).removeClass("ui-icon-triangle-1-n");
			$('#repicon' + ckid).addClass("ui-icon-comment");
		}
	});
});
$(function() {
	$('textarea').change(function() {
	var script_no = $('#scriptno').val();
	var checkid = $(this).attr('id');
		if(checkid == 'target_day_select'){
		return;
	}
	var com = $(this).val();
	var ckid = $(this).attr('ckaction_list_id');
	$.ajax('ajaxchange' + script_no + '.php',
	{
	type: 'post',
	data: {
		var_name: $(this).attr('name'),
		var_val: $(this).val(),
		target_ckaction_list_id: $(this).attr('ckaction_list_id'),
		t_ckaction_id: $('#t_ckaction_id').val(),
		target_ckaction_id: $('#target_ckaction_id').val(),
		target_shop_id: $('#target_shop_id').val()
	},
	dataType: 'text'
	}
	)
	// 成功時にはページに結果を反映
	.done(function(data) {
		if(data == 'NG'){
		window.alert('エラーが発生しました\\n今入力したデータは反映されていません');
		}

		if(com == ''){
			stopPropagation();
		}
		$('#reply' + ckid).val('');
		var now = new Date();
		var yy = now.getFullYear();
		var mm = now.getMonth() + 1;
		var dd = now.getDate();
		var h = now.getHours();
		var m = now.getMinutes();
		var mm = ('0' + mm).slice(-2);
		var dd = ('0' + dd).slice(-2);
		var h = ('0' + h).slice(-2);
		var m = ('0' + m).slice(-2);
		var nowdate = yy + '/' + mm + '/' + dd + ' ' + h + ':' + m;
		var htmltxt = '[' + nowdate + ' {$ybase->my_name}] ' + com + ' <a href="#!" id="repdel' + data + '" reply_id="' + data + '" class="badge badge-danger" style="color:#ffffff;font-weight:100;">削除</a><br>';
		$('#repcom' + ckid).append(htmltxt);
		$('#repspan' + ckid).css("display", "none");
		$('#repicon' + ckid).removeClass("ui-icon-triangle-1-n");
		$('#repicon' + ckid).addClass("ui-icon-comment");
		console.log(data);
	})
	 // 失敗時には、その旨をダイアログ表示
	.fail(function(data) {
		window.alert('エラーが発生しました\\n今入力したデータは反映されていません');
	});

	});
});

$(function(){
	$('[id^=reply]').change(function(){
	});
});
$(function(){
	$(document).on('click',"[id^=repdel]", function(){
		var replyid = $(this).attr('reply_id');
		if(!confirm('指定の返信を削除してもよいですか？')){
			return false;
		}else{
			location.href = './reply_del.php?target_ckaction_id={$target_ckaction_id}&t_shop_id={$t_shop_id}&reply_id=' + replyid;
		}
	});
});
$delscript
</script>
<input type="hidden" name="target_ckaction_id" value="$target_ckaction_id" id="target_ckaction_id">
<input type="hidden" name="target_shop_id" value="$t_shop_id" id="target_shop_id">
<input type="hidden" name="scriptno" value="4" id="scriptno">

<div class="container">
<h5 style="text-align:center;">店舗チェック確認</h5>

<p></p>
<form action="./check_check.php" method="post" id="Form1">

<div class="row">
<div class="col-sm-8 offset-sm-2" style="text-align:center;">
HTML;
$auth_edit = 1;

$ybase->ST_PRI .= <<<HTML
【対象店舗】<select name="t_shop_id">
<option value="">選択してください</option>
HTML;
foreach($ybase->section_list as $key => $val){
if($key < 100){continue;}
if($key > 1000){continue;}
if(preg_match("/^3[0-9]{2}$/",$key)){continue;}
if($key == $t_shop_id){
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
HTML;
$q_action_date = str_replace("-","/",$q_action_date);
$ybase->ST_PRI .= <<<HTML
</div>
</div>
</form>

<p></p>


<p></p>
<table border="0" width="100%">
<tr><td>
<form action="check_check.php" method="get" id="dayselectfrom">
<input type="hidden" name="t_ckaction_id" value="$target_ckaction_id" id="t_ckaction_id">
<input type="hidden" name="t_shop_id" value="$t_shop_id" id="t_shop_id">
</form>

<div class="h6">$q_action_date</div>
</td>
<td align="right">
<nobr>
<a href="check_check.php?$param&target_ckaction_id=$bf_ckaction_id" class="btn btn-sm btn-outline-secondary{$bf_disable}">前回</a>
<a href="check_check.php?$param&target_ckaction_id=$nx_ckaction_id" class="btn btn-sm btn-outline-secondary{$nx_disable}">次回</a>
</nobr>

</td></tr>
</table>
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:75%;">
<tbody>
HTML;

$allot_cnt=array();
$t_cnt=array();
$bf_cnt=array();
$bf2_cnt=array();
$allot_sum=0;
$t_sum=0;
$bf_sum=0;
$bf2_sum=0;
$forallotno=0;

foreach($category_list as $key => $val){
if(!$subject_list_cate_arr[$key]){
	continue;
}
$ybase->ST_PRI .= <<<HTML
<tr align="center" bgcolor="#cccccc">
<th colspan="2" width="30%">
$val
</th>
<th>
配点
</th>
<th valign="middle" style="left-padding:0px;right-padding:0px;margin:0px;">
前々回
</th>
<th valign="middle" style="left-padding:0px;right-padding:0px;margin:0px;">
前回
</th>
<th>
点数
</th>
<th>
コメント
</th>
</tr>
HTML;
$kk=0;
	foreach($subject_list_cate_arr[$key] as $key2 => $val2){

$kk++;
if($kk%2 == 0){
	$hbgcolor = "#fafafa";
}else{
	$hbgcolor = "#ffffff";
}
$analysis_rows = intval(strlen($t_action_com[$key2])/30) + 1;
$analysis_cr = substr_count($t_action_com[$key2],"\n") + 1;
if($analysis_rows < $analysis_cr){
	$analysis_rows = $analysis_cr;
}
$allot_cnt[$key] += $check->allot_by_subject[$key2];
$t_cnt[$key] += $t_action_num[$key2];
$bf_cnt[$key] += $bf_action_num[$key2];
$bf2_cnt[$key] += $bf2_action_num[$key2];
$forallotno++;

if($t_action_photo[$key2]){
	$photo_dis = "";
	$photo_arr = json_decode($t_action_photo_json[$key2],true);
	foreach($photo_arr as $pkey => $pval){
		$photo_dis .= "<a href=\"#!\" newhref=\"./photo_view.php?t_shop_id={$t_shop_id}&t_ckaction_list_id={$t_ckaction_list_id[$key2]}&pno={$pkey}\" delhref=\"./photo_del.php?t_shop_id={$t_shop_id}&t_ckaction_list_id={$t_ckaction_list_id[$key2]}&pno={$pkey}&target_ckaction_id=$target_ckaction_id\" id=\"photoa{$t_ckaction_list_id[$key2]}\"><img src=\"./view.php?t_shop_id={$t_shop_id}&t_ckaction_list_id={$t_ckaction_list_id[$key2]}&pno={$pkey}&thum=35\" width=\"35\" border=\"0\"></a>";
	}
}else{
	$photo_dis = "";
}
$d_ckaction_list_id = $t_ckaction_list_id[$key2];

$ybase->ST_PRI .= <<<HTML
<td align="center">
$kk
</td>
<td>
$val2
</td>
<td align="center" style="width:3rem;">
<span  style="font-size:150%;color:#777777;">
{$check->allot_by_subject[$key2]}
</span>
<td align="center" style="width:2rem;">
<span  style="font-size:150%;color:#555555;">
{$bf2_action_num[$key2]}
</span>
</td>
<td align="center" style="width:2rem;">
<span  style="font-size:150%;color:#555555;">
{$bf_action_num[$key2]}
</span>
</td>
<td align="center" style="width:3rem;">
<span id="a_num{$t_ckaction_list_id[$key2]}" style="font-size:150%;font-weight:bold;">
{$t_action_num[$key2]}
</span>
</td>

<td><a name="li{$t_ckaction_list_id[$key2]}"></a>
<table class="table-borderless m-0" width="100%">
<tr><td style="padding:0px;margin:0px;">
{$t_action_com[$key2]}

</td>
<td style="padding:0px;margin:0px;" align="right">
<a href="#!" id="repiclink{$t_ckaction_list_id[$key2]}" ckaction_list_id="{$t_ckaction_list_id[$key2]}" title="新規返信">
<span class="ui-icon ui-icon-comment" style="margin-left:0px;padding-left:0px;" id="repicon{$t_ckaction_list_id[$key2]}"></span></a>
</td>
</tr>
<tr><td style="padding:0px;margin:0px;" colspan="2">
$photo_dis
</td></tr>
<tr><td style="padding:0px;margin:0px;">
<hr style="padding:0px;margin:0px;">
<span id="repcom{$t_ckaction_list_id[$key2]}">
$repcom[$d_ckaction_list_id]
</span id>
</td><td></td></tr>


<tr><td style="padding:0px;margin:0px;" colspan="2">
<span id="repspan{$t_ckaction_list_id[$key2]}" style="display:none;">
[新規返信]<br>
<textarea name="reply{$t_ckaction_list_id[$key2]}" id="reply{$t_ckaction_list_id[$key2]}" class="form-control m-0" rows="1" ckaction_list_id="{$t_ckaction_list_id[$key2]}">
</textarea>
</span>
</td></tr>

</table>
</td>
</tr>
HTML;

}

$ybase->ST_PRI .= <<<HTML
<tr bgcolor="#fafafa">
<td colspan="2" align="center">
小計
</td>
<td align="center" style="width:3rem;text-algin:center;">
$allot_cnt[$key]
</td>
<td align="center" style="width:2rem;text-algin:center;">
$bf2_cnt[$key]
</td>
<td align="center" style="width:2rem;text-algin:center;">
$bf_cnt[$key]
</td>
<td align="center" style="width:3rem;text-algin:center;">
<span id="tsyoukei{$key}">
$t_cnt[$key]
</span>
</td>
<td>
</td>
</tr>
HTML;
$allot_sum += $allot_cnt[$key];
$bf_sum += $bf_cnt[$key];
$bf2_sum += $bf2_cnt[$key];
$t_sum += $t_cnt[$key];

}

$analysis_rows = intval(strlen($q_addcom)/50) + 1;
$analysis_cr = substr_count($q_addcom,"\n") + 1;
if($analysis_rows < $analysis_cr){
	$analysis_rows = $analysis_cr;
}

$ybase->ST_PRI .= <<<HTML
<tr bgcolor="#eaeaea">
<td colspan="2" align="center">
合計
</td>
<td align="center" style="width:3rem;text-algin:center;">
$allot_sum
</td>
<td align="center" style="width:2rem;text-algin:center;">
$bf2_sum
</td>
<td align="center" style="width:2rem;text-algin:center;">
$bf_sum
</td>
<td align="center" style="width:3rem;text-algin:center;">
<span id="tgoukei">
$t_sum
</span>
</td>

<td>
$tar_employee_name
</td>
</tr>

<tr>
<td colspan="7" align="center">
<br>【追記事項】

{$q_addcom}
</td>
</tr>

 </tbody>
</table>
</div>


<div class="table-responsive">
<table class="table table-sm table-borderless bm-5" id="table1" width="100%" style="font-size:80%;padding:0px;margin:0px;">

<thead>
<tr align="center">
<td colspan="3">【閲覧ログ】</td>
</tr>
<tr align="center">
<th>氏名</th>
<th>初回閲覧日時</th>
<th>最終閲覧日時</th>
</tr>

</thead>
<tbody id="tablebody1">

$LOG_PRN

</tbody>
</table>
</div>
</div>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>