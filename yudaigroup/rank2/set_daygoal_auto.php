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
$ybase->session_get();

if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}

$yy=substr($target_month,0,4);
$mm=substr($target_month,4,2);
$maxday = date("t",mktime(0,0,0,$mm,1,$yy));

$param = "target_month=$target_month&target_shop_id=$target_shop_id";
/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$addsql = "month = $target_month and shop_id = $target_shop_id";
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
if($day_num){
//	header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/set_daygoal_fm.php?{$param}");
//	exit;
}

///////////////////////////////////////////////////////////
$sql = "update telecom2_goal_day set status = '0' where {$addsql} and status = '1'";
$result = $ybase->sql($conn,$sql);


$sql = "select item_id,score from telecom2_item where {$addsql} and status = '1' order by bigitem_id,order_num";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当月の項目がまだ設定されていません");
}
for($i=0;$i<$num;$i++){
	list($q_item_id,$q_score) = pg_fetch_array($result,$i);
	$base_goal = $g_goal_num_lt[$q_item_id] / $maxday;
	$day_goal = floor($base_goal);
	$few_goal = $base_goal - $day_goal;
	$few_gokei = 0;
	$gokei = 0;
	for($k=1;$k<=$maxday;$k++){
		$few_gokei += $few_goal;
		if($few_gokei >= 1){
			$in_goal = $day_goal + 1;
			$few_gokei =  $few_gokei - 1;
		}else{
			$in_goal = $day_goal;
		}
		$check_gokei = $gokei + $in_goal;
		if($check_gokei > $g_goal_num_lt[$q_item_id]){
			$in_goal = $g_goal_num_lt[$q_item_id] - $gokei;
		}
		if($k == $maxday){
			$in_goal = $g_goal_num_lt[$q_item_id] - $gokei;
		}
		if($in_goal < 0){
			$in_goal = 0;
		}
		$gokei += $in_goal;
		$sql0 = "insert into telecom2_goal_day (shop_id,item_id,month,day,dgoal_num,add_date,status) values ($target_shop_id,$q_item_id,$target_month,$k,$in_goal,'now','1')";
		$result0 = $ybase->sql($conn,$sql0);

	}
}





header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/set_daygoal_fm.php?{$param}");
exit;
////////////////////////////////////////////////
?>