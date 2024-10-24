
<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<link rel="icon" href="https://yournet-jp.com/yudaigroup/portal/img/yudai_favicon.ico">
<link rel="apple-touch-icon" href="https://yournet-jp.com/yudaigroup/portal/img/yudai_favicon.ico" />
<title>アンケート集計</title>
<link href="/yudaigroup/inc/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</head>
<body><header>
<nav class="navbar navbar-expand-md navbar-dark" style="background-color: #63baa8;">
<a class="btn btn-outline-light btn-sm" href="https://yournet-jp.com/yudaigroup/portal/index.php" role="button">TOPへ</a> 
　<a class="navbar-brand" href="https://yournet-jp.com/yudaigroup/portal/index.php">雄大グループ業務管理ポータル</a>　アンケート集計
</nav>
</header>
<script>
$(function(){
	$('#insurvey_set_id,#inmonth,#ttype,#monthon,#sel_p_week').change(function(){
		$("#form1").submit();
	});
});

</script>
<div class="container">
  <ul class="nav nav-tabs nav-fill" id="myTab" style="font-size:70%;">
    <li class="nav-item">
      <a href="./survey_list.php" class="nav-link">アンケート一覧</a>
    </li>
    <li class="nav-item">
      <a href="./ans_list.php" class="nav-link">アンケート詳細</a>
    </li>
    <li class="nav-item">
      <a href="./ans_ana.php" class="nav-link">アンケート集計</a>
    </li>
    <li class="nav-item dropdown">
	<a class="nav-link dropdown-toggle" data-toggle="dropdown"  href="#" role="button" aria-haspopup="true" aria-expanded="false">集計グラフ</a>
	<div class="dropdown-menu">
	<a class="dropdown-item active" href="./ans_graf_sum.php">回数</a>
	<a class="dropdown-item" href="./ans_graf_nps.php">NPS</a>
	<a class="dropdown-item" href="./ans_graf_day.php">日別回数</a>
	</div>

    </li>
    <li class="nav-item">
      <a href="log_view.php" class="nav-link">ログ</a>
    </li>
  </ul>
</div>


<div class="container">

<p></p>
<div style="font-size:80%;margin:5px;">

<form action="ans_ana.php" method="post" id="form1"></select>　

<input type="month" name="selyymm" value="2021-05" id="inmonth">
　
<select name="p_week" id="sel_p_week"><option value="0">全日数</option><option value="1">1週(1-7日)</option><option value="2">2週(8-14日)</option><option value="3">3週(15-21日)</option><option value="4">4週(22-28日)</option><option value="5">5週(29-末日)</option></select>　

</form>
<p></p>

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
            data.addColumn('string', '店舗');
            data.addColumn('number', '回答数');
		data.addRow(['吉祥庵愛知東郷1',310]);
data.addRow(['赤から富士1',152]);
data.addRow(['甲羅富士1',138]);
data.addRow(['カルビ大仁1',104]);
data.addRow(['甲羅沼津1',85]);
data.addRow(['赤から三島1',57]);
data.addRow(['えびす家富士1',30]);
data.addRow(['フェスタ1',28]);
data.addRow(['松福静岡1',16]);
data.addRow(['赤から函南1',13]);
data.addRow(['ふたご1',11]);
data.addRow(['ラジオ三島1',10]);
data.addRow(['イマさん静岡1',10]);
data.addRow(['串屋物語1',9]);
data.addRow(['赤から沼津1',8]);
data.addRow(['カルビ御殿場1',7]);
data.addRow(['カルビ沼津1',6]);
data.addRow(['ラジオ函南1',4]);
data.addRow(['ラジオ沼津1',3]);
data.addRow(['赤から御殿場1',3]);
data.addRow(['ラジオ御殿場1',2]);
data.addRow(['ゆうが三島1',2]);
data.addRow(['ゆうが沼津1',2]);
data.addRow(['イマさん沼津1',1]);
data.addRow(['吉祥庵沼津1',1]);
data.addRow(['VANSAN浜松1',0]);


            // オプションの準備
            var options = {
                title: 'アンケート回答数',
                width: 1200,
                height: 600,
		hAxis: {maxTextLines:1,maxValue:100},
		chartArea: {width:'90%',height:'75%',top:10},
		legend: 'none'
		};

            // 描画用インスタンスの生成および描画メソッドの呼び出し
            var chart = new google.visualization.ColumnChart(document.getElementById('grafsum'));
            chart.draw(data, options);
        }

    })();
</script>

<table>
<tr><td align="center">
<span id="grafsum"></span>
</td></tr>
</table>

</div>



</div>
</div>
<p></p>

<footer style="background-color:#63baa8;color:#ffffff">YUDAI Group System</footer>
</body></html>