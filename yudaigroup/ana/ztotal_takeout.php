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
$ybase->shop_list['3001'] = "雄大ゴルフ熱函";	//雄大ゴルフ熱函
$ybase->shop_list['3002'] = "雄大ゴルフ清水町";	//雄大ゴルフ清水町
$ybase->make_employee_list();
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
if($selyymm){
	$yy = substr($selyymm,0,4);
	$mm = substr($selyymm,5,2);
	$tt = date('t',mktime(0,0,0,$mm,1,$yy));
}
if(!$yy || !$mm){
	$now_yy = date('Y');
	$now_mm = date('m');
	$now_dd = date('d');
	$yy = date('Y',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$mm = date('m',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$tt = date('t',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
}
$s_yymm = date("Y-m-d",mktime(0,0,0,$mm,1,$yy));
$e_yymm = date("Y-m-d",mktime(0,0,0,$mm,$tt,$yy));
$bf_yymm = date("Y-m-d",mktime(0,0,0,$mm,0,$yy));
$selyymm = "$yy-$mm";

$param = "shop_id=$shop_id&selyymm=$selyymm";

$conn = $ybase->connect();

$ybase->title = "店舗集計-店内テイクアウト別";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#inshop_id,#inmonth').change(function(){
		$("#form1").submit();
	});
});


$(function(){
	$('a[delhref]').click(function(){
		var tday = $(this).attr('tarday');
		if(!confirm(tday + '日を確認済みしますか？')){
			return false;
		}else{
			location.href = $(this).attr('delhref');
		}
	});
});

</script>
<div class="container">

<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./ana_top.php?$param" role="button">戻る</a></div>
<div style="font-size:100%;margin:5px;">

<form action="ztotal_takeout.php" method="post" id="form1">
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
<option value="$key"{$addselect}>$val</option>
HTML;
}

$ybase->ST_PRI .= <<<HTML

</select>
<input type="month" name="selyymm" value="$selyymm" id="inmonth">
</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1" style="font-size:80%;">

<thead>
<tr align="center" bgcolor="#eacaca">
<th rowspan="2">日付</th>
<th colspan="4">店内</th>
<th colspan="4">テイクアウト</th>
<th colspan="2">店内のみ</th>
<th colspan="2">テイクアウトのみ</th>
</tr>
<tr align="center" bgcolor="#eacaca">
<th width="80">売上<br>(税抜)</th>
<th width="80">点数</th>
<th width="80">組数</th>
<th width="80">客数</th>
<th width="80">売上<br>(税抜)</th>
<th width="80">点数</th>
<th width="80">組数</th>
<th width="80">客数</th>
<th width="80">組数</th>
<th width="80">客数</th>
<th width="80">組数</th>
<th width="80">客数</th>
</tr>
</thead>

<tbody>
HTML;


$sql = "select to_char(sale_date,'FMDD'),inshopsale_notax,takeoutsale_notax,inshopsale_score,takeoutsale_score,inshop_custom,inshop_pair,takeout_custom,takeout_pair,only_inshop_custom,only_inshop_pair,only_takeout_custom,only_takeout_pair from pos_ztotal_sales where pos_shopno = $pos_shopno and sale_date between '$s_yymm' and '$e_yymm' and status = '1' order by sale_date";

//print $sql;
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

$all_inshopsale_notax = 0;
$all_takeoutsale_notax = 0;
$all_inshopsale_score = 0;
$all_takeoutsale_score = 0;
$all_inshop_custom = 0;
$all_inshop_pair = 0;
$all_takeout_custom = 0;
$all_takeout_pair = 0;
$all_only_inshop_custom = 0;
$all_only_inshop_pair = 0;
$all_only_takeout_custom = 0;
$all_only_takeout_pair = 0;

for($i=0;$i<$num;$i++){
	list($q_sale_day,$q_inshopsale_notax,$q_takeoutsale_notax,$q_inshopsale_score,$q_takeoutsale_score,$q_inshop_custom,$q_inshop_pair,$q_takeout_custom,$q_takeout_pair,$q_only_inshop_custom,$q_only_inshop_pair,$q_only_takeout_custom,$q_only_takeout_pair) = pg_fetch_array($result,$i);
	$arr_inshopsale_notax[$q_sale_day] = $q_inshopsale_notax;
	$arr_takeoutsale_notax[$q_sale_day] = $q_takeoutsale_notax;
	$arr_inshopsale_score[$q_sale_day] = $q_inshopsale_score;
	$arr_takeoutsale_score[$q_sale_day] = $q_takeoutsale_score;
	$arr_inshop_custom[$q_sale_day] = $q_inshop_custom;
	$arr_inshop_pair[$q_sale_day] = $q_inshop_pair;
	$arr_takeout_custom[$q_sale_day] = $q_takeout_custom;
	$arr_takeout_pair[$q_sale_day] = $q_takeout_pair;
	$arr_only_inshop_custom[$q_sale_day] = $q_only_inshop_custom;
	$arr_only_inshop_pair[$q_sale_day] = $q_only_inshop_pair;
	$arr_only_takeout_custom[$q_sale_day] = $q_only_takeout_custom;
	$arr_only_takeout_pair[$q_sale_day] = $q_only_takeout_pair;
	$all_inshopsale_notax += $q_inshopsale_notax;
	$all_takeoutsale_notax += $q_takeoutsale_notax;
	$all_inshopsale_score += $q_inshopsale_score;
	$all_takeoutsale_score += $q_takeoutsale_score;
	$all_inshop_custom += $q_inshop_custom;
	$all_inshop_pair += $q_inshop_pair;
	$all_takeout_custom += $q_takeout_custom;
	$all_takeout_pair += $q_takeout_pair;
	$all_only_inshop_custom += $q_only_inshop_custom;
	$all_only_inshop_pair += $q_only_inshop_pair;
	$all_only_takeout_custom += $q_only_takeout_custom;
	$all_only_takeout_pair += $q_inshopsale_notax;
}




for($i=1;$i<=$tt;$i++){
	$k = $i - 1;
	$form_inshopsale_notax = number_format($arr_inshopsale_notax[$i]);
	$form_takeoutsale_notax = number_format($arr_takeoutsale_notax[$i]);
	$form_inshopsale_score = number_format($arr_inshopsale_score[$i]);
	$form_takeoutsale_score = number_format($arr_takeoutsale_score[$i]);
	$form_inshop_custom = number_format($arr_inshop_custom[$i]);
	$form_inshop_pair = number_format($arr_inshop_pair[$i]);
	$form_takeout_custom = number_format($arr_takeout_custom[$i]);
	$form_takeout_pair = number_format($arr_takeout_pair[$i]);
	$form_only_inshop_custom = number_format($arr_only_inshop_custom[$i]);
	$form_only_inshop_pair = number_format($arr_only_inshop_pair[$i]);
	$form_only_takeout_custom = number_format($arr_only_takeout_custom[$i]);
	$form_only_takeout_pair = number_format($arr_only_takeout_pair[$i]);


$ybase->ST_PRI .= <<<HTML
<tr>
<td align="right">$i</td>
<td align="right">$form_inshopsale_notax</td>
<td align="right">$form_inshopsale_score</td>
<td align="right">$form_inshop_pair</td>
<td align="right">$form_inshop_custom</td>
<td align="right">$form_takeoutsale_notax</td>
<td align="right">$form_takeoutsale_score</td>
<td align="right">$form_takeout_pair</td>
<td align="right">$form_takeout_custom</td>
<td align="right">$form_only_inshop_pair</td>
<td align="right">$form_only_inshop_custom</td>
<td align="right">$form_only_takeout_pair</td>
<td align="right">$form_only_takeout_custom</td>
</tr>
HTML;

}
$all_inshopsale_notax = number_format($all_inshopsale_notax);
$all_takeoutsale_notax = number_format($all_takeoutsale_notax);
$all_inshopsale_score = number_format($all_inshopsale_score);
$all_takeoutsale_score = number_format($all_takeoutsale_score);
$all_inshop_custom = number_format($all_inshop_custom);
$all_inshop_pair = number_format($all_inshop_pair);
$all_takeout_custom = number_format($all_takeout_custom);
$all_takeout_pair = number_format($all_takeout_pair);
$all_only_inshop_custom = number_format($all_only_inshop_custom);
$all_only_inshop_pair = number_format($all_only_inshop_pair);
$all_only_takeout_custom = number_format($all_only_takeout_custom);
$all_only_takeout_pair = number_format($all_only_takeout_pair);

$ybase->ST_PRI .= <<<HTML
<tr bgcolor="#dddddd">
<td>計</td>
<td align="right">$all_inshopsale_notax</td>
<td align="right">$all_inshopsale_score</td>
<td align="right">$all_inshop_pair</td>
<td align="right">$all_inshop_custom</td>
<td align="right">$all_takeoutsale_notax</td>
<td align="right">$all_takeoutsale_score</td>
<td align="right">$all_takeout_pair</td>
<td align="right">$all_takeout_custom</td>
<td align="right">$all_only_inshop_pair</td>
<td align="right">$all_only_inshop_custom</td>
<td align="right">$all_only_takeout_pair</td>
<td align="right">$all_only_takeout_custom</td>

</tr>
</tbody>
</table>
</div>
</div>



</div>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>