<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

//$edit_f = 1;
/////////////////////////////////////////


$conn = $ybase->connect();

$ybase->title = "メニュー画面設定";
$ybase->HTMLheader();

$path = $_SERVER['HTTP_HOST'].$ybase->PATH."inc/easyui";

$ybase->ST_PRI .= $ybase->header_pri($ybase->title.$addtitle);


$ybase->ST_PRI .= <<<HTML
<link rel="stylesheet" href="../portal/portal.css">
<script>
$(function($) {
	$("input[type=checkbox]" + "[id^=all]").change(function(){
		var targetval = $(this).attr('tarval');
		if($(this).prop('checked')){
			$("[id^=" + targetval + "]").prop("disabled", true);
			$("[id^=" + targetval + "]").css('display', 'none');
		}else{
			$("[id^=" + targetval + "]").prop("disabled", false);
			$("[id^=" + targetval + "]").css('display', 'inline');
		}
	});
});
</script>
<p></p>
<div style="font-size:80%;margin:1px 50px;">

<p></p>
<div style="text-align:right"><a class="btn btn-info btn-sm" href="./menu_new.php" role="button">新規メニュー作成</a></div>
メニューの表示設定をします。アイコンは選択された条件を全て満たす人に表示されます。
<div class="table-responsive">

<table class="table table-sm table-hover table-striped table-bordered" id="table1">

<thead class="thead-light">
<tr align="center">
<th class="border_bolt_right border_bolt_bottom">メニュー項目</th>
<th class="border_bolt_bottom">メニュー名<br>表示箇所<br>状態<br>クラス名<br>リンク先<br>表示順</th>
<th class="border_bolt_bottom">表示会社</th>
<th class="border_bolt_bottom">表示部署</th>
<th class="border_bolt_bottom">表示雇用区分</th>
<th class="border_bolt_bottom">表示役職区分</th>
<th class="border_bolt_bottom">表示管理区分</th>
<th class="border_bolt_bottom">更新</th>
</tr>
</thead>

<tbody>
HTML;

$sql = "select menu_id,menu_name,menu_class,menu_sub,link,belong_id,campaney_list,section_list,type_list,position_list,admin_list,list_no,add_date,status from menu where status <> '0' order by belong_id,list_no";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

for($i=0;$i<$num;$i++){
	list($q_menu_id,$q_menu_name,$q_menu_class,$q_menu_sub,$q_link,$q_belong_id,$q_campaney_list,$q_section_list,$q_type_list,$q_position_list,$q_admin_list,$q_list_no,$q_add_date,$q_status) = pg_fetch_array($result,$i);
$letters = array('{','}');
$q_campaney_list = str_replace($letters,"",$q_campaney_list);
$q_section_list = str_replace($letters,"",$q_section_list);
$q_type_list = str_replace($letters,"",$q_type_list);
$q_position_list = str_replace($letters,"",$q_position_list);
$q_admin_list = str_replace($letters,"",$q_admin_list);
$campaney_list_arry = explode(",",$q_campaney_list);
$section_list_arry = explode(",",$q_section_list);
$type_list_arry = explode(",",$q_type_list);
$position_list_arry = explode(",",$q_position_list);
$admin_list_arry = explode(",",$q_admin_list);



if($q_status == 2){
	$li_add_class = " inactive";
}else{
	$li_add_class = "";
}
$ybase->ST_PRI .= <<<HTML
<tr>
<form action="menu_cg.php" method="post">
<input type="hidden" name="tar_menu_id" value="$q_menu_id">
<td style="text-align:center;vertical-align:middle;">
<ul class="menu_btn flex justify_center flex_wrap">
<li class="{$q_menu_class}{$li_add_class}">
<p>$q_menu_name</p>
</li>
</ul>
</td>

<td style="text-align:left;vertical-align:middle;">
<table border="0">

<tr>
<td>メニュー名</td>
<td>
<input type="text" name="menu_name" value="$q_menu_name">
</td>
</tr>

<tr>
<td>表示箇所</td>
<td>
<select name="sel_belong">
HTML;
foreach($ybase->big_item_list as $key => $val){
	if($key == $q_belong_id){
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
<br><br>
</td>
</tr>

<tr>
<td>状態</td>
<td>
<select name="sel_status">
HTML;
foreach($ybase->menu_status_list as $key => $val){
	if($key == $q_status){
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
<br><br>
</td>
</tr>

<tr>
<td>クラス名</td>
<td>
<input type="text" name="menu_class" value="$q_menu_class">
</td>
</tr>

<tr>
<td>リンク先</td>
<td>
<input type="text" name="link" value="$q_link">
</td>
</tr>

<tr>
<td>表示順</td>
<td>
<input type="text" name="list_no" value="$q_list_no">
</td>
</tr>

</table>


</td>

HTML;
////////////////////////////////////////////
if(in_array("0",$campaney_list_arry, true)){
	$allchecked = " checked";
	$alldisabled = " disabled";
	$alltextcss = " style=\"display:none;\"";
}else{
	$allchecked = "";
	$alldisabled = "";
	$alltextcss = " style=\"display:inline;\"";
}
$ybase->ST_PRI .= <<<HTML

<td>
<input type="checkbox" name="campaney_all" value="1" id="all_campaney_{$q_menu_id}" tarval="campaney_{$q_menu_id}"{$allchecked}><label for="all_campaney_{$q_menu_id}">全員</label>
<hr style="margin:0px;padding:0px;">
HTML;
foreach($ybase->company_list as $key => $val){
//	if(!$alldisabled){
		if(in_array("$key",$campaney_list_arry, true)){
			$checked = " checked";
		}else{
			$checked = "";
		}
//	}
$ybase->ST_PRI .= <<<HTML
<input type="checkbox" name="sel_campaney[$key]" value="1" id="campaney_{$q_menu_id}_{$key}"{$alltextcss}{$checked}{$alldisabled}><label for="campaney_{$q_menu_id}_{$key}" id="campaney_{$q_menu_id}_{$key}_label"{$alltextcss}>$val</label><br id="campaney_{$q_menu_id}_{$key}_br"{$alltextcss}>
HTML;
}
/////////////////////////////////////////////////
$ybase->ST_PRI .= <<<HTML
</td>

HTML;
////////////////////////////////////////////
if(in_array("0",$section_list_arry, true)){
	$allchecked = " checked";
	$alldisabled = " disabled";
	$alltextcss = " style=\"display:none;\"";
}else{
	$allchecked = "";
	$alldisabled = "";
	$alltextcss = " style=\"display:inline;\"";
}
$ybase->ST_PRI .= <<<HTML

<td>
<input type="checkbox" name="section_all" value="1" id="all_section_{$q_menu_id}" tarval="section_{$q_menu_id}"{$allchecked}><label for="all_section_{$q_menu_id}">全員</label>
<hr style="margin:0px;padding:0px;">
HTML;
foreach($ybase->section_list as $key => $val){
//	if(!$alldisabled){
		if(in_array("$key",$section_list_arry, true)){
			$checked = " checked";
		}else{
			$checked = "";
		}
//	}
$ybase->ST_PRI .= <<<HTML
<input type="checkbox" name="sel_section[$key]" value="1" id="section_{$q_menu_id}_{$key}"{$alltextcss}{$checked}{$alldisabled}><label for="section_{$q_menu_id}_{$key}" id="section_{$q_menu_id}_{$key}_label"{$alltextcss}>$val</label><br id="section_{$q_menu_id}_{$key}_br"{$alltextcss}>
HTML;
}
/////////////////////////////////////////////////
$ybase->ST_PRI .= <<<HTML
</td>

HTML;
////////////////////////////////////////////
if(in_array("0",$type_list_arry, true)){
	$allchecked = " checked";
	$alldisabled = " disabled";
	$alltextcss = " style=\"display:none;\"";
}else{
	$allchecked = "";
	$alldisabled = "";
	$alltextcss = " style=\"display:inline;\"";
}
$ybase->ST_PRI .= <<<HTML

<td>
<input type="checkbox" name="type_all" value="1" id="all_type_{$q_menu_id}" tarval="type_{$q_menu_id}"{$allchecked}><label for="all_type_{$q_menu_id}">全員</label>
<hr style="margin:0px;padding:0px;">
HTML;
foreach($ybase->employee_type_list as $key => $val){
//	if(!$alldisabled){
		if(in_array("$key",$type_list_arry, true)){
			$checked = " checked";
		}else{
			$checked = "";
		}
//	}
$ybase->ST_PRI .= <<<HTML
<input type="checkbox" name="sel_type[$key]" value="1" id="type_{$q_menu_id}_{$key}"{$alltextcss}{$checked}{$alldisabled}><label for="type_{$q_menu_id}_{$key}" id="type_{$q_menu_id}_{$key}_label"{$alltextcss}>$val</label><br id="type_{$q_menu_id}_{$key}_br"{$alltextcss}>
HTML;
}
/////////////////////////////////////////////////
$ybase->ST_PRI .= <<<HTML
</td>

HTML;
//////////////////////////////////////////////////
if(in_array("0",$position_list_arry, true)){
	$allchecked = " checked";
	$alldisabled = " disabled";
	$alltextcss = " style=\"display:none;\"";
}else{
	$allchecked = "";
	$alldisabled = "";
	$alltextcss = " style=\"display:inline;\"";
}
$ybase->ST_PRI .= <<<HTML

<td>
<input type="checkbox" name="position_all" value="1" id="all_position_{$q_menu_id}" tarval="position_{$q_menu_id}"{$allchecked}><label for="all_position_{$q_menu_id}">全員</label>
<hr style="margin:0px;padding:0px;">
HTML;
foreach($ybase->position_class_list as $key => $val){
//	if(!$alldisabled){
		if(in_array("$key",$position_list_arry, true)){
			$checked = " checked";
		}else{
			$checked = "";
		}
//	}
$ybase->ST_PRI .= <<<HTML
<input type="checkbox" name="sel_position[$key]" value="1" id="position_{$q_menu_id}_{$key}"{$alltextcss}{$checked}{$alldisabled}><label for="position_{$q_menu_id}_{$key}" id="position_{$q_menu_id}_{$key}_label"{$alltextcss}>$val</label><br id="position_{$q_menu_id}_{$key}_br"{$alltextcss}>
HTML;
}
///////////////////////////////////////////////
$ybase->ST_PRI .= <<<HTML
</td>

<td>
HTML;
if(in_array('0',$admin_list_arry, true)){
	$adminchecked0 = " checked";
}else{
	$adminchecked0 = "";
}
if(in_array('1',$admin_list_arry, true)){
	$adminchecked1 = " checked";
}else{
	$adminchecked1 = "";
}
if(in_array('2',$admin_list_arry, true)){
	$adminchecked2 = " checked";
}else{
	$adminchecked2 = "";
}
$ybase->ST_PRI .= <<<HTML

<input type="radio" name="sel_admin" value="0" id="admin_{$q_menu_id}_0}"{$adminchecked0}><label for="admin_{$q_menu_id}_0}">全員</label><hr style="margin:0px;padding:0px;">
<input type="radio" name="sel_admin" value="1" id="admin_{$q_menu_id}_0}"{$adminchecked1}><label for="admin_{$q_menu_id}_1}">全権限ありのみ</label><br>
<input type="radio" name="sel_admin" value="2" id="admin_{$q_menu_id}_0}"{$adminchecked2}><label for="admin_{$q_menu_id}_2}">全権限なしのみ</label><br>
<br>
※この条件は左の条件に<br>
関係なく全てに優先されます<br>
</td>
<td style="text-align:center;vertical-align:middle;">
<input type="submit" value="更新">
</td>
</form>
</tr>
HTML;

}

$ybase->ST_PRI .= <<<HTML


</tbody>
</table>
$endexplain
</div>
</div>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>