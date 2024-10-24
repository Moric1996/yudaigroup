<?php
include("./base.inc");
include("./item_list.inc");

$base            = new base();

if(preg_match("/^[0-9]+$/",$entry_id)){
	$sql = "";

}

$base->hd("モラールサーベイINDEX");

$base->ST_PRI .= <<<HTML

<div class="main">2017年雄大グループ</div>
<div class="main">モラールサーベイ＜組織診断シート＞</div>
<hr>


<div class="">
会社は望む成果（目標）に対して、結果を出す組織ですが、そのプロセスにおいてどんな雰囲気、組織で成果を出していくのかが問題です。
雄大グループのモラールや組織状況を把握し、すばらしい風土の会社にしていく参考にしたいと思いますのでアンケートお願いします。<br>
※このシートは、組織改善、新年度の事業計画づくりに反映させるための資料ですので、率直な（本音）なところ記入願います。<br>
※このシートは、本部取扱いとし、土屋社長、土屋副社長及びグループ執行役員へフィードバック致します。<br>
また記載内容について記載者に不利がないことをお約束します。<br>
なお、上司に関するコメントは土屋社長、土屋副社長及び集計作業の為ユアネット勝又社長のみが扱うものとします。
</div>
<hr>
まずは、所属部署の選択、氏名、フリガナ、直属上司を入力してください。<br>
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
氏名<br>
<input type="text" name="namae" value="$namae" size="30" placeholder="例)雄大 太郎" required><br>
氏名フリガナ<br>
<input type="text" name="namaekana" value="$namaekana" size="30" placeholder="例)ユウダイ タロウ" required><br>
社員番号(※わからない場合は空欄)<br>
<input type="text" name="employee_num" value="$employee_num" size="30"><br>
直属上司<br>
<input type="text" name="superior" value="$superior" size="30" placeholder="例)土屋 大雅" required><br>
</div><br>
<div class="center"><input type="submit" class="square_btn" value="入力完了 次へ"></div><br>
</form>

HTML;

$base->ft();
$base->priout();
?>