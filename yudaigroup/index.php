<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('./inc/ybase.inc');

$ybase = new ybase();

$ybase->session_get();

if($ybase->my_employee_id){
header("Location: https://".$_SERVER['HTTP_HOST']."/yudaigroup/portal/?$QUERY_STRING");
exit;

}
header("Location: https://yournet-jp.com/yudaigroup/login.php?$QUERY_STRING");
exit;
?>