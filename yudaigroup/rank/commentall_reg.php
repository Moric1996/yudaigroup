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
if(!preg_match("/^[0-9]+$/",$sec_id)){
	$sec_id = 0;
}
$rank_section_name['0'] = "Y☆Shop";
$rank_section_name['1'] = "CS☆Board";

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
$sql = "select item_id,employee_id,sum(action_num) from telecom_action where {$addsql2}{$addsql4} and day = $target_day group by item_id,employee_id order by item_id,employee_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_employee_id,$q_action_num) = pg_fetch_array($result3,$i);
	$action_day[$q_item_id] += $q_action_num;
	$today_action_pt[$q_item_id] += $q_action_num * $item_score[$q_item_id];
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
$contents="$YEAR/$MONTH/$target_day";

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

$ybase->title = $rank_section_name[$sec_id]."-コメント管理";

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
<h5 style="text-align:center;">【{$rank_section_name[$sec_id]} {$YEAR}年{$MONTH}月{$target_day}日】コメント管理</h5>
<p></p>
<div style="text-align:center;">
<a href="commentall_reg.php?$param&target_day=$bf0" class="btn btn-sm btn-outline-secondary{$bf0_disable}">前日</a>
<a href="commentall_reg.php?$param&target_day=$nx0" class="btn btn-sm btn-outline-secondary{$nx0_disable}">翌日</a>
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
<a href="commentall_reg.php?t_month=$t_month&target_day=$target_day&t_shop_id=$t_shop_id&sec_id=$key" class="btn btn-sm btn-outline-info{$linkable}">{$ybase->section_list[$key]}</a>　
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
<a href="commentall_reg.php?$param&target_num=$nx" class="btn btn-sm btn-outline-secondary{$nx_disable}">前の投稿</a>
<a href="commentall_reg.php?$param&target_num=$bf" class="btn btn-sm btn-outline-secondary{$bf_disable}">次の投稿</a>
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