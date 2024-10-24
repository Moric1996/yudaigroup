<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

//$kind = 1;
$company_id = 5;
/////////////////////////////////////////
$body = "nodeid:$nodeid\nnewtext:$newtext\n";
//mail("katsumata@yournet-jp.com","test","$body");
if(!$nodeid){
	$ybase->error("error");
}
if(!$newtext){
	$ybase->error("error");
}

$dirname = "/usr/local/htdocs/afc";
if($parentid){
	$dirname = $dirname."/".$parentid;
}


$deldir = array('.','..','app','css','asfs','webAd');

//引数 $file_list2 配列の[N][1] でソートする関数
function order_by_desc($a, $b){
	if ( strtotime($a[1]) > strtotime($b[1]) ){
		return -1;
	}elseif(strtotime($a[1]) < strtotime($b[1])) {
		return 1;
	}else{
		return 0;
	}
}

/////////////////////////////////////////
$dir_h = opendir("$dirname");

while (false !== ($file_list[] = readdir($dir_h))) ;
closedir( $dir_h ) ;

//$file_list = array_diff($file_list,$deldir);
$file_list2 = array();

$i = 0 ;
$no = 0;
foreach($file_list as $file_name){
	if($file_name == $newtext){
		$ybase->error("error");
	}
	$i++ ;
}

$oldfname = $dirname."/"."$nodeid";
$newfname = $dirname."/"."$newtext";

rename($oldfname,$newfname);

print "OK";
exit;

////////////////////////////////////////////////
?>