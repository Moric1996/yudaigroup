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
$ybase->make_employee_list();

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
	$date_sql = "to_char(add_date,'YYYY-MM') = '$selyymm'";
	$g_width = 2000;
}else{
	$date_sql = "to_char(add_date,'YYYY-MM-DD') = '$selyymmdd'";
	$g_width = 1000;
}


$sql = "select plog_id,survey_set_id,shop_id,answer_id,employee_id,session,ua,ip,to_char(add_date,'YYYY/MM/DD HH24:MI:SS'),log_type from survey_private_log where {$date_sql} order by add_date desc";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

$ybase->title = "アンケート集計";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#insurvey_set_id,#inmonth,#ttype,#monthon,#free_class_id').change(function(){
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
      <a href="log_view.php" class="nav-link active">ログ</a>
    </li>
  </ul>
</div>

<div class="container">

<p></p>
<div style="font-size:80%;margin:5px;">

<form action="ans_ana.php" method="post" id="form1">
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

$selectdate

</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th width="100">LOG_ID</th>
<th width="100">アンケートNO</th>
<th width="100">店舗名</th>
<th width="100">回答ID</th>
<th width="100">操作者</th>
<th width="100">UA/IP</th>
<th width="100">操作日時</th>
<th width="100">操作内容</th>
</tr>
</thead>

<tbody>
HTML;
for($i=0;$i<$num;$i++){
	list($q_plog_id,$q_survey_set_id,$q_shop_id,$q_answer_id,$q_employee_id,$q_session,$q_ua,$q_ip,$q_add_date,$q_log_type) = pg_fetch_array($result,$i);

/////////////////////
$ybase->ST_PRI .= <<<HTML
<tr align="center">
<td width="100">{$q_plog_id}</td>
<td width="100">{$q_survey_set_id}</td>
<td width="100">{$ybase->section_list[$q_shop_id]}</td>
<td width="100">{$q_answer_id}</td>
<td width="100">{$ybase->employee_name_list[$q_employee_id]}</td>
<td width="100">{$q_ua}<br>({$q_ip})</td>
<td width="100">{$q_add_date}</td>
<td width="100">{$ybase->survey_log_list[$q_log_type]}</td>
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