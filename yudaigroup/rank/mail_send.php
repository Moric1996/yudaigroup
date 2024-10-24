<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/rank_list.inc');

$ybase = new ybase();
$ybase->session_get();
$title = trim($title);
$mailbody = trim($mailbody);

if(!preg_match("/^[0-9]+$/",$t_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20001");
}
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20002");
}
if(!$tomail[1]){
	$ybase->error("送信先を入力してください");
}
if(!$title){
	$ybase->error("タイトルを入力してください");
}
if(!$mailbody){
	$ybase->error("本文を入力してください");
}
if(!$frommail){
	$ybase->error("送信元メールアドレスを入力してください");
}
if(!in_array("1", $s_type)){
	$ybase->error("必ずToの送信先を１つは入れてください");
}

if($t_shop_id == 302){
	$pdffilename = "daily_achieve4.php";
}else{
	$pdffilename = "daily_achieve4.php";
}


$target_month = $t_month;
$YEAR = substr($target_month,0,4);
$MONTH = intval(substr($target_month,4,2));

if(!$target_day){
	$nowday = date("j");
	$target_day = date("j",mktime(0,0,0,$MONTH,$nowday,$YEAR));
	$target_month = date("Ym",mktime(0,0,0,$MONTH,$nowday,$YEAR));
}else{
	$target_day = date("j",mktime(0,0,0,$MONTH,$target_day,$YEAR));
	$target_month = date("Ym",mktime(0,0,0,$MONTH,$target_day,$YEAR));
}
$YEAR = substr($target_month,0,4);
$MONTH = intval(substr($target_month,4,2));
$maxday = date("t",mktime(0,0,0,$MONTH,$target_day,$YEAR));
$nissin = round($target_day/$maxday*100);


$param = "t_month=$t_month&t_shop_id=$t_shop_id&target_num=$target_num";
/////////////////////////////////////////

$conn = $ybase->connect();

//////////////////////////////////////////条件
$maillistto="";
$maillistcc="";
$maillistbcc="";
foreach($s_type as $key => $val){
$temp_email=trim($tomail[$key]);
switch($val){
	case 1:
		if($temp_email){
			if($maillistto){
				$maillistto .= ",";
			}
				$maillistto .= $temp_email;
		}
	break;
	case 2:
		if($temp_email){
			if($maillistcc){
				$maillistcc .= ",";
			}
				$maillistcc .= $temp_email;
		}
	break;
	case 3:
		if($temp_email){
			if($maillistbcc){
				$maillistbcc .= ",";
			}
				$maillistbcc .= $temp_email;
		}
	break;
}
}

if(!$maillistto){
	$ybase->error("送信先を入力してください");
}else{
	$addsqlto = "{"."$maillistto"."}";
}
if($maillistcc){
	$addheadcc = "Cc: $maillistcc\n";
	$addsqlcc = "{"."$maillistcc"."}";
}else{
	$addheadcc = "";
	$addsqlcc = "{}";
}
if($maillistbcc){
	$addheadbcc = "Bcc: $maillistbcc\n";
	$addsqlbcc = "{"."$maillistbcc"."}";
}else{
	$addheadbcc = "";
	$addsqlbcc = "{}";
}


$fileurl = "https://yournet-jp.com/yudaigroup/rank/{$pdffilename}?t_month=$t_month&t_shop_id=$t_shop_id";
$filename = "report_".$t_shop_id.$t_month.date('d').".pdf";
$filebody = file_get_contents("$fileurl");

mb_language("ja");
mb_internal_encoding('utf-8');
$mime_type = "application/octet-stream";



$boundary = '----=_Boundary_' . uniqid(rand(1000,9999) . '_') . '_';
$head  = "From: $frommail\n";
$head .= $addheadcc;
if($addheadbcc){
	$head .= $addheadbcc;
}
$head .= "MIME-Version: 1.0\n";
$head .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\n";
$head .= "Content-Transfer-Encoding: 7bit";

// 本文
$body .= "--{$boundary}\n";
$body .= "Content-Type: text/plain; charset=ISO-2022-JP;" .
    "Content-Transfer-Encoding: 7bit\n";
$body .= "\n";
$body .= "{$mailbody}\n";
$body .= "\n";
// 添付ファイル
$filename = mb_convert_encoding($filename, 'ISO-2022-JP');
$filename = "=?ISO-2022-JP?B?" . base64_encode($filename) . "?=";
$body .= "--{$boundary}\n";
$body .= "Content-Type: {$mime_type}; name=\"{$filename}\"\n" .
    "Content-Transfer-Encoding: base64\n" .
    "Content-Disposition: attachment; filename=\"{$filename}\"\n\n";
$f_encoded = chunk_split(base64_encode($filebody));
$body .= $f_encoded . "\n\n";

if (!mb_send_mail($maillistto, $title, $body, $head)) {
    error_log('mb_send_mail fail');
}

$mailbody = addslashes($mailbody);
$title = addslashes($title);


$sql = "insert into telecom_mail_log  (mail_id,shop_id,employee_id,to_ls,cc_ls,bcc_ls,from1,title,message,add_date,status) values (nextval('telecom_mail_log_id_seq'),$t_shop_id,$ybase->my_employee_id,'$addsqlto','$addsqlcc','$addsqlbcc','$frommail','$title','$mailbody','now','1')";
$result = $ybase->sql($conn,$sql);


$ybase->title = "Y☆Rank-メール送信";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("メール送信");
$ybase->ST_PRI .= <<<HTML
<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./rank_top.php?$param" role="button">Y☆RankTOPに戻る</a></div>
<p></p>
<h5 style="text-align:center;">【{$rank_section_name[$t_shop_id]} {$YEAR}年{$MONTH}月】メール送信管理</h5>

<p></p>
HTML;

$ybase->ST_PRI .= <<<HTML
<div style="text-align:center;">
メール送信完了<br>
<p></p>
<a class="btn btn-info btn-sm" href="./mail_reg.php?$param" role="button">戻る</a></div>
<p></p>
</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();


////////////////////////////////////////////////
?>