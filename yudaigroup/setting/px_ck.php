<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

mb_language("Ja");
mb_internal_encoding("utf-8");
////////////エラーチェック

$error_flag = 0;
$space = array();

//////////////////////////////////////////////////
$conn = $ybase->connect();

for($i=0;$i<10;$i++){
	$comp_count[$i] = 0;
}
$update_cnt = 0;
$sql = "select comp_status,count(*) from employee_in_data group by comp_status order by comp_status";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	for($i=0;$i<$num;$i++){
		list($q_comp_status,$q_cnt) = pg_fetch_array($result,$i);
		$comp_count[$q_comp_status] = $q_cnt;
		$update_cnt += $q_cnt;
	}
}

//未登録チェック
$sql = "select employee_in_data_id,employee_num,employee_name,kana_name,sex,birthday,indate,company_id,section_id,employee_type,position_name,position_class,email,add_date,comp_status from employee_in_data where comp_status = '0' and employee_num not in (select employee_num from employee_list where status = '1') order by employee_in_data_id";
$result = $ybase->sql($conn,$sql);
$num_add = pg_num_rows($result);

//変更チェック
$sql = "select a.employee_in_data_id,a.employee_num,a.employee_name,b.employee_id from employee_in_data as a inner JOIN employee_list as b ON a.employee_num = b.employee_num where a.comp_status = '0' and ((a.section_id <> b.section_id) or (a.employee_type <> b.employee_type) or (a.employee_name <> b.employee_name) or (a.kana_name <> b.kana_name))";
$result = $ybase->sql($conn,$sql);
$num_update = pg_num_rows($result);

//退職チェック
$sql = "select employee_id,employee_num,employee_name,kana_name,sex,birthday,indate,company_id,section_id,employee_type,position_name,position_class,email,add_date,status from employee_list where status = '1' and (nodel_flag is null or nodel_flag <> 1) and employee_num not in (select employee_num from employee_in_data) order by employee_id";
$result = $ybase->sql($conn,$sql);
$num_del = pg_num_rows($result);

$all_add_num = $num_add + $comp_count[1] + $comp_count[4];
$all_update_num = $num_update + $comp_count[2] + $comp_count[5];
if($num_add){
	$num_add_disbaled="";
	$num_add_color="info";
}else{
	$num_add_disbaled=" disabled";
	$num_add_color="secondary";
}
if($num_update){
	$num_update_disbaled="";
	$num_update_color="info";
}else{
	$num_update_disbaled=" disabled";
	$num_update_color="secondary";
}
if($num_del){
	$num_del_disbaled="";
	$num_del_color="info";
}else{
	$num_del_disbaled=" disabled";
	$num_del_color="secondary";
}

$ybase->title = "従業員情報更新";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("従業員情報更新");


$ybase->ST_PRI .= <<<HTML
<div class="container">
<p></p>
<br>
<div class="row m-3">
  <div class="col text-center">
  全アップロードデータ合計【{$update_cnt}】件
  </div>
</div>
<br><br>

<div class="row m-1">
  <div class="col border" style="background-color:#eeeeff;">
アップロードデータ中、未登録のデータ【<b>{$all_add_num}</b>】件<br>
  </div>
</div>
<div class="row">
  <div class="col-lg-2 offset-lg-1">
そのうち
  </div>
</div>
<div class="row">
  <div class="col-lg-3 offset-lg-2">
未処理のデータ[<b>{$num_add}</b>]件
  </div>
  <div class="col-lg-3">
登録処理済データ[<b>{$comp_count[1]}</b>]件
  </div>
  <div class="col-lg-3">
登録しないデータ[<b>{$comp_count[4]}</b>]件
  </div>
</div>
<div class="row m-4">
  <div class="col text-center">
<a href="./px_add.php" class="btn btn-{$num_add_color} btn{$num_add_disbaled}">未処理のデータ一覧表示へ</a>
  </div>
</div>

<br><br>

<div class="row m-1">
  <div class="col border" style="background-color:#ffffee;">
アップロードデータ中、登録データと差異のあるデータ【<b>{$all_update_num}</b>】件<br>
  </div>
</div>
<div class="row">
  <div class="col-lg-2 offset-lg-1">
そのうち
  </div>
</div>
<div class="row">
  <div class="col-lg-3 offset-lg-2">
未処理のデータ[<b>{$num_update}</b>]件
  </div>
  <div class="col-lg-3">
変更処理済データ[<b>{$comp_count[2]}</b>]件
  </div>
  <div class="col-lg-3">
変更しないデータ[<b>{$comp_count[5]}</b>]件
  </div>
</div>
<div class="row m-4">
  <div class="col text-center">
<a href="./px_update.php" class="btn btn-{$num_update_color} btn{$num_update_disbaled}">未処理のデータ一覧表示へ</a>
  </div>
</div>


<br><br>

<div class="row m-1">
  <div class="col border" style="background-color:#ffeeee;">
退職の可能性があるデータ【<b>{$num_del}</b>】件<br>
  </div>
</div>
<div class="row">
  <div class="col-lg offset-lg-1">
<small>※現在登録のある従業員データのうち、アップロードデータにないデータ件数です。</small>
  </div>
</div>

<div class="row m-4">
  <div class="col text-center">
<a href="./px_del.php" class="btn btn-{$num_del_color} btn{$num_del_disbaled}">退職の可能性があるデータ一覧表示へ</a>
  </div>
</div>

<br>
  <div class="col text-center">
<a href="./employee_in_del.php" class="btn btn-primary m-5">全処理完了</a>
  </div>

<br><br>

<a href="./new_employee.php">新規ユーザーアカウント作成画面に戻る</a><br><br>
</div>
HTML;

$ybase->HTMLfooter();
$ybase->priout();

////////////////////////////////////////////////
?>