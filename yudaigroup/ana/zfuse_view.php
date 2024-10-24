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

$ybase->title = "店舗分析-オーダー担当者集計";

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

<form action="zfuse_view.php" method="post" id="form1">
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
<th rowspan="2">日付</th>
<th rowspan="2">オーダー担当従業員</th>
<th colspan="3">オーダー</th>
<th colspan="3">オーダー取消</th>
</tr>
<tr align="center" bgcolor="#eacaca">
<th>回数</th>
<th>客数</th>
<th>金額</th>
<th>回数</th>
<th>客数</th>
<th>金額</th>
</tr>
</thead>

<tbody>
HTML;
$g_count=0;
$g_custom=0;
$g_price=0;
$g_can_count=0;
$g_can_custom=0;
$g_can_price=0;

if($monthon){
	$date_sql = "between '{$selyymm}-01' and '{$selyymm}-{$maxday}'";
	$g_width = 2000;
}else{
	$date_sql = "= '$selyymmdd'";
	$g_width = 1000;
}
$sql = "select sale_date,pos_zfuse_id,order_emp_code,employee_name,order_count,order_custom,order_price,cancel_order_count,cancel_order_custom,cancel_order_price from pos_zfuse where pos_shopno = $pos_shopno and sale_date {$date_sql} and status = '1' and order_price > 0 order by sale_date,pos_zfuse_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$graphtxt = "";

for($i=0;$i<$num;$i++){
	list($q_sale_date,$q_pos_zfuse_id,$q_order_emp_code,$q_employee_name,$q_order_count,$q_order_custom,$q_order_price,$q_cancel_order_count,$q_cancel_order_custom,$q_cancel_order_price) = pg_fetch_array($result,$i);
	$q_employee_name = trim($q_employee_name);
	$d_order_count = number_format($q_order_count);
	$d_order_custom = number_format($q_order_custom);
	$d_order_price = number_format($q_order_price);
	$d_cancel_order_count = number_format($q_cancel_order_count);
	$d_cancel_order_custom = number_format($q_cancel_order_custom);
	$d_cancel_order_price = number_format($q_cancel_order_price);

	$g_count += $q_order_count;
	$g_custom += $q_order_custom;
	$g_price += $q_order_price;
	$g_can_count += $q_cancel_order_count;
	$g_can_custom += $q_cancel_order_custom;
	$g_can_price += $q_cancel_order_price;

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
<td align="center">$q_employee_name</td>
<td align="right">$d_order_count</td>
<td align="right">$d_order_custom</td>
<td align="right">$d_order_price</td>
<td align="right">$d_cancel_order_count</td>
<td align="right">$d_cancel_order_custom</td>
<td align="right">$d_cancel_order_price</td>
</tr>
HTML;
$graphtxt .= "data.addRow(['{$d_day}{$q_employee_name}',$d_order_price]);\n";

}
$g_count = number_format($g_count);
$g_custom = number_format($g_custom);
$g_price = number_format($g_price);
$g_can_count = number_format($g_can_count);
$g_can_custom = number_format($g_can_custom);
$g_can_price = number_format($g_can_price);

$ybase->ST_PRI .= <<<HTML
<tr bgcolor="#dddddd">
<td colspan="2" align="center">合計</td>
<td align="right">$g_count</td>
<td align="right">$g_custom</td>
<td align="right">$g_price</td>
<td align="right">$g_can_count</td>
<td align="right">$g_can_custom</td>
<td align="right">$g_can_price</td>
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