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
if(!preg_match("/^[0-9]+$/",$sourceid)){
	$ybase->error("error");
}
if(!preg_match("/^[0-9]+$/",$targetid)){
	$ybase->error("error");
}
if(!preg_match("/^[0-9a-zA-Z]+$/",$droppoint)){
	$ybase->error("error");
}
if(!$kind){
	$ybase->error("パラメーターエラー");
}
$body = "sourceid:$sourceid\ntargetid:$targetid\ndroppoint:$droppoint";
//mail("katsumata@yournet-jp.com","test","$body");

$conn = $ybase->connect();
$sql = "select company_id,parent_id,type,sortno from docu_manage where kind = $kind and company_id = $company_id and status = '1' and file_id = $sourceid";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("対象文書がありません");
}
list($s_company_id,$s_parent_id,$s_type,$s_sortno) = pg_fetch_array($result,0);

$sql = "select company_id,parent_id,type,sortno from docu_manage where kind = $kind and company_id = $company_id and status = '1' and file_id = $targetid";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("対象文書がありません");
}
list($t_company_id,$t_parent_id,$t_type,$t_sortno) = pg_fetch_array($result,0);

if($s_company_id != $t_company_id){
	$ybase->error("error");
}

if($droppoint == "top"){
	$sql = "update docu_manage set sortno = sortno + 2  where parent_id = $t_parent_id and sortno >= $t_sortno and kind = $kind and company_id = $company_id and status = '1'";
	$result = $ybase->sql($conn,$sql);
	$sql = "update docu_manage set sortno = $t_sortno , parent_id = $t_parent_id where file_id = $sourceid";
	$result = $ybase->sql($conn,$sql);

}elseif($droppoint == "bottom"){

	$sql = "update docu_manage set sortno = sortno + 2  where parent_id = $t_parent_id and sortno > $t_sortno and kind = $kind and status = '1' and company_id = $s_company_id";
	$result = $ybase->sql($conn,$sql);
	$new_sortno = $t_sortno + 1;
	$sql = "update docu_manage set sortno = $new_sortno , parent_id = $t_parent_id where file_id = $sourceid";
	$result = $ybase->sql($conn,$sql);

}elseif($droppoint == "append"){
	if($t_type != "1"){
		$ybase->error("error");
	}
	$sql = "select sortno from docu_manage where kind = $kind and and company_id = $company_id status = '1' and parent_id = $targetid order by sortno desc limit 1";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num == 0){
		$new_sortno = 2;
	}elseif($num){
		list($last_sortno) = pg_fetch_array($result,0);
		$new_sortno = $last_sortno + 2;
	}
	$sql = "update docu_manage set sortno = $new_sortno , parent_id = $targetid where file_id = $sourceid";
	$result = $ybase->sql($conn,$sql);
}

print "OK";
exit;

////////////////////////////////////////////////
?>