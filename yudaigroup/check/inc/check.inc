<?php

////////////////////////////////////////////////////////////////////// START class check
class check extends ybase{



////////////////////////////////////////////////////////////////////// END function construct
public function category_make(){				//HTMLデータ作成
	$conn = $this->connect();
	$sql = "select category_id,cate_name from ck_category_list where status = '1' order by category_id";
	$result = $this->sql($conn,$sql);
	$num = pg_num_rows($result);
	$category_list = array();
	for($i=0;$i<$num;$i++){
		list($q_category_id,$q_cate_name) = pg_fetch_array($result,$i);
		$q_cate_name = trim($q_cate_name);
		if($q_cate_name){
			$category_list[$q_category_id] = $q_cate_name;
		}
	}
	return $category_list;
}
////////////////////////////////////////////////////////////////////// END category_make
public function item_make($category_id = ""){				//HTMLデータ作成
	$conn = $this->connect();
	$sql = "select item_id,category_id,item_name from ck_item_list where status = '1'";
	if(preg_match("/^[0-9]+$/",$category_id)){
		$sql .= " and category_id = $category_id";
	}
	$sql .= " order by category_id,item_id";
	$result = $this->sql($conn,$sql);
	$num = pg_num_rows($result);
	$item_list = array();
	for($i=0;$i<$num;$i++){
		list($q_item_id,$q_category_id,$q_item_name) = pg_fetch_array($result,$i);
		$item_list[$q_item_id] = $q_item_name;
		$this->item_cate[$q_item_id] = $q_category_id;
	}
	return $item_list;
}
////////////////////////////////////////////////////////////////////// END item_make
public function check_set_make($section_id = "",$ckset_id=""){				//HTMLデータ作成
	$conn = $this->connect();
	
	if(!preg_match("/^[0-9]+$/",$section_id)){
		return false;
	}
	if(preg_match("/^[0-9]+$/",$ckset_id)){
		$sql = "select ckset_id,array_to_json(subject_list),array_to_json(allot_list) from ck_check_set where ckset_id = $ckset_id";
	}else{
		$sql = "select ckset_id,array_to_json(subject_list),array_to_json(allot_list) from ck_check_set where section_id = '$section_id' and last_flag = 1 and status = '1' order by add_date desc limit 1";
	}
	$result = $this->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$this->error("まだ設定が完了していません。まず、設定を完了してください。ERROR_CODE:10001");
	}
	list($q_ckset_id,$q_subject_list,$q_allot_list) = pg_fetch_array($result,0);
	$subject_list_arr = json_decode($q_subject_list);
	$allot_list_arr = json_decode($q_allot_list);
	$this->target_ckset_id = $q_ckset_id;
	foreach($subject_list_arr as $key => $val){
		$this->allot_by_subject[$val] = $allot_list_arr[$key];
	}

	return $subject_list_arr;
}
////////////////////////////////////////////////////////////////////// END item_make
public function check_set_item_make($subject_list_arr = ""){				//HTMLデータ作成
	$conn = $this->connect();
	
	if(!$subject_list_arr){
		$aaa = array();
		return $aaa;
	}
	$subject_list_cate_arr = array();
	foreach($subject_list_arr as $key => $val){
		$sql = "select category_id,item_name from ck_item_list where item_id = $val";
		$result = $this->sql($conn,$sql);
		$num = pg_num_rows($result);
		if($num){
			list($q_category_id,$q_item_name) = pg_fetch_array($result,0);
		}
			$subject_list_cate_arr[$q_category_id][$val] = "$q_item_name";
	}
	return $subject_list_cate_arr;
}
////////////////////////////////////////////////////////////////////// END item_make
public function check_menu($menu_id = "",$shop_id = ""){
	$disabled = array();
	if(isset($menu_id)){
		$disabled[$menu_id] = " disabled";
	}
	if($this->protect == 1){
		$disabled[99] = " disabled";
		$disabled[1] = " disabled";
	}
					//HTMLデータ作成
$MENU_PR = <<<HTML
<div class="container">
<div class="row">
<div class="mx-auto">
<a class="btn btn-info btn-sm my-1{$disabled[7]}" href="./check_check.php" role="button">店舗チェック確認</a>
<a class="btn btn-info btn-sm my-1{$disabled[1]}" href="./check_in.php" role="button">店舗チェック入力</a>
<a class="btn btn-info btn-sm my-1{$disabled[2]}" href="./allshop_summary.php" role="button">サマリーページ</a>
<a class="btn btn-info btn-sm my-1{$disabled[3]}" href="./allshop_lastcheck.php" role="button">入力状況確認</a>
<a class="btn btn-info btn-sm my-1{$disabled[4]}" href="./vw_monthly.php" role="button">入力状況確認(月毎)</a>
<a class="btn btn-info btn-sm my-1{$disabled[5]}" href="./vw_shop.php?t_shop_id=$shop_id" role="button">店舗別入力推移</a>
<a class="btn btn-info btn-sm my-1{$disabled[6]}" href="./vw_shop_chart.php?t_shop_id=$shop_id" role="button">店舗別チャート</a>
<a class="btn btn-outline-info btn-sm my-1{$disabled[99]}" href="./set_top.php" role="button">設定</a>
</div>
</div>
</div>
HTML;

	return $MENU_PR;
}
////////////////////////////////////////////////////////////////////// END item_make


}
////////////////////////////////////////////////////////////////////// END class rank
?>