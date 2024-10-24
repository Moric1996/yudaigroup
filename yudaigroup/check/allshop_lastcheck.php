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
$category_list = $check->category_make();
$item_list = $check->item_make();

$k=0;
foreach($ybase->section_list as $key => $val){
	if($key < 100){continue;}
	if($key > 1000){continue;}
	if(preg_match("/^3[0-9]{2}$/",$key)){continue;}
	$kk++;
	$sql = "select ckaction_id,ckset_id,employee_id,action_date,extract(day from current_timestamp - action_date) from ck_check_action where section_id = '$key' and status = '1' order by action_date desc";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
		list($q_ckaction_id,$q_ckset_id,$q_employee_id,$q_action_date,$q_pass_date) = pg_fetch_array($result,$i);
			$ckaction_id_arr[$key] = $q_ckaction_id;
			$employee_id_arr[$key] = $q_employee_id;
			$action_date_arr[$key] = $q_action_date;
			$pass_date_arr[$key] = $q_pass_date;
		$subject_list_arr = $check->check_set_make($key,$q_ckset_id);
		$subject_list_cate_arr = $check->check_set_item_make($subject_list_arr);
/*
		$sql2 = "select item_id,action from ck_check_action_list where section_id = '$key' and ckaction_id = $q_ckaction_id and status = '1' order by ckaction_list_id";
		$result2 = $ybase->sql($conn,$sql2);
		$num2 = pg_num_rows($result2);
		for($i=0;$i<$num2;$i++){
			list($q_item_id,$q_action) = pg_fetch_array($result2,$i);
			$item_action_val[$q_item_id] = $q_action;
		}
		$allsumval[$key] = 0;
		foreach($category_list as $key2 => $val2){
			$sumval[$key][$key2] = 0;
			$sumken = 0;
			foreach($subject_list_cate_arr[$key2] as $key3 => $val3){
				if(!isset($item_action_val[$key3])){
					$item_action_val[$key3] = 0;
				}
				$sumval[$key][$key2] += $item_action_val[$key3];
				$sumken++;
			}
			$allsumval[$key] += $sumval[$key][$key2];
		}
*/
	}else{
		foreach($category_list as $key4 => $val4){
			$subject_list_cate_arr[$key4]=array();
			$item_action_val[$key4] = 0;
		}
		$allsumval[$key] = 0;
	}
}
asort($pass_date_arr);

$ybase->title = "店舗チェック-入力状況確認";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("入力状況確認");
$ybase->ST_PRI .= $check->check_menu("3");

/////////////////////////////////////////////////
/////////////////////////////////////////////////////
$catecnt = count($category_list) + 1;

$ybase->ST_PRI .= <<<HTML

<div class="container">
<h5 style="text-align:center;">入力状況確認</h5>

<p></p>
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
<tr bgcolor="#cccccc" align="center">
<th rowspan="2">店舗名</th>
<th rowspan="2">実施日</th>
<th rowspan="2">実施者</th>
</tr>
  </thead>
  <tbody>
HTML;
/////////////////////////////////////////////

$kk=0;
$jun=0;
$preval = 0;
foreach($pass_date_arr as $key => $val){
$kk++;
if($kk%2 == 0){
	$hbgcolor = "#fafafa";
}else{
	$hbgcolor = "#ffffff";
}
if("$preval" != "$val"){
	$jun = $kk;
}
$preval = $val;

$d_employee_id = $employee_id_arr[$key];
if($pass_date_arr[$key] > 60){
	$pass_color = " style=\"color:#ff0000;\"";
}elseif($pass_date_arr[$key] > 30){
	$pass_color = " style=\"color:#ffd700;\"";
}elseif($pass_date_arr[$key] > 14){
	$pass_color = " style=\"color:#000080;\"";
}else{
	$pass_color = "";
}

$ybase->ST_PRI .= <<<HTML
<tr bgcolor="$hbgcolor" align="center">
<td align="center">
{$ybase->section_list[$key]}
</td>
<td align="center">
<span{$pass_color}>
{$action_date_arr[$key]}
</span>
</td>
<td align="center">
{$ybase->employee_name_list[$d_employee_id]}
</td>
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