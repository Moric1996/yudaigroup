<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();
$ttype_list[1] = "会計時間帯";
$ttype_list[2] = "オーダー時間帯";
//$edit_f = 1;
/////////////////////////////////////////
if(!$ttype){
	$ttype = 1;
}
$ybase->make_shop_list();
//$ybase->shop_list['3001'] = "雄大ゴルフ熱函";	//雄大ゴルフ熱函
//$ybase->shop_list['3002'] = "雄大ゴルフ清水町";	//雄大ゴルフ清水町

$ybase->make_employee_list("1");
if(!$shop_id){
if(array_key_exists((int)$ybase->my_section_id,$ybase->shop_list)){
	$shop_id = $ybase->my_section_id;
}else{
	$shop_id = 0;
}
}

$pos_shopno = $ybase->section_to_pos[$shop_id];
if(!$pos_shopno){
	$pos_shopno = 0;
}
if($monthon){
if($selyymm){
	$yy = substr($selyymm,0,4);
	$mm = substr($selyymm,5,2);
}elseif($selyymmdd){
	$yy = substr($selyymmdd,0,4);
	$mm = substr($selyymmdd,5,2);
}
if(!$yy || !$mm){
	$now_yy = date('Y');
	$now_mm = date('m');
	$now_dd = date('d');
	$yy = date('Y',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$mm = date('m',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$dd = date('d',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
}
$maxday = date('t',mktime(0,0,0,$mm,1,$yy));
$selyymm = "$yy-$mm";
}else{
if($selyymmdd){
	$yy = substr($selyymmdd,0,4);
	$mm = substr($selyymmdd,5,2);
	$dd = substr($selyymmdd,8,2);
}
if(!$yy || !$mm || !$dd){
	$now_yy = date('Y');
	$now_mm = date('m');
	$now_dd = date('d');
	$yy = date('Y',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$mm = date('m',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$dd = date('d',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
}
$selyymmdd = "$yy-$mm-$dd";
}
$conn = $ybase->connect();

$ybase->title = "ランチ/ディナータイム設定";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);

$sql = "select shop_id,set_no,from_lunch,to_lunch,from_dinner,to_dinner from lunch_time where status = '1' and now() between from_set and to_set order by shop_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
for($i=0;$i<$num;$i++){
	list($q_shop_id,$q_set_no,$q_from_lunch,$q_to_lunch,$q_from_dinner,$q_to_dinner) = pg_fetch_array($result,$i);
	$set_no_ls[$q_shop_id] = $q_set_no;
	$q_from_lunch = sprintf('%04d',$q_from_lunch);
	$q_to_lunch = sprintf('%04d',$q_to_lunch);
	$d_from_lunch = substr($q_from_lunch,0,2).":".substr($q_from_lunch,2,2);
	$d_to_lunch = substr($q_to_lunch,0,2).":".substr($q_to_lunch,2,2);
	$from_lunch_ls[$q_shop_id] = $d_from_lunch;
	$to_lunch_ls[$q_shop_id] = $d_to_lunch;

	$q_from_dinner = sprintf('%04d',$q_from_dinner);
	$q_to_dinner = sprintf('%04d',$q_to_dinner);
	$d_from_dinner = substr($q_from_dinner,0,2).":".substr($q_from_dinner,2,2);
	$d_to_dinner = substr($q_to_dinner,0,2).":".substr($q_to_dinner,2,2);
	$from_dinner_ls[$q_shop_id] = $d_from_dinner;
	$to_dinner_ls[$q_shop_id] = $d_to_dinner;
}

$ybase->ST_PRI .= <<<HTML

<div class="container">

<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./ana_top.php?$param" role="button">戻る</a></div>

<div style="font-size:100%;margin:5px;">

<p></p>

<div class="table-responsive">
<form action="set_lunch_ex.php" method="post" id="form1">
分析用ランチ/ディナー時間の設定をしてください。<br>
<!----※ランチ時間以外はディナーとなります。<br>---->
<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#caeaca">
<th>店舗</th>
<th>ランチ開始時間</th>
<th>ランチ終了時間</th>
<th>ディナー開始時間</th>
<th>ディナー終了時間</th>
<th></th>
</tr>
</thead>
<tbody>

HTML;
foreach($ybase->shop_list as $key => $val){
if(!$set_no_ls[$key]){
	$set_no_ls[$key]=1;
}
if(!$from_lunch_ls[$key]){
	$from_lunch_ls[$key] = "00:00";
}
if(!$to_lunch_ls[$key]){
	$to_lunch_ls[$key] = "00:00";
}
if(!$from_dinner_ls[$key]){
	$from_dinner_ls[$key] = "00:00";
}
if(!$to_dinner_ls[$key]){
	$to_dinner_ls[$key] = "00:00";
}
$ybase->ST_PRI .= <<<HTML
<input type="hidden" name="set_no[$key]" value="{$set_no_ls[$key]}">
<tr align="center"><td>$val</td>
<td><input type="time" id="from_lunch[$key]" name="from_lunch[$key]" value="{$from_lunch_ls[$key]}"></td>
<td><input type="time" id="to_lunch[$key]" name="to_lunch[$key]" value="{$to_lunch_ls[$key]}"></td>
<td><input type="time" id="from_dinner[$key]" name="from_dinner[$key]" value="{$from_dinner_ls[$key]}"></td>
<td><input type="time" id="to_dinner[$key]" name="to_dinner[$key]" value="{$to_dinner_ls[$key]}"></td>
<td></td></tr>
HTML;
}
$ybase->ST_PRI .= <<<HTML



</tbody>
</table>

<button class="btn btn-info col-sm-2 offset-sm-4 border-dark" type="submit">変更</button>
<button class="btn btn-light col-sm-2 border-dark" type="reset">クリア</button>
</form>
</div>
</div>

</div>

<p></p>
<br>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>