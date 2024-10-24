<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

mb_language("Ja");
mb_internal_encoding("utf-8");
////////////エラーチェック

$error_flag = 0;
$space = array();

//////////////////////////////////////////////////
$conn = $ybase->connect();

$sql = "select employee_id,kana_name from employee_list order by employee_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
print "$num<br>";
for($i=0;$i<$num;$i++){
	list($q_employee_id,$q_kana_name) = pg_fetch_array($result,$i);
	$q_kana_name = trim($q_kana_name);
	$q_kana_name = mb_convert_kana($q_kana_name, "KVC");
	$q_kana_name = preg_replace('/　/', ' ', $q_kana_name);

	$sql2 = "update employee_list set kana_name='$q_kana_name' where employee_id = $q_employee_id";
	$result2 = $ybase->sql($conn,$sql2);

}

print "OK<br>";







////////////////////////////////////////////////
?>