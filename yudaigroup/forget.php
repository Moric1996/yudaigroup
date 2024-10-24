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
$ybase->title = "雄大グループ業務管理ポータル-パスワードを忘れた方";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri(1);

$ybase->ST_PRI .= <<<HTML
<br>
<br>
<br>
<br>
<br>
<p></p>
<p></p>
<p></p>

<p></p>
<div class="card border border-dark mx-auto" style="max-width: 40rem;">
<div class="card-header border-dark bg-light text-center">パスワードを忘れた方</div>
<div class="card-body">
<form action="forget_mail_send.php" method="post">

<div class="form-row">
<div class="form-group col-sm-6 offset-sm-3">
<label for="InputEmail">Eメールアドレス</label>
<input type="text" name="email" value="$email" class="form-control form-control-sm border-dark{$email_err}" id="InputEmail" placeholder="" required>
<div class="invalid-feedback">
該当するEメールアドレスがみつかりません。管理者にお問合せ下さい。
</div>
</div>
</div>

<p></p>

<button class="btn btn-secondary col-sm-2 offset-sm-5 border-dark" type="submit">送信</button>

</form>
</div>
※ドメイン[yournet-jp.com]からのメールを受け取れる様にしておいてください。<br>
※登録Eメールアドレスがわからない場合は、管理者の方にお問合せ下さい。<br>
※Eメールアドレスを登録していない場合は、管理者の方にお問合せ下さい。
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