<?php

////////////////////////////////////////////////////////////////////// START class slip
include(dirname(__FILE__).'/scan_list.inc');
class slip extends ybase{
////////////////////////////////////////////////////////////////////// END function construct
public function supplier_make(){				//HTMLデータ作成
	$conn = $this->connect(3);
	$sql = "select supplier_id,kana,name,code from slip_supplier where company_id = 5 and count >= 0 and status = '1' order by count desc,kana,supplier_id";
	$result = $this->sql($conn,$sql);
	$num = pg_num_rows($result);
	$this->supplier_list = array();
	for($i=0;$i<$num;$i++){
		list($q_supplier_id,$q_kana,$q_name,$q_code) = pg_fetch_array($result,$i);
		$this->supplier_list[$q_supplier_id] = $q_name."[{$q_code}]";
		$this->supplier_code_list[$q_supplier_id] = $q_code;
	}
	return $this->supplier_list;
}
////////////////////////////////////////////////////////////////////// END supplier_make
public function accept_list_make($slip_type=0,$section_id=0){				//HTMLデータ作成
	$conn = $this->connect(3);
	$sql = "select accept_list_id,accept_type,accept_name,accept_employees,send_employees,d_jun,accept_jun from accept_list where company_id = 5 and slip_type = $slip_type and section_id = $section_id and status = '1' order by d_jun,accept_jun,accept_list_id";
	$result = $this->sql($conn,$sql);
	$num = pg_num_rows($result);
	for($i=0;$i<$num;$i++){
		list($q_accept_list_id,$q_accept_type,$q_accept_name,$q_accept_employees,$q_send_employees,$q_d_jun,$q_accept_jun) = pg_fetch_array($result,$i);
		$this->accept_list[$slip_type][$q_accept_list_id] = $q_accept_name;
		$this->accept_type_list[$slip_type][$q_accept_list_id] = $q_accept_type;
		$this->accept_employees_list[$slip_type][$q_accept_list_id] = $q_accept_employees;
		$this->accept_send_employees_list[$slip_type][$q_accept_list_id] = $q_send_employees;
		$this->accept_d_jun_list[$slip_type][$q_accept_list_id] = $q_d_jun;
		$this->accept_jun_list[$slip_type][$q_accept_list_id] = $q_accept_jun;
	}
	return $this->accept_list;
}
////////////////////////////////////////////////////////////////////// END accept_list_make
public function transfer_make(){				//HTMLデータ作成
	$conn = $this->connect(3);
	$sql = "select transfer_id,supplier_id,kind,bank_code,branch_code,b_type,account_no,bank_name,branch_name,account_holder,account_kana,com from slip_transfer where company_id = 5 and status = '1' order by transfer_id";
	$result = $this->sql($conn,$sql);
	$num = pg_num_rows($result);
	$this->transfer_list = array();
	for($i=0;$i<$num;$i++){
		list($q_transfer_id,$q_supplier_id,$q_kind,$q_bank_code,$q_branch_code,$q_b_type,$q_account_no,$q_bank_name,$q_branch_name,$q_account_holder,$q_account_kana,$q_com) = pg_fetch_array($result,$i);
		$this->transfer_list[$q_transfer_id] = $q_bank_name.$bankkind_list[$q_kind]."({$q_branch_name})".$banktype_list[q_b_type].$q_account_no.$q_account_holder;
;
	}
	return $this->transfer_list;
}
////////////////////////////////////////////////////////////////////// END supplier_make
public function check_position($position){				//HTMLデータ作成
	if(!$position){
		return 0;
	}elseif($position <= 50){
		return 1;
	}else{
		return 0;
	}
	return 0;
}
////////////////////////////////////////////////////////////////////// END check_position
public function group_const_make($t_shop_id=0,$t_month=0,$group_id=0){				//HTMLデータ作成
	if(!$t_month || !$t_shop_id){
		return;
	}
	$sql = "select group_const_id,group_id,employee_id from telecom_group_const where month=$t_month and shop_id=$t_shop_id and status = '1'";
	if($group_id){
		$sql .= " and group_id = $group_id";
	}
	$sql .= " order by group_id,group_const_id";
	$conn = $this->connect();

	$result = $this->sql($conn,$sql);
	$const_num = pg_num_rows($result);
	if(!$const_num){
		$this->error("チームの設定がされていません。先にチームの設定をしてください。");
	}
	$this->make_employee_list();

	for($i=0;$i<$const_num;$i++){
		list($q_group_const_id,$q_group_id,$q_employee_id) = pg_fetch_array($result,$i);
		$this->group_const_list[$q_employee_id] = $this->employee_name_list[$q_employee_id];
	}
	return;
}
////////////////////////////////////////////////////////////////////// END group_const_make
public function check_group_set($t_shop_id=0,$t_month=0,$t_employee_id=0){				//HTMLデータ作成
	if(!$t_month || !$t_shop_id || !$t_employee_id){
		return;
	}
	$sql = "select group_id from telecom_group where month=$t_month and shop_id=$t_shop_id and leader_employee_id = $t_employee_id and status = '1'";
	$conn = $this->connect();
	$result = $this->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num == 1){
		$group_id = pg_fetch_result($result,0,0);
	}else{
		return 0;
	}
	$sql = "select item_id from telecom_item where month=$t_month and shop_id=$t_shop_id and item_name <> '' and status = '1'";
	$result = $this->sql($conn,$sql);
	$item_num = pg_num_rows($result);
	if($item_num == 0){
		return 0;
	}
	$sql = "select item_id from telecom_goal_group where month=$t_month and shop_id=$t_shop_id and group_id = $group_id and status = '1'";
	$result = $this->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num2 != $item_num){
		return 0;
	}
	$sql = "select employee_id from telecom_group_const where month=$t_month and shop_id=$t_shop_id and group_id = $group_id and employee_id is not null and status = '1'";
	$result = $this->sql($conn,$sql);
	$group_cost_num = pg_num_rows($result);
	if($group_cost_num == 0){
		return 0;
	}
	$check = 0;
	for($i=0;$i<$group_cost_num;$i++){
		list($q_employee_id) = pg_fetch_array($result,$i);
		$sql = "select item_id from telecom_goal where month=$t_month and shop_id=$t_shop_id and employee_id = $q_employee_id and status = '1'";
		$result = $this->sql($conn,$sql);
		$num2 = pg_num_rows($result);
		if($num2 != $item_num){
			$check = $group_id;
		}
	}
	return $check;
}
////////////////////////////////////////////////////////////////////// END check_position
public function make_rank_employee_list($status = "",$auth = "",$section_id = "") {

$conn = $this->connect();
$wflag=0;
if(preg_match("/^[0-9]+$/",$status)){
	$add_sql = " where status = '$status'";
	$wflag=1;
}else{
	$add_sql = "";
}
$section_list = "";
foreach($rank_section_list as $key => $val){
	if($val == $section_id){
		if($section_list){
			$section_list .= ",";
		}
		$section_list .= "'$key'";
	}

}
if($section_list){
	if($wflag){
		$add_sql .= " and";
	}else{
		$add_sql .= " where";
	}
	$add_sql .= " section_id in ($section_list)";
}
if(preg_match("/^[0-9]+$/",$auth)){
	if($wflag){
		$add_sql .= " and";
	}else{
		$add_sql .= " where";
	}
	$add_sql .= " admin_auth = $auth";
}
$sql = "select employee_id,employee_name from employee_list{$add_sql} order by section_id,employee_id";

$result = $this->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
for($i=0;$i<$num;$i++){
	list($q_employee_id,$q_employee_name) = pg_fetch_array($result,$i);
	$employee_name_list[$q_employee_id] = $q_employee_name;
}
}
	return $employee_name_list;
}
////////////////////////////////////////////////////////////////////// END group_const_make
public function make_yobi($month = 0,$day = 0) {

	$YEAR = substr($month,0,4);
	$MONTH = intval(substr($month,4,2));
	$yobi = date("w",mktime(0,0,0,$MONTH,$day,$YEAR));
	if($day < 10){
		$day = "0"."$day";
	}
	$monthday = "$month"."$day";

	if($syukubi[$monthday]){
		$color = $weekcolor[7];
	}else{
		$color = $weekcolor[$yobi];
	}

	$hyouji = $weekname[$yobi];
	$tag = "<span style=\"color:$color;\">$hyouji</span>";

	return $tag;
}
////////////////////////////////////////////////////////////////////// END group_const_make
public function my_supplier_up($my_id=0){				//HTMLデータ作成
	$conn = $this->connect(3);
	$sql = "select supplier,count(*) from slip where charge_emp = $my_id and status = '2' group by supplier order by count(*)";
	$result = $this->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$this->supplier_list){
		$this->supplier_list = array();
	}
	for($i=0;$i<$num;$i++){
		list($q_supplier_id,$q_cnt) = pg_fetch_array($result,$i);
		if($this->supplier_list[$q_supplier_id]){
			$supplier_id = $q_supplier_id;
			$supplier_name = $this->supplier_list[$q_supplier_id];
			unset($this->supplier_list[$q_supplier_id]);
			$this->supplier_list = array($q_supplier_id => "$supplier_name") + $this->supplier_list;
		}
	}
	return $this->supplier_list;
}
////////////////////////////////////////////////////////////////////// END group_const_make
public function no_release_check($my_id="0"){
	$release_id_arr = array('10005','201201512','200416643');
	if(in_array("$my_id",$release_id_arr, true)){
		$ck_flag = "";
	}else{
		$ck_flag = "1";
	}
	return $ck_flag;
}
////////////////////////////////////////////////////////////////////// END no_release_check
public function slack_send() {

	$url = "https://hooks.slack.com/services/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";

//	$message = array(
//		"channel" => "{$this->mess_channel}",
//		"text" => "{$this->mess_text}"
//	);
/*
	$ch = curl_init();

	$options = array(
		CURLOPT_URL => $url,
// 返り値を文字列で返す
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
  // POST
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => http_build_query(array(
		'payload' => json_encode($message)
		))
	);

	curl_setopt_array($ch, $options);
	curl_exec($ch);
	curl_close($ch);
*/
	$cmd = "curl -X POST --data-urlencode \"payload={\\\"channel\\\": \\\"{$this->mess_channel}\\\",\\\"text\\\": \\\"{$this->mess_text}\\\"}\" $url";
	$res = exec($cmd);



	return $res;
}
////////////////////////////////////////////////////////////////////// END group_const_make
public function api_error($error_code = 400,$mess = "送信データに問題があります"){

//	header("HTTP/1.1 {$error_code} Bad Request");
	header("HTTP/1.1 200 OK");
	header("Content-Type: application/json; charset=utf-8");
	$result = array('code'=>$error_code, 'message'=>"$mess");
	echo json_encode($result);
	exit;
	return;
}
////////////////////////////////////////////////////////////////////// END api_error
public function api_success($error_code = 400,$mess = "送信完了"){

	header("HTTP/1.1 200 OK");
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode(array('code' =>200));
	exit;
	return;
}
////////////////////////////////////////////////////////////////////// END no_release_check


}
////////////////////////////////////////////////////////////////////// END class rank
?>