<?php
include("./base.inc");
include("./item_list.inc");

$base            = new base();

if(preg_match("/^[0-9]+$/",$entry_id)){
	$sql = "";

}

$base->hd("2019決起大会アンケート");

$base->ST_PRI .= <<<HTML

<div class="main">2019年雄大グループ</div>
<div class="main">決起大会アンケート</div>
<hr>


<div class="">
2019年2月21日に行われた雄大グループの決起大会に関するアンケートです<br>
無記名式です。<br>
</div>
<hr>
まずは、所属部署、雇用形態の選択をしてください。<br>
<form action="./in1_ck.php" method="post">
<div class="subitem">
所属部署<br>
<select name="belong">
<option value="">選択してください</option>
HTML;

reset($belong_arr);
while(list($key,$val)=each($belong_arr)){
if($belong == $key){
	$selected = " selected";
}else{
	$selected = "";
}
$base->ST_PRI .= <<<HTML
<option value="$key"$selected>{$val}</option>
HTML;
}

$base->ST_PRI .= <<<HTML
</select><br>

雇用形態<br>
<select name="employee_type">
<option value="">選択してください</option>
HTML;

reset($employee_type_arr);
while(list($key,$val)=each($employee_type_arr)){
if($employee_type == $key){
	$selected = " selected";
}else{
	$selected = "";
}
$base->ST_PRI .= <<<HTML
<option value="$key"$selected>{$val}</option>
HTML;
}

$base->ST_PRI .= <<<HTML
</select><br>

</div><br>
<div class="center"><input type="submit" class="square_btn" value="入力完了 次へ"></div><br>
</form>

HTML;

$base->ft();
$base->priout();
?>