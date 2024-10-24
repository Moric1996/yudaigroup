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
include('./inc/ybase.inc');

$ybase = new ybase();

$JUMPSCRIPT = "login.php";

$conn = $ybase->connect();

if(!isset($_COOKIE[sess]) || !isset($_COOKIE[my_employee_id])){
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$JUMPSCRIPT}");
exit;
}
$sess = $_COOKIE[sess];
$my_employee_id = $_COOKIE[my_employee_id];

if(!preg_match("/^[0-9a-zA-Z_\.\-]+$/i",$sess)){
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$JUMPSCRIPT}");
exit;
}
if(!preg_match("/^[0-9]+$/i",$my_employee_id)){
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$JUMPSCRIPT}");
exit;
}
setcookie("sess","", time() + 8 * 3600, "/yudaigroup/");
setcookie("my_employee_id","", time() + 8 * 3600, "/yudaigroup/");

$sql = "delete from session where session_id = '$sess' and employee_id = $my_employee_id";
$result = $ybase->sql($conn,$sql);
$param = "sess=$sess&my_employee_id=$my_employee_id";

header("Location: https://yudai.info/yudaigroup/logout.php?$param");
exit;


////////////////////////////////////////////////
?>