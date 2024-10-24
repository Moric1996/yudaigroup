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

$ybase->title = "カテゴリー設定";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("カテゴリー設定");

$ybase->ST_PRI .= <<<HTML
<script src="./js/check_update.js?$time"></script>
<input type="hidden" name="scriptno" value="2" id="scriptno">

<div class="container">
<p></p>
<div style="text-align:right;"><a class="btn btn-secondary" href="./set_top.php?target_month=$target_month&target_shop_id=$target_shop_id" role="button">設定TOPに戻る</a></div>
<h5 style="text-align:center;">カテゴリー設定</h5>

<p></p>
<p>$notice</p>
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:{$tablerate}%;">
  <thead>
<tr align="center" bgcolor="#eeeeee">
<th width="40">NO</th>
<th>カテゴリー</th>
</tr>
  </thead>
  <tbody>
HTML;

$sql = "select category_id,cate_name from ck_category_list where status = '1' order by category_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
for($i=0;$i<$num;$i++){
	list($q_category_id,$q_cate_name) = pg_fetch_array($result,$i);
$k=$i+1;
$ybase->ST_PRI .= <<<HTML
<tr>
<td align="center">
$k
</td>

<td align="left">
<input type="text" name="catename{$q_category_id}" value="$q_cate_name" class="form-control" id="catename{$q_category_id}" size="30">
</td>
</tr>
HTML;

}


$ybase->ST_PRI .= <<<HTML

 </tbody>
</table>
<a href="set_item_in_category.php?$param" class="btn btn-sm btn-outline-secondary">カテゴリー追加</a>
</div>
</div>
<p></p>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>