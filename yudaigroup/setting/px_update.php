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
$sql = "select a.employee_in_data_id,a.employee_num,a.employee_name,a.kana_name,a.sex,a.birthday,a.indate,a.company_id,a.section_id,a.employee_type,a.position_name,a.position_class,a.email,b.employee_id,b.employee_name,b.kana_name,b.section_id,b.employee_type,b.position_name,b.position_class,b.email,b.admin_auth from employee_in_data as a inner JOIN employee_list as b ON a.employee_num = b.employee_num where a.comp_status = '0' and ((a.section_id <> b.section_id) or (a.employee_type <> b.employee_type) or (a.employee_name <> b.employee_name) or (a.kana_name <> b.kana_name))";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);


$ybase->title = "従業員情報更新";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("従業員情報更新");


$ybase->ST_PRI .= <<<HTML
<div class="container">
<p></p>

未処理のデータ【{$num}】件<br><br>
情報変更しないデータはチェックをはずしてください。<br>
<form action="px_update_ex.php" method="post" id="add_form">
<table class="table table-bordered table-hover table-sm" style="font-size:85%;">
  <thead>
    <tr align="center" class="table-primary">
      <th scope="col">対象</th>
      <th scope="col">社員番号</th>
      <th scope="col"></th>
      <th scope="col">氏名</th>
      <th scope="col">カナ</th>
      <th scope="col">所属部署・店舗</th>
      <th scope="col">雇用区分</th>
      <th scope="col">役職区分</th>
      <th scope="col">権限</th>
    </tr>
  </thead>
  <tbody>

HTML;

for($i=0;$i<$num;$i++){
	list($q_employee_in_data_id,$q_employee_num,$q_employee_name,$q_kana_name,$q_sex,$q_birthday,$q_indate,$q_company_id,$q_section_id,$q_employee_type,$q_position_name,$q_position_class,$q_email,$now_employee_id,$now_employee_name,$now_kana_name,$now_section_id,$now_employee_type,$now_position_name,$now_position_class,$now_email,$now_admin_auth) = pg_fetch_array($result,$i);
	$q_employee_num = trim($q_employee_num);
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
	$now_employee_name = trim($now_employee_name);
	$now_kana_name = trim($now_kana_name);
	$now_section_id = trim($now_section_id);
	$now_employee_type = trim($now_employee_type);
	$now_position_name = trim($now_position_name);
	$now_position_class = trim($now_position_class);
	$now_email = trim($now_email);
	$now_admin_auth = trim($now_admin_auth);

	if(!$q_position_class && $now_position_class){
		$q_position_class = $now_position_class;
	}elseif(!$q_position_class){
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

if($now_employee_name == $q_employee_name){
	$to_employee_name = "<span class=\"text-secondary\">変更なし</span>";
}else{
	$to_employee_name = "<b class=\"text-danger\">$q_employee_name</b>";
}
if($now_kana_name == $q_kana_name){
	$to_kana_name = "<span class=\"text-secondary\">変更なし</span>";
}else{
	$to_kana_name = "<b class=\"text-danger\">$q_kana_name</b>";
}
if($now_section_id == $q_section_id){
	$to_section_id = "<span class=\"text-secondary\">変更なし</span>";
}else{
	$to_section_id = "<b class=\"text-danger\">".$ybase->section_list[$q_section_id]."</b>";
}
if($now_employee_type == $q_employee_type){
	$to_employee_type = "<span class=\"text-secondary\">変更なし</span>";
}else{
	$to_employee_type = "<b class=\"text-danger\">".$ybase->employee_type_list[$q_employee_type]."</b>";
}


$ybase->ST_PRI .= <<<HTML

<tr align="center">
<td>
<input type="checkbox" name="update_data_id[]" value="$q_employee_in_data_id" checked>
</td>
<td>
$q_employee_num
</td>
<td>
<span class="text-secondary">現在<span><br>
<span class="text-secondary">変更後<span>
</td>
<td>
<nobr>{$now_employee_name}</nobr><br>
$to_employee_name
</td>
<td>
<nobr>{$now_kana_name}</nobr><br>
$to_kana_name
</td>
<td>
{$ybase->section_list[$now_section_id]}<br>
$to_section_id
</td>
<td>
{$ybase->employee_type_list[$now_employee_type]}<br>
$to_employee_type
</td>
<td>
{$ybase->position_class_list[$now_position_class]}<br>
<select name="in_position_class[$q_employee_in_data_id]" class="" id="in_position_class_{$q_employee_in_data_id}">
HTML;
foreach($ybase->position_class_list as $key => $val){
	if($key == $q_position_class){
		$selected = " selected";
	}else{
		$selected = "";
	}
$ybase->ST_PRI .= <<<HTML
<b><option value="$key"{$selected}>$val</option></b>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</select>
</td>
<td>
{$ybase->admin_auth_list[$now_admin_auth]}<br>
<select name="in_admin_auth[$q_employee_in_data_id]" class="" id="in_admin_auth_{$q_employee_in_data_id}">
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
<button type="sumit" class="btn btn-outline-info btn-sm" role="button">チェックしたデータの情報変更実行</button>
</div>
</form>


<a href="./px_ck.php">戻る</a><br><br>

HTML;

$ybase->HTMLfooter();
$ybase->priout();

////////////////////////////////////////////////
?>