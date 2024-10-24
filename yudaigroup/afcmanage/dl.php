<?php

$resorce = './sample.txt';
// ファイルを読み込み変数に格納
$content = file_get_contents($resorce);

$filename = "index.html";
$filesize = strlen($content);
header('Content-Type: application/pdf; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header("Content-Length: $filesize");
header("Content-Disposition: attachment; filename=$filename");
header('Connection: close');
while (ob_get_level()) { ob_end_clean(); }
echo $content;

exit;
?>