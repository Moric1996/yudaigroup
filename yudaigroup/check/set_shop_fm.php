<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/check.inc');
include('./inc/check_list.inc');

$ybase = new ybase();
$check = new check();
$ybase->session_get();

$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;

$category_list = $check->category_make();
$item_list = $check->item_make();

/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$param = "t_shop_id=$t_shop_id";
$addsql = "section_id = $t_shop_id";
//////////////////////////////////////////
foreach($category_list as $key => $val){
	$subject_list_cate_arr[$key] = array();
}



if($t_shop_id){
	$sql = "select ckset_id,array_to_json(subject_list) from ck_check_set where section_id = '$t_shop_id' and last_flag = 1 and status = '1' order by add_date desc limit 1";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num){
	list($q_ckset_id,$q_subject_list) = pg_fetch_array($result,0);
//	$subject_list_arr = json_decode($q_subject_list);

	$subject_list_arr = $check->check_set_make($t_shop_id,$q_ckset_id);
if($itemset){
$subject_list_arr = array();
	foreach($itemset as $key1 => $val1){
		foreach($val1 as $key2 => $val2){
			$val2 = trim($val2);
			if($val2){
				array_push($subject_list_arr,$val2);
				$check->allot_by_subject[$val2] = $allotset[$key1][$key2];
			}
		}
	}
}
	$subject_list_cate_arr = $check->check_set_item_make($subject_list_arr);
	}
}

$sumcate=count($category_list);
$maxcatekey=max(array_keys($category_list));
//////////////////

$ybase->title = "店舗別チェック項目設定";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("店舗別チェック項目設定");

$addrow1 = "<tr><td align=\"center\">";
//$kk
$addrow2 = "</td><td><select name=\"itemset[";
//$key
$addrow3 = "][";
//$kk
$addrow4 = "]\" class=\"form-control\">";

$addrow6 = "</select></td><td><input type=\"number\" name=\"allotset[";
//$key
$addrow7 = "][";
//$kk
$addrow8 = "]\" value=\"$allotvalue\" class=\"form-control\" required></td><td><input type=\"checkbox\" name=\"delset[";
//$key
$addrow9 = "][";
//$kk
$addrow10 = "]\" value=\"1\" class=\"form-control\"></td></tr>";

$jsbaselist = "		var options = [''";
$optionkey=1;
foreach($category_list as $key => $val){
$checksa = $key - $optionkey;
for($i=0;$i<$checksa;$i++){
	$jsbaselist .= ",";
	$optionkey++;
}
$oplist = "";
foreach($item_list as $key3 => $val3){
if($check->item_cate[$key3] != $key){
	continue;
}
$oplist .= "<option value=\"$key3\">$val3</option>";
}
$jsbaselist .= ",'$oplist'";
	$optionkey++;
}
$jsbaselist .= "];";

$ybase->ST_PRI .= <<<HTML
<script type="text/javascript">
$(function(){
	$('select[name="t_shop_id"]').change(function(){
		$("#Form1").submit();
	});
	$("[id^=addtr]").click(function () {
$jsbaselist
		var rowNo = $(this).attr('prerows');
		var cateNo = $(this).attr('cate');
		var lastkk = $(this).attr('jun');
		var nexkk = +lastkk + 1;
		$(this).attr('jun',nexkk);
		var rodata = '$addrow1' + nexkk + '$addrow2' + cateNo + '$addrow3' + nexkk + '$addrow4' + options[cateNo] + '$addrow6' + cateNo + '$addrow7' + nexkk + '$addrow8' + cateNo + '$addrow9' + nexkk + '$addrow10';
		$('#settable tr').eq( rowNo ).after(rodata);
		for (let i = cateNo; i <= {$maxcatekey}; i++) {
			var rowsn = $("#addtr" + i).attr('prerows');
			var nxrowsn = +rowsn + 1;
			$("#addtr" + i).attr('prerows',nxrowsn);
		}
	});
	$("#slidebutton").click(function(){
		if($("#import_part").css('display') == 'none'){
		$("#import_part").css("display","block");
		$("#slidebutton").text('▲他店舗のコピー');

	}else{
		$("#import_part").css("display","none");
		$("#slidebutton").text('▼他店舗のコピー');
	}
	});
	$("#import_ex").click(function(){
		var im_shop_id = $("#import_shop_id").val();
		if(!im_shop_id){
			confirm('対象店舗を選択してください')
			return false;
		}
		if(!confirm('本当によろしいですか？')){
			return false;
		}else{
			location.href = 'set_shop_import.php?$param&import_shop_id=' + im_shop_id;
	    }
	});

});

</script>

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary" href="./set_top.php?target_month=$target_month&target_shop_id=$target_shop_id" role="button">設定TOPに戻る</a></div>
<h5 style="text-align:center;">チェック項目設定</h5>

<p></p>
<form action="./set_shop_fm.php" method="post" id="Form1">

<div class="row">
<div class="col-sm-8 offset-sm-2" style="text-align:center;">
HTML;
$auth_edit = 1;

$ybase->ST_PRI .= <<<HTML
【対象店舗】<select name="t_shop_id">
<option value="">選択してください</option>
HTML;
foreach($ybase->section_list as $key => $val){
if($key < 100){continue;}
if($key > 1000){continue;}
if(preg_match("/^3[0-9]{2}$/",$key)){continue;}
if($key == $t_shop_id){
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
HTML;

$ybase->ST_PRI .= <<<HTML
</div>
</div>
</form>

<p></p>

<div id="import_menu"><a href="#!" class="btn btn-sm btn-outline-info" id="slidebutton">▼他店舗のコピー</a></div>
<div id="import_part" style="display:none;">
<div class="card">
<div class="row">
<div class="col-sm-10 align-self-center">
<small>コピー元の店舗を選んでコピーボタンを押してください</small>
</div>
</div>
<div class="row">
<div class="col-sm-4 align-self-center">

【対象店舗】<select name="import_shop_id" id="import_shop_id">
<option value="">選択してください</option>
HTML;
foreach($ybase->section_list as $key => $val){
if($key < 100){continue;}
if($key > 1000){continue;}
if(preg_match("/^3[0-9]{2}$/",$key)){continue;}
if($key == $import_shop_id){
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
<div class="col-sm-4 align-self-center">
<a href="#!" class="btn btn-sm btn-outline-secondary" id="import_ex">コピー</a>
</div>
</div>
</div>

</div>
<p></p>

<p></p>
<form action="./set_shop_cg.php" method="post" id="form_shopcg">
<input type="hidden" name="t_shop_id" value="$t_shop_id">
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:75%;" id="settable">
<tbody>
HTML;

$t_cnt=array();
$bf_cnt=array();
$t_sum=0;
$bf_sum=0;
$rowcnt = -1;
foreach($category_list as $key => $val){
$ybase->ST_PRI .= <<<HTML
<tr align="center" bgcolor="#cccccc">
<th colspan="2">
$val
</th>
<th width="80">
配点
</th>
<th>
削除
</th>
</tr>
HTML;
$rowcnt++;

$kk=0;
if(!isset($subject_list_cate_arr[$key])){
	$subject_list_cate_arr[$key] = array();
}
	foreach($subject_list_cate_arr[$key] as $key2 => $val2){

$kk++;
if($kk%2 == 0){
	$hbgcolor = "#fafafa";
}else{
	$hbgcolor = "#ffffff";
}
$analysis_rows = intval(strlen($t_action_com[$key2])/30) + 1;
$analysis_cr = substr_count($t_action_com[$key2],"\n") + 1;
if($analysis_rows < $analysis_cr){
	$analysis_rows = $analysis_cr;
}
$t_cnt[$key] += $t_action_num[$key2];
$bf_cnt[$key] += $bf_action_num[$key2];

$ybase->ST_PRI .= <<<HTML
<tr>
<td align="center">
$kk
</td>
<td>
<select name="itemset[$key][$kk]" class="form-control" required>
HTML;
foreach($item_list as $key3 => $val3){
if($check->item_cate[$key3] != $key){
	continue;
}
if($key3 == $key2){
	$selected = " selected";
}else{
	$selected = "";
}
if($itemset[$key][$kk] && ($key3 == $itemset[$key][$kk])){
	$selected = " selected";
}

$ybase->ST_PRI .= <<<HTML

<option value="$key3"{$selected}>$val3</option>
HTML;

}
$allotvalue = $check->allot_by_subject[$key2];
$ybase->ST_PRI .= <<<HTML
</select>
</td>
<td>
<input type="number" name="allotset[$key][$kk]" value="$allotvalue" class="form-control" id="allotset[$key][$kk]" required>
</td>
<td>
<input type="checkbox" name="delset[$key][$kk]" value="1" class="form-control" id="delset[$key][$kk]">

</td>
</tr>
HTML;
$rowcnt++;
}

$ybase->ST_PRI .= <<<HTML
<tr>
<td colspan="4">
<button type="button" class="btn btn-outline-dark btn-sm text-center" role="button" id="addtr{$key}" prerows="$rowcnt" cate="$key" jun="$kk">追加</button>　
</td>
</tr>
HTML;
$rowcnt++;
$bf_sum += $bf_cnt[$key];
$t_sum += $t_cnt[$key];

}

$ybase->ST_PRI .= <<<HTML
 </tbody>
</table>
</div>
<div class="text-center">
HTML;
if($t_shop_id){
$ybase->ST_PRI .= <<<HTML
<button type="submit" class="btn btn-danger btn-lg text-center" role="button">　登　録　</button>　
<button type="reset" class="btn btn-secondary btn-lg text-center" role="button">　リセット　</button>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</form>
</div>
</div>
</div>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>