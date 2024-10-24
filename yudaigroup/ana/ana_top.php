<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

//$edit_f = 1;
/////////////////////////////////////////

$ybase->make_shop_list();
$ybase->make_employee_list("1");
if(!$shop_id){
if(array_key_exists((int)$ybase->my_section_id,$ybase->shop_list)){
	$shop_id = $ybase->my_section_id;
}else{
	$shop_id = 0;
}
}

$pos_shopno = $ybase->section_to_pos[$shop_id];
if(!$pos_shopno){
	$pos_shopno = 0;
}
if($selyymmdd){
	$yy = substr($selyymmdd,0,4);
	$mm = substr($selyymmdd,5,2);
	$dd = substr($selyymmdd,8,2);
}
if(!$yy || !$mm || !$dd){
	$now_yy = date('Y');
	$now_mm = date('m');
	$now_dd = date('d');
	$yy = date('Y',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$mm = date('m',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
	$dd = date('d',mktime(0,0,0,$now_mm,$now_dd - 1,$now_yy));
}
$selyymmdd = "$yy-$mm-$dd";

$conn = $ybase->connect();

$ybase->title = "店舗分析";

$ybase->HTMLheader();


$ybase->ST_PRI .= $ybase->header_pri($ybase->title);


$ybase->ST_PRI .= <<<HTML
<div class="container">

<p></p>
<div style="font-size:100%;margin:5px;">

<p></p>
<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">



<tbody>
HTML;

$ybase->ST_PRI .= <<<HTML
<tr>
<td><a href="./manage_money.php">レジ現金出納(試用バージョン)</a></td>
</tr>
<tr>
<td><a href="./ztotal_discount.php">割引集計</a></td>
</tr>
<tr>
<td><a href="./ztotal_note.php">金券集計</a></td>
</tr>
<tr>
<td><a href="./ztotal_pay.php">支払いメディア(その他掛)集計</a></td>
</tr>
<tr>
<td><a href="./ztotal_takeout.php">店内テイクアウト内訳</a></td>
</tr>
<tr>
<td><a href="./gotoeat.php">GOtoEAT分析</a></td>
</tr>
<tr>
<td><a href="./zitem_view_nolimit.php">カルビ一丁食べ放題分析</a></td>
</tr>

</tbody>
</table>
</div>


<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">



<tbody>
HTML;

$ybase->ST_PRI .= <<<HTML
<tr>
<td><a href="./pay_media.php">支払い種別分析</a></td>
</tr>
<tr>
<td><a href="./ztime_view.php">時間帯別集計</a></td>
</tr>

<tr>
<td><a href="./ztair_view.php">滞在時間集計</a></td>
</tr>

<tr>
<td><a href="./zkkum_view.php">客組数集計</a></td>
</tr>

<tr>
<td><a href="./zkkak_view.php">価格帯集計</a></td>
</tr>

<tr>
<td><a href="./zitem_view.php">商品別集計</a></td>
</tr>

<tr>
<td><a href="./zfree_view.php">フリー集計</a></td>
</tr>

<tr>
<td><a href="./zfloor_view.php">フロア集計</a></td>
</tr>

<tr>
<td><a href="./zdealer_view.php">扱者集計</a></td>
</tr>

<tr>
<td><a href="./zfuse_view.php">オーダー担当者集計</a></td>
</tr>

<tr>
<td><a href="./zticket_view.php">券類集計</a></td>
</tr>

<tr>
<td><a href="./zaudit_view.php">監査リスト</a></td>
</tr>

</tbody>
</table>
</div>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">



<tbody>
HTML;

$ybase->ST_PRI .= <<<HTML
<tr>
<td><b>各種履歴</b></td>
</tr>
<tr>
<td><a href="./titem_view.php">商品明細トランザクション</a></td>
</tr>

<tr>
<td><a href="./tslip_view.php">伝票トランザクション</a></td>
</tr>

<tr>
<td><a href="./ttotal_view.php">合計トランザクション</a></td>
</tr>

<tr>
<td><a href="./ttend_view.php">支払明細トランザクション</a></td>
</tr>

</tbody>
</table>
</div>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">



<tbody>
HTML;

$ybase->ST_PRI .= <<<HTML
<tr>
<td><b>ポイントカード管理</b></td>
</tr>
<tr>
<td><a href="./mpoint_ttotal_pt.php">トランザクションポイントカード履歴突合せ</a></td>
</tr>
<tr>
<td><a href="./mpoint_sum.php">ポイントカード月集計</a></td>
</tr>

<tr>
<td><a href="./mpoint_pt_view.php">ポイントカード履歴</a></td>
</tr>

<tr>
<td><a href="./mpoint_ttotal_view.php">ポイント用トランザクション確認</a></td>
</tr>

</tbody>
</table>
</div>

<div class="table-responsive">

<table class="table table-sm table-bordered table-striped" id="table1">



<tbody>
HTML;

$ybase->ST_PRI .= <<<HTML
<tr>
<td><b>設定</b></td>
</tr>
<tr>
<td><a href="./set_lunch.php">ランチ/ディナー時間設定</a></td>
</tr>


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