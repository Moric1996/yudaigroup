<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/rank.inc');
include('./inc/rank_list.inc');

$ybase = new ybase();
$rank = new rank();
$ybase->session_get();
$tablerate = $ybase->mbscale(7);

if(!preg_match("/^[0-9]+$/",$t_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20001");
}
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:20002");
}
$rank->unitname_make($t_shop_id,$t_month);
$target_month = $t_month;
$YEAR = substr($target_month,0,4);
$MONTH = intval(substr($target_month,4,2));
$nowYYMM = date("Ym");
if(!$target_day){
	$nowday = date("j");
	if($nowYYMM > $target_month){
		$target_day = date("t",mktime(0,0,0,$MONTH,1,$YEAR));
	}else{
		$target_day = date("j",mktime(0,0,0,$MONTH,$nowday,$YEAR));
	}
	$target_month = date("Ym",mktime(0,0,0,$MONTH,$nowday,$YEAR));
}else{
	$target_day = date("j",mktime(0,0,0,$MONTH,$target_day,$YEAR));
	$target_month = date("Ym",mktime(0,0,0,$MONTH,$target_day,$YEAR));
}
$YEAR = substr($target_month,0,4);
$MONTH = intval(substr($target_month,4,2));
$maxday = date("t",mktime(0,0,0,$MONTH,$target_day,$YEAR));
$nissin = round($target_day/$maxday*100);


$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;

/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$param = "t_month=$t_month&t_shop_id=$t_shop_id";
$addsql = "shop_id = $t_shop_id and status = '1'";
$addsql2 = "month=$target_month and shop_id = $t_shop_id and status = '1'";
//////////////////////////////////////////

///////////////////////////////////////

$max_rows = intval(strlen($contents)/30) + 1;
$attack_cr = substr_count($contents,"\n") + 1;
if($max_rows < $attack_cr){
	$max_rows = $attack_cr;
	}
if($max_rows < 10){
	$max_rows = 10;
}
/////////////////////////////////////////
$to_arr = array();
$cc_arr = array();
$bcc_arr = array();
$sql = "select mail_id,employee_id,array_to_json(to_ls),array_to_json(cc_ls),array_to_json(bcc_ls),from1,title,message,to_char(add_date,'YYYY/MM/DD HH24:MI') from telecom2_mail_log where {$addsql} order by add_date desc";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	list($q_mail_id,$q_employee_id,$to_ls,$cc_ls,$bcc_ls,$frommail,$title,$contents,$q_add_date) = pg_fetch_array($result,0);
	$to_arr = json_decode($to_ls);
	$cc_arr = json_decode($cc_ls);
	$bcc_arr = json_decode($bcc_ls);
}

$kk = 0;
foreach($to_arr as $key => $val){
	$kk++;
	$sel_type[$kk] = 1;
	$tomail[$kk] = "$val";
}
foreach($cc_arr as $key => $val){
	$kk++;
	$sel_type[$kk] = 2;
	$tomail[$kk] = "$val";
}
foreach($bcc_arr as $key => $val){
	$kk++;
	$sel_type[$kk] = 3;
	$tomail[$kk] = "$val";
}
if(!$kk){
	$kk = 1;
}
$ybase->title = "Y☆Judge-メール送信";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("メール送信");

$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('textarea').on('change keyup keydown paste cut', function(){
		var change_val = $(this).attr('rows');
		if ($(this).outerHeight() > this.scrollHeight){
			$(this).height(change_val);
		}
		while ($(this).outerHeight() < this.scrollHeight){
			$(this).height($(this).height() + 1);
		}
	});
	$('a[delhref]').click(function(){
		if(!confirm('本当に削除しますか？')){
			return false;
		}else{
			location.href = $(this).attr('delhref');
		}
	});
	$('#addsendto').click(function(){
		var nowkk = $(this).attr('kkvalue');
		var nexkk = +nowkk + 1;
		$(this).attr('kkvalue',nexkk);
		$('#addsendto').val(nexkk);
		$("#mailtofm" + nexkk).css("display", "block");
	});
});

</script>

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./rank_top.php?$param" role="button">Y☆JudgeTOPに戻る</a></div>
<p></p>
<h5 style="text-align:center;">【{$rank_section_name[$t_shop_id]} {$YEAR}年{$MONTH}月{$target_day}日】メール送信管理</h5>

<p></p>
HTML;

$ybase->ST_PRI .= <<<HTML
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <tbody>
<tr><td align="center">
【新規メール送信】<br><span style="position:relative;left:110px;top:20px;"><a href="#" class="btn btn-sm btn-outline-info" id="addsendto" kkvalue="$kk">送信先追加</a></span><br>
<form action="mail_send.php" method="post">
<input type="hidden" name="t_shop_id" value="$t_shop_id">
<input type="hidden" name="t_month" value="$t_month">
HTML;

for($i=1;$i<10;$i++){
if(($kk < $i) && ($i > 1)){
	$tomailcss = "none";
}else{
	$tomailcss = "block";
}
$ybase->ST_PRI .= <<<HTML
<span id="mailtofm{$i}" style="display:$tomailcss;">
<select name="s_type[{$i}]">
HTML;
foreach($mail_send_type as $key => $val){
if($sel_type[$i] == $key){
	$selected = " selected";
}else{
	$selected = "";
}

$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>《送信先({$val}:)》</option>
HTML;
}
if($i == 1){
	$require = " required";
}else{
	$require = "";
}
$ybase->ST_PRI .= <<<HTML
</select>
<br><input type="email" name="tomail[$i]" value="{$tomail[$i]}" size="45"$require>
<br>
</span>
HTML;
}
$ybase->ST_PRI .= <<<HTML
《送信元(From:)》<br><input type="email" name="frommail" value="$frommail" size="45" required><br>
《タイトル》<br><input type="text" name="title" value="$title" size="45" required><br>
《本文》<br>
<textarea name="mailbody" id="commnet_reg" rows="$max_rows" cols="45" required>$contents</textarea>
<br>
<div style="text-align:center;"><a href="daily_achieve2.php?$param" target="_blank" class="btn btn-sm btn-outline-info">添付ファイル確認</a>
</div><br>
<input type="submit" value="送信">
<input type="reset" value="クリア">
</form>
<br>
※前回に送った内容がそのまま表示されています<br>
※送信すると送信元にも同じ内容のメールが届きます<br>
※迷惑メールのフィルター等厳しい設定している相手には届かない場合があります
</td></tr>
</table>

<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
  </thead>
  <tbody>
<tr><td align="center">
<div>
【過去の送信(最新5件)】<br><br>

HTML;
for($i=0;$i<$num;$i++){
	list($q_mail_id,$q_employee_id,$q_tomail,$q_tomail2,$q_tomail3,$q_frommail,$q_title,$q_contents,$q_add_date) = pg_fetch_array($result,$i);

$ybase->ST_PRI .= <<<HTML
<div>$q_add_date $q_title {$sec_employee_list[$q_employee_id]}</div>

HTML;

}
if(!$num){
$ybase->ST_PRI .= <<<HTML
過去の送信はありません。
HTML;
}
$ybase->ST_PRI .= <<<HTML


</div>

<p></p>
<p></p>
</td></tr>
</tbody>
</table>
<p></p>

</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>