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

$ybase->title = "グーグルマイビジネス(ビジネスプロフィール)新規認証";
$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri($ybase->title);
$ybase->ST_PRI .= <<<HTML
<div class="container">
<p></p>

HTML;

////////////////////////////////////////////
$setAuthConfig = 'client_secret_609763822407-lrcjjac0ipfvqj6hsd2vf6anaof96gcf.apps.googleusercontent.com.json';
$redirect_uri = "https://yudai.info/yudaigroup/gmb/gmb_list.php";

$client = new \Google_Client();
$client->setApplicationName("YUDAI GMB管理");
$client->setScopes("https://www.googleapis.com/auth/business.manage");
$client->addScope(["https://www.googleapis.com/auth/plus.business.manage"]);
$client->addScope("https://www.googleapis.com/auth/userinfo.profile");
$client->addScope("https://www.googleapis.com/auth/userinfo.email");
$client->setAuthConfig("$setAuthConfig");
$client->setAccessType("offline");
$client->setApprovalPrompt('force');

if ($_GET['code']) {
	// 認証コードをトークンに交換
	$accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
	$client->setAccessToken($accessToken);
	$token = $accessToken['access_token'];

	// エラーの確認
	if (array_key_exists('error', $accessToken)) {
$ybase->ST_PRI .= <<<HTML
<div class="text-center">
認証に失敗しました。<br>
<a href="./gmb_regi.php">認証に戻る</a>
</div>
</div>

HTML;
	$ybase->HTMLfooter();
	$ybase->priout();

			}else{
				$db_token = json_encode($client->getAccessToken());
			}
}else{

$ybase->ST_PRI .= <<<HTML
<div class="text-center">
認証に失敗しました。<br>
<a href="./gmb_regi.php">認証に戻る</a>
</div>
</div>

HTML;
	$ybase->HTMLfooter();
	$ybase->priout();
}

$header[] = "Authorization: Bearer {$token}";

$curl = curl_init("https://www.googleapis.com/oauth2/v1/userinfo?access_token={$token}");
//$curl = curl_init("https://www.googleapis.com/oauth2/v3/tokeninfo?access_token={$token}");
//$curl = curl_init("https://www.googleapis.com/oauth2/v1/tokeninfo?access_token={$token}");
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);
//print_r($response);
//print "<br><br>";









$conn = $ybase->connect(2);

$sql = "select gmb_token_id,token,setAuthConfig from gmb_token where shop_id = '$shop_id' and setAuthConfig = '$setAuthConfig' order by gmb_token_id desc limit 1";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	list($q_gmb_token_id,$q_token,$q_setAuthConfig) = pg_fetch_array($result,0);
}else{
	$q_token = "";
}
$db_token = "";



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

	}


	if($num){
		$sql = "update gmb_token set token = '$db_token',add_date = 'now' where gmb_token_id = $q_gmb_token_id";
	}else{
		$sql = "delete from gmb_token where shop_id = '$shop_id'";
		$result = $ybase->sql($conn,$sql);
		$sql = "insert into gmb_token values(nextval('gmb_token_id_seq'),'$gmb_acount','$shop_id','$db_token','$setAuthConfig','now')";
	}
	if($db_token){
		$result = $ybase->sql($conn,$sql);
	}


$token = $accessToken['access_token'];
$header[] = "Authorization: Bearer {$token}";
$curl = curl_init('https://mybusinessaccountmanagement.googleapis.com/v1/accounts');
curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);
print_r($response);

print "<br><br>";

$accountlist = json_decode($response);
$aa = $accountlist->accounts;
print_r($aa);
print "<br><br>";
$bb = $aa[0]->name;
print "<br>account<br>";
print $bb;
print "<br><br>";
list($acname,$accountid) = explode("/",$bb);

//個別の情報

$curl = curl_init("https://mybusinessaccountmanagement.googleapis.com/v1/accounts/{$accountid}");
curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);

print_r($response);
print "<br><br>";


//$readmask = "locationName,categories,primaryPhone,address.regionCode,address.postalCode,locationKey.placeId,labels,storeCode";
$readmask = "name,languageCode,storeCode,title,phoneNumbers,categories,websiteUri,metadata,relationshipData,labels";
$curl = curl_init("https://mybusinessbusinessinformation.googleapis.com/v1/accounts/{$accountid}/locations?readMask={$readmask}");
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);

print_r($response);
print "<br><br>";
exit;




$readmask = "name,languageCode,storeCode,title,phoneNumbers,categories,websiteUri,specialHours,serviceArea,profile";
$curl = curl_init("https://mybusinessaccountmanagement.googleapis.com/v1/accounts/{$accountid}/locations?readMask={$readmask}");
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);

print_r($response);
print "<br><br>";
exit;
$curl = curl_init("https://mybusinessaccountmanagement.googleapis.com/v4/accounts/{$accountid}/locations/13746540536059991173/reviews/");

curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);

print_r($response);
print "<br><br>";


exit;

curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");

/*
$data= array(
    'client_id' => 'my-new-client-id',
    'client_secret' => my-new-client-secret,
    'redirect_uri' => 'https://my-site.com',
    'grant_type' => 'authorization_code',
    'code' => $_GET['code']
);

$curl = curl_init('https://oauth2.googleapis.com/token');
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);

$tokens = json_decode($response);

*/


/*


$my_business_account = new \Google_Service_MyBusinessAccountManagement($client);

$list_accounts_response = $my_business_account->accounts->listAccounts();
print "<br>list_accounts_response<br>";
Var_dump($list_accounts_response);
print "<br><br>";
$accountsList = $my_business_account->accounts->listAccounts()->getAccounts();
print "<br>accountsList<br>";
print_r($accountsList);
print "<br><br>";
$account = $accountsList[0];
print "<br>account<br>";
print_r($account);
print "<br><br>";
print "<br>account->name<br>";
print $account->name;
print "<br><br>";
*/














$mybusinessService = new \Google_Service_MyBusinessQA($client);

$locations_questions = $mybusinessService->locations_questions;
$quetion = $locations_questions->Question;

print "<br>quetion<br>";
print_r($quetion);
print "<br><br>";
exit;
$locations_questions_answers = $mybusinessService->locations_questions_answers;
print "<br>locations_questions_answers<br>";
print_r($locations_questions_answers);
print "<br><br>";

exit;

$mybusinessService = new \Google_Service_MyBusinessLodging($client);

$locations = $mybusinessService->locations;
print "<br>locations<br>";
print_r($locations);
print "<br><br>";

$locations_lodging = $mybusinessService->locations_lodging;
print "<br>locations_lodging<br>";
print_r($locations_lodging);
print "<br><br>";


$mybusinessService = new \Google_Service_MyBusinessPlaceActions($client);

$locations_placeActionLinks = $mybusinessService->locations_placeActionLinks;
print "<br>locations_placeActionLinks<br>";
print_r($locations_placeActionLinks);
print "<br><br>";

$placeActionTypeMetadata = $mybusinessService->placeActionTypeMetadata;
print "<br>placeActionTypeMetadata<br>";
print_r($placeActionTypeMetadata);
print "<br><br>";








exit;


$mybusinessService = new \Google_Service_MyBusinessBusinessCalls($client);

$locations = $mybusinessService->locations;
print "<br>locations<br>";
print_r($locations);
print "<br><br>";

$locations_businesscallsinsights = $mybusinessService->locations_businesscallsinsights;
print "<br>locations_businesscallsinsights<br>";
print_r($locations_businesscallsinsights);
print "<br><br>";

$mybusinessService = new \Google_Service_MyBusinessBusinessInformation($client);

$accounts_locations = $mybusinessService->accounts_locations;
print "<br>accounts_locations<br>";
print_r($accounts_locations);
print "<br><br>";

$attributes = $mybusinessService->attributes;// Undefined variable
print "<br>attributes<br>";
print_r($locations);
print "<br><br>";

$categories = $mybusinessService->categories;
print "<br>categories<br>";
print_r($categories);
print "<br><br>";

$chains = $mybusinessService->chains;
print "<br>chains<br>";
print_r($chains);
print "<br><br>";

$googleLocations = $mybusinessService->googleLocations;
print "<br>googleLocations<br>";
print_r($googleLocations);
print "<br><br>";

$locations = $mybusinessService->locations;
print "<br>locations<br>";
print_r($locations);
print "<br><br>";

$locations_attributes = $mybusinessService->locations_attributes;
print "<br>locations_attributes<br>";
print_r($locations_attributes);
print "<br><br>";



exit;





$response = $mybusinessService->accounts_locations_reviews;
print_r($response);
print "<br><br>";
$lists = $response->listAccountsLocationsReviews($account->name,['pageSize' => '1']);
print_r($lists);


$queryParams = [
    "pageSize" => 10,
    'readMask' => "user.display_name,photo"
];
$locationsList = $locations->listAccountsLocations($account->name, $queryParams);
print "<br>locationsList<br>";
print_r($locationsList);


//$locations = $my_business_account->accounts_locations->listAccountsLocations($results['modelData']['accounts']['0']['name']);
//print "$locations<br><br>";
$response = $my_business_account->accounts_locations_reviews;
print_r($response);
print "<br><br>";
$lists = $response->listAccountsLocationsReviews($account,['pageSize' => '1']);
print_r($lists);


    // Get the first location in the locations array
$locations = $my_business_account->accounts_locations;
print_r($locations);
print "<br><br>";
$locationsList = $locations->listAccountsLocations($account->name)->getLocations();
print_r($locationsList);
print "<br><br>";
$location = $locationsList[0];
var_export($location);

















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
*/

$ybase->ST_PRI .= <<<HTML
</div>

HTML;

$ybase->HTMLfooter();
$ybase->priout();

////////////////////////////////////////////////
?>