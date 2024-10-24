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

$ybase = new ybase();
$ybase->session_get();
//$edit_f = 1;
/////////////////////////////////////////
if(!preg_match("/^[0-9]+$/",$survey_set_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:11111");
}

if(!preg_match("/^[0-9]+$/",$shop_id)){
	$shop_id="";
}
$param = "survey_set_id=$survey_set_id&shop_id=$shop_id";
$ybase->make_shop_list();

$monthon = 1;//月単位で
//$ybase->make_employee_list("1");


if($monthon){
if($selyymm){
	$yy = substr($selyymm,0,4);
	$mm = substr($selyymm,5,2);
}elseif($selyymmdd){
	$yy = substr($selyymmdd,0,4);
	$mm = substr($selyymmdd,5,2);
}
if(!$yy || !$mm){
	$now_yy = date('Y');
	$now_mm = date('m');
	$now_dd = date('d');
	$yy = date('Y',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$mm = date('m',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$dd = date('d',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
}
$maxday = date('t',mktime(0,0,0,$mm,1,$yy));
$selyymm = "$yy-$mm";
}else{
if($selyymmdd){
	$yy = substr($selyymmdd,0,4);
	$mm = substr($selyymmdd,5,2);
	$dd = substr($selyymmdd,8,2);
}
if(!$yy || !$mm || !$dd){
	$now_yy = date('Y');
	$now_mm = date('m');
	$now_dd = date('d');
	$yy = date('Y',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$mm = date('m',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$dd = date('d',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
}
$selyymmdd = "$yy-$mm-$dd";
$selyymm = "$yy-$mm";
}

$conn = $ybase->connect(2);

//アンケートの存在確認
$sql = "select shop_id from survey_set where company_id = 1 and survey_set_id = $survey_set_id and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("パラメーターエラー。ERROR_CODE:11112");
}
	list($q_shop_id) = pg_fetch_array($result,0);
$shop_name = $ybase->section_list[$q_shop_id];
if($pv_mode == '1'){
	$add_sql = "";
}else{
	$add_sql = " and (private is null or private = '1')";
}

//各カード（質問内容)を配列へ
$sql = "select card_id,type,number,matter,com,require,jun from survey_card where survey_set_id = $survey_set_id and type <> 99 and status = '1'{$add_sql} order by jun";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("パラメーターエラー。ERROR_CODE:11113");
}

for($i=0;$i<$num;$i++){
	list($q_card_id,$q_type,$q_number,$q_matter,$q_com,$q_require) = pg_fetch_array($result,$i);
	$q_matter = trim($q_matter);
	$q_number = trim($q_number);
	$q_com = trim($q_com);
	$card_type[$q_card_id] = $q_type;
	$card_umber[$q_card_id] = $q_number;
	$card_matter[$q_card_id] = $q_matter;

}

//各カードの選択肢を配列へ
$sql = "select card_id,grid_no,other_flag,key,value from survey_option where survey_set_id = $survey_set_id and status = '1' order by jun";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("パラメーターエラー。ERROR_CODE:11113");
}

for($i=0;$i<$num;$i++){
	list($q_card_id,$q_grid_no,$q_other_flag,$q_key,$q_value) = pg_fetch_array($result,$i);
	$q_value = trim($q_value);
	$varname1 = "option_other_flag".$q_card_id."_".$q_grid_no."_".$q_key;
	$varname2 = "option_value".$q_card_id."_".$q_grid_no."_".$q_key;
	$varname3 = "option_cnt".$q_card_id."_".$q_grid_no."_".$q_key;
	$varname4 = "option_value_arr".$q_card_id."_".$q_grid_no;
	${$varname1}= $q_other_flag;
	${$varname2} = $q_value;
	${$varname3} = 0;
	${$varname4}[$q_key] = $q_value;
	if(!$option_grid[$q_card_id]){
		$option_grid[$q_card_id] = 1;
	}
	if($option_grid[$q_card_id] < $q_grid_no){
		$option_grid[$q_card_id] = $q_grid_no;
	}
}



$csv_head = "\"NO\",\"回答ID\"";

foreach($card_type as $key => $val){
	if($card_umber[$key]){
		$ditem = $card_umber[$key].".".$card_matter[$key];
	}else{
		$ditem = $card_matter[$key];
	}
	$csv_head .= ","."\"$ditem\"";
}

if($pv_mode == '1'){
	$csv_head .= ",\"会員番号\"";
}

$csv_head .= ",\"回答日\",\"前回回答ID\",\"リンク元\",\"GMB\",\"確認・抽選\",\"確認日時\",\"確認者\"\r\n";

$csv_body = "";

if($monthon){
	$date_sql = "between '{$selyymm}-01' and '{$selyymm}-{$maxday}'";
	$g_width = 2000;
}else{
	$date_sql = "= '$selyymmdd'";
	$g_width = 1000;
}
$sql = "select answer_id,member_id,media_type,answer_kind,confirm,to_char(confirm_date,'YYYY/MM/DD HH24:MI:SS'),confirm_staff,ua,ip,last_answer_id,to_char(add_date,'YYYY/MM/DD HH24:MI:SS') from survey_answer where survey_set_id = $survey_set_id and company_id = 1 and status = '1' and add_date {$date_sql} order by add_date desc";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
for($i=0;$i<$num;$i++){
	list($q_answer_id,$q_member_id,$q_media_type,$q_answer_kind,$q_confirm,$q_confirm_date,$q_confirm_staff,$q_ua,$q_ip,$q_last_answer_id,$q_add_date) = pg_fetch_array($result,$i);//各カードの回答を配列へ
	$sql2 = "select card_id,type,grid_no,ans_text,ans_div,other_flag,other_text from survey_answer_part where answer_id = $q_answer_id and survey_set_id = $survey_set_id and status = '1' order by card_id,grid_no";
	$result2 = $ybase->sql($conn,$sql2);
	$num2 = pg_num_rows($result2);
	$answer_type=array();
	$answer_text=array();
	$answer_div=array();
	$answer_other_flag=array();
	$answer_other_text=array();
	for($n=0;$n<$num2;$n++){
		list($q_card_id,$q_type,$q_grid_no,$q_ans_text,$q_ans_div,$q_other_flag,$q_other_text) = pg_fetch_array($result2,$n);
		$q_ans_text = trim($q_ans_text);
		$q_other_text = trim($q_other_text);
		$answer_type[$q_card_id][$q_grid_no] = $q_type;
		$answer_text[$q_card_id][$q_grid_no] = $q_ans_text;
		$answer_div[$q_card_id][$q_grid_no] = $q_ans_div;
		$answer_other_flag[$q_card_id][$q_grid_no] = $q_other_flag;
		$answer_other_text[$q_card_id][$q_grid_no] = $q_other_text;
		switch ($q_type) {
			case 3:
			case 5:
			case 6:
			case 7:
				$varname3 = "option_cnt".$q_card_id."_".$q_grid_no."_".$q_ans_text;
				${$varname3} += 1;
				break;
			case 4:
			case 8:
				$aaa = str_replace("{","",$q_ans_div);
				$aaa = str_replace("}","",$aaa);
				$keyno_arr = explode(",",$aaa);
				foreach($keyno_arr as $key2 => $val2){
					$varname3 = "option_cnt".$q_card_id."_".$q_grid_no."_".$val2;
					${$varname3} += 1;
				}
				break;
		}
	}

	$k = $i + 1;

$csv_body .= "$k,$q_answer_id";

foreach($card_type as $key => $val){
	$content = "";

	switch ($val) {
		case 1:
		case 2:
		case 10:
		case 11:
		case 12:
			$content = $answer_text[$key][1];
			break;
		case 3:
		case 5:
		case 6:
			$keyno = $answer_text[$key][1];
			$varname1 = "option_other_flag".$key."_1_".$keyno;
			$varname2 = "option_value".$key."_1_".$keyno;
			$content = ${$varname2};
			if($keyno == 99){
				$content .= "<br>[".$answer_other_text[$key][1]."]";
			}
			break;
		case 4:
			$keyno = $answer_div[$key][1];
			$keyno = str_replace("{","",$keyno);
			$keyno = str_replace("}","",$keyno);
			$keyno_arr = explode(",",$keyno);
			foreach($keyno_arr as $key2 => $val2){
				$varname1 = "option_other_flag".$key."_1_".$val2;
				$varname2 = "option_value".$key."_1_".$val2;
				$content .= "・".${$varname2}."<br>";
			if($val2 == 99){
				$content .= "[".$answer_other_text[$key][1]."]<br>";
			}
			}
			break;
		case 7:
		for($mm=1;$mm<=$option_grid[$key];$mm++){
			$keyno = $answer_text[$key][$mm];
			$varname1 = "option_other_flag".$key."_".$mm."_".$keyno;
			$varname2 = "option_value".$key."_".$mm."_".$keyno;
			$content .= ${$varname2}."<br>";
			if($keyno == 99){
				$content .= "[".$answer_other_text[$key][1]."]<br>";
			}
		}
			break;
		case 8:
		for($mm=1;$mm<=$option_grid[$key];$mm++){
			$keyno = $answer_div[$key][$mm];
			$keyno = str_replace("{","",$keyno);
			$keyno = str_replace("}","",$keyno);
			$keyno_arr = explode(",",$keyno);
			foreach($keyno_arr as $key2 => $val2){
				$varname1 = "option_other_flag".$key."_".$mm."_".$val2;
				$varname2 = "option_value".$key."_".$mm."_".$val2;
				$content .= ${$varname2}."<br>";
				if($keyno == 99){
					$content .= "[".$answer_other_text[$key][$mm]."]<br>";
				}
			}
			break;
		}
	}



	$csv_body .= ",\"$content\"";

}
if($pv_mode == '1'){
	$csv_body .= ",\"$q_member_id\"";
}

	$csv_body .= ",\"$q_add_date\",$q_last_answer_id,\"{$ybase->mediatype_list[$q_media_type]}\",\"{$ybase->answer_kind_list[$q_answer_kind]}\",\"{$ybase->check_flag_list[$q_confirm]}\",\"{$q_confirm_date}\",\"{$ybase->employee_name_list[$q_confirm_staff]}\"\r\n";

}


if($pv_mode == 1){
	$uagent = $_SERVER['HTTP_USER_AGENT'];
	$raddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
	if(!$answer_id){
		$answer_id = 'null';
	}
	$sql= "insert into survey_private_log values(nextval('survey_private_log_id_seq'),$survey_set_id,'$shop_id',$answer_id,{$ybase->my_employee_id},'{$ybase->my_session_id}','$uagent','$raddr','now',2)";
	$result = $ybase->sql($conn,$sql);
}

$csvfile = "$csv_head"."$csv_body";
$csvfile = mb_convert_encoding($csvfile,"sjis-win","UTF-8");

$filename = "questionnairedata".$survey_set_id.$shop_id.date("Ymd").".csv";
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