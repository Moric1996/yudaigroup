<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

$ybase->pm	= "";
$conn = $ybase->connect();

$sql = "delete from employee_in_data";
$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/new_employee.php");
exit;




////////////////////////////////////////////////
?>