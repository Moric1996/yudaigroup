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
mb_language("Japanese");
mb_internal_encoding("UTF-8");

include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/scan.inc');


$ybase = new ybase();
$ybase->session_get();

mb_internal_encoding("UTF-8");

$ybase->my_company_id = 5;

if(!$tarfile){
	$ybase->error("パラメーターエラー。ERROR_CODE:17981");
}

///////////////////////////////データ取得

//print "$tarfile<br>";
$filename = $FTPdir."/".$tarfile;
//print "$filename<br>";

 unlink($filename);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/scan_list.php?{$param}");
exit;


////////////////////////////////////////////////
?>