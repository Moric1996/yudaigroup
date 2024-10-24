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
include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/slip.inc');

$ybase = new ybase();
$slip = new slip();

$ybase->session_get();
$ybase->my_company_id = 5;

$ybase->make_yournet_employee_list("1");
$slip->supplier_make();
/////////////////////////////////////////
foreach($slip_type_list as $key => $val){
	$slip->accept_list_make($key,0);
}

if(!preg_match("/^[0-9]+$/",$slip_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:17984");
}
if(!preg_match("/^[0-9]+$/",$attach_no)){
	$ybase->error("パラメーターエラー。ERROR_CODE:17985");
}

/////////////////////////////////////////

$conn = $ybase->connect(3);

//////////////////////////////////////////条件

$ybase->ST_PRI .= <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<link href="/yudaigroup/inc/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<a href="./view.php?slip_id=$slip_id&attach_no=$attach_no" target="_blank">
<img src="./view.php?slip_id=$slip_id&attach_no=$attach_no" border="0" width="300" class="img-thumbnail">
</a>
</body>
</html>

HTML;
$ybase->priout();
////////////////////////////////////////////////
?>