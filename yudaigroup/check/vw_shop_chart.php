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
$ybase->make_now_section_list();

if(!isset($MAXCOL)){
$MAXCOL = 4;
}
/////////////////////////////////////////
if(!$ybase->my_position_class || ($ybase->my_position_class > 40)){
	$check->protect = 1;
}

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$param = "t_shop_id=$t_shop_id";
$addsql = "section_id = $t_shop_id";
//////////////////////////////////////////
if($t_shop_id){
	$sql = "select ckaction_id,ckset_id,action_date from ck_check_action where section_id = '$t_shop_id' and status = '1' order by action_date desc limit 50";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$ybase->error("該当するデータがありません");
	}
	for($i=0;$i<$num;$i++){
	list($q_ckaction_id,$q_ckset_id,$q_action_date) = pg_fetch_array($result,$i);
	if($i==0){
		$subject_list_arr = $check->check_set_make($t_shop_id,$q_ckset_id);
		$subject_list_cate_arr = $check->check_set_item_make($subject_list_arr);
	}
		$ckaction_date_list[$q_ckaction_id] = $q_action_date;
	}
/////////////////////データ範囲取得
if($target_action_date_id){
	$add_sql = " and ckaction_id <= $target_action_date_id";
}else{
	$add_sql = "";
}
$sql = "select ckaction_id,ckset_id,action_date,employee_id from ck_check_action where section_id = '$t_shop_id'{$add_sql} and status = '1' order by action_date desc limit $MAXCOL";
$result = $ybase->sql($conn,$sql);
$colnum = pg_num_rows($result);
$kk = 0;
$t_ckaction_id_list = "";
for($i=0;$i<$colnum;$i++){
	list($q_ckaction_id,$q_ckset_id,$q_action_date,$q_employee_id) = pg_fetch_array($result,$i);
	if(!$target_action_date_id && ($i == 0)){
		$target_action_date_id = $q_ckaction_id;
	}
	$t_ckaction_id_arr[$kk] = $q_ckaction_id;
	$t_ckaction_action_date_arr[$q_ckaction_id] = $q_action_date;
	$t_ckaction_employee_arr[$q_ckaction_id] = $q_employee_id;
	if($t_ckaction_id_list){
		$t_ckaction_id_list .= ",";
	}
	$t_ckaction_id_list .= $q_ckaction_id;
	$kk++;
}

//////////////////
/////////////////////データ取得
$maxval = 4;
$sql = "select ckaction_list_id,ckaction_id,item_id,action,com from ck_check_action_list where ckaction_id in ($t_ckaction_id_list) and section_id = '$t_shop_id' and status = '1' order by ckaction_id,item_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	for($i=0;$i<$num;$i++){
	list($q_ckaction_list_id,$q_ckaction_id,$q_item_id,$q_action,$q_com) = pg_fetch_array($result,$i);
	if($maxval < $q_action){
		$maxval = $q_action;
	}
	$t_check_action_arr[$q_ckaction_id][$q_item_id] = $q_action;
//	print "$q_ckaction_id:$q_item_id:$q_action<br>";
	$t_check_action_com_arr[$q_ckaction_id][$q_item_id] = $q_com;
	}
}
/////////
}else{
	foreach($category_list as $key => $val){
		$subject_list_cate_arr[$key]=array();
	}
	$t_ckaction_id_arr=array();
	$ckaction_date_list= array();;
}

//////////////////

$ybase->title = "店舗チェック-店舗別チャート";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("店舗別チャート");
$ybase->ST_PRI .= $check->check_menu("6","$t_shop_id");

$ybase->ST_PRI .= <<<HTML
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js" integrity="sha512-QSkVNOCYLtj73J4hbmVoOV6KVZuMluZlioC+trLpewV8qMjsWqlIQvkn1KGX2StWvPMdWGBqim1xlC8krl1EKQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript">
$(function(){
	$('select').change(function(){
		$("#Form1").submit();
	});
});

</script>
<input type="hidden" name="target_ckaction_id" value="$target_ckaction_id" id="target_ckaction_id">
<input type="hidden" name="target_shop_id" value="$t_shop_id" id="target_shop_id">
<input type="hidden" name="scriptno" value="1" id="scriptno">

<div class="container">
<h5 style="text-align:center;">店舗別チャート</h5>

<p></p>
<form action="./vw_shop_chart.php" method="post" id="Form1">

<div class="row">
<div class="mx-auto">
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
　
<select name="target_action_date_id">
HTML;
foreach($ckaction_date_list as $key => $val){
if($key == $target_action_date_id){
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
から過去<select name="MAXCOL">
HTML;
for($i=1;$i<10;$i++){
if($i == $MAXCOL){
	$selected = " selected";
}else{
	$selected = "";
}

$ybase->ST_PRI .= <<<HTML
<option value="$i"{$selected}>$i</option>
HTML;

}
$ybase->ST_PRI .= <<<HTML
</select>日間
HTML;

$ybase->ST_PRI .= <<<HTML
</div>
</div>
</form>

<p></p>


<p></p>
<table border="0" width="100%">
<tr><td>
</td>
<td align="right">
<nobr>
<!---<a href="check_in.php?$param&target_ckaction_id=$bf_ckaction_id" class="btn btn-sm btn-outline-secondary{$bf_disable}">前回</a>---->
<!---<a href="check_in.php?$param&target_ckaction_id=$nx_ckaction_id" class="btn btn-sm btn-outline-secondary{$nx_disable}">次回</a>---->
</nobr>

</td></tr>
</table>
HTML;

foreach($category_list as $key => $val){
	$title = "$val";
$ybase->ST_PRI .= <<<HTML
<div class="row">
    <div class="col-md-7">

<div class="chart mx-auto" style="width:500px;height:500px;">
<canvas id="myChart{$key}"></canvas>
</div>
HTML;
	$dataset = "";
	foreach($t_ckaction_id_arr as $ckaction_key => $ckaction_id){
		$labels = "";
		$data1 = "";
		$action_list = "";
		$no = 1;
		if(!isset($subject_list_cate_arr[$key])){
			$subject_list_cate_arr[$key] = array();
		}
		foreach($subject_list_cate_arr[$key] as $key2 => $val2){
			$actionval = $t_check_action_arr[$ckaction_id][$key2];
			if(!$actionval){
				$actionval = 0;
			}
			if($labels){
				$labels .= ",";
			}
			$labels .= "'$no'";
			$action_list .= "{$no}.{$val2}<br>";
			$no ++;
			if($data1){
				$data1 .= ",";
			}
			$data1 .= "$actionval";
		}
		$orderno = $MAXCOL - $ckaction_key;
		$action_date = $t_ckaction_action_date_arr[$ckaction_id];
		$action_color = $radar_bgcolor_arr[$ckaction_key];
		if($dataset){
			$dataset .= ",";
		}
		$dataset .= "{
      label: '{$action_date}',
      data: [{$data1}],
      borderColor: '{$action_color}',
      borderWidth: 2,
      }";
	}

$ybase->ST_PRI .= <<<HTML
    </div>
    <div class="col-md-5 d-flex align-items-center" style="font-size:70%;">
$action_list
    </div>
</div>

<script>
var ctx = document.getElementById('myChart{$key}');
var myChart{$key} = new Chart(ctx, {
  type: 'radar',
  data: {
    labels: [{$labels}],
    datasets: [{$dataset}
    ],
  },
  options: {
    plugins: {
        title: {
          display: true,
          text: '$title',
        },
    },
    scales: {
      r: {
        // 最小値・最大値
        min: 0,
        max: {$maxval},
        // 背景色
        backgroundColor: '#ffffee',
        // グリッドライン
        grid: {
          color: 'lightseagreen',
          borderDash:[3,3],
        },
        // アングルライン
        angleLines: {
          color: 'brown',
        },
        // ポイントラベル
        pointLabels: {
          color: '#111111',
          display: true,
          backdropColor: '#ffffff',
          backdropPadding: 2,
          padding: 10,
        },
      },
    },
  },
});
</script>
HTML;


}//category

$ybase->ST_PRI .= <<<HTML

</div>
<p></p>

HTML;



$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>