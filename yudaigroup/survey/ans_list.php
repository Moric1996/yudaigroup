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
	$survey_set_id=1;
}

if(!preg_match("/^[0-9]+$/",$shop_id)){
	$shop_id="";
}
$param = "survey_set_id=$survey_set_id&shop_id=$shop_id";
$ybase->make_shop_list();
$ybase->make_employee_list();

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
	$s_day = 1 + (7 * ($p_week -1));
	$e_day = $s_day + 6;
	if($p_week == 5){
		$e_day = $maxday;
	}
	if(!$p_week){
		$s_day = 1;
		$e_day = $maxday;
	}
	$s_date = date('Y-m-d',mktime(0,0,0,$mm,$s_day,$yy));
	$e_date = date('Y-m-d',mktime(0,0,0,$mm,$e_day,$yy));

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
	$s_date = $selyymmdd;
	$e_date = $selyymmdd;

}
if($monthon == 1){
	$monthon_checked = " checked";
	$selectdate = "<input type=\"month\" name=\"selyymm\" value=\"$selyymm\" id=\"inmonth\">";
}else{
	$monthon_checked = "";
	$selectdate = "<input type=\"date\" name=\"selyymmdd\" value=\"$selyymmdd\" id=\"inmonth\">";
}


$conn = $ybase->connect(2);

$sql = "select survey_set_id,shop_id,survey_no from survey_set where company_id = 1 and status = '1' order by shop_id,survey_no";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$mun0 = 0;
$survey_set_list = array();
for($i=0;$i<$num;$i++){
	list($q_survey_set_id,$q_shop_id,$q_survey_no) = pg_fetch_array($result,$i);
	$survey_set_list[$q_survey_set_id] = $ybase->section_list[$q_shop_id].$q_survey_no;
}



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

$conn = $ybase->connect(1);
$pos_shopno = $ybase->section_to_pos[$q_shop_id];
if($pos_shopno){
	$sql = "select sum(num_pair) from pos_ztotal_sales where pos_shopno = $pos_shopno and sale_date between '$s_date' and '$e_date' and status = '1'";
//print "$sql<br>";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
		list($q_num_pair) = pg_fetch_array($result,0);
	}
}

$conn = $ybase->connect(2);

//各カード（質問内容)を配列へ
$sql = "select card_id,type,number,matter,com,require,jun from survey_card where survey_set_id = $survey_set_id and type <> 99 and status = '1'{$add_sql} order by jun";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("パラメーターエラー。ERROR_CODE:11113");
}
$last_number = "";
for($i=0;$i<$num;$i++){
	list($q_card_id,$q_type,$q_number,$q_matter,$q_com,$q_require) = pg_fetch_array($result,$i);
	$q_matter = trim($q_matter);
	$q_number = trim($q_number);
	$q_com = trim($q_com);
	$card_type[$q_card_id] = $q_type;
	$card_umber[$q_card_id] = $q_number;
	$card_matter[$q_card_id] = $q_matter;
	$card_score_sum[$q_card_id] = 0;
	$card_score_cnt[$q_card_id] = 0;
	$card_NPS_sum[$q_card_id] = 0;
	$card_NPS6_cnt[$q_card_id] = 0;
	$card_NPS9_cnt[$q_card_id] = 0;
	$card_NPS_flag[$q_card_id] = 0;
	if($q_number){
		$last_number = $q_number;
	}
}

//各カードの選択肢を配列へ
$sql = "select card_id,grid_no,other_flag,key,value,score from survey_option where survey_set_id = $survey_set_id and status = '1' order by jun";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("パラメーターエラー。ERROR_CODE:11113");
}

for($i=0;$i<$num;$i++){
	list($q_card_id,$q_grid_no,$q_other_flag,$q_key,$q_value,$q_score) = pg_fetch_array($result,$i);
	$q_value = trim($q_value);
	$q_score = trim($q_score);
	$varname1 = "option_other_flag".$q_card_id."_".$q_grid_no."_".$q_key;
	$varname2 = "option_value".$q_card_id."_".$q_grid_no."_".$q_key;
	$varname3 = "option_cnt".$q_card_id."_".$q_grid_no."_".$q_key;
	$varname4 = "option_value_arr".$q_card_id."_".$q_grid_no;
	$varname5 = "option_score".$q_card_id."_".$q_grid_no."_".$q_key;
	${$varname1}= $q_other_flag;
	${$varname2} = $q_value;
	${$varname3} = 0;
	${$varname4}[$q_key] = $q_value;
	${$varname5} = $q_score;
	if(!$option_grid[$q_card_id]){
		$option_grid[$q_card_id] = 1;
	}
	if($option_grid[$q_card_id] < $q_grid_no){
		$option_grid[$q_card_id] = $q_grid_no;
	}
	$card_NPS_flag[$q_card_id] += $q_score;
}



$ybase->title = "アンケート回答一覧-{$shop_name}";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#insurvey_set_id,#inmonth,#ttype,#monthon,#sel_p_week').change(function(){
		$("#form1").submit();
	});
	$("[id^=delete]").click(function(){
		var dhref = $(this).attr('delhref');
		if(!confirm('本当に削除しますか？')){
		        return false;
		}else{
			location.href = dhref;
		}
	});
});

</script>
<style type="text/css">
#table1 {
    display: block;
    overflow-y: scroll;
    height: calc(200vh/3);
    border:1px solid;
    border-collapse: collapse;
}

table#table1 thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #eacaca;
    border-top:#FFFFFF;
    border-bottom:solid 2px #000000;
    font-weight: normal;
}
</style>
<div class="container">
  <ul class="nav nav-tabs nav-fill" id="myTab" style="font-size:70%;">
    <li class="nav-item">
      <a href="./survey_list.php" class="nav-link">アンケート一覧</a>
    </li>
    <li class="nav-item">
      <a href="./ans_list.php" class="nav-link active">アンケート詳細</a>
    </li>
    <li class="nav-item dropdown">
	<a class="nav-link dropdown-toggle" data-toggle="dropdown"  href="#" role="button" aria-haspopup="true" aria-expanded="false">アンケート集計</a>
	<div class="dropdown-menu">
	<a class="dropdown-item" href="./ans_ana.php">評価NPS</a>
	<a class="dropdown-item" href="./ans_gmb.php">GMB</a>
	</div>
    </li>
    <li class="nav-item dropdown">
	<a class="nav-link dropdown-toggle" data-toggle="dropdown"  href="#" role="button" aria-haspopup="true" aria-expanded="false">集計グラフ</a>
	<div class="dropdown-menu">
	<a class="dropdown-item" href="./ans_graf_sum.php">回数</a>
	<a class="dropdown-item" href="./ans_graf_nps.php">NPS</a>
	<a class="dropdown-item" href="./ans_graf_day.php">日別回数</a>
	</div>
    </li>
    <li class="nav-item">
      <a href="log_view.php" class="nav-link">ログ</a>
    </li>
  </ul>
</div>

<div class="container-fluid">

<p></p>
<div style="font-size:80%;margin:5px;">

<form action="ans_list.php" method="post" id="form1">
<input type="hidden" name="shop_id" value="$shop_id">
<select name="survey_set_id" id="insurvey_set_id">
HTML;
foreach($survey_set_list as $key => $val){
	if("$survey_set_id" == "$key"){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"{$addselect}>$val</option>
HTML;
}

$ybase->ST_PRI .= <<<HTML
</select>　

{$selectdate}
　
<select name="p_week" id="sel_p_week">
HTML;
foreach($ybase->p_week_list as $key => $val){
	if("$p_week" == "$key"){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"{$addselect}>$val</option>
HTML;
}

$ybase->ST_PRI .= <<<HTML
</select>　

</form>
<p></p>
HTML;
if($pv_mode == 1){
$ybase->ST_PRI .= <<<HTML
<div style="text-align:right;font-size:80%"><a class="btn btn-danger btn-sm" href="./ans_list.php?$param&pv_mode=" role="button">個人情報を隠す</a></div>
HTML;
}else{
$ybase->ST_PRI .= <<<HTML
<div style="text-align:right;font-size:80%"><a class="btn btn-danger btn-sm" href="./ans_list.php?$param&pv_mode=1" role="button">個人情報表示</a>※表示した従業員の操作ログが残ります</div>
HTML;
}
$ybase->ST_PRI .= <<<HTML

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th>NO</th>
<th>回答ID</th>
HTML;
$last_number_flag = "";
$left_border_arr = array();
foreach($card_type as $key => $val){
if($card_umber[$key]){
	if($card_umber[$key] == $last_number){
		$last_number_flag = $last_number;
	}
	$ditem = $card_umber[$key].".".$card_matter[$key];
	$addcss = "border-left:solid 2px #555555;";
	array_push($left_border_arr,$key);
}else{
	$ditem = $card_matter[$key];
	$addcss = "";
	if($last_number_flag){
		array_push($left_border_arr,$key);
		$last_number_flag = "";
		$addcss = "border-left:solid 2px #555555;";
	}
}
$ybase->ST_PRI .= <<<HTML

<th width="100" style="font-size:50%;{$addcss}">$ditem</th>

HTML;
}

if($pv_mode == '1'){
$ybase->ST_PRI .= <<<HTML
<th width="100" style="font-size:50%;">会員番号</th>
HTML;
}

$ybase->ST_PRI .= <<<HTML
<th width="100" style="font-size:50%;">回答日</th>
<th width="100" style="font-size:50%;">前回回答ID</th>
<th width="100" style="font-size:50%;">リンク元</th>
<th width="100" style="font-size:50%;">GMB</th>
<th width="100" style="font-size:50%;">確認・抽選</th>
<th width="100" style="font-size:50%;">変更</th>
<th width="100" style="font-size:50%;">削除</th>
</tr>
</thead>

<tbody>
HTML;
if($monthon){
	$date_sql = "between '{$selyymm}-01' and '{$selyymm}-{$maxday}'";
	$g_width = 2000;
}else{
	$date_sql = "= '$selyymmdd'";
	$g_width = 1000;
}
$sql = "select answer_id,member_id,media_type,answer_kind,confirm,to_char(confirm_date,'YYYY/MM/DD HH24:MI:SS'),confirm_staff,ua,ip,last_answer_id,to_char(add_date,'YYYY/MM/DD HH24:MI:SS') from survey_answer where survey_set_id = $survey_set_id and company_id = 1 and status = '1' and to_char(add_date,'YYYY-MM-DD') between '$s_date' and '$e_date' order by add_date desc";

$result = $ybase->sql($conn,$sql);
$answer_num = pg_num_rows($result);

//計算用ループ
$answer_kind_sum = array();
$answersum = 0;
for($i=0;$i<$answer_num;$i++){
	list($q_answer_id,$q_member_id,$q_media_type,$q_answer_kind,$q_confirm,$q_confirm_date,$q_confirm_staff,$q_ua,$q_ip,$q_last_answer_id,$q_add_date) = pg_fetch_array($result,$i);//各カードの回答を配列へ
	$q_yyy = substr($q_add_date,0,4);
	$q_mmm = substr($q_add_date,5,2);
	$q_ddd = substr($q_add_date,8,2);
	if(($q_yyy == "2021") && ($q_mmm == "04") && ($q_ddd < 9)){
	}else{
	$answer_kind_sum[$q_answer_kind]++;
	$answersum++;
	}
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
				$varname5 = "option_score".$q_card_id."_".$q_grid_no."_".$q_ans_text;
				if(preg_match("/^[\-0-9]+$/",${$varname5})){
					$card_score_cnt[$q_card_id]++;
					$card_score_sum[$q_card_id] += ${$varname5};
				}
				if($card_NPS_flag[$q_card_id] == 55){
					$card_NPS_sum[$q_card_id]++;
					if(${$varname5} < 7){
						$card_NPS6_cnt[$q_card_id]++;
					}elseif(${$varname5} > 8){
						$card_NPS9_cnt[$q_card_id]++;
					}
				}
				break;
			case 4:
			case 8:
				$aaa = str_replace("{","",$q_ans_div);
				$aaa = str_replace("}","",$aaa);
				$keyno_arr = explode(",",$aaa);
				$score_flag="";
				foreach($keyno_arr as $key2 => $val2){
					$varname3 = "option_cnt".$q_card_id."_".$q_grid_no."_".$val2;
					$varname5 = "option_score".$q_card_id."_".$q_grid_no."_".$val2;
					if(preg_match("/^[\-0-9]+$/",${$varname5})){
						$card_score_sum[$q_card_id] += ${$varname5};
						$score_flag++;
					}
					${$varname3} += 1;
				}
				if($score_flag){
					$card_score_cnt[$q_card_id]++;
				}
				break;
		}
	}

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

	$content_list[$q_answer_id][$key] = $content;
}

}
/////////////////////評価点数平均
if($q_num_pair){
	$get_rate = round($answer_num/$q_num_pair*100,1);
}else{
	$get_rate = "-";
	$q_num_pair = "-";
}
$ybase->ST_PRI .= <<<HTML
<tr align="center">
<th width="100" style="border:solid 3px #111111;"><div style="font-size:50%;">獲得率</div>{$get_rate}%({$answer_num}/{$q_num_pair})</th>
<th width="100"></th>
HTML;
foreach($card_type as $key => $val){
	if(in_array($key,$left_border_arr,true)){
		$addcss = "border-left:solid 2px #555555;";
	}else{
		$addcss = "";
	}
	$avgaddcss = "border:solid 3px #111111;";
	if($card_NPS_flag[$key] == 55){
		if($card_NPS_sum[$key]){
			$NPS6rate = round(($card_NPS6_cnt[$key]/$card_NPS_sum[$key])*100);
			$NPS9rate = round(($card_NPS9_cnt[$key]/$card_NPS_sum[$key])*100);
			$NPSv = $NPS9rate - $NPS6rate;
		}else{
			$NPSv = "-";
		}
		$aveg = "<div style=\"font-size:50%;\">NPS</div>$NPSv";
	}elseif($card_score_cnt[$key]){
		$avg_score = round($card_score_sum[$key]/$card_score_cnt[$key],1);
		if($avg_score < $ybase->survey_assess_base_list[1]){
			$avg_color = $ybase->survey_assess_base_color[1];
		}elseif($avg_score < $ybase->survey_assess_base_list[2]){
			$avg_color = $ybase->survey_assess_base_color[2];
		}else{
			$avg_color = $ybase->survey_assess_base_color[3];
		}
		$aveg = "<div style=\"font-size:50%;\">平均値</div><div style=\"color:{$avg_color};\">$avg_score</div>";
	}else{
		$aveg = "";
		$avgaddcss = "";
	}
$ybase->ST_PRI .= <<<HTML

<th width="100" align="center" style="{$addcss}{$avgaddcss}">$aveg</th>

HTML;
}
if($pv_mode == '1'){
$ybase->ST_PRI .= <<<HTML

<th width="100" style="border-left:solid 2px #555555;"></th>
HTML;
}
$ansgmb = $answer_kind_sum[2] + $answer_kind_sum[9];
if($answersum){
	$ansgmb_rate = round($ansgmb / $answersum *100,1);
}else{
	$ansgmb_rate = "-";
}
if($ansgmb){
	$ansgmb_click_rate = round($answer_kind_sum[9] / $ansgmb *100,1);
}else{
	$ansgmb_click_rate = "-";
}

$ybase->ST_PRI .= <<<HTML
<th width="100"></th>
<th width="100"></th>
<th width="100"></th>
<th width="100" style="border:solid 3px #111111;"><div style="font-size:50%;">誘導率<div><div>{$ansgmb_rate}%</div><div style="font-size:50%;">クリック率</div><div>{$ansgmb_click_rate}%</div><div style="font-size:50%;">2021/4/9以降<div></th>
<th width="100"></th>
<th width="100"></th>
<th width="100"></th>
</tr>
HTML;

////////////////////// 表示用ループ
$k=0;
for($i=0;$i<$answer_num;$i++){
	list($q_answer_id,$q_member_id,$q_media_type,$q_answer_kind,$q_confirm,$q_confirm_date,$q_confirm_staff,$q_ua,$q_ip,$q_last_answer_id,$q_add_date) = pg_fetch_array($result,$i);//各カードの回答を配列へ
	$k = $i + 1;
$ybase->ST_PRI .= <<<HTML
<tr>
<td align="right" width="100">$k</td>
<td align="right" width="100">$q_answer_id</td>
HTML;
foreach($card_type as $key => $val){
	if(in_array($key,$left_border_arr,true)){
		$addcss = "border-left:solid 2px #555555;";
	}else{
		$addcss = "";
	}
$ybase->ST_PRI .= <<<HTML

<td width="100" style="{$addcss}">{$content_list[$q_answer_id][$key]}</td>

HTML;
}
if($pv_mode == '1'){
$ybase->ST_PRI .= <<<HTML
<td width="100" style="border-left:solid 2px #555555;">$q_member_id</td>
HTML;
}
$checkbutton = "";
foreach($ybase->check_flag_list as $key => $val){
	if("$key" == "0"){
		continue;
	}
	if("$q_confirm" == "9"){
		continue;
	}
	if("$key" == "$q_confirm"){
		continue;
	}
	if(("$q_confirm" == "8")&&("$key" != "9")){
		continue;
	}
	if(("$q_confirm" < "3")&&("$key" > "3")){
		continue;
	}
	if(("$q_confirm" != "0")&&("$key" == "1")){
		continue;
	}
$checkbutton .= "<a class=\"btn btn-outline-info btn-sm\" href=\"./check_cg.php?$param&t_answer_id={$q_answer_id}&check_flag={$key}\" role=\"button\" style=\"font-size:90%;padding:0px 10px;\">{$val}</a>";


}

$ybase->ST_PRI .= <<<HTML
<td width="100">$q_add_date</td>
<td width="100">$q_last_answer_id</td>
<td width="100">{$ybase->mediatype_list[$q_media_type]}</td>
<td width="100">{$ybase->answer_kind_list[$q_answer_kind]}</td>
<td width="100">{$ybase->check_flag_list[$q_confirm]}<br>{$q_confirm_date}<br>{$ybase->employee_name_list[$q_confirm_staff]}</td>
<td width="100">{$checkbutton}</td>
<td width="100"><a class="btn btn-secondary btn-sm" delhref="./check_del.php?$param&t_answer_id={$q_answer_id}" id="delete{$q_answer_id}" role="button">削除</a></td>
</tr>
HTML;

}
$ybase->ST_PRI .= <<<HTML
</tbody>
</table>
</div>

<form action="csv_dl.php" method="post">
<input type="hidden" name="survey_set_id" value="$survey_set_id">
<input type="hidden" name="shop_id" value="$shop_id">
<input type="hidden" name="pv_mode" value="$pv_mode">
<input type="hidden" name="selyymm" value="$selyymm">
<button class="btn btn-sm col-sm-2 offset-sm-10 btn-outline-dark" type="submit">データダウンロード</button>

</form>


</div>



</div>
<script src="https://www.gstatic.com/charts/loader.js"></script>

HTML;
$gno=0;
foreach($card_type as $key => $val){
	if($card_umber[$key]){
		$ditem = $card_umber[$key].".".$card_matter[$key];
	}else{
		$ditem = $card_matter[$key];
	}
	switch ($val) {
		case 3:
		case 4:
		case 5:
		case 6:
		case 7:
		case 8:
			for($mm=1;$mm<=$option_grid[$key];$mm++){
			$varname4 = "option_value_arr".$key."_".$mm;
				$graphtxt="";
				$gno++;
				foreach(${$varname4} as $key2 => $val2){
					$varname3 = "option_cnt".$key."_".$mm."_".$key2;
					$cnt = ${$varname3};
					$graphtxt .= "data.addRow(['$val2',$cnt]);\n";
				}
	$targetname[$gno] = "target".$gno;
$ybase->ST_PRI .= <<<HTML
<script>
    (function() {
      'use strict';

        // パッケージのロード
        google.charts.load('current', {packages: ['corechart']});
        // コールバックの登録
        google.charts.setOnLoadCallback(drawChart);

        // コールバック関数の実装
        function drawChart() {
            // データの準備
            var data　= new google.visualization.DataTable();
            data.addColumn('string', '回答');
            data.addColumn('number', '回答数');
		$graphtxt

            // オプションの準備
            var options = {
                title: '{$ditem}',
                width: 1000,
                height: 400,
		is3D: true
            };


            // 描画用インスタンスの生成および描画メソッドの呼び出し
            var chart = new google.visualization.PieChart(document.getElementById('{$targetname[$gno]}'));
            chart.draw(data, options);
        }

    })();
  </script>
HTML;

			}
			break;

	}
}
$ybase->ST_PRI .= <<<HTML
<table width="100%">
HTML;

foreach($targetname as $key => $val){


$ybase->ST_PRI .= <<<HTML
<tr><td align="center">
<span id="$val"></span>
</td></tr>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</table>
</div>
<p></p>

HTML;


if($pv_mode == 1){
	$uagent = $_SERVER['HTTP_USER_AGENT'];
	$raddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
	if(!$answer_id){
		$answer_id = 'null';
	}
	$sql= "insert into survey_private_log values(nextval('survey_private_log_id_seq'),$survey_set_id,'$shop_id',$answer_id,{$ybase->my_employee_id},'{$ybase->my_session_id}','$uagent','$raddr','now',1)";
	$result = $ybase->sql($conn,$sql);
}

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>