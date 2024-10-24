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


$param = "t_shop_id=$t_shop_id";
/////////////////////////////////////////

$conn = $ybase->connect();

//////////////////////////////////////////条件
$addsql = "";
/////////////////////////
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:23915");
}
$subject_list = "";
$allot_list = "";
$category_list = $check->category_make();

foreach($category_list as $key => $val){
if(!isset($itemset[$key])){
	continue;
}
$value_count = array_count_values($itemset[$key]);
$max = max($value_count);
if ($max > 1) {


$ybase->title = "店舗別チェック項目設定";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("店舗別チェック項目設定確認");

$ybase->ST_PRI .= <<<HTML
<div class="container">

<p></p>
<div class="text-center">
重複している項目があります
</div>

<p></p>
<form action="./set_shop_fm.php" method="post" id="form_shopcg">
<input type="hidden" name="t_shop_id" value="$t_shop_id">
HTML;
foreach($itemset as $key1 => $val1){
	foreach($val1 as $key2 => $val2){
$ybase->ST_PRI .= <<<HTML
<input type="hidden" name="itemset[$key1][$key2]" value="$val2">
<input type="hidden" name="allotset[$key1][$key2]" value="{$allotset[$key1][$key2]}">

HTML;
	}
}

$ybase->ST_PRI .= <<<HTML
<br>
<div class="text-center">
<button type="submit" class="btn btn-info btn-lg align-middle" role="button">　戻る　</button>
</div>

</form>
<br>
</div>
HTML;




$ybase->HTMLfooter();
$ybase->priout();
exit;

}
}


foreach($category_list as $key => $val){
	if(!isset($itemset[$key])){
		continue;
	}
	foreach($itemset[$key] as $key2 => $val2){
	if($delset[$key][$key2] == 1){
		continue;
	}
	if(!preg_match("/^[0-9]+$/",$val2)){
		$ybase->error("チェック項目を選択してください");
	}
	if(!preg_match("/^[0-9]+$/",$allotset[$key][$key2])){
		$ybase->error("配点を設定してください");
	}
		if($subject_list){
			$subject_list .= ",";
		}
		if($allot_list){
			$allot_list .= ",";
		}
		$subject_list .= $val2;
		$allot_list .= $allotset[$key][$key2];
	}
}
$subject_list = "{".$subject_list."}";
$allot_list = "{".$allot_list."}";
//print $subject_list;
//print "<br>";
//print $allot_list;
//exit;

$sql = "select nextval('ck_check_set_id_seq')";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データベースエラーです。ERROR_CODE:23916");
}
$new_ckset_id = pg_fetch_result($result,0,0);
$sql = "insert into ck_check_set values ($new_ckset_id,'$t_shop_id','$subject_list',1,'now','1','$allot_list')";
$result = $ybase->sql($conn,$sql);

$sql = "update ck_check_set set last_flag = 0 where section_id = '$t_shop_id' and ckset_id <> $new_ckset_id and status = '1'";
$result = $ybase->sql($conn,$sql);

header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/set_shop_fm.php?{$param}");
exit;
////////////////////////////////////////////////
?>