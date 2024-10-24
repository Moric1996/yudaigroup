<?php
include("../base.inc");
include("../item_list.inc");

$base            = new base();

$csvFileName = "tmpfile.csv";

// ダウンロード開始
header('Content-Type: application/octet-stream');

// ここで渡されるファイルがダウンロード時のファイル名になる
header('Content-Disposition: attachment; filename=msdata.csv'); 
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($csvFileName));
readfile($csvFileName);

?>