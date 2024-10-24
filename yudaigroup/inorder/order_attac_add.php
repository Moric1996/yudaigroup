<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

include('./inorder_list.inc');

if(!preg_match("/^[0-9]+$/",$t_order_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}


$param = "t_order_id=$t_order_id";
/////////////////////////////////////////

$conn = $ybase->connect();



////////////////////////////////

$sql = "select array_to_json(attachfile) from order_main where order_id = $t_order_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	$ybase->error("データがありません");
}
list($q_attachfile) = pg_fetch_array($result,0);
$q_attachfile = json_decode($q_attachfile);
if(!$q_attachfile){
	 $q_attachfile = array();
}
////////////////////////////////
$uploaddir = '/home/yournet/yudai/inorder';
if(!file_exists($uploaddir)){
	mkdir($uploaddir, 0775);
}
$ttt=time().rand(1000,9999);

$filehead = $t_order_id."_".$ttt;
$dbinname = "";
$nn = 0;
foreach($_FILES["attachfile"]['name'] as $key => $val){
	$nn++;
	$ext = substr($val,strrpos($val,'.') + 1);
	$filehead0=$uploaddir."/".$filehead.$nn;
	$filenamesam=$filehead0."_thum.png";
	$filename=$filehead0.".$ext";
	if($val){
		array_push($q_attachfile,$filename);
		if(!move_uploaded_file($_FILES["attachfile"]['tmp_name'][$key],$filename)){
			$msg = "ファイルのアップロードに失敗しました。再度お試し下さい。ERROR_CODE:10891";
			$ybase->error("$msg");
		}
		if($key > 10){
			$msg = "ファイルの複数アップロードは10ファイルまでにしてください。ERROR_CODE:10892";
			$sbase->error("$msg");
		}
//	$ybase->thumbnail_make($filename,$filenamesam,$ext,$uploaddir,$filehead);

	}
}

$value = implode("','", $q_attachfile);
$dbinname = "ARRAY['".$value."']";

/////////////////////////////////
$sql = "update order_main set attachfile = $dbinname where order_id = $t_order_id";

$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/order_vw.php?{$param}");
exit;

////////////////////////////////////////////////
?>