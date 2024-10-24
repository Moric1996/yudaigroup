<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
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
include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/scan.inc');

$ybase = new ybase();
//$slip = new slip();

$ybase->session_get();
$ybase->my_company_id = 5;

//$ybase->make_yournet_employee_list("");
/////////////////////////////////////////

$kensu=50;
$tim=time();
/////////////////////////////////////////

//$conn = $ybase->connect(3);

//////////////////////////////////////////条件


$tardir = "$FTPdir"."/*";

$result = glob("$tardir");
//var_dump($result);

//////////////////////////////////////////

$allpage = ceil($num / $kensu);
if(!$page){
	$page = 1;
}
$st = ($page - 1) * $kensu;
$end = $st + $kensu;
if($end > $num){
	$end = $num;
}

$ybase->title = "BROTHERスキャンリスト";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("BROTHERスキャンリスト");

$ybase->ST_PRI .= <<<HTML
HTML;



$ybase->ST_PRI .= <<<HTML
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js"></script>

<div class="container">




<p></p>
<div class="text-center">
<div class="text-left">
brotherの「スキャン」→「FTP:FTPサーバーに保存」→「yournet」を選択してスキャンしてください。<br>
PDFファイルのA4サイズでこのリストにアップされます。<br>
ファイルは30分で自動削除されます。
</div>
</div>


<p></p>

<table class="table table-bordered table-hover table-sm" id="sliptable1">
  <thead>
    <tr align="center" class="table-primary">
      <th scope="col">UP日時</th>
      <th scope="col">ファイル名</th>
      <th scope="col">ダウンロード</th>
      <th scope="col">削除</th>
    </tr>
  </thead>
  <tbody id="downloadbody1">
<form action="./evicsvdl.php" method="post" id="formdowncsv">
HTML;

foreach($result as $key => $filename){
//	print "$filename<br>";
	list($tmp,$filename0) = explode("/var/scanftp/",$filename);
//	print "$filename0<br>";
	$updateDate = filemtime($filename);
	$updateYMD = date("Y/m/d H:i:s",$updateDate);
//	print "$updateYMD<br>";


$ybase->ST_PRI .= <<<HTML
<tr>
<td align="center">
$updateYMD
</td>
<td align="center">
$filename0
</td>
<td align="center">
<a href="./dl.php?tarfile=$filename0" type="button" class="btn btn-primary btn-sm">ダウンロード</a>
</td>
<td align="center">
<a href="./del.php?tarfile=$filename0" type="button" class="btn btn-secondary btn-sm">削除</a>
</td>
</tr>
HTML;


}


$bf=$page-1;
$nx=$page+1;
if($bf < 1){
	$addbfclass=" disabled";
}else{
	$addbfclass="";
}
if($nx > $allpage){
	$addnxclass=" disabled";
}else{
	$addnxclass="";
}

$ybase->ST_PRI .= <<<HTML
 </tbody>
</table>
<div class="row">
<div class="col float-right">
<span class="float-right">
{$page}／{$allpage}
<a href="scan_list.php?$param&page=$bf" class="btn btn-sm btn-outline-secondary{$addbfclass}">＜</a>
<a href="scan_list.php?$param&page=$nx" class="btn btn-sm btn-outline-secondary{$addnxclass}">＞</a>
</span>
</div>
</div>
</div>
<p></p>

HTML;

$k = 10 - $num;

if($k > 0){
for($i=0;$i<$k;$i++){
$ybase->ST_PRI .= "<br><br>";

}

}
$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////


?>