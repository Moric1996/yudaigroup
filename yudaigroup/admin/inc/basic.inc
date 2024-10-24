<?php
######################################################################
#
# Project Name	:ネクサイトサーバー管理
# File name		:61.115.230.230　/usr/local/httpd/nex/inc/basic.inc
# Description	:クラス定義
# Attention		:
#
######################################################################

if(!$PHP_AUTH_USER || !$PHP_AUTH_PW) {
	header("HTTP/1.0 401 Authorized Request");
	header("WWW-authenticate: basic realm=\"reffer analyze\"");
	exit;
}

$result = file_get_contents("https://$PHP_AUTH_USER:$PHP_AUTH_PW@yournet-jp.com/api/auth/");

if ($result <> 1) {
	Header("HTTP/1.0 401 Unauthorized");
	Header("WWW-authenticate: basic realm=\"reffer analyze\"");
	print("認証エラー!!");
	exit ;
}

#
######################

?>
