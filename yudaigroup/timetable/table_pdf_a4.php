<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
require_once('../../TCPDF/tcpdf.php');
// TCPDFインスタンスを作成
$orientation = 'Landscape'; // 用紙の向き
$unit = 'mm'; // 単位
$format = 'A4'; // 用紙フォーマット
$unicode = true; // ドキュメントテキストがUnicodeの場合にTRUEとする
$encoding = 'UTF-8'; // 文字コード
$diskcache = false; // ディスクキャッシュを使うかどうか
$tcpdf = new TCPDF($orientation, $unit, $format, $unicode, $encoding, $diskcache);
$tcpdf->SetMargins(10, 4, 10);
$tcpdf->setPrintHeader(false);
$tcpdf->setPrintFooter(false);
$tcpdf->AddPage();
$tcpdf->SetFont("kozminproregular", "", 9.2);

$ybase = new ybase();
$ybase->session_get();
mb_internal_encoding("UTF-8");
////////////エラーチェック
if(!$STARTday){$STARTday=1;}
if(!$ENDday){$ENDday=10;}
$time_list = array("9:00","9:30","10:00","10:30","11:00","11:30","12:00","12:30","13:00","13:30","14:00","14:30","15:00","15:30","16:00","16:30","17:00","17:30","18:00","18:30","19:00","19:30","20:00","20:30","21:00");
$OFFCOLOR = "#cc9999";
$ONCOLOR = "#ffffff";

$ybase->title = "シフト表";

//$ybase->HTMLheader();
//$ybase->ST_PRI .= $ybase->header_pri("シフト表");

$error_flag = 0;
$space = array();

//////////////////////////////////////////////////

$uploaddir = '/home/yournet/yudai/com/';
if(!file_exists($uploaddir)){
	mkdir($uploaddir, 0775);
}
if(!$filename){
	$msg = "ファイルが特定できません。ERROR_CODE:10191";
	$ybase->error("$msg");
}

$fullname = $uploaddir.$filename;
$handle = fopen($fullname, "r");

$i=0;
while (($buffer[$i] = fgets($handle, 4096)) !== false) {
	$buffer[$i] = trim($buffer[$i]);
//	$buffer[$i] = mb_convert_encoding($buffer[$i],"UTF-8","auto");
	$i++;
}
fclose($handle);

$test="";
//////////////////////////////////////////////////
////事業所,姓,名,日付,曜日,祝日,シフト種類,シフト名称,出勤予定,退勤予定,休憩予定

////////////////
$item['shop_name'] = "事業所";
$item['f_name'] = '姓';
$item['g_name'] = '名';
$item['shift_date'] = '日付';
$item['shift_week'] = '曜日';
$item['holiday_f'] = '祝日';
$item['shift_type'] = 'シフト種類';
$item['shift_name'] = 'シフト名称';
$item['in_time'] = '出勤予定';
$item['out_time'] = '退勤予定';
$item['break_time'] = '休憩予定';

$koumoku = explode(",",$buffer[0]);
//print "<br>{$buffer[0]}<br>";
foreach($koumoku as $key => $val){
	$val = trim($val);
	foreach($item as $key2 => $val2){
		$val2 = trim($val2);
		if("$val" == "$val2"){
//		print "$val,$val2,$key2,$key<br>";
			$space[$key2] = "$key";
		}

	}
}
$space['shop_name'] = 0;

$shop_key = $space['shop_name'];
$fname_key = $space['f_name'];
$gname_key = $space['g_name'];
$date_key = $space['shift_date'];
$week_key = $space['shift_week'];
$holiday_key = $space['holiday_f'];
$type_key = $space['shift_type'];
$shift_key = $space['shift_name'];
$in_key = $space['in_time'];
$out_key = $space['out_time'];
$break_key = $space['break_time'];

$datack = explode(",",$buffer[1]);
$taget_shop_name = $datack[$shop_key];
$taget_date = $datack[$date_key];
if(preg_match("/([0-9]{4})\/([0-9]{1,2})\/([0-9]{1,2})/",$taget_date,$str)){
	$taget_YY = $str[1];
	$taget_MM = $str[2];
}else{
	$msg = "データ形式に問題があります。[{$taget_date}]ERROR_CODE:10991";
	$ybase->error("$msg");
}


$emp_name_list = array();
$i=0;
foreach($buffer as $key => $val){
	if($key == 0){continue;}
	$data = explode(",",$val);
	$empname = $data[$fname_key].$data[$gname_key];
	$empname = trim($empname);
	if(!$empname){continue;}
	$emp_key = array_search($empname, $emp_name_list);
	if($emp_key === false){
		array_push($emp_name_list,$empname);
		$emp_key = array_search($empname, $emp_name_list);
	}
	if(preg_match("/{$taget_YY}\/{$taget_MM}\/([0-9]{1,2})/",$data[$date_key],$str)){
		$taget_day = $str[1];
//		print "$taget_day<br>";
	}else{
		continue;
	}
	$week_list[$taget_day] = $data[$week_key];
	$holiday_list[$taget_day] = $data[$holiday_key];
	$fname_list[$emp_key] = $data[$fname_key];
	$gname_list[$emp_key] = $data[$gname_key];
	$type_list[$taget_day][$emp_key] = $data[$type_key];
	$shift_list[$taget_day][$emp_key] = $data[$shift_key];
	$in_list[$taget_day][$emp_key] = $data[$in_key];
	$out_list[$taget_day][$emp_key] = $data[$out_key];
	$break_list[$taget_day][$emp_key] = $data[$break_key];
}

$ybase->ST_PRI .= <<<HTML
HTML;
foreach($week_list as $dkey => $dval){
if($STARTday > $dkey){continue;}
if($ENDday < $dkey){continue;}

$ybase->HTMLheader();

$ybase->ST_PRI .= <<<HTML
<style type="text/css">
.td-topdel { 
	border-style:none; 
} 
</style>
<h5 class="text-center">{$taget_shop_name}シフト表【{$taget_YY}年{$taget_MM}月{$dkey}日】</h5>
<table border="1" align="center" style="font-size:70%;" cellpadding="0.5">
<tr align="center">
<th rowspan="2">
時間
</th>
HTML;
foreach($emp_name_list as $key => $val){
if(strlen($fname_list[$key]) < 8){
	$d_name = $fname_list[$key].mb_substr($gname_list[$key],0,1);
}else{
	$d_name = $fname_list[$key];
}

$ybase->ST_PRI .= <<<HTML
<th width="30">
{$d_name}
</th>
HTML;

}
$ybase->ST_PRI .= <<<HTML
</tr>
<tr align="center">
HTML;
foreach($emp_name_list as $key => $val){
$ybase->ST_PRI .= <<<HTML
<td>
{$shift_list[$dkey][$key]}
</td>
HTML;

}
$ybase->ST_PRI .= <<<HTML
</tr>
HTML;

foreach($time_list as $tkey => $tval){
if($tkey == 0){
	$addtd = "<td class=\"td-topdel\" height=\"5\"></td>";
}else{
	$addtd = "";
}
$ybase->ST_PRI .= <<<HTML
<tr>
$addtd
HTML;
foreach($emp_name_list as $key => $val){
if(preg_match("/([0-9]{1,2}):([0-9]{2})/",$tval,$str)){
	$thh=$str[1];
	$tmi=$str[2];
}else{
	$thh="";
	$tmi="";
}
if(preg_match("/([0-9]{1,2}):([0-9]{2})/",$in_list[$dkey][$key],$str)){
	$inhh=$str[1];
	$inmi=$str[2];
}else{
	$inhh="";
	$inmi="";
}
if(preg_match("/([0-9]{1,2}):([0-9]{2})/",$out_list[$dkey][$key],$str)){
	$outhh=$str[1];
	$outmi=$str[2];
}else{
	$outhh="";
	$outmi="";
}
if($thh == "" || $inhh == ""){
	$bgcol = $ONCOLOR;
}elseif($thh < $inhh){
	$bgcol = $OFFCOLOR;
}elseif($thh > $inhh){
	$bgcol = $ONCOLOR;
}elseif($thh == $inhh){
	if($tmi <= $inmi){
		$bgcol = $OFFCOLOR;
	}else{
		$bgcol = $ONCOLOR;
	}
}
if($bgcol == $ONCOLOR){
if($thh == "" || $outhh == ""){
	$bgcol = $ONCOLOR;
}elseif($thh > $outhh){
	$bgcol = $OFFCOLOR;
}elseif($thh < $outhh){
	$bgcol = $ONCOLOR;
}elseif($thh == $outhh){
	if($tmi <= $outmi){
		$bgcol = $ONCOLOR;
	}else{
		$bgcol = $OFFCOLOR;
	}
}
}
if(!$in_list[$dkey][$key]){
	$bgcol = $OFFCOLOR;
}
$ybase->ST_PRI .= <<<HTML
<td rowspan="2" bgcolor="$bgcol">　
</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
<tr align="right">
<td rowspan="2" valign="middle" style="border-style:none;border-top: 0px dotted white;border-left: 1px solid #000000;">
<span style="text-align:right;padding: 10px 5px 20px 15px;margin: 10px 20px 5px 15px;">$tval</span>
</td>
</tr>
HTML;

}
$ybase->ST_PRI .= <<<HTML
<tr height="5">
HTML;
foreach($emp_name_list as $key => $val){
if(preg_match("/([0-9]{1,2}):([0-9]{2})/",$out_list[$dkey][$key],$str)){
	$outhh=$str[1];
	$outmi=$str[2];
}else{
	$outhh="";
	$outmi="";
}

if($thh == "" || $outhh == ""){
	$bgcol = $ONCOLOR;
}elseif($thh > $outhh){
	$bgcol = $OFFCOLOR;
}elseif($thh < $outhh){
	$bgcol = $ONCOLOR;
}elseif($thh == $outhh){
	if($tmi < $outmi){
		$bgcol = $ONCOLOR;
	}else{
		$bgcol = $OFFCOLOR;
	}
}
if(!$in_list[$dkey][$key]){
	$bgcol = $OFFCOLOR;
}
$ybase->ST_PRI .= <<<HTML
<td bgcolor="$bgcol">
</td>
HTML;
}


$ybase->ST_PRI .= <<<HTML
</tr>
</table>
</body></html>
HTML;
$tcpdf->writeHTML($ybase->ST_PRI);
$ybase->ST_PRI = "";
}

$ybase->ST_PRI .= <<<HTML

HTML;


$fileName = 'shift_'."$taget_YY$taget_MM".'.pdf';
$tcpdf->Output("$fileName");
$pdfData = $tcpdf->Output(rawurlencode($fileName), 'S');

// ブラウザにそのまま表示
header('Content-Type: application/pdf');
header("Content-Disposition: inline; filename*=UTF-8''".rawurlencode($fileName));
echo $pdfData;



//$ybase->priout();

////////////////////////////////////////////////
?>