<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/rank.inc');
include('./inc/rank_list.inc');

$ybase = new ybase();
$rank = new rank();
$ybase->session_get();
$tablerate = $ybase->mbscale(7);

if(!preg_match("/^[0-9]+$/",$t_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20001");
}
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20002");
}
$rank->unitname_make($t_shop_id,$t_month);
if(!$target_day){
	$nowday = date("j");
	$target_day = date("j",mktime(0,0,0,$MONTH,$nowday - 1,$YEAR));
	if($target_day < 1){
		$target_day = 1;
	}
}
$YEAR = substr($t_month,0,4);
$MONTH = intval(substr($t_month,4,2));
$maxday = date("t",mktime(0,0,0,$MONTH,$target_day,$YEAR));
$nissin = round($target_day/$maxday*100);

$bf = $target_day - 1;
$nx = $target_day + 1;
if($bf < 1){
	$bf_disable=" disabled";
}else{
	$bf_disable="";
}
if($nx > $maxday){
	$nx_disable=" disabled";
}else{
	$nx_disable="";
}

$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;
foreach($sec_employee_list as $key => $val){
if(strlen($val) > 9){
	list($aa,$bb) = explode(" ",$val);
	$sec_employee_list[$key] = $aa;
}
}

/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}

$rankbgcolor[1]="#dab300";
$rankbgcolor[2]="#bec1c3";
$rankbgcolor[3]="#c08d5e";

//////////////////////////////////////////条件
$param = "t_month=$t_month&t_shop_id=$t_shop_id";
$addsql = "month = $t_month and shop_id = $t_shop_id and status = '1'";
//////////////////////////////////////////
/////////////////////////グループリスト
$sql = "select group_id,group_name,allot from telecom_group where {$addsql} and status = '1' order by group_id";
$result = $ybase->sql($conn,$sql);
$group_num = pg_num_rows($result);
if(!$group_num){
	$ybase->error("チームの設定がされていません。先にチームの設定をしてください。");
}
for($i=0;$i<$group_num;$i++){
	list($q_group_id,$q_group_name,$q_allot) = pg_fetch_array($result,$i);
	$group_name_lt[$q_group_id] = $q_group_name;
}
/////////////////////////グループ構成員リスト
$sql = "select group_const_id,group_id,employee_id from telecom_group_const where {$addsql} and status = '1' order by group_id,group_const_id";
$result = $ybase->sql($conn,$sql);
$const_num = pg_num_rows($result);
if(!$const_num){
	$ybase->error("チームの設定がされていません。先にチームの設定をしてください。");
}
$group_cnt = array();
for($i=0;$i<$const_num;$i++){
	list($q_group_const_id,$q_group_id,$q_employee_id) = pg_fetch_array($result,$i);
	$group_emp_lt[$q_employee_id] = $q_group_id;
	$group_cnt[$q_group_id]++;
}
/////////////////////////配点リスト
$sql = "select item_id,score from telecom_item where {$addsql} order by item_id";

$result = $ybase->sql($conn,$sql);
$item_num = pg_num_rows($result);
if(!$item_num){
	$ybase->error("項目の設定がされていません。先に項目の設定をしてください。");
}
for($i=0;$i<$item_num;$i++){
	list($q_item_id,$q_score) = pg_fetch_array($result,$i);
	$item_score_lt[$q_item_id] = $q_score;
}

///////////////////////////////////////

$ybase->title = "Y☆Rank-チーム実績(個別)";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("チーム実績(個別)");

$ybase->ST_PRI .= <<<HTML

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./rank_top.php?$param" role="button">Y☆RankTOPに戻る</a></div>
<p></p>
<h5 style="text-align:center;">【{$rank_section_name[$t_shop_id]} {$YEAR}年{$MONTH}月】チーム実績(個別)</h5>

<p></p>
<b>{$MONTH}月 {$target_day}日</b>　
<span style="text-align:center;">
<a href="personal_rank.php?$param&target_day=$bf" class="btn btn-sm btn-outline-secondary{$bf_disable}">前日</a>
<a href="personal_rank.php?$param&target_day=$nx" class="btn btn-sm btn-outline-secondary{$nx_disable}">翌日</a>
</span>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="group_rank.php?$param&target_day=$target_day" class="btn btn-sm btn-outline-info">チーム別</a>

<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
<tr bgcolor="#cccccc" align="center">
<th></th>
HTML;
$nn=0;
$baseno=0;
foreach($group_emp_lt as $key => $val){
if($baseno != $val){
	$nn++;
	$baseno = $val;
}
$ybase->ST_PRI .= <<<HTML
<th bgcolor="{$group_bgcolor[$nn]}">{$sec_employee_list[$key]}</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
  </thead>
  <tbody>
HTML;
//目標
$group_goal_lt=array();
$sql = "select item_id,employee_id,goal_num from telecom_goal where {$addsql} order by item_id,employee_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_employee_id,$q_goal_num) = pg_fetch_array($result3,$i);
	$group_goal_lt[$q_employee_id] += $q_goal_num * $item_score_lt[$q_item_id];
}
//当日実績
$group_action_lt=array();
$sql = "select item_id,employee_id,sum(action_num) from telecom_action where {$addsql} and day <= $target_day group by item_id,employee_id order by item_id,employee_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_employee_id,$q_action_num) = pg_fetch_array($result3,$i);
	$group_action_lt[$q_employee_id] += $q_action_num * $item_score_lt[$q_item_id];
}
/////////////////////////////////////////////
foreach($group_emp_lt as $key => $val){
	if($group_goal_lt[$key]){
		$achieve_rate[$key] = ($group_action_lt[$key] / $group_goal_lt[$key]) * 100;
	}else{
		$achieve_rate[$key] = 0;
	}
}
arsort($group_action_lt);
$no=1;
$basescore="0";
$repeat = 1;
foreach($group_action_lt as $key => $val){
	if($basescore == $val){
		$rankno = $no - $repeat;
		$repeat++;
	}else{
		$rankno = $no;
		$repeat=1;
	}
	$basescore = $val;
	$no++;
	$group_action_rank[$key] = $rankno;
	$group_action_point[$key] = ($const_num * 2) - (($rankno - 1) * 2);
}
arsort($achieve_rate);
$no=1;
$basescore="0";
$repeat = 0;
foreach($achieve_rate as $key => $val){
	if($basescore == $val){
		$rankno = $no - $repeat;
		$repeat++;
	}else{
		$rankno = $no;
		$repeat=1;
	}
	$basescore = $val;
	$no++;
	$achieve_rate_rank[$key] = $rankno;
	$achieve_rate_point[$key] = $const_num - ($rankno - 1);
}
foreach($group_emp_lt as $key => $val){
	$all_point[$key] = $group_action_point[$key] + $achieve_rate_point[$key];
}
arsort($all_point);
$no=1;
$basescore="0";
$repeat = 1;
foreach($all_point as $key => $val){
	if($basescore == $val){
		$rankno = $no - $repeat;
		$repeat++;
	}else{
		$rankno = $no;
		$repeat=1;
	}
	$basescore = $val;
	$no++;
	$all_point_rank[$key] = $rankno;
}

$ybase->ST_PRI .= <<<HTML
<tr>
<td align="center" bgcolor="#ff9999">
目標
</td>
HTML;
foreach($group_emp_lt as $key => $val){
$ybase->ST_PRI .= <<<HTML
<td align="center">
{$group_goal_lt[$key]}pt
</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>

<tr>
<td align="center" bgcolor="#ff9999">
実績
</td>
HTML;
$graph_data_action="";
foreach($group_emp_lt as $key => $val){
$aaa = $group_action_lt[$key];
if(!$aaa){
	$aaa = 0;
}
$emp_name = $sec_employee_list[$key];
$graph_data_action .= ",\n['$emp_name',{$aaa}]";
$ybase->ST_PRI .= <<<HTML
<td align="center">
{$group_action_lt[$key]}pt
</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>

<tr>
<td align="center" bgcolor="#ff9999">
実績順位
</td>
HTML;
foreach($group_emp_lt as $key => $val){
$aaa = $group_action_rank[$key];
$ybase->ST_PRI .= <<<HTML
<td align="center" bgcolor="{$rankbgcolor[$aaa]}">
{$aaa}位
</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>

<tr>
<td align="center" bgcolor="#ff9999">
達成率
</td>
HTML;
foreach($group_emp_lt as $key => $val){
$aaa = round($achieve_rate[$key]);
if(!$aaa){
	$aaa = 0;
}
$emp_name = $sec_employee_list[$key];
$graph_data_rate .= ",\n['$emp_name',{$aaa}]";
$ybase->ST_PRI .= <<<HTML
<td align="center">
{$aaa}%
</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>

<tr>
<td align="center" bgcolor="#ff9999">
達成率順位
</td>
HTML;
foreach($group_emp_lt as $key => $val){
$aaa = $achieve_rate_rank[$key];
$ybase->ST_PRI .= <<<HTML
<td align="center" bgcolor="{$rankbgcolor[$aaa]}">
{$aaa}位
</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>

<tr>
<td align="center" bgcolor="#ff9999">
実績ポイント
</td>
HTML;
foreach($group_emp_lt as $key => $val){
$ybase->ST_PRI .= <<<HTML
<td align="center">
{$group_action_point[$key]}pt
</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>

<tr>
<td align="center" bgcolor="#ff9999">
達成ポイント
</td>
HTML;
foreach($group_emp_lt as $key => $val){
$ybase->ST_PRI .= <<<HTML
<td align="center">
{$achieve_rate_point[$key]}pt
</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>

<tr>
<td align="center" bgcolor="#ff9999">
総合ポイント
</td>
HTML;
foreach($group_emp_lt as $key => $val){
$ybase->ST_PRI .= <<<HTML
<td align="center">
{$all_point[$key]}pt
</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>

<tr>
<td align="center" bgcolor="#ff9999">
総合順位
</td>
HTML;
foreach($group_emp_lt as $key => $val){
$aaa = $all_point_rank[$key];
$ybase->ST_PRI .= <<<HTML
<td align="center" bgcolor="{$rankbgcolor[$aaa]}">
{$aaa}位
</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>

 </tbody>
</table>
<p></p>


<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(
        function() {
            var data = google.visualization.arrayToDataTable([
                ['','pt']
		$graph_data_action
            ]);
    
            var options = {
                title: '個人実績ランキング',
		vAxis: {title: 'pt'},
		legend: {position: 'none'}
            };
    
            var chart = new google.visualization.ColumnChart(document.getElementById('group_action_praph'));
            chart.draw(data, options);
        }
    );
</script>
<div id="group_action_praph" style="width:110%; height:250pt" ></div>

<script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(
        function() {
            var data = google.visualization.arrayToDataTable([
                ['','%']
		$graph_data_rate
            ]);
    
            var options = {
                title: '個人達成率ランキング',
		vAxis: {title: '%'},
		legend: {position: 'none'}
            };
    
            var chart = new google.visualization.ColumnChart(document.getElementById('group_rate_praph'));
            chart.draw(data, options);
        }
    );
</script>
<div id="group_rate_praph" style="width:110%; height:250pt" ></div>

















</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>