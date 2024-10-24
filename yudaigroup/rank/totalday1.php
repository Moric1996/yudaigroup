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
////////SBの特別処理
$sb_group_oohito1 = 119;	//大仁A
$sb_group_oohito2 = 125;	//大仁B

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

/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$param = "t_month=$t_month&t_shop_id=$t_shop_id";
$addsql = "month = $t_month and shop_id = $t_shop_id and status = '1'";
//////////////////////////////////////////
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

$ybase->title = "Y☆Rank-日別集計表(全体)";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("日別集計表(全体)");


/////////////////////////グループリスト
$sql = "select group_id,group_name,allot from telecom_group where {$addsql} and status = '1' order by group_id";
$result3 = $ybase->sql($conn,$sql);
$group_num = pg_num_rows($result3);
if(!$group_num){
	$ybase->error("チームの設定がされていません。先にチームの設定をしてください。");
}
for($i=0;$i<$group_num;$i++){
	list($q_group_id,$q_group_name,$q_allot) = pg_fetch_array($result3,$i);
	$group_name_lt[$q_group_id] = $q_group_name;
}


if($t_shop_id == 305){
	$group_name_lt[3059999] = "大仁AB";
}
/////////////////////////グループ構成員リスト＆目標データ確認
$sql = "select group_const_id,group_id,employee_id,short_name from telecom_group_const where {$addsql} and status = '1' order by group_id,group_const_id";
$result3 = $ybase->sql($conn,$sql);
$const_num = pg_num_rows($result3);
if(!$const_num){
	$ybase->error("チームの設定がされていません。先にチームの設定をしてください。");
}
for($i=0;$i<$const_num;$i++){
	list($q_group_const_id,$q_group_id,$q_employee_id,$q_short_name) = pg_fetch_array($result3,$i);
	$group_emp_lt[$q_group_id][] = $q_employee_id;
	$group_cnt[$q_group_id]++;
	if($q_employee_id == $ybase->my_employee_id){
		$my_group_id = $q_group_id;
	}
}
////////////////////////////////
if($target_group_id == 3059999){//SBの場合の大仁A大仁Bを同時に出す処理
	$dammy1 = $group_emp_lt[$sb_group_oohito1];
	$dammy2 = $group_emp_lt[$sb_group_oohito2];
	$dammy = array_merge($dammy1,$dammy2);
}else{
	$dammy = $group_emp_lt[$target_group_id];
}
if($dammy){
	$emp_lists = implode(",",$dammy);
}else{
	$emp_lists = 0;
}
/////////////////////////////////////////////////
//itemscore
$sql = "select item_id,score from telecom_item where {$addsql} order by order_num";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_score) = pg_fetch_array($result3,$i);
	$item_score[$q_itemid] = $q_score;
}

//当日目標
$dgoalnum_sum_arr=array();
$sql = "select item_id,dgoal_num,day from telecom_goal_day where {$addsql} and day <= $target_day order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_dgoal_num,$q_day) = pg_fetch_array($result3,$i);
	$dgoalnum_arr[$q_day][$q_itemid] = $q_dgoal_num;
	$dgoalnum_sum_arr[$q_itemid] += $q_dgoal_num;
}
$add_emplt_sql = "";
$add_grouplt_sql = "";
if($target_group_id){
	$dgoalnum_arr = array();
	$dgoalnum_sum_arr =  array();

	$add_emplt_sql = " and employee_id in ({$emp_lists})";
	if($target_group_id == 3059999){
		$add_grouplt_sql = " and group_id in ($sb_group_oohito1,$sb_group_oohito2)";
	}else{
		$add_grouplt_sql = " and group_id = $target_group_id";
	}
}

//当日実績
$sql = "select item_id,sum(action_num),sum(dgoal_num) from telecom_action where {$addsql} and day = $target_day{$add_emplt_sql} group by item_id order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_action_num,$q_dgoal_num) = pg_fetch_array($result3,$i);
	if(!$q_action_num){
		$q_action_num = "";
	}
	$actnum_arr[$q_itemid] = $q_action_num;
}
//昨日実績
$last_day = $target_day - 1;
if($last_day > 0){
$sql = "select item_id,sum(action_num) from telecom_action where {$addsql} and day = $last_day{$add_emplt_sql} group by item_id order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_action_num) = pg_fetch_array($result3,$i);
	if(!$q_action_num){
		$q_action_num = "";
	}
	$last_actnum_arr[$q_itemid] = $q_action_num;
}
}
$all_act_score = 0;
$all_goal_score = 0;

//月間実績
$sql = "select item_id,sum(action_num) from telecom_action where {$addsql} and day <= $target_day{$add_emplt_sql} group by item_id order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_action_num) = pg_fetch_array($result3,$i);
	if(!$q_action_num){
		$q_action_num = "";
	}
	$month_actnum_arr[$q_itemid] = $q_action_num;
	$all_act_score += $q_action_num * $item_score[$q_itemid];
}
//月間目標
$sql = "select item_id,sum(goal_num) from telecom_goal_group where {$addsql}{$add_grouplt_sql} group by item_id order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_dgoal_num) = pg_fetch_array($result3,$i);
	$month_goalnum_arr[$q_itemid] = $q_dgoal_num;
	$all_goal_score += $q_dgoal_num * $item_score[$q_itemid];
}

/////////////////////////////////////////////////////
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
<h5 style="text-align:center;">【{$rank_section_name[$t_shop_id]} {$YEAR}年{$MONTH}月】日別集計表(全体)</h5>

<p></p>
<table border="0" width="100%">
<tr>
<form action="totalday1.php" method="get" id="dayselectfrom">
<input type="hidden" name="t_month" value="$t_month">
<input type="hidden" name="t_shop_id" value="$t_shop_id">
<td>
<nobr>
<b> 
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
<nobr><a href="totalday1.php?$param&target_day=$bf" class="btn btn-sm btn-outline-secondary{$bf_disable}">前日</a>
<a href="totalday1.php?$param&target_day=$nx" class="btn btn-sm btn-outline-secondary{$nx_disable}">翌日</a>
&nbsp;&nbsp;&nbsp;<a href="totalday2.php?$param&target_day=$target_day" class="btn btn-sm btn-outline-info">個人</a>
HTML;

if($t_shop_id == 305){
	if($target_group_id){
$ybase->ST_PRI .= <<<HTML
&nbsp;&nbsp;&nbsp;<a href="totalday1.php?$param&target_day=$target_day" class="btn btn-sm btn-outline-info">全体</a>
HTML;
	}else{
$ybase->ST_PRI .= <<<HTML
&nbsp;&nbsp;&nbsp;<button class="btn btn-sm btn-info">全体</button>
HTML;
}
	foreach($group_name_lt as $key => $val){
		if($key == $target_group_id){
$ybase->ST_PRI .= <<<HTML
&nbsp;<button class="btn btn-sm btn-info">$val</button>
HTML;
		}else{
$ybase->ST_PRI .= <<<HTML
&nbsp;<a href="totalday1.php?$param&target_day=$target_day&target_group_id=$key" class="btn btn-sm btn-outline-info">$val</a>
HTML;
		}
	}
}

$ybase->ST_PRI .= <<<HTML
</nobr>
</td></form><td><div style="text-align:right;">
<nobr>目標:<span style="color:red;font-size:150%;">{$all_goal_score}</span>点</nobr><br><nobr>　獲得:<span style="color:red;font-size:150%;">{$all_act_score}</span>点</nobr><br><nobr>　達成:<span style="color:red;font-size:150%;">{$reach_rate}</span>%</nobr></div>
</td></tr>
</table>
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
<tr bgcolor="#cccccc">
<th></th>
<th></th>
<th>当日目標</th>
<th>当日実績</th>
<th>昨日実績</th>
<th>月間累計</th>
<th>月間目標</th>
<th>日数進捗</th>
<th>貯金借金</th>
<th>配点</th>
<th>目標点数</th>
<th>獲得点数</th>

</tr>
  </thead>
  <tbody>
HTML;
/////////////////////////////////////////////
$kk=0;
$all_goal_score = 0;
$all_act_score = 0;
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
$goal_score = $month_goalnum_arr[$q_item_id] * $q_score;
$act_score = $month_actnum_arr[$q_item_id] * $q_score;
$all_goal_score += $goal_score;
$all_act_score += $act_score;
$goal_score = number_format($goal_score);
$act_score = number_format($act_score);
$progress = $dgoalnum_sum_arr[$q_item_id];
$savings = $month_actnum_arr[$q_item_id] - $progress;
if($ybase->my_employee_id == 10005){
	$additem = "({$q_item_id})";
}else{
	$additem = "";
}
$ybase->ST_PRI .= <<<HTML
<td align="center">
$q_item_name {$additem}
</td>
<td align="right">{$dgoalnum_arr[$target_day][$q_item_id]}{$rank->unitname_list[$q_item_id]}</td>
<td align="right">{$actnum_arr[$q_item_id]}{$rank->unitname_list[$q_item_id]}</td>
<td align="right">{$last_actnum_arr[$q_item_id]}{$rank->unitname_list[$q_item_id]}</td>
<td align="right">{$month_actnum_arr[$q_item_id]}{$rank->unitname_list[$q_item_id]}</td>
<td align="right">{$month_goalnum_arr[$q_item_id]}{$rank->unitname_list[$q_item_id]}</td>
<td align="right">{$progress}{$rank->unitname_list[$q_item_id]}</td>
<td align="right">{$savings}{$rank->unitname_list[$q_item_id]}</td>
<td align="right">{$q_score}点</td>
<td align="right">{$goal_score}点</td>
<td align="right">{$act_score}点</td>

</tr>
HTML;

}

}
$all_goal_score = number_format($all_goal_score);
$all_act_score = number_format($all_act_score);

$ybase->ST_PRI .= <<<HTML
<tr>
<td colspan ="2">合計</td>
<td colspan ="8"></td>
<td align="right">{$all_goal_score}点</td>
<td align="right">{$all_act_score}点</td>

</tr>

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