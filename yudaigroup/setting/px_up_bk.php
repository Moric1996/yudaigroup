<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

////////////エラーチェック




$error_flag = 0;
$space = array();

if(!$incompany_id){
	$incompany_id = 1;
}

//////////////////////////////////////////////////

$uploaddir = '/home/yournet/yudai/';
if(!file_exists($uploaddir)){
	mkdir($uploaddir, 0775);
}

$filenamebase = time().rand(1000,9999);
if(!isset($_FILES["jinjerfile"]['name'][0])){
	$msg = "ファイルのアップロードに失敗しました。再度お試し下さい。ERROR_CODE:10891";
	$ybase->error("$msg");
}
$handle = fopen($_FILES["jinjerfile"]['tmp_name'], "r");
$i=0;
while (($buffer[$i] = fgets($handle, 4096)) !== false) {
	$buffer[$i] = trim($buffer[$i]);
	$buffer[$i] = mb_convert_encoding($buffer[$i],"UTF-8","SJIS");
	$i++;
}
fclose($handle);
//echo $buffer[$i];

$test="";
//////////////////////////////////////////////////

////////////////
$item['employee_num'] = '社員番号';
$item['employee_name'] = '社員氏名';
$item['kana_name'] = '氏名フリガナ';
$item['sex'] = '性別';
$item['birthday'] = '生年月日';
$item['section_id'] = '部課';
$item['employee_type'] = '役社員区分';
$item['indate'] = '入社日';
//$item['email'] = 'Ｅメール';

$koumoku = explode(",",$buffer[0]);

foreach($koumoku as $key => $val){
	foreach($item as $key2 => $val2){
//		print "$val,$val2";
		if($val == $val2){
			$space[$key2] = $key;
		}

	}
}
$conn = $ybase->connect();
$sql = "delete from employee_list_tmp";
$result = $ybase->sql($conn,$sql);
$sql = "select * from employee_list_tmp";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$ybase->error("データベースエラーです。ERROR_CODE:33042");
}

$all_cnt=0;
$insert_cnt=0;
foreach($buffer as $key => $val){
	if($key == 0){continue;}
	$data = explode(",",$val);
	$up_sql="";
	$i=0;
	$employee_num = "";
	foreach($space as $key2 => $val2){
		if($i>0){$up_sql.=",";}
		$aaa = trim($data[$val2]);
		switch($key2){
			case "kana_name":
				$aaa = mb_convert_kana($aaa, "KVC");
				$aaa = preg_replace('/　/', ' ', $aaa);
				$aaa = "'".$aaa."'";
				break;
			case "employee_name":
			case "email":
				$aaa = preg_replace('/　/', ' ', $aaa);
				$aaa = "'".$aaa."'";
				break;
			case "employee_num":
				$employee_num = sprintf("%04d",$aaa);
				$aaa = "'".$employee_num."'";
				break;
			case "sex":
				if($aaa == "男性"){
					$aaa = "m";
				}elseif($aaa == "女性"){
					$aaa = "w";
				}else{
					$aaa = "";
				}
				$aaa = "'".$aaa."'";
				break;
			case "birthday":
			case "indate":
				if(preg_match("/(平成||昭和||令和)([0-9]{1,2})年([0-9]{1,2})月([0-9]{1,2})/",$aaa,$str)){
					if($str[1] == "昭和"){
						$byy = 1925 + $str[2];
					}elseif($str[1] == "平成"){
						$byy = 1988 + $str[2];
					}elseif($str[1] == "令和"){
						$byy = 2018 + $str[2];
					}else{
						$byy = "";
					}
					$aaa = $byy."-".sprintf("%02d",$str[3])."-".sprintf("%02d",$str[4]);

				
				}else{
					$byy = "";
				}
				if($byy){
					$aaa = "'".$aaa."'";
				}else{
					$aaa = 'null';
				}
				break;
			case "section_id":
				list($secid,$other) = explode(":",$aaa);
				if(preg_match("/^[0-9]+$/",$secid)){
					$aaa = "'".$secid."'";
				}else{
					$aaa = "''";
				}
				break;
			case "employee_type":
				foreach($ybase->employee_type_list as $key3 => $val3){
					if($aaa == $val3){
						$aaa = $key3;
					}
				}
				if(!preg_match("/^[0-9]+$/",$aaa)){
					$aaa = 'null';
				}
				break;
		}


		$up_sql .= "$key2="."$aaa";
		$i++;
	}
	$all_cnt++;
	if(!$employee_num || ($employee_num == "0000")){
		continue;
	}
$up_sql .= ",company_id={$incompany_id}";
$test.=	$up_sql."<br>";

	$sql = "select nextval('employee_list_tmp_id_seq')";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$ybase->error("データベースエラーです。ERROR_CODE:33002");
	}
	$new_employee_id = pg_fetch_result($result,0,0);
	$sql = "insert into employee_list_tmp (employee_id_tmp,pass,add_date,status) values ($new_employee_id,'18da54','now','1')";
	$result = $ybase->sql($conn,$sql);
	$sql = "update employee_list_tmp set $up_sql where employee_id_tmp=$new_employee_id";
	$result = $ybase->sql($conn,$sql);

	$insert_cnt++;
}

$ybase->title = "従業員管理";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("従業員管理-データ更新");
$ybase->ST_PRI .= <<<HTML
<div class="container">
<p></p>
新規登録候補(現在登録がない=同じ従業員番号がない一覧)<br>
登録をしない従業員はチェックを外してください。<br>
<table>

HTML;

$sql = "select a.employee_id_tmp,a.employee_num,a.employee_name,b.employee_id from employee_list_tmp as a LEFT OUTER JOIN employee_list as b ON a.employee_num = b.employee_num WHERE b.employee_id is null";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
print "$sql";
for($i=0;$i<$num;$i++){
	list($q_employee_id_tmp,$q_employee_num,$q_employee_name,$q_employee_id) = pg_fetch_array($result,$i);
$ybase->ST_PRI .= <<<HTML
<tr>
<td>$q_employee_num</td>
<td>$q_employee_name</td>
<td>$q_employee_id</td>
</tr>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</table>
<p></p>

</div>
HTML;

$ybase->HTMLfooter();
$ybase->priout();

////////////////////////////////////////////////
?>