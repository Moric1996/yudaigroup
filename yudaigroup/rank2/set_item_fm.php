<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/rank_list.inc');

$ybase = new ybase();
$ybase->session_get();
$tablerate = $ybase->mbscale(5);

if(!preg_match("/^[0-9]+$/",$target_month)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10821");
}
if(!preg_match("/^[0-9]+$/",$target_shop_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:10822");
}

/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$addsql = "month = $target_month and shop_id = $target_shop_id";
$param = "target_month=$target_month&target_shop_id=$target_shop_id";
//////////////////////////////////////////
$yy=substr($target_month,0,4);
$mm=substr($target_month,4,2);
$now_month = date("Ym");

$sql = "select item_id,u_name from telecom2_unitname where {$addsql} and status = '1' order by item_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
for($i=0;$i<$num;$i++){
	list($q_item_id,$q_u_name) = pg_fetch_array($result,$i);
	$u_name_list[$q_item_id] = $q_u_name;
}

$sql = "select bigitem_id,count(*) from telecom2_item where {$addsql} and status = '1' group by bigitem_id order by bigitem_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$last_month = date("Ym",mktime(0,0,0,$mm - 1,1,$yy));
	$lastyy=substr($last_month,0,4);
	$lastmm=substr($last_month,4,2);

	$sql = "update telecom2_bigitem set status = '0' where {$addsql} and status = '1'";
	$result = $ybase->sql($conn,$sql);

	$sql = "insert into telecom2_bigitem (bigitem_id,bigitem_name,shop_id,month,order_num,add_date,status) select bigitem_id,bigitem_name,shop_id,$target_month,order_num,'now',status from telecom2_bigitem where month = $last_month and shop_id = $target_shop_id and status = '1' order by bigitem_id";
	$result = $ybase->sql($conn,$sql);

	$sql = "insert into telecom2_item (item_id,item_name,shop_id,month,bigitem_id,score,order_num,add_date,status) select item_id,item_name,shop_id,$target_month,bigitem_id,score,order_num,'now',status from telecom2_item where month = $last_month and shop_id = $target_shop_id and status = '1' order by item_id";
	$result = $ybase->sql($conn,$sql);

	$sql = "insert into telecom2_unitname (shop_id,month,item_id,u_name,add_date,status) select shop_id,$target_month,item_id,u_name,'now',status from telecom2_unitname where month = $last_month and shop_id = $target_shop_id and status = '1' order by item_id";
	$result = $ybase->sql($conn,$sql);
	$sql = "select item_id,u_name from telecom2_unitname where {$addsql} and status = '1' order by item_id";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	for($i=0;$i<$num;$i++){
		list($q_item_id,$q_u_name) = pg_fetch_array($result,$i);
		$u_name_list[$q_item_id] = $q_u_name;
	}

	$notice = "新しい月の為、前月の情報を引継ぎ設定しました。変更がある場合は下記を変更してください。";
$sql = "select bigitem_id,count(*) from telecom2_item where {$addsql} and status = '1' group by bigitem_id order by bigitem_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);

}else{
	$notice = "変更がある場合は下記を変更してください。";
}
for($i=0;$i<$num;$i++){
	list($q_bigitem_id,$q_count) = pg_fetch_array($result,$i);
	$bigitem_cnt[$q_bigitem_id] = $q_count;
}

$sql = "select bigitem_id,bigitem_name from telecom2_bigitem where {$addsql} and status = '1' order by order_num";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$sql = "insert into telecom2_bigitem (bigitem_id,bigitem_name,shop_id,month,order_num,add_date,status) values (nextval('telecom2_bigitem_id_seq'),'',$target_shop_id,$target_month,1,'now','1')";
	$result = $ybase->sql($conn,$sql);
	$sql = "select bigitem_id,bigitem_name from telecom2_bigitem where {$addsql} and status = '1' order by order_num";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	$bignew_flag = 1;
}else{
	$bignew_flag = 0;
}

$ybase->title = "Y☆Judge-項目設定";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("項目設定");

$ybase->ST_PRI .= <<<HTML
<script src="./js/rank_update.js?$time"></script>
<input type="hidden" name="target_month" value="$target_month" id="target_month">
<input type="hidden" name="target_shop_id" value="$target_shop_id" id="target_shop_id">
<input type="hidden" name="scriptno" value="2" id="scriptno">
<script>
$(function($) {
HTML;
if($confirm_f != 'no'){
$ybase->ST_PRI .= <<<HTML

	$(document).ready( function(){
	var nowmmyy = $now_month;
	if(nowmmyy == $target_month){
		if(!confirm('今月({$yy}年{$mm}月)の設定をします。よろしいですか？')) {
			window.location.href = './set_top.php?target_month={$target_month}&target_shop_id={$target_shop_id}';
			return false;
		}
	}
	if(nowmmyy > $target_month){
		if(!confirm('以前({$yy}年{$mm}月)の設定をします。よろしいですか？')) {
			window.location.href = './set_top.php?target_month={$target_month}&target_shop_id={$target_shop_id}';
			return false;
		}
	}
	
	});

HTML;
}
$ybase->ST_PRI .= <<<HTML

	$("[id^=del_bigitem_]").click(function(){
		var bigitemid = $(this).attr('bigitem_id');
		var bigitemname = $(this).attr('bigitem_name');
		if(!confirm('「' + bigitemname + '」を本当に削除しますか？\\n大項目を削除すると配下の項目も削除されます。')){
			return false;
		}else{
			location.href = 'set_item_del_bigitem.php?$param&t_bigitem_id=' + bigitemid;
	    }
	});
	$("[id^=del_item_]").click(function(){
		var itemid = $(this).attr('item_id');
		var itemname = $(this).attr('item_name');
		if(!confirm('「' + itemname + '」を本当に削除しますか？')){
			return false;
		}else{
			location.href = 'set_item_del_item.php?$param&t_item_id=' + itemid;
	    }
	});
	$("#import_ex").click(function(){
		var im_shop_id = $("#import_shop_id").val();
		var im_month = $("#import_month").val();
		if(!im_shop_id){
			confirm('対象キャリアを選択してください')
			return false;
		}
		if(!im_month){
			confirm('対象年月を選択してください')
			return false;
		}
		if(!confirm('現在の項目(データ含む)は削除されて、対象の項目が新たに設定されます。本当によろしいですか？')){
			return false;
		}else{
			location.href = 'set_item_import.php?$param&import_shop_id=' + im_shop_id + '&import_month=' + im_month;
	    }
	});
	$("#slidebutton").click(function(){
		if($("#import_part").css('display') == 'none'){
		$("#import_part").css("display","block");
		$("#slidebutton").text('▲他店舗の項目コピー');

	}else{
		$("#import_part").css("display","none");
		$("#slidebutton").text('▼他店舗の項目コピー');
	}
	});
});
</script>

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary" href="./set_top.php?target_month=$target_month&target_shop_id=$target_shop_id" role="button">設定TOPに戻る</a></div>
<h5 style="text-align:center;">【{$rank_section_name[$target_shop_id]} {$yy}年{$mm}月】 項目設定</h5>

<div id="import_menu"><a href="#!" class="btn btn-sm btn-outline-info" id="slidebutton">▼他店舗の項目コピー</a></div>
<div id="import_part" style="display:none;">
<div class="card">
<div class="row">
<div class="col-sm-10 align-self-center">
<small>コピー元の店舗と年月を選んでコピーボタンを押してください</small>
</div>
</div>
<div class="row">
<div class="col-sm-4 align-self-center">

【対象キャリア】<select name="import_shop_id" id="import_shop_id">
<option value="">選択してください</option>
HTML;
foreach($rank_section_name as $key => $val){
if($key == $import_shop_id){
	$selected = " selected";
}else{
	$selected = "";
}

$ybase->ST_PRI .= <<<HTML
<option value="$key"{$selected}>$val</option>
HTML;

}
$ybase->ST_PRI .= <<<HTML
</select>
</div>
<div class="col-sm-4 align-self-center">
【対象年月】<input type="month" name="import_month" value="$import_month" id="import_month">
</div>
<div class="col-sm-4 align-self-center">
<a href="#!" class="btn btn-sm btn-outline-secondary" id="import_ex">コピー</a>
</div>
</div>
</div>

</div>
<p></p>
<p>$notice</p>
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
<tr bgcolor="#eeeeee">
<th>大項目 <a href="set_item_in_bigitem.php?$param" class="btn btn-sm btn-outline-secondary">大項目追加</a>
</th>
<th>項目</th>
<th>配点</th>
<th>単位</th>
<th>入力不可</th>
</tr>
  </thead>
  <tbody>
HTML;

$sql = "select item_id,action_num from telecom2_action where {$addsql} and status = '1' order by item_id";
$result3 = $ybase->sql($conn,$sql);
$num3 = pg_num_rows($result3);
for($i=0;$i<$num3;$i++){
	list($q_item_id,$q_action_num) = pg_fetch_array($result3,$i);
	$actnum_arr[$q_item_id] = $q_action_num;
}

for($i=0;$i<$num;$i++){
	list($q_bigitem_id,$q_bigitem_name) = pg_fetch_array($result,$i);

$sql = "select item_id,item_name,score,noinput from telecom2_item where {$addsql} and bigitem_id = $q_bigitem_id and status = '1' order by order_num";

$result2 = $ybase->sql($conn,$sql);
$num2 = pg_num_rows($result2);
if(!$num2){
	$sql = "insert into telecom2_item (item_id,item_name,shop_id,month,bigitem_id,score,order_num,add_date,status) values (nextval('telecom2_item_id_seq'),'',$target_shop_id,$target_month,$q_bigitem_id,0,1,'now','1')";
	$result2 = $ybase->sql($conn,$sql);
	$sql = "select item_id,item_name,score,noinput from telecom2_item where {$addsql} and bigitem_id = $q_bigitem_id and status = '1' order by order_num";
	$result2 = $ybase->sql($conn,$sql);
	$num2 = pg_num_rows($result2);
	$itemnew_flag = 1;
}else{
	$itemnew_flag = 0;
}
for($ii=0;$ii<$num2;$ii++){
	list($q_item_id,$q_item_name,$q_score,$q_noinput) = pg_fetch_array($result2,$ii);
if($ii == 0){
$rowspan = $bigitem_cnt[$q_bigitem_id] + 1;
if($bignew_flag){$rowspan += 1;}
$ybase->ST_PRI .= <<<HTML
<tr>
<td rowspan="$rowspan" style="vertical-align: middle;" bgcolor="{$bcolor[$i]}">
<input type="text" name="bigitem_name$q_bigitem_id" value="$q_bigitem_name" id="bigitem_name$q_bigitem_id">
<a href="#" id="del_bigitem_{$q_bigitem_id}" bigitem_id="$q_bigitem_id" bigitem_name="$q_bigitem_name">削除</a>
</td>
HTML;
}else{
$ybase->ST_PRI .= <<<HTML
<tr>
HTML;
}
if($u_name_list[$q_item_id]){
	$d_name = $u_name_list[$q_item_id];
}else{
	$d_name = "件";
}
if($q_noinput == "1"){
	$noinputchecked = " checked";
}else{
	$noinputchecked = "";
}
$ybase->ST_PRI .= <<<HTML

<td align="left">
<a href="./set_item_updn.php?updn=up&item_id=$q_item_id&$param">▲</a>|
<a href="./set_item_updn.php?updn=dn&item_id=$q_item_id&$param">▼</a>
<input type="text" name="item_name$q_item_id" value="$q_item_name" id="item_name$q_item_id" size="30">
<a href="#" id="del_item_{$q_item_id}" item_id="$q_item_id" item_name="$q_item_name">削除</a>
</td>
<td>
<input type="{$ybase->NUM_INPUT_TYPE}" name="score$q_item_id" value="$q_score" id="action$q_item_id" item_id="$q_item_id" style="width:4em;">
</td>
<td>
<input type="text" name="u_name$q_item_id" value="$d_name" id="u_name$q_item_id" item_id="$q_item_id" size="4">
</td>
<td>
<input type="checkbox" name="noinput$q_item_id" value="1" id="noinput$q_item_id" item_id="$q_item_id"{$noinputchecked}>
</td>
</tr>
HTML;
if($ii == ($num2 - 1)){
$ybase->ST_PRI .= <<<HTML
<tr>
<td colspan="4" align="left">
<a href="set_item_in_item.php?$param&t_bigitem_id=$q_bigitem_id" class="btn btn-sm btn-outline-secondary">項目追加</a>
</td>
</tr>
HTML;
}
}

}

$ybase->ST_PRI .= <<<HTML

 </tbody>
</table>
</div>
<div style="text-align:center;"><a class="btn btn-danger" href="./set_team_fm.php?target_month=$target_month&target_shop_id=$target_shop_id" role="button">チーム設定へ</a></div>
<div style="text-align:right;"><a class="btn btn-secondary" href="./set_top.php?target_month=$target_month&target_shop_id=$target_shop_id" role="button">設定を完了して戻る</a></div>
</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>