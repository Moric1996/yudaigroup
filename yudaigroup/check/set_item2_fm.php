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
$tablerate = $ybase->mbscale(5);

/////////////////////////////////////////

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$addsql = "";
$param = "";
//////////////////////////////////////////

$ybase->title = "チェック項目設定";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("チェック項目設定");

$ybase->ST_PRI .= <<<HTML
<script src="./js/check_update.js?$time"></script>
<input type="hidden" name="scriptno" value="3" id="scriptno">

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary" href="./set_top.php?target_month=$target_month&target_shop_id=$target_shop_id" role="button">設定TOPに戻る</a></div>
<h5 style="text-align:center;">チェック項目設定</h5>

<p></p>
<p>$notice</p>
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <tbody>
HTML;

$sql = "select category_id,cate_name from ck_category_list where status = '1' order by category_id";
$result0 = $ybase->sql($conn,$sql);
$num0 = pg_num_rows($result0);
for($ii=0;$ii<$num0;$ii++){
	list($q_category_id,$q_cate_name) = pg_fetch_array($result0,$ii);
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="#dddddd">
<th colspan="2">
　{$q_cate_name}</th>
</tr>
<tr align="center" bgcolor="#eeeeee">
<th width="40">
no</th>
<th>
チェック項目</th>
</tr>

HTML;

$sql = "select item_id,item_name from ck_item_list where category_id = $q_category_id and status = '1' order by item_name,add_date";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
for($i=0;$i<$num;$i++){
	list($q_item_id,$q_item_name) = pg_fetch_array($result,$i);
$k = $i + 1;
$ybase->ST_PRI .= <<<HTML
<tr>
<td align="center">
$k
</td>
<td align="left">
<input type="text" name="itemname{$q_item_id}" value="$q_item_name" class="form-control" id="itemname{$q_item_id}">
</td>
</tr>
HTML;

}
$ybase->ST_PRI .= <<<HTML
<tr>
<td colspan="2">
<a href="set_item2_in_item.php?category_id=$q_category_id" class="btn btn-sm btn-outline-secondary">チェック項目追加</a>
</td>
</tr>
HTML;



}

$ybase->ST_PRI .= <<<HTML

 </tbody>
</table>
</div>
</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>