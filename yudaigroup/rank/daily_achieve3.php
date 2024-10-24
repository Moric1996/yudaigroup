<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/rank.inc');
include('./inc/rank_list.inc');

require_once('../../TCPDF/tcpdf.php');

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

$moto = array(
"ァ","ア","ィ","イ","ゥ","ウ","ェ","エ","ォ","オ","カ","ガ","キ","ギ","ク",
"グ","ケ","ゲ","コ","ゴ","サ","ザ","シ","ジ","ス","ズ","セ","ゼ","ソ","ゾ","タ",
"ダ","チ","ヂ","ッ","ツ","ヅ","テ","デ","ト","ド","ナ","ニ","ヌ","ネ","ノ","ハ",
"バ","パ","ヒ","ビ","ピ","フ","ブ","プ","ヘ","ベ","ペ","ホ","ボ","ポ","マ","ミ",
"ム","メ","モ","ャ","ヤ","ュ","ユ","ョ","ヨ","ラ","リ","ル","レ","ロ","ヮ","ワ",
"ヰ","ヱ","ヲ","ン","ヴ","ヵ","ヶ","ー"
);
$saki = array(
"&#x30A1;","&#x30A2;","&#x30A3;","&#x30A4;","&#x30A5;","&#x30A6;","&#x30A7;","&#x30A8;","&#x30A9;","&#x30AA;","&#x30AB;","&#x30AC;","&#x30AD;","&#x30AE;","&#x30AF;",
"&#x30B0;","&#x30B1;","&#x30B2;","&#x30B3;","&#x30B4;","&#x30B5;","&#x30B6;","&#x30B7;","&#x30B8;","&#x30B9;","&#x30BA;","&#x30BB;","&#x30BC;","&#x30BD;","&#x30BE;","&#x30BF;",
"&#x30C0;","&#x30C1;","&#x30C2;","&#x30C3;","&#x30C4;","&#x30C5;","&#x30C6;","&#x30C7;","&#x30C8;","&#x30C9;","&#x30CA;","&#x30CB;","&#x30CC;","&#x30CD;","&#x30CE;","&#x30CF;",
"&#x30D0;","&#x30D1;","&#x30D2;","&#x30D3;","&#x30D4;","&#x30D5;","&#x30D6;","&#x30D7;","&#x30D8;","&#x30D9;","&#x30DA;","&#x30DB;","&#x30DC;","&#x30DD;","&#x30DE;","&#x30DF;",
"&#x30E0;","&#x30E1;","&#x30E2;","&#x30E3;","&#x30E4;","&#x30E5;","&#x30E6;","&#x30E7;","&#x30E8;","&#x30E9;","&#x30EA;","&#x30EB;","&#x30EC;","&#x30ED;","&#x30EE;","&#x30EF;",
"&#x30F0;","&#x30F1;","&#x30F2;","&#x30F3;","&#x30F4;","&#x30F5;","&#x30F6;","&#x30FC;"
);

// TCPDFインスタンスを作成
$orientation = 'Landscape'; // 用紙の向き
$unit = 'mm'; // 単位
$format = 'B4'; // 用紙フォーマット
$unicode = true; // ドキュメントテキストがUnicodeの場合にTRUEとする
$encoding = 'UTF-8'; // 文字コード
$diskcache = false; // ディスクキャッシュを使うかどうか
$tcpdf = new TCPDF($orientation, $unit, $format, $unicode, $encoding, $diskcache);
$tcpdf->SetMargins(10, 2, 10);
$tcpdf->setPrintHeader(false);
$tcpdf->setPrintFooter(false);
$tcpdf->AddPage();
//$tcpdf->SetFont("kozminproregular", "", 9.2);
$tcpdf->SetFont("kozminproregular", "", 5.0);

$ybase = new ybase();
$rank = new rank();
//$ybase->session_get();
$tablerate = $ybase->mbscale(5);

if(!preg_match("/^[0-9]+$/",$t_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20001");
}
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20002");
}
$rank->unitname_make($t_shop_id,$t_month);

$YEAR = substr($t_month,0,4);
$MONTH = intval(substr($t_month,4,2));
$maxday = date("t",mktime(0,0,0,$MONTH,1,$YEAR));
if($maxday > 30){
	$maxday = 30;
}
$nowtime = date("Y/n/j H:i現在");
/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$param = "t_month=$t_month&t_shop_id=$t_shop_id";
$addsql = "month = $t_month and shop_id = $t_shop_id and status = '1'";
//////////////////////////////////////////
/////////////////////////////////////////

$sql = "select bigitem_id,count(*) from telecom_item where {$addsql} group by bigitem_id order by bigitem_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当月がまだ設定されていません");
}
for($i=0;$i<$num;$i++){
	list($q_bigitem_id,$q_count) = pg_fetch_array($result,$i);
	$bigitem_cnt[$q_bigitem_id] = $q_count;
}

$sql = "select bigitem_id,bigitem_name from telecom_bigitem where {$addsql} order by order_num";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当月がまだ設定されていません");
}

$ybase->title = "Y☆Rank-日別実績(全体)";

//$ybase->HTMLheader();
//$ybase->ST_PRI .= $ybase->header_pri("日別実績(全体)");
//itemscore
$sql = "select item_id,score from telecom_item where {$addsql} order by order_num";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_score) = pg_fetch_array($result3,$i);
	$item_score[$q_itemid] = $q_score;
}
$all_act_score = 0;
$all_goal_score = 0;

//目標
$sql = "select item_id,sum(goal_num) from telecom_goal_group where {$addsql} group by item_id order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_goal_num) = pg_fetch_array($result3,$i);
	$goalnum_arr[$q_itemid] = $q_goal_num;
	$all_goal_score += $q_goal_num * $item_score[$q_itemid];
}

//当日実績個人
$item_action_cnt = array();
$day_action_cnt = array();
$sql = "select item_id,day,sum(action_num) from telecom_action where {$addsql} group by item_id,day order by item_id,day";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_day,$q_action_num) = pg_fetch_array($result3,$i);
	if($q_action_num == 0){
		$q_action_num = "";
	}
	$actnum_arr[$q_itemid][$q_day] = $q_action_num;
	$item_action_cnt[$q_itemid] += $q_action_num;
	$day_action_cnt[$q_day] += $q_action_num * $item_score[$q_itemid];
	$all_act_score += $q_action_num * $item_score[$q_itemid];
}
if($all_goal_score){
	$reach_rate = round(($all_act_score / $all_goal_score) * 100,1);
}else{
	$reach_rate = 0.0;
}
$all_goal_score = number_format($all_goal_score);
$all_act_score = number_format($all_act_score);


$ybase->ST_PRI .= <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Y☆Rank-日別実績(全体)</title>
</head>
<body>
<h3 style="text-align:center;">【{$rank_section_name[$t_shop_id]} {$YEAR}年{$MONTH}月】日別実績(全体)</h3>

<div style="text-align:right;">
目標:<span style="color:red;font-size:150%;">{$all_goal_score}</span>点　
獲得:<span style="color:red;font-size:150%;">{$all_act_score}</span>点　
達成:<span style="color:red;font-size:150%;">{$reach_rate}</span>%
</div>
<table border="1" cellpadding="0.5" width="4000">
<tr bgcolor="#cccccc" align="center">
<th rowspan="2" width="50"></th>
<th rowspan="2"></th>
<th rowspan="2" width="35">目標</th>
<th rowspan="2" width="40">目標<br>
まで<br>あと</th>
HTML;
for($k=1;$k<=$maxday;$k++){
$ybase->ST_PRI .= <<<HTML
<th width="20">{$k}<br>日</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
<th rowspan="2" width="50">合計</th>
</tr>
<tr bgcolor="#d5d5d5" align="center">
HTML;
for($k=1;$k<=$maxday;$k++){
$youbi = $rank->make_yobi($t_month,$k);
$ybase->ST_PRI .= <<<HTML
<th>$youbi</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
HTML;
///////////////////////////////////
$ybase->ST_PRI .= <<<HTML
<tr align="right" bgcolor="#eeeeee">
<td colspan="4">ポイント計</td>
HTML;
$all_total=0;
for($k=1;$k<=$maxday;$k++){
$all_total += $day_action_cnt[$k];
$dtotal = number_format($day_action_cnt[$k]);
$ybase->ST_PRI .= <<<HTML
<td>$dtotal</td>
HTML;
}
$all_total = number_format($all_total);
$ybase->ST_PRI .= <<<HTML
<td>$all_total</td>
</tr>
HTML;

///////////////////////////////////
$aaa="";
$kk=0;
$addrowscnt=0;
$commentout = array();
$total_pt=array();
for($i=0;$i<$num;$i++){
	list($q_bigitem_id,$q_bigitem_name) = pg_fetch_array($result,$i);

$sql = "select item_id,item_name,score from telecom_item where {$addsql} and bigitem_id = $q_bigitem_id order by order_num";

$result2 = $ybase->sql($conn,$sql);
$num2 = pg_num_rows($result2);
if(!$num2){
	$ybase->error("該当月がまだ設定されていません");
}
for($ii=0;$ii<$num2;$ii++){
$kk++;
if($kk%2 == 0){
	$hbgcolor = "#eaeaea";
}else{
	$hbgcolor = "#ffffff";
}
	list($q_item_id,$q_item_name,$q_score) = pg_fetch_array($result2,$ii);
	$to_goal = $goalnum_arr[$q_item_id] - $item_action_cnt[$q_item_id];
if($ii == 0){
	$charlist = mb_str_split($q_bigitem_name);
	$str="";
	$bigitemcharnum=0;
	foreach($charlist as $key => $val){
		if($key != 0){
			$str .= "<br>";
		}
		if($val == 'ー'){
			$val = "｜";
		}
			$val = mb_convert_kana($val,"KVA","UTF-8");
			$str .= $val;
	$bigitemcharnum++;
	}
$addrows = $bigitemcharnum - $bigitem_cnt[$q_bigitem_id];
if($addrows > 0){
	$addrowscnt++;
	$commentout[$addrowscnt] = "※".$addrowscnt.":".$q_bigitem_name;
	$str = "※".$addrowscnt;
}
$str = $q_bigitem_name;
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="$hbgcolor">
<td rowspan="{$bigitem_cnt[$q_bigitem_id]}" style="vertical-align: middle;" bgcolor="{$bcolor[$i]}" width="50">
$str
</td>
HTML;
}else{
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="$hbgcolor">
HTML;
}
$q_item_name = mb_convert_kana($q_item_name, "KVa",'UTF-8');
if(strlen($q_item_name) < 30){
	$rowheight = " height=\"9\"";
}else{
	$rowheight = "";
//	$q_item_name = preg_replace('/[^ぁ-んァ-ンーa-zA-Z0-9一-龠０-９\-\r]+/u','' ,$q_item_name);
//	$q_item_name = preg_replace('/[^ぁ-んーa-zA-Z0-9一-龠０-９\-\r\(\)\+]+/u','' ,$q_item_name);
//	$q_item_name = preg_replace('/[ァ-ン]+/u','' ,$q_item_name);
//$q_item_name = str_replace($moto, $saki, $q_item_name);
//$q_item_name = mb_substr($q_item_name,0,12);
/*
	if(($i == 1) && ($ii == 3)){
	$aaa.= "<br>".$q_item_name;
	$q_item_name = '';
	}elseif(($i == 1) && ($ii == 4)){
	$aaa.= "<br>".$q_item_name;
	$q_item_name = '';
	}elseif(($i == 1) && ($ii == 19)){
	$aaa.= "<br>".$q_item_name;
	$q_item_name = '';
	}elseif(($i == 1) && ($ii == 20)){
	$aaa.= "<br>".$q_item_name;
	$q_item_name = '';
	}elseif(($i == 1) && ($ii == 21)){
	$aaa.= "<br>".$q_item_name;
	$q_item_name = '';
	}elseif(($i == 1) && ($ii == 23)){
	$aaa.= "<br>".$q_item_name;
	$q_item_name = '';
	}elseif(($i == 2) && ($ii == 3)){
	$aaa.= "<br>".$q_item_name;
	$q_item_name = '';
	}elseif(($i == 2) && ($ii == 4)){
	$aaa.= "<br>".$q_item_name;
	$q_item_name = '';
	}elseif(($i == 2) && ($ii == 11)){
	$aaa.= "<br>".$q_item_name;
	$q_item_name = '';
	}elseif(($i == 3) && ($ii == 17)){
	$aaa.= "<br>".$q_item_name;
	$q_item_name = '';
	}
*/
}


$ybase->ST_PRI .= <<<HTML
<td align="center">
{$q_item_name}
</td>
<td align="right" width="35">{$goalnum_arr[$q_item_id]}{$rank->unitname_list[$q_item_id]}</td>
<td align="right">{$to_goal}</td>
HTML;
for($k=1;$k<=$maxday;$k++){
$total_pt[$k] += $actnum_arr[$q_item_id][$k] * $q_score;
$ybase->ST_PRI .= <<<HTML
<td align="right" width="20">{$actnum_arr[$q_item_id][$k]}</td>
HTML;
}
$i_cnt = number_format($item_action_cnt[$q_item_id]);
$ybase->ST_PRI .= <<<HTML
<td align="right" bgcolor="#cccccc">{$i_cnt}</td>
</tr>
HTML;

}


}

$ybase->ST_PRI .= <<<HTML
<tr align="right" bgcolor="#dddddd">
<td colspan="4">ポイント計</td>
HTML;
$all_total=0;
for($k=1;$k<=$maxday;$k++){
$all_total += $total_pt[$k];
$dtotal = number_format($total_pt[$k]);
$ybase->ST_PRI .= <<<HTML
<td>$dtotal</td>
HTML;
}
$all_total = number_format($all_total);
$colspan = $maxday + 5;
$addstr = "";
foreach($commentout as $key => $val){
//	$addstr .= $val;
}
$ybase->ST_PRI .= <<<HTML
<td>$all_total</td>
</tr>
<tr>
<td colspan="$colspan"><div style="text-align:right;">{$addstr} {$nowtime}</div>
</td>
</tr>
</table>
$aaa
HTML;

$ybase->ST_PRI .= <<<HTML
</body></html>
HTML;

//$ybase->priout();
//exit;
$tcpdf->writeHTML($ybase->ST_PRI);

$fileName = 'report'.date("Ymd").'.pdf';
$tcpdf->Output("$fileName");
$pdfData = $tcpdf->Output(rawurlencode($fileName), 'S');

// ブラウザにそのまま表示
header('Content-Type: application/pdf');
header("Content-Disposition: inline; filename*=UTF-8''".rawurlencode($fileName));
echo $pdfData;

//$ybase->priout();
////////////////////////////////////////////////
?>