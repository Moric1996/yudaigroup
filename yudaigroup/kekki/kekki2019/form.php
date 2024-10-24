<?php
include("./base.inc");
include("./item_list.inc");

$base            = new base();

$base->param = "entry_id=$entry_id";
$base->bk_param=$base->param;
$base->bk_script="index.php";

if(!preg_match("/^[0-9]+$/",$entry_id)){
	$base->error("エラー");
}
if(!preg_match("/^[0-9]+$/",$main_item_no)){
	$base->error("エラー");
}
$nokori = $MAIN_ITEM_CNT - $main_item_no + 1;
if($nokori < 0){
	$base->error("エラー");
}
$conn = $base->connect();

$sql = "select comp_flag from answerdatakekki2019 where entry_id = $entry_id";
$result = $base->sql($conn,$sql);
$comp_flag = @pg_fetch_result($result,0,0);


if($write_mode){
	$pre_item_no = $main_item_no - 1;
	if(($pre_item_no < 1)||($pre_item_no > $MAIN_ITEM_CNT)){
		$base->error("エラー");
	}
	if($pre_item_no > $comp_flag){
		$add_sql0 = ",comp_flag = '$pre_item_no'";
	}else{
		$add_sql0 = "";
	}

	$sql_add = "";
	$param_add = "&main_item_no=$pre_item_no";
	$error_data = array();
	$error_chk = array();
	reset($sub_item_arr[$pre_item_no]);
	while(list($key,$val)=each($sub_item_arr[$pre_item_no])){
		$no_name = sprintf("%02d",$pre_item_no) . sprintf("%02d",$key);
		$ans_name = "ans"."$no_name";
		$com_name = "com"."$no_name";
		$ans_data = trim($$ans_name);
		if(!preg_match("/^[0-9]+$/",$ans_data)&&($key!=4)){
			array_push($error_data,$key);
		}
		$com_data = trim($$com_name);
		if(($ans_data == 3) && !$com_data){
			array_push($error_data,$key);
		}
		if($key == 4){
			$ans_data = 0;
		}
		$param_add .= "&{$ans_name}={$ans_data}&{$com_name}={$com_data}";
		$com_data = addslashes($com_data);
		$sql_add .= "$ans_name=$ans_data,$com_name='$com_data',";
		if($chk_arr[$pre_item_no][$key]){
			if($ans_data <> 3){
				$chk_name = "chk"."$no_name";
				$chk_data = trim($$chk_name);
				if(!preg_match("/^[0-9]+$/",$chk_data)){
					array_push($error_chk,$key);
				}
				$param_add .= "&{$chk_name}={$chk_data}";
				$sql_add .= "$chk_name='$chk_data',";
			}
		}
	}
	if($error_data || $error_chk){
		$base->bk_param .= $param_add;
		$base->bk_script="form.php";
		$base->error("未回答項目があります<br>不満と回答した場合は、その理由を記入してください");
	}
	$sql = "update answerdatakekki2019 set $sql_add add_date='now'$add_sql0 where entry_id=$entry_id";
	$result = $base->sql($conn,"$sql");
}

$nextno=$main_item_no+1;
if($main_item_no > $MAIN_ITEM_CNT ){

$base->hd("雄大グループ決起大会アンケート");

$base->ST_PRI .= <<<HTML
<div class="head">雄大グループ 決起大会アンケート</div><br>
<hr>
<h2>入力完了</h2>
お疲れ様でした。全ての入力が完了しました。<br><br>
ご協力ありがとうございました。<br><br>
<!-------
<p><div class="center"><a href="#" onClick="window.close(); return false;" class="square_btn">CLOSE</a></div></p>
------->
HTML;

$base->ft();
$base->priout();
exit;
}



$base->hd("決起大会アンケート入力フォーム");

$base->ST_PRI .= <<<HTML
<div class="head">雄大グループ 決起大会アンケート</div><br>

<h3>$item_arr[$main_item_no]</h3>
2019年2月21日に行われた雄大グループ決起大会のアンケートです。<br>
該当する項目を選択してください。<br>
不満を選択した場合は理由を入力してください。<br>
その他ご意見等ありましたら、フリーコメント欄に記入してください。<br>
<form action="" method="post">
<input type="hidden" name="entry_id" value="$entry_id">
<input type="hidden" name="main_item_no" value="$nextno">
<input type="hidden" name="write_mode" value="1">
HTML;

reset($sub_item_arr[$main_item_no]);
while(list($key,$val)=each($sub_item_arr[$main_item_no])){
if($key == 4){
	continue;
}
$base->ST_PRI .= <<<HTML
<div class="subitem">
<b>$small_alfa[$key]){$val}</b><br>
<ul>
HTML;

	$sub_item_no="$main_item_no". sprintf("%02d",$key);
	$sub_item_no4 = sprintf("%04d",$sub_item_no);
	$ansname="ans{$sub_item_no4}";
	reset($option_arr[$sub_item_no]);
	while(list($key2,$val2)=each($option_arr[$sub_item_no])){
	if(${$ansname} == $key2){
		$checked=" checked";
	}else{
		$checked="";
	}
$base->ST_PRI .= <<<HTML
<li><input type="radio"name="$ansname" value="$key2"$checked required>$val2</li>
HTML;
	}
$textareaname = "com{$sub_item_no4}";
$base->ST_PRI .= <<<HTML
</ul>
HTML;

if($chk_arr[$main_item_no][$key]){
	$chkname = "chk"."$sub_item_no4";
	if($$chkname == "1"){
		$chkchecked1=" checked=\"checked\"";
		$chkchecked2="";
	}elseif($$chkname == "2"){
		$chkchecked1="";
		$chkchecked2=" checked=\"checked\"";
	}else{
		$chkchecked1="";
		$chkchecked2="";
	}
$base->ST_PRI .= <<<HTML
<div class="chk">※「調度良い」以外は下記どちらかにチェックしてください</div>
<div class="chk">( <input type="radio" name="$chkname" value="1"$chkchecked1>難しい or <input type="radio" name="$chkname" value="2"$chkchecked2>優しい )</div>

HTML;
}

$base->ST_PRI .= <<<HTML
<div align="center">
<b>【不満の場合はその理由】</b><br><textarea name="{$textareaname}" rows="5" cols="40">${$textareaname}</textarea></div>
</div>
<br>
HTML;
}

$base->ST_PRI .= <<<HTML
<div class="subitem">
<div align="center">

<b>【フリーコメント】</b><br><textarea name="com0104" rows="6" cols="40">$com0104</textarea></div>
</div>
<br>


<div class="center"><input type="submit" class="submit_btn" value="入力完了 次へ"></div>
</form>
<br>
<br>
HTML;
$base->ft();

$base->priout();
?>