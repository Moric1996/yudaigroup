<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();

$sess_id = $ybase->session_get();

if(!$ybase->my_employee_id){
header("Location: https://".$_SERVER['HTTP_HOST']."/yudaigroup/login.php?$QUERY_STRING");
exit;

}
if (isset($_COOKIE["PHPSESSID"])) {
	setcookie("PHPSESSID", '', time() - 1800,'/');
}


$conn = $ybase->connect();

$ttl = time();
if(preg_match("/iPhone|Android/i",$HTTP_USER_AGENT)) {
	$title_name="雄大業務管理ポータル";
}else{
	$title_name="雄大グループ業務管理ポータル";
}
if($ybase->my_employee_num == 'katsumata'){
	$test = 1;
}else{
	$test = "";
}
if($ybase->my_company_id == 5){
	$test = 1;
}
$sql = "select status from top_dashboard where employee_id = {$ybase->my_employee_id}";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$top_flag = pg_fetch_result($result,0,0);
}else{
	$top_flag = "";
}
if($top_flag == "1"){
	$checked = " checked";
}else{
	$checked = "";
}
$sql = "select a.menu_id,a.menu_name,a.menu_class,a.menu_sub,a.link,a.belong_id,a.campaney_list,a.section_list,a.type_list,a.position_list,a.admin_list,a.list_no,a.add_date,a.status,b.jun from menu as a,dashboard as b where a.menu_id = b.menu_id and b.employee_id = {$ybase->my_employee_id} and a.status <> '0' and b.status = '1' order by b.jun,a.belong_id,a.list_no";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

$ybase->ST_PRI = <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <link rel="icon" href="./img/yudai_favicon.ico">
    <link rel="stylesheet" href="portal.css?$ttl">
	<link href="/yudaigroup/inc/css/bootstrap.min.css" rel="stylesheet">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script type="text/javascript" src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script type="text/javascript" src="./js/dashboard_e.js"></script>
    <title>$title_name</title>

</head>
<body>
<div class="header flex justify_between items_center">
    <div class="header_title flex flex1">
        <a class="yd_logo" href="https://www.yudai.co.jp/hq/"></a>
        <div>$title_name</div>
    </div>
	<div class="member_name">
        {$ybase->section_list[$ybase->my_section_id]}<br>{$ybase->my_name} 様
    </div>
</div>
$new_notice

HTML;

$ybase->ST_PRI .= <<<HTML
<div class="subtitle">
   My Dashboard
</div>
<ul id="sortable1" class="menu_btn flex justify_center flex_wrap connectedSortable">
HTML;
if(!$num){
$ybase->ST_PRI .= <<<HTML
<br>
<br>
<br>
<br>
<br>
<br>
HTML;

}
$display_menu_id_arr = array();
$subtitle_no = 0;
for($i=0;$i<$num;$i++){
	list($q_menu_id,$q_menu_name,$q_menu_class,$q_menu_sub,$q_link,$q_belong_id,$q_campaney_list,$q_section_list,$q_type_list,$q_position_list,$q_admin_list,$q_list_no,$q_add_date,$q_status,$q_jun) = pg_fetch_array($result,$i);
	array_push($display_menu_id_arr,$q_menu_id);
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
	$display_flag="1";
if($ybase->my_admin_auth == "1"){
	if(in_array("2",$admin_list_arry, true)){
		$display_flag="0";
	}
}else{
	if(in_array("1",$admin_list_arry, true)){
		$display_flag="0";
	}
}
if($display_flag){
	if(!in_array("0",$campaney_list_arry, true)){
		if(!in_array("{$ybase->my_company_id}",$campaney_list_arry, true)){
			$display_flag="0";
		}elseif(!$ybase->my_company_id){
			$display_flag="0";
		}
	}
}
if($display_flag){
	if(!in_array("0",$section_list_arry, true)){
		if(!in_array("{$ybase->my_section_id}",$section_list_arry, true)){
			$display_flag="0";
		}elseif(!$ybase->my_section_id){
			$display_flag="0";
		}
	}
}
if($display_flag){
	if(!in_array("0",$type_list_arry, true)){
		if(!in_array("{$ybase->my_employee_type}",$type_list_arry, true)){
			$display_flag="0";
		}elseif(!$ybase->my_employee_type){
			$display_flag="0";
		}
	}
}
if($display_flag){
	if(!in_array("0",$position_list_arry, true)){
		if(!in_array("{$ybase->my_position_class}",$position_list_arry, true)){
			$display_flag="0";
		}elseif(!$ybase->my_position_class){
			$display_flag="0";
		}
	}
}
if($test){
	$display_flag="1";
}

	if($display_flag){

$ybase->ST_PRI .= <<<HTML
    <li class="{$q_menu_class}{$li_add_class}" id="$q_menu_id">
            <p>$q_menu_name</p>
        </a>
{$badge[$q_menu_id]}
    </li>
HTML;
	}
}


$ybase->ST_PRI .= <<<HTML
</ul>
<div class="text-center">
<input type="checkbox" name="boardon" value="1"{$checked} id="boardon">「My Dashboard」をトップページにする<br>
<a class="btn btn-info btn-sm" href="./dashboard.php" role="button">編集完了</a><br><br>
<div class="container">
<div class="text-left">
※追加するメニュー項目を下のリストから「My Dashboard」へドラッグ&ドロップしてください<br>
※削除する場合は、削除するメニュー項目を「My Dashboard」から下のリストへドラッグ&ドロップしてください<br>
※ドラッグ&ドロップで順番も変更できます
</div>
</div>
</div>


HTML;


$sql = "select menu_id,menu_name,menu_class,menu_sub,link,belong_id,campaney_list,section_list,type_list,position_list,admin_list,list_no,add_date,status from menu where status <> '0' order by belong_id,list_no";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

$ybase->ST_PRI .= <<<HTML
<div class="subtitle">
   Menu List not on Dashboard
</div>
<ul id="sortable2" class="menu_btn flex justify_center flex_wrap connectedSortable">
HTML;
$subtitle_no = 0;
for($i=0;$i<$num;$i++){
	list($q_menu_id,$q_menu_name,$q_menu_class,$q_menu_sub,$q_link,$q_belong_id,$q_campaney_list,$q_section_list,$q_type_list,$q_position_list,$q_admin_list,$q_list_no,$q_add_date,$q_status) = pg_fetch_array($result,$i);
	if((in_array($q_menu_id,$display_menu_id_arr,true))){
		continue;
	}

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
	$display_flag="1";
if($ybase->my_admin_auth == "1"){
	if(in_array("2",$admin_list_arry, true)){
		$display_flag="0";
	}
}else{
	if(in_array("1",$admin_list_arry, true)){
		$display_flag="0";
	}
}
if($display_flag){
	if(!in_array("0",$campaney_list_arry, true)){
		if(!in_array("{$ybase->my_company_id}",$campaney_list_arry, true)){
			$display_flag="0";
		}elseif(!$ybase->my_company_id){
			$display_flag="0";
		}
	}
}
if($display_flag){
	if(!in_array("0",$section_list_arry, true)){
		if(!in_array("{$ybase->my_section_id}",$section_list_arry, true)){
			$display_flag="0";
		}elseif(!$ybase->my_section_id){
			$display_flag="0";
		}
	}
}
if($display_flag){
	if(!in_array("0",$type_list_arry, true)){
		if(!in_array("{$ybase->my_employee_type}",$type_list_arry, true)){
			$display_flag="0";
		}elseif(!$ybase->my_employee_type){
			$display_flag="0";
		}
	}
}
if($display_flag){
	if(!in_array("0",$position_list_arry, true)){
		if(!in_array("{$ybase->my_position_class}",$position_list_arry, true)){
			$display_flag="0";
		}elseif(!$ybase->my_position_class){
			$display_flag="0";
		}
	}
}
if($test){
	$display_flag="1";
}

	if($display_flag){
$ybase->ST_PRI .= <<<HTML
    <li class="{$q_menu_class}{$li_add_class}" id="$q_menu_id">
            <p>$q_menu_name</p>
        </a>
{$badge[$q_menu_id]}
    </li>
HTML;
	}
}


$ybase->ST_PRI .= <<<HTML
</ul>
HTML;



$ybase->HTMLfooter();
$ybase->priout();

////////////////////////////////////////////////
?>