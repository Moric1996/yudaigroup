<?php

////////////////////////////////////////////////////////////////////// START class rank
class rank extends ybase{



////////////////////////////////////////////////////////////////////// END function construct
public function unitname_make($shop_id=0,$t_month=0){				//HTMLデータ作成
	$conn = $this->connect();
	$sql = "select item_id,u_name from telecom_unitname where shop_id = $shop_id and month = $t_month and status = '1' order by item_id";
	$result = $this->sql($conn,$sql);
	$num = pg_num_rows($result);
	$this->unitname_list = array();
	for($i=0;$i<$num;$i++){
		list($q_item_id,$q_u_name) = pg_fetch_array($result,$i);
		$this->unitname_list[$q_item_id] = $q_u_name;
	}
	$sql = "select item_id from telecom_item where shop_id = $shop_id and month = $t_month and status = '1' order by item_id";
	$result = $this->sql($conn,$sql);
	$num = pg_num_rows($result);
	for($i=0;$i<$num;$i++){
		list($q_item_id) = pg_fetch_array($result,$i);
		if(!$this->unitname_list[$q_item_id]){
			$this->unitname_list[$q_item_id] = "件";
		}
	}
	return;
}
////////////////////////////////////////////////////////////////////// END unitname_make
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
include('rank_list.inc');

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
include('rank_list.inc');

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


}
////////////////////////////////////////////////////////////////////// END class rank
?>