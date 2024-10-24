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

$ybase = new ybase();
$ybase->session_get();
if(!isset($camp_id)){
	$camp_id = 1;
}
$camp_list[1] = "第1回キャンペーン";
$kensu=10;

$param = "camp_id=$camp_id";
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

///////////////////////////////
$sql = "select code_id,to_char(t_date,'YYYY/MM') from stamp_code where camp_id = $camp_id and status = '1' order by code_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	for($i=0;$i<$num;$i++){
		list($q_code_id,$q_t_date) = pg_fetch_array($result,$i);
		$stamp_code_list[$q_code_id] = "$q_t_date";
	}
}

///////////////////////////////
$ybase->title = "キャンペーン管理-スタンプ獲得一覧";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#camp_id').change(function(){
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
      <a href="./regi_list.php" class="nav-link">応募一覧</a>
    </li>
    <li class="nav-item">
      <a href="./stamp_list.php" class="nav-link active">スタンプログ</a>
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

<form action="stamp_list.php" method="post" id="form1">
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
</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th>押印ID</th>
<th>種類</th>
<th>ユーザーID</th>
<th>IP</th>
<th>UA</th>
<th>獲得日</th>
<th>状態</th>
</tr>
</thead>
<tbody>
HTML;

$sql = "select mark_id,code_id,shop_id,user_id,ip,ua,to_char(add_date,'YYYY/MM/DD HH24:MI:SS'),status from stamp_mark where camp_id = $camp_id and status = '1' order by add_date desc";

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
	list($q_mark_id,$q_code_id,$q_shop_id,$q_user_id,$q_ip,$q_ua,$q_add_date,$q_status) = pg_fetch_array($result,$i);
	$q_code_id = trim($q_code_id);
	$q_shop_id = trim($q_shop_id);
	$q_user_id = trim($q_user_id);
	$q_ip = trim($q_ip);
	$q_ua = trim($q_ua);
	$q_add_date = trim($q_add_date);
	$q_status = trim($q_status);

	$k++;
$ybase->ST_PRI .= <<<HTML
<tr>
<td align="right">{$q_mark_id}</td>
<td align="center">{$stamp_code_list[$q_code_id]}</td>
<td align="center">{$q_user_id}</td>
<td align="center">{$q_ip}</td>
<td align="center">{$q_ua}</td>
<td align="center">{$q_add_date}</td>
<td align="center">{$q_status}</td>
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
<a href="stamp_list.php?$param&page=$bf" class="btn btn-sm btn-outline-secondary{$addbfclass}">＜</a>
<a href="stamp_list.php?$param&page=$nx" class="btn btn-sm btn-outline-secondary{$addnxclass}">＞</a>
</span>
</div>
</div>



<p></p>
</div>
</div>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>