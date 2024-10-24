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

/////////////////////////////////////////

$conn = $ybase->connect();

//////////////////////////////////////////条件
$param = "t_shop_id=$t_shop_id&target_ckaction_id=$target_ckaction_id";
//////////////////////////////////////////
if(!isset($_FILES['uploadfile']['name'][0])){
	$ybase->error("ファイルが選択されていません");
}
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー");
}
if(!preg_match("/^[0-9]+$/",$t_ckaction_list_id)){
	$ybase->error("パラメーターエラー");
}
if(!preg_match("/^[0-9]+$/",$t_item_id)){
	$ybase->error("パラメーターエラー");
}
if(!preg_match("/^[0-9]+$/",$target_ckaction_id)){
	$target_ckaction_id = "";
}

$count = count($_FILES['uploadfile']['name']);
if($count > 5){
	$ybase->error("一度にアップできるファイルは5ファイルまでです");
}

$uploaddir = "/home/yournet/yudai/check/"."$t_shop_id"."/";
if(!file_exists($uploaddir)){
	mkdir($uploaddir, 0775);
}

/////////////////////データ取得
$sql = "select photo from ck_check_action_list where ckaction_list_id = $t_ckaction_list_id and section_id = '$t_shop_id' and item_id = $t_item_id and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データエラー");
}
list($q_photo) = pg_fetch_array($result,0);
$q_photo = trim($q_photo);
if($q_photo){
	$photo_arr = json_decode($q_photo,true);
	if($photo_arr){
		$no=max(array_keys($photo_arr));
	}else{
		$no=0;
	}
}else{
	$photo_arr = array();
	$no=0;
}
foreach((array)$_FILES['uploadfile']['name'] as $key2 => $val2){
	if($val2){
		$no ++;
		$ext = substr($val2,strrpos($val2,'.') + 1);
		$ext = strtolower($ext);
		$motofilename = substr($val2,0,strrpos($val2,'.'));
		$next_filename = "$t_ckaction_list_id_$no_".time().rand(1000,9999);
		$filename = "$uploaddir"."$next_filename"."."."$ext";
		if(!move_uploaded_file($_FILES['uploadfile']['tmp_name'][$key2],$filename)){
			$msg = "ファイルのアップロードに失敗しました。再度お試し下さい。ERROR_CODE:10885";
			$ybase->error("$msg");
		}
	array_push($photo_arr,"$filename");
	}
}
$photo_json=json_encode($photo_arr);

$sql = "update ck_check_action_list set photo = '$photo_json' where ckaction_list_id = $t_ckaction_list_id and section_id = '$t_shop_id' and item_id = $t_item_id";
$result = $ybase->sql($conn,$sql);


//$body = "t_ckaction_list_id:$t_ckaction_list_id\nt_shop_id:$t_shop_id\nphoto_json:$photo_json";
//mail("katsumata@yournet-jp.com","test","$body");

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/check_in.php?{$param}#li{$t_ckaction_list_id}");
exit;



////////////////////////////////////////////////
?>