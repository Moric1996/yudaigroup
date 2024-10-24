<?php
include("../../inc/auth.inc");
include("../base.inc");
include("../item_list.inc");
if($ybase->my_admin_auth != "1"){
	$ybase->error("権限がありません");
}

$base            = new base();
if(!$number){
	$number = $MS_NUMBER;
}
$conn = $base->connect();

$sql = "select entry_id,belong,employee_type,to_char(add_date,'YYYY/MM/DD'),ans0101,ans0102,ans0103,com0101,com0102,com0103,com0104 from answerdatakekki2019 where number = $number and comp_flag = '$MAIN_ITEM_CNT'";
if(preg_match("/^[0-9]+$/",$sel_employee_type)){
	$sql .= " and employee_type = '$sel_employee_type'";
}
if(preg_match("/^[0-9]+$/",$sel_belong)){
	$sql .= " and belong = '$sel_belong'";
}
$sql .= " order by belong,entry_id";
$result = $base->sql($conn,$sql);
$num = pg_num_rows($result);

$base->hd("雄大グループ決起大会アンケート確認ページ");

if($number == 1){
	$selected1 = " selected";
	$selected2 = "";
}elseif($number == 2020){
	$selected1 = "";
	$selected2 = " selected";
}

$base->ST_PRI .= <<<HTML

<div class="main">雄大グループ</div>
<div class="main">決起大会アンケート</div>
<div class="main">入力者一覧</div>
<hr>

<form action="" method="post">
開催年
<select name="number" onchange="submit(this.form)">
<option value="1"{$selected1}>2019年</option>
<option value="2020"{$selected2}>2020年</option>
</select>
<hr>
対象雇用形態：
<select name="sel_employee_type" onchange="submit(this.form)">
<option value="">全て</option>
HTML;
reset($employee_type_arr);
while(list($key,$val)=each($employee_type_arr)){
if($sel_employee_type == $key){
	$selected = " selected";
}else{
	$selected = "";
}
$base->ST_PRI .= <<<HTML
	
<option value="$key"$selected>$val</option>

HTML;
}
$base->ST_PRI .= <<<HTML
</select>

所属：
<select name="sel_belong" onchange="submit(this.form)">
<option value="">全て</option>
HTML;
reset($belong_arr);
while(list($key,$val)=each($belong_arr)){
if($sel_belong == $key){
	$selected = " selected";
}else{
	$selected = "";
}
$base->ST_PRI .= <<<HTML
	
<option value="$key"$selected>$val</option>

HTML;
}
$base->ST_PRI .= <<<HTML
</select>

</form>

<hr>
該当者 {$num}人
<hr>
<table border="1">
<tr bgcolor="#eeffee">
<th rowspan="2">所属</th>
<th rowspan="2">雇用形態</th>
<th rowspan="2">入力日</th>
<th colspan="2">決起大会（1部）</th>
<th colspan="2">遊楽道（2部）</th>
<th colspan="2">懇親会（3部）</th>
<th rowspan="2">フリーコメント</th>
<th rowspan="2">削除</th>
</tr>
<tr bgcolor="#ddffdd">
<th>回答</th>
<th>理由</th>
<th>回答</th>
<th>理由</th>
<th>回答</th>
<th>理由</th>
</tr>

HTML;

$kaitosu1[1]=0; 
$kaitosu1[2]=0; 
$kaitosu1[3]=0; 

$kaitosu2[1]=0; 
$kaitosu2[2]=0; 
$kaitosu2[3]=0; 

$kaitosu3[1]=0; 
$kaitosu3[2]=0; 
$kaitosu3[3]=0; 

for($i=0;$i<$num;$i++){
        list($q_entry_id,$q_belong,$q_employee_type,$q_add_date,$q_ans0101,$q_ans0102,$q_ans0103,$q_com0101,$q_com0102,$q_com0103,$q_com0104) = pg_fetch_array($result,$i);
	$kaitosu1[$q_ans0101]++;
	$kaitosu2[$q_ans0102]++;
	$kaitosu3[$q_ans0103]++;

	$ans0101=$option_arr[101][$q_ans0101];
	$ans0102=$option_arr[102][$q_ans0102];
	$ans0103=$option_arr[103][$q_ans0103];
if($i%2){
	$bgc="#ffffff";
}else{
	$bgc="#f7f7fa";
}
$q_com0101 = ereg_replace("([\r|\n|\r\n]+)","<br>",$q_com0101);
$q_com0102 = ereg_replace("([\r|\n|\r\n]+)","<br>",$q_com0102);
$q_com0103 = ereg_replace("([\r|\n|\r\n]+)","<br>",$q_com0103);
$q_com0104 = ereg_replace("([\r|\n|\r\n]+)","<br>",$q_com0104);

$base->ST_PRI .= <<<HTML

<tr bgcolor="$bgc">
<td>$belong_arr[$q_belong]</td>
<td>$employee_type_arr[$q_employee_type]</td>
<td>$q_add_date</td>
<td>$ans0101</td>
<td>$q_com0101</td>
<td>$ans0102</td>
<td>$q_com0102</td>
<td>$ans0103</td>
<td>$q_com0103</td>
<td>$q_com0104</td>
<td><a href="./kanridel.php?entry_id=$q_entry_id" onclick="return confirm('データ削除します。よろしいですか？')">削除</a></td>
</tr>

HTML;

}

$base->ST_PRI .= <<<HTML

</table>
<hr>
<table border="0">
<tr>
<td>
<div id="target"></div>
</td>
<td>
<div id="target2"></div>
</td>
<td>
<div id="target3"></div>
</td>
</tr>
</table>

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
            data.addColumn('string', '回答');
            data.addColumn('number', '回答数');
            data.addRow(['満足', $kaitosu1[1]]);
            data.addRow(['普通', $kaitosu1[2]]);
            data.addRow(['不満', $kaitosu1[3]]);

            // データの準備
            var data2　= new google.visualization.DataTable();
            data2.addColumn('string', '回答');
            data2.addColumn('number', '回答数');
            data2.addRow(['満足', $kaitosu2[1]]);
            data2.addRow(['普通', $kaitosu2[2]]);
            data2.addRow(['不満', $kaitosu2[3]]);

            // データの準備
            var data3　= new google.visualization.DataTable();
            data3.addColumn('string', '回答');
            data3.addColumn('number', '回答数');
            data3.addRow(['満足', $kaitosu3[1]]);
            data3.addRow(['普通', $kaitosu3[2]]);
            data3.addRow(['不満', $kaitosu3[3]]);

            // オプションの準備
            var options = {
                title: '決起大会（1部）',
                width: 500,
                height: 300,
		is3D: true
            };

            // オプションの準備
            var options2 = {
                title: '遊楽道（2部）',
                width: 500,
                height: 300,
		is3D: true
            };

            // オプションの準備
            var options3 = {
                title: '懇親会（3部）',
                width: 500,
                height: 300,
		is3D: true
            };

            // 描画用インスタンスの生成および描画メソッドの呼び出し
            var chart = new google.visualization.PieChart(document.getElementById('target'));
            chart.draw(data, options);
            var chart = new google.visualization.PieChart(document.getElementById('target2'));
            chart.draw(data2, options2);
            var chart = new google.visualization.PieChart(document.getElementById('target3'));
            chart.draw(data3, options3);

        }


    })();
  </script>



HTML;

$base->ft();
$base->priout();
?>