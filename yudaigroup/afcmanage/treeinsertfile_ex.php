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
if(!isset($_FILES['uploadfile']['name'][0])){
	$ybase->error("ファイルが選択されていません");
}

$count = count($_FILES['uploadfile']['name']);
if($count > 10){
	$ybase->error("一度にアップできるファイルは10ファイルまでです");
}
if(!$insert_nodeid){
	$ybase->error("パラメーターエラー");
}


$dirname = "/usr/local/htdocs/afc";
if($insert_parentid){
	$indirname = "$dirname"."/"."$insert_parentid";
}else{
	$indirname = "$dirname"."/"."$insert_nodeid";
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
$dir_h = opendir("$indirname");

while (false !== ($file_list[] = readdir($dir_h))) ;
closedir( $dir_h ) ;

//$file_list = array_diff($file_list,$deldir);
$file_list2 = array();


$uploaddir = "$indirname"."/";
if(!file_exists($uploaddir)){
	mkdir($uploaddir, 0775);
}


//		print "$key => $val<br>";
$no=0;
foreach((array)$_FILES['uploadfile']['name'] as $key2 => $val2){
	$ext = substr($val2,strrpos($val2,'.') + 1);
	$ext = strtolower($ext);
	$filename = "$uploaddir"."$val2";
	if($val2){
		$no ++;
//$body = $_FILES['uploadfile']['name']."\n".$_FILES['uploadfile']['tmp_name']."\n".$filename;
//mail("katsumata@yournet-jp.com","test","$body");
		if(!move_uploaded_file($_FILES['uploadfile']['tmp_name'][$key2],$filename)){
			$msg = "ファイルのアップロードに失敗しました。再度お試し下さい。ERROR_CODE:10885";
			$ybase->error("$msg");
		}

	}
}
//$body = "sourceid:$sourceid\ntargetid:$targetid\ndroppoint:$droppoint";
//mail("katsumata@yournet-jp.com","test","$body");

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/viewedit.php");
exit;

////////////////////////////////////////////////
?>