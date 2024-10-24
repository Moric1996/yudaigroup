<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include_once(dirname(__FILE__).'/inc/code_list.inc');

$ybase = new ybase();
$ybase->session_get();
//$edit_f = 1;
/////////////////////////////////////////

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

$ybase->title = "店舗分析-券類集計";

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

<form action="zticket_view.php" method="post" id="form1">
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
<th>券種フラグ名称</th>
<th>値引/割引</th>
<th>券名称</th>
<th>額面額</th>
<th>枚数</th>
<th>回数</th>
<th>金額(円)</th>
</tr>
</thead>

<tbody>
HTML;
$g_sum_price=0;
$g_score=0;
$g_custom=0;
if($monthon){
	$date_sql = "between '{$selyymm}-01' and '{$selyymm}-{$maxday}'";
	$g_width = 2000;
}else{
	$date_sql = "= '$selyymmdd'";
	$g_width = 1000;
}
$sql = "select sale_date,pos_zticket_id,ticket_flag,ticket_flag_name,ticket_code,ticket_price,discount_status,ticket_name,sheet,score,sum_price from pos_zticket where pos_shopno = $pos_shopno and sale_date {$date_sql} and status = '1' order by sale_date,ticket_flag,ticket_code,ticket_price,pos_zticket_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$graphtxt = "";

for($i=0;$i<$num;$i++){
	list($q_sale_date,$q_pos_zticket_id,$q_ticket_flag,$q_ticket_flag_name,$q_ticket_code,$q_ticket_price,$q_discount_status,$q_ticket_name,$q_sheet,$q_score,$q_sum_price) = pg_fetch_array($result,$i);
	$q_ticket_flag_name = trim($q_ticket_flag_name);
	$q_ticket_name = trim($q_ticket_name);
	$d_ticket_price = number_format($q_ticket_price);
	$d_sum_price = number_format($q_sum_price);
	if($q_discount_status == 0){
		$d_ticket_price = $d_ticket_price."円";
	}elseif($q_discount_status == 1){
		$d_ticket_price = $d_ticket_price."％";
	}
	$g_sum_price += $q_sum_price;
	$g_score += $q_score;
$ybase->ST_PRI .= <<<HTML
<tr>
HTML;
if($q_sale_date != $pre_sale_date){
	$d_day = substr($q_sale_date,8,2)."日";
}else{
	$d_day = "";
}
$pre_sale_date = $q_sale_date;
$ybase->ST_PRI .= <<<HTML
<td align="center">$d_day</td>
HTML;
$ybase->ST_PRI .= <<<HTML
<td align="center">$q_ticket_flag_name</td>
<td align="right">{$discount_status_list[$q_discount_status]}</td>
<td align="right">$q_ticket_name</td>
<td align="right">$d_ticket_price</td>
<td align="right">$q_sheet</td>
<td align="right">$q_score</td>
<td align="right">$d_sum_price</td>
</tr>
HTML;
$graphtxt .= "data.addRow(['{$d_day}{$q_ticket_name}({$q_ticket_price})',$q_sum_price]);\n";

}
$g_sum_price = number_format($g_sum_price);
$g_score = number_format($g_score);
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="#dddddd">
<td colspan="6" align="center">合計</td>
<td align="right">$g_score</td>
<td align="right">$g_sum_price</td>
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