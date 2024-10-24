<?php
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

$data['api_id'] = "yournetapi8987";
$data['api_pass'] = "kHDoji34gjsdHH";

$data['slip_type'] = 1;
$data['company_id'] = 5;
$data['section_id'] = 100;
$data['charge_emp'] = 10005;
$data['target_date'] = '2022-10-15';
$data['target_month'] = '2022-10';
$data['supplier_id'] = 3499;
$data['supplier_other'] = '';
$data['money'] = 95123;
$data['debit_code'] = 109;
$data['fee_st'] = '1';
$data['pay_date'] = '2022-10-30';
$data['all_biko'] = "test";
$data['no_release_flag'] = '0';

$file = "invoice_202209.pdf";

$atfile = file_get_contents("$file");

$atfile = base64_encode($atfile);

$data['attachfile']['body'][0] = $atfile;
$data['attachfile']['ext'][0] = "pdf";

$data = json_encode($data);

$API_URL = "https://yournet-jp.com/yudaigroup/slip/newslip_ex_api.php";

$options = array(
'http' => array(
'method'=> 'POST',
'header'=> 'Content-type: application/json; charset=UTF-8',
'content' => $data
)
);

$context = stream_context_create($options);

$response = file_get_contents("$API_URL", false, $context);
$get_data = json_decode($response, true);

print <<<HTML
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<body>
HTML;
print_r($get_data);

print "<br>OK";
print <<<HTML
</body>
</html>
HTML;



exit;











$headers = array(
	"Content-Type: application/x-www-form-urlencoded",
);
//    "Content-Type: application/json",

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,"$API_URL");
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
$response = curl_exec($ch);
curl_close ($ch);

$get_data = json_decode($response, true);

print_r($get_data);

print "OK";
exit;

////////////////////////////////////////////////
?>