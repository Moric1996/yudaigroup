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
$tablerate = $ybase->mbscale(6);

if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}

//$full_employee_list = $ybase->make_employee_list();
$sec_employee_list = $rank->make_rank_employee_list("1","","$target_shop_id");
mb_language("Ja");
mb_internal_encoding("utf-8");
$forjs_emp_list .= "";
$i=0;
foreach($sec_employee_list as $key => $val){
	$short_val = mb_substr($val,0,2);
	if($i > 0){
		$forjs_emp_list .= ",";
	}
	$forjs_emp_list .= "$key".":'"."$short_val"."'";
	$i++;
}
/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
$param = "target_month=$target_month&target_shop_id=$target_shop_id";
//////////////////////////////////////////条件
$addsql = "month = $target_month and shop_id = $target_shop_id";
$param = "target_month=$target_month&target_shop_id=$target_shop_id";
//////////////////////////////////////////
$yy=substr($target_month,0,4);
$mm=substr($target_month,4,2);

$sql = "select group_id,count(*) from telecom2_group_const where {$addsql} and status = '1' group by group_id order by group_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$last_month = date("Ym",mktime(0,0,0,$mm - 1,1,$yy));
	$lastyy=substr($last_month,0,4);
	$lastmm=substr($last_month,4,2);

	$sql = "update telecom2_group set status = '0' where {$addsql} and status = '1'";
	$result = $ybase->sql($conn,$sql);

	$sql = "insert into telecom2_group (group_id,group_name,shop_id,month,leader_employee_id,allot,add_date,status) select group_id,group_name,shop_id,$target_month,leader_employee_id,allot,'now',status from telecom2_group where month = $last_month and shop_id = $target_shop_id and status = '1' order by group_id";
	$result = $ybase->sql($conn,$sql);

	$sql = "insert into telecom2_group_const (group_const_id,shop_id,month,group_id,employee_id,add_date,status,short_name) select group_const_id,shop_id,$target_month,group_id,employee_id,'now',status,short_name from telecom2_group_const where month = $last_month and shop_id = $target_shop_id and status = '1' order by group_id,group_const_id";
	$result = $ybase->sql($conn,$sql);
	$notice = "新しい月の為、前月の情報を引継ぎ設定しました。変更がある場合は下記を変更してください。";

$sql = "select group_id,count(*) from telecom2_group_const where {$addsql} and status = '1' group by group_id order by group_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

}else{
	$notice = "変更がある場合は下記を変更してください。";
}
for($i=0;$i<$num;$i++){
	list($q_group_id,$q_count) = pg_fetch_array($result,$i);
	$group_cnt[$q_group_id] = $q_count;
}

$sql = "select group_id,group_name,leader_employee_id,allot from telecom2_group where {$addsql} and status = '1' order by group_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$sql = "insert into telecom2_group (group_id,group_name,shop_id,month,add_date,status) values (nextval('telecom2_group_id_seq'),'',$target_shop_id,$target_month,'now','1')";
	$result = $ybase->sql($conn,$sql);
	$sql = "select group_id,group_name,leader_employee_id,allot from telecom2_group where {$addsql} and status = '1' order by group_id";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	$group_new_flag = 1;
}else{
	$group_new_flag = 0;
}

$ybase->title = "Y☆Judge-チーム設定";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("チーム設定");

$group_id_list = "";
$goukei_allot = 0;
for($i=0;$i<$num;$i++){
	list($q_group_id,$q_group_name,$q_leader_employee_id,$q_allot) = pg_fetch_array($result,$i);
		if($i > 0){
		$group_id_list .= ",";
	}
	$group_id_list .= "'"."$q_group_id"."'";
	$goukei_allot += $q_allot;
}
$ybase->ST_PRI .= <<<HTML
<script src="./js/rank_update.js?$time"></script>
<input type="hidden" name="target_month" value="$target_month" id="target_month">
<input type="hidden" name="target_shop_id" value="$target_shop_id" id="target_shop_id">
<input type="hidden" name="scriptno" value="3" id="scriptno">
<script type="text/javascript">
$(function($) {
	var g_list = new Array($group_id_list);
	$("[id^=allot]").change(function() {
		var allot_num = 0;
		for (var i=0; i<$num; i++) {
			var g_id = g_list[i];
			var anum = $('#allot'+ g_id).val();
			allot_num += Number(anum);
		}
		$('#haibun_dis').text(allot_num);
	});
});
$(function($) {
	var emp_list = {{$forjs_emp_list}};
	$("[name^=employee_id]").change(function() {
		var const_id = $(this).attr('group_const_id');
		var emp_id = $(this).val();
		var s_name = emp_list[emp_id];
		$('#short_name' + const_id).val(s_name);
		$('#short_name' + const_id).ajaxchange_js();

	});
});
</script>
<script>
$(function($) {
	$("[id^=del_group_]").click(function(){
		var groupid = $(this).attr('group_id');
		var groupid_name = $(this).attr('group_id_name');
		if(!confirm('「' + groupid_name + '」を本当に削除しますか？\\nチームを削除すると配下の構成員も削除されます。')){
			return false;
		}else{
			location.href = 'set_team_del_group.php?$param&t_group_id=' + groupid;
	    }
	});
	$("[id^=del_emp_]").click(function(){
		var groupconstid = $(this).attr('group_const_id');
		if(!confirm('本当に削除しますか？')){
			return false;
		}else{
			location.href = 'set_team_del_emp.php?$param&t_group_const_id=' + groupconstid;
	    }
	});
});
</script>


<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary" href="./set_team_emp_check.php?$param&jump_script=set_top.php" role="button">設定TOPに戻る</a></div>
<h5 style="text-align:center;">【{$rank_section_name[$target_shop_id]} {$yy}年{$mm}月】 チーム設定</h5>

<p>$notice</p>


<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
<tr bgcolor="#eeeeee">
<th rowspan="2">チーム名 <a href="set_team_in_group.php?$param" class="btn btn-sm btn-outline-secondary">チーム追加</a></th>
<th rowspan="2">配分 <small>合計<span id="haibun_dis" style="color:#ff0000;">$goukei_allot</span>%</small></th>
<th rowspan="2">リーダー</th>
<th colspan="2">構成員</th>
</tr>
<tr bgcolor="#eeeeee">
<th>氏名</th>
<th>表示用名</th>
</tr>
  </thead>
  <tbody>
HTML;

for($i=0;$i<$num;$i++){
	list($q_group_id,$q_group_name,$q_leader_employee_id,$q_allot) = pg_fetch_array($result,$i);
	$q_leader_employee_id == trim($q_leader_employee_id);
$sql = "select group_const_id,employee_id,short_name from telecom2_group_const where {$addsql} and group_id = $q_group_id and status = '1' order by group_const_id";

$result2 = $ybase->sql($conn,$sql);
$num2 = pg_num_rows($result2);
if(!$num2){
	$sql = "insert into telecom2_group_const (group_const_id,shop_id,month,group_id,add_date,status) values (nextval('telecom2_group_const_id_seq'),$target_shop_id,$target_month,$q_group_id,'now','1')";
	$result = $ybase->sql($conn,$sql);
	$sql = "select group_const_id,employee_id,short_name from telecom2_group_const where {$addsql} and group_id = $q_group_id and status = '1' order by group_const_id";

	$result2 = $ybase->sql($conn,$sql);
	$num2 = pg_num_rows($result2);
	$groupconst_new_flag = 1;
}else{
	$groupconst_new_flag = 0;
}
for($ii=0;$ii<$num2;$ii++){
	list($q_group_const_id,$q_employee_id,$q_short_name) = pg_fetch_array($result2,$ii);
	$q_employee_id == trim($q_employee_id);
	$q_short_name == trim($q_short_name);

	if($q_leader_employee_id && ($q_leader_employee_id == $q_employee_id)){
		$checked = " checked";
	}else{
		$checked = "";
	}

if($ii == 0){
$rowspan = $group_cnt[$q_group_id] + $groupconst_new_flag + 1;
$nn = $i + 1;
$ybase->ST_PRI .= <<<HTML
<tr>
<td rowspan="$rowspan" style="vertical-align: middle;" bgcolor="{$group_bgcolor[$nn]}">
<input type="text" name="group_name$q_group_id" value="$q_group_name" id="group_name$q_group_id">
<a href="#" id="del_group_{$q_group_id}" group_id="$q_group_id" group_id_name="$q_group_name">削除</a>

</td>
<td rowspan="$rowspan" style="vertical-align: middle;" bgcolor="{$group_bgcolor[$nn]}">
<nobr><input type="{$ybase->NUM_INPUT_TYPE}" name="allot$q_group_id" value="$q_allot" id="allot$q_group_id" style="width:3.2em;" target_group_id="$q_group_id">%</nobr>
</td>
HTML;
}else{
$ybase->ST_PRI .= <<<HTML
<tr>
HTML;
}
$ybase->ST_PRI .= <<<HTML
<td align="left">
<input type="radio" name="leader_employee_id$q_group_id" value="$q_group_const_id" id="leader_employee_id$q_group_id{$ii}"{$checked} >
</td>
<td align="left">
<select name="employee_id$q_group_const_id" target_group_id="$q_group_id" radioid="leader_employee_id$q_group_id{$ii}" group_const_id="$q_group_const_id">
<option value="">選択してください</option>
HTML;
foreach($sec_employee_list as $key => $val){
	if($q_employee_id == $key){
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
<a href="#" id="del_emp_{$q_group_const_id}" group_const_id="$q_group_const_id">削除</a>
</td>
<td>
<input type="text" name="short_name$q_group_const_id" value="$q_short_name" id="short_name$q_group_const_id" group_const_id="$q_group_const_id" size="5">
</td>
</tr>
HTML;
if($ii == ($num2 - 1)){
$ybase->ST_PRI .= <<<HTML
<tr>
<td></td>
<td colspan="2" align="left">
<a href="set_team_in_emp.php?$param&t_group_id=$q_group_id" class="btn btn-sm btn-outline-secondary">構成員追加</a>
</td>
</tr>
HTML;
}
}

}

$ybase->ST_PRI .= <<<HTML

 </tbody>
</table>
</div>
<div style="text-align:center;"><a class="btn btn-danger" href="./set_team_emp_check.php?$param&jump_script=set_teamallot_fm.php" role="button">月間目標・チーム配分設定へ</a></div>
<div style="text-align:right;"><a class="btn btn-secondary" href="./set_team_emp_check.php?$param&jump_script=set_top.php" role="button">設定を完了して戻る</a></div>
</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>