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

$Rd = array(255,214,214,255,255,214,185,255,255,185,185,255);
$Gr = array(214,255,214,214,255,255,255,185,255,255,185,185);
$Bl = array(214,214,255,255,214,255,255,255,185,185,255,185);

// TCPDFインスタンスを作成
$orientation = 'Landscape'; // 用紙の向き
$unit = 'mm'; // 単位
$format = 'A4'; // 用紙フォーマット
$unicode = true; // ドキュメントテキストがUnicodeの場合にTRUEとする
$encoding = 'UTF-8'; // 文字コード
$diskcache = false; // ディスクキャッシュを使うかどうか
$tcpdf = new TCPDF($orientation, $unit, $format, $unicode, $encoding, $diskcache);
$tcpdf->SetMargins(10, 2, 10);
$tcpdf->SetAutoPageBreak(false,10);
$tcpdf->setPrintHeader(false);
$tcpdf->setPrintFooter(false);
$tcpdf->AddPage();
//$tcpdf->SetFont("kozminproregular", "", 9.2);
$tcpdf->SetFont("kozminproregular", "", 5.0);

$ybase = new ybase();
$rank = new rank();
//$ybase->session_get();

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
//$maxday = 30;
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
$item_all_cnt = 0;
$sql = "select bigitem_id,count(*) from telecom_item where {$addsql} group by bigitem_id order by bigitem_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("該当月がまだ設定されていません");
}
for($i=0;$i<$num;$i++){
	list($q_bigitem_id,$q_count) = pg_fetch_array($result,$i);
	$bigitem_cnt[$q_bigitem_id] = $q_count;
	$item_all_cnt += $q_count;
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
//$all_goal_score = number_format($all_goal_score);
//$all_act_score = number_format($all_act_score);


//////////////base setting////////////////
$hi = round(180 / $item_all_cnt,1);
if($hi > 3){
	$hi = 3.0;
}

//start x
$x0 = 10;
//start y
$y0 = 11;
//行高さ
//$hi = 3;
//幅親カテゴリ
$wcate0 = 18;
//幅カテゴリ
$wcate = 40;
//幅目標
$wgoal = 15;
//幅data
$wdata = 5.6;
//幅合計
$wtotal = 14;

//高さタイトル
$ytitle = 4;
//達成位置
$xachi = 0;
$yachi = 5;
//日付位置
$xdate = 240;
$ydate = 190;

///////////////////////////////////////////


$tcpdf->SetFont("kozminproregular", "", 9.0);
$tcpdf->MultiCell(0,10,"【{$rank_section_name[$t_shop_id]} {$YEAR}年{$MONTH}月】日別実績(全体)",0,'C',0,0,0,$ytitle,false,0,false);

//$tcpdf->SetDrawColor(255, 0, 0);
$text = "目標:<span style=\"color:red;\">{$all_goal_score}</span>点　
獲得:<span style=\"color:red;\">{$all_act_score}</span>点　
達成:<span style=\"color:red;\">{$reach_rate}</span>%";


$tcpdf->MultiCell(0,10,"$text",0,'R',0,0,$xachi,$ytitle,false,0,true);

$tcpdf->SetFont("msgothic01", "", 4.6);

$tcpdf->SetFillColor(224,224,224);

$hh = $hi * 2;
$tcpdf->MultiCell($wcate0,$hh,"",1,'C',1,0,$x0,$y0,true,0,false);
$xx = $x0 + $wcate0;
$tcpdf->MultiCell($wcate,$hh,"",1,'C',1,0,$xx,$y0,true,0,false);
$xx += $wcate;
$tcpdf->MultiCell($wgoal,$hh,"目標",1,'C',1,0,$xx,$y0,true,0,true);
$xx += $wgoal;
$tcpdf->MultiCell($wgoal,$hh,"目標まで<br>あと",1,'C',1,0,$xx,$y0,true,0,true);
$xx += $wgoal;

for($k=1;$k<=$maxday;$k++){
$youbi = $rank->make_yobi($t_month,$k);
if($k <> 1){$xx += $wdata;}
$tcpdf->MultiCell($wdata,$hh,"{$k}<br>{$youbi}",1,'C',1,0,$xx,$y0,true,0,true);

}
$xx += $wdata;
$tcpdf->MultiCell($wtotal,$hh,"合計",1,'C',1,0,$xx,$y0,true,0,false);

$tcpdf->SetFillColor(238,238,238);
$xx = $x0;
$yy = $y0 + $hi * 2;
$ww = $wcate0 + $wcate + $wgoal + $wgoal;
$tcpdf->MultiCell($ww,$hi,"ポイント計",1,'R',1,0,$xx,$yy,true,0,false);

///////////////////////////////////
$xx = $xx + $ww;
$all_total=0;
for($k=1;$k<=$maxday;$k++){
$all_total += $day_action_cnt[$k];
//$dtotal = number_format($day_action_cnt[$k]);
$dtotal = $day_action_cnt[$k];
if($k <> 1){$xx += $wdata;}
$tcpdf->MultiCell($wdata,$hi,"{$dtotal}",1,'R',1,0,$xx,$yy,true,0,false);
}
//$all_total = number_format($all_total);
$xx += $wdata;
$tcpdf->MultiCell($wtotal,$hi,"{$all_total}",1,'R',1,0,$xx,$yy,true,0,false);

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
$yy += $hi;
$xx = $x0;
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
$tcpdf->SetFillColor($Rd[$i],$Gr[$i],$Bl[$i]);

$hh = $hi * $bigitem_cnt[$q_bigitem_id];
$tcpdf->MultiCell($wcate0,$hh,"{$str}",1,'C',1,0,$x0,$yy,true,0,false);
}else{
}
$q_item_name = mb_convert_kana($q_item_name, "KVa",'UTF-8');

if($kk%2 == 0){
$tcpdf->SetFillColor(246,246,246);
}else{
$tcpdf->SetFillColor(255,255,255);
}

$xx += $wcate0;
$tcpdf->MultiCell($wcate,$hi,"{$q_item_name}",1,'C',1,0,$xx,$yy,true,0,false);

$xx += $wcate;
$tcpdf->MultiCell($wgoal,$hi,"{$goalnum_arr[$q_item_id]}{$rank->unitname_list[$q_item_id]}",1,'C',1,0,$xx,$yy,true,0,false);

$xx += $wgoal;
$tcpdf->MultiCell($wgoal,$hi,"{$to_goal}",1,'C',1,0,$xx,$yy,true,0,false);

$xx += $wgoal;

for($k=1;$k<=$maxday;$k++){
$total_pt[$k] += $actnum_arr[$q_item_id][$k] * $q_score;
if($k <> 1){$xx += $wdata;}
$tcpdf->MultiCell($wdata,$hi,"{$actnum_arr[$q_item_id][$k]}",1,'C',1,0,$xx,$yy,true,0,false);
}

//$i_cnt = number_format($item_action_cnt[$q_item_id]);
$i_cnt = $item_action_cnt[$q_item_id];
$xx += $wdata;
$tcpdf->MultiCell($wtotal,$hi,"{$i_cnt}",1,'R',1,0,$xx,$yy,true,0,false);

}
}

$tcpdf->SetFillColor(238,238,238);
$xx = $x0;
$yy += $hi;
$ww = $wcate0 + $wcate + $wgoal + $wgoal;
$tcpdf->MultiCell($ww,$hi,"ポイント計",1,'R',1,0,$xx,$yy,true,0,false);
///////////////////////////////////
$xx = $xx + $ww;
$all_total=0;
for($k=1;$k<=$maxday;$k++){
$all_total += $day_action_cnt[$k];
//$dtotal = number_format($day_action_cnt[$k]);
$dtotal = $day_action_cnt[$k];
if($k <> 1){$xx += $wdata;}
$tcpdf->MultiCell($wdata,$hi,"{$dtotal}",1,'R',1,0,$xx,$yy,true,0,false);
}
//$all_total = number_format($all_total);
$xx += $wdata;
$tcpdf->MultiCell($wtotal,$hi,"{$all_total}",1,'R',1,0,$xx,$yy,true,0,false);

///////////////////////////////////
$yy += $hi;

$tcpdf->SetFont("kozminproregular", "", 6);

$tcpdf->MultiCell(0,10,"{$nowtime}",0,'R',0,0,$xachi,$yy,false,0,true);

//$ybase->priout();
//exit;
//$tcpdf->writeHTML($ybase->ST_PRI);

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