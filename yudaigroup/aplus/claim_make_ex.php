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
///////////////////////////////////////////////////////////////
include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/aplus.inc');

$ybase = new ybase();
$aplus = new aplus();

////////////////////////////////////////////////define
if(!isset($shop_id)){
	$shop_id = '';
}
if(!isset($selyymm)){
	$selyymm = '';
}
if(!isset($selyymmdd)){
	$selyymmdd = '';
}
if(!isset($q_answer_count_arr)){
	$q_answer_count_arr = '';
}
if(!isset($yy)){
	$yy = '';
}
if(!isset($mm)){
	$mm = '';
}
if(!isset($param)){
	$param = '';
}
$ybase->session_get();

///////////////////////////////////////////////
//$dbno = $ybase->my_dbno;
//$edit_f = 1;
/////////////////////////////////////////

$now_yy = date('Y');
$now_mm = date('m');
$now_dd = date('d');


if(!isset($tar_claim_list)){
	$ybase->error("どれか１つ以上選択してください");
}
foreach($tar_claim_list as $key => $val){
	$campany_id = $campany_banktrans[$val];
	if(!preg_match("/^[0-9]+$/",$claim_money[$campany_id])){
		$ybase->error("金額を入れてください");
	}
}
if(!preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/",$next_target_date)){
	$ybase->error("口座振替日を入れてください");
}
if(!preg_match("/^[0-9]{4}\-[0-9]{2}$/",$target_month)){
	$ybase->error("対象月を入れてください");
}
$target_date = $target_month."-01";


$conn = $ybase->connect(4);

foreach($tar_claim_list as $key => $val){
	$campany_id = $campany_banktrans[$val];
	$price_intax = $claim_money[$campany_id];
	$price_notax = round($in_money / 1.1);
	$sql ="insert into claim (claim_id,claim_date,target_date,company_id,price_notax,price_intax,plan_date,paper_banktrans_id,add_date,status) values (nextval('claim_id_seq'),'now','$target_date',$campany_id,$price_notax,$price_intax,'$next_target_date',$val,'now','9')";
	$result = $ybase->sql($conn,$sql);
}


$JUMPSCRIPT = "aplus/claim_list.php?tar_month=$target_month";
header("Location: {$ybase->PATH}{$JUMPSCRIPT}");
exit;



$ybase->priout();
////////////////////////////////////////////////
?>