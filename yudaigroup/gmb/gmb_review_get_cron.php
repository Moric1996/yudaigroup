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
if(!isset($e_msg)){
	$e_msg = '';
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
//$ybase->session_get($sess0,$mei0);
//$edit_f = 1;
/////////////////////////////////////////
function err_mail($msg=""){
	global $guserid,$locationid;
	$my_email = "sys-katsumata@yournet-jp.com";
	$subject = "YUDAI GMB gmb_review_get_cron error";
	mail($my_email, $subject, $msg);
}

////////////////////////////////////////
//$ybase->make_shop_list();
//$ybase->shop_list['3001'] = "雄大ゴルフ熱函";	// 雄大ゴルフ熱函
//$ybase->shop_list['3002'] = "雄大ゴルフ清水町";	//雄大ゴルフ清水町
//$monthon = 1;//月単位で
//$ybase->make_employee_list("1");

$nowYMD = date("Y-m-d");

$company_id = 1;

$conn = $ybase->connect(2);

$add_sql = "";

$sql = "select distinct locationid from gmb_review_log where company_id = $company_id and to_char(add_date,'YYYY-MM-DD') = '$nowYMD' order by locationid";
$comp_locationids = array();
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$comp_locationids = $pg_fetch_all_columns($result,0);
}


$sql = "select distinct locationid from gmb_relation where company_id = $company_id order by locationid";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$e_msg = "データなし";
	$e_msg .= "\n$sql";
	err_mail($e_msg);
	exit;
}
$cnt = 0;
for($i=0;$i<$num;$i++){
	list($q_locationid) = pg_fetch_array($result,$i);
	if(in_array($q_locationid,$comp_locationids)){
		continue;
	}
	if($cnt > 4){
		continue;
	}
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, "https://yudai.info/yudaigroup/gmb/gmb_review_get.php?locationid={$q_locationid}"); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close ($ch);
print "$response";
$cnt++;
if($response != "OK"){//有効
	$e_msg = "更新失敗:$q_locationid\n";
	$e_msg .= "$response\n";
	err_mail($e_msg);
}
}

//////////////////////////////////////////////////////////////////////
echo "OK\n$cnt";
////////////////////////////////////////////////
?>