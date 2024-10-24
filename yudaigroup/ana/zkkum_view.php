<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();
$ttype_list[1] = "会計時間帯";
$ttype_list[2] = "オーダー時間帯";
//$edit_f = 1;
/////////////////////////////////////////
if(!$ttype){
	$ttype = 1;
}
$ybase->make_shop_list();
$ybase->shop_list['3001'] = "雄大ゴルフ熱函";	//雄大ゴルフ熱函
$ybase->shop_list['3002'] = "雄大ゴルフ清水町";	//雄大ゴルフ清水町

$ybase->make_employee_list("1");
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
}
$conn = $ybase->connect();

$ybase->title = "店舗分析-客組数集計";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#inshop_id,#inmonth,#ttype,#monthon').change(function(){
		$("#form1").submit();
	});
});

</script>
<div class="container">

<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./ana_top.php?$param" role="button">戻る</a></div>

<div style="font-size:100%;margin:5px;">

<form action="zkkum_view.php" method="post" id="form1">
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
if($monthon == 1){
	$monthon_checked = " checked";
	$selectdate = "<input type=\"month\" name=\"selyymm\" value=\"$selyymm\" id=\"inmonth\">";
}else{
	$monthon_checked = "";
	$selectdate = "<input type=\"date\" name=\"selyymmdd\" value=\"$selyymmdd\" id=\"inmonth\">";
}
$ybase->ST_PRI .= <<<HTML

</select>
$selectdate
<input type="checkbox" name="monthon" value="1" id="monthon"{$monthon_checked}>月表示


</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th>日付</th>
<th>客組数</th>
<th>税抜売上(円)</th>
<th>売上点数</th>
<th>客数(人)</th>
<th>客単価(円/人)</th>
</tr>
</thead>

<tbody>
HTML;
$g_sale=0;
$g_score=0;
$g_custom=0;
if($monthon){
	$date_sql = "between '{$selyymm}-01' and '{$selyymm}-{$maxday}'";
	$g_width = 2000;
}else{
	$date_sql = "= '$selyymmdd'";
	$g_width = 1000;
}
$sql = "select sale_date,pair_num,max(pair_start),max(pair_end),sum(totalsale1_notax_price),sum(totalsale1_notax_score),sum(num_custom),sum(num_pair),sum(totalsale3_notax_price) from pos_zkkum where pos_shopno = $pos_shopno and sale_date {$date_sql} and status = '1' group by sale_date,pair_num order by sale_date,pair_num";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$graphtxt = "";

for($i=0;$i<$num;$i++){
	list($q_sale_date,$q_pair_num,$q_start_time,$q_end_time,$q_totalsale1_notax_price,$q_totalsale1_notax_score,$q_num_custom,$q_num_pair,$q_totalsale3_notax_price) = pg_fetch_array($result,$i);
	$check_start_time = $q_start_time;
	if($q_pair_num == 999){
		$d_pair_num = $q_start_time."名以上";
	}else{
		$d_pair_num = $q_pair_num."名";
	}
	$d_totalsale1_notax_price = number_format($q_totalsale1_notax_price);
	$d_totalsale1_notax_score = number_format($q_totalsale1_notax_score);
	$d_num_custom = number_format($q_num_custom);
	if($q_num_custom){
		$d_unit_p = number_format(round($q_totalsale1_notax_price / $q_num_custom));
	}else{
		$d_unit_p = "0";
	}
	$g_sale += $q_totalsale1_notax_price;
	$g_score += $q_totalsale1_notax_score;
	$g_custom += $q_num_custom;
$ybase->ST_PRI .= <<<HTML
<tr>
HTML;
	if($check_start_time == 0){
		$d_day = substr($q_sale_date,8,2)."日";
$ybase->ST_PRI .= <<<HTML
<td align="center" rowspan="10">$d_day</td>
HTML;

	}
$ybase->ST_PRI .= <<<HTML
<td align="center">$d_pair_num</td>
<td align="right">$d_totalsale1_notax_price</td>
<td align="right">$d_totalsale1_notax_score</td>
<td align="right">$d_num_custom</td>
<td align="right">$d_unit_p</td>
</tr>
HTML;
$graphtxt .= "data.addRow(['{$d_day}{$d_pair_num}',$q_totalsale1_notax_price]);\n";

}
if($g_custom){
	$g_unit_p = number_format(round($g_sale / $g_custom));
}else{
	$g_unit_p = "0";
}
$g_sale = number_format($g_sale);
$g_score = number_format($g_score);
$g_custom = number_format($g_custom);
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="#dddddd">
<td colspan="2" align="center">合計</td>
<td align="right">$g_sale</td>
<td align="right">$g_score</td>
<td align="right">$g_custom</td>
<td align="right">$g_unit_p</td>
</tr>
</tbody>
</table>
</div>
</div>

<script src="https://www.gstatic.com/charts/loader.js"></script>
<script>
    (function() {
      'use strict';

        // パッケージのロード
        google.charts.load('current', {packages: ['corechart']});
        // コールバックの登録
        google.charts.setOnLoadCallback(drawChart);

        // コールバック関数の実装
        function drawChart() {
            // データの準備
            var data　= new google.visualization.DataTable();
            data.addColumn('string', '時間帯');
            data.addColumn('number', '売上');
		$graphtxt

            // オプションの準備
            var options = {
                title: '{$ybase->title}',
                width: {$g_width},
                height: 600,
		is3D: true
            };


            // 描画用インスタンスの生成および描画メソッドの呼び出し
            var chart = new google.visualization.ColumnChart(document.getElementById('target'));
            chart.draw(data, options);
        }

    })();
  </script>


</div>
<table width="100%">
<tr><td align="center">
<span id="target"></span>
</td></tr>
</table>
</div>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>