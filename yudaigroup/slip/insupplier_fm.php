<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
if(isset($_POST)){
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
}
if(isset($_GET)){
	foreach($_GET as $key => $value){
		${$key} = $value;
	}
}
include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/slip.inc');

$ybase = new ybase();
$slip = new slip();

$ybase->session_get();
$ybase->my_company_id = 5;

$ybase->make_yournet_employee_list("1");
$slip->supplier_make();
/////////////////////////////////////////
if(!$charge_emp){
	$charge_emp = $ybase->my_employee_id;
}
$n_yy = date("Y");
$n_mm = date("m");
$n_dd = date("d");
if(!$target_date){
	$target_date = date("Y-m-d");
}
if(!$target_month){
	$target_month = date("Y-m",mktime(0,0,0,$n_mm - 1,1,$n_yy));
}
if(!$pay_date){
	$pay_date = date("Y-m-d",mktime(0,0,0,$n_mm + 1,0,$n_yy));
}

$ybase->title = "新規伝票作成";
$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("伝票管理");
$ybase->ST_PRI .= <<<HTML
<link rel="stylesheet" href="./inc/fileupload.css?4">
<link rel="stylesheet" type="text/css" href="https://yournet-jp.com/yudaigroup/inc/easyui/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="https://yournet-jp.com/yudaigroup/inc/easyui/themes/icon.css">
<script type="text/javascript" src="https://yournet-jp.com/yudaigroup/inc/easyui/jquery.easyui.min.js"></script>
<script>
$(function(){
	$("textarea").attr("rows", 2).on("input", e => {
		$(e.target).height(0).innerHeight(e.target.scrollHeight);
	});
});
</script>
HTML;

$ybase->ST_PRI .= <<<HTML
<div class="container">
<table class="table table-bordered table-sm mx-auto small text-center">
<tbody>
<tr>
<td>
<a href="./newslip_fm.php">新規伝票作成</a>
</td>
<td>
<a href="./slip_list.php">伝票管理</a>
</td>
<td class="table-active">
取引先取込
</td>
</tr>
</tbody>
</table>

<p></p>
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">取引先取込</div>
<div class="card-body">
<form action="insupplier_ex.php" method="post" enctype="multipart/form-data" id="form01">

<div class="text-center">
</div>


<div class="form-row">
<div class="form-group files col-sm-12">
<label>【取引先リストファイル】</label>
<input type="hidden" name="MAX_FILE_SIZE" value="50000000">
<input type="file" name="attachfile" class="form-control-file form-control-sm" id="attachfile" accept=".txt,.csv">
</div>
</div>


<p></p>
<br>
<button class="btn btn-secondary col-sm-2 offset-sm-4 border-dark" type="submit">取込</button>
<button class="btn btn-light col-sm-2 border-dark" type="reset">クリア</button>


</form>
</div>
</div>
<p></p>
※取引先リストファイルは「FX4」の「会社情報」→「64取引先情報の切り出し」→「テキストファイル」で作成してください。<br>
ファイルフォーマット（コード　頭フリガナ　取引先名）<br>


</div>
<p></p>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>