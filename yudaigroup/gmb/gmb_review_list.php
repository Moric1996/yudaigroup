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
if(($ybase->my_section_id == "003")||($ybase->my_position_class <= 40)){
	$delokflag="1";
}else{
	$delokflag="";
}

$client = new \Google_Client();
//アプリケーションの名前

$client->setApplicationName("YUDAI GMB");

$client->setScopes(["https://www.googleapis.com/auth/business.manage"]);
//OAuth 2.0 クライアント ID認証情報が記載されたJSON
$client->setAuthConfig('client_secret_609763822407-lrcjjac0ipfvqj6hsd2vf6anaof96gcf.apps.googleusercontent.com.json');
$client->setAccessType("offline");



$gmbService = new \Google_Service_MyBusinessAccountManagement($client);
//アカウントリストを取得
$results = $gmbService->accounts->listAccounts();
//取得したアカウントリストの1番目から、ACCOUNT_NAMEというのを取得
$locations = $gmbService->accounts_locations->listAccountsLocations($results['modelData']['accounts']['0']['name']);
//ACCOUNT_NAME($locations)から1件だけレビューを取得
$response = $gmbService->accounts_locations_reviews;
//$lists = $response->listAccountsLocationsReviews($location,['pageSize' => '1']);
print_r($results);














$conn = $ybase->connect(2);
/*
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
/*
$ybase->title = "グーグルマイビジネス(ビジネスプロフィール)一覧";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML



<div class="container">

<p></p>


</div>




HTML;

$ybase->HTMLfooter();
$ybase->priout();
*/
////////////////////////////////////////////////
?>