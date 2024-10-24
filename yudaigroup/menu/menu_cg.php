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

$conn = $ybase->connect();
if(!preg_match("/^[0-9]+$/",$tar_menu_id)){
	$ybase->error("パラメーターエラー");
}
//////////////////////////////////
if($campaney_all == "1"){
	$up_campaney = "'{0}'";
}elseif($sel_campaney){
	$up_campaney = "'{";
	$i=0;
	foreach($sel_campaney as $key => $val){
		if($i > 0){
			$up_campaney .= ",";
		}
		$up_campaney .= "$key";
		$i++;
	}
	$up_campaney .= "}'";
}else{
	$up_campaney .= "null";
}
///////////////////////////////////
if($section_all == "1"){
	$up_section = "'{0}'";
}elseif($sel_section){
	$up_section = "'{";
	$i=0;
	foreach($sel_section as $key => $val){
		if($i > 0){
			$up_section .= ",";
		}
		$up_section .= "$key";
		$i++;
	}
	$up_section .= "}'";
}else{
	$up_section .= "null";
}
//////////////////////////////////////
if($type_all == "1"){
	$up_type = "'{0}'";
}elseif($sel_type){
	$up_type = "'{";
	$i=0;
	foreach($sel_type as $key => $val){
		if($i > 0){
			$up_type .= ",";
		}
		$up_type .= "$key";
		$i++;
	}
	$up_type .= "}'";
}else{
	$up_type .= "null";
}
/////////////////////////////////////
if($position_all == "1"){
	$up_position = "'{0}'";
}elseif($sel_position){
	$up_position = "'{";
	$i=0;
	foreach($sel_position as $key => $val){
		if($i > 0){
			$up_position .= ",";
		}
		$up_position .= "$key";
		$i++;
	}
	$up_position .= "}'";
}else{
	$up_position .= "null";
}
///////////////////////////////////////
if(!$sel_admin){
	$sel_admin = 0;
}
$up_admin = "'{".$sel_admin."}'";
/////////////////////////////////////////
$menu_class = trim($menu_class);
$link = trim($link);
$list_no = trim($list_no);
$menu_name = trim($menu_name);
$menu_name = addslashes($menu_name);
if(!preg_match("/^[A-Za-z0-9_\-\.]+$/",$menu_class)){
	$menu_class = "";
}
if(!preg_match("/^[0-9]+$/",$list_no)){
	$list_no = 0;
}

/////////////////////////////////////////


$sql = "update menu set belong_id=$sel_belong,status='$sel_status',campaney_list=$up_campaney,section_list=$up_section,type_list=$up_type,position_list=$up_position,admin_list=$up_admin,menu_name='$menu_name',menu_class='$menu_class',link='$link',list_no=$list_no where menu_id = $tar_menu_id";

$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/menu_top.php");
exit;


////////////////////////////////////////////////
?>