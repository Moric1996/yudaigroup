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
	$ybase->error("パラメーターエラー。ERROR_CODE:12001");
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
	$ybase->error("DBエラー。ERROR_CODE:10002");
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
	$com_max_rows = intval(strlen($q_com)/120);
	$com_max_cr = substr_count($q_com,"\n");
	$com_max_cr2 = substr_count($q_com,"\r");
	if($com_max_cr < $com_max_cr2){
		$com_max_cr = $com_max_cr2;
	}
		$com_max_rows += $com_max_cr;
//////////////////////////////////////////////////////////////////////////////////
$ybase->ST_PRI .= <<<HTML
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
<div class="card border-0 rounded bg-info mx-auto" style="background-color: {$ybase->BASE_COLOR1} !important;">
<div class="card-body">


<div class="card rounded border-0 mx-auto" style="max-width:950px;">
<img class="img-fluid rounded" src="../../survey/img/{$q_image}" alt="$shop_name">
</div>
<p></p>

<div class="card border-0 rounded bg-white mx-auto" style="max-width:950px;">
<div class="card-header border-0 bg-white text-center fs-3">雄大グループお客様アンケート</div>
<div class="card-body">
<textarea name="com" class="form-control" id="com" rows="$com_max_rows" placeholder="前説明文を入力" title="前説明文" colname="com">$q_com</textarea>
<br>
</div><!-----card-body------>
</div><!-----card------>
<p></p>


HTML;

$sql = "select card_id,type,number,matter,image,com,require,jun from survey_card where survey_set_id = $survey_set_id and status = '1' order by jun";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

for($i=0;$i<$num;$i++){
	list($q_card_id,$q_type,$q_number,$q_matter,$q_card_image,$q_card_com,$q_require,$q_jun) = pg_fetch_array($result,$i);
	$q_matter = trim($q_matter);
	$q_matter = stripslashes($q_matter);
	$q_card_com = trim($q_card_com);
	$q_card_com = stripslashes($q_card_com);
	$q_require = trim($q_require);
	if($q_require){
		$badge = " <button torequire=\"0\" class=\"btn badge bg-danger\" id=\"requirecg{$q_card_id}\" card_id=\"$q_card_id\" title=\"必須ON\">必須</button>";
	}else{
		$badge = " <button torequire=\"1\" class=\"btn badge bg-light\" id=\"requirecg{$q_card_id}\" card_id=\"$q_card_id\" style=\"color:#bbbbbb;\" title=\"必須OFF\">必須</button>";
	}
	$nameid_hd="nm_".$q_survey_set_id."_".$q_card_id;
	$nameid_hd_other="nm_".$q_survey_set_id."_".$q_card_id."_99";
	$val = ${$nameid_hd};
	$valother = ${$nameid_hd_other};


///////////////////////////////////////////////////////////////////////////各選択肢の作成
	$str="";

	$sql2 = "select option_id,grid_no,other_flag,key,value,jun from survey_option where survey_set_id = $survey_set_id and card_id = $q_card_id and status = '1' order by jun";
	$result2 = $ybase->sql($conn,$sql2);
	$num2 = pg_num_rows($result2);
	switch($q_type){
		case 1:
		case 10:
		case 11:
		case 12:
		if($q_type == 1){
			$inputtype="text";
			$textval = "記述式テキスト(1行のみ)";
		}elseif($q_type == 10){
			$textval = "日付";
		}elseif($q_type == 11){
			$textval = "時刻";
		}elseif($q_type == 12){
			$textval = "Eメール";
		}
			for($ii=0;$ii<$num2;$ii++){
				list($q_option_id,$q_grid_no,$q_other_flag,$q_key,$q_value,$q_jun) = pg_fetch_array($result2,$ii);
			$nameid="nm_".$survey_set_id."_".$q_card_id;
$str .= <<<HTML
<input type="text" class="textsmp" placeholder="$textval" size="50%" disabled>
HTML;
			}
			break;
		case 2:
			for($ii=0;$ii<$num2;$ii++){
				list($q_option_id,$q_grid_no,$q_other_flag,$q_key,$q_value,$q_jun) = pg_fetch_array($result2,$ii);
			$nameid="nm_".$survey_set_id."_".$q_card_id;
$str .= <<<HTML
<input type="text" class="textsmp" placeholder="記述式テキスト(複数行)" size="100%" disabled>

HTML;
			}
			break;
		case 3:
		case 4:
		case 5:
		$inputtype = "<div class=\"form-check form-control-lg\">\n";
		if($q_type == 3){
			$inputtype.="<input type=\"radio\" class=\"form-check-input\" disabled>";
		}elseif($q_type == 4){
			$inputtype.="<input type=\"checkbox\" class=\"form-check-input\" disabled>";
		}elseif($q_type == 5){
			$inputtype="";
		}
			$nameid="nm_".$survey_set_id."_".$q_option_id;
			$other_flag = "";
			for($ii=0;$ii<$num2;$ii++){
				list($q_option_id,$q_grid_no,$q_other_flag,$q_key,$q_value,$q_jun) = pg_fetch_array($result2,$ii);
				if($q_type == 5){
					$inputtypeno=$ii + 1;
					$inputtype = "<div class=\"input-group mb-2 form-control-lg\">\n<div class=\"input-group-prepend\">\n{$inputtypeno}.</div>\n";
				}
				$q_value = trim($q_value);
				$q_value = stripslashes($q_value);
				if($q_other_flag == "1"){
					$otdisable = " disabled";
					$other_flag = 1;
				}else{
					$otdisable = "";
				}
				if($q_other_flag != "2"){
$str .= <<<HTML
<div class="form-group row">
<div class="col-lg-11">
{$inputtype}
<input type="text" class="form-control textoption" name="value{$q_option_id}" value="{$q_value}" placeholder="選択肢を入力" option_id="$q_option_id" colname="value"{$otdisable}>
</div>
</div>
<div class="col-lg-1">
<div class="form-control-lg">
<button type="button" class="btn btn-light btn-md" id="optiondel_{$q_card_id}_{$q_option_id}" title="削除" card_id="$q_card_id" option_id="$q_option_id">×</button>

</div>
</div>
</div>
HTML;
				}
			}
$str .= <<<HTML
<div class="form-group row">
<div class="col-lg-11">
<div class="form-check form-control-lg">
<button type="button" class="btn btn-light btn-md" id="optionadd{$q_card_id}" card_id="$q_card_id">選択肢を追加</button>
HTML;
if(!$other_flag){
$str .= <<<HTML
<small>または</small>
<button type="button" class="btn btn-light btn-md" id="optionother{$q_card_id}" card_id="$q_card_id">その他を追加</button>
HTML;
}
$str .= <<<HTML
</div>
</div>
</div>
HTML;

			break;
		case 6:
		case 7:
		case 8:
		case 9:
		case 99:
			break;
	}
	$content = $str;

//////////////////////////////////////////////////////////////////////////////////////////////////////////////


	$sel_number = "<select name=\"number_{$q_card_id}\" id=\"number_{$q_card_id}\" class=\"form-control\" title=\"質問番号を選択\" card_id=\"$q_card_id\" colname=\"number\"><option value=\"\" title=\"番号を設定しない場合は空欄を選択\"></option>\n";
	for($k=1;$k<=99;$k++){
		if($k == $q_number){
			$selected = " selected";
		}else{
			$selected = "";
		}
		$sel_number .= "<option value=\"$k\"$selected>{$k}.</option>";
	}
		$sel_number .= "</select>";

$ybase->ST_PRI .= <<<HTML

<div class="card border-0 rounded bg-white mx-auto" style="max-width:950px;">
<div class="card-header border-0 bg-white">

<div class="form-group row">
<div class="col-sm-5 offset-sm-7">
<select class="form-control" name="type_{$q_card_id}" id="type_{$q_card_id}" card_id="$q_card_id">
<option value="" style="color:#888888;">質問タイプを選択</option>
HTML;
foreach($ybase->survey_type_list as $key => $val){
	if($key == $q_type){
		$selected = " selected";
	}else{
		$selected = "";
	}
$ybase->ST_PRI .= <<<HTML
	<option value="$key"{$selected}>$val</option>
HTML;

}
$ybase->ST_PRI .= <<<HTML
</select>
</div>
</div>
<hr>
<div class="form-group row">
<div class="col-sm-11">
<div class="input-group mb-2">
<div class="input-group-prepend">
{$sel_number}
</div>
<input type="text" class="form-control" name="matter_{$q_card_id}" value="{$q_matter}" placeholder="質問内容を入力" title="質問内容を入力" card_id="$q_card_id" colname="matter">
</div>
</div>
<div class="col-auto">
<div class="input-group mb-2">
{$badge}
</div>
</div>
</div>

</div>
<div class="card-body">

{$content}
HTML;
if($q_type == 99){
	$card_com_max_rows = intval(strlen($q_card_com)/120);
	$card_com_max_cr = substr_count($q_card_com,"\n");
	$card_com_max_cr2 = substr_count($q_card_com,"\r");
	if($card_com_max_cr < $card_com_max_cr2){
		$card_com_max_cr = $card_com_max_cr2;
	}
		$card_com_max_rows += $card_com_max_cr;

//////////////////////////////////////////////////////////////////////////////////
$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$("textarea#card_com{$q_card_id}").attr("rows", $card_com_max_rows).on("input", e => {
		$(e.target).height(0).innerHeight(e.target.scrollHeight);
	});
});
</script>

<div class="col-sm-11">
<textarea name="card_com{$q_card_id}" class="form-control" id="card_com{$q_card_id}" rows="$card_com_max_rows" placeholder="テキスト内容を入力" title="テキスト内容を入力" card_id="$q_card_id" colname="card_com">$q_card_com</textarea>
</div>
HTML;
}
$ybase->ST_PRI .= <<<HTML

</div><!-----card-body------>
</div><!-----card------>
<p></p>
HTML;

}

$ybase->ST_PRI .= <<<HTML

<p></p>

</div><!-----card-body------>
</div><!-----card------>

</div><!-----container------>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>