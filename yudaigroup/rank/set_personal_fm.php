<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/rank_list.inc');

$ybase = new ybase();
$ybase->session_get();
$tablerate = $ybase->mbscale(5);

if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}
if(!preg_match("/^[0-9]+$/",$target_group_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10823");
}
$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;

$yy=substr($target_month,0,4);
$mm=substr($target_month,4,2);

/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
$param = "target_month=$target_month&target_shop_id=$target_shop_id";
//////////////////////////////////////////条件
$addsql = "month = $target_month and shop_id = $target_shop_id";
///////////////////////////////////////項目
$sql = "select bigitem_id,count(*) from telecom_item where {$addsql} and status = '1' group by bigitem_id order by bigitem_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当月の項目がまだ設定されていません");
}
$item_count = 0;
for($i=0;$i<$num;$i++){
	list($q_bigitem_id,$q_count) = pg_fetch_array($result,$i);
	$bigitem_cnt[$q_bigitem_id] = $q_count;
	$item_count += $q_count;
}

//////////////////////////////////////////グループ名取得
$sql = "select group_name from telecom_group where {$addsql} and group_id = $target_group_id and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("チームが設定されていません。先にチームの設定をしてください。");
}else{
	$group_name = pg_fetch_result($result,0,0);
}
//////////////////////////////////////////グループ目標取得
$sql = "select item_id,goal_num from telecom_goal_group where {$addsql} and group_id = $target_group_id and status = '1' order by item_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("チーム目標が設定されていません。先にチームの目標設定をしてください。");
}
for($i=0;$i<$num;$i++){
	list($q_item_id,$q_goal_num) = pg_fetch_array($result,$i);
	$goal_num_lt[$q_item_id] = $q_goal_num;
}

//////////////////////////////////////////itemscore取得
$score_list = "";
$sql = "select item_id,score from telecom_item where {$addsql} and status = '1' order by order_num";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("項目が設定されていません。先に項目の目標設定をしてください。");
}
for($i=0;$i<$num;$i++){
	list($q_item_id,$q_score) = pg_fetch_array($result,$i);
	$item_score_lt[$q_item_id] = $q_score;
	if($i > 0){
		$score_list .= ",";
	}
	$score_list .= "$q_item_id:$q_score";
}

/////////////////////////グループ構成員リスト＆目標データ確認
$sql = "select group_const_id,employee_id from telecom_group_const where {$addsql} and group_id = $target_group_id and status = '1' order by group_id,group_const_id";
$result = $ybase->sql($conn,$sql);
$const_num = pg_num_rows($result);
if(!$const_num){
	$ybase->error("チームの設定がされていません。先にチームの設定をしてください。");
}
$emp_id_list = "";
$notice = "";
for($i=0;$i<$const_num;$i++){
	list($q_group_const_id,$q_employee_id) = pg_fetch_array($result,$i);
	$group_emp_lt[$q_group_const_id] = $q_employee_id;
	if($i > 0){
		$emp_id_list .= ",";
	}
	$emp_id_list .= "'"."$q_employee_id"."'";

	$sql3 = "select item_id,goal_num from telecom_goal where {$addsql} and employee_id = $q_employee_id and status = '1' order by item_id";
	$result3 = $ybase->sql($conn,$sql3);
	$num3 = pg_num_rows($result3);
	if(!$num3){
		$last_month = date("Ym",mktime(0,0,0,$mm - 1,1,$yy));
		$lastyy=substr($last_month,0,4);
		$lastmm=substr($last_month,4,2);
		$sql4 = "insert into telecom_goal (shop_id,item_id,month,employee_id,goal_num,add_date,status) select shop_id,item_id,$target_month,employee_id,goal_num,'now',status from telecom_goal where month = $last_month and shop_id = $target_shop_id and employee_id = $q_employee_id and status = '1'";
		$result4 = $ybase->sql($conn,$sql4);
		$cmdtuples = pg_affected_rows($result4);
		if($cmdtuples){
			$notice .= $sec_employee_list[$q_employee_id]."=>前月の情報を引継ぎました。";
		}else{
			$notice .= $sec_employee_list[$q_employee_id]."=>新しく設定してください。";
		}
		$sql3 = "select item_id,goal_num from telecom_goal where {$addsql} and employee_id = $q_employee_id and status = '1' order by item_id";
		$result3 = $ybase->sql($conn,$sql3);
		$num3 = pg_num_rows($result3);
	}elseif($num3 == $item_count){
		$notice .= $sec_employee_list[$q_employee_id]."=>変更できます。";
	}else{
		$notice .= $sec_employee_list[$q_employee_id]."=>未設定項目があります。";
	}
	for($k=0;$k<$num3;$k++){
		list($q_item_id,$q_goal_num) = pg_fetch_array($result3,$k);
		$emp_goal_num_lt[$q_item_id][$q_employee_id] = $q_goal_num;
	}
}
///////////////////////////////////////////////////////////
$sql = "select bigitem_id,bigitem_name from telecom_bigitem where {$addsql} and status = '1' order by order_num";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当月の項目がまだ設定されていません");
}

$ybase->title = "Y☆Rank-個別配分設定";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("個別配分設定");

$ybase->ST_PRI .= <<<HTML
<script src="./js/rank_update.js?$time"></script>
<input type="hidden" name="target_month" value="$target_month" id="target_month">
<input type="hidden" name="target_shop_id" value="$target_shop_id" id="target_shop_id">
<input type="hidden" name="scriptno" value="5" id="scriptno">
<script type="text/javascript">
$(function($) {
	var e_list = new Array($emp_id_list);
	var scr_list = {{$score_list}};
	$("[id^=goal_num_]").change(function() {
		var text  = $(this).val();
		var hen = text.replace(/[０-９]/g,function(s){
		return String.fromCharCode(s.charCodeAt(0)-0xFEE0);
		});
		$(this).val(hen);
		this.value = this.value.replace(/[^0-9]+/i,'');
		var itemid = $(this).attr('item_id');
		var g_goal = $(this).attr('goal_to');
		var gokei = 0;
		for (var i=0; i<$const_num; i++) {
			var emp_id = e_list[i];
			var new_val = $('#goal_num_' + itemid + '_' + emp_id).val();
			gokei += Number(new_val);
		}
		var check_num = gokei - g_goal;
		if(check_num == 0){
			var c_notice = 'OK';
			var c_color = "#000000";
		}
		if(check_num > 0){
			var c_notice = check_num + '件超過';
			var c_color = "#9900ff";
		}
		if(check_num < 0){
			check_num = check_num * -1;
			var c_notice = check_num + '件不足';
			var c_color = "#ff0000";
		}
			$('#gokei_num1_' + itemid).text(gokei);
			$('#gokei_num2_' + itemid).text(c_notice);
			$('#gokei_num2_' + itemid).css("color",c_color);
			var gokei_num = new Array();
			var gokei_score = new Array();
		$.each(e_list, function(index_e, value_empid) {
			gokei_num[value_empid] = 0;
			gokei_score[value_empid] = 0;
		});
		var all_gokei_num = 0;
		var all_gokei_score = 0;
		$.each(e_list, function(index_e, value_empid) {
		$.each(scr_list, function(index_itemid, value_score) {
			var get_val = $('#goal_num_' + index_itemid + '_' + value_empid).val();
			gokei_num[value_empid] += Number(get_val);
			gokei_score[value_empid] += Number(get_val) * value_score;
		});
			$('#gk_num_' + value_empid).text(gokei_num[value_empid]);
			$('#gk_score_' + value_empid).text(gokei_score[value_empid]);
			all_gokei_num += gokei_num[value_empid];
			all_gokei_score += gokei_score[value_empid];
		});
		$('#all_gk_num').text(all_gokei_num);
		$('#all_gk_score').text(all_gokei_score);

	});
});
</script>

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary" href="./set_person_top.php?target_month=$target_month&target_shop_id=$target_shop_id" role="button">個別配分設定TOPに戻る</a></div>
<h5 style="text-align:center;">【{$rank_section_name[$target_shop_id]} {$yy}年{$mm}月】 {$group_name} 個別配分設定</h5>

<p>$notice</p>
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
<tr bgcolor="#eeeeee" align="center">
<th colspan="2">項目</th>
<th>配点</th>
<th>チーム<br>月間目標</th>
HTML;
foreach($group_emp_lt as $key => $val){
$e_name = $sec_employee_list[$val];
if(strlen($e_name) > 20){
	list($aa,$bb) = explode(" ",$e_name);
	$e_name = $aa;
}

$ybase->ST_PRI .= <<<HTML
	<th>$e_name</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
<th>合計</th>

</tr>
  </thead>
  <tbody>
HTML;
$g_num = array();
$g_score = array();
$kk=0;
for($i=0;$i<$num;$i++){
	list($q_bigitem_id,$q_bigitem_name) = pg_fetch_array($result,$i);

$sql = "select item_id,item_name,score from telecom_item where {$addsql} and bigitem_id = $q_bigitem_id and status = '1' order by order_num";

$result2 = $ybase->sql($conn,$sql);
$num2 = pg_num_rows($result2);
if(!$num2){
	$ybase->error("該当月がまだ設定されていません");
}
for($ii=0;$ii<$num2;$ii++){
$kk++;
if($kk%2 == 0){
	$hbgcolor = "#fafafa";
}else{
	$hbgcolor = "#ffffff";
}
	list($q_item_id,$q_item_name,$q_score) = pg_fetch_array($result2,$ii);

if($ii == 0){
	$charlist = mb_str_split($q_bigitem_name);
	$str="";
	foreach($charlist as $key => $val){
		if($key != 0){
			$str .= "<br>";
		}
		if($val == 'ー'){
			$val = "｜";
		}
			$val = mb_convert_kana($val,"KVA","UTF-8");
			$str .= $val;
	}
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="$hbgcolor">
<td rowspan="{$bigitem_cnt[$q_bigitem_id]}" style="vertical-align: middle;" bgcolor="{$bcolor[$i]}">
$str
</td>
HTML;
}else{
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="$hbgcolor">
HTML;
}
$gvalue = $goal_num_lt[$q_item_id];
$ybase->ST_PRI .= <<<HTML
<td align="center">
$q_item_name
</td>
<td align="right">{$q_score}点</td>
<td align="right">
{$goal_num_lt[$q_item_id]}件
</td>
HTML;
$goukei=0;
foreach($group_emp_lt as $key => $val){
$num_value = $emp_goal_num_lt[$q_item_id][$val];
$goukei += $num_value;
$g_num[$val] += $num_value;
$g_score[$val] += $num_value * $q_score;

$ybase->ST_PRI .= <<<HTML
<td align="right" style="padding:1px;">
<input type="{$ybase->NUM_INPUT_TYPE}" name="goal_num_{$q_item_id}_{$val}" value="$num_value" id="goal_num_{$q_item_id}_{$val}" item_id="$q_item_id" target_employee_id="$val" goal_to="{$goal_num_lt[$q_item_id]}" style="width:4em;margin:0px;">
HTML;
}
$check_num = $goal_num_lt[$q_item_id] - $goukei;
if($check_num == 0){
	$c_notice = "OK";
	$notice_color = "#000000";
}elseif($check_num > 0){
	$c_notice = "{$check_num}件不足";
	$notice_color = "#ff0000";
}elseif($check_num < 0){
	$check_num *= -1;
	$c_notice = "{$check_num}件超過";
	$notice_color = "#9900ff";
}

$ybase->ST_PRI .= <<<HTML
<td align="right"><b id="gokei_num1_{$q_item_id}">{$goukei}</b>(<span id="gokei_num2_{$q_item_id}" style="color:{$notice_color};">$c_notice</span>)</td>
</tr>
HTML;

}

}

$ybase->ST_PRI .= <<<HTML
<tr bgcolor="#ededed">
<td colspan="2"></td>
<td></td>
<td>計</td>
HTML;
$all_g_num = 0;
$all_g_score = 0;
foreach($group_emp_lt as $key => $val){
$all_g_num += $g_num[$val];
$all_g_score += $g_score[$val];

$ybase->ST_PRI .= <<<HTML
<td align="right"><span id="gk_num_{$val}">{$g_num[$val]}</span>(<span id="gk_score_{$val}">{$g_score[$val]}</span>点)</td>
HTML;
}


$ybase->ST_PRI .= <<<HTML
<td align="right"><span id="all_gk_num">{$all_g_num}</span>(<span id="all_gk_score">{$all_g_score}</span>点)</td></tr>
 </tbody>
</table>
</div>
<div style="text-align:right;"><a class="btn btn-secondary" href="./set_personal_auto.php?$param&target_group_id=$target_group_id&jump_script=set_person_top.php" role="button">設定を完了して戻る</a></div>
</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>