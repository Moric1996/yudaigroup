<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$add_sql = ''; // または適切なSQLクエリ

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

$force = false; // 適切な初期値に設定

if(!$force){
	$sql = "select status from top_dashboard where employee_id = {$ybase->my_employee_id} and status = '1'";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
header("Location: https://".$_SERVER['HTTP_HOST']."/yudaigroup/portal/dashboard.php");
exit;
	}
}
if (isset($ybase->test_user[$ybase->my_employee_id]) && $ybase->test_user[$ybase->my_employee_id] == 302){
	$telecomtestf = 1; 
}else{
	$telecomtestf = 0; 
}

$sql = "select a.message_id from message as a INNER JOIN message_log as b ON a.message_id = b.message_id and b.employee_id = $ybase->my_employee_id where a.status = '1' and ((b.status is null) or (b.status = '1'))".$add_sql." order by a.add_date desc";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$badge = array();
if($num > 9){
	$badge[107] = "<span class=\"unread_badge\">9+</span>";
}elseif($num > 0){
	$badge[107] = "<span class=\"unread_badge\">{$num}</span>";
}else{
	$badge[107] = "";
}
if(!$ybase->my_email){
	$new_notice = "<div style=\"text-align:center;font-size:90%;\"><br>まずはEメールアドレスを登録してください<br><a href=\"../user/pass_cg.php\" class=\"regi_btn\">登録する</a></dev>";
}else{
	$new_notice = "";
}
$ttl = time();
if(preg_match("/iPhone|Android/i", $_SERVER['HTTP_USER_AGENT'])) {
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

$sql = "select menu_id,menu_name,menu_class,menu_sub,link,belong_id,campaney_list,section_list,type_list,position_list,admin_list,list_no,add_date,status from menu where status <> '0' order by belong_id,list_no";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

print <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <link rel="icon" href="./img/yudai_favicon.ico">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
	<link href="/yudaigroup/inc/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="portal.css?$ttl">
    <title>$title_name</title>

</head>
<body>
<div class="header flex justify_between items_center">
    <div class="header_title flex flex1">
        <a class="yd_logo" href="https://www.yudai.co.jp/hq/"></a>
        <div>$title_name</div>
    <div>　　　</div>
    <div><a class="btn btn-info btn-sm" href="./dashboard.php" role="button">My Dashboard</a></div>
    </div>
	<div class="member_name">

<span class="navbar-text">
<div class="dropdown">
<a class="navbar-brand dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 90%;">{$ybase->section_list[$ybase->my_section_id]}<br>{$ybase->my_name} 様</a>
<div class="dropdown-menu" aria-labelledby="dropdownMenuLink" style="font-size: 90%;font-color: black;">
<a class="dropdown-item text-reset" href="../logout.php">ログアウト</a>
</div>
</div>
</span>

    </div>
</div>
$new_notice
HTML;
$subtitle_no = 0;
for($i=0;$i<$num;$i++){
	list($q_menu_id,$q_menu_name,$q_menu_class,$q_menu_sub,$q_link,$q_belong_id,$q_campaney_list,$q_section_list,$q_type_list,$q_position_list,$q_admin_list,$q_list_no,$q_add_date,$q_status) = pg_fetch_array($result,$i);
if($telecomtestf && ($q_menu_id != 129)){
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
if($display_flag && ($ybase->my_company_id != 5)){
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
if($subtitle_no != $q_belong_id){
	if($ul_flag == 1){
		print "</ul>\n";
		$ul_flag = "";
	}
print <<<HTML
<div class="subtitle">
    {$ybase->big_item_list[$q_belong_id]}
</div>
<ul class="menu_btn flex justify_center flex_wrap">
HTML;
	$subtitle_no = $q_belong_id;
	$ul_flag = 1;
}
	if($display_flag){
	if(preg_match("/yudai.info/",$q_link)){
		$pm = "?sess0=$sess_id&mei0=".$ybase->my_employee_id;
	}else{
		$pm = "";
	}
	if(preg_match("/revuen.shop/",$q_link)){
		$pm = "?sess_id2=$sess_id&my_employee_id2=".$ybase->my_employee_id;
	}
print <<<HTML
    <li class="{$q_menu_class}{$li_add_class}">
        <a href="{$q_link}{$pm}">
            <p>$q_menu_name</p>
        </a>
{$badge[$q_menu_id]}
    </li>
HTML;
	}
}

$ul_flag = 0;

if($ul_flag == 1){
	print "</ul>\n";
	$ul_flag = "";
}
if(!$telecomtestf){

print <<<HTML
<div class="flex justify_center">
    <div class="memo">
        <p class="text_center font_bold">▶ WEBアンケート分析</p>
        <p>【ID】yudai.system@gmail.com【PASS】yuda1200</p>
    </div>
</div>
</body>
</html>
HTML;
}

////////////////////////////////////////////////
?>