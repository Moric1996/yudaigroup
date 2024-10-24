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
if(!$kind){
	$ybase->error("パラメーターエラー");
}

//$body = "sourceid:$sourceid\ntargetid:$targetid\ndroppoint:$droppoint";
//mail("katsumata@yournet-jp.com","test","$body");
$conn = $ybase->connect();
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
	list($q_sortno) = pg_fetch_array($result,0);
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

$sql = "insert into docu_manage (file_id,company_id,kind,parent_id,type,displayname,employee_id,sortno,add_date,status) values ($new_file_id,$company_id,$kind,$in_parent_id,'1','新規フォルダ',{$ybase->my_employee_id},$q_sortno,'now','1')";

$result = $ybase->sql($conn,$sql);

//header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/viewedit.php?kind=$kind&company_id=$company_id");
exit;

////////////////////////////////////////////////
?>