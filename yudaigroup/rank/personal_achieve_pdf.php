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

// TCPDFインスタンスを作成
$orientation = 'PORTRAIT'; // 用紙の向き
$unit = 'mm'; // 単位
$format = 'A4'; // 用紙フォーマット
$unicode = true; // ドキュメントテキストがUnicodeの場合にTRUEとする
$encoding = 'UTF-8'; // 文字コード
$diskcache = false; // ディスクキャッシュを使うかどうか
$tcpdf = new TCPDF($orientation, $unit, $format, $unicode, $encoding, $diskcache);
$tcpdf->SetMargins(10, 2, 10);
$tcpdf->setPrintHeader(false);
$tcpdf->setPrintFooter(false);
$tcpdf->AddPage();
$tcpdf->SetFont("kozgopromedium", "", 8);

$ybase = new ybase();
$rank = new rank();
$ybase->session_get();
$tablerate = $ybase->mbscale(6);

if(!preg_match("/^[0-9]+$/",$t_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20001");
}
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20002");
}

if(!$target_employee_id){
	$target_employee_id = $ybase->my_employee_id;
}

if(!preg_match("/^[0-9]+$/",$target_employee_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20002");
}

$YEAR = substr($t_month,0,4);
$MONTH = intval(substr($t_month,4,2));
$maxday = date("t",mktime(0,0,0,$MONTH,1,$YEAR));


$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;

$rank->group_const_make($t_shop_id,$t_month);
if(!array_key_exists($target_employee_id,$rank->group_const_list)) {
	$aarr = array_keys($rank->group_const_list);
	$target_employee_id = $aarr[0];
}

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

$ybase->title = "Y☆Rank-日次実績(個人)";

$ybase->HTMLheader();
//$ybase->ST_PRI .= $ybase->header_pri("日次実績(個人)");
//itemscore
$sql = "select item_id,score from telecom_item where {$addsql} order by order_num";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_score) = pg_fetch_array($result3,$i);
	$item_score[$q_itemid] = $q_score;
}
$all_goal_score = 0;
//個人目標
$sql = "select item_id,goal_num from telecom_goal where {$addsql} and employee_id = $target_employee_id order by item_id";
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
$all_total=0;
$sql = "select item_id,day,action_num from telecom_action where {$addsql} and employee_id = $target_employee_id order by item_id,day";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_itemid,$q_day,$q_action_num) = pg_fetch_array($result3,$i);
	if($q_action_num == 0){
		$q_action_num = "";
	}
	$per_actnum_arr[$q_itemid][$q_day] = $q_action_num;
	$item_action_cnt[$q_itemid] += $q_action_num;
	$day_action_cnt[$q_day] += $q_action_num * $item_score[$q_itemid];
	$all_total += $q_action_num * $item_score[$q_itemid];

}
if($all_goal_score){
	$reach_rate = round(($all_total / $all_goal_score) * 100,1);
}else{
	$reach_rate = 0.0;
}
$all_goal_score = number_format($all_goal_score);
$all_total = number_format($all_total);

$ybase->ST_PRI .= <<<HTML

<div class="container-fluid">
<h4 style="text-align:center;">【{$rank_section_name[$t_shop_id]} {$YEAR}年{$MONTH}月】日次実績(個人)</h4>

<p></p>
<table border="0" width="100%"><tr><td width="170">

HTML;
$ybase->ST_PRI .= <<<HTML
<b> {$sec_employee_list[$target_employee_id]}</b>
HTML;
$ybase->ST_PRI .= <<<HTML

</td><td>
</td><td><div style="text-align:right;">
<nobr>目標:<span style="color:red;font-size:120%;">{$all_goal_score}</span>点</nobr><nobr>　獲得:<span style="color:red;font-size:120%;">{$all_total}</span>点</nobr><nobr>　達成:<span style="color:red;font-size:120%;">{$reach_rate}</span>%</nobr></div>
</td></tr>
</table>



<div class="table-responsive">
<table border="1" style="font-size:70%;border-color:#888888;">
<thead>
<tr bgcolor="#cccccc" align="center">
<th rowspan="2" width="15"></th>
<th rowspan="2" width="120"></th>
<th rowspan="2" width="18">目標</th>
<th rowspan="2" width="18">合計</th>
<th rowspan="2" width="18">目標<br>まで<br>あと</th>
HTML;
for($k=1;$k<=$maxday;$k++){
$ybase->ST_PRI .= <<<HTML
<th width="12">{$k}<br>日</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
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
  </thead>
  <tbody>
HTML;

///////////////////////////////////
/*////////合計行
$all_total = number_format($all_total);
$ybase->ST_PRI .= <<<HTML
<tr align="right" bgcolor="#eeeeee">
<td colspan="3">ポイント計</td>
<td colspan="2"><nobr>合計{$all_total}pt</nobr></td>
HTML;
for($k=1;$k<=$maxday;$k++){
$dtotal = number_format($day_action_cnt[$k]);
$ybase->ST_PRI .= <<<HTML
<td>$dtotal</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
HTML;
*/
///////////////////////////////////
$kk=0;
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
	$hbgcolor = "#fafafa";
}else{
	$hbgcolor = "#ffffff";
}
	list($q_item_id,$q_item_name,$q_score) = pg_fetch_array($result2,$ii);
	$to_goal = $goalnum_arr[$q_item_id] - $item_action_cnt[$q_item_id];
if($ii == 0){
	$charlist = mb_str_split($q_bigitem_name);
	$str="";
	foreach($charlist as $key => $val){
		if($key != 0){
			$str .= "<br>";
		}
		if($val == 'ー'){
			$val = "｜";
		}
			$val = mb_convert_kana($val,"KVA","UTF-8");
			$str .= $val;
	}
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="$hbgcolor">
<td rowspan="{$bigitem_cnt[$q_bigitem_id]}" style="vertical-align: middle;font-size:60%;" bgcolor="{$bcolor[$i]}" width="15">
$str
</td>
HTML;
}else{
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="$hbgcolor">
HTML;
}
if(!$item_action_cnt[$q_item_id]){
	$d_acton_cnt = "";
}else{
	$d_acton_cnt = $item_action_cnt[$q_item_id];
}
$ybase->ST_PRI .= <<<HTML
<td align="center" width="120">
$q_item_name
</td>
<td align="right" width="18">{$goalnum_arr[$q_item_id]}</td>
<td align="right" width="18">$d_acton_cnt</td>
<td align="right" width="18">{$to_goal}</td>
HTML;
for($k=1;$k<=$maxday;$k++){
$ybase->ST_PRI .= <<<HTML
<td align="right" width="12">{$per_actnum_arr[$q_item_id][$k]}</td>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
HTML;

}

}

$ybase->ST_PRI .= <<<HTML

 </tbody>
</table>
</div>
</div>
<p></p>

</body>
</html>
HTML;

//$ybase->HTMLfooter();

$tcpdf->writeHTML($ybase->ST_PRI);

$fileName = 'personal'.$target_employee_id.date("Ymd").'.pdf';
$tcpdf->Output("$fileName");
$pdfData = $tcpdf->Output(rawurlencode($fileName), 'S');

// ブラウザにそのまま表示
header('Content-Type: application/pdf');
header("Content-Disposition: inline; filename*=UTF-8''".rawurlencode($fileName));
echo $pdfData;

//$ybase->priout();
////////////////////////////////////////////////
?>