<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/check.inc');
include('./inc/check_list.inc');

$ybase = new ybase();
$check = new check();
$ybase->session_get();

$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;

$category_list = $check->category_make();
$item_list = $check->item_make();

$conn = $ybase->connect();
$param = "t_shop_id=$t_shop_id&target_ckaction_id=$target_ckaction_id";
//////////////////////////////////////////
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー");
}
if(!preg_match("/^[0-9]+$/",$t_ckaction_list_id)){
	$ybase->error("パラメーターエラー");
}
if(!preg_match("/^[0-9]+$/",$pno)){
	$ybase->error("パラメーターエラー");
}

$uploaddir = "/home/yournet/yudai/check/"."$t_shop_id"."/";

///////////////////////////////データ取得
$sql = "select photo from ck_check_action_list where ckaction_list_id = $t_ckaction_list_id and section_id = '$t_shop_id' and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データエラー");
}
list($q_photo) = pg_fetch_array($result,0);
$q_photo = trim($q_photo);
if($q_photo){
	$photo_arr = json_decode($q_photo,true);
	$filename = $photo_arr[$pno];
}else{
	$filename = "/usr/local/htdocs/yudairgoup/check/image/noimage.png";
}


$ext = strtolower(substr($filename,strrpos($filename,'.') + 1));
$content_type = $content_type_list[$ext];
if(!$content_type){
	$ybase->error("ファイル種類エラー。ERROR_CODE:10986");
}



if($thum){
	switch ($ext){
		case "png":
		$original_image = imagecreatefrompng($filename);
			break;
		case "jpg":
		case "jpeg":
		$original_image = imagecreatefromjpeg($filename);
			break;
		case "gif":
		$original_image = imagecreatefromgif($filename);
			break;

	}

	list($original_width, $original_height) = getimagesize($filename);
	$thumb_width = $thum;
	$thumb_height = $original_height*$thumb_width/$original_width;

	$thumb_image = imagecreatetruecolor($thumb_width, $thumb_height);
	imagecopyresized($thumb_image, $original_image,0,0,0,0,$thumb_width,$thumb_height,$original_width, $original_height);

header("Content-Type: image/png");
	imagepng($thumb_image,$thumbnail_file);
	imagedestroy($thumb_image);
}else{
	$handle = fopen($filename, "r");
	$contents = fread($handle, filesize($filename));
	fclose($handle);
header("Content-Type: $content_type");
	echo $contents;
}


exit;

////////////////////////////////////////////////
?>