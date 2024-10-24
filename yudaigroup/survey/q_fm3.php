<?php
/////////////////////////////////////////////////////////////////
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
include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/../../survey/inc/survbase.inc');
$ybase = new ybase();
$survbase = new survbase();
//$ybase->session_get();

if(!preg_match("/^[0-9]+$/",$survey_set_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:12001");
}
$time = time();
$conn = $ybase->connect(2);
//////////////////////////////////////////条件

$ybase->title = "お客様アンケート";
$ybase->BASE_COLOR1 = "#deeeff";
$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri(1);

$param = "survey_set_id=$survey_set_id";
$addsql = "";
//////////////////////////////////////////1時間前まで回答があるか確認

////////////////////////
$sql = "select company_id,shop_id,survey_no,uiset,title,title_set,com,image,end_com,end_com2,end_URL,add_date from survey_set where survey_set_id = $survey_set_id and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("DBエラー。ERROR_CODE:10002");
}
list($q_company_id,$q_shop_id,$q_survey_no,$q_uiset,$q_title,$q_title_set,$q_com,$q_image,$q_end_com,$q_end_com2,$q_end_URL,$q_add_date) = pg_fetch_array($result,0);
	$shop_name = $survbase->shop_list[$q_shop_id];
	$q_title = trim($q_title);
	$q_survey_no = trim($q_survey_no);
	$q_title = stripslashes($q_title);
	$q_com = trim($q_com);
	$q_com = stripslashes($q_com);
	$q_end_com = trim($q_end_com);
	$q_end_com = stripslashes($q_end_com);
	$q_end_com2 = trim($q_end_com2);
	$q_end_com2 = stripslashes($q_end_com2);
	$q_end_URL = trim($q_end_URL);
	
	$q_com = str_replace("#SHOPNAME#","$shop_name", $q_com);

///////////////////////////////////////////////////////////////////////////////////2重回答防ぐ
	$com_max_rows = intval(strlen($q_com)/120);
	$com_max_cr = substr_count($q_com,"\n");
	$com_max_cr2 = substr_count($q_com,"\r");
	if($com_max_cr < $com_max_cr2){
		$com_max_cr = $com_max_cr2;
	}
		$com_max_rows += $com_max_cr;
//////////////////////////////////////////////////////////////////////////////////
$ybase->ST_PRI .= <<<HTML
<script type="text/javascript" src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="./js/update.js?$time"></script>
<script>
$(function(){
	$("textarea#com").attr("rows", $com_max_rows).on("input", e => {
		$(e.target).height(0).innerHeight(e.target.scrollHeight);
	});
});
</script>



<style type="text/css">
<!--
textarea{
	resize: none;
	overflow: hidden;
}
input.textsmp{
	background: #ffffff;
	border-top: none;
	border-left: none;
	border-right: none;
}
input.textoption{
	background: #ffffff !important;
	border: none;
}
-->
</style>
<input type="hidden" name="shop_id" value="$q_shop_id" id="shop_id">
<input type="hidden" name="survey_no" value="$q_survey_no" id="survey_no">
<input type="hidden" name="company_id" value="$q_company_id" id="company_id">
<input type="hidden" name="survey_set_id" value="$survey_set_id" id="survey_set_id">

<div class="container">
  <ul class="nav nav-tabs nav-fill" id="myTab" style="font-size:70%;">
    <li class="nav-item">
      <a href="./q_fm.php?{$param}" class="nav-link active">アンケート修正</a>
    </li>
    <li class="nav-item">
      <a href="./q_fm2.php?{$param}" class="nav-link">アンケート完了画面修正</a>
    </li>
    <li class="nav-item">
      <a href="" class="nav-link">設定</a>
    </li>
  </ul>
</div>


<div class="container">

<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">アンケート設定</div>
<div class="card-body">

<p></p>

<div class="form-row ">
<div class="form-group col-6 col-sm-3">
<label class="font-weight-bold">店舗名</label>
<select name="t_shop_id" id="t_shop_id" class="form-control form-control-sm border-dark"{$disable}>
<option value="">選択してください</option>
HTML;
foreach($survbase->shop_list as $key => $val){
if($key == $q_shop_id){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key"$selected>$val</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</div>

<div class="form-group col-6 col-sm-3">
<label class="font-weight-bold">アンケートNO</label>
<select name="t_survey_no" id="t_survey_no" class="form-control form-control-sm border-dark"{$disable}>
<option value="">選択してください</option>
HTML;
for($i=1;$i<100;$i++){
	if($key == $q_survey_no){
		$selected = " selected";
	}else{
		$selected = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$i"$selected>$i</option>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</div>

</div>



<p></p>
</div><!-----card-body------>
</div><!-----card------>

</div><!-----container------>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>