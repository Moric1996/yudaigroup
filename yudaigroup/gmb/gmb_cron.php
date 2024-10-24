<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
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
//$url = "https://yudai.info/yudaigroup/gmb/gmb_review_get_cron.php";
//$url = "https://yudai.info/yudaigroup/gmb/test.php";
$url = "https://test.yournet-jp.com/naoki/gmb/gmb_cron.php";

//	$ch = curl_init(); 
//	curl_setopt($ch, CURLOPT_URL, "$url"); 
//	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/html'));
//	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36');
//	curl_setopt($ch, CURLOPT_NOBODY, TRUE);
//	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
//	$response = curl_exec($ch);
//	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//	curl_close ($ch);

	$response = file_get_contents("$url");

	$msg="yournet-jp.com gmb_cron.php start $response $httpCode";
	$my_email = "katsumata@yournet-jp.com";
	$subject = "yournet_cron_yudai_gmb";
//	mail($my_email, $subject, $msg);

//////////////////////////////////////////////////////////////////////
echo "OK\n$httpCode\n";
////////////////////////////////////////////////
?>