<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/check.inc');
include('./inc/check_list.inc');

$ybase = new ybase();
$check = new check();
$ybase->session_get();
$tablerate = $ybase->mbscale(6);

$YEAR = substr($t_month,0,4);
$MONTH = intval(substr($t_month,4,2));
$nowYYMM = date("Ym");
if($yy == "" || $mm == ""){
	$yy = date("Y");
	$mm = date("n");
	$startYM = date("Y-m-01");
	$endYM = date("Y-m-d",mktime(0,0,0,$mm + 1,0,$yy));
}else{
	$newyy = date("Y",mktime(0,0,0,$mm,1,$yy));
	$newmm = date("n",mktime(0,0,0,$mm,1,$yy));
	$yy = $newyy;
	$mm = $newmm;
	$startYM = date("Y-m-01",mktime(0,0,0,$mm + 1,0,$yy));
	$endYM = date("Y-m-d",mktime(0,0,0,$mm + 1,0,$yy));
}
$nx = $mm + 1;
$bf = $mm - 1;
$maxdays = date("t",mktime(0,0,0,$mm,1,$yy));

/////////////////////////////////////////
if(!$ybase->my_position_class || ($ybase->my_position_class > 40)){
	$check->protect = 1;
}

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$param = "";
$addsql = "";
//////////////////////////////////////////
$ybase->make_employee_list();
$ybase->make_now_section_list();


$sql = "select to_char(action_date,'FMDD') from ck_check_action where action_date between '$startYM' and '$endYM' and status > '0' group by action_date order by action_date";
$result = $ybase->sql($conn,$sql);
$action_date_num = pg_num_rows($result);
if($action_date_num){
	$action_date_arr = pg_fetch_all_columns($result,0);
}else{
	$action_date_arr = array();
}



$sql = "select ckaction_id,section_id,to_char(action_date,'FMDD'),status,employee_id from ck_check_action where action_date between '$startYM' and '$endYM' and status > '0' order by section_id,action_date";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$q_ckaction_list = array();
$q_ckaction_status = array();
$q_ckaction_employee = array();

for($i=0;$i<$num;$i++){
	list($q_ckaction_id,$q_section_id,$q_action_date,$q_status,$q_employee_id) = pg_fetch_array($result,$i);
	$q_ckaction_list[$q_section_id][$q_action_date] = $q_ckaction_id;
	$q_ckaction_status[$q_ckaction_id] = $q_status;
	$q_ckaction_employee[$q_ckaction_id] = $q_employee_id;
}

$ybase->title = "店舗チェック-入力状況確認(月毎)";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("入力状況確認(月毎)");
$ybase->ST_PRI .= $check->check_menu("4");

/////////////////////////////////////////////////
/////////////////////////////////////////////////////

$ybase->ST_PRI .= <<<HTML

<div class="container">
<h5 style="text-align:center;">【{$yy}年{$mm}月】入力状況確認(月毎)</h5>

<p></p>
<table border="0" width="100%">
<tr>
<td>
<nobr><a href="vw_monthly.php?yy=$yy&mm=$bf" class="btn btn-sm btn-outline-secondary{$bf_disable}">前月</a>
<a href="vw_monthly.php?yy=$yy&mm=$nx" class="btn btn-sm btn-outline-secondary{$nx_disable}">翌月</a>
</nobr>
</td><td>
</td></tr>
</table>
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
<tr bgcolor="#cccccc" align="center">
<th>店舗</th>
HTML;
foreach($action_date_arr as $key2 => $val2){

$ybase->ST_PRI .= <<<HTML
<th>{$val2}日</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
  </thead>
  <tbody>
HTML;
/////////////////////////////////////////////

$kk=0;

foreach($ybase->section_list as $key => $val){
if($key < 100){continue;}
if($key > 1000){continue;}
if(preg_match("/^3[0-9]{2}$/",$key)){continue;}
$kk++;
if($kk%2 == 0){
	$hbgcolor = "#fafafa";
}else{
	$hbgcolor = "#ffffff";
}

$ybase->ST_PRI .= <<<HTML
<tr bgcolor="$hbgcolor">
<td align="center">
<nobr>$val</nobr>
</td>

HTML;
	foreach($action_date_arr as $key2 => $val2){
	$tar_ckaction_id = $q_ckaction_list[$key][$val2];
	$tar_status = $q_ckaction_status[$tar_ckaction_id];
	$tar_employee_id = $q_ckaction_employee[$tar_ckaction_id];
	$tar_employee_name = $ybase->employee_name_list[$tar_employee_id];
	$tar_employee_name = str_replace("　", " ",$tar_employee_name);
	$pieces = explode(" ", $tar_employee_name);
	$tar_employee_name = $pieces[0];
	if($tar_status == '1'){
		$d_sta = "<a href=\"./check_in.php?t_shop_id=$key&target_ckaction_id=$tar_ckaction_id\">〇</a><br>{$tar_employee_name}";
	}elseif($tar_status == '2'){
		$d_sta = "<a href=\"./check_in.php?t_shop_id=$key&target_ckaction_id=$tar_ckaction_id\">途中</a><br>{$tar_employee_name}";
	}else{
		$d_sta = "";
	}
$ybase->ST_PRI .= <<<HTML
<td align="center">
$d_sta
</td>
HTML;
	}

$ybase->ST_PRI .= <<<HTML
</tr>
HTML;
}

$ybase->ST_PRI .= <<<HTML

 </tbody>
</table>
</div>
</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>