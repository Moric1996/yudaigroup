<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
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
include('../inc/ybase.inc');
include(dirname(__FILE__).'/../../camp/inc/campbase.inc');

$ybase = new ybase();
$campbase = new campbase();

$ybase->session_get();
if(!preg_match("/^[0-9]+$/",$camp_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:19202");
}
if(!preg_match("/^[0-9]+$/",$sourcetype)){
	$ybase->error("パラメーターエラー。ERROR_CODE:192102");
}
$param = "camp_id=$camp_id";
//$edit_f = 1;
/////////////////////////////////////////
if(($ybase->my_section_id == "003")||($ybase->my_position_class <= 40)){
	$delokflag="1";
}else{
	$delokflag="";
}
$conn = $ybase->connect(1);
$emp_arr = array();

if($sourcetype == 1){
	$sql = "select employee_id,employee_name from employee_list where status = '1' order by employee_id";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
		$emp_arr = pg_fetch_all_columns($result,1);
	}else{
		$emp_arr = array();
	}

}elseif($sourcetype == 2){
	$name_list = mb_ereg_replace("[\r|\n|\r\n]+","\n",$name_list);

	$emp_arr=array();
	$emp_arr = explode("\n","$idlist");

}else{
header("Location: https://".$_SERVER['HTTP_HOST']."/yudaigroup/camp/emp_fm.php?$param");
exit;

}
foreach($emp_arr as $key => $val){
	$val = mb_ereg_replace("[\r|\n|\r\n| |　]+","",$val);
	$emp_arr[$key] = $val;
}
print "emp:$num<br>";
print_r($emp_arr);
print "<br>";

$conn = $ybase->connect(2);

$sql = "select regist_id,name from stamp_regist where camp_id = $camp_id and staff_flag = '0' and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

$regi_name_arr = array();
for($i=0;$i<$num;$i++){
	list($q_regist_id,$q_name) = pg_fetch_array($result,0);
	$q_name = stripslashes($q_name);
	$q_name = mb_ereg_replace("[\r|\n|\r\n|\s]+","",$q_name);
	$regi_name_arr[$q_regist_id] = "$q_name";
}
print "regist:$num<br>";
print_r($regi_name_arr);
print "<br>";

$new_arr = array_intersect($regi_name_arr,$emp_arr);
$cnt = count($new_arr);
print "count:$cnt<br>";

foreach($new_arr as $key => $val){
	$sql = "update stamp_regist setstaff_flag = '1' where regist_id = $key";
	print "$sql<br>";
//	$result = $ybase->sql($conn,$sql);
}

//header("Location: https://".$_SERVER['HTTP_HOST']."/yudaigroup/camp/regi_list.php?$param");
exit;


////////////////////////////////////////////////
?>