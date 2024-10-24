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
include('../inc/ybase.inc');
include(dirname(__FILE__).'/../../camp/inc/campbase.inc');

$ybase = new ybase();
$campbase = new campbase();
$ybase->session_get();
//$edit_f = 1;
/////////////////////////////////////////
if(!preg_match("/^[0-9]+$/",$camp_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:11111");
}
if($ybase->my_section_id != "003"){

}
$param = "camp_id=$camp_id&sel_prize_id=$sel_prize_id&sel_lot_stat=$sel_lot_stat&page=$page";

$sqladd = "";
if($sel_prize_id){
	$sqladd .= " and prize_id = $sel_prize_id";
}
if(preg_match("/[0-9]+/",$sel_lot_stat){
	$sqladd .= " and lot_stat = $sel_lot_stat";
}

$conn = $ybase->connect(2);


//各カード（質問内容)を配列へ
$sql = "select regist_id,user_id,shop_id,prize_id,name,name_kana,zipcode,prefecture_code,address,address2,telno,email,sex,age,staff_flag,lot_stat,to_char(add_date,'YYYY/MM/DD'),status from stamp_regist where camp_id = $camp_id{$sqladd} and status > '0' order by add_date";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("パラメーターエラー。ERROR_CODE:11113");
}

$csv_head = "\"応募ID\",\"応募賞品\",\"氏名\",\"氏名カナ\",\"ユーザーID\",\"性別\",\"年齢\",\"郵便番号\",\"都道府県\",\"住所1\",\"住所2\",\"電話番号\",\"Eメール\",\"店舗\",\"staff\",\"応募日\",\"状態\"\r\n";

$csv_body = "";


for($i=0;$i<$num;$i++){
	list($q_regist_id,$q_user_id,$q_shop_id,$q_prize_id,$q_name,$q_name_kana,$q_zipcode,$q_prefecture_code,$q_address,$q_address2,$q_telno,$q_email,$q_sex,$q_age,$q_staff_flag,$q_lot_stat,$q_add_date,$q_status) = pg_fetch_array($result,$i);
	$q_name = stripslashes($q_name);
	$q_name_kana = stripslashes($q_name_kana);
	$address = stripslashes($address);
	$address2 = stripslashes($address2);
	$q_user_id = trim($q_user_id);
	$q_shop_id = trim($q_shop_id);
	$q_prize_id = trim($q_prize_id);
	$q_name = trim($q_name);
	$q_name_kana = trim($q_name_kana);
	$q_zipcode = trim($q_zipcode);
	$q_prefecture_code = trim($q_prefecture_code);
	$q_address = trim($q_address);
	$q_address2 = trim($q_address2);
	$q_telno = trim($q_telno);
	$q_email = trim($q_email);
	$q_sex = trim($q_sex);
	$q_age = trim($q_age);
	$q_staff_flag = trim($q_staff_flag);
	$q_lot_stat = trim($q_lot_stat);
	$q_add_date = trim($q_add_date);
	$q_status = trim($q_status);

	$csv_body .= "$q_regist_id,\"{$campbase->prize_list[$q_prize_id]}\",\"{$q_name}\",\"{$q_name_kana}\",$q_user_id,\"{$campbase->sex_list[$q_sex]}\",$q_age,\"{$q_zipcode}\",\"{$campbase->PrefCodeList[$q_prefecture_code]}\",\"{$q_address}\",\"{$q_address2}\",\"{$q_telno}\",\"{$q_email}\",\"{$campbase->app_s_id_list[$q_shop_id]}\",\"{$campbase->staff_flag_list[$q_staff_flag]}\",\"{$q_add_date}\",\"{$campbase->lot_stat_list[$q_lot_stat]}\"\r\n";

}


/*
if($pv_mode == 1){
	$uagent = $_SERVER['HTTP_USER_AGENT'];
	$raddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
	if(!$answer_id){
		$answer_id = 'null';
	}
	$sql= "insert into survey_private_log values(nextval('survey_private_log_id_seq'),$survey_set_id,'$shop_id',$answer_id,{$ybase->my_employee_id},'{$ybase->my_session_id}','$uagent','$raddr','now',2)";
	$result = $ybase->sql($conn,$sql);
}
*/
$csvfile = "$csv_head"."$csv_body";
$csvfile = mb_convert_encoding($csvfile,"sjis-win","UTF-8");

$filename = "campaign2021".$camp_id.date("Ymd").".csv";
$filesize = strlen($csvfile);
header('Content-Type: text/csv; charset=Shift_JIS');
header('X-Content-Type-Options: nosniff');
header("Content-Length: $filesize");
header("Content-Disposition: attachment; filename=$filename");
header('Connection: close');
while (ob_get_level()) { ob_end_clean(); }
echo $csvfile;
exit;

////////////////////////////////////////////////
?>