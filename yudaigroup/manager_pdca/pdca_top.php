<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

//$edit_f = 1;
/////////////////////////////////////////

$ybase->make_shop_list();
$ybase->make_employee_list("1");
if(!$shop_id){
if(array_key_exists((int)$ybase->my_section_id,$ybase->shop_list)){
	$shop_id = $ybase->my_section_id;
}else{
	$shop_id = 0;
	$edit_f = "";
}
}
if($shop_id){
	$shop_employee_list = $ybase->make_employee_list("1","","$shop_id");

}else{
	$shop_employee_list = array();
}
$ybase->employee_name_list = array();
$ybase->make_employee_list("1");
$tmp_employee_list = array_diff_assoc($ybase->employee_name_list,$shop_employee_list);
$last_employee_name_list = $shop_employee_list + $tmp_employee_list;


if($selyymm){
	$yy = substr($selyymm,0,4);
	$mm = substr($selyymm,5,2);
}
if(!$yy || !$mm){
	$yy = date('Y');
	$mm = date('m');
}
$selyymm = "$yy-$mm";

$conn = $ybase->connect();

$ybase->title = "店長会議-取組報告";
if($edit_f == 1){
	$addtitle = "　<a href=\"./pdca_top.php?shop_id={$shop_id}&selyymm={$selyymm}&edit_f=\" class=\"btn btn-secondary btn-sm\">編集終了</a>\n";
}elseif($shop_id){
	$addtitle = "　<a href=\"./pdca_top.php?shop_id={$shop_id}&selyymm={$selyymm}&edit_f=1\" class=\"btn btn-secondary btn-sm\">編集</a>\n";
}
$ybase->HTMLheader();

$path = $_SERVER['HTTP_HOST'].$ybase->PATH."inc/easyui";

$ybase->ST_PRI .= $ybase->header_pri($ybase->title.$addtitle);

if($edit_f == 1){
	$add_th1="<th class=\"border_bolt_bottom\"></th>\n<th class=\"border_bolt_bottom\"></th>";
}else{
	$add_th1="";
}

$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#inshop_id,#inmonth').change(function(){
		$("#form1").submit();
	});
	$("td > :input").change(function() {
	var vav_cate = $(this).attr('cate');
	$.ajax('ajaxchangepdca.php',
	{
	type: 'post',
	data: {
		var_name: $(this).attr('name'),
		var_val: $(this).val(),
		pdca_id: $(this).attr('pdcaid'),
		category: $(this).attr('cate'),
		shop_id: $('#inshop_id').val(),
		selyymm: $('#inmonth').val()
	},
	dataType: 'text'
	}).done(function(redata) {
		if(redata.substr(0,9) == 'newpdcaid'){
			var new_pdcaid = redata.substr(10);
		console.log(redata);
		console.log(new_pdcaid);
		console.log(vav_cate);
			$('#attack__' + vav_cate).attr('pdcaid', new_pdcaid);
			$('#goal__' + vav_cate).attr('pdcaid', new_pdcaid);
			$('#charge__' + vav_cate).attr('pdcaid', new_pdcaid);
			$('#result__' + vav_cate).attr('pdcaid', new_pdcaid);
			$('#analysis__' + vav_cate).attr('pdcaid', new_pdcaid);
			$('#attack__' + vav_cate).attr('id', 'attack_' + new_pdcaid + '_' + vav_cate);
			$('#goal__' + vav_cate).attr('id', 'goal_' + new_pdcaid + '_' + vav_cate);
			$('#charge__' + vav_cate).attr('id', 'charge_' + new_pdcaid + '_' + vav_cate);
			$('#result__' + vav_cate).attr('id', 'result_' + new_pdcaid + '_' + vav_cate);
			$('#analysis__' + vav_cate).attr('id', 'analysis_' + new_pdcaid + '_' + vav_cate);
		}

	});
	});
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
});

</script>




<style>
	.table {
		border: solid 2px #444444 !important;
	}
	.border_bolt_top {
		border-top: solid 2px #aaaaaa !important;
	}
	.border_bolt_bottom {
		border-bottom: solid 2px #666666 !important;
	}
	.border_bolt_right {
		border-right: solid 2px #666666 !important;
	}
	.border_thin {
		border-top: solid 1px #cccccc !important;
	}
	.bgcolor_attack {
		background-color: #fafffa !important;
	}
</style>

<p></p>
<div style="font-size:70%;margin:1px 50px;">

<form action="pdca_top.php" method="post" id="form1">
<input type="hidden" name="edit_f" value="$edit_f">
<select name="shop_id" id="inshop_id">
<option value="">選択してください</option>
HTML;
foreach($ybase->shop_list as $key => $val){
	if($shop_id == $key){
		$addselect = " selected";
	}else{
		$addselect = "";
	}
$ybase->ST_PRI .= <<<HTML
<option value="$key"{$addselect}>$val</option>>
HTML;
}

$ybase->ST_PRI .= <<<HTML

</select>
<input type="month" name="selyymm" value="$selyymm" id="inmonth">
</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered" id="table1">

<thead class="thead-light">
<tr align="center">
<th class="border_bolt_right border_bolt_bottom">目的</th>
<th class="border_bolt_bottom">取組み</th>
<th class="border_bolt_bottom">数値目標</th>
<th class="border_bolt_bottom">担当責任</th>
<th class="border_bolt_bottom">結果</th>
<th class="border_bolt_bottom">目標結果差の要因分析</th>
{$add_th1}
</tr>
</thead>

<tbody>
HTML;

foreach($ybase->pdca_purpose_list as $key => $val){
	$sql = "select pdca_id,pre_pdca_id,attack,goal,charge,result,analysis,add_date,status from manager_pdca where shop_id = $shop_id and to_char(month,'YYYYMM') = '$yy$mm' and purpose_id = $key and status = '1' order by pdca_id";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$rows = 1;
	}else{
		$rows = $num;
		if($edit_f == 1){
			$rows++;
		}
	}
	for($i=0;$i<$rows;$i++){
		if($num && ($num > $i)){
			list($q_pdca_id,$q_pre_pdca_id,$q_attack,$q_goal,$q_charge,$q_result,$q_analysis,$q_add_date,$q_status) = pg_fetch_array($result,$i);
		}else{
			list($q_pdca_id,$q_pre_pdca_id,$q_attack,$q_goal,$q_charge,$q_result,$q_analysis,$q_add_date,$q_status) = array();
		}
		if($i == 0){
			$ybase->ST_PRI .= "<tr class=\"border_bolt_top\">\n";
		}else{
			$ybase->ST_PRI .= "<tr class=\"border_thin\">\n";
		}
		if($i == 0){
			$ybase->ST_PRI .= "<th rowspan=\"$rows\" style=\"vertical-align:middle;text-align:center;\" class=\"border_bolt_right\">$val</th>\n";
		}
		if($edit_f == 1){
	$attack_rows = intval(strlen($q_attack)/30) + 1;
	$attack_cr = substr_count($q_attack,"\n") + 1;
	if($attack_rows < $attack_cr){
		$attack_rows = $attack_cr;
	}
	$goal_rows = intval(strlen($q_goal)/30) + 1;
	$goal_cr = substr_count($q_goal,"\n") + 1;
	if($goal_rows < $goal_cr){
		$goal_rows = $goal_cr;
	}
	$result_rows = intval(strlen($q_result)/30) + 1;
	$result_cr = substr_count($q_result,"\n") + 1;
	if($result_rows < $result_cr){
		$result_rows = $result_cr;
	}
	$analysis_rows = intval(strlen($q_analysis)/30) + 1;
	$analysis_cr = substr_count($q_analysis,"\n") + 1;
	if($analysis_rows < $analysis_cr){
		$analysis_rows = $analysis_cr;
	}
	$max_rows = max($attack_rows,$goal_rows,$result_rows,$analysis_rows);
$ybase->ST_PRI .= <<<HTML
<td class="bgcolor_attack"><textarea name="attack" class="form-control" id="attack_{$q_pdca_id}_{$key}" pdcaid="$q_pdca_id" cate="$key" rows="$max_rows" style="font-size:75%;">$q_attack</textarea></td>
<td class="bgcolor_attack"><textarea name="goal" class="form-control" id="goal_{$q_pdca_id}_{$key}" pdcaid="$q_pdca_id" cate="$key" rows="$max_rows" style="font-size:75%;">$q_goal</textarea></td>
<td class="bgcolor_attack"><select name="charge" id="charge_{$q_pdca_id}_{$key}" pdcaid="$q_pdca_id" cate="$key">
<option value=""></option>
HTML;
foreach($last_employee_name_list as $key2 => $val2){
if($q_charge == $key2){
	$selected = " selected";
}else{
	$selected = "";
}
$ybase->ST_PRI .= <<<HTML
<option value="$key2"{$selected}>$val2</option>
HTML;
}
if($q_pdca_id){
	$adddel = "<nobr><a class=\"btn btn-secondary btn-sm\" href=\"#\" delhref=\"./pdca_del.php?del_pdca_id={$q_pdca_id}&shop_id={$shop_id}&selyymm={$selyymm}&edit_f=$edit_f&cate=$key\" role=\"button\" style=\"font-size:70%;\">削除</a></nobr>";
}else{
	$adddel = "";
}
$ybase->ST_PRI .= <<<HTML
</select></td>
<td><textarea name="result" class="form-control" id="result_{$q_pdca_id}_{$key}" pdcaid="$q_pdca_id" cate="$key" rows="$max_rows" style="font-size:75%;">$q_result</textarea></td>
<td><textarea name="analysis" class="form-control" id="analysis_{$q_pdca_id}_{$key}" pdcaid="$q_pdca_id" cate="$key" rows="$max_rows" style="font-size:75%;">$q_analysis</textarea></td>
<td>$adddel</td>
HTML;
if($i == 0){
$add =time();
	$ybase->ST_PRI .= "<td rowspan=\"$rows\" style=\"vertical-align:bottom;text-align:center;\"><nobr><a class=\"btn btn-outline-secondary btn-sm\" href=\"./pdca_top.php?shop_id={$shop_id}&selyymm={$selyymm}&edit_f=$edit_f&$add&#tuika{$key}\" role=\"button\" style=\"font-size:70%;\" id=\"tuika{$key}\">行追加</a></nobr></td>\n";
}
$ybase->ST_PRI .= "</tr>";
$endexplain = "<small>※未入力の行がある場合は「行追加」を押しても新しい行は追加されません</small>";
		}else{
$order   = array("\r\n", "\n", "\r");
$replace = '<br>';
$q_attack = str_replace($order, $replace, htmlspecialchars($q_attack));
$q_goal = str_replace($order, $replace, htmlspecialchars($q_goal));
$q_result = str_replace($order, $replace, htmlspecialchars($q_result));
$q_analysis = str_replace($order, $replace, htmlspecialchars($q_analysis));
$ybase->ST_PRI .= <<<HTML
<td class="bgcolor_attack">$q_attack</td>
<td class="bgcolor_attack">$q_goal</td>
<td class="bgcolor_attack">{$ybase->employee_name_list[$q_charge]}</td>
<td>$q_result</td>
<td>$q_analysis</td>
</tr>
HTML;
		}
	}
}

$ybase->ST_PRI .= <<<HTML


</tbody>
</table>
$endexplain
</div>
</div>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>