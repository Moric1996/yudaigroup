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
$dirname = "/usr/local/htdocs/afc";
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
$maxno = 0;
$no = 0;
foreach($file_list as $file_name){
	if(preg_match("/^(新しいフォルダ)(.+)/",$file_name,$str)){
		$no = $str[2];
		if($no > $maxno){
			$maxno = $no;
		}
	}
	$i++ ;
}
$maxno++;

$newfname = "新しいフォルダ"."$maxno";

mkdir("$dirname"."/"."$newfname", 0755);



//$body = "sourceid:$sourceid\ntargetid:$targetid\ndroppoint:$droppoint";
//mail("katsumata@yournet-jp.com","test","$body");

//header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/viewedit.php?kind=$kind&company_id=$company_id");
exit;

////////////////////////////////////////////////
?>