<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
if(isset($_POST)){
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
}
if(isset($_GET)){
	foreach($_GET as $key => $value){
		${$key} = $value;
	}
}
include(dirname(__FILE__).'/../inc/ybase.inc');

$ybase = new ybase();

$sess_id = $ybase->session_get();


if(!preg_match("/^[0-9]+$/",$ybase->my_employee_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:121921");
}
$sortp = print_r($sort, true);
//print "uiid:$uiid";
//print "sortp:$sortp";
//print "parentid:$parentid";
//print "senderid:$senderid";
$body = "uiid:$uiid\n";
$body .= "sortp:$sortp\n";
$body .= "parentid:$parentid\n";
$body .= "senderid:$senderid\n";
//mail("katsumata@yournet-jp.com","dashboard","$body");
$conn = $ybase->connect();
if($parentid == "sortable2"){
	if(!in_array($uiid,$sort, true)){
		print "OK";
		exit;
	}
	$sql = "delete from dashboard where employee_id = {$ybase->my_employee_id} and menu_id = $uiid";
	$result = $ybase->sql($conn,$sql);
}elseif($parentid == "sortable1"){
	if(!in_array($uiid,$sort, true)){
		print "OK";
		exit;
	}

$inflag = 0;
$sql = "select menu_id,jun from dashboard where employee_id = {$ybase->my_employee_id} and status = '1' order by jun";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

for($i=0;$i<$num;$i++){
	list($q_menu_id,$q_jun) = pg_fetch_array($result,$i);
	$card_jun_arr[$q_menu_id]=$q_jun;
	if($q_menu_id == $uiid){
		$inflag = 1;
	}
}
$pre_card_id=0;
$key_jun = 0;
$nex_jun = 1;
foreach($sort as $key => $val){
	if(!$val){
		continue;
	}
	if($val == $uiid){
		$key_card_id = $pre_card_id;
		if($card_jun_arr[$key_card_id]){
			$key_jun = $card_jun_arr[$key_card_id];
			$nex_jun = $card_jun_arr[$key_card_id] + 1;
		}
	}
	$pre_card_id = $val;
}

$sql = "update dashboard set jun = jun + 1 where employee_id = {$ybase->my_employee_id} and status = '1' and jun > $key_jun";
$result = $ybase->sql($conn,$sql);

if($inflag == 1){
	$sql = "update dashboard set jun = $nex_jun where employee_id = {$ybase->my_employee_id} and menu_id = $uiid and status = '1'";
}else{
	$sql = "insert into dashboard values (nextval('dashboard_id_seq'),{$ybase->my_employee_id},$uiid,null,null,$nex_jun,'now','1')";
}
$result = $ybase->sql($conn,$sql);
}

if($result){
	print "OK";
}else{
	print "NG";
}


////////////////////////////////////////////////
?>