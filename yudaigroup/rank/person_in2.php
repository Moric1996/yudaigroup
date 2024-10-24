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
$target_employee_id = trim($target_employee_id);
if(!$target_employee_id){
	$target_employee_id = $ybase->my_employee_id;
}
if(!preg_match("/^[0-9]+$/",$t_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20001");
}
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20002");
}
if(!preg_match("/^[0-9]+$/",$target_employee_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20003");
}
if($t_shop_id == 302 && $ybase->my_employee_id != 10005){
//	$ybase->error("現在メンテナンス中です。しばらくお待ちください。");
}



$rank->unitname_make($t_shop_id,$t_month);
$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;

$YEAR = substr($t_month,0,4);
$MONTH = intval(substr($t_month,4,2));
$maxday = date("t",mktime(0,0,0,$MONTH,$target_day,$YEAR));
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
$rank->group_const_make($t_shop_id,$t_month);
if(array_key_exists($target_employee_id,$rank->group_const_list)) {
	$indisabled = "";
}else{
	$indisabled = " disabled";
}
/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$param = "t_month=$t_month&t_shop_id=$t_shop_id";
$addsql = "month = $t_month and shop_id = $t_shop_id and status = '1'";
//////////////////////////////////////////
$sql = "select bigitem_id,count(*) from telecom_item where {$addsql} group by bigitem_id order by bigitem_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当月がまだ設定されていません");
}
for($i=0;$i<$num;$i++){
	list($q_bigitem_id,$q_count) = pg_fetch_array($result,$i);
	$bigitem_cnt[$q_bigitem_id] = $q_count;
}



$sql = "select item_id,action_num from telecom_action where {$addsql} and day = $target_day and employee_id = $target_employee_id order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_action_num) = pg_fetch_array($result3,$i);
	$actnum_arr[$q_item_id] = $q_action_num;
}

$sql = "select bigitem_id,item_id,item_name,score from telecom_item where {$addsql} and status = '1' order by order_num";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
$today_act_num = 0;
for($i=0;$i<$num3;$i++){
	list($q_bigitem_id,$q_item_id,$q_item_name,$q_score) = pg_fetch_array($result3,$i);
	$today_act_num += $actnum_arr[$q_item_id] * $q_score;
	$item_score[$q_item_id] = $q_score;
}
$all_goal_score = 0;
//個人目標
$sql = "select item_id,goal_num from telecom_goal where {$addsql} and employee_id = $target_employee_id order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_goal_num) = pg_fetch_array($result3,$i);
	$all_goal_score += $q_goal_num * $item_score[$q_itemid];
}

//当日実績個人
$all_total=0;
$sql = "select item_id,day,action_num from telecom_action where {$addsql} and employee_id = $target_employee_id and day <= $target_day order by item_id,day";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_day,$q_action_num) = pg_fetch_array($result3,$i);
	$all_total += $q_action_num * $item_score[$q_itemid];

}
if($all_goal_score){
	$reach_rate = round(($all_total / $all_goal_score) * 100,1);
}else{
	$reach_rate = 0.0;
}
$tolast_day_num = $all_total - $today_act_num;

$sql = "select bigitem_id,bigitem_name from telecom_bigitem where {$addsql} order by order_num";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当月がまだ設定されていません");
}

$ybase->title = "Y☆Rank-個人実績管理";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("個人実績管理");

if($t_month == 202005){
	$smtab = 154;
}else{
	$smtab = 153;
}


if(!$item_score[143]){
	$item_score[143] = 0;
}
if(!$item_score[$smtab]){
	$item_score[$smtab] = 0;
}
if(!$item_score[144]){
	$item_score[144] = 0;
}
$ybase->ST_PRI .= <<<HTML
<script type="text/javascript">
$(function(){
	$("#t_emp_id_select").change(function(){
		$("#Form1").submit();
	});
});
$(function(){
	$("#target_day_select").change(function(){
		$("#dayselectfrom").submit();
	});
});
$(function(){
	$("[id^=plus]").click(function(){
		var action_val143 = $('#action143').val();
		if(!action_val143){
			action_val143 = 0;
		}
		var action_val144 = $('#action144').val();
		if(!action_val144){
			action_val144 = 0;
		}
		var action_val{$smtab} = $('#action{$smtab}').val();
		if(!action_val{$smtab}){
			action_val{$smtab} = 0;
		}
		var action_val150 = $('#action150').val();
		if(!action_val150){
			action_val150 = 0;
		}
		var action_val151 = $('#action151').val();
		if(!action_val151){
			action_val151 = 0;
		}
		var itemid = $(this).attr('item_id');
		if((itemid == 143)||(itemid == 144)||(itemid == {$smtab})){

		}
		var my_score = $(this).attr('score');
		var now_val = $('#action' + itemid).val();
		if(!now_val){
			now_val = 0;
		}
		var nx_val = +now_val + 1;
		$('#action' + itemid).val(nx_val);
		$('#action' + itemid).ajaxchange_js();
		$('#dis_action' + itemid).text(nx_val);
		var todayn = $('#today_num').text();
		todayn = +todayn + +my_score;
		if((itemid == 145)||(itemid == 146)||(itemid == 147)||(itemid == 148)||(itemid == 149)||(itemid == 150)||(itemid == 152)||(itemid == 546)){
			var nx_val143 = +action_val143 + 1;
			action_val143 = nx_val143;
			$('#action143').val(nx_val143);
			$('#action143').ajaxchange_js();
			$('#dis_action143').text(nx_val143);
			var now_score{$smtab} = +action_val{$smtab} * {$item_score[$smtab]};
			var nx_val{$smtab} = +nx_val143 - action_val150 - action_val151;
			if(nx_val{$smtab} < 0){
				nx_val{$smtab} = 0;
			}
			var nex_score{$smtab} = +nx_val{$smtab} * {$item_score[$smtab]};
			action_val{$smtab} = nx_val{$smtab};
			$('#action{$smtab}').val(nx_val{$smtab});
			$('#action{$smtab}').ajaxchange_js();
			$('#dis_action{$smtab}').text(nx_val{$smtab});
			todayn = +todayn + +{$item_score[143]};
			todayn = +todayn - now_score{$smtab} + nex_score{$smtab};
		}
		if((itemid == 145)||(itemid == 146)||(itemid == 147)||(itemid == 546)){
			var nx_val144 = +action_val144 + 1;
			$('#action144').val(nx_val144);
			$('#action144').ajaxchange_js();
			$('#dis_action144').text(nx_val144);
			todayn = +todayn + +{$item_score[144]};
		}
		if((itemid == 151)||(itemid == 150)){
			var now_score{$smtab} = +action_val{$smtab} * {$item_score[$smtab]};
			var nexval{$smtab} = +action_val143 - action_val150 - action_val151 - 1;
			if(nexval{$smtab} < 0){
				nexval{$smtab} = 0;
			}
			var nex_score{$smtab} = +nexval{$smtab} * {$item_score[$smtab]};
			action_val{$smtab} = nexval{$smtab};
			todayn = +todayn - now_score{$smtab} + nex_score{$smtab};
			$('#action{$smtab}').val(nexval{$smtab});
			$('#action{$smtab}').ajaxchange_js();
			$('#dis_action{$smtab}').text(nexval{$smtab});
		}
		var totaln = {$tolast_day_num} + +todayn;
		if($all_goal_score > 0){
			var achivn = Math.round(totaln / {$all_goal_score} * 1000) / 10;
		}else{
			var achivn = 0;
		}
		$('#today_num').text(todayn);
		$('#total_num').text(totaln);
		$('#achieve_num').text(achivn);
	});
	$("[id^=minus]").click(function(){
		var action_val143 = $('#action143').val();
		if(!action_val143){
			action_val143 = 0;
		}
		var action_val144 = $('#action144').val();
		if(!action_val144){
			action_val144 = 0;
		}
		var action_val{$smtab} = $('#action{$smtab}').val();
		if(!action_val{$smtab}){
			action_val{$smtab} = 0;
		}
		var action_val150 = $('#action150').val();
		if(!action_val150){
			action_val150 = 0;
		}
		var action_val151 = $('#action151').val();
		if(!action_val151){
			action_val151 = 0;
		}
		var itemid = $(this).attr('item_id');
		if((itemid == 143)||(itemid == 144)||(itemid == {$smtab})){

		}
		var my_score = $(this).attr('score');
		var now_val = $('#action' + itemid).val();
		if(!now_val){
			now_val = 0;
		}
		var nx_val = +now_val - 1;
		if(nx_val < 0){
			return false;
		}
		$('#action' + itemid).val(nx_val);
		$('#action' + itemid).ajaxchange_js();
		$('#dis_action' + itemid).text(nx_val);
		var todayn = $('#today_num').text();
		if(now_val > 0){
			todayn = +todayn - my_score;
		}
		if((itemid == 145)||(itemid == 146)||(itemid == 147)||(itemid == 148)||(itemid == 149)||(itemid == 150)||(itemid == 152)||(itemid == 546)){
			var nx_val143 = +action_val143 - 1;
			if(nx_val143 < 0){
				nx_val143 = 0;
			}
			action_val143 = nx_val143;
			$('#action143').val(nx_val143);
			$('#action143').ajaxchange_js();
			$('#dis_action143').text(nx_val143);
			var now_score{$smtab} = +action_val{$smtab} * {$item_score[$smtab]};
			var nx_val{$smtab} = +nx_val143 - action_val150 - action_val151;
			if(nx_val{$smtab} < 0){
				nx_val{$smtab} = 0;
			}
			var nex_score{$smtab} = +nx_val{$smtab} * {$item_score[$smtab]};
			action_val{$smtab} = nx_val{$smtab};
			$('#action{$smtab}').val(nx_val{$smtab});
			$('#action{$smtab}').ajaxchange_js();
			$('#dis_action{$smtab}').text(nx_val{$smtab});
			todayn = +todayn - {$item_score[143]};
			todayn = +todayn - now_score{$smtab} + nex_score{$smtab};
		}
		if((itemid == 145)||(itemid == 146)||(itemid == 147)||(itemid == 546)){
			var nx_val144 = +action_val144 - 1;
			if(nx_val144 < 0){
				nx_val144 = 0;
			}
			$('#action144').val(nx_val144);
			$('#action144').ajaxchange_js();
			$('#dis_action144').text(nx_val144);
			todayn = +todayn - {$item_score[144]};
		}
		if((itemid == 151)||(itemid == 150)){
			var now_score{$smtab} = +action_val{$smtab} * {$item_score[$smtab]};
			var nexval{$smtab} = +action_val143 - action_val150 - action_val151 + 1;
			if(nexval{$smtab} < 0){
				nexval{$smtab} = 0;
			}
			var nex_score{$smtab} = +nexval{$smtab} * {$item_score[$smtab]};
			action_val{$smtab} = nexval{$smtab};
			todayn = +todayn - now_score{$smtab} + nex_score{$smtab};
			$('#action{$smtab}').val(nexval{$smtab});
			$('#action{$smtab}').ajaxchange_js();
			$('#dis_action{$smtab}').text(nexval{$smtab});
		}

		if(todayn < 0){
			todayn = 0;
		}
		var totaln = {$tolast_day_num} + +todayn;
		if($all_goal_score > 0){
			var achivn = Math.round(totaln / {$all_goal_score} * 1000) / 10;
		}else{
			var achivn = 0;
		}
		$('#today_num').text(todayn);
		$('#total_num').text(totaln);
		$('#achieve_num').text(achivn);
	});
});
</script>
<script src="./js/rank_update.js?102"></script>
<input type="hidden" name="target_month" value="$t_month" id="target_month">
<input type="hidden" name="target_shop_id" value="$t_shop_id" id="target_shop_id">
<input type="hidden" name="target_day" value="$target_day" id="target_day">
<input type="hidden" name="target_employee_id" value="$target_employee_id" id="target_employee_id">
<input type="hidden" name="scriptno" value="1" id="scriptno">

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./rank_top.php?$param" role="button">Y☆RankTOPに戻る</a></div>
<p></p>
<h5 style="text-align:center;">【{$rank_section_name[$t_shop_id]} {$YEAR}年{$MONTH}月】個人実績管理</h5>

<form action="./person_in2.php" method="post" id="Form1">
<input type="hidden" name="t_shop_id" value="$t_shop_id">
<input type="hidden" name="t_month" value="$t_month">
<input type="hidden" name="target_day" value="$target_day">
<div class="text-center">
HTML;
$check = $rank->check_position($ybase->my_position_class);
if($check){
$ybase->ST_PRI .= <<<HTML
<select name="target_employee_id" id="t_emp_id_select">
<option value="">選択してください</option>
HTML;
foreach($rank->group_const_list as $key => $val){
if($key == $target_employee_id){
	$selected = " selected";
}else{
	$selected = "";
}

$ybase->ST_PRI .= <<<HTML
<option value="$key"{$selected}>$val</option>
HTML;

}
$ybase->ST_PRI .= <<<HTML
</select>
HTML;
}else{
$ybase->ST_PRI .= <<<HTML
{$sec_employee_list[$target_employee_id]}
HTML;
}
$youbi = $rank->make_yobi($t_month,$target_day);
//$all_goal_score = number_format($all_goal_score);
//$all_total = number_format($all_total);
//$today_act_num = number_format($today_act_num);

$ybase->ST_PRI .= <<<HTML

 さん実績入力</div></form>

<p></p>
<table border="0" width="100%">
<tr><td>
<form action="person_in2.php" method="get" id="dayselectfrom">
<input type="hidden" name="t_month" value="$t_month">
<input type="hidden" name="t_shop_id" value="$t_shop_id">
<input type="hidden" name="target_employee_id" value="$target_employee_id">

<nobr><b>{$MONTH}月 
<select name="target_day" id="target_day_select">
HTML;
for($i=1;$i<=$maxday;$i++){
if($target_day == $i){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$i"$selected>$i</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML

</select>日({$youbi})</b></nobr>
</form>
</td>
<td>
<nobr>
<a href="person_in2.php?$param&target_day=$bf&target_employee_id=$target_employee_id" class="btn btn-sm btn-outline-secondary{$bf_disable}">前日</a>
<a href="person_in2.php?$param&target_day=$nx&target_employee_id=$target_employee_id" class="btn btn-sm btn-outline-secondary{$nx_disable}">翌日</a>
</nobr>
</td><td align="right">

<nobr>目標:<span style="color:red;font-size:120%;">{$all_goal_score}</span>点</nobr><br><nobr>　獲得:<span style="color:red;font-size:120%;" id="total_num">{$all_total}</span>点</nobr><br><nobr>　達成:<span style="color:red;font-size:120%;" id="achieve_num">{$reach_rate}</span>%</nobr><br>
<nobr>
本日の合計点数:<span id="today_num" style="color:#ff0000;font-size:150%;">{$today_act_num}</span>点
</nobr>
</td></tr>
</table>
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:75%;">
  <thead>
<tr align="center" bgcolor="#cccccc">
<th colspan="2" width="20%">
項目
</th>
<th>
配点
</th>
<th colspan="2">
実績入力
</th>
</tr>
  </thead>
  <tbody>
HTML;



$kk=0;
for($i=0;$i<$num;$i++){
	list($q_bigitem_id,$q_bigitem_name) = pg_fetch_array($result,$i);

$sql = "select item_id,item_name,score,noinput from telecom_item where {$addsql} and bigitem_id = $q_bigitem_id and status = '1' order by order_num";

$result2 = $ybase->sql($conn,$sql);
$num2 = pg_num_rows($result2);
if(!$num2){
	$ybase->error("該当月がまだ設定されていません");
}
for($ii=0;$ii<$num2;$ii++){
$kk++;
if($kk%2 == 0){
	$hbgcolor = "#fafafa";
}else{
	$hbgcolor = "#ffffff";
}
	list($q_item_id,$q_item_name,$q_score,$q_noinput) = pg_fetch_array($result2,$ii);
if($ii == 0){
	$charlist = mb_str_split($q_bigitem_name);
	$str="";
	foreach($charlist as $key => $val){
		if($key != 0){
			$str .= "<br>";
		}
		if($val == 'ー'){
			$val = "｜";
		}
			$val = mb_convert_kana($val,"KVA","UTF-8");
			$str .= $val;
	}
$ybase->ST_PRI .= <<<HTML
<tr align="center" bgcolor="$hbgcolor">
<td rowspan="{$bigitem_cnt[$q_bigitem_id]}" style="vertical-align: middle;width:30px;" bgcolor="{$bcolor[$i]}">
$str
</td>
HTML;
}else{
$ybase->ST_PRI .= <<<HTML
<tr align="center" bgcolor="$hbgcolor">
HTML;
}
$ken = $actnum_arr[$q_item_id];
if(!$ken){
	$ken = "";
}
$ybase->ST_PRI .= <<<HTML

<td align="center">
<nobr>$q_item_name</nobr>
</td>
<td>
$q_score
</td>
<td>
<input type="hidden" name="action$q_item_id" value="$ken" id="action$q_item_id" item_id="$q_item_id">
<nobr><b><span id="dis_action$q_item_id">$ken</span></b> {$rank->unitname_list[$q_item_id]}</nobr>
</td>
<td><nobr>
HTML;
if($q_noinput == "1"){
$ybase->ST_PRI .= <<<HTML
-
HTML;
}else{
$ybase->ST_PRI .= <<<HTML
<button type="button" class="btn btn-info rounded-circle p-0" style="width:2rem;height:2rem;" id="minus$q_item_id" item_id="$q_item_id" score="$q_score"{$indisabled}>－</button>　
<button type="button" class="btn btn-info rounded-circle p-0" style="width:2rem;height:2rem;" id="plus$q_item_id" item_id="$q_item_id" score="$q_score"{$indisabled}>＋</button>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</nobr>
</td>
</tr>
HTML;

}

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