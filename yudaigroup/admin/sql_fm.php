<?php
######################################################################
#
# Project Name	:ASOBO
# File name	:/usr/local/httpd/admin/circle/top.php
# Description	:ADMINサークルTOP
# Attention	:
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


$nyear = date("Y");
$nmon = date("m");

$stat_arr[0] = "承認待ち";

print <<<HTML
<HTML>
<HEAD>
<TITLE>yudai-SQL実行フォーム</TITLE>
<STYLE TYPE=text/css>
A.LINK:LINK{ COLOR:BLUE;TEXT-DECORATION:NONE; }
A.LINK:VISITED{ COLOR:BLUE;TEXT-DECORATION:NONE; }
A.LINK:ACTIVE{ COLOR:BLUE;TEXT-DECORATION:NONE; }
A.LINK:HOVER{ COLOR:RED;TEXT-DECORATION:NONE; }
</STYLE>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">
</HEAD>
<BODY>
<SCRIPT Language="JavaScript">
<!---
function copy(d1) {
document.dataForm.sql.value = d1;
}


-->
</SCRIPT>
SQLを実行
<form action=sql.php method="post" name="dataForm">

<br>
SQL文<br>
<input type=text name=sql size=100><br>
<input type=submit value=実行>
</form>

<b><a href="./asobodoc/pages/tables/index.php">テーブル定義確認(本番のみ)</a></b><br><br>



<b>短縮コマンド</b><br>
<input type=button value="table" onClick=copy('table')>:テーブル一覧<br>
<input type=button value="table detail" onClick="copy('table detail')">:テーブル一覧詳細（統計情報）<br>
<input type=button value="size"  onClick=copy("size")>:テーブルサイズ一覧<br>
<input type=button value="index"  onClick=copy("index")>：インデックス一覧詳細（統計情報）<br>
<input type=button value="view" onClick=copy("view")>:ビューテーブル一覧<br>
<input type=button value="rule" onClick=copy("rule")>:ルール一覧<br>
<input type=button value="search *****" onClick=copy('search')>：テーブル「*****」があるDBを検索 ※本番環境でASOBOのDB内のみ
<hr>
HTML;
print <<<HTML
:<br>
:<br>
:<br>
</BODY>
</HTML>
HTML;
?>