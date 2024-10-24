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
$ybase->session_get($sess0,$mei0);
//$edit_f = 1;
/////////////////////////////////////////
$ybase->make_shop_list();
//$ybase->shop_list['3001'] = "雄大ゴルフ熱函";	// 雄大ゴルフ熱函
//$ybase->shop_list['3002'] = "雄大ゴルフ清水町";	//雄大ゴルフ清水町
$monthon = 1;//月単位で
$ybase->make_employee_list("1");


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

if($monthon){
	$date_sql = "between '{$selyymm}-01' and '{$selyymm}-{$maxday}'";
	$g_width = 2000;
}else{
	$date_sql = "= '$selyymmdd'";
	$g_width = 1000;
}
$sql = "select survey_set_id,shop_id,survey_no,card_sum,title,to_char(add_date,'YYYY/MM/DD') from survey_set where company_id = 1";

if($shop_id){
//	$sql .= " and shop_id = '$shop_id'";
}
$sql .= " and status = '1' order by shop_id,survey_no";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$mun0 = 0;
for($i=0;$i<$num;$i++){
	list($q_survey_set_id,$q_shop_id,$q_survey_no,$q_card_sum,$q_title,$q_add_date) = pg_fetch_array($result,$i);
	$shop_list[$q_shop_id] = $ybase->section_list[$q_shop_id];
	if($shop_id && ($shop_id != $q_shop_id)){
		continue;
	}
	$q_title = trim($q_title);
	$q_shop_id_arr[$q_survey_set_id] = $q_shop_id;
	$q_survey_no_arr[$q_survey_set_id] = $q_survey_no;
	$q_card_sum_arr[$q_survey_set_id] = $q_card_sum;
	$q_title_arr[$q_survey_set_id] = $q_title;
	$q_add_date_arr[$q_survey_set_id] = $q_add_date;
	$num0++;
}

$ybase->title = "アンケート一覧";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#inshop_id,#inmonth,#ttype,#monthon,#free_class_id').change(function(){
		$("#form1").submit();
	});
});

</script>
<div class="container">
  <ul class="nav nav-tabs nav-fill" id="myTab" style="font-size:70%;">
    <li class="nav-item">
      <a href="./survey_list.php" class="nav-link active">アンケート一覧</a>
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
<!--<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./ana_top.php?$param" role="button">戻る</a></div>-->

<div style="font-size:80%;margin:5px;">

<form action="survey_list.php" method="post" id="form1">
<select name="shop_id" id="inshop_id">
<option value="0">全店</option>
HTML;
foreach($shop_list as $key => $val){
	if("$shop_id" == "$key"){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"{$addselect}>$val</option>
HTML;
}
if($monthon == 1){
	$monthon_checked = " checked";
	$selectdate = "<input type=\"month\" name=\"selyymm\" value=\"$selyymm\" id=\"inmonth\">";
}else{
	$monthon_checked = "";
	$selectdate = "<input type=\"date\" name=\"selyymmdd\" value=\"$selyymmdd\" id=\"inmonth\">";
}
$ybase->ST_PRI .= <<<HTML

</select>
HTML;


$ybase->ST_PRI .= <<<HTML

</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th>NO</th>
<th>店舗名</th>
<th>アンケートNO</th>
<th>タイトル</th>
<th>作成日</th>
<th>回答数</th>
<th>回答</th>
</tr>
</thead>

<tbody>
HTML;

$sql = "select survey_set_id,count(*) from survey_answer where status = '1'";
if($shop_id){
	$sql .= " and shop_id = '$shop_id'";
}
$sql .= " group by survey_set_id order by count(*) desc";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

for($i=0;$i<$num;$i++){
	list($q_survey_set_id,$answer_count) = pg_fetch_array($result,$i);
	$answer_shop_id = $q_shop_id_arr[$q_survey_set_id];
	$k = $i + 1;
$ybase->ST_PRI .= <<<HTML
<tr>
<td align="right">$k</td>
<td align="center">{$ybase->section_list[$answer_shop_id]}</td>
<td align="right">{$q_survey_no_arr[$q_survey_set_id]}</td>
<td align="center">{$q_title_arr[$q_survey_set_id]}</td>
<td align="center">{$q_add_date_arr[$q_survey_set_id]}</td>
<td align="center">$answer_count</td>
<td align="center"><a href="./ans_list.php?survey_set_id=$q_survey_set_id&shop_id=$answer_shop_id">閲覧</a></td>
</tr>

HTML;
unset($q_shop_id_arr[$q_survey_set_id]);
}
foreach($q_shop_id_arr as $key => $val){
	$answer_shop_id = $q_shop_id_arr[$key];
	$k++;
$ybase->ST_PRI .= <<<HTML
<tr>
<td align="right">$k</td>
<td align="center">{$ybase->section_list[$answer_shop_id]}</td>
<td align="right">{$q_survey_no_arr[$key]}</td>
<td align="center">{$q_title_arr[$key]}</td>
<td align="center">{$q_add_date_arr[$key]}</td>
<td align="center">0</td>
<td align="center"><a href="./ans_list.php?survey_set_id=$key&shop_id=$answer_shop_id">閲覧</a></td>
</tr>

HTML;
}
$ybase->ST_PRI .= <<<HTML
</tbody>
</table>
</div>
</div>



</div>
<table width="100%">
<tr><td align="center">
<span id="target"></span>
</td></tr>
</table>
</div>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>