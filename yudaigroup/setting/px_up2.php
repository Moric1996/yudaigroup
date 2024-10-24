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
$sql = "select employee_in_data_id from employee_in_data";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){

$ybase->title = "従業員情報更新";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("従業員情報更新");


$ybase->ST_PRI .= <<<HTML
<div class="container">
<p></p><br><br>
<div class="text-center"><b>作業中のデータがあります</b></div><br><br>
<div class="text-center">作業の続きを開始しますか？</div><br><br>

<div class="text-center">
<a href="./px_ck.php" class="btn btn-outline-info m-5">続きを開始する</a>

<a href="./employee_in_del.php" class="btn btn-outline-info m-5">途中のデータを削除して最初から始める</a></div><br><br>
<br>
<br>
<br><br>

<a href="./new_employee.php">新規ユーザーアカウント作成画面に戻る</a><br><br>

</div>
HTML;

$ybase->HTMLfooter();
$ybase->priout();

exit;

}

///////////////////////////////////////////////////////

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
$company_id=1;

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

$all_cnt=0;
$error1_cnt=0;
$error1_data="";
$insert_cnt=0;
foreach($buffer as $key => $val){
	if($key == 0){continue;}
	$val = trim($val);
	if(!$val){continue;}
	$data = explode(",",$val);
	$up_sql="";
	$i=0;
	$employee_num = "";
	$insqldata = array();
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


		$insqldata[$key2] = "$aaa";
		$i++;
	}
	$all_cnt++;
	if(!$employee_num || ($employee_num == "0000")){
		$error1_cnt++;
		$error1_data .= "{$val}<br>";
		continue;
	}
//$test.=	$up_sql."<br>";

	$insqldata['position_name'] = "''";
	$insqldata['position_class'] = "null";
	$insqldata['email'] = "''";
	$sql = "insert into employee_in_data (employee_in_data_id,employee_num,employee_name,kana_name,sex,birthday,indate,company_id,section_id,employee_type,position_name,position_class,email,add_date,comp_status) values (nextval('employee_in_data_id_seq'),{$insqldata['employee_num']},{$insqldata['employee_name']},{$insqldata['kana_name']},{$insqldata['sex']},{$insqldata['birthday']},{$insqldata['indate']},{$company_id},{$insqldata['section_id']},{$insqldata['employee_type']},{$insqldata['position_name']},{$insqldata['position_class']},{$insqldata['email']},'now','0')";
	$result = $ybase->sql($conn,$sql);
	$insert_cnt++;
}
$not_in = $all_cnt - $insert_cnt;
if(!$not_in){
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/px_ck.php");
exit;
}

$ybase->title = "従業員情報更新";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("従業員情報更新");


$ybase->ST_PRI .= <<<HTML
<div class="container">
<p></p>
{$all_cnt}件中{$not_in}件でエラーがありました。<br><br>
そのうち、社員番号をによるエラー<br>エラーデータ({$error1_cnt}件)<br>{$error1_data}<br><br>
<br><br>
<br><br>
<a href="./px_ck.php">エラーを気にせず続ける</a><br><br>

<a href="./employee_in_del.php">アップロードデータを削除して最初からやり直す</a><br><br>


<br><br>
<br><br>

<a href="./new_employee.php">新規ユーザーアカウント作成画面に戻る</a><br><br>
</div>
HTML;

$ybase->HTMLfooter();
$ybase->priout();

////////////////////////////////////////////////
?>