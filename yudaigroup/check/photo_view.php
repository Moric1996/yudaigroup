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

$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;

$category_list = $check->category_make();
$item_list = $check->item_make();

/////////////////////////////////////////
if(!$ybase->my_position_class || ($ybase->my_position_class > 40)){
	$check->protect = 1;
}

$conn = $ybase->connect();

//////////////////////////////////////////条件
$param = "t_shop_id=$t_shop_id";
$addsql = "section_id = $t_shop_id";
//////////////////////////////////////////
if(!preg_match("/^[0-9]+$/",$t_shop_id)){
	$ybase->error("パラメーターエラー");
}
if(!preg_match("/^[0-9]+$/",$t_ckaction_list_id)){
	$ybase->error("パラメーターエラー");
}
if(!preg_match("/^[0-9]+$/",$pno)){
	$ybase->error("パラメーターエラー");
}

$sql = "select photo from ck_check_action_list where ckaction_list_id = $t_ckaction_list_id and section_id = '$t_shop_id' and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データエラー");
}
list($q_photo) = pg_fetch_array($result,0);
$q_photo = trim($q_photo);
if(!$q_photo){
	$ybase->error("画像がありません");
}
$photo_arr = json_decode($q_photo,true);
$filename = $photo_arr[$pno];


/////////////////////データ取得
$ybase->title = "画像表示";

$ybase->HTMLheader();

$ybase->ST_PRI .= <<<HTML
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="./js/check_update.js?102"></script>
<script type="text/javascript" src="./js/jquery.longpress.js"></script>
<script type="text/javascript">
$(function(){
	$("[id^=photodel]").click(function(){
		var delurl = $(this).attr('delhref');
		if(!confirm('画像を本当に削除しますか？')){
			return false;
		}else{
			location.href = delurl;
		}
	});
	$('#closewin').click(function(){
	window.onload = window.opener.location.reload();
		window.close();
		return false;
	});
});
</script>
<div class="container">

<div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
  <div class="carousel-inner">
HTML;
if(!isset($photo_arr[$pno])){
	reset($photo_arr);
	$pno = key($photo_arr);
}
foreach($photo_arr as $key => $val){
	if("$key" == "$pno"){
		$active = "active";
	}else{
		$active = "";
	}

if($check->protect != 1){
	$deldoc = "<div class=\"text-center\"><a type=\"button\" class=\"btn btn-danger m-1\" href=\"#\" delhref=\"./photo_del.php?t_shop_id={$t_shop_id}&t_ckaction_list_id={$t_ckaction_list_id}&pno={$key}&backscript=photo_view.php\" id=\"photodel{$key}\">削除</a>
    </div>";
}else{
	$deldoc = "";
}
$ybase->ST_PRI .= <<<HTML
    <div class="carousel-item $active">
      <img class="bd-placeholder-img bd-placeholder-img-lg d-block w-100" src="./view.php?t_shop_id={$t_shop_id}&t_ckaction_list_id={$t_ckaction_list_id}&pno={$key}">
{$deldoc}
</div>
HTML;

}
$ybase->ST_PRI .= <<<HTML
  </div>
  <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
</div>
<div class="text-center">
<a type="button" class="btn btn-secondary m-1" href="#" id="closewin">CLOSE</a>
</div>


</div>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>