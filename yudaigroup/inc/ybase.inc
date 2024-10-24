<?php

////////////////////////////////////////////////////////////////////// START class ybase
class ybase {

	public $yournet_type_list;
	public $yournet_class_list;
	public $test_user = array();
	public $PATH;
	public $ST_PRI;
	
public function __construct(){
	include("yarraylist.inc");
	$this->PATH = "/yudaigroup/";
	$this->ST_PRI = "";

	if($_SERVER["REMOTE_ADDR"] == "106.185.149.135"){//TEST用判断
		$this->TEST=1;
	}else{
		$this->TEST=0;
	}
	$ua = $_SERVER['HTTP_USER_AGENT'];
	if(preg_match("/iPhone|Android/i",$ua)){
		$this->device_type = "MB";
		$this->NUM_INPUT_TYPE = "tel";
	}else{
		$this->device_type = "PC";
		$this->NUM_INPUT_TYPE = "number";
		if(preg_match("/edge/i",$ua)){
			$this->browser_type = "edge";
		}elseif(preg_match("/chrome/i",$ua)){
			$this->browser_type = "chrome";
		}elseif(preg_match("/Safari/i",$ua)){
			$this->browser_type = "safari";
		}
	}
}
////////////////////////////////////////////////////////////////////// END function construct
public function pri($str=""){				//HTMLデータ作成
	$this->ST_PRI .= $str;
	return;
}
////////////////////////////////////////////////////////////////////// END function pri
public function priout(){						//HTMLデータ出力
	print $this->ST_PRI; 
	return;
}
////////////////////////////////////////////////////////////////////// END function priout
public function HTMLheader($title="") {			//HTMLヘッダー
if($this->title){
	$title = $this->title;
}
$this->ST_PRI .= <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<link rel="icon" href="https://yournet-jp.com/yudaigroup/portal/img/yudai_favicon.ico">
<link rel="apple-touch-icon" href="https://yournet-jp.com/yudaigroup/portal/img/yudai_favicon.ico" />
<title>$title</title>
<link href="/yudaigroup/inc/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
HTML;
	return;
}
////////////////////////////////////////////////////////////////////// END function HTMLheader
public function HTMLheader2($title="") {			//HTMLヘッダー
if($this->title){
	$title = $this->title;
}
$this->ST_PRI .= <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<link rel="icon" href="https://yournet-jp.com/yudaigroup/portal/img/yudai_favicon.ico">
<link rel="apple-touch-icon" href="https://yournet-jp.com/yudaigroup/portal/img/yudai_favicon.ico" />
<title>$title</title>
<link href="/yudaigroup/inc/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
HTML;
	return;
}
////////////////////////////////////////////////////////////////////// END function HTMLheader
public function HTMLfooter($title="") {			//HTMLフッター
$this->ST_PRI .= <<<HTML

<footer style="background-color:#63baa8;color:#ffffff">YUDAI Group System</footer>
</body></html>
HTML;
	return;
}
////////////////////////////////////////////////////////////////////// END function HTMLfooter
public function error($mess="",$sql="") {
	$this->ST_PRI = "";
	$this->HTMLheader("NOTICE");
	$this->ST_PRI .= $this->header_pri("NOTICE");
	if(!$this->my_employee_id){
		$url = "https://".$_SERVER['HTTP_HOST']."/yudaigroup/login.php";
		$mess .= "<br><br><a href=\"$url\">ログイン画面へ</a>";
	}
	if(preg_match("/rank$/",dirname($_SERVER['PHP_SELF']))){
		$url = "https://".$_SERVER['HTTP_HOST']."/yudaigroup/rank/rank_top.php";
		$mess .= "<br><br><a href=\"$url\">Y☆Rank TOPへ</a>";
	}
	$this->pri("<hr><div style=\"align:center\">$mess</div><hr>");
	$this->HTMLfooter();
	$this->priout();

	$headers = apache_request_headers();
	$aa = "$mess\n";
	$aa .= "header:\n";
	foreach($headers as $k => $v){
		$aa .= "$k = $v\n";
	}
	$REMOTE_ADDR = getenv("REMOTE_ADDR");
	$SCRIPT_NAME = getenv("SCRIPT_NAME");
	$QUERY_STRING = getenv("QUERY_STRING");

	$aa .= "\nREMOTE_ADDR:$n$REMOTE_ADDR\n";
	$aa .= "SCRIPT:$SCRIPT_NAME\n";
	$aa .= "QUERY_STRING:$QUERY_STRING\n";

	$aa .= "\nREQUEST:\n";
	foreach($_REQUEST as $k => $v){
		$aa .= "$k = $v\n";
	}
	$aa .= "\nSESSION:\n";
	if($_SESSION){
	foreach($_SESSION as $k => $v){
		$aa .= "$k = $v\n";
	}
	}
	$aa .= "\n$sql";
	mail("sys-katsumata@yournet-jp.com","YUDAI_SYSTEM_ERROR","$aa");

	exit;
}
////////////////////////////////////////////////////////////////////// END function error
public function connect($connid="") {
	if(preg_match("/localhost/",$_SERVER["SERVER_NAME"])){
		$host = "localhost";
		$port = "5432";
		$user = "yournet";
		$dbname = "yudaidb";
		$pass = "1234";
	// switch($connid){
	// 	case 2:
	// 		$dbname = "survey";
	// 		break;
	// 	case 3:
	// 		$dbname = "slip";
	// 		break;
	// 	case 4:
	// 		$dbname = "aplus";
	// 		break;
	// 	default:
	// 		$dbname = "yudaidb";
	// 		break;
	// }
	// }else{
	// switch($connid){
	// 	case 2:
	// 		$host = "localhost";
	// 		$port = "5432";
	// 		$dbname = "yudaidb";
	// 		$user = "yournet";
	// 		$pass = "1234";
	// 		break;
	// 	case 3:
	// 		$host = "yournet-jp.com";
	// 		$port = "5432";
	// 		$dbname = "slip";
	// 		$user = "yournet";
	// 		break;
	// 	case 4:
	// 		$host = "yournet-jp.com";
	// 		$port = "5432";
	// 		$dbname = "aplus";
	// 		$user = "yournet";
	// 		break;
	// 	default:
	// 		$host = "yournet-jp.com";
	// 		$port = "5432";
	// 		$dbname = "yudai_admin";
	// 		$user = "yournet";
	// 		break;
	// }
	}
	// エラーレポートの設定
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	// 接続文字列の設定
	if (isset($pass)) {
		$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$pass options='--client_encoding=UTF8'";
	} else {
		$conn_string = "host=$host port=$port dbname=$dbname user=$user password=your_password options='--client_encoding=UTF8'";
	}
	

	// データベース接続
	$conn = pg_connect($conn_string);

	// 接続エラーチェック
	if (!$conn) {
		$msg = pg_last_error();
		if (!$msg) {
			$msg = "Unknown error occurred.";
		}
		echo "DB_connect_error: " . $msg;
		exit;
	}
	return $conn;
}
////////////////////////////////////////////////////////////////////// END function error
public function connect_nex($connid="") {
	$host = "localhost";
	$port = "5432";
	$dbname = "yudaidb";
	$user = "yournet";
	$pass = "1234";
	$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$pass");
	if(!$conn) {
		$msg = "DB_connect_error 笑い声が溢れる。ERROR_CODE:00001";
		$this->error($msg);
	}
	pg_query($conn,"SET CLIENT_ENCODING TO 'UTF-8'");
	return $conn;
}
////////////////////////////////////////////////////////////////////// END function connect

public $title; // $titleプロパティを追加
    public $my_employee_id; // $my_employee_idプロパティを追加
    public $SCRIPT; // $SCRIPTプロパティを追加

	public function sql($conn, $sql) {
		$result = @pg_query($conn, $sql);
		if (!$result) {
			$error = pg_last_error($conn);
			$msg = $this->SCRIPT . "\n" . $sql . "\n";
			$msg .= "SQL_execute_error 時間を置いてお試しください。ERROR_CODE:00002";
			if ($_SERVER['REMOTE_ADDR'] == '106.185.149.135') {
				$msg .= "<br>$sql<br>";
			}
			$msg .= "<br>Error: " . $error;
			$this->error($msg);
		}
		return $result;
	}
////////////////////////////////////////////////////////////////////// END function sql
public function session_get2() {

session_start();

$this->my_employee_id = $_SESSION['my_employee_id'];
$this->my_employee_num = $_SESSION['my_employee_num'];
$this->my_name = $_SESSION['my_name'];
$this->my_sex = $_SESSION['my_sex'];
$this->my_company_id = $_SESSION['my_company_id'];
$this->my_section_id = $_SESSION['my_section_id'];
$this->my_employee_type = $_SESSION['my_employee_type'];
$this->my_position_class = $_SESSION['my_position_class'];
$this->my_view_auth = $_SESSION['my_view_auth'];
$this->my_edit_auth = $_SESSION['my_edit_auth'];
$this->my_admin_auth = $_SESSION['my_admin_auth'];
$this->my_email = $_SESSION['my_email'];

if(!$this->my_employee_id){


header("Location: https://".$_SERVER['HTTP_HOST']."/yudaigroup/login.php");

//		$msg = "ログイン情報の有効期限が切れました。ログインし直してください。<br>";
//		$this->error("$msg");
}

	return;
}
////////////////////////////////////////////////////////////////////// END function session_get
public function session_get($sess_id="",$my_employee_id="") {
	$QUERY_STRING = getenv("QUERY_STRING");
	$SCRIPT_NAME = getenv("SCRIPT_NAME");

	if(isset($_COOKIE['sess'])){
		$sess_id = $_COOKIE['sess'];
	}elseif(!$sess_id){
header("Location: https://".$_SERVER['HTTP_HOST']."/yudaigroup/login.php?jump_uri={$SCRIPT_NAME}&{$QUERY_STRING}");
exit;
	}
	if(isset($_COOKIE[my_employee_id])){
		$my_employee_id = $_COOKIE[my_employee_id];
	}elseif(!$my_employee_id){
header("Location: https://".$_SERVER['HTTP_HOST']."/yudaigroup/login.php?jump_uri={$SCRIPT_NAME}&{$QUERY_STRING}");
exit;
	}
	$conn = $this->connect();
	$sql = "select employee_num,name,sex,company_id,section_id,employee_type,position_class,view_auth,edit_auth,admin_auth,email,limit_time from session where session_id = '$sess_id' and employee_id = $my_employee_id and limit_time >= current_timestamp";
	$result = $this->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num != 1){
//print $num;
header("Location: https://".$_SERVER['HTTP_HOST']."/yudaigroup/login.php?jump_uri={$SCRIPT_NAME}&{$QUERY_STRING}");
exit;
	}
	list($q_employee_num,$q_name,$q_sex,$q_company_id,$q_section_id,$q_employee_type,$q_position_class,$q_view_auth,$q_edit_auth,$q_admin_auth,$q_email,$q_limit_time) = pg_fetch_array($result,$i);


	$this->my_employee_id = $my_employee_id;
	$this->my_session_id = $sess_id;
	$this->my_employee_num = trim($q_employee_num);
	$this->my_name = trim($q_name);
	$this->my_sex = trim($q_sex);
	$this->my_company_id = trim($q_company_id);
	$this->my_section_id = trim($q_section_id);
	$this->my_employee_type = trim($q_employee_type);
	$this->my_position_class = trim($q_position_class);
	$this->my_view_auth = trim($q_view_auth);
	$this->my_edit_auth = trim($q_edit_auth);
	$this->my_admin_auth = trim($q_admin_auth);
	$this->my_email = trim($q_email);

	if(!isset($_COOKIE[sess])){
		setcookie("sess","$sess_id", time() + 8 * 3600, "/yudaigroup/");
	}
	if(!isset($_COOKIE[my_employee_id])){
		setcookie("my_employee_id","$my_employee_id", time() + 8 * 3600, "/yudaigroup/");
	}
	return $sess_id;
}
////////////////////////////////////////////////////////////////////// END function session_get
public function create_session($employee_id=0) {
	if(!$employee_id){
		$msg = "正しい情報が取得できません。ログインし直してください。<br>";
		$this->error("$msg");
	}
	if(!$this->my_company_id){
		$msg = "正しい情報が取得できません。ログインし直してください。<br>";
		$this->error("$msg");
	}
	if(!$this->my_employee_type){
		$employee_type = 'null';
	}else{
		$employee_type = $this->my_employee_type;
	}
	if(!$this->my_position_class){
		$position_class = 'null';
	}else{
		$position_class = $this->my_position_class;
	}
	if(!$this->my_admin_auth){
		$admin_auth = 'null';
	}else{
		$admin_auth = $this->my_admin_auth;
	}
	if(!$this->my_view_auth){
		$view_auth = 'null';
	}else{
		$view_auth = "'".$this->my_view_auth."'";
	}
	if(!$this->my_edit_auth){
		$edit_auth = 'null';
	}else{
		$edit_auth = "'".$this->my_edit_auth."'";
	}
	$orig_sess = $employee_id.time().rand(1000,9999);
	$sess = sha1($orig_sess);

	$conn = $this->connect();

	$sql = "delete from session where employee_id = $employee_id and limit_time < current_timestamp";
	$result = $this->sql($conn,$sql);

	$sql = "insert into session (session_id,employee_id,employee_num,name,sex,company_id,section_id,employee_type,position_class,view_auth,edit_auth,admin_auth,email,limit_time,add_date) values ('$sess',$employee_id,'{$this->my_employee_num}','{$this->my_name}','{$this->my_sex}','{$this->my_company_id}','{$this->my_section_id}',$employee_type,$position_class,$view_auth,$edit_auth,$admin_auth,'{$this->my_email}',current_timestamp + '8 hour','now')";
	$result = $this->sql($conn,$sql);

	if(!$result){
		$msg = "DBエラーです。ログインし直してください。<br>";
		$this->error("$msg");
	}

	setcookie("my_employee_id","$employee_id", time() + 8 * 3600, "/yudaigroup/");
	setcookie("sess","$sess", time() + 8 * 3600, "/yudaigroup/");

	return $sess;
}
////////////////////////////////////////////////////////////////////// END function create_session
public function mbscale($size=0) {
	$ua = $_SERVER['HTTP_USER_AGENT'];
	if(preg_match("/iPhone|Android/i",$ua)){
		switch($size){
			case 0:
			$rate = 60;
				break;
			default:
			$rate = $size * 10;
				break;
		}
	}else{
		$rate = 80;
	}
	return $rate;
}
////////////////////////////////////////////////////////////////////// END function mbscale
public function header_pri($item="") {

if(isset($_COOKIE['my_employee_id'])){
	$conn = $this->connect();
	$sql = "select status from top_dashboard where employee_id = {$_COOKIE['my_employee_id']} and status = '1'";
	$result = $this->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
		$JUMPSCRIPT = "portal/dashboard.php";
	}else{
		$JUMPSCRIPT = "portal/index.php";
	}
	$linkto = $JUMPSCRIPT;
}else{
	$linkto = "login.php";
}

if($item == 1){
	$linkto = "login.php";
	$item = "";
}

$PRI = <<<HTML
<header>
<nav class="navbar navbar-expand-md navbar-dark" style="background-color: #63baa8;">
<a class="btn btn-outline-light btn-sm" href="{$this->PATH}{$linkto}" role="button">TOPへ</a> 
　<a class="navbar-brand" href="{$this->PATH}{$linkto}">雄大グループ業務管理ポータル</a>　{$item}
</nav>
</header>

HTML;

	return $PRI;
}
////////////////////////////////////////////////////////////////////// END function header_pri
public function make_employee_list($status = "",$auth = "",$section_id = "") {
$conn = $this->connect();
$wflag=0;
if(preg_match("/^[0-9]+$/",$status)){
	$add_sql = " where status = '$status'";
	$wflag=1;
}else{
	$add_sql = "";
}
if(preg_match("/^[0-9]+$/",$section_id)){
	if($wflag){
		$add_sql .= " and";
	}else{
		$add_sql .= " where";
		$wflag=1;
	}
	$add_sql .= " section_id = '$section_id'";
}
if(preg_match("/^[0-9]+$/",$auth)){
	if($wflag){
		$add_sql .= " and";
	}else{
		$add_sql .= " where";
		$wflag=1;
	}
	$add_sql .= " admin_auth = $auth";
}
$sql = "select employee_id,employee_name from employee_list{$add_sql} order by section_id,employee_id";

$result = $this->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
$this->employee_name_list = array();
for($i=0;$i<$num;$i++){
	list($q_employee_id,$q_employee_name) = pg_fetch_array($result,$i);
	$this->employee_name_list[$q_employee_id] = $q_employee_name;
}
}
	return $this->employee_name_list;
}
////////////////////////////////////////////////////////////////////// END function make_employee_list
public function make_employee_group_list($status = "") {

$conn = $this->connect();
$wflag=0;
if(preg_match("/^[0-9]+$/",$status)){
	$add_sql = " where status = '$status'";
	$wflag=1;
}else{
	$add_sql = "";
}
$sql = "select employee_id,employee_name,section_id from employee_list{$add_sql} order by section_id,employee_id";

$result = $this->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
for($i=0;$i<$num;$i++){
	list($q_employee_id,$q_employee_name,$q_section_id) = pg_fetch_array($result,$i);
	$this->group_employee_name_list[$q_section_id][$q_employee_id] = $q_employee_name;
}
}
	return $this->group_employee_name_list;
}
////////////////////////////////////////////////////////////////////// END function make_employee_group_list
public function make_shop_list() {

$conn = $this->connect();

$sql = "select id,name from shop_list order by category,id";

$result = $this->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
for($i=0;$i<$num;$i++){
	list($q_id,$q_name) = pg_fetch_array($result,$i);
	$this->shop_list[$q_id] = $q_name;
}
if($this->shop_list[997]){
	$this->shop_list[115] = $this->shop_list[997];
	$this->shop_list[997] = "";
}
}
	return $this->shop_list;
}
////////////////////////////////////////////////////////////////////// END function make_shop_list
public function make_consult_receive_list() {

$conn = $this->connect();

$sql = "select consult_receive_id,add_date from consult_receive order by consult_receive_id";

$result = $this->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	list($q_consult_receive_id,$q_add_date) = pg_fetch_array($result,0);
	$this->consult_receive_employee_id = $q_consult_receive_id;
}
	return;
}

////////////////////////////////////////////////////////////////////// END make_consult_receive_list
public function make_common_category_list($status = "") {
$wflag=0;
if(preg_match("/^[0-9]+$/",$status)){
	$add_sql = " where status = '$status'";
	$wflag=1;
}else{
	$add_sql = "";
}
$conn = $this->connect();

$sql = "select category_id,category_name from common_category{$add_sql} order by category_id";

$result = $this->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
for($i=0;$i<$num;$i++){
	list($q_id,$q_name) = pg_fetch_array($result,$i);
	$namelist[$q_id] = $q_name;
}
}
	return $namelist;
}

////////////////////////////////////////////////////////////////////// END make_common_category_list
public function make_common_tab_list($status = "") {
$wflag=0;
if(preg_match("/^[0-9]+$/",$status)){
	$add_sql = " where status = '$status'";
	$wflag=1;
}else{
	$add_sql = "";
}
$conn = $this->connect();

$sql = "select tab_id,tab_name from common_tab{$add_sql} order by tab_id";

$result = $this->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
for($i=0;$i<$num;$i++){
	list($q_id,$q_name) = pg_fetch_array($result,$i);
	$namelist[$q_id] = $q_name;
}
}
	return $namelist;
}

////////////////////////////////////////////////////////////////////// END make_common_tab_list
public function get_section_id($employee_id = 0) {

$conn = $this->connect();
if(!$employee_id){
	return;
}
$sql = "select section_id from employee_list where employee_id = $employee_id and status = '1'";

$result = $this->sql($conn,$sql);
$num = pg_num_rows($result);
if($num != 1){
	return;
}
	list($q_section_id) = pg_fetch_array($result,0);
	return $q_section_id;
}

////////////////////////////////////////////////////////////////////// END make_common_tab_list
public function file_to_pdf($original_file="") {

	if(!$original_file){
		return false;
	}
	if(!file_exists($original_file)){
		return false;
	}
	$ext = substr($original_file,strrpos($original_file,'.') + 1);
	$ext = strtolower($ext);
	$motofilename = substr($original_file,0,strrpos($original_file,'.'));
	$changefilename = $motofilename.".pdf";
	$uploaddir = substr($original_file,0,strrpos($original_file,'/'));

	if($ext == "txt"){
		$current = file_get_contents($original_file);
		$current = mb_convert_encoding($current, "UTF-8",'ASCII,JIS,UTF-8,SJIS');
		file_put_contents($original_file, $current);
	}
	if(($ext == "doc")||($ext == "docx")||($ext == "xls")||($ext == "xlsx")||($ext == "txt")){
		exec("sudo /usr/bin/libreoffice --nolockcheck --headless --norestore --language=ja --nologo --nofirststartwizard --convert-to pdf --outdir {$uploaddir} {$original_file}");
		unlink($original_file);
	}elseif(($ext == "png")||($ext == "jpg")||($ext == "jpeg")||($ext == "gif")){
		exec("sudo convert -density 150 {$original_file} {$changefilename}");
		unlink($original_file);
	}else{
		return false;
	}

	return true;

}
////////////////////////////////////////////////////////////////////// END function file_to_pdf
public function make_yournet_employee_list($status = "") {

$conn_nex = $this->connect_nex();

$wflag=0;
if(preg_match("/^[0-9]+$/",$status)){
	$add_sql = " where status = '$status'";
	$wflag=1;
}else{
	$add_sql = "";
}
$sql = "select mem_id,name from member{$add_sql} order by status,mem_id";

$result = $this->sql($conn_nex,$sql);
$num = pg_num_rows($result);
$this->employee_name_list = array();
for($i=0;$i<$num;$i++){
	list($q_mem_id,$q_employee_name) = pg_fetch_array($result,$i);
	if(preg_match("/^([wm]{1})(20[0-9]{7})$/",$q_mem_id,$reg)){
		$q_employee_id = $reg[2];
		if($q_employee_id == '200008987'){
			$q_employee_id = 10005;
		}
		$this->employee_name_list[$q_employee_id] = $q_employee_name;
	}
}
	return $this->employee_name_list;
}
////////////////////////////////////////////////////////////////////// END function make_employee_list
public function make_now_section_list() {

	unset($this->section_list[100],$this->section_list[107],$this->section_list[201]);
	return $this->section_list;
}
////////////////////////////////////////////////////////////////////// END function make_employee_list
public function thumbnail_make($original_file="",$thumbnail_file="",$ext="",$uploaddir="",$filehead="",$THUMBNAIL_HEIGHT=0) {
					//$original_file=>元のファイル、フルパス
					//$thumbnail_file=>サムネイルのファイル、フルパス,pngに変更
					//$ext=>元のファイルの拡張子、doc,docx,xls,xlsx,png,jpg,jpeg,gif,pdf
					//$uploaddir=>doc,xls系は一旦PDFにする為のアップ先パス /なし
					//$filehead=>ファイル名拡張子無し

	if(!$THUMBNAIL_HEIGHT){
		$THUMBNAIL_HEIGHT = 200;	//サムネールの高さ設定
	}

	if(!$original_file || !$thumbnail_file || !$ext){
		return false;
	}
	if(!file_exists($original_file)){
		return false;
	}
	if(($ext == "doc")||($ext == "docx")||($ext == "xls")||($ext == "xlsx")){
	if($uploaddir && $filehead){
		$uploaddir = $uploaddir."/";
		$filepdf = $uploaddir.$filehead.".pdf";
		exec("sudo /usr/bin/libreoffice --nolockcheck --headless --norestore --language=ja --nologo --nofirststartwizard --convert-to pdf --outdir {$uploaddir} {$original_file}");
		$original_file = $filepdf;
		$ext = "pdf";

	}
	}
	switch ($ext){
		case "png":
		$original_image = imagecreatefrompng($original_file);
			break;
		case "jpg":
		case "jpeg":
		$original_image = imagecreatefromjpeg($original_file);
			break;
		case "gif":
		$original_image = imagecreatefromgif($original_file);
			break;
		case "pdf":
		$pdf_flg = 1;
			break;
		default:
		return false;
	}
	if($pdf_flg){//////////pdfファイル

	$imagick = new Imagick();
	$imagick->readImage($original_file . '[0]');
	$imagick->setImageFormat('png');
	$original_width = $imagick->getImageWidth();
	$original_height = $imagick->getImageHeight();
	$thumb_height = $THUMBNAIL_HEIGHT;
	$thumb_width = $original_width*$thumb_height/$original_height;
	$imagick->thumbnailImage($thumb_width,$thumb_height);
	$imagick->writeimage($thumbnail_file);

	return true;

	}else{	/////////////イメージ系ファイル
	list($original_width, $original_height) = getimagesize($original_file);


	$thumb_height = $THUMBNAIL_HEIGHT;
	$thumb_width = $original_width*$thumb_height/$original_height;

	$thumb_image = imagecreatetruecolor($thumb_width, $thumb_height);
	imagecopyresized($thumb_image, $original_image,0,0,0,0,$thumb_width,$thumb_height,$original_width, $original_height);

	imagepng($thumb_image,$thumbnail_file);

	imagedestroy($original_image);
	imagedestroy($thumb_image);

	return true;
	}
}
////////////////////////////////////////////////////////////////////// END function thumbnail_make


}
////////////////////////////////////////////////////////////////////// END class ybase
?>