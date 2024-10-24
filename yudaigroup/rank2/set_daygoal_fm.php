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
$tablerate = $ybase->mbscale(6);

if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}

$yy=substr($target_month,0,4);
$mm=substr($target_month,4,2);
$maxday = date("t",mktime(0,0,0,$mm,1,$yy));
/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$addsql = "month = $target_month and shop_id = $target_shop_id";
///////////////////////////////////////項目
$sql = "select bigitem_id,count(*) from telecom2_item where {$addsql} and status = '1' group by bigitem_id order by bigitem_id";

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

$need_cnt = $item_count * $maxday;
/////////////////////////目標確認
$sql = "select item_id,sum(goal_num) from telecom2_goal_group where {$addsql} and status = '1' group by item_id order by item_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("月間目標がまだ設定されていません。先に月間目標を設定してください。");
}
for($i=0;$i<$num;$i++){
	list($q_item_id,$q_goal_num) = pg_fetch_array($result,$i);
	$g_goal_num_lt[$q_item_id] = $q_goal_num;
}
/////////////////////////目標データ確認
$sql = "select item_id,day,dgoal_num from telecom2_goal_day where {$addsql} and status = '1' order by item_id,day";
$result = $ybase->sql($conn,$sql);
$day_num = pg_num_rows($result);
for($i=0;$i<$day_num;$i++){
	list($q_item_id,$q_day,$q_dgoal_num) = pg_fetch_array($result,$i);
	$dgoal_num_lt[$q_item_id][$q_day] = $q_dgoal_num;
}
if(!$day_num){
	$notice = "新しい月の為、各項目の目標値を設定してください。";
}elseif($day_num == $need_cnt){
	$notice = "変更がある場合は下記を変更してください。";
}else{
	$notice = "設定が完了していません。各項目の目標値を設定してください。";
}
///////////////////////////////////////////////////////////
$sql = "select bigitem_id,bigitem_name from telecom2_bigitem where {$addsql} and status = '1' order by order_num";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当月の項目がまだ設定されていません");
}

$ybase->title = "Y☆Judge-日別目標設定";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("日別目標設定");

$ybase->ST_PRI .= <<<HTML
<script src="./js/rank_update.js?$time"></script>
<input type="hidden" name="target_month" value="$target_month" id="target_month">
<input type="hidden" name="target_shop_id" value="$target_shop_id" id="target_shop_id">
<input type="hidden" name="scriptno" value="6" id="scriptno">
<script type="text/javascript">
$(function($) {
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
		for (var i=1; i<=$maxday; i++) {
			var new_val = $('#goal_num_' + itemid + '_' + i).val();
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
	});
});
</script>

<div class="container-fluid">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary" href="./set_top.php?target_month=$target_month&target_shop_id=$target_shop_id" role="button">設定TOPに戻る</a></div>
<h5 style="text-align:center;">【{$rank_section_name[$target_shop_id]} {$yy}年{$mm}月】 日別目標設定</h5>

<p>$notice</p>
HTML;

$ybase->ST_PRI .= <<<HTML

<div style="text-align:left;"><a class="btn btn-info btn-sm" href="./set_daygoal_auto.php?target_month=$target_month&target_shop_id=$target_shop_id" role="button">自動割振り</a></div>
HTML;

$ybase->ST_PRI .= <<<HTML
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
<tr bgcolor="#eeeeee" align="center">
<th colspan="2">項目</th>
<th>月間目標</th>
HTML;
for($i=1;$i<=$maxday;$i++){


$ybase->ST_PRI .= <<<HTML
	<th>$i</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
<th>合計</th>

</tr>
  </thead>
  <tbody>
HTML;
$kk=0;

for($i=0;$i<$num;$i++){
	list($q_bigitem_id,$q_bigitem_name) = pg_fetch_array($result,$i);

$sql = "select item_id,item_name,score from telecom2_item where {$addsql} and bigitem_id = $q_bigitem_id and status = '1' order by order_num";

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
<td rowspan="{$bigitem_cnt[$q_bigitem_id]}" style="vertical-align: middle;color:{$bigcharcolor[$i]};" bgcolor="{$bcolor[$i]}">
$str
</td>
HTML;
}else{
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="$hbgcolor">
HTML;
}
$ybase->ST_PRI .= <<<HTML
<td align="center">
$q_item_name
</td>
<td align="right">
<nobr>{$g_goal_num_lt[$q_item_id]}件</nobr>
</td>
HTML;
$goukei=0;
for($k=1;$k<=$maxday;$k++){
$num_value = $dgoal_num_lt[$q_item_id][$k];
$goukei += $num_value;
$ybase->ST_PRI .= <<<HTML
<td align="right" style="padding:1px;">
<input type="{$ybase->NUM_INPUT_TYPE}" name="goal_num_{$q_item_id}_{$k}" value="$num_value" id="goal_num_{$q_item_id}_{$k}" item_id="$q_item_id" goal_to="{$g_goal_num_lt[$q_item_id]}" style="width:3.2em;margin:0px;">
HTML;
}
$check_num = $g_goal_num_lt[$q_item_id] - $goukei;
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
<td align="right"><nobr><b id="gokei_num1_{$q_item_id}">{$goukei}</b>(<span id="gokei_num2_{$q_item_id}" style="color:{$notice_color};">$c_notice</span>)</nobr></td>
</tr>
HTML;

}

}

$ybase->ST_PRI .= <<<HTML
<tr>
<td colspan="2"></td>
<td></td>
<td></td>
HTML;

for($k=1;$k<=$maxday;$k++){
$ybase->ST_PRI .= <<<HTML
<td align="right"></td>
HTML;
}


$ybase->ST_PRI .= <<<HTML
<td align="right"></td></tr>
 </tbody>
</table>
</div>
<div style="text-align:right;"><a class="btn btn-secondary" href="./set_top.php?target_month=$target_month&target_shop_id=$target_shop_id" role="button">設定を完了して戻る</a></div>
</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>