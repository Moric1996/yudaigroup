<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
if(isset($_POST)){
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
}
if(isset($_GET)){
	foreach($_GET as $key => $value){
		${$key} = $value;
	}
}
include('../inc/ybase.inc');
include(dirname(__FILE__).'/../../camp/inc/campbase.inc');

$ybase = new ybase();
$campbase = new campbase();

$ybase->session_get($sess0,$mei0);
if(!isset($camp_id)){
	$camp_id = 1;
}
$camp_list[1] = "第1回キャンペーン";
$kensu=10;

$param2 = "camp_id=$camp_id&sel_prize_id=$sel_prize_id&sel_lot_stat=$sel_lot_stat";
$param = $param2."&page=$page";
//$edit_f = 1;
/////////////////////////////////////////
$ybase->make_shop_list();
//$ybase->shop_list['3001'] = "雄大ゴルフ熱函";	// 雄大ゴルフ熱函
//$ybase->shop_list['3002'] = "雄大ゴルフ清水町";	//雄大ゴルフ清水町
$monthon = 1;//月単位で
$ybase->make_employee_list("1");


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
$selyymm = "$yy-$mm";
}
if(($ybase->my_section_id == "003")||($ybase->my_position_class <= 40)){
	$delokflag="1";
}else{
	$delokflag="";
}
$conn = $ybase->connect(2);


$ybase->title = "キャンペーン管理-応募一覧";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#camp_id,#sel_prize_id,#sel_lot_stat').change(function(){
		$("#form1").submit();
	});
	$("[id^=delete]").click(function(){
		var dhref = $(this).attr('delhref');
		if(!confirm('応募を削除していいですか？')){
		        return false;
		}else{
			location.href = dhref;
		}
	});
});

</script>
<div class="container">
  <ul class="nav nav-tabs nav-fill" id="myTab" style="font-size:70%;">
    <li class="nav-item">
      <a href="./regi_list.php" class="nav-link active">応募一覧</a>
    </li>
    <li class="nav-item">
      <a href="./stamp_list.php" class="nav-link">スタンプログ</a>
    </li>
    <li class="nav-item">
      <a href="./log_list.php" class="nav-link">シリアルコード入力エラーログ</a>
    </li>
    <li class="nav-item">
      <a href="./emp_fm.php" class="nav-link">スタッフフラグ追加</a>
    </li>
    <li class="nav-item">
      <a href="#" class="nav-link">設定</a>
    </li>
  </ul>
</div>

<div class="container">

<p></p>
<div style="font-size:80%;margin:5px;">

<form action="regi_list.php" method="post" id="form1">

<select name="camp_id" id="camp_id">
HTML;
foreach($camp_list as $key => $val){
	if("$camp_id" == "$key"){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"{$addselect}>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML

</select>

<select name="sel_prize_id" id="sel_prize_id">
<option value="">全て</option>
HTML;
foreach($campbase->prize_list2 as $key => $val){
	if("$sel_prize_id" == "$key"){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"{$addselect}>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML

</select>

<select name="sel_lot_stat" id="sel_lot_stat">
<option value="">全て</option>
HTML;
foreach($campbase->lot_stat_list as $key => $val){
	if("$sel_lot_stat" == "$key"){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"{$addselect}>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML

</select>

</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th>応募ID</th>
<th>氏名</th>
<th>氏名カナ</th>
<th>ユーザーID</th>
<th>郵便番号</th>
<th>性別</th>
<th>年齢</th>
<th>店舗</th>
<th>staff</th>
<th>応募日</th>
<th>応募賞品</th>
<th>状態</th>
<th>削除</th>
</tr>
</thead>
<tbody>
HTML;
$sqladd = "";
if($sel_prize_id){
	$sqladd .= " and prize_id = $sel_prize_id";
}
if(preg_match("/[0-9]+/",$sel_lot_stat)){
	$sqladd .= " and lot_stat = $sel_lot_stat";
}
$sql = "select regist_id,user_id,shop_id,prize_id,name,name_kana,zipcode,prefecture_code,address,address2,telno,email,sex,age,staff_flag,lot_stat,to_char(add_date,'YYYY/MM/DD'),status from stamp_regist where camp_id = $camp_id{$sqladd} and status > '0' order by add_date desc";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

$allpage = ceil($num / $kensu);
if(!isset($page) || $page <= 0){
	$page = 1;
}
$st = ($page - 1) * $kensu;
if($st < 0){
	$st = 0;
}
$end = $st + $kensu;
if($end > $num){
	$end = $num;
}
if(!$num){
	$page = 0;
}

$mun0 = 0;
for($i=$st;$i<$end;$i++){
	list($q_regist_id,$q_user_id,$q_shop_id,$q_prize_id,$q_name,$q_name_kana,$q_zipcode,$q_prefecture_code,$q_address,$q_address2,$q_telno,$q_email,$q_sex,$q_age,$q_staff_flag,$q_lot_stat,$q_add_date,$q_status) = pg_fetch_array($result,$i);
	$q_name = stripslashes($q_name);
	$q_name_kana = stripslashes($q_name_kana);
	$address = stripslashes($address);
	$address2 = stripslashes($address2);
	$q_user_id = trim($q_user_id);
	$q_shop_id = trim($q_shop_id);
	$q_prize_id = trim($q_prize_id);
	$q_name = trim($q_name);
	$q_name_kana = trim($q_name_kana);
	$q_zipcode = trim($q_zipcode);
	$q_prefecture_code = trim($q_prefecture_code);
	$q_address = trim($q_address);
	$q_address2 = trim($q_address2);
	$q_telno = trim($q_telno);
	$q_email = trim($q_email);
	$q_sex = trim($q_sex);
	$q_age = trim($q_age);
	$q_staff_flag = trim($q_staff_flag);
	$q_lot_stat = trim($q_lot_stat);
	$q_add_date = trim($q_add_date);
	$q_status = trim($q_status);

	$k++;
	$cg_btn="";
	if(!$q_lot_stat){
		$cg_btn ="<a href=\"lot_cg.php?camp_id=$camp_id&regi_id=$q_regist_id&cgt=10\" class=\"btn btn-sm btn-info p-0 m-0\">当選</a>";
	}elseif($q_lot_stat < 10){
		$cg_btn ="<a href=\"lot_cg.php?camp_id=$camp_id&regi_id=$q_regist_id&cgt=20\" class=\"btn btn-sm btn-outline-info p-0 m-0\">発送済</a>";
	}elseif($q_lot_stat < 20){

	}elseif($q_lot_stat < 30){

	}
$ybase->ST_PRI .= <<<HTML
<tr>
<td align="right">{$q_regist_id}</td>
<td align="center">{$q_name}</td>
<td align="center">{$q_name_kana}</td>
<td align="center">{$q_user_id}</td>
<td align="center">{$q_zipcode}</td>
<td align="center">{$campbase->sex_list[$q_sex]}</td>
<td align="center">{$q_age}</td>
<td align="center">{$ybase->section_list[$q_shop_id]}</td>
<td align="center">{$campbase->staff_flag_list[$q_staff_flag]}</td>
<td align="center">{$q_add_date}</td>
<td align="center">{$campbase->prize_list2[$q_prize_id]}</td>
<td align="center">
HTML;
foreach($campbase->lot_stat_list as $key => $val){
	if($q_lot_stat == $key){
$ybase->ST_PRI .= <<<HTML
<button class="btn btn-sm btn-info p-0 my-0 mx-1">$val</button>
HTML;
	}else{
$ybase->ST_PRI .= <<<HTML
<a href="lot_cg.php?{$param}&regi_id=$q_regist_id&cgt=$key" class="btn btn-sm btn-outline-secondary p-0 my-0 mx-1 text-secondary">$val</a>
HTML;
	}

}


$ybase->ST_PRI .= <<<HTML
</td>
<td align="center"><a href="#" delhref="./regi_del.php?{$param}&regi_id=$q_regist_id" id="delete{$q_regist_id}">削除</a></td>
</tr>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tbody>
</table>
</div>

HTML;

$bf=$page-1;
$nx=$page+1;
if($bf < 1){
	$addbfclass=" disabled";
}else{
	$addbfclass="";
}
if($nx > $allpage){
	$addnxclass=" disabled";
}else{
	$addnxclass="";
}

$ybase->ST_PRI .= <<<HTML

<div class="row">
<div class="col float-right">
<span class="float-right">
{$page}／{$allpage}
<a href="regi_list.php?$param2&page=$bf" class="btn btn-sm btn-outline-secondary{$addbfclass}">＜</a>
<a href="regi_list.php?$param2&page=$nx" class="btn btn-sm btn-outline-secondary{$addnxclass}">＞</a>
</span>
</div>
</div>



<p></p>

<form action="csv_dl.php" method="post">
<input type="hidden" name="camp_id" value="$camp_id">
<input type="hidden" name="sel_prize_id" value="$sel_prize_id">
<input type="hidden" name="sel_lot_stat" value="$sel_lot_stat">
<button class="btn btn-sm col-sm-2 offset-sm-10 btn-outline-dark" type="submit">データダウンロード</button>

</form>

</div>
</div>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>