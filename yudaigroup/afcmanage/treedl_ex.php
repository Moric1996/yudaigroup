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
$body = "id:$id\ntargetId:$targetId\npoint:$point";
mail("katsumata@yournet-jp.com","test","$body");
exit;
if(!$nodeid){
//	$ybase->error("error");
}
if(!$newtext){
//	$ybase->error("error");
}

$dirname = "/usr/local/htdocs/afc";
if($parentid){
	$dirname = $dirname."/".$parentid;
}


$deldir = array('.','..','app','css','asfs','webAd');

//���� $file_list2 �z���[N][1] �Ń\�[�g����֐�
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

print "id:$id<br>";
print "targetId:$targetId<br>";
print "point:$point<br>";



print "OK";
exit;

////////////////////////////////////////////////
?>