<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////

include('/usr/local/htdocs/yudaigroup/inc/auth.inc');



print <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <link rel="icon" href="./img/yudai_favicon.ico">
    <link rel="stylesheet" href="portal.css?4">
    <title>雄大グループ業務管理ポータル</title>
</head>
<body>
<div class="header flex justify_between items_center">
    <div class="header_title flex flex1">
        <a class="yd_logo" href="https://www.yudai.co.jp/hq/"></a>
        <div>雄大グループ業務管理ポータル</div>
    </div>
    <div class="member_name">
    </div>
</div>
<div class="subtitle">
    雄大社内システム
</div>
<ul class="menu_btn flex justify_center flex_wrap">
    <li class="icon_performance">
        <a href="https://yournet-jp.com/yudaigroup/dailyperfom/index.php">
            <p>日次業績確認</p>
        </a>
    </li>
    <li class="icon_webform">
        <a href="https://docs.google.com/forms/u/0/">
            <p>WEBアンケート</p>
        </a>
    </li>
    <li class="icon_document">
        <a href="../manager_meeting/manager_meeting.php">
            <p>店長会議資料</p>
        </a>
    </li>
    <li class="icon_rules">
        <a href="../documanage/view.php">
            <p>社内規則･ルール</p>
        </a>
    </li>
    <li class="icon_notice">
        <a href="../message/message_list.php">
            <p>お知らせ</p>
        </a>
$badge
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
    <li class="icon_manual inactive">
        <a href="#">
            <p>マニュアル</p>
        </a>
    </li>
    <li class="icon_minutes inactive">
        <a href="#">
            <p>議事録</p>
        </a>
    </li>
    <li class="icon_vision inactive">
        <a href="#">
            <p>経営計画･ビジョン</p>
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
<div class="flex justify_center">
    <div class="memo">
        <p class="text_center font_bold">▶ WEBアンケート分析</p>
        <p>【ID】yudai.system@gmail.com</p>
        <p>【PASS】yuda1200</p>
    </div>
</div>
<div class="subtitle">
    他システムリンク
</div>
<ul class="menu_btn flex justify_center flex_wrap">
    <li class="img_bino">
        <a href="https://www.bino-fs.com/"></a>
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