<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();
$ttype_list[1] = "会計時間帯";
$ttype_list[2] = "オーダー時間帯";
//$edit_f = 1;
/////////////////////////////////////////

$ybase->make_shop_list();
//$ybase->shop_list['3001'] = "雄大ゴルフ熱函";	//雄大ゴルフ熱函
//$ybase->shop_list['3002'] = "雄大ゴルフ清水町";	//雄大ゴルフ清水町

$conn = $ybase->connect();

foreach($ybase->shop_list as $key => $val){

	$in_set_no = $set_no[$key];
	$in_from_lunch = $from_lunch[$key];
	$in_to_lunch = $to_lunch[$key];
	$in_from_dinner = $from_dinner[$key];
	$in_to_dinner = $to_dinner[$key];
	if($in_set_no && $in_from_lunch && $in_to_lunch && $in_from_dinner && $in_to_dinner){
		$in_from_lunch = substr($in_from_lunch,0,2).substr($in_from_lunch,3,2);
		$in_to_lunch = substr($in_to_lunch,0,2).substr($in_to_lunch,3,2);
		$in_from_dinner = substr($in_from_dinner,0,2).substr($in_from_dinner,3,2);
		$in_to_dinner = substr($in_to_dinner,0,2).substr($in_to_dinner,3,2);
		$sql = "select shop_id from lunch_time where shop_id = $key and status = '1' and set_no = $in_set_no";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if($num){
			$sql = "update lunch_time set from_lunch='$in_from_lunch',to_lunch='$in_to_lunch',from_dinner='$in_from_dinner',to_dinner='$in_to_dinner' where shop_id = $key and status = '1' and set_no = $in_set_no";
		}else{
			$sql = "insert into lunch_time values($key,$in_set_no,'2000-01-01','2200-01-01','$in_from_lunch','$in_to_lunch','$in_from_dinner','$in_to_dinner','now','1')";
		}
		$result = $ybase->sql($conn,$sql);
	}
}

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/set_lunch.php");
exit;


////////////////////////////////////////////////
?>