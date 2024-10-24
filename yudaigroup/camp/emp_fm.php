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

$param = "camp_id=$camp_id";
//$edit_f = 1;
/////////////////////////////////////////
$ybase->make_shop_list();
//$ybase->shop_list['3001'] = "雄大ゴルフ熱函";	// 雄大ゴルフ熱函
//$ybase->shop_list['3002'] = "雄大ゴルフ清水町";	//雄大ゴルフ清水町
$monthon = 1;//月単位で
$ybase->make_employee_list("1");


if(($ybase->my_section_id == "003")||($ybase->my_position_class <= 40)){
	$delokflag="1";
}else{
	$delokflag="";
}
$conn = $ybase->connect(2);


$ybase->title = "キャンペーン管理-従業員フラグ";

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
      <a href="./stamp_list.php" class="nav-link">スタンプログ</a>
    </li>
    <li class="nav-item">
      <a href="./log_list.php" class="nav-link">シリアルコード入力エラーログ</a>
    </li>
    <li class="nav-item">
      <a href="./emp_fm.php" class="nav-link active">スタッフフラグ追加</a>
    </li>
    <li class="nav-item">
      <a href="#" class="nav-link">設定</a>
    </li>
  </ul>
</div>

<div class="container">

<p></p>
<div style="font-size:80%;margin:5px;">

<form action="emp_add.php" method="post" id="form1">

従業員のフラグを付けます。<br>
<br>
氏名で判断しますので、ソース元をフォームに入れるか、PSの従業員データを元にするか選んでください。
<br><br>






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
<br><br>
参照するデータを選んでください。
<br>
<input type="radio" name="sourcetype" value="1">PS従業員データ参照<br>
<input type="radio" name="sourcetype" value="2" checked>入力データ参照<br>
入力データ参照の場合、下記フォームに氏名を改行区切りで入れてください。<br>
<textarea name="name_list" cols="30" rows="20">
</textarea>
<br>
<input type="submit" value="送信" class="btn btn-sm btn-info">
</form>
<p></p>

<p></p>


</div>
</div>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>