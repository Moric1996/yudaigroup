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
$ybase->make_shop_list();
//$ybase->make_employee_list();

$monthon = 1;//月単位で


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
if($monthon){
	$date_sql = "between '$s_date' and '$e_date'";
	$g_width = 2000;
}else{
	$date_sql = "= '$selyymmdd'";
	$g_width = 1000;
}

$conn = $ybase->connect(2);

$sql = "select survey_set_id,shop_id,survey_no from survey_set where company_id = 1 and status = '1' order by shop_id,survey_no";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$mun0 = 0;
$survey_set_list = array();
for($i=0;$i<$num;$i++){
	list($q_survey_set_id,$q_shop_id,$q_survey_no) = pg_fetch_array($result,$i);
//	$survey_set_list[$q_survey_set_id] = $ybase->section_list[$q_shop_id].$q_survey_no;
	$survey_set_list[$q_survey_set_id] = $ybase->section_list[$q_shop_id];
	$survey_to_shop_list[$q_survey_set_id] = $q_shop_id;
	$answer_num[$q_survey_set_id] = 0;
}


//各カード（質問内容)を配列へ
$sql = "select survey_set_id,card_id,type,require,category from survey_card where type in (3,5,6) and category in (2,3,4,5) and status = '1' order by survey_set_id,jun";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("パラメーターエラー。ERROR_CODE:11113");
}

for($i=0;$i<$num;$i++){
	list($q_survey_set_id,$q_card_id,$q_type,$q_require,$q_category) = pg_fetch_array($result,$i);
	$card_type[$q_survey_set_id][$q_card_id] = $q_type;
	$card_category[$q_card_id] = $q_category;
}

//各カードの選択肢を配列へ
$sql = "select card_id,grid_no,key,score from survey_option where score is not null and status = '1' order by card_id,jun";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("パラメーターエラー。ERROR_CODE:11113");
}

for($i=0;$i<$num;$i++){
	list($q_card_id,$q_grid_no,$q_key,$q_score) = pg_fetch_array($result,$i);
	$q_score = trim($q_score);
	$varname3 = "option_cnt".$q_card_id."_".$q_grid_no."_".$q_key;
	$varname5 = "option_score".$q_card_id."_".$q_grid_no."_".$q_key;
	${$varname3} = 0;
	${$varname5} = $q_score;
	
	if($card_grid_max[$q_card_id] < $q_grid_no){
		$card_grid_max[$q_card_id] = $q_grid_no;
	}
}

$sql = "select survey_set_id,answer_id,member_id,to_char(add_date,'YYYY/MM/DD HH24:MI:SS') from survey_answer where to_char(add_date,'YYYY-MM-DD') {$date_sql} and company_id = 1 and status = '1'  order by add_date desc";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
for($i=0;$i<$num;$i++){
	list($q_survey_set_id,$q_answer_id,$q_member_id,$q_add_date) = pg_fetch_array($result,$i);//
	$answer_num[$q_survey_set_id]++;
	$sql2 = "select card_id,type,grid_no,ans_text from survey_answer_part where answer_id = $q_answer_id and type in (3,5,6) and status = '1' order by card_id,grid_no";
	$result2 = $ybase->sql($conn,$sql2);
	$num2 = pg_num_rows($result2);
	for($ii=0;$ii<$num2;$ii++){
		list($q_card_id,$q_type,$q_grid_no,$q_ans_text) = pg_fetch_array($result2,$ii);
		$q_ans_text = trim($q_ans_text);
		$varname = "answer_text".$q_card_id."_".$q_grid_no;
		${$varname}[$q_answer_id] = intval($q_ans_text);

	}
}
$ybase->title = "アンケート集計";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#insurvey_set_id,#inmonth,#ttype,#monthon,#sel_p_week').change(function(){
		$("#form1").submit();
	});
});

</script>
<div class="container">
  <ul class="nav nav-tabs nav-fill" id="myTab" style="font-size:70%;">
    <li class="nav-item">
      <a href="./survey_list.php" class="nav-link">アンケート一覧</a>
    </li>
    <li class="nav-item">
      <a href="./ans_list.php" class="nav-link">アンケート詳細</a>
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
	<a class="dropdown-item active" href="./ans_graf_sum.php">回数</a>
	<a class="dropdown-item" href="./ans_graf_nps.php">NPS</a>
	<a class="dropdown-item" href="./ans_graf_day.php">日別回数</a>
	</div>

    </li>
    <li class="nav-item">
      <a href="log_view.php" class="nav-link">ログ</a>
    </li>
  </ul>
</div>

<div class="container">

<p></p>
<div style="font-size:80%;margin:5px;">

<form action="ans_graf_sum.php" method="post" id="form1">
HTML;

if($monthon == 1){
	$monthon_checked = " checked";
	$selectdate = "<input type=\"month\" name=\"selyymm\" value=\"$selyymm\" id=\"inmonth\">";
}else{
	$monthon_checked = "";
	$selectdate = "<input type=\"date\" name=\"selyymmdd\" value=\"$selyymmdd\" id=\"inmonth\">";
}
$ybase->ST_PRI .= <<<HTML
</select>　

{$selectdate}
　

</form>
<p></p>


HTML;

foreach($survey_set_list as $key => $val){
	$cate_score_sum[$key][3] = 0;
	$cate_score_sum[$key][4] = 0;
	$cate_score_sum[$key][5] = 0;
	$cate_score_cnt[$key][3] = 0;
	$cate_score_cnt[$key][4] = 0;
	$cate_score_cnt[$key][5] = 0;
	$NPS_sum[$key] = 0;
	$NPS_cnt6[$key] = 0;
	$NPS_cnt9[$key] = 0;
	foreach($card_type[$key] as $card_idkey => $type_val){
		$cate = $card_category[$card_idkey];
		for($i=1;$i<=$card_grid_max[$card_idkey];$i++){
			$varname = "answer_text".$card_idkey."_".$i;
			foreach(${$varname} as $anskey => $ansval){
			$varname5 = "option_score".$card_idkey."_".$i."_".$ansval;
			$svalue = ${$varname5};
			if($cate == 2){
				$NPS_sum[$key]++;
				if($svalue < 7){
					$NPS_cnt6[$key]++;
				}elseif($svalue > 8){
					$NPS_cnt9[$key]++;
				}
			}elseif(($cate == 3)||($cate == 4)||($cate == 5)){
				$cate_score_sum[$key][$cate] += $svalue;
				$cate_score_cnt[$key][$cate] ++;
			}
			}
		}
	}

	if($NPS_sum[$key]){
		$NPS6rate = round(($NPS_cnt6[$key]/$NPS_sum[$key])*100);
		$NPS9rate = round(($NPS_cnt9[$key]/$NPS_sum[$key])*100);
		$NPSv[$key] = $NPS9rate - $NPS6rate;
	}else{
		$NPSv[$key] = "-";
	}
	for($i=3;$i<=5;$i++){
		if($cate_score_cnt[$key][$i]){
		$aveg[$key][$i] = round($cate_score_sum[$key][$i]/$cate_score_cnt[$key][$i],1);
		}else{
		$aveg[$key][$i] = "-";
		}
	}
}
arsort($answer_num);
arsort($NPSv);
/////////////////////

$graphtxt="";
foreach($answer_num as $key => $val){
	$sname = $survey_set_list[$key];
	$shop_id = $survey_to_shop_list[$key];
	$graphtxt .= "data.addRow(['{$sname}',$val]);\n";
}

$graphtxt2="";

foreach($NPSv as $key => $val){
	$sname = $survey_set_list[$key];
	$shop_id = $survey_to_shop_list[$key];
	$graphtxt2 .= "data.addRow(['{$sname}',$val]);\n";
}


$ybase->ST_PRI .= <<<HTML
<script src="https://www.gstatic.com/charts/loader.js"></script>
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
            data.addColumn('string', '店舗');
            data.addColumn('number', '回答数');
		$graphtxt

            // オプションの準備
            var options = {
                title: 'アンケート回答数',
                width: 1200,
                height: 600,
		hAxis: {maxTextLines:1,maxValue:100},
		chartArea: {width:'90%',height:'75%',top:10},
		legend: 'none'
		};

            // 描画用インスタンスの生成および描画メソッドの呼び出し
            var chart = new google.visualization.ColumnChart(document.getElementById('grafsum'));
            chart.draw(data, options);
        }

    })();
</script>

HTML;

$ybase->ST_PRI .= <<<HTML

<table>
<tr><td align="center">
<span id="grafsum"></span>
</td></tr>
</table>

</div>



</div>
</div>
<p></p>

HTML;


$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>