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

//$ybase->title = "Y☆Rank-日次実績(個人)";

//$ybase->HTMLheader();
//$ybase->ST_PRI .= $ybase->header_pri("日次実績(個人)");
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
//$all_goal_score = number_format($all_goal_score);
//$all_total = number_format($all_total);

$csvfile = "";
$csvfile .= "\"日次実績(個人)\",\"".$rank_section_name[$t_shop_id]."\",\"".$sec_employee_list[$target_employee_id]."\",$t_month,$all_goal_score,$all_total,$reach_rate";
$csvfile .= "\r\n";

$csvfile .= "\"大項目\",\"項目名\",\"目標\",\"合計\",\"目標まであと\"";

for($k=1;$k<=$maxday;$k++){

$csvfile .= ",$k";
}
$csvfile .= "\r\n";


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
	list($q_item_id,$q_item_name,$q_score) = pg_fetch_array($result2,$ii);
	$to_goal = $goalnum_arr[$q_item_id] - $item_action_cnt[$q_item_id];

	$csvfile .= "\"$q_bigitem_name\",\"$q_item_name\"";

if(!$item_action_cnt[$q_item_id]){
	$d_acton_cnt = "";
}else{
	$d_acton_cnt = $item_action_cnt[$q_item_id];
}
	$csvfile .= ",{$goalnum_arr[$q_item_id]},{$d_acton_cnt},{$to_goal}";

for($k=1;$k<=$maxday;$k++){
	$csvfile .= ",{$per_actnum_arr[$q_item_id][$k]}";
}
$csvfile .= "\r\n";
}

}

$filename = 'personal'.$target_employee_id.date("Ymd").'.csv';
$filesize = strlen($csvfile);
header('Content-Type: application/octet-stream');
header("Content-Length: $filesize");
header("Content-Disposition: attachment; filename=$filename");

print $csvfile;

exit;

////////////////////////////////////////////////
?>