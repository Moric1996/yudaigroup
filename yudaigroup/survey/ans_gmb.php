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

$sql = "select survey_set_id,max(shop_id),answer_kind,count(*) from survey_answer where to_char(add_date,'YYYY-MM-DD') {$date_sql} and add_date > '2021-04-09' and company_id = 1 and status = '1' group by survey_set_id,answer_kind order by survey_set_id,answer_kind";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
for($i=0;$i<$num;$i++){
	list($q_survey_set_id,$q_shop_id,$q_answer_kind,$q_cnt) = pg_fetch_array($result,$i);//
	$survey_set_shop_list[$q_survey_set_id]=$q_shop_id;
	$answer_kind_cnt[$q_survey_set_id][$q_answer_kind]=$q_cnt;
}
$ybase->title = "アンケートGMB集計";

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
	<a class="dropdown-item active" href="./ans_gmb.php">GMB</a>
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

<div class="container">

<p></p>
<div style="font-size:80%;margin:5px;">

<form action="ans_gmb.php" method="post" id="form1">
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

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th width="200">店舗名+NO</th>
<th width="200">{$ybase->answer_kind_list[1]}</th>
<th width="200">{$ybase->answer_kind_list[2]}</th>
<th width="200">{$ybase->answer_kind_list[9]}</th>
<th width="200">誘導率</th>
<th width="200">クリック率</th>
</tr>
</thead>

<tbody>
HTML;

asort($survey_set_shop_list);
/////////////////////
foreach($survey_set_shop_list as $key => $shop_id){
	$shop_name = $ybase->section_list[$shop_id];
	$ansgmb = $answer_kind_cnt[$key][2] + $answer_kind_cnt[$key][9];
	$answersum = $ansgmb + $answer_kind_cnt[$key][1];
	if($answersum){
		$ansgmb_rate = round($ansgmb / $answersum *100,1);
	}else{
		$ansgmb_rate = "-";
	}
	if($ansgmb){
		$ansgmb_click_rate = round($answer_kind_cnt[$key][9] / $ansgmb *100,1);
	}else{
		$ansgmb_click_rate = "-";
	}

$ybase->ST_PRI .= <<<HTML
<tr align="center">
<td width="200">$shop_name</td>
<td width="200" align="right">{$answer_kind_cnt[$key][1]}</td>
<td width="200" align="right">{$answer_kind_cnt[$key][2]}</td>
<td width="200" align="right">{$answer_kind_cnt[$key][9]}</td>
<td width="200" align="right">{$ansgmb_rate}%({$ansgmb}/{$answersum})</td>
<td width="200" align="right">{$ansgmb_click_rate}%({$answer_kind_cnt[$key][9]}/{$ansgmb})</td>
</tr>
HTML;
}


$ybase->ST_PRI .= <<<HTML
</tbody>
</table>
</div>


</div>



</div>
</div>
<p></p>

HTML;


$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>