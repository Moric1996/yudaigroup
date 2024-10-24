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

$ybase->my_company_id = 5;

$ybase->make_yournet_employee_list("1");
$slip->supplier_make();
/////////////////////////////////////////
foreach($slip_type_list as $key => $val){
	$slip->accept_list_make($key,0);
}

/////////////////////////////////////////

$conn = $ybase->connect(3);


//////////////////////////////////////////

$sql = "select to_employee_id,count(*),array_to_json(array_agg(slip_id)) from accept_log where add_date > '2022-10-05' and status = '1' group by to_employee_id order by to_employee_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

for($i=0;$i<$num;$i++){
	list($to_employee_id,$q_count,$q_arr_slip_id) = pg_fetch_array($result,$i);
	$json_slip_id = json_decode($q_arr_slip_id, true);
	$slip_id_arr = array_unique($json_slip_id);
	$username = $slack_user_name[$to_employee_id];
	$slip->mess_channel = "@{$username}";
	$slip->mess_text = "伝票の手続・承認をお願いします。({$q_count}件)<https://yournet-jp.com/yudaigroup/slip/slip_list.php|ここをクリック>\\r\\n";
	foreach($slip_id_arr as $key => $val){
		$sql3 = "select slip_type,action_date,section_id,money,supplier,charge_emp,pay_date from slip where slip_id = $val";
		$result3 = $ybase->sql($conn,$sql3);
		$num3 = pg_num_rows($result3);
		if($num3){
			list($q_slip_type,$q_action_date,$q_section_id,$q_money,$q_supplier,$q_charge_emp,$q_pay_date) = pg_fetch_array($result3,0);
			$slip->mess_text .= "伝票番号[<https://yournet-jp.com/yudaigroup/slip/slip_list.php?sel_slip_type={$q_slip_type}&sel_slip_id={$val}|{$val}>] ".$slip_type_list[$q_slip_type]." {$q_action_date} ".$slip->supplier_list[$q_supplier]."\\r\\n";
		}
	}

print "<html lang=\"ja\"><head><meta charSet=\"utf-8\"/></head><body>";
print $slip->mess_text;
print "</body></html>";

//	$slip->slack_send();
	$sql2 = "update accept_log set send_flg = '1' where to_employee_id = $to_employee_id and send_flg = '0' and status = '1'";
//	$result2 = $ybase->sql($conn,$sql2);
}

$youbi = date('w');
$HHH = date('H');
$iii = date('i');
if(($youbi == "0")||($youbi == "6")){
	print "OK";
	exit;
}elseif($youbi == "5"){
	$nokori = 4;
}else{
	$nokori = 3;
}
if(($HHH < 9)||($HHH > "18")){
	print "OK";
	exit;
}
if(($iii > 5)&&($iii < 30)){
	print "OK";
	exit;
}elseif(($iii > 35)&&($iii < 59)){
	print "OK";
	exit;
}
print "OK";
exit;


$sql = "select a.slip_id,a.slip_type,a.supplier,b.accept_status,EXTRACT(EPOCH FROM a.pay_date - current_timestamp) / (60 * 60 * 24),a.pay_date FROM slip as a LEFT JOIN accept_log as b ON a.slip_id = b.slip_id and b.accept_list_id = 6 and b.status = '1' where a.slip_type = 1 and a.status = '1' and EXTRACT(EPOCH FROM a.pay_date - current_timestamp) / (60 * 60 * 24) < $nokori order by slip_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$add_text = "";
for($i=0;$i<$num;$i++){
	list($q_slip_id,$q_slip_type,$q_supplier_id,$q_accept_status,$q_limitdays,$q_pay_date) = pg_fetch_array($result,$i);
	$q_accept_status = trim($q_accept_status);
	if($q_accept_status <> 1){
		$add_text .= "伝票番号[<https://yournet-jp.com/yudaigroup/slip/slip_list.php?sel_slip_type={$q_slip_type}&sel_slip_id={$val}|{$q_slip_id}>]".$slip->supplier_list[$q_supplier_id]." {$q_pay_date}まで\\r\\n";
	}
}
if($add_text){
	$slip->mess_channel = "@katsumata @m-akiyama @kondo";
	$slip->mess_text = "支払期限が迫っているデータ未承認の支払伝票があります。\\r\\n".$add_text."<https://yournet-jp.com/yudaigroup/slip/slip_list.php|ここをクリック>";
//	$slip->slack_send();
}

print "OK";
exit;

////////////////////////////////////////////////
?>