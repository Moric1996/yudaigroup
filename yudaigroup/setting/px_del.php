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

//未登録チェック
$sql = "select employee_id,employee_num,employee_name,kana_name,sex,company_id,section_id,employee_type from employee_list where status = '1' and (nodel_flag is null or nodel_flag <> 1) and employee_num not in (select employee_num from employee_in_data) order by employee_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);


$ybase->title = "従業員情報更新";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("従業員情報更新");


$ybase->ST_PRI .= <<<HTML
<div class="container">
<p></p>

退職の可能性があるデータ【{$num}】件<br><br>
退職処理をする場合は退職対象欄にチェックをつけて退職処理実行ボタンを押してください。<br>
「今後除く」の欄にチェックをすると以後の退職処理の対象から除かれます。<br>
<form action="px_del_ex.php" method="post" id="add_form">
<table class="table table-bordered table-hover table-sm" style="font-size:85%;">
  <thead>
    <tr align="center" class="table-primary">
      <th scope="col">退職<br>対象欄</th>
      <th scope="col">今後<br>除く</th>
      <th scope="col">社員番号</th>
      <th scope="col">氏名</th>
      <th scope="col">性別</th>
      <th scope="col">所属部署・店舗</th>
      <th scope="col">雇用区分</th>
    </tr>
  </thead>
  <tbody>

HTML;

for($i=0;$i<$num;$i++){
	list($q_employee_id,$q_employee_num,$q_employee_name,$q_kana_name,$q_sex,$q_company_id,$q_section_id,$q_employee_type) = pg_fetch_array($result,$i);
	$q_employee_num = trim($q_employee_num);
	$q_employee_name = trim($q_employee_name);
	$q_kana_name = trim($q_kana_name);
	$q_sex = trim($q_sex);
	$q_section_id = trim($q_section_id);
	$q_employee_type = trim($q_employee_type);


$ybase->ST_PRI .= <<<HTML

<tr align="center">
<td>
<input type="checkbox" name="del_data_id[]" value="$q_employee_id" id="del_data_id_{$q_employee_id}">
</td>
<td>
<input type="checkbox" name="no_target[]" value="$q_employee_id" id="no_target_{$q_employee_id}">
</td>
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
<td>
{$ybase->employee_type_list[$q_employee_type]}
</td>
</tr>
HTML;
}

$ybase->ST_PRI .= <<<HTML
  </tbody>
</table><br>
<div class="text-center">
<button type="sumit" class="btn btn-outline-info btn-sm" role="button">退職処理実行</button>
</div>
</form>


<a href="./px_ck.php">戻る</a><br><br>

HTML;

$ybase->HTMLfooter();
$ybase->priout();

////////////////////////////////////////////////
?>