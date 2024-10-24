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
if($rank_section_list[$ybase->my_section_id]){
	$my_rank_section = $rank_section_list[$ybase->my_section_id];
}else{
	$my_rank_section = 302;
}
if($t_shop_id == ""){
	$t_shop_id = $my_rank_section;
}
if($t_shop_id == $my_rank_section){
	$auth_edit = 1;
}else{
	$auth_edit = 0;
}
if($t_month){
	$tar_month = substr($t_month,0,4)."-".substr($t_month,4,2);
}
$nowyy =date("Y");
$nowmm =date("m");
$nowdd =date("d");
if(!$tar_month){
	$t_month = date("Ym",mktime(0,0,0,$nowmm,$nowdd,$nowyy));
	$tar_month = date("Y-m",mktime(0,0,0,$nowmm,$nowdd,$nowyy));
	$yy = substr($tar_month,0,4);
	$mm = substr($tar_month,5,2);
}else{
	$yy = substr($tar_month,0,4);
	$mm = substr($tar_month,5,2);
	$t_month = date("Ym",mktime(0,0,0,$mm,1,$yy));
}

if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}
$param = "t_month=$t_month&t_shop_id=$t_shop_id";
$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;

/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$addsql = "month = $t_month and shop_id = $t_shop_id";
//////////////////////////////////////////確認
$this_month = date("Ym",mktime(0,0,0,$nowmm,$nowdd,$nowyy));
$leader_check_this = $rank->check_group_set($t_shop_id,$this_month,$ybase->my_employee_id);
$last_month = date("Ym",mktime(0,0,0,$nowmm - 1,$nowdd,$nowyy));
$leader_check_last = $rank->check_group_set($t_shop_id,$last_month,$ybase->my_employee_id);
$next_month = date("Ym",mktime(0,0,0,$nowmm + 1,$nowdd,$nowyy));
$leader_check_next = $rank->check_group_set($t_shop_id,$next_month,$ybase->my_employee_id);

//////////////////////////////////////////
foreach($rank_section_list as $key => $val){
	if($t_shop_id && ($val != $t_shop_id)){
		continue;
	}
	if(($comm_id == $key) && $comm_date){
		$sql = "select comment_id from telecom_comment where shop_id = $key and status = '1' and to_char(add_date,'YYYY-MM-DD') <= '$comm_date' order by comment_id desc";
		$result = $ybase->sql($conn,$sql);
		$num = pg_num_rows($result);
			if(!$num){
				$comm_no[$key] = 0;
			}else{
				list($target_comment_id) = pg_fetch_array($result,0);
				$sql = "select comment_id from telecom_comment where shop_id = $key and status = '1' order by comment_id desc";
				$result = $ybase->sql($conn,$sql);
				$num = pg_num_rows($result);
				if(!$num){
					$comm_no[$key] = 0;
				}else{
					for($i=0;$i<$num;$i++){
						list($chk_comment_id) = pg_fetch_array($result,$i);
						if($chk_comment_id == $target_comment_id){
							$comm_no[$key] = $i;
						}
					}
				}
			}

	}

$sql = "select comment_id,employee_id,comment,to_char(add_date,'YYYY/MM/DD HH24:MI') from telecom_comment where shop_id = $key and status = '1' order by comment_id desc";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$comm_no[$key]){
	$comm_no[$key] = 0;
}
if($comm_no[$key] < $num){
	list($q_comment_id[$key],$q_com_employee_id[$key],$q_comment[$key],$q_com_date[$key]) = pg_fetch_array($result,$comm_no[$key]);
	$order = array("\r\n","\n","\r");
	$replace = '<br>';
	$q_comment[$key] = str_replace($order, $replace, $q_comment[$key]);
}
if($num == 0){
	$q_comment[$key] = "コメントはありません";
}
$bf[$key] = $comm_no[$key] + 1;
$nx[$key] = $comm_no[$key] - 1;
if($bf[$key] >= $num){
	$bf_disable[$key] = " disabled";
}else{
	$bf_disable[$key] = "";
}
if($nx[$key] < 0){
	$nx_disable[$key] = " disabled";
}else{
	$nx_disable[$key] = "";
}
$cvername = "lastcommid"."$key";

if($comm_no[$key] == 0){
	$check_cook = $_COOKIE["$cvername"];
	if($check_cook == $q_comment_id[$key]){
		$bodydisplay[$key] = " style=\"display:none;\"";
		$slidebutton[$key] = "▼";
	}else{
		$bodydisplay[$key] = "";
		$slidebutton[$key] = "▲";
	setcookie("$cvername","{$q_comment_id[$key]}");
	}
}else{
	$bodydisplay[$key] = "";
	$slidebutton[$key] = "▲";
}
}

$ybase->title = "Y☆Rank-TOP";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("Y☆Rank-TOP");

$ybase->ST_PRI .= <<<HTML
<link href="./css/rank_top.css" rel="stylesheet">
<script type="text/javascript">
$(function(){
	$('.date_cal').change(function(){
	var shop_id = $(this).attr("shop_id");
		$("#form_com_" + shop_id).submit();
	});
});
$(function(){
	$('input[type="month"],select').change(function(){
		$("#Form1").submit();
	});
});
$(function(){
	$("[id^=slidebutton]").click(function(){
	var shop_id = $(this).attr("shopid");
	if($("#commentbody" + shop_id).css('display') == 'none'){
		$("#commentbody" + shop_id).css("display","block");
		$("#slidebutton" + shop_id).text('▲');

	}else{
		$("#commentbody" + shop_id).css("display","none");
		$("#slidebutton" + shop_id).text('▼');
	}
	});
});

</script>

<div class="container">
<h4 style="text-align:center;">Y☆Rank-TOP</h4>
<p></p>
<form action="./rank_top.php" method="post" id="Form1">

<div class="row">
<div class="col-sm-8 offset-sm-2" style="text-align:center;">
HTML;
$check = $rank->check_position($ybase->my_position_class);
if($check){
	$auth_edit = 1;
}

$ybase->ST_PRI .= <<<HTML
【対象キャリア】<select name="t_shop_id">
<option value="0">全店舗</option>
HTML;
foreach($rank_section_name as $key => $val){
if($key == $t_shop_id){
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

$ybase->ST_PRI .= <<<HTML
</div>
</div>
<p></p>

<div class="row">
<div class="col-sm-8 offset-sm-2" style="text-align:center;">
【対象年月】<input type="month" name="tar_month" value="$tar_month">
</div>
</div>
</form>
<p></p>
HTML;
foreach($rank_section_list as $key => $val){
	if($t_shop_id && ($val != $t_shop_id)){
		continue;
	}
$emp_id= $q_com_employee_id[$key];
if(preg_match("/([0-9]{4})\/([0-9]{2})\/([0-9]{2})/",$q_com_date[$key],$str)){
	$comm_date_val = $str[1]."-".$str[2]."-".$str[3];
}else{
	$comm_date_val = "";
}
if(preg_match("/^([0-9]{4}\/[0-9]{1,2}\/[0-9]{1,2})(.*)/",$q_comment[$key],$str)){
	$q_comment[$key] = trim($str[2]);
}
$ybase->ST_PRI .= <<<HTML

<div class="row">
<div class="col-sm-6 offset-sm-3">
<div class="card">
<div class="card-header p-0">

<table border="0" style="padding:0;margin:0;" width="100%">
<tr>
<td align="left">
<a href="rank_top.php?$param&comm_no[$key]={$bf[$key]}" class="btn btn-sm btn-outline-secondary{$bf_disable[$key]}">◁</a>
<a href="rank_top.php?$param&comm_no[$key]={$nx[$key]}" class="btn btn-sm btn-outline-secondary{$nx_disable[$key]}">▷</a>

</td><td align="center"><span style="font-size:80%;">{$ybase->section_list[$key]}コメント</td></span><td align="right"><span style="font-size:60%;">{$q_com_date[$key]}　{$sec_employee_list[$emp_id]}</span>　<a href="#!" class="btn btn-sm btn-outline-info" id="slidebutton$key" shopid="$key">{$slidebutton[$key]}</a></td>
</tr>
</table>

</div>
<div class="card-body p-2" id="commentbody$key"{$bodydisplay[$key]}>
<form method="get" action="rank_top.php" id="form_com_{$key}">
<input type="hidden" name="t_month" value="$t_month">
<input type="hidden" name="t_shop_id" value="$t_shop_id">
<input type="hidden" name="comm_id" value="$key">
<input type="date" name="comm_date" class="date_cal" value="$comm_date_val" shop_id="$key">
</form>
$q_comment[$key]
</div>
</div>
</div>
</div>
HTML;
}

$ybase->ST_PRI .= <<<HTML

<p></p>

HTML;
if($t_shop_id){
	$linkable = "";
}else{
	$linkable = " disabled";
}
if($leader_check_last){
$ybase->ST_PRI .= <<<HTML
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-danger btn-block{$linkable}" href="./set_personal_fm.php?target_month=$last_month&target_shop_id=$t_shop_id&target_group_id=$leader_check_last" role="button">※{$last_month}のチーム配分を設定※</a></div>
</div>
<br>
HTML;
}
if($leader_check_this){
$ybase->ST_PRI .= <<<HTML
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-danger btn-block{$linkable}" href="./set_personal_fm.php?target_month=$this_month&target_shop_id=$t_shop_id&target_group_id=$leader_check_this" role="button">※{$this_month}のチーム配分を設定※</a></div>
</div>
<br>
HTML;
}
if($leader_check_next){
$ybase->ST_PRI .= <<<HTML
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-danger btn-block{$linkable}" href="./set_personal_fm.php?target_month=$next_month&target_shop_id=$t_shop_id&target_group_id=$leader_check_next" role="button">※{$next_month}のチーム配分を設定※</a></div>
</div>
<br>
HTML;
}
if($auth_edit == 1){
$ybase->ST_PRI .= <<<HTML
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-danger btn-block{$linkable}" href="./person_in.php?$param" role="button">個別実績入力</a></div>
</div>
<br>
HTML;
}

$ybase->ST_PRI .= <<<HTML
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block{$linkable}" href="./totalday1.php?$param" role="button">日別集計表（全体・個別）
</a></div>
</div>
<br>
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block{$linkable}" href="./daily_achieve.php?$param" role="button">日次実績(全体・グループ・個人)</a></div>
</div>
<br>
<!--
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block{$linkable}" href="./personal_achieve.php?$param" role="button">個別達成確認</a></div>
</div>
<br>
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block{$linkable}" href="./group_rank.php?$param" role="button">チーム実績</a></div>
</div>
<br>
-->
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block{$linkable}" href="./group_rank2.php?$param" role="button">チーム実績ランク順</a></div>
</div>
<br>
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block{$linkable}" href="./myp_rank.php?$param" role="button">日別MYP</a></div>
</div>
<br>
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block{$linkable}" href="./comment_reg.php?$param" role="button">コメント投稿用</a></div>
</div>
<br>
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block{$linkable}" href="./mail_reg.php?$param" role="button">メール送信用</a></div>
</div>
<br>
<!---
<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-info btn-block{$linkable}" href="./tunagu_reg.php?$param" role="button">TUNAG投稿用</a></div>
</div>
<br>
---->
HTML;
if($auth_edit == 1){
$ybase->ST_PRI .= <<<HTML

<div class="row">
<div class="col-sm-6 offset-sm-3"><a class="btn btn-outline-success btn-block{$linkable}" href="./set_top.php?{$param}&target_month=$t_month&target_shop_id=$t_shop_id" role="button">設定TOP</a></div>
</div>
<br>
HTML;
}

$ybase->ST_PRI .= <<<HTML

</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>