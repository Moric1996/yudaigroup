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

if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}

$yy=substr($target_month,0,4);
$mm=substr($target_month,4,2);

/////////////////////////////////////////

$conn = $ybase->connect();
$group_allot_lt = array();
$param = "target_month=$target_month&target_shop_id=$target_shop_id";
//////////////////////////////////////////条件
$addsql = "month = $target_month and shop_id = $target_shop_id and status = '1'";
/////////////////////////グループリスト
$sql = "select group_id,group_name,allot from telecom_group where {$addsql} and status = '1' order by group_id";
$result4 = $ybase->sql($conn,$sql);
$num4 = pg_num_rows($result4);
$all_allot=0;
for($i=0;$i<$num4;$i++){
	list($q_group_id,$q_group_name,$q_allot) = pg_fetch_array($result4,$i);
	$group_allot_lt[$q_group_id] = $q_allot;
	$all_allot += $q_allot;
}

//////////////////////////////////////////データ確認
$sql = "select item_id,sum(goal_num) from telecom_goal_group where {$addsql} group by item_id order by item_id";

$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_sum_goal_num) = pg_fetch_array($result3,$i);
	$allcount = 0;
	$kk=0;
	foreach($group_allot_lt as $key => $val){
		$kk++;
		$nx_allot = round($q_sum_goal_num * $val / $all_allot);
		$allcount += $nx_allot;
		if($kk >= $num4){
			if($allcount != $q_sum_goal_num){
				$nx_allot += $q_sum_goal_num - $allcount;
			}
		}
		if(!$nx_allot){
			$nx_allot = 0;
		}
		$sql = "update telecom_goal_group set goal_num = $nx_allot where {$addsql} and item_id = $q_item_id and group_id = $key";
		$result = $ybase->sql($conn,$sql);
	}
}

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/set_teamallot_fm.php?{$param}");
exit;

////////////////////////////////////////////////
?>