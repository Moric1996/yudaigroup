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
include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/../../survey/inc/survbase.inc');
$ybase = new ybase();
$survbase = new survbase();
//$ybase->session_get();

if(!preg_match("/^[0-9]+$/",$survey_set_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:12201");
}

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
	$ybase->error("DBエラー。ERROR_CODE:12202");
}
list($q_company_id,$q_shop_id,$q_survey_no,$q_uiset,$q_title,$q_title_set,$q_com,$q_image,$q_end_com,$q_end_com2,$q_end_URL,$q_add_date) = pg_fetch_array($result,0);
	$shop_name = $survbase->shop_list[$q_shop_id];
	$q_title = trim($q_title);
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
	$end_com_max_rows = intval(strlen($q_end_com)/120);
	$end_com_max_cr = substr_count($q_end_com,"\n");
	$end_com_max_cr2 = substr_count($q_end_com,"\r");
	if($end_com_max_cr < $end_com_max_cr2){
		$end_com_max_cr = $end_com_max_cr2;
	}
		$end_com_max_rows += $end_com_max_cr;

	$end_com2_max_rows = intval(strlen($q_end_com2)/120);
	$end_com2_max_cr = substr_count($q_end_com2,"\n");
	$end_com2_max_cr2 = substr_count($q_end_com2,"\r");
	if($end_com2_max_cr < $end_com2_max_cr2){
		$end_com2_max_cr = $end_com2_max_cr2;
	}
		$end_com2_max_rows += $end_com2_max_cr;
//////////////////////////////////////////////////////////////////////////////////


$sql = "select jump_id,jump_no,url,jump_type,jump_time,jump_com,target_card_ids,score_avg from survey_jump where survey_set_id = $survey_set_id and shop_id = '$q_shop_id' and status = '1' order by jump_no";
$result = $survbase->sql($conn,$sql);
$num = pg_num_rows($result);
$t_card_ids_arr = array();
if($num){
	list($q_jump_id,$q_jump_no,$q_url,$q_jump_type,$q_jump_time,$q_jump_com,$q_target_card_ids,$q_score_avg) = pg_fetch_array($result,0);
	$q_target_card_ids = str_replace("{","",$q_target_card_ids);
	$q_target_card_ids = str_replace("}","",$q_target_card_ids);
	$t_card_ids_arr = explode(",",$q_target_card_ids);
	$q_jump_com = $survbase->replace_br($q_jump_com);
}
$t_card_num = count($t_card_ids_arr);
	$jump_com_max_rows = intval(strlen($q_jump_com)/120);
	$jump_com_max_cr = substr_count($q_jump_com,"\n");
	$jump_com_max_cr2 = substr_count($q_jump_com,"\r");
	if($jump_com_max_cr < $jump_com_max_cr2){
		$jump_com_max_cr = $jump_com_max_cr2;
	}
		$jump_com_max_rows += $jump_com_max_cr;


$ybase->ST_PRI .= <<<HTML
<script src="./js/update.js?$time"></script>
<script>
$(function(){
	$("textarea#end_com").attr("rows", $end_com_max_rows).on("input", e => {
		$(e.target).height(0).innerHeight(e.target.scrollHeight);
	});
	$("textarea#end_com2").attr("rows", $end_com2_max_rows).on("input", e => {
		$(e.target).height(0).innerHeight(e.target.scrollHeight);
	});
	$("textarea#jump_com").attr("rows", $jump_com_max_rows).on("input", e => {
		$(e.target).height(0).innerHeight(e.target.scrollHeight);
	});
});

</script>
<style type="text/css">
<!--
textarea{
	border: none !important;
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
      <a href="./q_fm.php?{$param}" class="nav-link">アンケート修正</a>
    </li>
    <li class="nav-item">
      <a href="./q_fm2.php?{$param}" class="nav-link active">アンケート完了画面修正</a>
    </li>
    <li class="nav-item">
      <a href="" class="nav-link">設定</a>
    </li>
  </ul>
</div>
<div class="container">
<div class="card border-0 rounded bg-info mx-auto" style="background-color: {$ybase->BASE_COLOR1} !important;">
<div class="card-body">


<div class="card rounded border-0 mx-auto" style="max-width:950px;">
<img class="img-fluid rounded" src="../../survey/img/{$q_image}" alt="$shop_name">
</div>
<p></p>

HTML;

	if($q_jump_type == 1){
$gbm = <<<HTML
<textarea name="jump_com" class="form-control" id="jump_com" rows="$jump_com_max_rows" placeholder="誘導コメント内容を入力" title="誘導コメント内容を入力" colname="jump_com">$q_jump_com</textarea>

<div class="row">
<div class="col text-center">
<button href="#" class="btn btn-primary d-grid gap-2 col-10 mx-auto btn-lg" style="font-size:150%;">クチコミページへ</button>
</div>
</div>

HTML;
$jump_js = "";

	}elseif($q_jump_type == 2){
$gbm = <<<HTML
<textarea name="jump_com" class="form-control" id="jump_com" rows="$jump_com_max_rows" placeholder="誘導コメント内容を入力" title="誘導コメント内容を入力" colname="jump_com">$q_jump_com</textarea>
<div class="row">
<div class="col text-center">
<button href="#" class="btn btn-primary d-grid gap-2 col-10 mx-auto">クチコミページへ</button>
</div>
</div>
<div class="text-center"><small>
※ページは自動で移動します
</small></div>

HTML;
$jump_js = <<<HTML

setTimeout("link()",{$q_jump_time});
function link(){
	var linkurl = "{$q_Go_URL2}";
	if(!window.open(linkurl)) {
		location.href = linkurl;
	}else{
		window.open(linkurl,"_blank");
	}
}

HTML;

	}elseif($q_jump_type == 3){
$jump_js = <<<HTML

$(function($) {
	setTimeout(function(){
		$('.popup').addClass('show').fadeIn();
	},{$q_jump_time});
	$("[id^=close]").on('click',function(){
		$('.popup').fadeOut();
	});
});

HTML;
	}

$answer_add_date = date("Y年m月d日 H時i分");

$addcontents .= <<<HTML

<textarea name="end_com" class="form-control" id="end_com" rows="$end_com_max_rows" placeholder="コメント内容を入力" title="コメント内容を入力" colname="end_com">$q_end_com</textarea>

{$gbm}
<br>
ご回答日時【{$answer_add_date}】<br>
店舗名【{$shop_name}】<br><br>

<textarea name="end_com2" class="form-control" id="end_com2" rows="$end_com2_max_rows" placeholder="コメント2内容を入力" title="コメント2内容を入力" colname="end_com2">$q_end_com2</textarea>

HTML;



$ybase->ST_PRI .= <<<HTML
<div class="card rounded border-0 mx-auto" style="max-width:950px;">
<div class="card-header border-0 bg-white text-center fs-3">雄大グループお客様アンケート</div>
<div class="card-body">
$addcontents
<br>

<div class="text-center"><a href="https://yudai.co.jp/">雄大ホームページへ</a></div>

</div><!-----card-body------>
</div><!-----card------>

</div><!-----card-body------>
</div><!-----card------>

</div><!-----container------>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>