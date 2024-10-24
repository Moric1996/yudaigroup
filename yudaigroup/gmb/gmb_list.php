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
///////////////////////////////////////////////////////////////
include(dirname(__FILE__).'/../../vendor/autoload.php');
include(dirname(__FILE__).'/../inc/ybase.inc');

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
if(($ybase->my_section_id == "003")||($ybase->my_position_class <= 40)){
	$delokflag="1";
}else{
	$delokflag="";
}

$gmb_acount = "yournetgmb@gmail.com";
$setAuthConfig = 'client_secret_609763822407-lrcjjac0ipfvqj6hsd2vf6anaof96gcf.apps.googleusercontent.com.json';
$redirect_uri = "https://yudai.info/yudaigroup/gmb/gmb_list.php";

$conn = $ybase->connect(2);

$sql = "select gmb_token_id,token,setAuthConfig from gmb_token where shop_id = '2' and setAuthConfig = '$setAuthConfig' order by gmb_token_id desc limit 1";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	list($q_gmb_token_id,$q_token,$q_setAuthConfig) = pg_fetch_array($result,0);
}else{
	$q_token = "";
}
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
if($num){
	$accessToken = json_decode($q_token, true);
	if($accessToken){
		$client->setAccessToken($accessToken);
		if ($client->isAccessTokenExpired()) {
//			$client->setRedirectUri("$redirect_uri");
//			$authUrl = $client->createAuthUrl();
//			printf("Open the following link in your browser:\n%s\n", $authUrl);
//		$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

			$refreshToken = $client->getRefreshToken();
			$client->fetchAccessTokenWithRefreshToken($refreshToken);
			$newAccessToken = $client->getAccessToken();
		        $newAccessToken['refresh_token'] = $refreshToken;
		 	$db_token = json_encode($newAccessToken);
 			$accessToken = $newAccessToken;
			$client->setAccessToken($accessToken);
		}
	}
}else{
		if ($_GET['code']) {
			// 認証コードをトークンに交換
			$accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
			$client->setAccessToken($accessToken);
			// エラーの確認
			if (array_key_exists('error', $accessToken)) {
				throw new Exception(join(', ', $accessToken));
			}else{
				$db_token = json_encode($client->getAccessToken());
			}
		}else{
		// ユーザに許可を申請
			$client->setRedirectUri("$redirect_uri");
			$authUrl = $client->createAuthUrl();
			printf("Open the following link in your browser:\n%s\n", $authUrl);
			exit;
		}
	}


	if($num){
		$sql = "update gmb_token set token = '$db_token',add_date = 'now' where gmb_token_id = $q_gmb_token_id";
	}else{
		$sql = "delete from gmb_token where shop_id = '2'";
		$result = $ybase->sql($conn,$sql);
		$sql = "insert into gmb_token values(nextval('gmb_token_id_seq'),'$gmb_acount','2','$db_token','$setAuthConfig','now')";
	}
	if($db_token){
		$result = $ybase->sql($conn,$sql);
	}



////////////////////////////////////////////////
$token = $accessToken['access_token'];
$header[] = "Authorization: Bearer {$token}";

///////////////////////////////////////////////////ユーザー情報取得
$curl = curl_init("https://www.googleapis.com/oauth2/v1/userinfo?access_token={$token}");
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);
//print_r($response);
//print "<br><br>";
if(!$response){
	$ybase->error("GMBユーザー情報が取得できませんでした。ERROR_CODE:05487");
}
$userinfo = json_decode($response);
if(array_key_exists('error', $userinfo)){
	$ybase->error("GMBユーザー情報が取得できませんでした。ERROR_CODE:05488");
}

$googleID = trim($userinfo->id);
$googleEmail = trim($userinfo->email);
$googleName = trim($userinfo->name);
//print "$googleID<br>$googleEmail<br>$googleName<br>";

///////////////////////////////////////////////////ユーザー情報確認
$sql = "select guserid,googleName from gmb_guser where googleID = '$googleID' and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	list($guserid,$q_googleName) = pg_fetch_array($result,0);

	$sql = "update gmb_guser set googleEmail = '$googleEmail',googleName = '$googleName',token = '$db_token',up_date = 'now' where googleID = '$googleID' and status = '1'";
	$result = $ybase->sql($conn,$sql);
}else{
	$sql = "select nextval('gmb_guser_id_seq')";
	$result = $ybase->sql($conn,$sql);
	$num0 = pg_num_rows($result);
	if(!$num0){
		$ybase->error("データベースエラーです。ERROR_CODE:02801");
	}
	$guserid = pg_fetch_result($result,0,0);
	$sql = "insert into gmb_guser (guserid,googleID,googleEmail,googleName,company_id,token,add_date,up_date,status) values ($guserid,'$googleID','$googleEmail','$googleName',$company_id,'$db_token','now','now','1')";
	$result = $ybase->sql($conn,$sql);
}

////////////////////////////////////////////////////////////アカウント情報

$curl = curl_init('https://mybusinessaccountmanagement.googleapis.com/v1/accounts');
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);
//print_r($response);
//print "<br><br>";
if(!$response){
	$ybase->error("GMBアカウント情報が取得できませんでした。ERROR_CODE:05489");
}
$accountsource = json_decode($response);
if(array_key_exists('error', $accountsource)){
	$ybase->error("GMBアカウント情報が取得できませんでした。ERROR_CODE:054890");
}
$accountlist = $accountsource->accounts;
//print_r($aa);
//print "<br><br>";

////////////////////////////////////////////////////////////////////////////////
foreach($accountlist as $key => $val){///アカウント繰り返し
	$accountidbase = $val->name;
	$accountName = trim($val->accountName);
	$type = trim($val->type);
	list($acname,$accountid) = explode("/",$accountidbase);
	$accountid = trim($accountid);
print "<br>account<br>";
print "$accountidbase,$accountName,$type,$accountid";
print "<br><br>";
	//DBin
	$sql = "select accountName,type from gmb_account where accountid = '$accountid' and status = '1'";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
		$sql = "update gmb_account set accountName = '$accountName',type = '$type',up_date = 'now' where accountid = '$accountid' and status = '1'";
		$result = $ybase->sql($conn,$sql);
	}else{
		$sql = "insert into gmb_account (accountid,accountName,type,company_id,add_date,up_date,status) values ('$accountid','$accountName','$type',$company_id,'now','now','1')";
		$result = $ybase->sql($conn,$sql);
	}

	//locationデータ取得
	$readmask = "name,languageCode,storeCode,title,phoneNumbers,websiteUri,metadata";
	$curl = curl_init("https://mybusinessbusinessinformation.googleapis.com/v1/accounts/{$accountid}/locations?readMask={$readmask}");
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($curl);
	curl_close($curl);

//print_r($response);
//print "<br><br>";
	if(!$response){
		$ybase->error("GMBお店情報が取得できませんでした。GMBの登録が完了してない場合は、登録後再度試してください。ERROR_CODE:05449");
	}
	$locationsource = json_decode($response);
	if(array_key_exists('error', $locationsource)){
		$ybase->error("GMBお店情報が取得できませんでした。GMBの登録が完了してない場合は、登録後再度試してください。ERROR_CODE:054490");
	}
	$locationlist = $locationsource->locations;
//店舗データ取得
	foreach($locationlist as $key2 => $val2){///location繰り返し
		$locationidbase = $val2->name;
		$locationtitle = trim($val2->title);
		$storeCode = trim($val2->storeCode);
		$languageCode = trim($val2->languageCode);
		$websiteUri = trim($val2->websiteUri);
		$mapsUri = trim($val2->metadata->mapsUri);
		$newReviewUri = trim($val2->metadata->newReviewUri);
		$placeId = trim($val2->metadata->placeId);
		list($acname,$locationid) = explode("/",$locationidbase);
		$locationid = trim($locationid);
print "<br>account<br>";
print "$locationidbase,$locationtitle,$storeCode,$websiteUri,$mapsUri,$newReviewUri,$placeId,$locationid";
print "<br><br>";

		$sql = "select title,storeCode from gmb_location where locationid = '$locationid' and status = '1'";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if($num){
			$sql = "update gmb_location set placeId = '$placeId',title = '$title',storeCode = '$storeCode',languageCode = '$languageCode',websiteUri = '$websiteUri',mapsUri = '$mapsUri',newReviewUri = '$newReviewUri',up_date = 'now' where locationid = '$locationid' and status = '1'";
			$result = $ybase->sql($conn,$sql);
		}else{
			$sql = "insert into gmb_location (locationid,company_id,shop_id,placeId,title,storeCode,languageCode,websiteUri,mapsUri,newReviewUri,add_date,up_date,status) values ('$locationid',$company_id,null,'$placeId','$title','$storeCode','$languageCode','$websiteUri','$mapsUri','$newReviewUri','now','now','1')";
			$result = $ybase->sql($conn,$sql);
		}
	///関係テーブル

		$sql = "select relation_id from gmb_relation where locationid = '$locationid' and guserid = $guserid and accountid = '$accountid' and company_id = $company_id";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$sql = "insert into gmb_relation (relation_id,guserid,accountid,locationid,company_id,add_date) values (nextval('gmb_relation_id_seq'),$guserid,'$accountid','$locationid',$company_id,'now')";
			$result = $ybase->sql($conn,$sql);
		}








	}///location繰り返し

}///アカウント繰り返し









/////////////////////////////////レビュー取得
/*
$curl = curl_init("https://mybusinessaccountmanagement.googleapis.com/v4/accounts/{$accountid}/locations/13746540536059991173/reviews/");

curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);

print_r($response);
print "<br><br>";
*/





exit;










/*

$conn = $ybase->connect(2);
if($monthon){
	$date_sql = "between '{$selyymm}-01' and '{$selyymm}-{$maxday}'";
	$g_width = 2000;
}else{
	$date_sql = "= '$selyymmdd'";
	$g_width = 1000;
}
$sql = "select survey_set_id,shop_id,survey_no,card_sum,title,to_char(add_date,'YYYY/MM/DD') from survey_set where company_id = 1";

if($shop_id){
//	$sql .= " and shop_id = '$shop_id'";
}
$sql .= " and status = '1' order by jun,shop_id,survey_no";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$mun0 = 0;
for($i=0;$i<$num;$i++){
	list($q_survey_set_id,$q_shop_id,$q_survey_no,$q_card_sum,$q_title,$q_add_date) = pg_fetch_array($result,$i);
	$shop_list[$q_shop_id] = $ybase->section_list[$q_shop_id];
	if($shop_id && ($shop_id != $q_shop_id)){
		continue;
	}
	$q_title = trim($q_title);
	$q_shop_id_arr[$q_survey_set_id] = $q_shop_id;
	$q_survey_no_arr[$q_survey_set_id] = $q_survey_no;
	$q_card_sum_arr[$q_survey_set_id] = $q_card_sum;
	$q_title_arr[$q_survey_set_id] = $q_title;
	$q_add_date_arr[$q_survey_set_id] = $q_add_date;
	$num0++;
}
$ybase->title = "グーグルマイビジネス(ビジネスプロフィール)一覧";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);
*/


$ybase->ST_PRI .= <<<HTML



<div class="container">

<p></p>


</div>




HTML;

$ybase->HTMLfooter();
$ybase->priout();

////////////////////////////////////////////////
?>