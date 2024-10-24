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
$sql = "select employee_in_data_id,employee_num,employee_name,kana_name,sex,birthday,indate,company_id,section_id,employee_type,position_name,position_class,email from employee_in_data where comp_status = '0' and employee_num not in (select employee_num from employee_list where status = '1') order by employee_in_data_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);


$ybase->title = "従業員情報更新";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("従業員情報更新");


$ybase->ST_PRI .= <<<HTML
<div class="container">
<p></p>

未処理のデータ【{$num}】件<br><br>
新規追加しないデータはチェックをはずしてください。<br>
<form action="px_add_ex.php" method="post" id="add_form">
<table class="table table-bordered table-hover table-sm" style="font-size:85%;">
  <thead>
    <tr align="center" class="table-primary">
      <th scope="col">対象</th>
      <th scope="col">社員番号</th>
      <th scope="col">氏名</th>
      <th scope="col">カナ</th>
      <th scope="col">性別</th>
      <th scope="col">誕生日</th>
      <th scope="col">入社日</th>
      <th scope="col">所属部署・店舗</th>
      <th scope="col">雇用区分</th>
      <th scope="col">役職区分</th>
      <th scope="col">権限</th>
    </tr>
  </thead>
  <tbody>

HTML;

for($i=0;$i<$num;$i++){
	list($q_employee_in_data_id,$q_employee_num,$q_employee_name,$q_kana_name,$q_sex,$q_birthday,$q_indate,$q_company_id,$q_section_id,$q_employee_type,$q_position_name,$q_position_class,$q_email) = pg_fetch_array($result,$i);
	$q_employee_name = trim($q_employee_name);
	$q_kana_name = trim($q_kana_name);
	$q_sex = trim($q_sex);
	$q_birthday = trim($q_birthday);
	$q_indate = trim($q_indate);
	$q_company_id = trim($q_company_id);
	$q_section_id = trim($q_section_id);
	$q_employee_type = trim($q_employee_type);
	$q_position_name = trim($q_position_name);
	$q_position_class = trim($q_position_class);
	$q_email = trim($q_email);
	if(!$q_position_class){
		$q_position_class = 90;
	}
	switch ($q_position_class){
		case 10:
		case 20:
		case 30:
			$new_admin_auth = 1;
			break;
		case 40:
			$new_admin_auth = 2;
			break;
		case 50:
			$new_admin_auth = 3;
			break;
		default:
			$new_admin_auth = 0;
	}
$ybase->ST_PRI .= <<<HTML

<tr align="center">
<td>
<input type="checkbox" name="add_data_id[]" value="$q_employee_in_data_id" checked>
</td>
<td>
$q_employee_num
</td>
<td>
{$q_employee_name}　<a href="./px_add_nameck.php?t_employee_in_data_id=$q_employee_in_data_id" target="_blank" class="btn btn-outline-info btn-sm p-0">確認</a>

</td>
<td>
$q_kana_name
</td>
<td>
{$ybase->sex_list[$q_sex]}
</td>
<td>
{$q_birthday}
</td>
<td>
{$q_indate}
</td>
<td>
{$ybase->section_list[$q_section_id]}
</td>
<td>
{$ybase->employee_type_list[$q_employee_type]}
</td>
<td>
<select name="in_position_class[$q_employee_in_data_id]" class="form-control-sm" id="in_position_class_{$q_employee_in_data_id}">
HTML;
foreach($ybase->position_class_list as $key => $val){
	if($key == $q_position_class){
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
</td>
<td>
<select name="in_admin_auth[$q_employee_in_data_id]" class="form-control-sm" id="in_admin_auth_{$q_employee_in_data_id}">
HTML;
foreach($ybase->admin_auth_list as $key => $val){
	if($key == $new_admin_auth){
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
</td>

</tr>
HTML;
}

$ybase->ST_PRI .= <<<HTML
  </tbody>
</table><br>
<div class="text-center">
<button type="sumit" class="btn btn-outline-info btn-sm" role="button">チェックしたデータを新規追加実行</button>
</div>
</form>


<a href="./px_ck.php">戻る</a><br><br>

HTML;

$ybase->HTMLfooter();
$ybase->priout();

////////////////////////////////////////////////
?>