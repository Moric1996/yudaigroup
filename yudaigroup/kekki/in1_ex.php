<?php
include("./base.inc");
include("./item_list.inc");

$base            = new base();
$namae=trim($namae);
$namaekana=trim($namaekana);
$employee_num=trim($employee_num);
$superior=trim($superior);

$base->param = "belong=$belong&namae=$namae&namaekana=$namaekana&employee_num=$employee_num&superior=$superior&employee_type=$employee_type";
$base->bk_param=$base->param;
$base->bk_script="index.php";


if(!preg_match("/^[0-9]+$/",$belong)){
	$base->error("所属部署を選択してください");
}
if(!preg_match("/^[0-9]+$/",$employee_type)){
	$base->error("雇用形態を選択してください");
}
if(!$namae){
//	$base->error("氏名を入力してください");
}
if(!$namaekana){
//	$base->error("氏名フリガナを入力してください");
}
if(!$superior){
//	$base->error("直属上司を入力してください");
}

$conn = $base->connect();
if(!preg_match("/^[0-9]+$/",$entry_id)){
	$sql = "select NEXTVAL('answerdatakekki2019_entry_id_seq')";
	$result0 = $base->sql($conn,$sql);
	$entry_id = @pg_fetch_result($result0,0,0);
	$sql = "insert into answerdatakekki2019 (entry_id,add_date) values($entry_id,'now')";
	$result = $base->sql($conn,"$sql");
}

$namae = addslashes($namae);
$namaekana = addslashes($namaekana);
$superior = addslashes($superior);
$agent = addslashes($base->agent);

$sql = "update answerdatakekki2019 set number = $MS_NUMBER ,employee_num = '$employee_num',belong = $belong,name = '$namae',kana = '$namaekana',employee_type = '$employee_type',add_date = 'now',comp_flag = '0',ua = '$agent',ip = '$base->remote_addr' where entry_id = $entry_id";
$result = $base->sql($conn,"$sql");

header("Location: http://yournet-jp.com/kekki/form.php?entry_id=$entry_id&main_item_no=1");
exit;
?>