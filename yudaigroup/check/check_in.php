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
}else{
	$sql = "select ckaction_id,ckset_id,employee_id,action_date,add_date,status,com from ck_check_action where section_id = '$t_shop_id' and status = '2' order by add_date desc";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
		$add_com = "前回完了していないデータがありました。完了する場合は終了ボタンを押してください。";
	}else{
		$sql = "select ckset_id,array_to_json(subject_list),array_to_json(allot_list) from ck_check_set where section_id = '$t_shop_id' and last_flag = 1 and status = '1' order by add_date desc limit 1";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$ybase->error("まだ設定が完了していません。まず、設定を完了してください。ERROR_CODE:10002");
		}
		list($q_ckset_id,$q_subject_list,$q_allot_list) = pg_fetch_array($result,0);
		$subject_list_arr = json_decode($q_subject_list);
		$allot_list_arr = json_decode($q_allot_list);
		$sql = "select nextval('ck_check_action_id_seq')";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$ybase->error("データベースエラーです。ERROR_CODE:78006");
		}
		$target_ckaction_id = pg_fetch_result($result,0,0);
		$sql = "insert into ck_check_action values($target_ckaction_id,'$t_shop_id',$q_ckset_id,$ybase->my_employee_id,'now','now','2','')";
		$result = $ybase->sql($conn,$sql);
		foreach($subject_list_arr as $key => $val){
			$sql = "insert into ck_check_action_list values(nextval('ck_check_action_list_id_seq'),$target_ckaction_id,'$t_shop_id',$val,null,'','now','1')";
			$result = $ybase->sql($conn,$sql);
		}
	$sql = "select ckaction_id,ckset_id,employee_id,action_date,add_date,status,com from ck_check_action where ckaction_id = $target_ckaction_id and section_id = '$t_shop_id' and status > '0'";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$ybase->error("該当するデータがありません。ERROR_CODE:78008");
	}
	}
}
list($target_ckaction_id,$q_ckset_id,$q_employee_id,$q_action_date,$q_add_date,$q_status,$q_addcom) = pg_fetch_array($result,0);
if(($q_status == '1')&&($editok != '1')){
	$indisabled = " disabled";
	$delscript = "";
	$tar_employee_name = "入力:[".$ybase->employee_name_list[$q_employee_id]."]";

}else{
	$indisabled = "";
	$tar_employee_name = "";


}
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
$sql = "select ckaction_id from ck_check_action where ckaction_id < $target_ckaction_id and section_id = '$t_shop_id' and status > '0' order by ckaction_id desc limit 2";
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
$sql = "select ckaction_id from ck_check_action where ckaction_id > $target_ckaction_id and section_id = '$t_shop_id' and status > '0' order by ckaction_id limit 1";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$nx_ckaction_id = pg_fetch_result($result,0,0);
	$nx_disable="";
}else{
	if($q_status == '1'){
		$nx_disable="";
		$nx_ckaction_id = "";
	}else{
		$nx_disable=" disabled";
	}
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
//////////////////


$ybase->title = "店舗チェック";

$ybase->HTMLheader2();
$ybase->ST_PRI .= $ybase->header_pri("店舗チェック入力");
$ybase->ST_PRI .= $check->check_menu("1");

$ybase->ST_PRI .= <<<HTML
<script type="text/javascript" src="./js/check_update.js?102"></script>
<script type="text/javascript" src="./js/jquery.longpress.js"></script>
<script type="text/javascript">
$(function(){
	$('select').change(function(){
		$("#Form1").submit();
	});
});
$(function(){
	$("[id^=file]").change(function(){
		$(this).parent().parent().submit();
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
	$("[id^=comm]").focus(function(){
		this.style.width = "18rem";
	}).blur(function(){
		this.style.width = "12.5rem";
	});
});
$(function(){
	$("[id^=plus]").click(function(){
		var allotlist = [$jsallotarr];
		var ca_list_id = $(this).attr('ckaction_list_id');
		var now_val = $('#action' + ca_list_id).val();
		var cate = $('#action' + ca_list_id).attr('catego');
		var allot = $('#action' + ca_list_id).attr('allotno');
		var syoukei = $('#tsyoukei' + cate).text();
		var goukei = $('#tgoukei').text();
		if(!now_val){
			now_val = 0;
		}
		if(!syoukei){
			syoukei = 0;
		}
		if(!goukei){
			goukei = 0;
		}
		var nx_val = +now_val + 1;
		if(nx_val > allotlist[allot]){
			return false;
		}
		var nx_syoukei = +syoukei + 1;
		var nx_goukei = +goukei + 1;
		$('#action' + ca_list_id).val(nx_val);
		$('#action' + ca_list_id).ajaxchange_js();
		$('#a_num' + ca_list_id).text(nx_val);
		$('#tsyoukei' + cate).text(nx_syoukei);
		$('#tgoukei').text(nx_goukei);

	});
	$("[id^=minus]").click(function(){
		var ca_list_id = $(this).attr('ckaction_list_id');
		var now_val = $('#action' + ca_list_id).val();
		var cate = $('#action' + ca_list_id).attr('catego');
		var syoukei = $('#tsyoukei' + cate).text();
		var goukei = $('#tgoukei').text();
		if(!now_val){
			now_val = 0;
		}
		if(!syoukei){
			syoukei = 0;
		}
		if(!goukei){
			goukei = 0;
		}
		var nx_val = +now_val - 1;
		if(nx_val < 0){
			return false;
		}
		var nx_syoukei = +syoukei - 1;
		var nx_goukei = +goukei - 1;
		$('#action' + ca_list_id).val(nx_val);
		$('#action' + ca_list_id).ajaxchange_js();
		$('#a_num' + ca_list_id).text(nx_val);
		$('#tsyoukei' + cate).text(nx_syoukei);
		$('#tgoukei').text(nx_goukei);
	});
});
$(function(){
	$("[id^=sheetdel]").click(function(){
		var delurl = $(this).attr('delhref');
		if(!confirm('このシートを削除してもよいですか？')){
			return false;
		}else{
			location.href = delurl;
		}
	});
});
$delscript
</script>
<input type="hidden" name="target_ckaction_id" value="$target_ckaction_id" id="target_ckaction_id">
<input type="hidden" name="target_shop_id" value="$t_shop_id" id="target_shop_id">
<input type="hidden" name="scriptno" value="1" id="scriptno">

<div class="container">
<h5 style="text-align:center;">店舗チェック入力</h5>

<p></p>
<form action="./check_in.php" method="post" id="Form1">

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

$ybase->ST_PRI .= <<<HTML
</div>
</div>
</form>

<p></p>


<p></p>
<table border="0" width="100%">
<tr><td>
<form action="check_in.php" method="get" id="dayselectfrom">
<input type="hidden" name="t_ckaction_id" value="$target_ckaction_id" id="t_ckaction_id">
<input type="hidden" name="t_shop_id" value="$t_shop_id" id="t_shop_id">

<input type="date" name="t_action_date" value="$q_action_date" id="t_action_date"{$indisabled}>
</form>
</td>
<td align="right">
<nobr>
<a href="check_in.php?$param&target_ckaction_id=$bf_ckaction_id" class="btn btn-sm btn-outline-secondary{$bf_disable}">前回</a>
<a href="check_in.php?$param&target_ckaction_id=$nx_ckaction_id" class="btn btn-sm btn-outline-secondary{$nx_disable}">次回</a>
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
<th colspan="2">
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
<th colspan="2">
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
		$photo_dis .= "<a href=\"#\" newhref=\"./photo_view.php?t_shop_id={$t_shop_id}&t_ckaction_list_id={$t_ckaction_list_id[$key2]}&pno={$pkey}\" delhref=\"./photo_del.php?t_shop_id={$t_shop_id}&t_ckaction_list_id={$t_ckaction_list_id[$key2]}&pno={$pkey}&target_ckaction_id=$target_ckaction_id\" id=\"photoa{$t_ckaction_list_id[$key2]}\"><img src=\"./view.php?t_shop_id={$t_shop_id}&t_ckaction_list_id={$t_ckaction_list_id[$key2]}&pno={$pkey}&thum=35\" width=\"35\" border=\"0\"></a>";
	}
}else{
	$photo_dis = "";
}

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
<td style="width:4rem;">

<nobr>
<input type="hidden" name="action{$t_ckaction_list_id[$key2]}" value="{$t_action_num[$key2]}" id="action{$t_ckaction_list_id[$key2]}" ckaction_list_id="{$t_ckaction_list_id[$key2]}" catego="$key" allotno="$forallotno">
<button type="button" class="btn btn-info rounded-circle p-0" style="width:1.8rem;height:1.8rem;" id="minus{$t_ckaction_list_id[$key2]}" ckaction_list_id="{$t_ckaction_list_id[$key2]}"{$indisabled}>－</button>
<button type="button" class="btn btn-info rounded-circle p-0" style="width:1.8rem;height:1.8rem;" id="plus{$t_ckaction_list_id[$key2]}" ckaction_list_id="{$t_ckaction_list_id[$key2]}"{$indisabled}>＋</button>
</nobr>
</td>
<td><a name="li{$t_ckaction_list_id[$key2]}"></a>
<table class="table-borderless m-0">
<tr><td style="padding:0px;margin:0px;">
<textarea name="comm{$t_ckaction_list_id[$key2]}" id="comm{$t_ckaction_list_id[$key2]}" class="form-control m-0" rows="$analysis_rows" cols="20" ckaction_list_id="{$t_ckaction_list_id[$key2]}"{$indisabled}>{$t_action_com[$key2]}
</textarea>
</td>
<td style="padding:0px;margin:0px;">
<form action="./check_in_file.php" method="post" id="fupform_{$t_ckaction_list_id[$key2]}" enctype="multipart/form-data" class="m-0">
<input type="hidden" name="target_ckaction_id" value="$target_ckaction_id">
<input type="hidden" name="t_shop_id" value="$t_shop_id">
<input type="hidden" name="t_ckaction_list_id" value="{$t_ckaction_list_id[$key2]}">
<input type="hidden" name="t_item_id" value="$key2">
<label for="file{$t_ckaction_list_id[$key2]}">
<img src="./image/picadd.png" alt="画像登録" style="cursor: pointer;" width="35">
<input id="file{$t_ckaction_list_id[$key2]}" type="file" name="uploadfile[]" style="display:none" multiple="multiple" accept="image/*"{$indisabled}>
</label>
</form>
</td></tr>
<tr><td colspan="2" style="padding:0px;margin:0px;">
$photo_dis
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
</td>
<td>
$tar_employee_name
</td>
</tr>

<tr>
<td colspan="8" align="center">
<br>【追記事項】

<textarea name="addcom" id="addcomm" class="form-control" rows="$analysis_rows" {$indisabled}>
{$q_addcom}
</textarea>
</td>
</tr>

 </tbody>
</table>
</div>
<div class="text-center">
HTML;
if($t_shop_id){
if($q_status == '2'){
$ybase->ST_PRI .= <<<HTML
<a class="btn btn-danger btn-lg text-center" href="./check_cg.php?$param&target_ckaction_id=$target_ckaction_id&stat=1" role="button">　完　了　</a>
HTML;
}elseif($editok == 1){
$ybase->ST_PRI .= <<<HTML
<a class="btn btn-danger btn-lg text-center" href="./check_in.php?$param&target_ckaction_id=$target_ckaction_id" role="button">　終　了　</a>
HTML;

}else{
$ybase->ST_PRI .= <<<HTML
<a class="btn btn-danger btn-lg text-center" href="./check_in.php?$param&target_ckaction_id=$target_ckaction_id&editok=1" role="button">　編　集　</a>
HTML;
}

$ybase->ST_PRI .= <<<HTML
　<a class="btn btn-secondary btn-lg text-center" href="#" delhref="./check_del.php?$param&target_ckaction_id=$target_ckaction_id" role="button" id="sheetdel">　削　除　</a>
HTML;

}
$ybase->ST_PRI .= <<<HTML
</div>
</div>
</div>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>