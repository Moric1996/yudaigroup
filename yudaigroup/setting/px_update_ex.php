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
if(!isset($q_employee_in_data_id)){
	$q_employee_in_data_id = array();
}
//////////////////////////////////////////////////
$conn = $ybase->connect();

//未登録チェック

$sql = "select a.employee_in_data_id,a.employee_num,a.employee_name,a.kana_name,a.sex,a.birthday,a.indate,a.company_id,a.section_id,a.employee_type,a.position_name,a.position_class,a.email,b.employee_id,b.employee_name,b.kana_name,b.section_id,b.employee_type,b.position_name,b.position_class,b.email,b.admin_auth from employee_in_data as a inner JOIN employee_list as b ON a.employee_num = b.employee_num where a.comp_status = '0' and ((a.section_id <> b.section_id) or (a.employee_type <> b.employee_type) or (a.employee_name <> b.employee_name) or (a.kana_name <> b.kana_name))";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

for($i=0;$i<$num;$i++){
	list($q_employee_in_data_id,$q_employee_num,$q_employee_name,$q_kana_name,$q_sex,$q_birthday,$q_indate,$q_company_id,$q_section_id,$q_employee_type,$q_position_name,$q_position_class,$q_email,$now_employee_id,$now_employee_name,$now_kana_name,$now_section_id,$now_employee_type,$now_position_name,$now_position_class,$now_email,$now_admin_auth) = pg_fetch_array($result,$i);
	$q_employee_num = trim($q_employee_num);
	$q_employee_name = trim($q_employee_name);
	$q_kana_name = trim($q_kana_name);
	$q_sex = trim($q_sex);
	$q_birthday = trim($q_birthday);
	$q_indate = trim($q_indate);
	$q_company_id = trim($q_company_id);
	$q_section_id = trim($q_section_id);
	$q_employee_type = trim($q_employee_type);
	$q_position_name = trim($q_position_name);
	$q_position_class = trim($q_position_class);
	$now_employee_name = trim($now_employee_name);
	$now_kana_name = trim($now_kana_name);
	$now_section_id = trim($now_section_id);
	$now_employee_type = trim($now_employee_type);
	$now_position_name = trim($now_position_name);
	$now_position_class = trim($now_position_class);
	$now_email = trim($now_email);
	$now_admin_auth = trim($now_admin_auth);

	if($in_position_class[$q_employee_in_data_id]){
			$q_position_class = $in_position_class[$q_employee_in_data_id];
	}
	switch ($q_position_class){
		case 10:
		case 20:
		case 30:
			$new_admin_auth = 1;
			break;
		case 40:
			$new_admin_auth = 2;
			break;
		case 50:
			$new_admin_auth = 3;
			break;
		default:
			$new_admin_auth = 0;
	}
	if(!$q_position_class){
		$q_position_class = 'null';
	}
	if($in_admin_auth[$q_employee_in_data_id]){
			$new_admin_auth = $in_admin_auth[$q_employee_in_data_id];
	}

	if(in_array($q_employee_in_data_id,$update_data_id,true)){
		$sql2 = "update employee_list set employee_name = '$q_employee_name',kana_name = '$q_kana_name',section_id = '$q_section_id',employee_type = $q_employee_type,position_class = $q_position_class,admin_auth = $new_admin_auth where employee_id = $now_employee_id";
		$result2 = $ybase->sql($conn,$sql2);
//		print "$sql2<br>";

		$sql2 = "update employee_in_data set comp_status = '2' where employee_in_data_id = $q_employee_in_data_id";
		$result2 = $ybase->sql($conn,$sql2);
//		print "$sql2<br>";
	}else{
		$sql2 = "update employee_in_data set comp_status = '5' where employee_in_data_id = $q_employee_in_data_id";
		$result2 = $ybase->sql($conn,$sql2);
//		print "$sql2<br>";
	}
}

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/px_ck.php");
exit;


////////////////////////////////////////////////
?>