<?php
if(empty($_SERVER['HTTPS'])) {
    header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
    exit;
}
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('./inc/ybase.inc');

$ybase = new ybase();
if(preg_match("/^[0-9]+$/",$messageid)){
	$addhtml="<input type=\"hidden\" name=\"display_message_id\" value=\"$messageid\">\n";

}
if(preg_match("/^[0-9]+$/",$consultid)){
	$addhtml="<input type=\"hidden\" name=\"display_consultid\" value=\"$consultid\">\n";
}
if(preg_match("/^[0-9]+$/",$manag_reconsultid)){
	$addhtml="<input type=\"hidden\" name=\"manag_reconsultid\" value=\"$manag_reconsultid\">\n";
}
if(preg_match("/^[0-9]+$/",$reconsultid)){
	$addhtml="<input type=\"hidden\" name=\"reconsultid\" value=\"$reconsultid\">\n";
}

if(isset($_COOKIE[setaccountid])){
	$accountid = $_COOKIE[setaccountid];
}
if(isset($_COOKIE[setpasswd])){
	$passwd = $_COOKIE[setpasswd];
}

$ybase->title = "雄大業務管理ポータル ログイン";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri(1);

$ybase->ST_PRI .= <<<HTML

<br>
<br>
<br>
<br>
<p></p>
<p></p>
<p></p>

<p></p>
<div class="card border border-dark mx-auto" style="max-width: 40rem;">
<div class="card-header border-dark bg-light text-center">雄大グループ業務管理ポータル ログイン</div>
<div class="card-body">
<form action="login_ex2.php" method="post" id="LoginForm">
$addhtml

<div class="form-row">
<div class="form-group col-sm-6 offset-sm-3">
<label for="LoginAccountid">Eメールor社員番号</label>
<input type="text" name="accountid" value="$accountid" class="form-control form-control-sm border-dark{$accountid_err}" id="LoginAccountid" placeholder="4桁以上(例:0001)" required>
<div class="invalid-feedback">
Eメールアドレスor社員番号またはパスワードが間違っています
</div>
</div>
</div>

<div class="form-row">
<div class="form-group col-sm-6 offset-sm-3">
<label for="LoginPasswd">パスワード</label>
<input type="password" name="passwd" value="$passwd" class="form-control form-control-sm border-dark" id="LoginPasswd" placeholder="" required>

</div>
</div>

<div class="form-row">
<div class="form-group col-sm-6 offset-sm-3">
<div class="form-check">
<input name="savepass" class="form-check-input" type="checkbox" id="check1a" value="1" checked>
<label class="form-check-label" for="check1a">パスワードを保存する</label>
</div>
</div>
</div>

<div class="text-center small"><a href="./forget.php">パスワードを忘れた方</a></div>
<p></p>

<button class="btn btn-secondary col-sm-2 offset-sm-5 border-dark" type="submit">ログイン</button>

</form>
</div>
</div>
<p></p>
<br>
<br>
<br>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>