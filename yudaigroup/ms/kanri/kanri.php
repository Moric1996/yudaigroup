<?php
include("../../inc/auth.inc");
include("../base.inc");
include("../item_list.inc");

$base            = new base();
$number = 1;
$conn = $base->connect();

$sql = "select entry_id,employee_num,belong,name,kana,superior,to_char(add_date,'YYYY/MM/DD'),comp_flag from answerdata where number = $number order by comp_flag desc,belong,kana";
$result = $base->sql($conn,$sql);
$num = pg_num_rows($result);

$base->hd("モラールサーベイ確認ページ");

$base->ST_PRI .= <<<HTML

<div class="main">2017年雄大グループ</div>
<div class="main">モラールサーベイ</div>
<div class="main">入力者一覧</div>
<hr>
該当者 {$num}人
<hr>
<table border="1">
<tr>
<th>所属</th>
<th>氏名</th>
<th>入力日</th>
<th>状態</th>
</tr>

HTML;

for($i=0;$i<$num;$i++){
        list($q_entry_id,$q_employee_num,$q_belong,$q_name,$q_kana,$q_superior,$q_add_date,$q_comp_flag) = pg_fetch_array($result,$i);
if($q_comp_flag == $MAIN_ITEM_CNT){
	$jotai = "<b>完了</b>";
}else{
	$jotai = "<div style=\"color: red\">記入途中</div>";	
}
$base->ST_PRI .= <<<HTML

<tr>
<td>$belong_arr[$q_belong]</td>
<td>$q_name</td>
<td>$q_add_date</td>
<td>$jotai</td>
</tr>

HTML;

}

$base->ST_PRI .= <<<HTML

</table>
<hr>
HTML;

$base->ft();
$base->priout();
?>