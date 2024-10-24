<?php
######################################################################
#
# Project Name	:ASOBO
# File name		:/usr/local/httpd/admin/sql.php
# Description	:SQL実行結果
# Attention		:
#
######################################################################
if(isset($_POST)){
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
}
if(isset($_GET)){
	foreach($_GET as $key => $value){
		${$key} = $value;
	}
}
include('./inc/basic.inc');


function connect($connid="") {
	$host = "localhost";
	$port = "5432";
	$dbname = "yudai_admin";
	$user = "yournet";
	$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user");
	if(!$conn) {
		$msg = "DB_connect_error 時間を置いてお試しください。ERROR_CODE:00001";
		print $msg ;
		exit;
	}
	return $conn;
}

$conn = connect();


if($sql == "view") {
	$tablemode = 0;
	$sql = "select * from pg_views where schemaname = 'public' order by viewname";
}
if($sql == "rule") {
	$tablemode = 0;
	$sql = "select * from pg_rules where schemaname = 'public' order by tablename,rulename";
}
if($sql == "table") {
	$tablemode = 1;
	$sql = "SELECT c.oid as oid,n.nspname as \"Schema\",  c.relname as \"Name\",  CASE c.relkind WHEN 'r' THEN 'table' WHEN 'v' THEN 'view' WHEN 'i' THEN 'index' WHEN 'S' THEN 'sequence' WHEN 's' THEN 'special' END as \"Type\",  u.usename as \"Owner\" FROM pg_catalog.pg_class c LEFT JOIN pg_catalog.pg_user u ON u.usesysid = c.relowner LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace WHERE c.relkind IN ('r','')       AND n.nspname NOT IN ('pg_catalog', 'pg_toast')  AND pg_catalog.pg_table_is_visible(c.oid) and n.nspname = 'public' order by c.relname";	
}
if($sql == "table detail") {
	$tablemode = 1;
	$tabledetailmode = 1;
	$sql = "select * from pg_stat_user_tables;";
}

if($sql == "index") {
$sql = "select * from pg_stat_user_indexes order by relname,indexrelname;";
$indexmode = 1;
}

if($sql == "size") {
$sql = "select relname ,relfilenode ,(relpages::int8 * 8192) /1000000 as MB from pg_class  order by MB desc";
}


if(eregi("^[ ]*(update|delete)",$sql)) {
//print "Error";
//exit;
}
if(ereg("^[0-9]+$",$sql)) {
$sql = "SELECT a.attname,  pg_catalog.format_type(a.atttypid, a.atttypmod) FROM pg_catalog.pg_attribute a WHERE a.attrelid = '$sql' AND a.attnum > 0 AND NOT a.attisdropped ORDER BY a.attnum";
}
$sql = stripslashes($sql);


echo '$sqlの値は'.$sql.'です<br />';
$result = pg_query($conn,$sql);

$num = pg_num_rows($result);



print <<<HTML
<HTML>
<HEAD>
<TITLE>yudai-SQL実行</TITLE>
<STYLE TYPE=text/css>
A.LINK:LINK{ COLOR:BLUE;TEXT-DECORATION:NONE; }
A.LINK:VISITED{ COLOR:BLUE;TEXT-DECORATION:NONE; }
A.LINK:ACTIVE{ COLOR:BLUE;TEXT-DECORATION:NONE; }
A.LINK:HOVER{ COLOR:RED;TEXT-DECORATION:NONE; }
</STYLE>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">
</HEAD>
<BODY>
実行結果：$server <br>
$sql<br>
<hr>

HTML;



print "結果：${num}件<br>\n";

print("<table border>\n");
$columns = pg_num_fields($result);
for ($j = 0;$j < $num;$j++) {
  if ($j == 0) {
    print("<tr>");
    for ($i = 0;$i < $columns;$i++) {
      $str = pg_field_name($result,$i);	// 列名の取り出し
      print "<th>$str</th>";
    }
    print("</tr>\n");
  }



if($tabledetailmode) {
$kstr1 = pg_result($result,$j,3);
$kstr2 = pg_result($result,$j,5);
$kstr3 = pg_result($result,$j,7);
$kstr4 = pg_result($result,$j,8);
$kstr5 = pg_result($result,$j,9);

if(!$kstr1 && !$kstr2 && !$kstr3 && !$kstr4 && !$kstr5) {
  print("<tr bgcolor=red>");
} else {
  print("<tr>");
}
} elseif($indexmode) {
$kstr1 = pg_result($result,$j,5);
$kstr2 = pg_result($result,$j,6);
$kstr3 = pg_result($result,$j,7);

if(!$kstr1 && !$kstr2 && !$kstr3) {
  print("<tr bgcolor=red>");
} else {
  print("<tr>");
}


} else {
  print("<tr>");
}
  for ($i = 0;$i < $columns;$i++) {
    $str = pg_result($result,$j,$i);	// データの取り出し
    if($tablemode && $i == 0) {
    print("<td><a href=sql.php?sql=$str&server=$server&db_name=$db_name>$str</a></td>");
} elseif($tablemode && ($i == 2)) {
	$nxsql = "select * from $str";
    print("<td><a href=\"sql.php?sql=$nxsql\" target=\"_blank\">$str</a></td>");
} else {
    print("<td>$str</td>");
}
  }
  print("</tr>\n");
}
pg_freeresult($result);	// 検索結果の解放
pg_close();		// データベースとの接続切断
print("</table>");
print <<< HTML
</BODY>
</HRML>
HTML;
######################################################################
#
# 2015/01/19 Y-Ueda
# PHPバージョンアップにつき関数変更
#
######################################################################
?>