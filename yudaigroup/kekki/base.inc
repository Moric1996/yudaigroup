<?php
class base  {
//////////////////////////////////////////////////////////////////////////////
function base() {
	$this->agent  = getenv("HTTP_USER_AGENT");
	$this->remote_addr  = getenv("REMOTE_ADDR");

	if(preg_match("/Android/i",$this->agent)){
		$this->device = 1;
	}elseif(preg_match("/iPhone|iPad|iPod/i",$this->agent)){
		$this->device = 1;
	}elseif(preg_match("/DoCoMo/i",$this->agent)){
		$this->device = 3;
	}elseif(preg_match("/KDDI/i",$this->agent)){
		$this->device = 3;
	}elseif(preg_match("/SoftBank/i",$this->agent)){
		$this->device = 3;
	}elseif(preg_match("/WILLCOM/i",$this->agent)){
		$this->device = 3;
	}else{
		$this->device = 2;
	}
}
//////////////出力データ作成////////////////////////////////////////////////////////////////
function pri($str=""){
	$this->ST_PRI .= $str;
	return;
}
//////////////HTMLヘッダー////////////////////////////////////////////////////////////////
function hd($title=""){
if($this->device == "2"){
	$body_add = " class=\"pcver\"";
}else{
	$body_add = "";
}

$this->ST_PRI = <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="format-detection" content="telephone=no">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=0">
<link rel="stylesheet" href="./main.css?3" type="text/css" />
<title>$title</title>
</head>
<body{$body_add}>
HTML;
	return;
}
///////////////HTMLフッター///////////////////////////////////////////////////////////////
function ft(){

$this->ST_PRI .= <<<HTML
<hr>
</body></html>
HTML;
	return;
}
////////////////エラー処理//////////////////////////////////////////////////////////////
function error($comment="",$sendmail=""){
	$this->hd("エラー");
	$this->pri("<div class=\"head\">雄大グループ モラールサーベイ アンケート</div><hr>");
	$this->pri("<br>$comment<br><br>");
	$this->pri("<hr><div class=\"center\"><a href=\"$this->bk_script?$this->bk_param\" class=\"square_btn\">戻る</a></div>");
	

	$this->ft();
	$this->priout();
	if($sendmail == 1){
//	mail("katsumata@yournet-jp.com","モラールサーベイシステムエラー","$comment");	
	}
	exit;
return;
}
///////////////HTML出力処理///////////////////////////////////////////////////////////////
function priout(){
	print $this->ST_PRI; 
	return;
}
////////////////DB接続//////////////////////////////////////////////////////////////
function connect($connid=""){
	$conn = @pg_connect("host=localhost port=5432 dbname=moralesurvey user=yournet");
	if(!$conn){
		$this->error("DB接続エラーです。何度か試して解決しない場合はユアネット勝又までご連絡ください。katsumata@yournet-jp.com",1);
	}
	return $conn;
}
////////////////DBクエリー実行/////////////////////////////////////////////////////////////
function sql($conn,$sql) {
	$result = @pg_query($conn,$sql);
	if(!$result) {
		$this->error("DBクエリエラーです。何度か試して解決しない場合はユアネット勝又までご連絡ください。katsumata@yournet-jp.com<br>$sql<br>",1);
	}
	return $result;
}

//////////////////////////////////////////////////////////////////////////////
}
?>