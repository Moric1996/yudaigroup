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
include(dirname(__FILE__).'/inc/slip.inc');

$ybase = new ybase();
$slip = new slip();

$ybase->session_get();
$ybase->my_company_id = 5;

$ybase->make_yournet_employee_list("1");
$slip->supplier_make();

$slip_id = trim($slip_id);
$accept_list_id = trim($accept_list_id);

if(!preg_match("/^[0-9]+$/",$slip_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$accept_list_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}
if(!preg_match("/^[0-9]+$/",$myaccept_count)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10823");
}

$conn = $ybase->connect(3);

$sql = "select slip_type,action_date,section_id,money,supplier,charge_emp,pay_date from slip where slip_id = $slip_id and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データエラー。ERROR_CODE:10823");
}
list($slip_type,$q_action_date,$q_section_id,$q_money,$q_supplier,$q_charge_emp,$q_pay_date) = pg_fetch_array($result,0);


$sql = "select accept_log_id from accept_log where slip_id = $slip_id and accept_list_id = $accept_list_id and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$sql = "update accept_log set employee_id = {$ybase->my_employee_id},add_date = 'now',accept_status = 1 where slip_id = $slip_id and accept_list_id = $accept_list_id and status = '1'";
	$result = $ybase->sql($conn,$sql);
}else{
	$sql = "insert into accept_log (accept_log_id,slip_id,accept_list_id,to_employee_id,send_flg,employee_id,accept_status,add_date,status) values (nextval('accept_log_id_seq'),$slip_id,$accept_list_id,null,'2',{$ybase->my_employee_id},'1','now','1')";
	$result = $ybase->sql($conn,$sql);
}

$sql = "select accept_list_id,accept_type,accept_name,send_employees from accept_list where company_id = {$ybase->my_company_id} and slip_type = $slip_type and section_id = 0 and status = '1' order by accept_jun,accept_list_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$check_flg = 0;
for($i=0;$i<$num;$i++){
	list($q_accept_list_id,$q_accept_type,$q_accept_name,$q_send_employees) = pg_fetch_array($result,$i);
	if($check_flg){
		$q_send_employees = trim($q_send_employees);
		if($q_send_employees){
			$q_send_employees_arr = json_decode($q_send_employees,true);
			foreach($q_send_employees_arr as $key => $val){
				$sql = "insert into accept_log (accept_log_id,slip_id,accept_list_id,to_employee_id,send_flg,employee_id,accept_status,add_date,status) values (nextval('accept_log_id_seq'),$slip_id,$q_accept_list_id,$val,'0',null,0,'now','1')";
				$result = $ybase->sql($conn,$sql);
			}
		}else{
			$q_send_employees_arr = array();
			$sql = "insert into accept_log (accept_log_id,slip_id,accept_list_id,to_employee_id,send_flg,employee_id,accept_status,add_date,status) values (nextval('accept_log_id_seq'),$slip_id,$q_accept_list_id,null,'2',null,0,'now','1')";
			$result = $ybase->sql($conn,$sql);
		}
		$check_flg = 0;
		break;
	}
	if($q_accept_list_id == $accept_list_id){
		$check_flg = 1;
	}
}

$sql = "select accept_list_id from accept_log where slip_id = $slip_id and accept_status = 1 and status = '1' group by accept_list_id";
$result2 = $ybase->sql($conn,$sql);
$num2 = pg_num_rows($result2);

if($num2 == $myaccept_count){
	$sql = "update slip set status = '2',up_date = 'now',last_accept_list_id = $accept_list_id where slip_id = $slip_id and status = '1'";
	$result2 = $ybase->sql($conn,$sql);
	$res_str="ALLOK";
	if($slip_type == 1){
//		$username = "katsumata";
		$username = $slack_user_name[$q_charge_emp];
		$slip->mess_channel = "@{$username}";
		$slip->mess_text .= "出金伝票の支払手続が完了しました。\\r\\n 伝票番号[<https://yournet-jp.com/yudaigroup/slip/slip_list.php?sel_slip_type={$slip_type}&sel_slip_id={$slip_id}|{$slip_id}>] {$q_action_date} ".$slip->supplier_list[$q_supplier]."\\r\\n";
		$slip->slack_send();
	}
}else{
	$sql = "update slip set up_date = 'now',last_accept_list_id = $accept_list_id where slip_id = $slip_id and status = '1'";
	$result2 = $ybase->sql($conn,$sql);
	$res_str="OK1";
}

if($result){
	print "$res_str";
}else{
	print "NG";
}

////////////////////////////////////////////////
?>