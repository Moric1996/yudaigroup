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
$tablerate = $ybase->mbscale(6.5);

if(!preg_match("/^[0-9]+$/",$t_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20001");
}
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20002");
}
$rank->unitname_make($t_shop_id,$t_month);
$YEAR = substr($t_month,0,4);
$MONTH = intval(substr($t_month,4,2));
$nowYYMM = date("Ym");
if(!$target_day){
	$nowday = date("j");
	if($nowYYMM > $t_month){
		$target_day = date("t",mktime(0,0,0,$MONTH,1,$YEAR));
	}else{
		$target_day = date("j",mktime(0,0,0,$MONTH,$nowday,$YEAR));
	}
	if($target_day < 1){
		$target_day = 1;
	}
}
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

/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}


//////////////////////////////////////////条件
$param = "t_month=$t_month&t_shop_id=$t_shop_id";
if($all_ca == 1){
	$addsql = "month = $t_month and status = '1'";
	$carria_name = "全店";
	$car_link = "";
	foreach($rank_section_name as $key => $val){
		$car_link .= "<a href=\"group_rank2.php?t_month=$t_month&target_day=$target_day&t_shop_id=$key\" class=\"btn btn-sm btn-outline-warning\">$val</a>";
	}

}else{
	$addsql = "month = $t_month and shop_id = $t_shop_id and status = '1'";
	$carria_name = $rank_section_name[$t_shop_id];
	$car_link = "<a href=\"group_rank2.php?$param&target_day=$target_day&all_ca=1\" class=\"btn btn-sm btn-outline-warning\">全店</a> ";
	foreach($rank_section_name as $key => $val){
		if($t_shop_id == $key){continue;}
		$car_link .= "<a href=\"group_rank2.php?t_month=$t_month&target_day=$target_day&t_shop_id=$key\" class=\"btn btn-sm btn-outline-warning\">$val</a> ";
	}

}
//////////////////////////////////////////
/////////////////////////グループリスト
$sql = "select group_id,group_name,allot,shop_id from telecom2_group where {$addsql} and status = '1' order by group_id";
$result = $ybase->sql($conn,$sql);
$group_num = pg_num_rows($result);
if(!$group_num){
	$ybase->error("チームの設定がされていません。先にチームの設定をしてください。");
}
$color_cnt=array();
for($i=0;$i<$group_num;$i++){
	list($q_group_id,$q_group_name,$q_allot,$q_shop_id) = pg_fetch_array($result,$i);
	$group_name_lt[$q_group_id] = $q_group_name;
	$group_shop_lt[$q_group_id] = $q_shop_id;
	$color_cnt[$q_shop_id]++;
	$group_color_lt[$q_group_id] = $color_cnt[$q_shop_id];
}
/////////////////////////グループ構成員リスト
$sql = "select group_const_id,group_id,employee_id from telecom2_group_const where {$addsql} and status = '1' order by group_id,group_const_id";
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
$sql = "select item_id,score from telecom2_item where {$addsql} order by item_id";

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

$ybase->title = "Y☆Judge-チーム実績";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("チーム実績");

$ybase->ST_PRI .= <<<HTML

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./rank_top.php?$param" role="button">Y☆JudgeTOPに戻る</a></div>
<p></p>
<h5 style="text-align:center;">【{$carria_name} {$YEAR}年{$MONTH}月{$target_day}日】チーム実績</h5>

<p></p>
<a href="group_rank2.php?$param&target_day=$bf" class="btn btn-sm btn-outline-secondary{$bf_disable}">前日</a>
<a href="group_rank2.php?$param&target_day=$nx" class="btn btn-sm btn-outline-secondary{$nx_disable}">翌日</a>
&nbsp;<a href="personal_rank2.php?$param&target_day=$target_day&all_ca=$all_ca" class="btn btn-sm btn-outline-info">個人</a>
&nbsp;<nobr>$car_link</nobr>
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
<tr bgcolor="#cccccc" align="center">
<th></th>
<th>目標</th>
<th>実績</th>
<th>実績順位</th>
<th>達成率</th>
<th>達成率順位</th>
<th>実績PT</th>
<th>達成率PT</th>
<th>総合PT</th>
<th>総合順位</th>
</tr>
  </thead>
  <tbody>
HTML;
//目標
$group_goal_lt=array();
$sql = "select item_id,group_id,goal_num from telecom2_goal_group where {$addsql} order by item_id,group_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_group_id,$q_goal_num) = pg_fetch_array($result3,$i);
	$group_goal_lt[$q_group_id] += $q_goal_num * $item_score_lt[$q_item_id];
}
//当日実績
$group_action_lt=array();
$sql = "select item_id,employee_id,sum(action_num) from telecom2_action where {$addsql} and day <= $target_day group by item_id,employee_id order by item_id,employee_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_employee_id,$q_action_num) = pg_fetch_array($result3,$i);
	$q_group_id = $group_emp_lt[$q_employee_id];
	$group_action_lt[$q_group_id] += $q_action_num * $item_score_lt[$q_itemid];
}
/////////////////////////////////////////////
foreach($group_name_lt as $key => $val){
	if(!$group_action_lt[$key]){
		$group_action_lt[$key] = 0;
	}
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
	if(!$val){
		$group_action_rank[$key] = 10000;
		$group_action_point[$key] = 0;
	}else{
		$group_action_rank[$key] = $rankno;
		$group_action_point[$key] = ($group_num * 2) - (($rankno - 1) * 2);
	}
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
	if(!$val){
		$achieve_rate_rank[$key] = 10000;
		$achieve_rate_point[$key] = 0;
	}else{
		$achieve_rate_rank[$key] = $rankno;
		$achieve_rate_point[$key] = $group_num - ($rankno - 1);
	}
}
foreach($group_name_lt as $key => $val){
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
	if(!$val){
		$all_point_rank[$key] = 10000;
	}
}
asort($all_point_rank);

$kk=0;

foreach($all_point_rank as $key => $val){
$kk++;
if($kk%2 == 0){
	$hbgcolor = "#fafafa";
}else{
	$hbgcolor = "#ffffff";
}

/////////実績
$d_action = $group_action_lt[$key];
if(!$d_action){
	$d_action = 0;
}
/////////実績順位
switch($group_action_rank[$key]){
	case 10000:
		$rank_action = "-";
		break;
	case 1:
	case 2:
	case 3:
		$rank_action = "<img src=\"./image/oukan".$group_action_rank[$key].".png\" height=\"18\" border=\"0\">";
		break;
	default:
		$rank_action = $group_action_rank[$key]."位";
		break;
}
/////////達成率
$d_rate = round($achieve_rate[$key],1);
if(!$d_rate){
	$d_rate = 0;
}
/////////達成率順位
switch($achieve_rate_rank[$key]){
	case 10000:
		$rank_rate = "-";
		break;
	case 1:
	case 2:
	case 3:
		$rank_rate = "<img src=\"./image/oukan".$achieve_rate_rank[$key].".png\" height=\"18\" border=\"0\">";
		break;
	default:
		$rank_rate = $achieve_rate_rank[$key]."位";
		break;
}
/////////総合順位
switch($all_point_rank[$key]){
	case 10000:
		$rank_total = "-";
		break;
	case 1:
	case 2:
	case 3:
		$rank_total = "<img src=\"./image/oukan".$all_point_rank[$key].".png\" height=\"18\" border=\"0\">";
		break;
	default:
		$rank_total = $all_point_rank[$key]."位";
		break;
}


$gcolor = $group_color_lt[$key];
$shopid = $group_shop_lt[$key];
if($all_ca){
	$nam_color = $carriercolor[$shopid];
}else{
	$nam_color = $group_bgcolor[$gcolor];
}
$ybase->ST_PRI .= <<<HTML

<tr align="center" bgcolor="$hbgcolor">
<td bgcolor="{$nam_color}"> {$group_name_lt[$key]}</td>
<td>{$group_goal_lt[$key]}pt</td>
<td>{$d_action}pt</td>
<td bgcolor="{$rankbgcolor[$rank_action]}">{$rank_action}</td>
<td>{$d_rate}%</td>
<td bgcolor="{$rankbgcolor[$rank_rate]}">{$rank_rate}</td>
<td>{$group_action_point[$key]}pt</td>
<td>{$achieve_rate_point[$key]}pt</td>
<td>{$all_point[$key]}pt</td>
<td bgcolor="{$rankbgcolor[$rank_total]}">{$rank_total}</td>
</tr>

HTML;
}

$graph_data_action="";
$graph_data_rate="";

asort($group_action_rank);
foreach($group_action_rank as $key => $val){
	$d_action = $group_action_lt[$key];
	if(!$d_action){
		$d_action = 0;
	}
	$graph_data_action .= ",\n['{$group_name_lt[$key]}',{$d_action}]";
}

asort($achieve_rate_rank);
foreach($achieve_rate_rank as $key => $val){
$d_rate = round($achieve_rate[$key],1);
	if(!$d_rate){
		$d_rate = 0;
	}
	$graph_data_rate .= ",\n['{$group_name_lt[$key]}',{$d_rate}]";
}


$ybase->ST_PRI .= <<<HTML

 </tbody>
</table>
</div>
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
                title: 'チーム実績ランキング',
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
                title: 'チーム達成率ランキング',
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