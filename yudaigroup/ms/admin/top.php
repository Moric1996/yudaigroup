<?php
exit;
include("../base.inc");
include("../item_list.inc");

$base            = new base();
$number = 1;
$conn = $base->connect();

$file_txt="";

//$sql = "select * from answerdata where number = $number order by comp_flag desc,belong,kana";
$sql = "select * from answerdata where number = $number order by entry_id";
$result = $base->sql($conn,$sql);
$num_row = pg_num_rows($result);
$num_col = pg_num_fields($result);

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
HTML;

for($j=0;$j<$num_col;$j++){
$fieldname = pg_field_name($result, $j);
$base->ST_PRI .= <<<HTML
<th>$fieldname</th>
HTML;
	if($j > 0){
		$file_txt.=",";
	}
	$file_txt.="$fieldname";
}
$file_txt.="\r\n";
$base->ST_PRI .= <<<HTML
</tr>
HTML;

for($i=0;$i<$num_row;$i++){
$base->ST_PRI .= <<<HTML
<tr>
HTML;
	$row = pg_fetch_assoc($result,$i);
	for($j=0;$j<$num_col;$j++){
		$fieldname = pg_field_name($result, $j);
$base->ST_PRI .= <<<HTML
<td>$row[$fieldname]</td>
HTML;
		if($j > 0){
			$file_txt.=",";
		}
		if($fieldname == 'belong'){
			$q_belong = $row[$fieldname];
			$file_txt.='"'.$belong_arr[$q_belong].'"';
		}else{
			$file_txt.='"'.$row[$fieldname].'"';
		}
	}
$file_txt.="\r\n";
$base->ST_PRI .= <<<HTML
</tr>
HTML;
}

$fp = fopen('tmpfile.csv', 'w'); 

// 一時ファイルに書き込む
fwrite($fp, $file_txt); 

// 最後にクローズする
fclose($fp); 

$base->ST_PRI .= <<<HTML

</table>
<hr>
<a href="dl.php">ダウンロード</a>
HTML;

$base->ft();
$base->priout();
?>