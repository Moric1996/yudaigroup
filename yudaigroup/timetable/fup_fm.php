<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

/////////////////////////////////////////

$conn = $ybase->connect();



//////////////////////////////////////////条件

//////////////////////////////////////////

$ybase->title = "通信タイムテーブル生成";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("通信タイムテーブル生成");


$ybase->ST_PRI .= <<<HTML
<div class="container">
<p></p>
<br>
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">タイムテーブル表生成</div>
<div class="card-body">
シフトのタイムテーブル表を作成します。(PDFはA3横になります)<br>
下記ファイルフォーマットのCSVファイルをアップロードしてください。
<br>
<br>
【ファイルフォーマット】<br>
[事業所,姓,名,日付,曜日,祝日,シフト種類,シフト名称,出勤予定,退勤予定,休憩予定]<br>
※1行目は項目名にしてください。<br>
※１つのファイルは1事業所、1ヵ月分としてください。<br>
<br><br>
※PDFの作成には数十秒程時間がかかります。
<br><br>

<br>
<form action="./table_vw.php" method="post" enctype="multipart/form-data" id="form2">

<div class="form-row">
<div class="form-group col-sm-4 offset-sm-4">
 <input type="hidden" name="MAX_FILE_SIZE" value="50000000">
<input type="file" name="csvfile" class="form-control-file form-control-sm" id="csvfile">
</div>
<div class="text-right">

<button class="btn btn-primary btn-sm" type="submit">送信</button> <button class="btn btn-secondary btn-sm" type="reset">クリア</button>
</div>
</div>

</form>
</div>
</div>


HTML;

$k = 10;

if($k > 0){
for($i=0;$i<$k;$i++){
$ybase->ST_PRI .= "<br><br>";

}

}
$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>