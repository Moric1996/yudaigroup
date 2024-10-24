<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();

$ybase->session_get();

if(!$ybase->my_employee_id){
header("Location: https://".$_SERVER['HTTP_HOST']."/yudaigroup/login.php?$QUERY_STRING");
exit;

}
if($ybase->my_admin_auth == "1"){
	$consult_top = "consult_manage.php";
}else{
	$consult_top = "consult_top.php";
}

$conn = $ybase->connect();
$sql = "select a.message_id from message as a LEFT OUTER JOIN message_log as b ON a.message_id = b.message_id and b.employee_id = $ybase->my_employee_id where a.status = '1' and ((b.status is null) or (b.status = '1'))".$add_sql." order by a.add_date desc";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

if($num > 9){
	$badge = "<span class=\"unread_badge\">9+</span>";
}elseif($num > 0){
	$badge = "<span class=\"unread_badge\">{$num}</span>";
}else{
	$badge = "";
}
if(!$ybase->my_email){
	$new_notice = "<div style=\"text-align:center;font-size:90%;\"><br>まずはEメールアドレスを登録してください<br><a href=\"../user/pass_cg.php\" class=\"regi_btn\">登録する</a></dev>";
}else{
	$new_notice = "";
}
$ttl = time();
if(preg_match("/iPhone|Android/i",$HTTP_USER_AGENT)) {
	$title_name="雄大業務管理ポータル";
}else{
	$title_name="雄大グループ業務管理ポータル";
}
if($ybase->my_employee_num == 'katsumata'){
	$test = $HTTP_USER_AGENT;
}
print <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <link rel="icon" href="./img/yudai_favicon.ico">
    <link rel="stylesheet" href="portal.css?$ttl">
	<link href="/yudaigroup/inc/css/bootstrap.min.css" rel="stylesheet">
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

<div class="subtitle">
    業務分析・確認・報告
</div>


<ul class="menu_btn flex justify_center flex_wrap">
    <li class="icon_performance">
        <a href="https://yournet-jp.com/yudaigroup/dailyperfom/index.php">
            <p>日次業績確認</p>
        </a>
    </li>
    <li class="icon_performance">
        <a href="https://yournet-jp.com/yudaigroup/monthlyperfom/revenue_total.php">
            <p>月次業績グラフ確認</p>
        </a>
    </li>
    <li class="icon_webform">
        <a href="https://docs.google.com/forms/u/0/">
            <p>WEBアンケート</p>
        </a>
    </li>
    <li class="icon_management">
        <a href="../csvconvert/csv_display.php">
        <p>経営会議資料</p>
        </a>
    </li>
    <li class="icon_document">
        <a href="../manager_meeting/manager_meeting.php">
            <p>店長会議資料</p>
        </a>
    </li>
    <li class="icon_pdca">
        <a href="../manager_pdca/pdca_top.php">
            <p>店長会議-取組報告</p>
        </a>
    </li>
    <li class="icon_analysis inactive">
        <a href="#">
            <p>店舗経営分析</p>
        </a>
    </li>
</ul>
<div class="flex justify_center">
    <div class="memo">
        <p class="text_center font_bold">▶ WEBアンケート分析</p>
        <p>【ID】yudai.system@gmail.com【PASS】yuda1200</p>
    </div>
</div>

<div class="subtitle">
    ツール・ドキュメント
</div>

$new_notice

<ul class="menu_btn flex justify_center flex_wrap">
    <li class="icon_notice">
        <a href="../message/message_list.php">
            <p>お知らせ</p>
        </a>
$badge
    </li>
    <li class="icon_consult">
         <a href="../consult/$consult_top">
	    <p>相談･提案</p>
        </a>
    </li>
    <li class="icon_rules">
        <a href="../documanage/view.php?kind=1">
            <p>社内規則･ルール</p>
        </a>
    </li>
    <li class="icon_minutes">
        <a href="../documanage/view.php?kind=2">
            <p>議事録</p>
        </a>
    </li>
    <li class="icon_manual">
        <a href="../documanage/view.php?kind=3">
            <p>マニュアル</p>
        </a>
    </li>
    <li class="icon_vision">
        <a href="../documanage/view.php?kind=4">
            <p>経営計画･ビジョン</p>
        </a>
    </li>
    <li class="icon_labor inactive">
        <a href="#">
            <p>労務管理</p>
        </a>
    </li>
    <li class="icon_request inactive">
        <a href="#">
            <p>日報<br>サービスリクエスト</p>
        </a>
    </li>
</ul>

<div class="subtitle">
    他システムリンク
</div>
<ul class="menu_btn flex justify_center flex_wrap">
    <li class="img_bino">
        <a href="https://www.bino-fs.com/"></a>
    </li>
    <li class="img_infomart">
        <a href="https://www.infomart.co.jp/scripts/logon.asp"></a>
    </li>
    <li class="img_ptcard">
        <a href="#"></a>
    </li>
    <li class="img_reserve">
        <a href="#"></a>
    </li>
    <li class="img_yumail">
        <a href="#"></a>
    </li>
    <li class="img_funcrew">
        <a href="https://www.fancrew.jp/flogin"></a>
    </li>
    <li class="img_jinjer">
        <a href="https://jinji.jinjer.biz/auth/login"></a>
    </li>
    <li class="img_jmotto">
        <a href="https://www1.j-motto.co.jp/fw/dfw/po80/portal/contents/login.html"></a>
    </li>
    <li class="img_tunag">
        <a href="https://tunag.jp/users/sign_in"></a>
    </li>
    <li class="empty"></li>
    <li class="empty"></li>
    <li class="empty"></li>
    <li class="empty"></li>
    <li class="empty"></li>
    <li class="empty"></li>
</ul>
<div class="subtitle">
    オプション
</div>
<ul class="menu_btn flex justify_center flex_wrap">
    <li class="icon_myaccount">
        <a href="../user/pass_cg.php">
            <p>マイアカウント</p>
        </a>
    </li>
    <li class="icon_setting">
        <a href="">
            <p>設定</p>
        </a>
    </li>
    <li class="icon_manage">
        <a href="../setting/user_list.php">
            <p>管理</p>
        </a>
    </li>
    <li class="empty"></li>
    <li class="empty"></li>
    <li class="empty"></li>
    <li class="empty"></li>
    <li class="empty"></li>
    <li class="empty"></li>
    <li class="empty"></li>
    <li class="empty"></li>
    <li class="empty"></li>
</ul>
</body>
</html>
HTML;

////////////////////////////////////////////////
?>