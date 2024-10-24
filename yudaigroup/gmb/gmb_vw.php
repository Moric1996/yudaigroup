<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
if(isset($_POST)){
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
}
if(isset($_GET)){
	foreach($_GET as $key => $value){
		${$key} = $value;
	}
}
///////////////////////////////////////////////////////////////
if(!isset($sess0)){
	$sess0 = '';
}
if(!isset($mei0)){
	$mei0 = '';
}
if(!isset($selyymm)){
	$selyymm = '';
}
if(!isset($selyymmdd)){
	$selyymmdd = '';
}
if(!isset($yy)){
	$yy = '';
}
if(!isset($mm)){
	$mm = '';
}
if(!isset($dd)){
	$dd = '';
}
$company_id = 1;
///////////////////////////////////////////////////////////////
include(dirname(__FILE__).'/../../vendor/autoload.php');
include(dirname(__FILE__).'/../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get($sess0,$mei0);
//$edit_f = 1;
/////////////////////////////////////////
$ybase->make_shop_list();
//$ybase->shop_list['3001'] = "雄大ゴルフ熱函";	// 雄大ゴルフ熱函
//$ybase->shop_list['3002'] = "雄大ゴルフ清水町";	//雄大ゴルフ清水町
$monthon = 1;//月単位で
$ybase->make_employee_list("1");


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
$selyymm = "$yy-$mm";
}

$shop_id = 101;
$company_id = 1;
$conn = $ybase->connect(2);



$ybase->title = "GMBレビュー";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$sql = "select locationid from gmb_location where shop_id = $shop_id and company_id = $company_id and status = '1' order by add_date desc";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$sql2 = "select locationid from gmb_location where shop_id is null and company_id = $company_id and status = '1' order by add_date desc";
	$result2 = $ybase->sql($conn,$sql2);
	$num2 = pg_num_rows($result2);
	if(!$num2){
		$sql3 = "select guserid from gmb_guser where company_id = $company_id and status = '1' order by add_date desc";
		$result3 = $ybase->sql($conn,$sql3);
		$num3 = pg_num_rows($result3);
		if(!$num3){
			$notice = "まだGMB用グーグルアカウントが登録されていません。<br>以下ボタンを押して、グーグルアカウントの認証・登録をしてください。<br><br><a href=\"./gmb_regi.php\" class=\"btn btn-primary btn-lg\" role=\"button\">グーグルアカウントの認証・登録へ</a>";
		}else{
			$notice = "選択した店舗のGMB用グーグルアカウントが登録されていません。<br>以下ボタンを押して、選択した店舗を管理するグーグルアカウントの認証・登録をしてください。<br><br><a href=\"./gmb_regi.php\" class=\"btn btn-primary btn-lg\" role=\"button\">グーグルアカウントの認証・登録へ</a>";
		}
	}else{
		$notice = "選択した店舗のデータがみつかりません。<br>店舗の関連付けが完了していないGMBデータがありますので、関連付けをするか、選択した店舗のグーグルアカウントの認証・登録をしてください。<br><br><a href=\"./gmb_shop_select.php\" class=\"btn btn-primary btn-lg\" role=\"button\">店舗の関連付けへ</a><br><br><a href=\"./gmb_regi.php\" class=\"btn btn-primary btn-lg\" role=\"button\">グーグルアカウントの認証・登録へ</a>";
	}
$ybase->ST_PRI .= <<<HTML
<div class="container">
<p></p>
<div class="text-center mb-3">
<br>
$notice
</div>
<p></p>
</div>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
exit;
}
list($q_locationid) = pg_fetch_array($result,0);

$sql = "select to_char(createTime,'YYYY-MM'),sum(starRating),count(*) from gmb_location where locationid = '$q_locationid' and company_id = $company_id and starRating is not null group by to_char(createTime,'YYYY-MM') order by to_char(createTime,'YYYY-MM')";
//$sql = "select gmb_review_id,reviewId,locationid,nametitle,profilePhotoUrl,displayName,starRating,comment,createTime,updateTime,Replycomment,ReplyupdateTime from gmb_location where locationid = '$q_locationid' and company_id = $company_id and starRating is not null order by add_date desc";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

if(!$num){
	$notice = "レビューデータがまだありません";
}else{
	$notice = "";
}
$total_sumstarRating = 0;
$total_cnt = 0;
$total_month_starRating = array();
$only_month_starRating = array();
$total_month_cnt = array();
$only_month_cnt = array();

for($i=0;$i<$num;$i++){
	list($q_createTime,$q_sumstarRating,$q_cnt) = pg_fetch_array($result,$i);
	$total_sumstarRating += $q_sumstarRating;
	$total_cnt += $q_cnt;
	$total_month_starRating[$q_createTime] = round($total_sumstarRating / $total_cnt,1);
	$only_month_starRating[$q_createTime] = round($q_sumstarRating / $q_cnt,1);
	$total_month_cnt[$q_createTime] = $total_cnt;
	$only_month_cnt[$q_createTime] = $q_cnt;
}



$dataset = "";
$labels = "";
$data1 = "";
$data2 = "";
$data3 = "";
$action_list = "";
foreach($total_month_starRating as $key => $val){
	if($labels){
		$labels .= ",";
	}
	if($data1){
		$data1 .= ",";
	}
	if($data2){
		$data2 .= ",";
	}
	if($data3){
		$data3 .= ",";
	}
	$labels .= "'$key'";
	$data1 .= "{$only_month_cnt[$key]}";
	$data2 .= "{$val}";
	$data3 .= "{$only_month_starRating[$key]}";
}

	$dataset .= "{
	label: '件数',
	data: [{$data1}],
	borderColor : 'rgba(254,97,132,0.8)',
	backgroundColor : 'rgba(254,97,132,0.5)',
	},
	{
	label: '評価(トータル)',
	data: [{$data1}],
	borderColor : 'rgba(54,164,235,0.8)',
	backgroundColor : 'rgba(54,164,235,0.5)',
	},
	{
	label: '評価(月毎)',
	data: [{$data1}],
	borderColor : 'rgba(54,235,132,0.8)',
	backgroundColor : 'rgba(54,235,132,0.5)',
	},";


$ybase->ST_PRI .= <<<HTML
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js" integrity="sha512-QSkVNOCYLtj73J4hbmVoOV6KVZuMluZlioC+trLpewV8qMjsWqlIQvkn1KGX2StWvPMdWGBqim1xlC8krl1EKQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
</script>

<div class="container">
<p></p>
<div class="text-center mb-3">
$notice
</div>
<form method="post" action="gmb_vw.php">
</form>

<div class="row">
    <div class="col-md-7">

<div class="chart mx-auto" style="width:500px;height:500px;">
<canvas id="myChartRating"></canvas>
</div>

</div>
</div>

<script>
var ctx = document.getElementById('myChartRating');
var myChartRating = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: [{$labels}],
    datasets: [{$dataset}
    ],
  },
  options: {
	responsive: true,
  },
});
</script>


<table class="table table-bordered">

HTML;
/*
for($i=0;$i<$num;$i++){
	list($q_locationid,$q_title) = pg_fetch_array($result,$i);


$ybase->ST_PRI .= <<<HTML
    <tr align="center">
      <td>$q_title</td>
      <td><select name="gmbshop[$q_locationid]">
<option value="">選択してください</option>

HTML;
foreach($ybase->shop_list as $key => $val){


$ybase->ST_PRI .= <<<HTML
<option value="$key">$val</option>
HTML;

}
$ybase->ST_PRI .= <<<HTML
</select>
</td>
    </tr>
HTML;

}




*/

$ybase->ST_PRI .= <<<HTML
<div class="text-center">
</div>
<p></p>
</div>




HTML;
$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>