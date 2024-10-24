<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

////////////エラーチェック

//print "var_name:{$var_name}<br>";
//print "var_val:{$var_val}<br>";
//print "target_order_id:{$target_order_id}<br>";
//print "target_tool_id:{$target_tool_id}<br>";
//print "target_shooting_id:{$target_shooting_id}<br>";
//print "target_check:{$target_check}<br>";


$var_val = trim($var_val);
$var_val = preg_replace('/　/', ' ', $var_val);
mb_language("Ja");
mb_internal_encoding("utf-8");
//$var_val = mb_convert_kana($var_val, "n");
if(!preg_match("/^[0-9]+$/",$target_order_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!$var_name){
	$ybase->error("パラメーターエラー。ERROR_CODE:10823");
}

$var_val = addslashes($var_val);
$colname = $var_name;
if(preg_match("/^([a-zA-Z0-9_]+)\[([0-9]+)\]$/i",$var_name,$str)){
	$colname = $str[1];
	$tar_val = $str[2];
	$tar_arr = array($str[2]);
}
$conn = $ybase->connect();

$addsql = "";
//print "col:$colname<br>";
switch ($colname){
	case "order_date":
	case "delivery_date":
	case "photo_date":
	case "delivery_plan_date":
		$tablename = "order_main";
		if(!$var_val){
			$value = "null";
		}else{
			$value = "'$var_val'";
		}
		break;
	case "invoice_date":
		$tablename = "order_main";
		if(!$var_val){
			$value = "null";
		}else{
			$value = "'{$var_val}-01'";
		}
		break;
	case "invoice_date_yy":
		$tablename = "order_main";
		$sql = "select to_char(invoice_date,'MM') from $tablename where order_id = $target_order_id";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$ybase->error("error");
		}
		$q_invoice_date_mm = pg_fetch_result($result,0,0);
		$invoice_date = "$var_val"."-"."$q_invoice_date_mm"."-01";
		$colname = "invoice_date";
		$value = "'{$invoice_date}'";
		break;
	case "invoice_date_mm":
		$tablename = "order_main";
		$sql = "select to_char(invoice_date,'YYYY') from $tablename where order_id = $target_order_id";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$ybase->error("error");
		}
		$q_invoice_date_yy = pg_fetch_result($result,0,0);
		$invoice_date = "$q_invoice_date_yy"."-".sprintf("%02d", $var_val)."-01";
		$colname = "invoice_date";
		$value = "'{$invoice_date}'";
		break;
	case "shop_id":
	case "order_emp_id":
	case "approval":
	case "mg_emp_id":
	case "sales_emp_id":
		$tablename = "order_main";
		if(!$var_val){
			$value = "null";
		}else{
			$value = "$var_val";
		}
		break;
	case "photo_place":
	case "product_other":
	case "all_biko":
		$tablename = "order_main";
		$value = "'$var_val'";
		break;
	case "change_status":
		$sql = "insert into order_main_log (order_main_log_id,order_id,change_emp_id,add_date,nex_status) values (nextval('order_main_log_id_seq'),$target_order_id,'{$ybase->my_employee_id}','now','{$var_val}')";
		$result = $ybase->sql($conn,$sql);

		$colname = "status";
		$tablename = "order_main";
		$value = "'$var_val'";
		break;
	case "product_id":
		$tablename = "order_main";
		$sql = "select array_to_json(product_id) from $tablename where order_id = $target_order_id";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$ybase->error("error");
		}
		$q_product_id = pg_fetch_result($result,0,0);
		$arr_product_id = json_decode($q_product_id);
		if(!$arr_product_id){
			$arr_product_id = array();
		}
		if($target_check == "true"){
			$new_arr = array_merge($arr_product_id,$tar_arr);
			$new_arr = array_unique($new_arr);
		}else{
			$new_arr = array_diff($arr_product_id,$tar_arr);
		}
		$value = "";
		$value = implode(",", $new_arr);
		if($value){
			$value = "ARRAY[".$value."]";
		}else{
			$value = "null";
		}
		break;
	case "photo_start":
	case "photo_end":
		$tablename = "order_main";
		$sql = "select to_char(photo_date,'YYYY-MM-DD') from $tablename where order_id = $target_order_id";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$ybase->error("error");
		}
		$q_photo_date = pg_fetch_result($result,0,0);
		$q_photo_date = trim($q_photo_date);
		if(!$q_photo_date){
			$ybase->error("先に撮影入り可能日を設定してください。");
		}
		if(!$var_val){
			$value = "null";
		}else{
			$value = "'{$q_photo_date} {$var_val}:00'";
		}
		break;
	case "tool_name":
	case "delivery":
	case "toolbiko":
	case "laminate":
		$tablename = "order_tool";
		$addsql = " and num = $tar_val";
		if($target_tool_id){
			$addsql .= " and tool_id = $target_tool_id";
		}
		$sql = "select tool_id from $tablename where order_id = $target_order_id{$addsql}";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$sql = "select nextval('order_tool_id_seq')";
			$result = $ybase->sql($conn,$sql);
			$num0 = pg_num_rows($result);
			if(!$num0){
				$ybase->error("データベースエラーです。ERROR_CODE:23002");
			}
			$new_tool_id = pg_fetch_result($result,0,0);
			$sql = "insert into order_tool (tool_id,order_id,num,add_date,status) values ($new_tool_id,$target_order_id,$tar_val,'now','1')";
			$result = $ybase->sql($conn,$sql);
			$addsql = " and num = $tar_val and tool_id = $new_tool_id";
		}
		$value = "'$var_val'";
		break;
	case "quantity":
	case "papersize":
	case "tool_emp_id":
		$tablename = "order_tool";
		$addsql = " and num = $tar_val";
		if($target_tool_id){
			$addsql .= " and tool_id = $target_tool_id";
		}
		$sql = "select tool_id from $tablename where order_id = $target_order_id{$addsql}";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$sql = "select nextval('order_tool_id_seq')";
			$result = $ybase->sql($conn,$sql);
			$num0 = pg_num_rows($result);
			if(!$num0){
				$ybase->error("データベースエラーです。ERROR_CODE:23002");
			}
			$new_tool_id = pg_fetch_result($result,0,0);
			$sql = "insert into $tablename (tool_id,order_id,num,add_date,status) values ($new_tool_id,$target_order_id,$tar_val,'now','1')";
			$result = $ybase->sql($conn,$sql);
			$addsql = " and num = $tar_val and tool_id = $new_tool_id";
		}
		$var_val = mb_convert_kana($var_val, "n");
		if(!$var_val){
			$value = "null";
		}else{
			$value = "$var_val";
		}
		break;
	case "photo_name":
	case "foodst":
	case "photo_biko":
		$tablename = "order_shooting";
		$addsql = " and num = $tar_val";
		if($target_shooting_id){
			$addsql .= " and shooting_id = $target_shooting_id";
		}
		$sql = "select shooting_id from $tablename where order_id = $target_order_id{$addsql}";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$sql = "select nextval('order_shooting_id_seq')";
			$result = $ybase->sql($conn,$sql);
			$num0 = pg_num_rows($result);
			if(!$num0){
				$ybase->error("データベースエラーです。ERROR_CODE:23002");
			}
			$new_shooting_id = pg_fetch_result($result,0,0);
			$sql = "insert into $tablename (shooting_id,order_id,num,add_date,status) values ($new_shooting_id,$target_order_id,$tar_val,'now','1')";
			$result = $ybase->sql($conn,$sql);
			$addsql = " and num = $tar_val and shooting_id = $new_shooting_id";
		}
		$value = "'$var_val'";
		break;
	case "price":
		$tablename = "order_shooting";
		$addsql = " and num = $tar_val";
		if($target_shooting_id){
			$addsql .= " and shooting_id = $target_shooting_id";
		}
		$sql = "select shooting_id from $tablename where order_id = $target_order_id{$addsql}";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
		if(!$num){
			$sql = "select nextval('order_shooting_id_seq')";
			$result = $ybase->sql($conn,$sql);
			$num0 = pg_num_rows($result);
			if(!$num0){
				$ybase->error("データベースエラーです。ERROR_CODE:23002");
			}
			$new_shooting_id = pg_fetch_result($result,0,0);
			$sql = "insert into $tablename (shooting_id,order_id,num,add_date,status) values ($new_shooting_id,$target_order_id,$tar_val,'now','1')";
			$result = $ybase->sql($conn,$sql);
			$addsql = " and num = $tar_val and shooting_id = $new_shooting_id";
		}
		$var_val = mb_convert_kana($var_val, "n");
		if(!$var_val){
			$value = "null";
		}else{
			$value = "$var_val";
		}
		break;
		default:
			print "no_target";
		break;
}


$sql = "update $tablename set $colname = $value where order_id = $target_order_id{$addsql}";

$result = $ybase->sql($conn,$sql);
if($result){
	print "OK";
}else{
	print "NG";
}

////////////////////////////////////////////////
?>