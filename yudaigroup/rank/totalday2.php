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
$tablerate = $ybase->mbscale(6);

if(!preg_match("/^[0-9]+$/",$t_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20001");
}
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20002");
}
$rank->unitname_make($t_shop_id,$t_month);
$YEAR = substr($t_month,0,4);
$MONTH = intval(substr($t_month,4,2));
$nowYYMM = date("Ym");
if(!$target_day){
	$nowday = date("j");
	if($nowYYMM > $t_month){
		$target_day = date("t",mktime(0,0,0,$MONTH,1,$YEAR));
	}else{
		$target_day = date("j",mktime(0,0,0,$MONTH,$nowday,$YEAR));
	}
	if($target_day < 1){
		$target_day = 1;
	}
}
$maxdays = date("t",mktime(0,0,0,$MONTH,$target_day,$YEAR));
$nissin = round($target_day/$maxdays*100);

$bf = $target_day - 1;
$nx = $target_day + 1;
if($bf < 1){
	$bf_disable=" disabled";
}else{
	$bf_disable="";
}
if($nx > $maxdays){
	$nx_disable=" disabled";
}else{
	$nx_disable="";
}

$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;
foreach($sec_employee_list as $key => $val){
if(strlen($val) > 9){
	list($aa,$bb) = explode(" ",$val);
	$sec_employee_list[$key] = $aa;
}
}
/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$param = "t_month=$t_month&t_shop_id=$t_shop_id";
$addsql = "month = $t_month and shop_id = $t_shop_id and status = '1'";
//////////////////////////////////////////
/////////////////////////グループリスト
$sql = "select group_id,group_name,allot from telecom_group where {$addsql} and status = '1' order by group_id";
$result = $ybase->sql($conn,$sql);
$group_num = pg_num_rows($result);
if(!$group_num){
	$ybase->error("チームの設定がされていません。先にチームの設定をしてください。");
}
for($i=0;$i<$group_num;$i++){
	list($q_group_id,$q_group_name,$q_allot) = pg_fetch_array($result,$i);
	$group_name_lt[$q_group_id] = $q_group_name;
}
/////////////////////////グループ構成員リスト＆目標データ確認
$sql = "select group_const_id,group_id,employee_id,short_name from telecom_group_const where {$addsql} and status = '1' order by group_id,group_const_id";
$result = $ybase->sql($conn,$sql);
$const_num = pg_num_rows($result);
if(!$const_num){
	$ybase->error("チームの設定がされていません。先にチームの設定をしてください。");
}
for($i=0;$i<$const_num;$i++){
	list($q_group_const_id,$q_group_id,$q_employee_id,$q_short_name) = pg_fetch_array($result,$i);
	$group_emp_lt[$q_group_id][$q_employee_id] = $q_short_name;
	$group_cnt[$q_group_id]++;
}
/////////////////////////////////////////

$sql = "select bigitem_id,count(*) from telecom_item where {$addsql} group by bigitem_id order by bigitem_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当月がまだ設定されていません");
}
for($i=0;$i<$num;$i++){
	list($q_bigitem_id,$q_count) = pg_fetch_array($result,$i);
	$bigitem_cnt[$q_bigitem_id] = $q_count;
}

$sql = "select bigitem_id,bigitem_name from telecom_bigitem where {$addsql} order by order_num";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当月がまだ設定されていません");
}

$ybase->title = "Y☆Rank-日別集計表(個別)";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("日別集計表(個別)");

//itemscore
$sql = "select item_id,score from telecom_item where {$addsql} order by order_num";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_score) = pg_fetch_array($result3,$i);
	$item_score[$q_itemid] = $q_score;
}
//当日目標
$sql = "select item_id,dgoal_num from telecom_goal_day where {$addsql} and day = $target_day order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_dgoal_num) = pg_fetch_array($result3,$i);
	$dgoalnum_arr[$q_itemid] = $q_dgoal_num;
}
//当日実績
$sql = "select item_id,sum(action_num),sum(dgoal_num) from telecom_action where {$addsql} and day = $target_day group by item_id order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_action_num,$q_dgoal_num) = pg_fetch_array($result3,$i);
	if(!$q_action_num){
		$q_action_num = "";
	}
	$actnum_arr[$q_itemid] = $q_action_num;
}

//当日実績個人
$sql = "select item_id,employee_id,action_num from telecom_action where {$addsql} and day = $target_day order by item_id,employee_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_employee_id,$q_action_num) = pg_fetch_array($result3,$i);
	if(!$q_action_num){
		$q_action_num = "";
	}
	$per_actnum_arr[$q_itemid][$q_employee_id] = $q_action_num;
}

$all_act_score = 0;
$all_goal_score = 0;
//月間実績
$sql = "select item_id,sum(action_num) from telecom_action where {$addsql} and day <= $target_day group by item_id order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_action_num) = pg_fetch_array($result3,$i);
	$all_act_score += $q_action_num * $item_score[$q_itemid];
}
//月間目標
$sql = "select item_id,sum(goal_num) from telecom_goal_group where {$addsql} group by item_id order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_dgoal_num) = pg_fetch_array($result3,$i);
	$all_goal_score += $q_dgoal_num * $item_score[$q_itemid];
}
if($all_goal_score){
	$reach_rate = round(($all_act_score / $all_goal_score) * 100,1);
}else{
	$reach_rate = 0.0;
}
$all_goal_score = number_format($all_goal_score);
$all_act_score = number_format($all_act_score);


$ybase->ST_PRI .= <<<HTML
<script type="text/javascript">
$(function(){
	$('select').change(function(){
		$("#dayselectfrom").submit();
	});
});
</script>

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./rank_top.php?$param" role="button">Y☆RankTOPに戻る</a></div>
<p></p>
<h5 style="text-align:center;">【{$rank_section_name[$t_shop_id]} {$YEAR}年{$MONTH}月】日別集計表(個別)</h5>

<p></p>
<table border="0" width="100%"><tr>
<form action="totalday2.php" method="get" id="dayselectfrom">
<input type="hidden" name="t_month" value="$t_month">
<input type="hidden" name="t_shop_id" value="$t_shop_id">
<td>
<nobr><b>
<select name="target_day">
HTML;
for($i=1;$i<=$maxdays;$i++){
if($target_day == $i){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$i"$selected>$i</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML

</select>
日</b> (日数進捗{$nissin}%)　</nobr>
<nobr><a href="totalday2.php?$param&target_day=$bf" class="btn btn-sm btn-outline-secondary{$bf_disable}">前日</a>
<a href="totalday2.php?$param&target_day=$nx" class="btn btn-sm btn-outline-secondary{$nx_disable}">翌日</a>
&nbsp;&nbsp;&nbsp;<a href="totalday1.php?$param&target_day=$target_day" class="btn btn-sm btn-outline-info">全体</a>
</nobr>
</td>
</form>
<td><div style="text-align:right;">
<nobr>目標:<span style="color:red;font-size:150%;">{$all_goal_score}</span>点</nobr><br><nobr>　獲得:<span style="color:red;font-size:150%;">{$all_act_score}</span>点</nobr><br><nobr>　達成:<span style="color:red;font-size:150%;">{$reach_rate}</span>%</nobr></div>
</td></tr>
</table>

<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
<tr bgcolor="#cccccc" align="center">
<th rowspan="2"></th>
<th rowspan="2"></th>
<th rowspan="2">当日目標</th>
<th rowspan="2">当日実績</th>
HTML;
$nn=0;
foreach($group_name_lt as $key => $val){
$nn++;
$ybase->ST_PRI .= <<<HTML
<th colspan="{$group_cnt[$key]}" bgcolor="{$group_bgcolor[$nn]}">$val</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
HTML;
$nn=0;

foreach($group_name_lt as $key => $val){
$nn++;
	foreach($group_emp_lt[$key] as $key2 => $val2){
$ybase->ST_PRI .= <<<HTML
<th bgcolor="{$group_bgcolor[$nn]}">$val2</th>
HTML;
	}
}
$ybase->ST_PRI .= <<<HTML
</tr>
  </thead>
  <tbody>
HTML;
///////////////////////////////////
$kk=0;

for($i=0;$i<$num;$i++){
	list($q_bigitem_id,$q_bigitem_name) = pg_fetch_array($result,$i);

$sql = "select item_id,item_name,score from telecom_item where {$addsql} and bigitem_id = $q_bigitem_id order by order_num";

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
$ybase->ST_PRI .= <<<HTML
<td align="center">
$q_item_name
</td>
<td align="right">{$dgoalnum_arr[$q_item_id]}{$rank->unitname_list[$q_item_id]}</td>
<td align="right">{$actnum_arr[$q_item_id]}{$rank->unitname_list[$q_item_id]}</td>
HTML;
foreach($group_name_lt as $key => $val){
	foreach($group_emp_lt[$key] as $key2 => $val2){
$ybase->ST_PRI .= <<<HTML
<td align="right">{$per_actnum_arr[$q_item_id][$key2]}</td>
HTML;
	}
}
$ybase->ST_PRI .= <<<HTML
</tr>
HTML;

}

}

$ybase->ST_PRI .= <<<HTML

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