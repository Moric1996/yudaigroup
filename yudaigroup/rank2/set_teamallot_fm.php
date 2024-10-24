<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/rank.inc');
include('./inc/rank_list.inc');

$ybase = new ybase();
$rank = new rank();
$ybase->session_get();
$tablerate = $ybase->mbscale(5);

if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}
$rank->unitname_make($target_shop_id,$target_month);

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
//////////////////////////////////////////データ確認
$sql = "select item_id,group_id,goal_num from telecom2_goal_group where {$addsql} and status = '1' order by item_id,group_id";

$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
if(!$num3){
	$last_month = date("Ym",mktime(0,0,0,$mm - 1,1,$yy));
	$lastyy=substr($last_month,0,4);
	$lastmm=substr($last_month,4,2);

	$sql = "insert into telecom2_goal_group (shop_id,item_id,month,group_id,goal_num,add_date,status) select shop_id,item_id,$target_month,group_id,goal_num,'now',status from telecom2_goal_group where month = $last_month and shop_id = $target_shop_id and status = '1'";
	$result = $ybase->sql($conn,$sql);
	$cmdtuples = pg_affected_rows($result);
	if($cmdtuples){
		$notice = "新しい月の為、前月の情報を引継ぎ設定しました。変更がある場合は下記を変更してください。";
	}else{
		$notice = "前月の設定がない為、前月の情報を引継ぎできませんでした。新しく設定してください。";
	}

	$sql = "select item_id,group_id,goal_num from telecom2_goal_group where {$addsql} and status = '1' order by item_id,group_id";
	$result3 = $ybase->sql($conn,$sql);
	$num3 = pg_num_rows($result3);

}else{
	$notice = "変更がある場合は下記を変更してください。";
}
$goal_num_itemg=array();
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_group_id,$q_goal_num) = pg_fetch_array($result3,$i);
	$goal_num_lt[$q_item_id][$q_group_id] = $q_goal_num;
	$goal_num_itemg[$q_item_id] += $q_goal_num;
}
/////////////////////////グループリスト
$sql = "select group_id,group_name,allot from telecom2_group where {$addsql} and status = '1' order by group_id";
$result4 = $ybase->sql($conn,$sql);
$num4 = pg_num_rows($result4);
$group_id_list = "";
$group_allot_list = "";
for($i=0;$i<$num4;$i++){
	list($q_group_id,$q_group_name,$q_allot) = pg_fetch_array($result4,$i);
	$group_name_lt[$q_group_id] = $q_group_name;
	$group_allot_lt[$q_group_id] = $q_allot;
	if($i > 0){
		$group_id_list .= ",";
		$group_allot_list .= ",";
	}
	$group_id_list .= "'"."$q_group_id"."'";
	$group_allot_list .= "'"."$q_allot"."'";
}


///////////////////////////////////////項目
$sql = "select bigitem_id,count(*) from telecom2_item where {$addsql} and status = '1' group by bigitem_id order by bigitem_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当月の項目がまだ設定されていません");
}
for($i=0;$i<$num;$i++){
	list($q_bigitem_id,$q_count) = pg_fetch_array($result,$i);
	$bigitem_cnt[$q_bigitem_id] = $q_count;
}

$sql = "select bigitem_id,bigitem_name from telecom2_bigitem where {$addsql} and status = '1' order by order_num";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当月の項目がまだ設定されていません");
}

$ybase->title = "Y☆Judge-月間目標・チーム配分設定";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("月間目標・チーム配分設定");

$ybase->ST_PRI .= <<<HTML
<script src="./js/rank_update.js?$time"></script>
<input type="hidden" name="target_month" value="$target_month" id="target_month">
<input type="hidden" name="target_shop_id" value="$target_shop_id" id="target_shop_id">
<input type="hidden" name="scriptno" value="4" id="scriptno">
<script type="text/javascript">
$(function($) {
	var g_list = new Array($group_id_list);
	var g_allot_list = new Array($group_allot_list);
	$("[id^=g_goal_num]").change(function() {
		var all_num = $(this).val();
		var itemid = $(this).attr('item_id')
		var gokei = 0;
		for (var i=0; i<$num4; i++) {
			var g_id = g_list[i];
			var allot_num = g_allot_list[i];
			if(i == ($num4 - 1)){
				var cg_val = all_num - gokei;
			}else{
				var cg_val = Math.round(all_num * allot_num / 100);
			}
			gokei += Number(cg_val);
			var check_num = all_num - gokei;
			if(check_num < 0){
				cg_val += check_num;
				gokei += check_num;
			}
			$('#goal_num_' + itemid + '_' + g_id).val(cg_val);
			$('#goal_num_' + itemid + '_' + g_id).ajaxchange_js();
		}
	});
	$("[id^=goal_num_]").change(function() {
		var itemid = $(this).attr('item_id')
		var gokei = 0;
		for (var i=0; i<$num4; i++) {
			var g_id = g_list[i];
			var new_val = $('#goal_num_' + itemid + '_' + g_id).val();
			gokei += Number(new_val);
		}
			$('#g_goal_num' + itemid).val(gokei);
	});
});
</script>

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary" href="./set_top.php?target_month=$target_month&target_shop_id=$target_shop_id" role="button">設定TOPに戻る</a></div>
<h5 style="text-align:center;">【{$rank_section_name[$target_shop_id]} {$yy}年{$mm}月】 <br>月間目標・チーム配分設定</h5>

<p>$notice</p>
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
<tr bgcolor="#eeeeee" align="center">
<th colspan="2" rowspan="2">項目</th>
<th rowspan="2">月間目標</th>
<th rowspan="2">配点</th>
HTML;
$nn=0;
foreach($group_name_lt as $key => $val){
$nn++;
$ybase->ST_PRI .= <<<HTML
	<th bgcolor="{$group_bgcolor[$nn]}">$val</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
<tr bgcolor="#eeeeee" align="center">
HTML;
$nn=0;
foreach($group_allot_lt as $key => $val){
$nn++;
$ybase->ST_PRI .= <<<HTML
<th bgcolor="{$group_bgcolor[$nn]}">{$val}%</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
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
$gvalue = $goal_num_itemg[$q_item_id];
$ybase->ST_PRI .= <<<HTML
<td align="center">
$q_item_name
</td>
<td align="right">
<input type="{$ybase->NUM_INPUT_TYPE}" name="g_goal_num$q_item_id" value="$gvalue" id="g_goal_num$q_item_id" item_id="$q_item_id" style="width:4em;">{$rank->unitname_list[$q_item_id]}
</td>
<td align="right">{$q_score}点</td>
HTML;
foreach($group_allot_lt as $key => $val){
$num_value = $goal_num_lt[$q_item_id][$key];
$ybase->ST_PRI .= <<<HTML
<td align="right">
<input type="{$ybase->NUM_INPUT_TYPE}" name="goal_num_{$q_item_id}_{$key}" value="$num_value" id="goal_num_{$q_item_id}_{$key}" item_id="$q_item_id" target_group_id="$key" style="width:4em;">
HTML;
}

$ybase->ST_PRI .= <<<HTML
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

foreach($group_allot_lt as $key => $val){
$ybase->ST_PRI .= <<<HTML
<td align="right"></td>
HTML;
}


$ybase->ST_PRI .= <<<HTML
</tr>
 </tbody>
</table>
</div>
<div style="text-align:center;"><a class="btn btn-danger" href="./set_teamallot_auto.php?$param&jump_script=set_person_top.php" role="button">個別配分設定へ</a></div>
<div style="text-align:right;"><a class="btn btn-secondary" href="./set_teamallot_auto.php?$param&jump_script=set_top.php" role="button">設定を完了して戻る</a></div>
</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>