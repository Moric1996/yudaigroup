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
$sql = "select employee_in_data_id,employee_num,employee_name,kana_name,sex,birthday,indate,company_id,section_id,employee_type,position_name,position_class,email from employee_in_data where comp_status = '0' and employee_num not in (select employee_num from employee_list where status = '1') order by employee_in_data_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

for($i=0;$i<$num;$i++){
	list($q_employee_in_data_id,$q_employee_num,$q_employee_name,$q_kana_name,$q_sex,$q_birthday,$q_indate,$q_company_id,$q_section_id,$q_employee_type,$q_position_name,$q_position_class,$q_email) = pg_fetch_array($result,$i);
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
	$q_email = trim($q_email);
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

	if(in_array($q_employee_in_data_id,$add_data_id,true)){
		$sql2 = "select nextval('employee_list_id_seq')";
		$result2 = $ybase->sql($conn,$sql2);
		$num2 = pg_num_rows($result2);
		if(!$num2){
			$ybase->error("データベースエラーです。ERROR_CODE:421512");
		}
		$new_employee_id = pg_fetch_result($result2,0,0);
		$sql2 = "insert into employee_list (employee_id,employee_num,employee_name,kana_name,sex,company_id,section_id,employee_type,position_name,position_class,admin_auth,email,pass,add_date,status) values ($new_employee_id,'$q_employee_num','$q_employee_name','$q_kana_name','$q_sex',$q_company_id,'$q_section_id',$q_employee_type,'$q_position_name',$q_position_class,$new_admin_auth,'$q_email','18da54','now','1')";
		$result2 = $ybase->sql($conn,$sql2);
//		print "$sql2<br>";

		$sql2 = "update employee_list set pass = crypt('$new_pass',gen_salt('md5')) where employee_id = $new_employee_id";
		$result2 = $ybase->sql($conn,$sql2);
//		print "$sql2<br>";

		$sql2 = "update employee_in_data set comp_status = '1' where employee_in_data_id = $q_employee_in_data_id";
		$result2 = $ybase->sql($conn,$sql2);
//		print "$sql2<br>";
	}else{
		$sql2 = "update employee_in_data set comp_status = '4' where employee_in_data_id = $q_employee_in_data_id";
		$result2 = $ybase->sql($conn,$sql2);
//		print "$sql2<br>";
	}
}

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/px_ck.php");
exit;


////////////////////////////////////////////////
?>