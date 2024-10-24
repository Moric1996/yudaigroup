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

if(!$target_employee_id){
	$target_employee_id = $ybase->my_employee_id;
}

if(!preg_match("/^[0-9]+$/",$target_employee_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20002");
}

$YEAR = substr($t_month,0,4);
$MONTH = intval(substr($t_month,4,2));
$maxday = date("t",mktime(0,0,0,$MONTH,1,$YEAR));


$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;

$rank->group_const_make($t_shop_id,$t_month);
if(!array_key_exists($target_employee_id,$rank->group_const_list)) {
	$aarr = array_keys($rank->group_const_list);
	$target_employee_id = $aarr[0];
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

$ybase->title = "Y☆Rank-日次実績(個人)";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("日次実績(個人)");
//itemscore
$sql = "select item_id,score from telecom_item where {$addsql} order by order_num";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_score) = pg_fetch_array($result3,$i);
	$item_score[$q_itemid] = $q_score;
}
$all_goal_score = 0;
//個人目標
$sql = "select item_id,goal_num from telecom_goal where {$addsql} and employee_id = $target_employee_id order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_goal_num) = pg_fetch_array($result3,$i);
	$goalnum_arr[$q_itemid] = $q_goal_num;
	$all_goal_score += $q_goal_num * $item_score[$q_itemid];
}

//当日実績個人
$item_action_cnt = array();
$day_action_cnt = array();
$all_total=0;
$sql = "select item_id,day,action_num from telecom_action where {$addsql} and employee_id = $target_employee_id order by item_id,day";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_day,$q_action_num) = pg_fetch_array($result3,$i);
	if($q_action_num == 0){
		$q_action_num = "";
	}
	$per_actnum_arr[$q_itemid][$q_day] = $q_action_num;
	$item_action_cnt[$q_itemid] += $q_action_num;
	$day_action_cnt[$q_day] += $q_action_num * $item_score[$q_itemid];
	$all_total += $q_action_num * $item_score[$q_itemid];

}
if($all_goal_score){
	$reach_rate = round(($all_total / $all_goal_score) * 100,1);
}else{
	$reach_rate = 0.0;
}
$all_goal_score = number_format($all_goal_score);
$all_total = number_format($all_total);

$ybase->ST_PRI .= <<<HTML
<script type="text/javascript">
$(function(){
	$('input[type="month"],select').change(function(){
		$("#Form1").submit();
	});
});

</script>

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./rank_top.php?$param" role="button">Y☆RankTOPに戻る</a></div>
<p></p>
<h5 style="text-align:center;">【{$rank_section_name[$t_shop_id]} {$YEAR}年{$MONTH}月】日次実績(個人)</h5>

<p></p>
<table border="0" width="100%"><tr><td width="100">

<form action="./personal_achieve.php" method="post" id="Form1">
<input type="hidden" name="t_shop_id" value="$t_shop_id">
<input type="hidden" name="t_month" value="$t_month">
HTML;
$check = $rank->check_position($ybase->my_position_class);
$check = 1;
if($check){
$ybase->ST_PRI .= <<<HTML
<select name="target_employee_id">
<option value="">選択してください</option>
HTML;
foreach($rank->group_const_list as $key => $val){
if($key == $target_employee_id){
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
}else{
$ybase->ST_PRI .= <<<HTML
<b> {$sec_employee_list[$target_employee_id]}</b>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</form>

</td><td>
<nobr><a href="daily_achieve.php?$param" class="btn btn-sm btn-outline-info">全体</a>
<a href="group_achieve.php?$param" class="btn btn-sm btn-outline-info">グループ</a></nobr>
</td><td><div style="text-align:right;">
<nobr>目標:<span style="color:red;font-size:120%;">{$all_goal_score}</span>点</nobr><nobr>　獲得:<span style="color:red;font-size:120%;">{$all_total}</span>点</nobr><nobr>　達成:<span style="color:red;font-size:120%;">{$reach_rate}</span>%</nobr></div>
</td>
<td>
<a href="personal_achieve_pdf.php?$param&target_employee_id=$target_employee_id" target="_blank" class="btn btn-sm btn-outline-info">PDF</a>
<a href="personal_achieve_csv.php?$param&target_employee_id=$target_employee_id" class="btn btn-sm btn-outline-info">CSV</a>
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
<th rowspan="2">合計</th>
<th rowspan="2">目標<br>まで<br>あと</th>
HTML;
for($k=1;$k<=$maxday;$k++){
$ybase->ST_PRI .= <<<HTML
<th style="width:22px;">{$k}<br>日</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
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
/*////////合計行
$all_total = number_format($all_total);
$ybase->ST_PRI .= <<<HTML
<tr align="right" bgcolor="#eeeeee">
<td colspan="3">ポイント計</td>
<td colspan="2"><nobr>合計{$all_total}pt</nobr></td>
HTML;
for($k=1;$k<=$maxday;$k++){
$dtotal = number_format($day_action_cnt[$k]);
$ybase->ST_PRI .= <<<HTML
<td>$dtotal</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
HTML;
*/
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
<td rowspan="{$bigitem_cnt[$q_bigitem_id]}" style="vertical-align: middle;" bgcolor="{$bcolor[$i]}">
$str
</td>
HTML;
}else{
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="$hbgcolor">
HTML;
}
if(!$item_action_cnt[$q_item_id]){
	$d_acton_cnt = "";
}else{
	$d_acton_cnt = $item_action_cnt[$q_item_id];
}
$ybase->ST_PRI .= <<<HTML
<td align="center">
$q_item_name
</td>
<td align="right">{$goalnum_arr[$q_item_id]}</td>
<td align="right">$d_acton_cnt</td>
<td align="right">{$to_goal}</td>
HTML;
for($k=1;$k<=$maxday;$k++){
$ybase->ST_PRI .= <<<HTML
<td align="right">{$per_actnum_arr[$q_item_id][$k]}</td>
HTML;
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