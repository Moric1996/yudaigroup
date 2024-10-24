<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/rank_list.inc');

$ybase = new ybase();
$ybase->session_get();
if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}
$param = "target_month=$target_month&target_shop_id=$target_shop_id";

$yy=substr($target_month,0,4);
$mm=substr($target_month,4,2);

/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$addsql = "month = $target_month and shop_id = $target_shop_id";
//////////////////////////////////////////項目数
$sql = "select count(item_id) from telecom2_item where {$addsql} and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("項目設定が終了していません。先に項目の設定をしてください。");
}
$item_count = pg_fetch_result($result,0,0);

//////////////////////////////////////////グループリスト
$sql = "select group_id,group_name,allot from telecom2_group where {$addsql} and status = '1' order by group_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
for($i=0;$i<$num;$i++){
	list($q_group_id,$q_group_name,$q_allot) = pg_fetch_array($result,$i);
	$group_name_lt[$q_group_id] = $q_group_name;
	$group_allot_lt[$q_group_id] = $q_allot;
}


$ybase->title = "Y☆Judge-個別配分設定TOP";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("個別配分設定TOP");

$ybase->ST_PRI .= <<<HTML


<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary" href="./set_top.php?target_month=$target_month&target_shop_id=$target_shop_id" role="button">設定TOPに戻る</a></div>
<h5 style="text-align:center;">【{$rank_section_name[$target_shop_id]} {$yy}年{$mm}月】 <br>個別配分設定TOP</h5>

<p></p>
HTML;

foreach($group_name_lt as $key => $val){
	$sql = "select group_const_id,employee_id from telecom2_group_const where {$addsql} and group_id = $key and status = '1' order by group_const_id";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	$finish_cnt = 0;
	for($i=0;$i<$num;$i++){
		list($q_group_const_id,$q_employee_id) = pg_fetch_array($result,$i);
		if(!$q_employee_id){
			$ybase->error("チーム設定が終了していません。先にチームの設定をしてください。");
		}
		$sql_p = "select item_id from telecom2_goal where {$addsql} and employee_id = $q_employee_id and status = '1' order by item_id";
		$result_p = $ybase->sql($conn,$sql_p);
		$num_p = pg_num_rows($result_p);
		if($num_p == $item_count){
			$finish_cnt++;
		}
	}
	if($num == $finish_cnt){
		$set_color = "alert alert-secondary";
		$set_notice = "設定済みです。変更ができます。";
	}else{
		$nofinish = $num - $finish_cnt;
		$set_color = "alert alert-success";
		$set_notice = "<span style=\"color:red;\">{$nofinish}人分 未設定です</span>";
	}

$ybase->ST_PRI .= <<<HTML
<div class="row">
<div class="col-sm"><a class="btn btn-{$set_color} btn-block" href="./set_personal_fm.php?{$param}&target_group_id=$key" role="button">$val</a></div>
<div class="col-sm-1">→</div>
<div class="col-sm">$set_notice</div>
</div>
<br>
HTML;
}


$ybase->ST_PRI .= <<<HTML


HTML;


$ybase->ST_PRI .= <<<HTML
<div style="text-align:center;"><a class="btn btn-danger" href="./set_daygoal_fm.php?target_month=$target_month&target_shop_id=$target_shop_id" role="button">日別目標設定へ</a></div>
<div style="text-align:right;"><a class="btn btn-secondary" href="./set_top.php?target_month=$target_month&target_shop_id=$target_shop_id" role="button">設定を完了して戻る</a></div>

</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>