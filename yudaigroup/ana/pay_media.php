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

$conn = $ybase->connect();

$ybase->title = "店舗分析-支払いメディア";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML
<script>
$(function(){
	$('#inshop_id,#inmonth').change(function(){
		$("#form1").submit();
	});
});

</script>
<div class="container">

<p></p>
<div style="text-align:right;"><a class="btn btn-secondary btn-sm" href="./ana_top.php?$param" role="button">戻る</a></div>
<div style="font-size:100%;margin:5px;">

<form action="pay_media.php" method="post" id="form1">
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
<input type="date" name="selyymmdd" value="$selyymmdd" id="inmonth">
</form>
<p></p>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">

<thead>
<tr align="center" bgcolor="#eacaca">
<th>項目名</th>
<th>金額(税込)</th>
<th>回数</th>
<th>割合(%)</th>
</tr>
</thead>

<tbody>
HTML;
$sql = "select sum(pay_price),sum(pay_count) from pos_ztotal_pay where pos_shopno = $pos_shopno and sale_date = '$selyymmdd' and status = '1' and pay_price > 0";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	list($sum_price,$sum_count) = pg_fetch_array($result,0);
}

$sql = "select pos_ztotal_pay_id,ztotal_no,pay_item_name,pay_price,pay_count,pay_sheet from pos_ztotal_pay where pos_shopno = $pos_shopno and sale_date = '$selyymmdd' and status = '1' and pay_price > 0 order by pay_price desc";
//print $sql;
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$graphtxt = "";

for($i=0;$i<$num;$i++){
	list($q_pos_ztotal_pay_id,$q_ztotal_no,$q_pay_item_name,$q_pay_price,$q_pay_count,$q_pay_sheet) = pg_fetch_array($result,$i);
	$rate = round($q_pay_price/$sum_price*100, 2);
	$q_pay_item_name = trim($q_pay_item_name);
	$num_price = number_format($q_pay_price);
	$num_count = number_format($q_pay_count);
$ybase->ST_PRI .= <<<HTML
<tr>
<td>$q_pay_item_name</td>
<td align="right">$num_price</td>
<td align="right">$num_count</td>
<td align="right">$rate</td>
</tr>
HTML;
$graphtxt .= "data.addRow(['$q_pay_item_name',$q_pay_price]);\n";

}

$num_price = number_format($sum_price);
$num_count = number_format($sum_count);
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="#dddddd">
<td>合計</td>
<td align="right">$num_price</td>
<td align="right">$num_count</td>
<td align="right">100.00</td>
</tr>
</tbody>
</table>
</div>
</div>
<div id="target"></div>

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
            data.addColumn('string', 'メディア');
            data.addColumn('number', '金額');
		$graphtxt

            // オプションの準備
            var options = {
                title: '{$ybase->title}',
                width: 1000,
                height: 400,
		is3D: true
            };


            // 描画用インスタンスの生成および描画メソッドの呼び出し
            var chart = new google.visualization.PieChart(document.getElementById('target'));
            chart.draw(data, options);
        }

    })();
  </script>


</div>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>