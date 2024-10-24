<?php
include("./base.inc");
include("./item_list.inc");

$base            = new base();
$namae=trim($namae);
$namaekana=trim($namaekana);
$employee_num=trim($employee_num);
$superior=trim($superior);


$base->param = "belong=$belong&namae=$namae&namaekana=$namaekana&employee_num=$employee_num&superior=$superior";
$base->bk_param=$base->param;
$base->bk_script="index.php";



if(!$belong){
	$base->error("所属部署を選択してください");
}
if(!$namae){
	$base->error("氏名を入力してください");
}
if(!$namaekana){
	$base->error("氏名フリガナを入力してください");
}
if(!$superior){
	$base->error("直属上司を入力してください");
}



$base->hd("モラールサーベイ氏名等確認");

$base->ST_PRI .= <<<HTML

<div class="head">雄大グループ モラールサーベイ アンケート</div><br><hr>
入力事項を確認下さい。<br>
<form action="./in1_ex.php" method="post">
<input type="hidden" name="entry_id" value="$entry_id">
<input type="hidden" name="belong" value="$belong">
<input type="hidden" name="namae" value="$namae">
<input type="hidden" name="namaekana" value="$namaekana">
<input type="hidden" name="employee_num" value="$employee_num">
<input type="hidden" name="superior" value="$superior">

<div class="subitem">
所属部署<br>
<div class="kakunin">【 {$belong_arr[$belong]} 】</div>
氏名<br>
<div class="kakunin">【 {$namae} 】</div>
氏名フリガナ<br>
<div class="kakunin">【 {$namaekana} 】</div>
社員番号<br>
<div class="kakunin">【 {$employee_num} 】</div>
直属上司<br>
<div class="kakunin">【 {$superior} 】</div>
</div>
<div><a href="$base->bk_script?$base->param" class="square_btn2">修正</a></div>
<div class="center"><input type="submit" class="square_btn" value="OK 次へ"></div><br>
</form>

HTML;

$base->ft();
$base->priout();
?>