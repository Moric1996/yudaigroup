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
$company_id = 1;
/////////////////////////////////////////
if(!isset($_FILES['uploadfile']['name'][0])){
	$ybase->error("ファイルが選択されていません");
}
if(!$kind){
	$ybase->error("パラメーターエラー");
}

$count = count($_FILES['uploadfile']['name']);
if($count > 10){
	$ybase->error("一度にアップできるファイルは10ファイルまでです");
}

$conn = $ybase->connect();
$sql = "select max(filename) from docu_manage";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:25005");
}
$last_filename = pg_fetch_result($result,0,0);
$next_filename = $last_filename + 1;
$last_filename = sprintf("%06d", $last_filename);

$uploaddir = "/home/yournet/yudai/documanage/"."$kind"."/";
if(!file_exists($uploaddir)){
	mkdir($uploaddir, 0775);
}


//		print "$key => $val<br>";
$no=0;
foreach((array)$_FILES['uploadfile']['name'] as $key2 => $val2){
	$ext = substr($val2,strrpos($val2,'.') + 1);
	$ext = strtolower($ext);
	$motofilename = substr($val2,0,strrpos($val2,'.'));
	$next_filename += $no;
	$dbfilename = sprintf("%06d", $next_filename);
	$filename = "$uploaddir"."$dbfilename"."."."$ext";
	if($val2){
		$no ++;
//$body = $_FILES['uploadfile']['name']."\n".$_FILES['uploadfile']['tmp_name']."\n".$filename;
//mail("katsumata@yournet-jp.com","test","$body");
		if(!move_uploaded_file($_FILES['uploadfile']['tmp_name'][$key2],$filename)){
			$msg = "ファイルのアップロードに失敗しました。再度お試し下さい。ERROR_CODE:10885";
			$ybase->error("$msg");
		}
		if(($ext == 'mp4')||($ext == 'mov')||($ext == 'avi')||($ext == 'webm')||($ext == 'mpeg')||($ext == 'mpg')){
//			$motofilename = "📹".$motofilename;
		}elseif(($topdf_flag == '1')&&(($ext == 'doc')||($ext == 'docx')||($ext == 'xls')||($ext == 'xlsx')||($ext == 'jpg')||($ext == 'jpeg')||($ext == 'gif')||($ext == 'png')||($ext == 'txt'))){
			$ybase->file_to_pdf($filename);
			$ext = "pdf";
		}elseif(($ext == 'doc')||($ext == 'docx')||($ext == 'xls')||($ext == 'xlsx')||($ext == 'ppt')||($ext == 'pptx')){
//			$motofilename = "[DL]".$motofilename;
		}


$in_sortno = 1;
$in_parent_id = 0;
if(preg_match("/^[0-9]+$/",$insert_nodeid)){
	$sql = "select type,parent_id,sortno from docu_manage where file_id = $insert_nodeid and kind = $kind and company_id = $company_id and status = '1'";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num == 1){
		list($in_type,$in_parent_id,$in_sortno) = pg_fetch_array($result,0);
		if($in_type == "1"){
			$in_sortno = 1;
			$in_parent_id = $insert_nodeid;
		}elseif($in_type == "2"){
		if((!preg_match("/^[0-9]+$/",$in_parent_id)) || (!preg_match("/^[0-9]+$/",$in_sortno))){
			$insert_nodeid = 0;
			$in_sortno = 1;
			$in_parent_id = 0;
		}
		}else{
			$insert_nodeid = 0;
			$in_sortno = 1;
			$in_parent_id = 0;
		}
	}else{
		$insert_nodeid = 0;
	}
}else{
	$insert_nodeid = 0;
}





		$sql = "select sortno from docu_manage where kind = $kind and status = '1' and parent_id = $in_parent_id and sortno >= $in_sortno and company_id = $company_id order by sortno";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$q_sortno = 1;
		}else{
			$q_sortno = pg_fetch_result($result,0,0);
		}
		$sql = "update docu_manage set sortno = sortno + 1 where kind = $kind and status = '1' and parent_id = $in_parent_id and sortno >= $q_sortno and company_id = $company_id";
		$result = $ybase->sql($conn,$sql);

		$sql = "select nextval('docu_manage_file_id_seq')";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$ybase->error("データベースエラーです。ERROR_CODE:24002");
		}
		$new_file_id = pg_fetch_result($result,0,0);
		$sql = "insert into docu_manage (file_id,company_id,kind,parent_id,type,displayname,filename,employee_id,sortno,add_date,status,ext) values ($new_file_id,$company_id,$kind,$in_parent_id,'2','$motofilename','$dbfilename',{$ybase->my_employee_id},$q_sortno,'now','1','$ext')";
		$result = $ybase->sql($conn,$sql);
	}
}

//$body = "sourceid:$sourceid\ntargetid:$targetid\ndroppoint:$droppoint";
//mail("katsumata@yournet-jp.com","test","$body");

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/viewedit.php?kind=$kind&company_id=$company_id");
exit;

////////////////////////////////////////////////
?>