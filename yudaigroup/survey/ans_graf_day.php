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

$monthon = 1;// 月単位で


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
	$maxday = date('t',mktime(0,0,0,$mm,$dd,$yy));

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



$sql = "select survey_set_id,to_char(add_date,'FMDD'),count(*) from survey_answer where to_char(add_date,'YYYY-MM-DD') {$date_sql}";
if($survey_set_id){
	$sql .= " and survey_set_id = $survey_set_id";
}
$sql .= " and company_id = 1 and status = '1' group by survey_set_id,to_char(add_date,'FMDD') order by survey_set_id,to_char(add_date,'FMDD')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
for($i=0;$i<$num;$i++){
	list($q_survey_set_id,$q_day,$q_cnt) = pg_fetch_array($result,$i);//
	$day_cont[$q_survey_set_id][$q_day] = $q_cnt;

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
	<a class="dropdown-item" href="./ans_graf_sum.php">回数</a>
	<a class="dropdown-item" href="./ans_graf_nps.php">NPS</a>
	<a class="dropdown-item active" href="./ans_graf_day.php">日別回数</a>
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

<form action="ans_graf_day.php" method="post" id="form1">
<input type="hidden" name="shop_id" value="$shop_id">
<select name="survey_set_id" id="insurvey_set_id">
<option value="">選択してください</option>
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

/////////////////////

$graphtxt="['日にち', '".$survey_set_list[$survey_set_id]."'],\n";

for($i=1;$i<=$maxday;$i++){
	$hi = $i."日";
	$kaisu = $day_cont[$survey_set_id][$i];
	if(!$kaisu){
		$kaisu = 0;
	}
	$graphtxt .= "['{$hi}', {$kaisu}],\n";
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
            var data　= new google.visualization.arrayToDataTable([
		$graphtxt
		]);
            // オプションの準備
            var options = {
                title: '日毎回答数',
                width: 1200,
                height: 600
		};

            // 描画用インスタンスの生成および描画メソッドの呼び出し
            var chart = new google.visualization.LineChart(document.getElementById('grafsum'));
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