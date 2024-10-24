<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

/////////////////////////////////////////

//$body = "var_name:$var_name\nvar_val:$var_val\nshop_id:$shop_id\ncategory:$category\nselyymm:$selyymm\npdca_id:$pdca_id\n";
//mail("katsumata@yournet-jp.com","test","$body");


if(!preg_match("/^[0-9]+$/",$shop_id)){
	$ybase->error("パラメーターエラーです。ERROR_CODE:29001");
}
if(!preg_match("/^[0-9]+$/",$category)){
	$ybase->error("パラメーターエラーです。ERROR_CODE:29002");
}
if(!$var_name){
	$ybase->error("パラメーターエラーです。ERROR_CODE:29003");
}
if(!$selyymm){
	$ybase->error("パラメーターエラーです。ERROR_CODE:29004");
}else{
	$yy = substr($selyymm,0,4);
	$mm = substr($selyymm,5,2);
}
if(!$yy || !$mm){
	$ybase->error("パラメーターエラーです。ERROR_CODE:29005");
}

$var_val = trim($var_val);
$var_val = addslashes($var_val);
$pdca_id = trim($pdca_id);

$in_attack = "";
$in_goal = "";
$in_charge = "";
$in_result = "";
$in_analysis = "";

switch ($var_name){
	case "attack":
		$in_attack = $var_val;
		break;
	case "goal":
		$in_goal = $var_val;
		break;
	case "charge":
		$in_charge = $var_val;
		break;
	case "result":
		$in_result = $var_val;
		break;
	case "analysis":
		$in_analysis = $var_val;
		break;
}
$conn = $ybase->connect();

if(preg_match("/^[0-9]+$/",$pdca_id)){
	$sql = "select pdca_id,pre_pdca_id,attack,goal,charge,result,analysis,add_date,status from manager_pdca where pdca_id = $pdca_id and shop_id = $shop_id and to_char(month,'YYYYMM') = '$yy$mm' and purpose_id = $category and status = '1' order by pdca_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
	if($num){
		$upsql = "update manager_pdca set $var_name = '$var_val' where pdca_id = $pdca_id";
	}
}
if(!$upsql){
	$sql = "select nextval('manager_pdca_id_seq')";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$ybase->error("データベースエラーです。ERROR_CODE:29006");
	}
	$new_pdca_id = pg_fetch_result($result,0,0);
	if(!$in_charge){$in_charge = "null";}
	$upsql = "insert into manager_pdca (pdca_id,shop_id,month,pre_pdca_id,purpose_id,attack,goal,charge,result,analysis,add_date,status) values ($new_pdca_id,$shop_id,'{$yy}-{$mm}-01',null,$category,'$in_attack','$in_goal',$in_charge,'$in_result','$in_analysis','now','1')";
}

$result = $ybase->sql($conn,$upsql);

if($result){
	if($new_pdca_id){
		$replay = "newpdcaid=$new_pdca_id";
	}else{
		$replay = "OK";
	}
	print "$replay";
}else{
	print "NG";
}
////////////////////////////////////////////////
?>