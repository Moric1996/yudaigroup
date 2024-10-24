<?php



///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////

//$d="asobo";
//$t1="aaaaaaaaa";
//$t2="aaaaaaaaaaa";
$enc = urldecode($d);
$d = rawurlencode($d);
print <<<HTML

<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>QR表示</title>
</head>
<body>

<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<p></p>
<p></p>
<p></p>
<div style="text-align:center;">$enc<br><br>
<img src="https://yournet-jp.com/qr/qr_img.php?d=$d">
</div>
HTML;

////////////////////////////////////////////////
?>