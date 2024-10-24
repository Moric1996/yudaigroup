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
$maxday = date("t",mktime(0,0,0,$MONTH,1,$YEAR));

/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$param = "t_month=$t_month&t_shop_id=$t_shop_id";
$addsql = "month = $t_month and shop_id = $t_shop_id and status = '1'";
//////////////////////////////////////////
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

$ybase->title = "Y☆Rank-日次実績(全体)";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("日次実績(全体)");
//itemscore
$sql = "select item_id,score from telecom_item where {$addsql} order by order_num";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_score) = pg_fetch_array($result3,$i);
	$item_score[$q_itemid] = $q_score;
}
$all_act_score = 0;
$all_goal_score = 0;

//目標
$sql = "select item_id,sum(goal_num) from telecom_goal_group where {$addsql} group by item_id order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_goal_num) = pg_fetch_array($result3,$i);
	$goalnum_arr[$q_itemid] = $q_goal_num;
	$all_goal_score += $q_goal_num * $item_score[$q_itemid];
}

//当日実績
$item_action_cnt = array();
$day_action_cnt = array();
$sql = "select item_id,day,sum(action_num) from telecom_action where {$addsql} group by item_id,day order by item_id,day";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_day,$q_action_num) = pg_fetch_array($result3,$i);
	if($q_action_num == 0){
		$q_action_num = "";
	}
	$actnum_arr[$q_itemid][$q_day] = $q_action_num;
	$item_action_cnt[$q_itemid] += $q_action_num;
	$day_action_cnt[$q_day] += $q_action_num * $item_score[$q_itemid];
	$all_act_score += $q_action_num * $item_score[$q_itemid];
}
if($all_goal_score){
	$reach_rate = round(($all_act_score / $all_goal_score) * 100,1);
}else{
	$reach_rate = 0.0;
}
$all_goal_score = number_format($all_goal_score);
$all_act_score = number_format($all_act_score);
if($t_shop_id == 302){
	$pdffilename = "daily_achieve4.php";
}else{
	$pdffilename = "daily_achieve4.php";
}
$ybase->ST_PRI .= <<<HTML
<div class="container-fluid">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./rank_top.php?$param" role="button">Y☆RankTOPに戻る</a><br>

<p></p>
<h5 style="text-align:center;">【{$rank_section_name[$t_shop_id]} {$YEAR}年{$MONTH}月】日次実績(全体)</h5>
<table border="0" width="100%"><tr><td>
<nobr><a href="group_achieve.php?$param" class="btn btn-sm btn-outline-info">グループ</a>
<a href="personal_achieve.php?$param" class="btn btn-sm btn-outline-info">個人</a></nobr>
</td>
<td>
<div style="text-align:right;">
<nobr>目標:<span style="color:red;font-size:150%;">{$all_goal_score}</span>点</nobr><nobr>　獲得:<span style="color:red;font-size:150%;">{$all_act_score}</span>点</nobr><nobr>　達成:<span style="color:red;font-size:150%;">{$reach_rate}</span>%</nobr>
　
<a href="{$pdffilename}?$param" target="_blank" class="btn btn-sm btn-outline-info">PDF</a>
</div>
</td>
</tr>
</table>
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
<thead>
<tr bgcolor="#cccccc" align="center">
<th rowspan="2"></th>
<th rowspan="2"></th>
<th rowspan="2">目標</th>
<th rowspan="2">目標<br>まで<br>あと</th>
HTML;
for($k=1;$k<=$maxday;$k++){
$ybase->ST_PRI .= <<<HTML
<th style="width:22px;">{$k}<br>日</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
<th rowspan="2">合計</th>
</tr>
<tr bgcolor="#d5d5d5" align="center">
HTML;
for($k=1;$k<=$maxday;$k++){
$youbi = $rank->make_yobi($t_month,$k);
$ybase->ST_PRI .= <<<HTML
<th>$youbi</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
  </thead>
  <tbody>
HTML;

///////////////////////////////////
$ybase->ST_PRI .= <<<HTML
<tr align="right" bgcolor="#eeeeee">
<td colspan="4">ポイント計</td>
HTML;
$all_total=0;
for($k=1;$k<=$maxday;$k++){
$all_total += $day_action_cnt[$k];
$dtotal = number_format($day_action_cnt[$k]);
$ybase->ST_PRI .= <<<HTML
<td>$dtotal</td>
HTML;
}
$all_total = number_format($all_total);
$ybase->ST_PRI .= <<<HTML
<td>$all_total</td>
</tr>
HTML;
///////////////////////////////////
$kk=0;
$total_pt=array();
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
	$to_goal = $goalnum_arr[$q_item_id] - $item_action_cnt[$q_item_id];
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
<td rowspan="{$bigitem_cnt[$q_bigitem_id]}" style="vertical-align: middle;" bgcolor="{$bcolor[$i]}" width="15rem">
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
<nobr>$q_item_name</nobr>
</td>
<td align="right"><nobr>{$goalnum_arr[$q_item_id]}{$rank->unitname_list[$q_item_id]}<nobr></td>
<td align="right">{$to_goal}</td>
HTML;
for($k=1;$k<=$maxday;$k++){
$total_pt[$k] += $actnum_arr[$q_item_id][$k] * $q_score;
$ybase->ST_PRI .= <<<HTML
<td align="right">{$actnum_arr[$q_item_id][$k]}</td>
HTML;
}
$i_cnt = number_format($item_action_cnt[$q_item_id]);
$ybase->ST_PRI .= <<<HTML
<td align="right">{$i_cnt}</td>
</tr>
HTML;

}

}

$ybase->ST_PRI .= <<<HTML
<tr align="right" bgcolor="#eeeeee">
<td colspan="4">ポイント計</td>
HTML;
$all_total=0;
for($k=1;$k<=$maxday;$k++){
$all_total += $total_pt[$k];
$dtotal = number_format($total_pt[$k]);
$ybase->ST_PRI .= <<<HTML
<td>$dtotal</td>
HTML;
}
$all_total = number_format($all_total);
$ybase->ST_PRI .= <<<HTML
<td>$all_total</td>
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