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
//$ybase->session_get();

mb_internal_encoding("UTF-8");

$ybase->my_company_id = 5;

///////////////////////////////データ取得

$tardir = "$FTPdir"."/*";

$result = glob("$tardir");

$checktime = time() - 60 * 5;

foreach($result as $key => $filename){
	$updateDate = filemtime($filename);
	if($checktime > $updateDate){
		unlink($filename);
	}
}

exit;


////////////////////////////////////////////////
?>