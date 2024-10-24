<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

//$kind = 1;
$company_id = 1;
/////////////////////////////////////////
if(!$kind){
	$ybase->error("パラメーターエラー");
}

$conn = $ybase->connect();
$base_sql = "select file_id,company_id,kind,parent_id,type,displayname,filename,employee_id,section_auth,employee_type_auth,position_class_auth,sortno,add_date from docu_manage where kind = $kind and company_id = $company_id and status = '1'";

$sql = "{$base_sql} and parent_id = 0 order by sortno";


$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
//	$ybase->error("対象文書がありません");
}



$ybase->title = $ybase->docu_kind_list[$kind];

if($ybase->my_admin_auth == "1"){
	$addtitle = "　<a href=\"./viewedit.php?kind=$kind&company_id=$company_id\" class=\"btn btn-secondary btn-sm\">編集する</a>";
}else{
	$addtitle = "";
}

$ybase->HTMLheader();

$path = $_SERVER['HTTP_HOST'].$ybase->PATH."inc/easyui";

$ybase->ST_PRI .= <<<HTML
<link rel="stylesheet" href="pdfdoc.css?7">
<link rel="stylesheet" type="text/css" href="https://{$path}/themes/default/easyui.css?7">
<link rel="stylesheet" type="text/css" href="https://{$path}/themes/icon.css?7">
<script type="text/javascript" src="https://{$path}/jquery.easyui.min.js?7"></script>

HTML;

$ybase->ST_PRI .= $ybase->header_pri($ybase->title.$addtitle);

$ybase->ST_PRI .= <<<HTML
<div class="row">

<div class="col-md-3">
<div class="sidebar-nav affix" role="complementary" style="padding: 10px;">


<span class="boldtxt">{$ybase->docu_kind_list[$kind]}</span>
<ul id="filelist" class="easyui-tree" data-options="url:'tree_json.php?kind=$kind&company_id=$company_id',method:'get',animate:true,dnd:false,lines:true">

</ul>

<script>
$(document).ready(function(){
	$("#filelist").treeview();
});
//画面高さに合わせてiframeをリサイズ
$(function(){
	var wH = $(window).height();
	var hH = wH - 50;

  $('#pdfiframe').css('height', hH + 'px');
});
//PDF切替
$(function() {
	$("a[class='pdfjump']").click(function() {
		var jurl = $(this).attr('value');
		$("#pdfiframe").attr("src",jurl);
	});
});
$('#filelist').tree({
	onClick: function(node){
		if(node.ext == "pdf"){
			var jurl = "../inc/pdfjs/web/viewer2.html?file=/yudaigroup/documanage/viewpdf2.php%3Ffile_id%3D" + node.id;
		}
		if(node.ext == ""){
			var jurl = "../inc/pdfjs/web/viewer2.html?file=/yudaigroup/documanage/viewpdf2.php%3Ffile_id%3D" + node.id;
		}
		if((node.ext == "mpeg")||(node.ext == "mp4")||(node.ext == "mov")||(node.ext == "mpg")||(node.ext == "avi")||(node.ext == "webm")){
			var jurl = "./movieviewer2.php?company_id=$company_id&file_id=" + node.id;
		}
		if((node.ext == "jpg")||(node.ext == "jpeg")||(node.ext == "png")||(node.ext == "gif")||(node.ext == "txt")){
			var jurl = "./movieviewer2.php?company_id=$company_id&file_id=" + node.id;
		}
		if((node.ext == "doc")||(node.ext == "xls")||(node.ext == "ppt")||(node.ext == "docx")||(node.ext == "xlsx")||(node.ext == "pptx")){
			var jurl = "./msviewer2.php?company_id=$company_id&file_id=" + node.id;
		}
		if(node.types != "folder"){
		$("#pdfiframe").attr("src",jurl);
		}
	}
});



</script>


</div>
</div>

<div class="col-md-9" id="iframediv">

<iframe src="../inc/pdfjs/web/viewer2.html?file=" width="100%" height="100%" id="pdfiframe"></iframe>
    </div>
    </div>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>