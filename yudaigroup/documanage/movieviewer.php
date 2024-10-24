<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

//$kind = 1;
$company_id = 1;
/////////////////////////////////////////
if(!$company_id){
	$ybase->error("パラメーターエラー");
}
if(!$file_id){
	$ybase->error("パラメーターエラー");
}

// 7日前の日付取得
$ago = date("Y-m-d", strtotime("-1 day"));

// JPGファイル一覧を取得
$res = glob("tmp/*.*");
foreach ($res as $file) {
	// ファイルのタイムスタンプを取得
	$unixdate = filemtime($file);
	// タイムスタンプを日付のフォーマットに変更
	$filedate = date("Y-m-d", $unixdate);
	// 日付を比較して、7日より前のファイルなら削除
	if($filedate < $ago){
		unlink($file);
	}
}


$ua = $_SERVER['HTTP_USER_AGENT'];

$conn = $ybase->connect();
$sql = "select filename,kind,ext from docu_manage where file_id = $file_id and company_id = $company_id and status = '1' and type='2'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当なし:$sql");
}
list($q_filename,$q_kind,$q_ext) = pg_fetch_array($result,0);
if(!$ybase->mime_type[$q_ext]){
	$ybase->error("該当なし");
}
$filename = "/home/yournet/yudai/documanage/".$q_kind."/".$q_filename.".".$q_ext;
$tempfilename = "tmp/".$q_filename.time().rand(1000,9999).".".$q_ext;

$cpfilename = "/usr/local/htdocs/yudaigroup/documanage/".$tempfilename;
$urlname = "./".$tempfilename;
$flg = copy("$filename","$cpfilename");



$ybase->HTMLheader();

if(($q_ext == "mpeg")||($q_ext == "mpg")||($q_ext == "mp4")||($q_ext == "mov")||($q_ext == "avi")||($q_ext == "webm")){
if(!preg_match("/iPhone/i",$ua)){
	$urlname = "./viewmove.php?file_id=$file_id&company_id=$company_id";
}
$ybase->ST_PRI .= <<<HTML
<video autoplay controls playsinline controlsList="nodownload" oncontextmenu="return false;" src="$urlname" width="100%"></video>
HTML;
}elseif(($q_ext == "jpeg")||($q_ext == "jpg")||($q_ext == "png")||($q_ext == "gif")){
$ybase->ST_PRI .= <<<HTML
<img src="$urlname" width="100%" border="0">
HTML;
}elseif($q_ext == "txt"){

$ybase->ST_PRI .= <<<HTML
<object data="$urlname" type="text/plain" width="100%" height="500px"></object>
HTML;
}


$ybase->ST_PRI .= <<<HTML
</body>
</html>
HTML;

//$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////

?>