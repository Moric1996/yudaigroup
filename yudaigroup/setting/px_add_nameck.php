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
if(!preg_match("/^[0-9]+$/i",$t_employee_in_data_id)){
	$ybase->error("パラメータエラー。ERROR_CODE:541269");
}
//////////////////////////////////////////////////
$conn = $ybase->connect();

//未登録チェック
$sql = "select replace(replace(employee_name,'　',''),' ','') from employee_in_data where employee_in_data_id = $t_employee_in_data_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データが検索できませんでした。ERROR_CODE:541268");
}
list($ck_employee_name) = pg_fetch_array($result,0);
$ck_employee_name = trim($ck_employee_name);

$sql = "select employee_id,employee_num,employee_name,sex,company_id,section_id from employee_list where status = '1' and replace(replace(employee_name,'　',''),' ','') = '$ck_employee_name'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$notice = "<div class=\"text-center m-4\">同じ名前の登録はみつかりませんでした。</div>";
}




$ybase->title = "従業員情報更新";

$ybase->HTMLheader();


$ybase->ST_PRI .= <<<HTML
<div class="container">
<p></p>
<br><br><br>
同じ名前のデータ【{$num}】件<br><br>
<table class="table table-bordered table-hover table-sm" style="font-size:85%;">
  <thead>
    <tr align="center" class="table-primary">
      <th scope="col">社員番号</th>
      <th scope="col">氏名</th>
      <th scope="col">性別</th>
      <th scope="col">所属部署・店舗</th>
    </tr>
  </thead>
  <tbody>

HTML;

for($i=0;$i<$num;$i++){
	list($q_employee_id,$q_employee_num,$q_employee_name,$q_sex,$q_company_id,$q_section_id) = pg_fetch_array($result,$i);
	$q_employee_num = trim($q_employee_num);
	$q_kana_name = trim($q_employee_name);
	$q_sex = trim($q_sex);
	$q_section_id = trim($q_section_id);
$ybase->ST_PRI .= <<<HTML

<tr align="center">
<td>
$q_employee_num
</td>
<td>
$q_employee_name
</td>
<td>
{$ybase->sex_list[$q_sex]}
</td>
<td>
{$ybase->section_list[$q_section_id]}
</td>
</tr>
HTML;
}

$ybase->ST_PRI .= <<<HTML
  </tbody>
</table><br>
$notice
<p>
<div class="text-center"><a href="#" onClick="window.close(); return false;" class="btn btn-secondary">ウィンドウを閉じる</a></div></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();

////////////////////////////////////////////////
?>