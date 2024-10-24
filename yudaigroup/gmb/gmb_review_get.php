<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
if(isset($_POST)){
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
}
if(isset($_GET)){
	foreach($_GET as $key => $value){
		${$key} = $value;
	}
}
///////////////////////////////////////////////////////////////
if(!isset($sess0)){
	$sess0 = '';
}
if(!isset($mei0)){
	$mei0 = '';
}
if(!isset($selyymm)){
	$selyymm = '';
}
if(!isset($selyymmdd)){
	$selyymmdd = '';
}
if(!isset($yy)){
	$yy = '';
}
if(!isset($mm)){
	$mm = '';
}
if(!isset($dd)){
	$dd = '';
}
if(!isset($_GET['code'])){
	$_GET['code'] = "";
}
if(!isset($guserid)){
	$guserid = '';
}
if(!isset($locationid)){
	$locationid = '';
}

///////////////////////////////////////////////////////////////
include(dirname(__FILE__).'/../../vendor/autoload.php');
include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/gmbconstlist.inc');

$ybase = new ybase();
$ybase->session_get($sess0,$mei0);
//$edit_f = 1;
/////////////////////////////////////////
$ybase->make_shop_list();
//$ybase->shop_list['3001'] = "雄大ゴルフ熱函";	// 雄大ゴルフ熱函
//$ybase->shop_list['3002'] = "雄大ゴルフ清水町";	//雄大ゴルフ清水町
$monthon = 1;//月単位で
$ybase->make_employee_list("1");
$company_id = 1;



if($monthon){
if($selyymm){
	$yy = substr($selyymm,0,4);
	$mm = substr($selyymm,5,2);
}elseif($selyymmdd){
	$yy = substr($selyymmdd,0,4);
	$mm = substr($selyymmdd,5,2);
}
if(!$yy || !$mm){
	$now_yy = date('Y');
	$now_mm = date('m');
	$now_dd = date('d');
	$yy = date('Y',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$mm = date('m',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$dd = date('d',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
}
$maxday = date('t',mktime(0,0,0,$mm,1,$yy));
$selyymm = "$yy-$mm";
}else{
if($selyymmdd){
	$yy = substr($selyymmdd,0,4);
	$mm = substr($selyymmdd,5,2);
	$dd = substr($selyymmdd,8,2);
}
if(!$yy || !$mm || !$dd){
	$now_yy = date('Y');
	$now_mm = date('m');
	$now_dd = date('d');
	$yy = date('Y',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$mm = date('m',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$dd = date('d',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
}
$selyymmdd = "$yy-$mm-$dd";
$selyymm = "$yy-$mm";
}

if(!$guserid && !$locationid){
	$ybase->error("パラメーターエラー。ERROR_CODE:012457");
}

$setAuthConfig = 'client_secret_609763822407-lrcjjac0ipfvqj6hsd2vf6anaof96gcf.apps.googleusercontent.com.json';
$redirect_uri = "https://yudai.info/yudaigroup/gmb/gmb_list.php";

$conn = $ybase->connect(2);

$add_sql = "";
if(!$guserid && $locationid){
	$sql = "select relation_id,guserid,accountid from gmb_relation where locationid = '$locationid' order by relation_id desc limit 1";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$ybase->error("パラメーターエラー。ERROR_CODE:012458");
	}
	list($q_relation_id,$guserid,$q_accountid) = pg_fetch_array($result,0);
	$add_sql = " and locationid = '$locationid'";
}
if(!$guserid){
	$ybase->error("パラメーターエラー。ERROR_CODE:012459");
}

$sql = "select googleID,googleEmail,googleName,company_id,token from gmb_guser where guserid = $guserid and status = '1' order by up_date desc limit 1";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データエラー。ERROR_CODE:012460");
}
list($q_googleID,$q_googleEmail,$q_googleName,$q_company_id,$q_token) = pg_fetch_array($result,0);
$db_token = "";

$client = new \Google_Client();
$client->setApplicationName("YUDAI GMB管理");
$client->setScopes("https://www.googleapis.com/auth/business.manage");
$client->addScope("https://www.googleapis.com/auth/plus.business.manage");
$client->addScope("https://www.googleapis.com/auth/userinfo.profile");
$client->addScope("https://www.googleapis.com/auth/userinfo.email");
$client->setAuthConfig("$setAuthConfig");
$client->setAccessType("offline");
$client->setApprovalPrompt('force');

$client->setRedirectUri($redirect_uri);
$update_flag="";
	$accessToken = json_decode($q_token, true);
	if($accessToken){
		$client->setAccessToken($accessToken);
		if ($client->isAccessTokenExpired()) {
			$refreshToken = $client->getRefreshToken();
			$client->fetchAccessTokenWithRefreshToken($refreshToken);
			$newAccessToken = $client->getAccessToken();
		        $newAccessToken['refresh_token'] = $refreshToken;
		 	$db_token = json_encode($newAccessToken);
 			$accessToken = $newAccessToken;
			$client->setAccessToken($accessToken);
			$update_flag=1;
		}else{
			$db_token = $q_token;
		}
	}



if($update_flag){
	$sql = "update gmb_token set token = '$db_token',add_date = 'now' where shop_id = '2'";
	$result = $ybase->sql($conn,$sql);
	$sql = "update gmb_guser set token = '$db_token',up_date = 'now' where guserid = $guserid";
	$result = $ybase->sql($conn,$sql);
}

////////////////////////////////////////////////
$token = $accessToken['access_token'];
$header[] = "Authorization: Bearer {$token}";



$sql = "select relation_id,accountid,locationid,company_id from gmb_relation where guserid = $guserid{$add_sql} order by locationid,accountid";
print "$sql<br>";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データエラー。ERROR_CODE:012466");
}
$pre_locationid=0;
for($i=0;$i<$num;$i++){
	list($q_relation_id,$q_accountid,$q_locationid,$q_company_id) = pg_fetch_array($result,$i);
	if($pre_locationid == $q_locationid){
		continue;
	}
	$nextPageToken = "";
	for($kkk=0;$kkk<100;$kkk++){
	//レビュー取得
	$curl = curl_init("https://mybusinessaccountmanagement.googleapis.com/v4/accounts/{$q_accountid}/locations/{$q_locationid}/reviews/?pageToken={$nextPageToken}&pageSize=50");
	curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($curl);
	curl_close($curl);
print_r($response);
print "<br><br>";
	if(!$response){
		$ybase->error("レビューがありません。ERROR_CODE:01449");
	}
	$reviewssource = json_decode($response);
	if(array_key_exists('error', $reviewssource)){
		$ybase->error("GMBレビュー取得できませんでした。ERROR_CODE:014490");
	}
	$reviewlist = $reviewssource->reviews;
	$averageRating = trim($reviewssource->averageRating);
	$totalReviewCount = trim($reviewssource->totalReviewCount);
	if(!$totalReviewCount){
		$totalReviewCount = 0;
	}
	$nextPageToken = trim($reviewssource->nextPageToken);
	$sql2 = "insert into gmb_starRating (starRating_id,company_id,locationid,t_date,averageRating,totalReviewCount,add_date,status) values (nextval('gmb_starRating_id_seq'),$q_company_id,'$q_locationid','now','$averageRating','$totalReviewCount','now','1')";
print "$sql2<br>";
	$result2 = $ybase->sql($conn,$sql2);
	$sql2 = "select reviewId,to_char(updateTime,'YYYYMMDDHH24MISS') from gmb_review where guserid = $guserid{$add_sql} order by locationid,accountid";
print "$sql2<br>";
	$result2 = $ybase->sql($conn,$sql2);
	$num2 = pg_num_rows($result2);
	if($num2){
		$reviewId_nowarr = pg_fetch_all_columns($result2,0);
		$updateTime_nowarr = pg_fetch_all_columns($result2,1);
	}else{
		$reviewId_nowarr = array();
	}
	foreach($reviewlist as $key => $val){///レビュー繰り返し
		$reviewname = trim($val->name);
		$reviewId = trim($val->reviewId);
		$profilePhotoUrl = trim($val->reviewer->profilePhotoUrl);
		$displayName = addslashes(trim($val->reviewer->displayName));
		$isAnonymous = $val->reviewer->isAnonymous;
		$starRating_val = trim($val->starRating);
		$starRating = $starRating_list[$starRating_val];
		$comment = addslashes(trim($val->comment));
		$createTime = trim($val->createTime);
		$updateTime = trim($val->updateTime);
		$updateTime2 = substr($updateTime,0,4).substr($updateTime,5,2).substr($updateTime,8,2).substr($updateTime,11,2).substr($updateTime,14,2).substr($updateTime,17,2);
		$Replycomment = addslashes(trim($val->reviewReply->comment));
		$ReplyupdateTime = trim($val->reviewReply->updateTime);
		$sql2key = array_search($reviewId,$reviewId_nowarr);
		if($sql2key === false){
			$sql2 = "insert into gmb_review (gmb_review_id,reviewId,company_id,locationid,name,profilePhotoUrl,displayName,isAnonymous,starRating,comment,createTime,updateTime,Replycomment,ReplyupdateTime,add_date,up_date) values (nextval('gmb_review_id_seq'),'$reviewId',$q_company_id,'$q_locationid','$reviewname','$profilePhotoUrl','$displayName',$isAnonymous,$starRating,'$comment','$createTime','$updateTime','$Replycomment','$ReplyupdateTime','now','now')";
print "$sql2<br>";
			$result2 = $ybase->sql($conn,$sql2);
		}else{
			if($updateTime_nowarr[$sql2key] < $updateTime2){
				$sql2 = "update gmb_review set name = '$reviewname',profilePhotoUrl = '$profilePhotoUrl',displayName = '$displayName',isAnonymous = $isAnonymous,starRating = $starRating,comment = '$comment',updateTime = '$updateTime',Replycomment = '$Replycomment',ReplyupdateTime = '$ReplyupdateTime',up_date = 'now' where reviewId = $reviewId";
print "$sql2<br>";
				$result2 = $ybase->sql($conn,$sql2);
			}else{
				continue;
			}
		}
	}///レビュー繰り返し
		if(!$nextPageToken){
			break;
		}
	}
	$pre_locationid = $q_locationid;
}
////////////////////////////////////////////////////////////////////////////////
print "<br><br>OK";
////////////////////////////////////////////////
?>