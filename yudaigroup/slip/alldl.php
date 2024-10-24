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

$conn = $ybase->connect(3);



$zipfls[146001]=1;
$zipfls[146101]=1;
$zipfls[146701]=1;

$zipFileName = 'test.zip';
$zipDir = dirname(__FILE__) . '/tmp/zip/';

set_time_limit(0);

$zip = new ZipArchive();
$zipTmpDir = './tmp/zip/';

$result = $zip->open($zipTmpDir.$zipFileName, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
if( $result !== true ){ //エラー処理
	echo 'error!';
	exit();
}

foreach ($zipfls as $key => $val) {
	$attachno = substr($key, -2);
	$attachno = intval($attachno);
	$slip_no =  intval($key / 100);
	$filepath = "https://yournet-jp.com/yudaigroup/slip/dl.php?slip_id={$slip_no}&attach_no={$attachno}";
	$zip->addFile($filepath); 
}

$zip->close();

header('Content-Type: application/zip; name="'.$zipFileName.'"');
header('Content-Disposition: attachment; filename="'.$zipFileName.'"');
header('Content-Length: '.filesize($zipDir.$zipFileName));

ob_end_clean();
echo file_get_contents($zipDir.$zipFileName);
exit;
unlink($zipDir.$zipName);
exit;


////////////////////////////////////////////////
?>