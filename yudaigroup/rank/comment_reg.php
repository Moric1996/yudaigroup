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
$target_month = $t_month;
$YEAR = substr($target_month,0,4);
$MONTH = intval(substr($target_month,4,2));
$nowYYMM = date("Ym");
if(!$target_day){
	$nowday = date("j");
	if($nowYYMM > $target_month){
		$target_day = date("t",mktime(0,0,0,$MONTH,1,$YEAR));
	}else{
		$target_day = date("j",mktime(0,0,0,$MONTH,$nowday,$YEAR));
	}
	$target_month = date("Ym",mktime(0,0,0,$MONTH,$nowday,$YEAR));
}else{
	$target_day = date("j",mktime(0,0,0,$MONTH,$target_day,$YEAR));
	$target_month = date("Ym",mktime(0,0,0,$MONTH,$target_day,$YEAR));
}
$YEAR = substr($target_month,0,4);
$MONTH = intval(substr($target_month,4,2));
$maxday = date("t",mktime(0,0,0,$MONTH,$target_day,$YEAR));
$nissin = round($target_day/$maxday*100);
$bf0 = $target_day - 1;
$nx0 = $target_day + 1;
if($bf0 < 1){
	$bf0_disable=" disabled";
}else{
	$bf0_disable="";
}
if($nx0 > $maxday){
	$nx0_disable=" disabled";
}else{
	$nx0_disable="";
}


$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;
if(!$sec_id){
	$sec_id = $ybase->my_section_id;
}
if(!$sec_id){
	$sec_id = $t_shop_id;
}
if(!array_key_exists($sec_id,$rank_section_list)){
	$sec_id = $t_shop_id;
}
/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$param = "t_month=$t_month&t_shop_id=$t_shop_id&sec_id=$sec_id";
$addsql = "shop_id = $t_shop_id and status = '1'";
$addsql2 = "month=$target_month and shop_id = $t_shop_id and status = '1'";
//////////////////////////////////////////

///////////////////////////////////////

//item名
$item_lt=array();
$sql = "select item_id,item_name,score from telecom_item where {$addsql2} order by order_num";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_item_name,$q_score) = pg_fetch_array($result3,$i);
	$item_lt[$q_item_id] = $q_item_name;
	$item_score[$q_item_id] = $q_score;
}
$sql = "select max(item_id) from telecom_item where {$addsql2}";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
if($num3){
	list($max_item_id) = pg_fetch_array($result3,0);
}
/////グループ確認
$sql = "select group_id,leader_employee_id from telecom_group where {$addsql2} order by group_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
$grpno=0;
$target_group_id=array();
for($i=0;$i<$num3;$i++){
	list($q_group_id,$q_leader_employee_id) = pg_fetch_array($result3,$i);
	$l_section_id = $ybase->get_section_id($q_leader_employee_id);
	if($l_section_id == $sec_id){
		$target_group_id[$grpno] = $q_group_id;
		$grpno++;
	}
}
$empno=0;
$target_emp_id = array();
foreach($target_group_id as $key => $val){
	$sql = "select employee_id from telecom_group_const where {$addsql2} and group_id = $val order by group_id";
	$result3 = $ybase->sql($conn,$sql);
	$num3 = pg_num_rows($result3);
	for($i=0;$i<$num3;$i++){
		list($q_employee_id) = pg_fetch_array($result3,$i);
		$target_emp_id[$empno] = $q_employee_id;
		$empno++;
	}
}
$addsql3 = " and group_id in (";
$k=0;
foreach($target_group_id as $key => $val){
	if($k > 0){
		$addsql3 .= ",";
	}
		$addsql3 .= "$val";
	$k++;
}
if($k == "0"){
	$addsql3 .= "0";
}
$addsql3 .= ")";
$addsql4 = " and employee_id in (";
$k=0;
foreach($target_emp_id as $key => $val){
	if($k > 0){
		$addsql4 .= ",";
	}
		$addsql4 .= "$val";
	$k++;
}
if($k == "0"){
	$addsql4 .= "0";
}

$addsql4 .= ")";
if($t_shop_id != 305){
	$addsql3 = "";
	$addsql4 = "";
}
//目標
$group_goal_lt=array();
$total_goal_pt = 0;
$sql = "select item_id,group_id,sum(goal_num) from telecom_goal_group where {$addsql2}{$addsql3} group by item_id,group_id order by item_id,group_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_group_id,$q_goal_num) = pg_fetch_array($result3,$i);
	$group_goal_lt[$q_item_id] += $q_goal_num;
	$total_goal_ipt[$q_item_id] += $q_goal_num * $item_score[$q_item_id];
	$total_goal_pt += $q_goal_num * $item_score[$q_item_id];
}
//当日実績
for($i=0;$i<$max_item_id;$i++){
	$action_day[$i]=0;
}
$today_total_action_pt = 0;
$sql = "select item_id,employee_id,sum(action_num) from telecom_action where {$addsql2}{$addsql4} and day = $target_day group by item_id,employee_id order by item_id,employee_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_employee_id,$q_action_num) = pg_fetch_array($result3,$i);
	$action_day[$q_item_id] += $q_action_num;
	$today_action_pt[$q_item_id] += $q_action_num * $item_score[$q_item_id];
	$today_total_action_pt += $q_action_num * $item_score[$q_item_id];
}
//当日まで実績
for($i=0;$i<$max_item_id;$i++){
	$action_to[$i]=0;
}
$total_action_pt = 0;
$sql = "select item_id,employee_id,sum(action_num) from telecom_action where {$addsql2}{$addsql4} and day <= $target_day group by item_id,employee_id order by item_id,employee_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_employee_id,$q_action_num) = pg_fetch_array($result3,$i);
	$action_to[$q_item_id] += $q_action_num;
	$total_act_ipt[$q_item_id] += $q_action_num * $item_score[$q_item_id];
	$total_action_pt += $q_action_num * $item_score[$q_item_id];
}
/////////////////////////////////////////////
if($t_shop_id == 302){
//SLS総販
if($t_month < 202105){
	$action_day_mnp = $action_day[146];
	$action_to_mnp = $action_to[146];
	$action_goal_lt_mnp = $group_goal_lt[146];
	$action_day_myg = $action_day[149];
	$action_to_myg = $action_to[149];
	$action_goal_lt_myg = $group_goal_lt[149];
}else{
	$action_day_mnp = $action_day[146] + $action_day[595];
	$action_to_mnp = $action_to[146] + $action_to[595];
	$action_goal_lt_mnp = $group_goal_lt[146] + $group_goal_lt[595];
	$action_day_myg = $action_day[149] + $action_day[150];
	$action_to_myg = $action_to[149] + $action_to[150];
	$action_goal_lt_myg = $group_goal_lt[149] + $group_goal_lt[150];
}
$action_day_sls = $action_day[171] + $action_day[172] + $action_day[173] + $action_day[174];
$action_to_sls = $action_to[171] + $action_to[172] + $action_to[173] + $action_to[174];
$action_goal_lt_sls = $group_goal_lt[171] + $group_goal_lt[172] + $group_goal_lt[173] + $group_goal_lt[174];

//20210813 2.HS新規(当日実績／月間累計/月間目標）145→144変更

$contents="$YEAR/$MONTH/$target_day
1.総販(当日実績／月間累計/月間目標）
{$action_day[143]}台/{$action_to[143]}台/{$group_goal_lt[143]}台

2.新規総販(当日実績／月間累計/月間目標）
{$action_day[144]}台/{$action_to[144]}台/{$group_goal_lt[144]}台

3.MNP(当日実績／月間累計/月間目標）
{$action_day_mnp}台/{$action_to_mnp}台/{$action_goal_lt_mnp}台

4.マイグレ(当日実績／月間累計/月間目標）
{$action_day_myg}台/{$action_to_myg}台/{$action_goal_lt_myg}台

5.店舗目標（当日／累計／月目標）
【 ドコモ光 】{$action_day[159]}件/{$action_to[159]}件/{$group_goal_lt[159]}件
【 SLS総販 】{$action_day_sls}件/{$action_to_sls}件/{$action_goal_lt_sls}件
【スマホ教室】{$action_day[180]}件/{$action_to[180]}件/{$group_goal_lt[180]}件

6.Y☆Mission
『通信サービスを通して人々の生活に喜びを』

7.Buzz☆Mission

【 来客数  】 組
【明日の司会】
【明日の周知文】
【明日の書記】
【明日の朝トレ】
";
}elseif($t_shop_id == 303){
//当日新規総販
$action_day_new = $action_day[259] + $action_day[272] + $action_day[274] + $action_day[312] + $action_day[314] + $action_day[315] + $action_day[316] + $action_day[333];
$action_day_all = $action_day_new + $action_day[317] + $action_day[319];
//当日新規点数
$action_day_newpt = $action_day[259] + ($action_day[272] * 0.2) + ($action_day[274] * 2) + $action_day[311] + ($action_day[312] * 0.5) + ($action_day[313] * 0.2) + ($action_day[314] * 0.2) + ($action_day[316] * 0.5) + ($action_day[317] * 0.2);
//累計新規総販
$action_to_new = $action_to[259] + $action_to[272] + $action_to[274] + $action_to[312] + $action_to[314] + $action_to[315] + $action_to[316] + $action_to[333];
$action_to_all = $action_to_new + $action_to[317] + $action_to[319];
//累計新規点数
$action_to_newpt = $action_to[259] + $action_to[272] * 0.2 + $action_to[274] * 2 + $action_to[311] + $action_to[312] * 0.5 + $action_to[313] * 0.2 + $action_to[314] * 0.2 + $action_to[316] * 0.5 + $action_to[317] * 0.2;

//累目標新規総販
$group_goal_lt_new = $group_goal_lt[259] + $group_goal_lt[272] + $group_goal_lt[274] + $group_goal_lt[312] + $group_goal_lt[314] + $group_goal_lt[315] + $group_goal_lt[316] + $group_goal_lt[333];
$group_goal_lt_all = $group_goal_lt_new + $group_goal_lt[317] + $group_goal_lt[319];

//MNP
$action_day_mnp = $action_day[274] + $action_day[311];
$action_to_mnp = $action_to[274] + $action_to[311];
$group_goal_lt_mnp = $group_goal_lt[274] + $group_goal_lt[311];

$colleunit = $rank->unitname_list[326];
if($action_to_all){
	$action_to_colletan = sprintf("%01.2f",round($action_to[326] / $action_to_all,2));
}else{
	$action_to_colletan = "0.00";
}
$contents="$YEAR/$MONTH/$target_day
1.販売台数(総販/内新規/新規点数)
 {$action_day_all}台/{$action_day_new}台/{$action_day_newpt}台

2.累計台数(総販/内新規/新規点数/目標)
 {$action_to_all}台/{$action_to_new}台/{$action_to_newpt}台/{$group_goal_lt_all}台

3.MNP(当日/累計/目標)
 {$action_day_mnp}台/{$action_to_mnp}台/{$group_goal_lt_mnp}台

4.店舗目標(当日/累計/目標)
【auひかり】{$action_day[329]}件/{$action_to[329]}件/{$group_goal_lt[329]}件
【ＣＡＴＶ】{$action_day[331]}件/{$action_to[331]}件/{$group_goal_lt[331]}件
【固定代替】{$action_day[333]}件/{$action_to[333]}件/{$group_goal_lt[333]}件
【コミュファ光】{$action_day[330]}件/{$action_to[330]}件/{$group_goal_lt[330]}件
【BIGLOBE光】{$action_day[332]}件/{$action_to[332]}件/{$group_goal_lt[332]}件
【クレカ】{$action_day[260]}件/{$action_to[260]}件/{$group_goal_lt[260]}件
【じぶん銀行】{$action_day[323]}件/{$action_to[323]}件/{$group_goal_lt[323]}件
【でんき】{$action_day[324]}件/{$action_to[324]}件/{$group_goal_lt[324]}件

5.+1collection(当日/総販単価/当月累計/目標)
{$action_day[326]}{$colleunit}円/ {$action_to_colletan}{$colleunit}円/{$action_to[326]}{$colleunit}円/{$group_goal_lt[326]}{$colleunit}円

6.総合指標
  PT

7.Y☆Mission
『通信サービスを通して人々の生活に喜びを』

8.Buzz☆Mission

";
}elseif($t_shop_id == 305){
if($t_month < 202007){
//ＳＢ内新規
$action_day_newsb = $action_day[258] + $action_day[264];
$action_to_newsb = $action_to[258] + $action_to[264];
$group_goal_lt_newsb = $group_goal_lt[258] + $group_goal_lt[264];
//ＳＢ総　販
$action_day_allsb = $action_day_newsb + $action_day[268] + $action_day[269] + $action_day[278] + $action_day[279] + $action_day[280] + $action_day[309];
$action_to_allsb = $action_to_newsb + $action_to[268] + $action_to[269] + $action_to[278] + $action_to[279] + $action_to[280] + $action_to[309];
$group_goal_lt_allsb = $group_goal_lt_newsb + $group_goal_lt[268] + $group_goal_lt[269] + $group_goal_lt[278] + $group_goal_lt[279] + $group_goal_lt[280] + $group_goal_lt[309];
//ＹＭ内新規
$action_day_newym = $action_day[282] + $action_day[283];
$action_to_newym = $action_to[282] + $action_to[283];
$group_goal_lt_newym = $group_goal_lt[282] + $group_goal_lt[283];
//ＹＭ総　販
$action_day_allym = $action_day_newym + $action_day[284] + $action_day[310];
$action_to_allym = $action_to_newym + $action_to[284] + $action_to[310];
$group_goal_lt_allym = $group_goal_lt_newym + $group_goal_lt[284] + $group_goal_lt[310];
//Ｎｅｔ
$action_day_net = $action_day[285] + $action_day[286];
$action_to_net = $action_to[285] + $action_to[286];
$group_goal_lt_net = $group_goal_lt[285] + $group_goal_lt[286];

if($action_day_allsb){
	$action_day_tan = sprintf("%01.2f",round($action_day[295] / $action_day_allsb,2));
}else{
	$action_day_tan = "0.00";
}
if($action_to_allsb){
	$action_to_tan = sprintf("%01.2f",round($action_to[295] / $action_to_allsb,2));
}else{
	$action_to_tan = "0.00";
}
if($group_goal_lt_allsb){
	$group_goal_lt_tan = sprintf("%01.2f",round($group_goal_lt[295] / $group_goal_lt_allsb,2));
}else{
	$group_goal_lt_tan = "0.00";
}
$seleunit = $rank->unitname_list[295];

if($total_goal_pt){
	$achieve_rate = sprintf("%01.1f",round($total_action_pt / $total_goal_pt * 100,1));
}
//【総販単価】{$action_day_tan}{$seleunit}/{$action_to_tan}{$seleunit}/{$group_goal_lt_tan}{$seleunit}

$contents="$YEAR/$MONTH/$target_day
1.販売台数(本日/累計/目標)
【ＳＢ】
総　販: {$action_day_allsb}件/{$action_to_allsb}件/{$group_goal_lt_allsb}件
内新規: {$action_day_newsb}件/{$action_to_newsb}件/{$group_goal_lt_newsb}件

【ＹＭ】
総　販: {$action_day_allym}件/{$action_to_allym}件/{$group_goal_lt_allym}件
内新規: {$action_day_newym}件/{$action_to_newym}件/{$group_goal_lt_newym}件

2.重点項目(本日/累計/目標)
【ＭＮＰ】{$action_day[264]}件/{$action_to[264]}件/{$group_goal_lt[264]}件
【Ｔａｂ】{$action_day[269]}件/{$action_to[269]}件/{$group_goal_lt[269]}件
【Ｎｅｔ】{$action_day_net}件/{$action_to_net}件/{$group_goal_lt_net}件
【でんき】{$action_day[288]}件/{$action_to[288]}件/{$group_goal_lt[288]}件

3.セレクション販売（本日／累計／目標）
【販売粗利】{$action_day[295]}{$seleunit}/{$action_to[295]}{$seleunit}/{$group_goal_lt[295]}{$seleunit}

4.Ｙ☆Ｒａｎｋ獲得ポイント
【達成率】{$achieve_rate}%({$total_action_pt}pt/{$total_goal_pt}pt)

";
}elseif($t_month < 202101){
//202007以降202012まで
//対外　スマホ⑤
$action_day_smpho = $action_day[482] + $action_day[483] + $action_day[478] + $action_day[479];
$action_to_smpho = $action_to[482] + $action_to[483] + $action_to[478] + $action_to[479];
$group_goal_lt_smpho = $group_goal_lt[482] + $group_goal_lt[483] + $group_goal_lt[478] + $group_goal_lt[479];
//タブ　レット⑥
$action_day_tab = $action_day[491] + $action_day[492];
$action_to_tab = $action_to[491] + $action_to[492];
$group_goal_lt_tab = $group_goal_lt[491] + $group_goal_lt[492];
//ＳＢ内新規
$action_day_newsb = $action_day_smpho + $action_day[481] + $action_day[484] + $action_day[477] + $action_day[480];
$action_to_newsb = $action_to_smpho + $action_to[481] + $action_to[484] + $action_to[477] + $action_to[480];
$group_goal_lt_newsb = $group_goal_lt_smpho + $group_goal_lt[481] + $group_goal_lt[484] + $group_goal_lt[477] + $group_goal_lt[480];
//ＳＢ総　販
$action_day_allsb = $action_day_newsb + $action_day_tab + $action_day[485] + $action_day[486] + $action_day[487] + $action_day[488] + $action_day[489] + $action_day[490];
$action_to_allsb = $action_to_newsb + $action_to_tab + $action_to[485] + $action_to[486] + $action_to[487] + $action_to[488] + $action_to[489] + $action_to[490];
$group_goal_lt_allsb = $group_goal_lt_newsb + $group_goal_lt_tab + $group_goal_lt[485] + $group_goal_lt[486] + $group_goal_lt[487] + $group_goal_lt[488] + $group_goal_lt[489] + $group_goal_lt[490];
//ＹＭ内新規
$action_day_newym = $action_day[521] + $action_day[522] + $action_day[523] + $action_day[518] + $action_day[519] + $action_day[520];
$action_to_newym = $action_to[521] + $action_to[522] + $action_to[523] + $action_to[518] + $action_to[519] + $action_to[520];
$group_goal_lt_newym = $group_goal_lt[521] + $group_goal_lt[522] + $group_goal_lt[523] + $group_goal_lt[518] + $group_goal_lt[519] + $group_goal_lt[520];
//ＹＭ総　販
$action_day_allym = $action_day_newym + $action_day[524] + $action_day[525] + $action_day[526] + $action_day[527] + $action_day[528];
$action_to_allym = $action_to_newym + $action_to[524] + $action_to[525] + $action_to[526] + $action_to[527] + $action_to[528];
$group_goal_lt_allym = $group_goal_lt_newym + $group_goal_lt[524] + $group_goal_lt[525] + $group_goal_lt[526] + $group_goal_lt[527] + $group_goal_lt[528];
//ネットワーク⑦
$action_day_net = $action_day[493] + $action_day[494] + $action_day[496];
$action_to_net = $action_to[493] + $action_to[494] + $action_to[496];
$group_goal_lt_net = $group_goal_lt[493] + $group_goal_lt[494] + $group_goal_lt[496];
//おうちでんき⑧
$action_day_ele = $action_day[500];
$action_to_ele = $action_to[500];
$group_goal_lt_ele = $group_goal_lt[500];
//セレクション販売
$action_day_selec = $action_day[507];
$action_to_selec = $action_to[507];
$group_goal_lt_selec = $group_goal_lt[507];
//店舗評価ポイント
$group_action_point = $today_action_pt[481] + $today_action_pt[482] + $today_action_pt[483] + $today_action_pt[484] + $today_action_pt[477] + $today_action_pt[478] + $today_action_pt[479] + $today_action_pt[480] + $today_action_pt[485] + $today_action_pt[486] + $today_action_pt[487] + $today_action_pt[488] + $today_action_pt[489] + $today_action_pt[490] + $today_action_pt[491] + $today_action_pt[492] + $today_action_pt[493] + $today_action_pt[494] + $today_action_pt[496] + $today_action_pt[498] + $today_action_pt[499] + $today_action_pt[500] + $today_action_pt[501] + $today_action_pt[504] + $today_action_pt[505] + $today_action_pt[506] + $today_action_pt[514] + $today_action_pt[515] + $today_action_pt[521] + $today_action_pt[522] + $today_action_pt[523] + $today_action_pt[518] + $today_action_pt[519] + $today_action_pt[520] + $today_action_pt[524] + $today_action_pt[525] + $today_action_pt[526] + $today_action_pt[527] + $today_action_pt[528];
$group_total_point = $total_act_ipt[481] + $total_act_ipt[482] + $total_act_ipt[483] + $total_act_ipt[484] + $total_act_ipt[477] + $total_act_ipt[478] + $total_act_ipt[479] + $total_act_ipt[480] + $total_act_ipt[485] + $total_act_ipt[486] + $total_act_ipt[487] + $total_act_ipt[488] + $total_act_ipt[489] + $total_act_ipt[490] + $total_act_ipt[491] + $total_act_ipt[492] + $total_act_ipt[493] + $total_act_ipt[494] + $total_act_ipt[496] + $total_act_ipt[498] + $total_act_ipt[499] + $total_act_ipt[500] + $total_act_ipt[501] + $total_act_ipt[504] + $total_act_ipt[505] + $total_act_ipt[506] + $total_act_ipt[514] + $total_act_ipt[515] + $total_act_ipt[521] + $total_act_ipt[522] + $total_act_ipt[523] + $total_act_ipt[518] + $total_act_ipt[519] + $total_act_ipt[520] + $total_act_ipt[524] + $total_act_ipt[525] + $total_act_ipt[526] + $total_act_ipt[527] + $total_act_ipt[528];
$group_goal_point = $total_goal_ipt[481] + $total_goal_ipt[482] + $total_goal_ipt[483] + $total_goal_ipt[484] + $total_goal_ipt[477] + $total_goal_ipt[478] + $total_goal_ipt[479] + $total_goal_ipt[480] + $total_goal_ipt[485] + $total_goal_ipt[486] + $total_goal_ipt[487] + $total_goal_ipt[488] + $total_goal_ipt[489] + $total_goal_ipt[490] + $total_goal_ipt[491] + $total_goal_ipt[492] + $total_goal_ipt[493] + $total_goal_ipt[494] + $total_goal_ipt[496] + $total_goal_ipt[498] + $total_goal_ipt[499] + $total_goal_ipt[500] + $total_goal_ipt[501] + $total_goal_ipt[504] + $total_goal_ipt[505] + $total_goal_ipt[506] + $total_goal_ipt[514] + $total_goal_ipt[515] + $total_goal_ipt[521] + $total_goal_ipt[522] + $total_goal_ipt[523] + $total_goal_ipt[518] + $total_goal_ipt[519] + $total_goal_ipt[520] + $total_goal_ipt[524] + $total_goal_ipt[525] + $total_goal_ipt[526] + $total_goal_ipt[527] + $total_goal_ipt[528];

if($total_goal_pt){
	$achieve_rate = sprintf("%01.1f",round($total_action_pt / $total_goal_pt * 100,1));
}else{
	$achieve_rate = 0;
}

$contents="$YEAR/$MONTH/$target_day
1.販売台数(本日/累計/目標)
【ＳＢ】
総　販: {$action_day_allsb}件/{$action_to_allsb}件/{$group_goal_lt_allsb}件
内新規: {$action_day_newsb}件/{$action_to_newsb}件/{$group_goal_lt_newsb}件

【ＹＭ】
総　販: {$action_day_allym}件/{$action_to_allym}件/{$group_goal_lt_allym}件
内新規: {$action_day_newym}件/{$action_to_newym}件/{$group_goal_lt_newym}件

2.重点項目(本日/累計/目標)
【対外　スマホ】{$action_day_smpho}件/{$action_to_smpho}件/{$group_goal_lt_smpho}件
【タブ　レット】{$action_day_tab}件/{$action_to_tab}件/{$group_goal_lt_tab}件
【ネットワーク】{$action_day_net}件/{$action_to_net}件/{$group_goal_lt_net}件
【おうちでんき】{$action_day_ele}件/{$action_to_ele}件/{$group_goal_lt_ele}件

3.セレクション販売（本日／累計／目標）
【販売粗利】{$action_day_selec}千円/{$action_to_selec}千円/{$group_goal_lt_selec}千円

4.Ｙ☆Ｒａｎｋ獲得ポイント
【達成率】{$achieve_rate}%({$total_action_pt}pt/{$total_goal_pt}pt)

5.店舗評価ポイント（本日／累計／目標）
　{$group_action_point}pt／{$group_total_point}pt／{$group_goal_point}pt

6.Ｙ☆Mission
『通信サービスを通して人々の生活に喜びを』

7.Buzz☆Mission

";
}elseif($t_month < 202103){
//202101以降
//対外　スマホ⑤478 476
$action_day_smpho = $action_day[476] +  $action_day[478];
$action_to_smpho = $action_to[476] + $action_to[478];
$group_goal_lt_smpho = $group_goal_lt[476] + $group_goal_lt[478];
//タブ　レット⑥
$action_day_tab = $action_day[491] + $action_day[492];
$action_to_tab = $action_to[491] + $action_to[492];
$group_goal_lt_tab = $group_goal_lt[491] + $group_goal_lt[492];
//ＳＢ内新規
$action_day_newsb = $action_day[482] + $action_day[483] + $action_day[478] + $action_day[479] + $action_day[481] + $action_day[484] + $action_day[477] + $action_day[480];
$action_to_newsb = $action_to[482] + $action_to[483] + $action_to[478] + $action_to[479] + $action_to[481] + $action_to[484] + $action_to[477] + $action_to[480];
$group_goal_lt_newsb = $group_goal_lt[482] + $group_goal_lt[483] + $group_goal_lt[478] + $group_goal_lt[479] + $group_goal_lt[481] + $group_goal_lt[484] + $group_goal_lt[477] + $group_goal_lt[480];
//ＳＢ総　販
$action_day_allsb = $action_day_newsb + $action_day_tab + $action_day[485] + $action_day[486] + $action_day[487] + $action_day[488] + $action_day[489] + $action_day[490];
$action_to_allsb = $action_to_newsb + $action_to_tab + $action_to[485] + $action_to[486] + $action_to[487] + $action_to[488] + $action_to[489] + $action_to[490];
$group_goal_lt_allsb = $group_goal_lt_newsb + $group_goal_lt_tab + $group_goal_lt[485] + $group_goal_lt[486] + $group_goal_lt[487] + $group_goal_lt[488] + $group_goal_lt[489] + $group_goal_lt[490];
//ＹＭ内新規
$action_day_newym = $action_day[521] + $action_day[522] + $action_day[523] + $action_day[518] + $action_day[519] + $action_day[520];
$action_to_newym = $action_to[521] + $action_to[522] + $action_to[523] + $action_to[518] + $action_to[519] + $action_to[520];
$group_goal_lt_newym = $group_goal_lt[521] + $group_goal_lt[522] + $group_goal_lt[523] + $group_goal_lt[518] + $group_goal_lt[519] + $group_goal_lt[520];
//ＹＭ総　販
$action_day_allym = $action_day_newym + $action_day[524] + $action_day[525] + $action_day[526] + $action_day[527] + $action_day[528];
$action_to_allym = $action_to_newym + $action_to[524] + $action_to[525] + $action_to[526] + $action_to[527] + $action_to[528];
$group_goal_lt_allym = $group_goal_lt_newym + $group_goal_lt[524] + $group_goal_lt[525] + $group_goal_lt[526] + $group_goal_lt[527] + $group_goal_lt[528];
//ソフトバンク光493 597
$action_day_net = $action_day[493] + $action_day[597];
$action_to_net = $action_to[493] + $action_to[597];
$group_goal_lt_net = $group_goal_lt[493] + $group_goal_lt[597];
//おうちでんき⑧500
$action_day_ele = $action_day[500];
$action_to_ele = $action_to[500];
$group_goal_lt_ele = $group_goal_lt[500];
//セレクション販売
$action_day_selec = $action_day[507];
$action_to_selec = $action_to[507];
$group_goal_lt_selec = $group_goal_lt[507];
//店舗評価ポイント
$group_action_point = $today_action_pt[306] + $today_action_pt[477] + $today_action_pt[478] + $today_action_pt[479] + $today_action_pt[480] + $today_action_pt[481] + $today_action_pt[482] + $today_action_pt[483] + $today_action_pt[484] + $today_action_pt[485] + $today_action_pt[486] + $today_action_pt[487] + $today_action_pt[488] + $today_action_pt[489] + $today_action_pt[490] + $today_action_pt[491] + $today_action_pt[492] + $today_action_pt[493] + $today_action_pt[597] + $today_action_pt[494] + $today_action_pt[496] + $today_action_pt[501] + $today_action_pt[499] + $today_action_pt[500] + $today_action_pt[502] + $today_action_pt[504] + $today_action_pt[505] + $today_action_pt[611] + $today_action_pt[518] + $today_action_pt[519] + $today_action_pt[520] + $today_action_pt[521] + $today_action_pt[522] + $today_action_pt[523] + $today_action_pt[524] + $today_action_pt[525] + $today_action_pt[526] + $today_action_pt[527] + $today_action_pt[528];
$group_total_point = $total_act_ipt[306] + $total_act_ipt[477] + $total_act_ipt[478] + $total_act_ipt[479] + $total_act_ipt[480] + $total_act_ipt[481] + $total_act_ipt[482] + $total_act_ipt[483] + $total_act_ipt[484] + $total_act_ipt[485] + $total_act_ipt[486] + $total_act_ipt[487] + $total_act_ipt[488] + $total_act_ipt[489] + $total_act_ipt[490] + $total_act_ipt[491] + $total_act_ipt[492] + $total_act_ipt[493] + $total_act_ipt[597] + $total_act_ipt[494] + $total_act_ipt[496] + $total_act_ipt[501] + $total_act_ipt[499] + $total_act_ipt[500] + $total_act_ipt[502] + $total_act_ipt[504] + $total_act_ipt[505] + $total_act_ipt[611] + $total_act_ipt[518] + $total_act_ipt[519] + $total_act_ipt[520] + $total_act_ipt[521] + $total_act_ipt[522] + $total_act_ipt[523] + $total_act_ipt[524] + $total_act_ipt[525] + $total_act_ipt[526] + $total_act_ipt[527] + $total_act_ipt[528];
$group_goal_point = $total_goal_ipt[306] + $total_goal_ipt[477] + $total_goal_ipt[478] + $total_goal_ipt[479] + $total_goal_ipt[480] + $total_goal_ipt[481] + $total_goal_ipt[482] + $total_goal_ipt[483] + $total_goal_ipt[484] + $total_goal_ipt[485] + $total_goal_ipt[486] + $total_goal_ipt[487] + $total_goal_ipt[488] + $total_goal_ipt[489] + $total_goal_ipt[490] + $total_goal_ipt[491] + $total_goal_ipt[492] + $total_goal_ipt[493] + $total_goal_ipt[597] + $total_goal_ipt[494] + $total_goal_ipt[496] + $total_goal_ipt[501] + $total_goal_ipt[499] + $total_goal_ipt[500] + $total_goal_ipt[502] + $total_goal_ipt[504] + $total_goal_ipt[505] + $total_goal_ipt[611] + $total_goal_ipt[518] + $total_goal_ipt[519] + $total_goal_ipt[520] + $total_goal_ipt[521] + $total_goal_ipt[522] + $total_goal_ipt[523] + $total_goal_ipt[524] + $total_goal_ipt[525] + $total_goal_ipt[526] + $total_goal_ipt[527] + $total_goal_ipt[528];

if($total_goal_pt){
	$achieve_rate = sprintf("%01.1f",round($total_action_pt / $total_goal_pt * 100,1));
}else{
	$achieve_rate = 0;
}

$contents="$YEAR/$MONTH/$target_day
1.販売台数(本日/累計/目標)
【ＳＢ】
総　販: {$action_day_allsb}件/{$action_to_allsb}件/{$group_goal_lt_allsb}件
内新規: {$action_day_newsb}件/{$action_to_newsb}件/{$group_goal_lt_newsb}件

【ＹＭ】
総　販: {$action_day_allym}件/{$action_to_allym}件/{$group_goal_lt_allym}件
内新規: {$action_day_newym}件/{$action_to_newym}件/{$group_goal_lt_newym}件

2.重点項目(本日/累計/目標)
【 対外　スマホ 】{$action_day_smpho}件/{$action_to_smpho}件/{$group_goal_lt_smpho}件
【 タブ　レット 】{$action_day_tab}件/{$action_to_tab}件/{$group_goal_lt_tab}件
【ソフトバンク光】{$action_day_net}件/{$action_to_net}件/{$group_goal_lt_net}件
【 おうちでんき 】{$action_day_ele}件/{$action_to_ele}件/{$group_goal_lt_ele}件

3.セレクション販売（本日／累計／目標）
【販売粗利】{$action_day_selec}千円/{$action_to_selec}千円/{$group_goal_lt_selec}千円

4.Ｙ☆Ｒａｎｋ獲得ポイント
【達成率】{$achieve_rate}%({$total_action_pt}pt/{$total_goal_pt}pt)

5.店舗評価ポイント（本日／累計／目標）
　{$group_action_point}pt／{$group_total_point}pt／{$group_goal_point}pt

6.Ｙ☆Mission
『通信サービスを通して人々の生活に喜びを』

7.Buzz☆Mission

";
}elseif($t_month < 202105){
//202103以降
//対外　スマホ⑤478 476
$action_day_smpho = $action_day[476] +  $action_day[478];
$action_to_smpho = $action_to[476] + $action_to[478];
$group_goal_lt_smpho = $group_goal_lt[476] + $group_goal_lt[478];
//タブ　レット⑥
$action_day_tab = $action_day[491] + $action_day[492];
$action_to_tab = $action_to[491] + $action_to[492];
$group_goal_lt_tab = $group_goal_lt[491] + $group_goal_lt[492];
//ＳＢ内新規
$action_day_newsb = $action_day[482] + $action_day[483] + $action_day[478] + $action_day[479] + $action_day[481] + $action_day[484] + $action_day[477] + $action_day[480];
$action_to_newsb = $action_to[482] + $action_to[483] + $action_to[478] + $action_to[479] + $action_to[481] + $action_to[484] + $action_to[477] + $action_to[480];
$group_goal_lt_newsb = $group_goal_lt[482] + $group_goal_lt[483] + $group_goal_lt[478] + $group_goal_lt[479] + $group_goal_lt[481] + $group_goal_lt[484] + $group_goal_lt[477] + $group_goal_lt[480];
//ＳＢ総　販
$action_day_allsb = $action_day_newsb + $action_day_tab + $action_day[485] + $action_day[486] + $action_day[487] + $action_day[488] + $action_day[489] + $action_day[490];
$action_to_allsb = $action_to_newsb + $action_to_tab + $action_to[485] + $action_to[486] + $action_to[487] + $action_to[488] + $action_to[489] + $action_to[490];
$group_goal_lt_allsb = $group_goal_lt_newsb + $group_goal_lt_tab + $group_goal_lt[485] + $group_goal_lt[486] + $group_goal_lt[487] + $group_goal_lt[488] + $group_goal_lt[489] + $group_goal_lt[490];
//ＹＭ内新規
$action_day_newym = $action_day[521] + $action_day[522] + $action_day[523] + $action_day[518] + $action_day[519] + $action_day[520];
$action_to_newym = $action_to[521] + $action_to[522] + $action_to[523] + $action_to[518] + $action_to[519] + $action_to[520];
$group_goal_lt_newym = $group_goal_lt[521] + $group_goal_lt[522] + $group_goal_lt[523] + $group_goal_lt[518] + $group_goal_lt[519] + $group_goal_lt[520];
//ＹＭ総　販
$action_day_allym = $action_day_newym + $action_day[524] + $action_day[525] + $action_day[526] + $action_day[527] + $action_day[528];
$action_to_allym = $action_to_newym + $action_to[524] + $action_to[525] + $action_to[526] + $action_to[527] + $action_to[528];
$group_goal_lt_allym = $group_goal_lt_newym + $group_goal_lt[524] + $group_goal_lt[525] + $group_goal_lt[526] + $group_goal_lt[527] + $group_goal_lt[528];
//YM対外スマホ519 522
$action_day_ymsma = $action_day[519] + $action_day[522];
$action_to_ymsma = $action_to[519] + $action_to[522];
$group_goal_lt_ymsma = $group_goal_lt[519] + $group_goal_lt[522];
//SB機変スマホ486
$action_day_sbcsm = $action_day[486];
$action_to_sbcsm = $action_to[486];
$group_goal_lt_sbcsm = $group_goal_lt[486];
//Fromコンベ機変307
$action_day_fmkb = $action_day[307];
$action_to_fmkb = $action_to[307];
$group_goal_lt_fmkb = $group_goal_lt[307];
//ソフトバンク光493 597
$action_day_net = $action_day[493] + $action_day[597];
$action_to_net = $action_to[493] + $action_to[597];
$group_goal_lt_net = $group_goal_lt[493] + $group_goal_lt[597];
//おうちでんき⑧500
$action_day_ele = $action_day[500] + $action_day[607];
$action_to_ele = $action_to[500] + $action_to[607];
$group_goal_lt_ele = $group_goal_lt[500]+ $group_goal_lt[607];
//セレクション販売
$action_day_selec = $action_day[507];
$action_to_selec = $action_to[507];
$group_goal_lt_selec = $group_goal_lt[507];
//店舗評価ポイント
$group_action_point = $today_action_pt[306] + $today_action_pt[477] + $today_action_pt[478] + $today_action_pt[479] + $today_action_pt[480] + $today_action_pt[481] + $today_action_pt[482] + $today_action_pt[483] + $today_action_pt[484] + $today_action_pt[485] + $today_action_pt[486] + $today_action_pt[487] + $today_action_pt[488] + $today_action_pt[489] + $today_action_pt[490] + $today_action_pt[491] + $today_action_pt[492] + $today_action_pt[493] + $today_action_pt[597] + $today_action_pt[494] + $today_action_pt[496] + $today_action_pt[501] + $today_action_pt[499] + $today_action_pt[500] + $today_action_pt[502] + $today_action_pt[504] + $today_action_pt[505] + $today_action_pt[611] + $today_action_pt[518] + $today_action_pt[519] + $today_action_pt[520] + $today_action_pt[521] + $today_action_pt[522] + $today_action_pt[523] + $today_action_pt[524] + $today_action_pt[525] + $today_action_pt[526] + $today_action_pt[527] + $today_action_pt[528];
$group_total_point = $total_act_ipt[306] + $total_act_ipt[477] + $total_act_ipt[478] + $total_act_ipt[479] + $total_act_ipt[480] + $total_act_ipt[481] + $total_act_ipt[482] + $total_act_ipt[483] + $total_act_ipt[484] + $total_act_ipt[485] + $total_act_ipt[486] + $total_act_ipt[487] + $total_act_ipt[488] + $total_act_ipt[489] + $total_act_ipt[490] + $total_act_ipt[491] + $total_act_ipt[492] + $total_act_ipt[493] + $total_act_ipt[597] + $total_act_ipt[494] + $total_act_ipt[496] + $total_act_ipt[501] + $total_act_ipt[499] + $total_act_ipt[500] + $total_act_ipt[502] + $total_act_ipt[504] + $total_act_ipt[505] + $total_act_ipt[611] + $total_act_ipt[518] + $total_act_ipt[519] + $total_act_ipt[520] + $total_act_ipt[521] + $total_act_ipt[522] + $total_act_ipt[523] + $total_act_ipt[524] + $total_act_ipt[525] + $total_act_ipt[526] + $total_act_ipt[527] + $total_act_ipt[528];
$group_goal_point = $total_goal_ipt[306] + $total_goal_ipt[477] + $total_goal_ipt[478] + $total_goal_ipt[479] + $total_goal_ipt[480] + $total_goal_ipt[481] + $total_goal_ipt[482] + $total_goal_ipt[483] + $total_goal_ipt[484] + $total_goal_ipt[485] + $total_goal_ipt[486] + $total_goal_ipt[487] + $total_goal_ipt[488] + $total_goal_ipt[489] + $total_goal_ipt[490] + $total_goal_ipt[491] + $total_goal_ipt[492] + $total_goal_ipt[493] + $total_goal_ipt[597] + $total_goal_ipt[494] + $total_goal_ipt[496] + $total_goal_ipt[501] + $total_goal_ipt[499] + $total_goal_ipt[500] + $total_goal_ipt[502] + $total_goal_ipt[504] + $total_goal_ipt[505] + $total_goal_ipt[611] + $total_goal_ipt[518] + $total_goal_ipt[519] + $total_goal_ipt[520] + $total_goal_ipt[521] + $total_goal_ipt[522] + $total_goal_ipt[523] + $total_goal_ipt[524] + $total_goal_ipt[525] + $total_goal_ipt[526] + $total_goal_ipt[527] + $total_goal_ipt[528];

if($total_goal_pt){
	$achieve_rate = sprintf("%01.1f",round($total_action_pt / $total_goal_pt * 100,1));
}else{
	$achieve_rate = 0;
}

$contents="$YEAR/$MONTH/$target_day
1.販売台数(本日/累計/目標)
【ＳＢ】
総　販: {$action_day_allsb}件/{$action_to_allsb}件/{$group_goal_lt_allsb}件
内新規: {$action_day_newsb}件/{$action_to_newsb}件/{$group_goal_lt_newsb}件

【ＹＭ】
総　販: {$action_day_allym}件/{$action_to_allym}件/{$group_goal_lt_allym}件
内新規: {$action_day_newym}件/{$action_to_newym}件/{$group_goal_lt_newym}件

2.重点項目(本日/累計/目標)
【 SB対外スマホ 】{$action_day_smpho}件/{$action_to_smpho}件/{$group_goal_lt_smpho}件
【 YM対外スマホ 】{$action_day_ymsma}件/{$action_to_ymsma}件/{$group_goal_lt_ymsma}件
【 SB機変スマホ 】{$action_day_sbcsm}件/{$action_to_sbcsm}件/{$group_goal_lt_sbcsm}件
【Fromコンベ機変】{$action_day_fmkb}件/{$action_to_fmkb}件/{$group_goal_lt_fmkb}件
【ソフトバンク光】{$action_day_net}件/{$action_to_net}件/{$group_goal_lt_net}件
【 おうちでんき 】{$action_day_ele}件/{$action_to_ele}件/{$group_goal_lt_ele}件
【  タブレット  】{$action_day_tab}件/{$action_to_tab}件/{$group_goal_lt_tab}件

3.セレクション販売（本日／累計／目標）
【販売粗利】{$action_day_selec}千円/{$action_to_selec}千円/{$group_goal_lt_selec}千円

4.Ｙ☆Ｒａｎｋ獲得ポイント
【達成率】{$achieve_rate}%({$total_action_pt}pt/{$total_goal_pt}pt)

5.店舗評価ポイント（本日／累計／目標）
　{$group_action_point}pt／{$group_total_point}pt／{$group_goal_point}pt

6.Ｙ☆Mission
『通信サービスを通して人々の生活に喜びを』

7.Buzz☆Mission

";


}elseif($t_month < 202107){
//202105以降
//対外　スマホ⑤478 476
//タブ　レット⑥
$action_day_tab = $action_day[635] + $action_day[634];
$action_to_tab = $action_to[635] + $action_to[634];
$group_goal_lt_tab = $group_goal_lt[635] + $group_goal_lt[634];
//ＳＢ内新規
$action_day_newsb = $action_day[666] + $action_day[670];
$action_to_newsb = $action_to[666] + $action_to[670];
$group_goal_lt_newsb = $group_goal_lt[666] + $group_goal_lt[670];
//ＳＢ総　販
//ＹＭ内新規
$action_day_newym = $action_day[668] + $action_day[669];
$action_to_newym = $action_to[668] + $action_to[669];
$group_goal_lt_newym = $group_goal_lt[668] + $group_goal_lt[669];
//ＹＭ総　販
//YM対外スマホ519 522
//SB機変スマホ486
//Fromコンベ機変307
//ソフトバンク光596 609
$action_day_net = $action_day[596] + $action_day[609];
$action_to_net = $action_to[596] + $action_to[609];
$group_goal_lt_net = $group_goal_lt[596] + $group_goal_lt[609];
//おうちでんき⑧500
//セレクション販売
$action_day_selec = $action_day[644];
$action_to_selec = $action_to[644];
$group_goal_lt_selec = $group_goal_lt[644];
//店舗評価ポイント

if($total_goal_pt){
	$achieve_rate = sprintf("%01.1f",round($total_action_pt / $total_goal_pt * 100,1));
}else{
	$achieve_rate = 0;
}

$contents="$YEAR/$MONTH/$target_day
1.販売台数(本日/累計/目標)
【ＳＢ】
総　販: {$action_day[665]}件/{$action_to[665]}件/{$group_goal_lt[665]}件
内新規: {$action_day_newsb}件/{$action_to_newsb}件/{$group_goal_lt_newsb}件

【ＹＭ】
総　販: {$action_day[667]}件/{$action_to[667]}件/{$group_goal_lt[667]}件
内新規: {$action_day_newym}件/{$action_to_newym}件/{$group_goal_lt_newym}件

2.重点項目(本日/累計/目標)
【 SB対外スマホ 】{$action_day[306]}件/{$action_to[306]}件/{$group_goal_lt[306]}件
【 YM対外スマホ 】{$action_day[476]}件/{$action_to[476]}件/{$group_goal_lt[476]}件
【 SB機変スマホ 】{$action_day[620]}件/{$action_to[620]}件/{$group_goal_lt[620]}件
【Fromコンベ機変】{$action_day[307]}件/{$action_to[307]}件/{$group_goal_lt[307]}件
【ソフトバンク光】{$action_day_net}件/{$action_to_net}件/{$group_goal_lt_net}件
【 おうちでんき 】{$action_day[613]}件/{$action_to[613]}件/{$group_goal_lt[613]}件
【  タブレット  】{$action_day_tab}件/{$action_to_tab}件/{$group_goal_lt_tab}件

3.セレクション販売（本日／累計／目標）
【販売粗利】{$action_day_selec}千円/{$action_to_selec}千円/{$group_goal_lt_selec}千円

4.Ｙ☆Ｒａｎｋ獲得ポイント
【達成率】{$achieve_rate}%({$total_action_pt}pt/{$total_goal_pt}pt)

5.Ｙ☆Mission
『通信サービスを通して人々の生活に喜びを』

6.Buzz☆Mission

";


}elseif($t_month < 202108){
//202107以降
//ＳＢ総　販
$action_day_allsb = $action_day[306] + $action_day[620] + $action_day[476] + $action_day[609] + $action_day[596] + $action_day[613] + $action_day[635] + $action_day[701] + $action_day[702];
$action_to_allsb = $action_to[306] + $action_to[620] + $action_to[476] + $action_to[609] + $action_to[596] + $action_to[613] + $action_to[635] + $action_to[701] + $action_to[702];
$group_goal_lt_allsb = $group_goal_lt[306] + $group_goal_lt[620] + $group_goal_lt[476] + $group_goal_lt[609] + $group_goal_lt[596] + $group_goal_lt[613] + $group_goal_lt[635] + $group_goal_lt[701] + $group_goal_lt[702];
//ＳＢ内新規
$action_day_newsb = $action_day[306] + $action_day[620] + $action_day[476] + $action_day[609];
$action_to_newsb = $action_to[306] + $action_to[620] + $action_to[476] + $action_to[609];
$group_goal_lt_newsb = $group_goal_lt[306] + $group_goal_lt[620] + $group_goal_lt[476] + $group_goal_lt[609];
//ＹＭ総　販
$action_day_allym = $action_day[706] + $action_day[715] + $action_day[708] + $action_day[716] + $action_day[707] + $action_day[709] + $action_day[717];
$action_to_allym = $action_to[706] + $action_to[715] + $action_to[708] + $action_to[716] + $action_to[707] + $action_to[709] + $action_to[717];
$group_goal_lt_allym = $group_goal_lt[706] + $group_goal_lt[715] + $group_goal_lt[708] + $group_goal_lt[716] + $group_goal_lt[707] + $group_goal_lt[709] + $group_goal_lt[717];
//ＹＭ内新規
$action_day_newym = $action_day[706] + $action_day[715] + $action_day[708] + $action_day[716];
$action_to_newym = $action_to[706] + $action_to[715] + $action_to[708] + $action_to[716];
$group_goal_lt_newym = $group_goal_lt[706] + $group_goal_lt[715] + $group_goal_lt[708] + $group_goal_lt[716];
//SB対外スマホ
$action_day_sbsma = $action_day[722];
$action_to_sbsma = $action_to[722];
$group_goal_lt_sbsma = $group_goal_lt[306] + $group_goal_lt[476];
//YM対外スマホ
$action_day_ymsma = $action_day[706] + $action_day[708];
$action_to_ymsma = $action_to[706] + $action_to[708];
$group_goal_lt_ymsma = $group_goal_lt[706] + $group_goal_lt[708];
//SY対外スマホ
$action_day_sysma = $action_day[596] + $action_day[613] + $action_day[707] + $action_day[709];
$action_to_sysma = $action_to[596] + $action_to[613] + $action_to[707] + $action_to[709];
$group_goal_lt_sysma = $group_goal_lt[596] + $group_goal_lt[613] + $group_goal_lt[707] + $group_goal_lt[709];
//SB光SB Air
$action_day_sbhkr = $action_day[698] - $action_day[703];
$action_to_sbhkr = $action_to[698] - $action_to[703];
$group_goal_lt_sbhkr = $group_goal_lt[698] - $group_goal_lt[703];
//タブ　レット
$action_day_tab = $action_day[701] + $action_day[702];
$action_to_tab = $action_to[701] + $action_to[702];
$group_goal_lt_tab = $group_goal_lt[701] + $group_goal_lt[702];
//Fromコンベ機変
$action_day_from = $action_day[307] + $action_day[674];
$action_to_from = $action_to[307] + $action_to[674];
$group_goal_lt_from = $group_goal_lt[307] + $group_goal_lt[674];


//ソフトバンク光596 609
$action_day_net = $action_day[596] + $action_day[609];
$action_to_net = $action_to[596] + $action_to[609];
$group_goal_lt_net = $group_goal_lt[596] + $group_goal_lt[609];
//おうちでんき⑧500
//セレクション販売
$action_day_selec = $action_day[644];
$action_to_selec = $action_to[644];
$group_goal_lt_selec = $group_goal_lt[644];
//店舗評価ポイント
$group_action_point = $today_action_pt[306] + $today_action_pt[620] + $today_action_pt[476] + $today_action_pt[609] + $today_action_pt[722] + $today_action_pt[596] + $today_action_pt[307] + $today_action_pt[674] + $today_action_pt[613] + $today_action_pt[635] + $today_action_pt[706] + $today_action_pt[715] + $today_action_pt[708] + $today_action_pt[716] + $today_action_pt[707] + $today_action_pt[709] + $today_action_pt[717] + $today_action_pt[698] + $today_action_pt[703] + $today_action_pt[704] + $today_action_pt[702] + $today_action_pt[701] + $action_day[714];
$group_total_point = $total_act_ipt[306] + $total_act_ipt[620] + $total_act_ipt[476] + $total_act_ipt[609] + $total_act_ipt[722] + $total_act_ipt[596] + $total_act_ipt[307] + $total_act_ipt[674] + $total_act_ipt[613] + $total_act_ipt[635] + $total_act_ipt[706] + $total_act_ipt[715] + $total_act_ipt[708] + $total_act_ipt[716] + $total_act_ipt[707] + $total_act_ipt[709] + $total_act_ipt[717] + $total_act_ipt[698] + $total_act_ipt[703] + $total_act_ipt[704] + $total_act_ipt[702] + $total_act_ipt[701] + $action_to[714];
$group_goal_point = $group_goal_lt[714];

if($total_goal_pt){
	$achieve_rate = sprintf("%01.1f",round($total_action_pt / $total_goal_pt * 100,1));
}else{
	$achieve_rate = 0;
}

$contents="$YEAR/$MONTH/$target_day
1.販売台数(本日/累計/目標)
【ＳＢ】
総　販: {$action_day_allsb}件/{$action_to_allsb}件/{$group_goal_lt_allsb}件
内新規: {$action_day_newsb}件/{$action_to_newsb}件/{$group_goal_lt_newsb}件

【ＹＭ】
総　販: {$action_day_allym}件/{$action_to_allym}件/{$group_goal_lt_allym}件
内新規: {$action_day_newym}件/{$action_to_newym}件/{$group_goal_lt_newym}件

【来店組数】(本日/累計) {$action_day[710]}組/{$action_to[710]}組

2.重点項目(本日/累計/目標)
【 SB対外スマホ 】{$action_day_sbsma}件/{$action_to_sbsma}件/{$group_goal_lt_sbsma}件
【 YM対外スマホ 】{$action_day_ymsma}件/{$action_to_ymsma}件/{$group_goal_lt_ymsma}件
【 SY機変スマホ 】{$action_day_sysma}件/{$action_to_sysma}件/{$group_goal_lt_sysma}件
【 SB光・SB Air 】{$action_day_sbhkr}件/{$action_to_sbhkr}件/{$group_goal_lt_sbhkr}件
【 おうちでんき 】{$action_day[704]}件/{$action_to[704]}件/{$group_goal_lt[704]}件
【  タブレット  】{$action_day_tab}件/{$action_to_tab}件/{$group_goal_lt_tab}件
【Fromコンベ機変】{$action_day_from}件/{$action_to_from}件
【 Apple Watch  】{$action_day[700]}件/{$action_to[700]}件/{$group_goal_lt[700]}件

3.セレクション販売（本日／累計／目標）
【販売粗利】{$action_day_selec}千円/{$action_to_selec}千円/{$group_goal_lt_selec}千円

4.店舗評価ポイント（本日／累計／目標）
　{$group_action_point}pt／{$group_total_point}pt／{$group_goal_point}pt

5.Ｙ☆Mission
『通信サービスを通して人々の生活に喜びを』

6.Buzz☆Mission

";


}elseif($t_month < 202109){
//202108以降
//ＳＢ総　販
$action_day_allsb = $action_day[306] + $action_day[620] + $action_day[476] + $action_day[609] + $action_day[596] + $action_day[613] + $action_day[635] + $action_day[701] + $action_day[702];
$action_to_allsb = $action_to[306] + $action_to[620] + $action_to[476] + $action_to[609] + $action_to[596] + $action_to[613] + $action_to[635] + $action_to[701] + $action_to[702];
$group_goal_lt_allsb = $group_goal_lt[306] + $group_goal_lt[620] + $group_goal_lt[476] + $group_goal_lt[609] + $group_goal_lt[596] + $group_goal_lt[613] + $group_goal_lt[635] + $group_goal_lt[701] + $group_goal_lt[702];
//ＳＢ内新規
$action_day_newsb = $action_day[306] + $action_day[620] + $action_day[476] + $action_day[609];
$action_to_newsb = $action_to[306] + $action_to[620] + $action_to[476] + $action_to[609];
$group_goal_lt_newsb = $group_goal_lt[306] + $group_goal_lt[620] + $group_goal_lt[476] + $group_goal_lt[609];
//ＹＭ総　販
$action_day_allym = $action_day[706] + $action_day[715] + $action_day[708] + $action_day[716] + $action_day[707] + $action_day[709] + $action_day[717];
$action_to_allym = $action_to[706] + $action_to[715] + $action_to[708] + $action_to[716] + $action_to[707] + $action_to[709] + $action_to[717];
$group_goal_lt_allym = $group_goal_lt[706] + $group_goal_lt[715] + $group_goal_lt[708] + $group_goal_lt[716] + $group_goal_lt[707] + $group_goal_lt[709] + $group_goal_lt[717];
//ＹＭ内新規
$action_day_newym = $action_day[706] + $action_day[715] + $action_day[708] + $action_day[716];
$action_to_newym = $action_to[706] + $action_to[715] + $action_to[708] + $action_to[716];
$group_goal_lt_newym = $group_goal_lt[706] + $group_goal_lt[715] + $group_goal_lt[708] + $group_goal_lt[716];
//SB対外スマホ
$action_day_sbsma = $action_day[722];
$action_to_sbsma = $action_to[722];
$group_goal_lt_sbsma = $group_goal_lt[306] + $group_goal_lt[476];
//YM対外スマホ
$action_day_ymsma = $action_day[706] + $action_day[708];
$action_to_ymsma = $action_to[706] + $action_to[708];
$group_goal_lt_ymsma = $group_goal_lt[706] + $group_goal_lt[708];
//SY対外スマホ
$action_day_sysma = $action_day[596] + $action_day[613] + $action_day[707] + $action_day[709];
$action_to_sysma = $action_to[596] + $action_to[613] + $action_to[707] + $action_to[709];
$group_goal_lt_sysma = $group_goal_lt[596] + $group_goal_lt[613] + $group_goal_lt[707] + $group_goal_lt[709];
//SB光SB Air
$action_day_sbhkr = $action_day[698] - $action_day[703];
$action_to_sbhkr = $action_to[698] - $action_to[703];
$group_goal_lt_sbhkr = $group_goal_lt[698] - $group_goal_lt[703];
//タブ　レット
$action_day_tab = $action_day[701] + $action_day[702];
$action_to_tab = $action_to[701] + $action_to[702];
$group_goal_lt_tab = $group_goal_lt[701] + $group_goal_lt[702];
//Fromコンベ機変
$action_day_from = $action_day[307] + $action_day[674];
$action_to_from = $action_to[307] + $action_to[674];
$group_goal_lt_from = $group_goal_lt[307] + $group_goal_lt[674];


//ソフトバンク光596 609
$action_day_net = $action_day[596] + $action_day[609];
$action_to_net = $action_to[596] + $action_to[609];
$group_goal_lt_net = $group_goal_lt[596] + $group_goal_lt[609];
//おうちでんき⑧500
//セレクション販売
$action_day_selec = $action_day[644];
$action_to_selec = $action_to[644];
$group_goal_lt_selec = $group_goal_lt[644];
//店舗評価ポイント
$group_action_point = $today_action_pt[306] + $today_action_pt[620] + $today_action_pt[476] + $today_action_pt[609] + $today_action_pt[722] + $today_action_pt[596] + $today_action_pt[307] + $today_action_pt[674] + $today_action_pt[613] + $today_action_pt[635] + $today_action_pt[706] + $today_action_pt[715] + $today_action_pt[708] + $today_action_pt[716] + $today_action_pt[707] + $today_action_pt[709] + $today_action_pt[717] + $today_action_pt[698] + $today_action_pt[703] + $today_action_pt[704] + $today_action_pt[702] + $today_action_pt[701] + $action_day[714];
$group_total_point = $total_act_ipt[306] + $total_act_ipt[620] + $total_act_ipt[476] + $total_act_ipt[609] + $total_act_ipt[722] + $total_act_ipt[596] + $total_act_ipt[307] + $total_act_ipt[674] + $total_act_ipt[613] + $total_act_ipt[635] + $total_act_ipt[706] + $total_act_ipt[715] + $total_act_ipt[708] + $total_act_ipt[716] + $total_act_ipt[707] + $total_act_ipt[709] + $total_act_ipt[717] + $total_act_ipt[698] + $total_act_ipt[703] + $total_act_ipt[704] + $total_act_ipt[702] + $total_act_ipt[701] + $action_to[714];
$group_goal_point = $group_goal_lt[714];

if($total_goal_pt){
	$achieve_rate = sprintf("%01.1f",round($total_action_pt / $total_goal_pt * 100,1));
}else{
	$achieve_rate = 0;
}

$contents="$YEAR/$MONTH/$target_day
1.販売台数(本日/累計/目標)
【ＳＢ】
総　販: {$action_day_allsb}件/{$action_to_allsb}件/{$group_goal_lt_allsb}件
内新規: {$action_day_newsb}件/{$action_to_newsb}件/{$group_goal_lt_newsb}件

【ＹＭ】
総　販: {$action_day_allym}件/{$action_to_allym}件/{$group_goal_lt_allym}件
内新規: {$action_day_newym}件/{$action_to_newym}件/{$group_goal_lt_newym}件

【来店組数】(本日/累計) {$action_day[710]}組/{$action_to[710]}組

2.重点項目(本日/累計/目標)
【 SB対外スマホ 】{$action_day_sbsma}件/{$action_to_sbsma}件/{$group_goal_lt_sbsma}件
【 YM対外スマホ 】{$action_day_ymsma}件/{$action_to_ymsma}件/{$group_goal_lt_ymsma}件
【 SY機変スマホ 】{$action_day_sysma}件/{$action_to_sysma}件/{$group_goal_lt_sysma}件
【 SB光・SB Air 】{$action_day_sbhkr}件/{$action_to_sbhkr}件/{$group_goal_lt_sbhkr}件
【 おうちでんき 】{$action_day[704]}件/{$action_to[704]}件/{$group_goal_lt[704]}件
【  タブレット  】{$action_day_tab}件/{$action_to_tab}件/{$group_goal_lt_tab}件
【Fromコンベ機変】{$action_day_from}件/{$action_to_from}件
【 Apple Watch  】{$action_day[700]}件/{$action_to[700]}件/{$group_goal_lt[700]}件

3.セレクション販売（本日／累計／目標）
【販売粗利】{$action_day_selec}千円/{$action_to_selec}千円/{$group_goal_lt_selec}千円

4.店舗評価ポイント（本日／累計／目標）
　{$group_action_point}pt／{$group_total_point}pt／{$group_goal_point}pt

5.本日の店舗状況

";


}elseif($t_month < 202204){
//202109以降
//ＳＢ総　販
$action_day_allsb = $action_day[722] + $action_day[306] + $action_day[620] + $action_day[733] + $action_day[476] + $action_day[609] + $action_day[596] + $action_day[307] + $action_day[674] + $action_day[613] + $action_day[635];
$action_to_allsb = $action_to[722] + $action_to[306] + $action_to[620] + $action_to[733] + $action_to[476] + $action_to[609] + $action_to[596] + $action_to[307] + $action_to[674] + $action_to[613] + $action_to[635];
$group_goal_lt_allsb = $group_goal_lt[722] + $group_goal_lt[306] + $group_goal_lt[620] + $group_goal_lt[733] + $group_goal_lt[476] + $group_goal_lt[609] + $group_goal_lt[596] + $group_goal_lt[307] + $group_goal_lt[674] + $group_goal_lt[613] + $group_goal_lt[635];
//ＳＢ内新規
$action_day_newsb = $action_day[722] + $action_day[306] + $action_day[620] + $action_day[733] + $action_day[476] + $action_day[609];
$action_to_newsb = $action_to[722] + $action_to[306] + $action_to[620] + $action_to[733] + $action_to[476] + $action_to[609];
$group_goal_lt_newsb = $group_goal_lt[722] + $group_goal_lt[306] + $group_goal_lt[620] + $group_goal_lt[733] + $group_goal_lt[476] + $group_goal_lt[609];
//ＹＭ総　販
$action_day_allym = $action_day[706] + $action_day[715] + $action_day[708] + $action_day[716] + $action_day[707] + $action_day[709] + $action_day[717];
$action_to_allym = $action_to[706] + $action_to[715] + $action_to[708] + $action_to[716] + $action_to[707] + $action_to[709] + $action_to[717];
$group_goal_lt_allym = $group_goal_lt[706] + $group_goal_lt[715] + $group_goal_lt[708] + $group_goal_lt[716] + $group_goal_lt[707] + $group_goal_lt[709] + $group_goal_lt[717];
//ＹＭ内新規
$action_day_newym = $action_day[706] + $action_day[715] + $action_day[708] + $action_day[716];
$action_to_newym = $action_to[706] + $action_to[715] + $action_to[708] + $action_to[716];
$group_goal_lt_newym = $group_goal_lt[706] + $group_goal_lt[715] + $group_goal_lt[708] + $group_goal_lt[716];
//SB対外スマホ
$action_day_sbsma = $action_day[722] + $action_day[733];
$action_to_sbsma = $action_to[722] + $action_to[733];
$group_goal_lt_sbsma = $group_goal_lt[722] + $group_goal_lt[733];
//YM対外スマホ
$action_day_ymsma = $action_day[706] + $action_day[708];
$action_to_ymsma = $action_to[706] + $action_to[708];
$group_goal_lt_ymsma = $group_goal_lt[706] + $group_goal_lt[708];
//SY対外スマホ
$action_day_sysma = $action_day[596] + $action_day[307] + $action_day[613] + $action_day[707] + $action_day[709];
$action_to_sysma = $action_to[596] + $action_to[307] + $action_to[613] + $action_to[707] + $action_to[709];
$group_goal_lt_sysma = $group_goal_lt[596] + $group_goal_lt[307] + $group_goal_lt[613] + $group_goal_lt[707] + $group_goal_lt[709];
//SB光SB Air
$action_day_sbhkr = $action_day[698] - $action_day[703];
$action_to_sbhkr = $action_to[698] - $action_to[703];
$group_goal_lt_sbhkr = $group_goal_lt[698] - $group_goal_lt[703];
//タブ　レット
$action_day_tab = $action_day[701] + $action_day[702];
$action_to_tab = $action_to[701] + $action_to[702];
$group_goal_lt_tab = $group_goal_lt[701] + $group_goal_lt[702];
//Fromコンベ機変
$action_day_from = $action_day[307] + $action_day[674];
$action_to_from = $action_to[307] + $action_to[674];
$group_goal_lt_from = $group_goal_lt[307] + $group_goal_lt[674];


//ソフトバンク光596 609
$action_day_net = $action_day[596] + $action_day[609];
$action_to_net = $action_to[596] + $action_to[609];
$group_goal_lt_net = $group_goal_lt[596] + $group_goal_lt[609];
//おうちでんき⑧500
//セレクション販売
$action_day_selec = $action_day[644];
$action_to_selec = $action_to[644];
$group_goal_lt_selec = $group_goal_lt[644];
//店舗評価ポイント
$group_action_point = $today_action_pt[306] + $today_action_pt[620] + $today_action_pt[476] + $today_action_pt[609] + $today_action_pt[722] + $today_action_pt[596] + $today_action_pt[307] + $today_action_pt[674] + $today_action_pt[613] + $today_action_pt[635] + $today_action_pt[706] + $today_action_pt[715] + $today_action_pt[708] + $today_action_pt[716] + $today_action_pt[707] + $today_action_pt[709] + $today_action_pt[717] + $today_action_pt[698] + $today_action_pt[703] + $today_action_pt[704] + $today_action_pt[702] + $today_action_pt[701] + $action_day[714];
$group_total_point = $total_act_ipt[306] + $total_act_ipt[620] + $total_act_ipt[476] + $total_act_ipt[609] + $total_act_ipt[722] + $total_act_ipt[596] + $total_act_ipt[307] + $total_act_ipt[674] + $total_act_ipt[613] + $total_act_ipt[635] + $total_act_ipt[706] + $total_act_ipt[715] + $total_act_ipt[708] + $total_act_ipt[716] + $total_act_ipt[707] + $total_act_ipt[709] + $total_act_ipt[717] + $total_act_ipt[698] + $total_act_ipt[703] + $total_act_ipt[704] + $total_act_ipt[702] + $total_act_ipt[701] + $action_to[714];
$group_goal_point = $group_goal_lt[714];

if($total_goal_pt){
	$achieve_rate = sprintf("%01.1f",round($total_action_pt / $total_goal_pt * 100,1));
}else{
	$achieve_rate = 0;
}

$contents="$YEAR/$MONTH/$target_day
1.販売台数(本日/累計/目標)
【ＳＢ】
総　販: {$action_day_allsb}件/{$action_to_allsb}件/{$group_goal_lt_allsb}件
内新規: {$action_day_newsb}件/{$action_to_newsb}件/{$group_goal_lt_newsb}件

【ＹＭ】
総　販: {$action_day_allym}件/{$action_to_allym}件/{$group_goal_lt_allym}件
内新規: {$action_day_newym}件/{$action_to_newym}件/{$group_goal_lt_newym}件

【来店組数】(本日/累計) {$action_day[710]}組/{$action_to[710]}組

2.重点項目(本日/累計/目標)
【 SB対外スマホ 】{$action_day_sbsma}件/{$action_to_sbsma}件/{$group_goal_lt_sbsma}件
【 YM対外スマホ 】{$action_day_ymsma}件/{$action_to_ymsma}件/{$group_goal_lt_ymsma}件
【 SY機変スマホ 】{$action_day_sysma}件/{$action_to_sysma}件/{$group_goal_lt_sysma}件
【 SB光・SB Air 】{$action_day_sbhkr}件/{$action_to_sbhkr}件/{$group_goal_lt_sbhkr}件
【 おうちでんき 】{$action_day[704]}件/{$action_to[704]}件/{$group_goal_lt[704]}件
【  タブレット  】{$action_day_tab}件/{$action_to_tab}件/{$group_goal_lt_tab}件
【Fromコンベ機変】{$action_day_from}件/{$action_to_from}件/{$group_goal_lt_from}件
【 Apple Watch  】{$action_day[700]}件/{$action_to[700]}件/{$group_goal_lt[700]}件

3.セレクション販売（本日／累計／目標）
【販売粗利】{$action_day_selec}千円/{$action_to_selec}千円/{$group_goal_lt_selec}千円

4.店舗評価ポイント（本日／累計／目標）
　{$group_action_point}pt／{$group_total_point}pt／{$group_goal_point}pt

5.本日の店舗状況

";

}elseif($t_month < 202309){
//202204以降
//ＳＢ総　販
$action_day_allsb = $action_day[722] + $action_day[306] + $action_day[620] + $action_day[733] + $action_day[476] + $action_day[609] + $action_day[596] + $action_day[307] + $action_day[674] + $action_day[740] + $action_day[613] + $action_day[635];
$action_to_allsb = $action_to[722] + $action_to[306] + $action_to[620] + $action_to[733] + $action_to[476] + $action_to[609] + $action_to[596] + $action_to[307] + $action_to[674] + $action_to[740] + $action_to[613] + $action_to[635];
$group_goal_lt_allsb = $group_goal_lt[722] + $group_goal_lt[306] + $group_goal_lt[620] + $group_goal_lt[733] + $group_goal_lt[476] + $group_goal_lt[609] + $group_goal_lt[596] + $group_goal_lt[307] + $group_goal_lt[674] + $group_goal_lt[740] + $group_goal_lt[613] + $group_goal_lt[635];
//ＳＢ内新規
$action_day_newsb = $action_day[722] + $action_day[306] + $action_day[620] + $action_day[733] + $action_day[476] + $action_day[609];
$action_to_newsb = $action_to[722] + $action_to[306] + $action_to[620] + $action_to[733] + $action_to[476] + $action_to[609];
$group_goal_lt_newsb = $group_goal_lt[722] + $group_goal_lt[306] + $group_goal_lt[620] + $group_goal_lt[733] + $group_goal_lt[476] + $group_goal_lt[609];
//ＹＭ総　販
$action_day_allym = $action_day[706] + $action_day[715] + $action_day[708] + $action_day[716] + $action_day[707] + $action_day[709] + $action_day[717] + $action_day[753];
$action_to_allym = $action_to[706] + $action_to[715] + $action_to[708] + $action_to[716] + $action_to[707] + $action_to[709] + $action_to[717] + $action_to[753];
$group_goal_lt_allym = $group_goal_lt[706] + $group_goal_lt[715] + $group_goal_lt[708] + $group_goal_lt[716] + $group_goal_lt[707] + $group_goal_lt[709] + $group_goal_lt[717] + $group_goal_lt[753];
//ＹＭ内新規
$action_day_newym = $action_day[706] + $action_day[715] + $action_day[708] + $action_day[716];
$action_to_newym = $action_to[706] + $action_to[715] + $action_to[708] + $action_to[716];
$group_goal_lt_newym = $group_goal_lt[706] + $group_goal_lt[715] + $group_goal_lt[708] + $group_goal_lt[716];


//SB対外スマホ
$action_day_sbsma = $action_day[722] + $action_day[620] + $action_day[733];
$action_to_sbsma = $action_to[722] + $action_to[620] + $action_to[733];
$group_goal_lt_sbsma = $group_goal_lt[722] + $group_goal_lt[620] + $group_goal_lt[733];
//YM対外スマホ
$action_day_ymsma = $action_day[706] + $action_day[715] + $action_day[708];
$action_to_ymsma = $action_to[706] + $action_to[715] + $action_to[708];
$group_goal_lt_ymsma = $group_goal_lt[706] + $group_goal_lt[715] + $group_goal_lt[708];
//SY機変スマホ
$action_day_sysma = $action_day[596] + $action_day[613] + $action_day[707] + $action_day[709];
$action_to_sysma = $action_to[596] + $action_to[613] + $action_to[707] + $action_to[709];
$group_goal_lt_sysma = $group_goal_lt[596] + $group_goal_lt[613] + $group_goal_lt[707] + $group_goal_lt[709];
//SB光SB Air
$action_day_sbhkr = $action_day[698] - $action_day[703];
$action_to_sbhkr = $action_to[698] - $action_to[703];
$group_goal_lt_sbhkr = $group_goal_lt[698] - $group_goal_lt[703];
//おうちでんき⑧500
$action_day_dnk = $action_day[704] - $action_day[739];
$action_to_dnk = $action_to[704] - $action_to[739];
$group_goal_lt_dnk = $group_goal_lt[704] - $group_goal_lt[739];
//タブ　レット
$action_day_tab = $action_day[701] + $action_day[702];
$action_to_tab = $action_to[701] + $action_to[702];
$group_goal_lt_tab = $group_goal_lt[701] + $group_goal_lt[702];
//Fromコンベ機変
$action_day_from = $action_day[307] + $action_day[674];
$action_to_from = $action_to[307] + $action_to[674];
$group_goal_lt_from = $group_goal_lt[307] + $group_goal_lt[674];


//ソフトバンク光596 609
$action_day_net = $action_day[596] + $action_day[609];
$action_to_net = $action_to[596] + $action_to[609];
$group_goal_lt_net = $group_goal_lt[596] + $group_goal_lt[609];
//セレクション販売
$action_day_selec = $action_day[644];
$action_to_selec = $action_to[644];
$group_goal_lt_selec = $group_goal_lt[644];
//店舗評価ポイント
$group_action_point = $today_action_pt[306] + $today_action_pt[620] + $today_action_pt[476] + $today_action_pt[609] + $today_action_pt[722] + $today_action_pt[596] + $today_action_pt[307] + $today_action_pt[674] + $today_action_pt[613] + $today_action_pt[635] + $today_action_pt[706] + $today_action_pt[715] + $today_action_pt[708] + $today_action_pt[716] + $today_action_pt[707] + $today_action_pt[709] + $today_action_pt[717] + $today_action_pt[698] + $today_action_pt[703] + $today_action_pt[704] + $today_action_pt[702] + $today_action_pt[701] + $action_day[714];
$group_total_point = $total_act_ipt[306] + $total_act_ipt[620] + $total_act_ipt[476] + $total_act_ipt[609] + $total_act_ipt[722] + $total_act_ipt[596] + $total_act_ipt[307] + $total_act_ipt[674] + $total_act_ipt[613] + $total_act_ipt[635] + $total_act_ipt[706] + $total_act_ipt[715] + $total_act_ipt[708] + $total_act_ipt[716] + $total_act_ipt[707] + $total_act_ipt[709] + $total_act_ipt[717] + $total_act_ipt[698] + $total_act_ipt[703] + $total_act_ipt[704] + $total_act_ipt[702] + $total_act_ipt[701] + $action_to[714];
$group_goal_point = $group_goal_lt[714];

if($total_goal_pt){
	$achieve_rate = sprintf("%01.1f",round($total_action_pt / $total_goal_pt * 100,1));
}else{
	$achieve_rate = 0;
}

$contents="$YEAR/$MONTH/$target_day
1.販売台数(本日/累計/目標)
【ＳＢ】
総　販: {$action_day_allsb}件/{$action_to_allsb}件/{$group_goal_lt_allsb}件
内新規: {$action_day_newsb}件/{$action_to_newsb}件/{$group_goal_lt_newsb}件

【ＹＭ】
総　販: {$action_day_allym}件/{$action_to_allym}件/{$group_goal_lt_allym}件
内新規: {$action_day_newym}件/{$action_to_newym}件/{$group_goal_lt_newym}件

【来店組数】(本日/累計) {$action_day[710]}組/{$action_to[710]}組

2.重点項目(本日/累計/目標)
【 SB対外スマホ 】{$action_day_sbsma}件/{$action_to_sbsma}件/{$group_goal_lt_sbsma}件
【 YM対外スマホ 】{$action_day_ymsma}件/{$action_to_ymsma}件/{$group_goal_lt_ymsma}件
【 SY機変スマホ 】{$action_day_sysma}件/{$action_to_sysma}件/{$group_goal_lt_sysma}件
【 SB光・SB Air 】{$action_day_sbhkr}件/{$action_to_sbhkr}件/{$group_goal_lt_sbhkr}件
【 おうちでんき 】{$action_day_dnk}件/{$action_to_dnk}件/{$group_goal_lt_dnk}件
【  タブレット  】{$action_day_tab}件/{$action_to_tab}件/{$group_goal_lt_tab}件
【Fromコンベ機変】{$action_day_from}件/{$action_to_from}件/{$group_goal_lt_from}件
【 Apple Watch  】{$action_day[700]}件/{$action_to[700]}件/{$group_goal_lt[700]}件

3.セレクション販売（本日／累計／目標）
【販売粗利】{$action_day_selec}千円/{$action_to_selec}千円/{$group_goal_lt_selec}千円

4.店舗評価ポイント（本日／累計／目標）
　{$today_total_action_pt}pt／{$total_action_pt}pt／{$total_goal_pt}pt

";

}else{
//202309以降
//ＳＢ総　販
$action_day_allsb = $action_day[722] + $action_day[306] + $action_day[620] + $action_day[733] + $action_day[476] + $action_day[609] + $action_day[596] + $action_day[307] + $action_day[674] + $action_day[740] + $action_day[613] + $action_day[635];
$action_to_allsb = $action_to[722] + $action_to[306] + $action_to[620] + $action_to[733] + $action_to[476] + $action_to[609] + $action_to[596] + $action_to[307] + $action_to[674] + $action_to[740] + $action_to[613] + $action_to[635];
$group_goal_lt_allsb = $group_goal_lt[722] + $group_goal_lt[306] + $group_goal_lt[620] + $group_goal_lt[733] + $group_goal_lt[476] + $group_goal_lt[609] + $group_goal_lt[596] + $group_goal_lt[307] + $group_goal_lt[674] + $group_goal_lt[740] + $group_goal_lt[613] + $group_goal_lt[635];
//ＳＢ内新規
$action_day_newsb = $action_day[722] + $action_day[306] + $action_day[620] + $action_day[733] + $action_day[476] + $action_day[609];
$action_to_newsb = $action_to[722] + $action_to[306] + $action_to[620] + $action_to[733] + $action_to[476] + $action_to[609];
$group_goal_lt_newsb = $group_goal_lt[722] + $group_goal_lt[306] + $group_goal_lt[620] + $group_goal_lt[733] + $group_goal_lt[476] + $group_goal_lt[609];
//ＳＢ内アップグレード
$action_day_upgsb = $action_day[613] + $action_day[635];
$action_to_upgsb = $action_to[613] + $action_to[635];
$group_goal_lt_upgsb = $group_goal_lt[613] + $group_goal_lt[635];
//ＳＢ端末付き販売台数
$action_day_tsalesb = $action_day[722] + $action_day[306] + $action_day[733] + $action_day[476] + $action_day[596] + $action_day[307] + $action_day[674] + $action_day[740] + $action_day[613];
$action_to_tsalesb = $action_to[722] + $action_to[306] + $action_to[733] + $action_to[476] + $action_to[596] + $action_to[307] + $action_to[674] + $action_to[740] + $action_to[613];
$group_goal_lt_tsalesb = $group_goal_lt[722] + $group_goal_lt[306] + $group_goal_lt[733] + $group_goal_lt[476] + $group_goal_lt[596] + $group_goal_lt[307] + $group_goal_lt[674] + $group_goal_lt[740] + $group_goal_lt[613];

//ＹＭ総　販
$action_day_allym = $action_day[706] + $action_day[715] + $action_day[708] + $action_day[716] + $action_day[707] + $action_day[709] + $action_day[717] + $action_day[753];
$action_to_allym = $action_to[706] + $action_to[715] + $action_to[708] + $action_to[716] + $action_to[707] + $action_to[709] + $action_to[717] + $action_to[753];
$group_goal_lt_allym = $group_goal_lt[706] + $group_goal_lt[715] + $group_goal_lt[708] + $group_goal_lt[716] + $group_goal_lt[707] + $group_goal_lt[709] + $group_goal_lt[717] + $group_goal_lt[753];
//ＹＭ内新規
$action_day_newym = $action_day[706] + $action_day[715] + $action_day[708] + $action_day[716];
$action_to_newym = $action_to[706] + $action_to[715] + $action_to[708] + $action_to[716];
$group_goal_lt_newym = $group_goal_lt[706] + $group_goal_lt[715] + $group_goal_lt[708] + $group_goal_lt[716];
//ＹＭ端末付き販売台数
$action_day_tsaleym = $action_day[706] + $action_day[707] + $action_day[708] + $action_day[709];
$action_to_tsaleym = $action_to[706] + $action_to[707] + $action_to[708] + $action_to[709];
$group_goal_lt_tsaleym = $group_goal_lt[706] + $group_goal_lt[707] + $group_goal_lt[708] + $group_goal_lt[709];

//SB対外スマホ
$action_day_sbsma = $action_day[722] + $action_day[620] + $action_day[733];
$action_to_sbsma = $action_to[722] + $action_to[620] + $action_to[733];
$group_goal_lt_sbsma = $group_goal_lt[722] + $group_goal_lt[620] + $group_goal_lt[733];
//YM対外スマホ
$action_day_ymsma = $action_day[706] + $action_day[715] + $action_day[708];
$action_to_ymsma = $action_to[706] + $action_to[715] + $action_to[708];
$group_goal_lt_ymsma = $group_goal_lt[706] + $group_goal_lt[715] + $group_goal_lt[708];
//SY機変スマホ
$action_day_sysma = $action_day[596] + $action_day[307] + $action_day[613] + $action_day[707] + $action_day[709];
$action_to_sysma = $action_to[596] + $action_to[307] + $action_to[613] + $action_to[707] + $action_to[709];
$group_goal_lt_sysma = $group_goal_lt[596] + $group_goal_lt[307] + $group_goal_lt[613] + $group_goal_lt[707] + $group_goal_lt[709];
//SB光SB Air
$action_day_sbhkr = $action_day[698] + $action_day[927] + $action_day[928];
$action_to_sbhkr = $action_to[698] + $action_to[927] + $action_to[928];
$group_goal_lt_sbhkr = $group_goal_lt[698] + $group_goal_lt[927] + $group_goal_lt[928];
//おうちでんき⑧500
$action_day_dnk = $action_day[704] - $action_day[739];
$action_to_dnk = $action_to[704] - $action_to[739];
$group_goal_lt_dnk = $group_goal_lt[704] - $group_goal_lt[739];
//タブ　レット
$action_day_tab = $action_day[701] + $action_day[702];
$action_to_tab = $action_to[701] + $action_to[702];
$group_goal_lt_tab = $group_goal_lt[701] + $group_goal_lt[702];
//Fromコンベ機変
$action_day_from = $action_day[307] + $action_day[674];
$action_to_from = $action_to[307] + $action_to[674];
$group_goal_lt_from = $group_goal_lt[307] + $group_goal_lt[674];
// PayPayカード
$action_day_paypay = $action_day[751] + $action_day[888];
$action_to_paypay = $action_to[751] + $action_to[888];
$group_goal_lt_paypay = $group_goal_lt[751] + $group_goal_lt[888];

//ソフトバンク光596 609
$action_day_net = $action_day[596] + $action_day[609];
$action_to_net = $action_to[596] + $action_to[609];
$group_goal_lt_net = $group_goal_lt[596] + $group_goal_lt[609];
//セレクション販売
$action_day_selec = $action_day[644];
$action_to_selec = $action_to[644];
$group_goal_lt_selec = $group_goal_lt[644];
//店舗評価ポイント
$group_action_point = $today_action_pt[306] + $today_action_pt[620] + $today_action_pt[476] + $today_action_pt[609] + $today_action_pt[722] + $today_action_pt[596] + $today_action_pt[307] + $today_action_pt[674] + $today_action_pt[613] + $today_action_pt[635] + $today_action_pt[706] + $today_action_pt[715] + $today_action_pt[708] + $today_action_pt[716] + $today_action_pt[707] + $today_action_pt[709] + $today_action_pt[717] + $today_action_pt[698] + $today_action_pt[703] + $today_action_pt[704] + $today_action_pt[702] + $today_action_pt[701] + $action_day[714];
$group_total_point = $total_act_ipt[306] + $total_act_ipt[620] + $total_act_ipt[476] + $total_act_ipt[609] + $total_act_ipt[722] + $total_act_ipt[596] + $total_act_ipt[307] + $total_act_ipt[674] + $total_act_ipt[613] + $total_act_ipt[635] + $total_act_ipt[706] + $total_act_ipt[715] + $total_act_ipt[708] + $total_act_ipt[716] + $total_act_ipt[707] + $total_act_ipt[709] + $total_act_ipt[717] + $total_act_ipt[698] + $total_act_ipt[703] + $total_act_ipt[704] + $total_act_ipt[702] + $total_act_ipt[701] + $action_to[714];
$group_goal_point = $group_goal_lt[714];

if($total_goal_pt){
	$achieve_rate = sprintf("%01.1f",round($total_action_pt / $total_goal_pt * 100,1));
}else{
	$achieve_rate = 0;
}

$contents="$YEAR/$MONTH/$target_day
1.販売台数(本日/累計/目標)
【ＳＢ】
総　販: {$action_day_allsb}件/{$action_to_allsb}件/{$group_goal_lt_allsb}件
内新規: {$action_day_newsb}件/{$action_to_newsb}件/{$group_goal_lt_newsb}件
内アップグレード: {$action_day_upgsb}件/{$action_to_upgsb}件/{$group_goal_lt_upgsb}件
端末付き販売台数: {$action_day_tsalesb}台/{$action_to_tsalesb}台

【ＹＭ】
総　販: {$action_day_allym}件/{$action_to_allym}件/{$group_goal_lt_allym}件
内新規: {$action_day_newym}件/{$action_to_newym}件/{$group_goal_lt_newym}件
端末付き販売台数: {$action_day_tsaleym}台/{$action_to_tsaleym}台

2.重点項目(本日/累計/目標)
【 SB対外スマホ 】{$action_day_sbsma}件/{$action_to_sbsma}件/{$group_goal_lt_sbsma}件
【 YM対外スマホ 】{$action_day_ymsma}件/{$action_to_ymsma}件/{$group_goal_lt_ymsma}件
【 SY機変スマホ 】{$action_day_sysma}件/{$action_to_sysma}件/{$group_goal_lt_sysma}件
【 SB光・SB Air 】{$action_day_sbhkr}件/{$action_to_sbhkr}件/{$group_goal_lt_sbhkr}件
【SB光・SB Airキャンセル】{$action_day[703]}件
【  タブレット  】{$action_day_tab}件/{$action_to_tab}件/{$group_goal_lt_tab}件
【  Fromコンベ  】{$action_day_from}件/{$action_to_from}件/{$group_goal_lt_from}件
【 Apple Watch  】{$action_day[700]}件/{$action_to[700]}件/{$group_goal_lt[700]}件
【 PayPayカード 】{$action_day_paypay}件/{$action_to_paypay}件/{$group_goal_lt_paypay}件

3.セレクション販売（本日／累計／目標）
【同時割賦】{$action_day[696]}件/{$action_to[696]}件/{$group_goal_lt[696]}件
【物品販売】{$action_day[697]}個/{$action_to[697]}個/{$group_goal_lt[697]}個

4.店舗評価ポイント（本日／累計／目標）
　{$today_total_action_pt}pt／{$total_action_pt}pt／{$total_goal_pt}pt

";

}

}//SBend
	$max_rows = intval(strlen($contents)/30) + 1;
	$attack_cr = substr_count($contents,"\n") + 1;
	if($max_rows < $attack_cr){
		$max_rows = $attack_cr;
	}
if($max_rows < 10){
	$max_rows = 10;
}
/////////////////////////////////////////
$sql = "select comment_id,employee_id,comment,to_char(add_date,'YYYY/MM/DD HH24:MI') from telecom_comment where shop_id = $sec_id and status = '1' order by add_date desc";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$target_num){
	$target_num = 0;
}
if(($num - $target_num) > 0){
	list($q_comment_id,$q_employee_id,$q_comment,$q_add_date) = pg_fetch_array($result,$target_num);
}

$bf = $target_num - 1;
$nx = $target_num + 1;
if($bf < 0){
	$bf_disable=" disabled";
}else{
	$bf_disable="";
}
if($nx >= $num){
	$nx_disable=" disabled";
}else{
	$nx_disable="";
}


$ybase->title = "Y☆Rank-コメント管理";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("コメント管理");

$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('textarea').on('change keyup keydown paste cut', function(){
		var change_val = $(this).attr('rows');
		if ($(this).outerHeight() > this.scrollHeight){
			$(this).height(change_val);
		}
		while ($(this).outerHeight() < this.scrollHeight){
			$(this).height($(this).height() + 1);
		}
	});
	$('a[delhref]').click(function(){
		if(!confirm('本当に削除しますか？')){
			return false;
		}else{
			location.href = $(this).attr('delhref');
		}
	});
});

</script>

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./rank_top.php?$param" role="button">Y☆RankTOPに戻る</a></div>
<p></p>
<h5 style="text-align:center;">【{$ybase->section_list[$sec_id]} {$YEAR}年{$MONTH}月{$target_day}日】コメント管理</h5>
<p></p>
<div style="text-align:center;">
<a href="comment_reg.php?$param&target_day=$bf0" class="btn btn-sm btn-outline-secondary{$bf0_disable}">前日</a>
<a href="comment_reg.php?$param&target_day=$nx0" class="btn btn-sm btn-outline-secondary{$nx0_disable}">翌日</a>
</div>
<p></p>

HTML;
if($t_shop_id == 305){
$ybase->ST_PRI .= <<<HTML
<div style="text-align:center;">
HTML;
	foreach($rank_section_list as $key => $val){
		if($val != 305){continue;}
		if($key == $sec_id){
			$linkable = " disabled";
		}else{
			$linkable = "";
		}
$ybase->ST_PRI .= <<<HTML
<a href="comment_reg.php?t_month=$t_month&target_day=$target_day&t_shop_id=$t_shop_id&sec_id=$key" class="btn btn-sm btn-outline-info{$linkable}">{$ybase->section_list[$key]}</a>　
HTML;
	}
$ybase->ST_PRI .= <<<HTML
</div>
HTML;

}
$ybase->ST_PRI .= <<<HTML
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <tbody>
<tr><td align="center">
<form action="comment_ex.php" method="post">
<input type="hidden" name="t_shop_id" value="$t_shop_id">
<input type="hidden" name="t_month" value="$t_month">
<input type="hidden" name="sec_id" value="$sec_id">
【新規コメント投稿】<br>
<textarea name="comment" id="commnet_reg" rows="$max_rows" cols="45" required>
$contents
</textarea>
<br>
<input type="submit" value="投稿">
<input type="reset" value="クリア">
</form>
</td></tr>
</table>

<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
  </thead>
  <tbody>
<tr><td align="center">
<div>
【過去のコメント】<br><br>
<a href="comment_reg.php?$param&target_num=$nx" class="btn btn-sm btn-outline-secondary{$nx_disable}">前の投稿</a>
<a href="comment_reg.php?$param&target_num=$bf" class="btn btn-sm btn-outline-secondary{$bf_disable}">次の投稿</a>
<br><br>
HTML;

if($num){
if($q_employee_id == $ybase->my_employee_id){
	$addlink = " <a delhref=\"comment_del.php?t_comment_id=$q_comment_id&t_month=$t_month&target_day=$target_day&t_shop_id=$t_shop_id&sec_id=$sec_id\" class=\"btn btn-sm btn-secondary\">削除</a>\n";
}else{
	$addlink="";
}
$q_comment = stripcslashes($q_comment);
$ybase->ST_PRI .= <<<HTML
{$q_add_date}　{$sec_employee_list[$q_employee_id]}　投稿 <br>
<form action="comment_cg.php" method="post">
<input type="hidden" name="t_shop_id" value="$t_shop_id">
<input type="hidden" name="sec_id" value="$sec_id">
<input type="hidden" name="t_month" value="$t_month">
<input type="hidden" name="t_comment_id" value="$q_comment_id">
<input type="hidden" name="target_num" value="$target_num">
<textarea name="comment" id="commnet_reg" rows="10" cols="45" required>
$q_comment
</textarea>
<br>
<input type="submit" value="変更">
<input type="reset" value="クリア">$addlink
</form>
HTML;

}else{
$ybase->ST_PRI .= <<<HTML
過去のコメントはありません。
HTML;

}
$ybase->ST_PRI .= <<<HTML


</div>

<p></p>
<p></p>
</td></tr>
</tbody>
</table>
<p></p>

</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>