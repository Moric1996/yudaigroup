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
$dirname = "/usr/local/htdocs/afc";
if($parentid){
	$dirname .= "/".$parentid;
}
$oldfname = "$dirname"."/"."$nodeid";


if(is_dir($oldfname)){

$deldir = array('.','..','');
$dir_h = opendir("$oldfname");

while (false !== ($file_list[] = readdir($dir_h))) ;
closedir( $dir_h ) ;

$file_list = array_diff($file_list,$deldir);

foreach($file_list as $file_name){
	unlink($oldfname."/".$file_name);
}
	rmdir($oldfname);
}else{
	unlink($oldfname);
}


print "OK";
exit;

////////////////////////////////////////////////
?>