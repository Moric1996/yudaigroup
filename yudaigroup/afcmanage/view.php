<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
//$ybase->session_get();

//$kind = 1;
$company_id = 5;
$dirname = "/usr/local/htdocs/afc";
$deldir = array('.','..','app','css','asfs','webAd');

//引数 $file_list2 配列の[N][1] でソートする関数
function order_by_desc($a, $b){
	if ( strtotime($a[1]) > strtotime($b[1]) ){
		return -1;
	}elseif(strtotime($a[1]) < strtotime($b[1])) {
		return 1;
	}else{
		return 0;
	}
}

/////////////////////////////////////////
if(!$kind){
//	$ybase->error("パラメーターエラー");
}
$dir_h = opendir("$dirname");

while (false !== ($file_list[] = readdir($dir_h))) ;
closedir( $dir_h ) ;

$file_list = array_diff($file_list,$deldir);
$file_list2 = array();

$i = 0 ;
foreach($file_list as $file_name){
//$file_list2[N] の [0]にファイル名、[1]にファイル更新日
	$file_list2[$i][0] = "$file_name";
// ファイルの更新日時を取得
	$file_list2[$i][1] = date("Y/m/d H:i", filemtime( "$dirname"."/".$file_name )) ;
	$i++ ;
}

//$file_list2 をファイルの更新日時でソート
usort($file_list2,'order_by_desc');

print_r($file_list2) ;


exit;

$base_sql = "select file_id,company_id,kind,parent_id,type,displayname,filename,employee_id,section_auth,employee_type_auth,position_class_auth,sortno,add_date from docu_manage where kind = $kind and company_id = $company_id and status = '1'";

$sql = "{$base_sql} and parent_id = 0 order by sortno";


$ybase->title = "スクパス特定商表示管理";

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
<ul id="filelist" class="easyui-tree" data-options="url:'tree_json.php',method:'get',animate:true,dnd:false,lines:true">

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
		if(node.types != "folder"){
		$("#pdfiframe").attr("src",jurl);
		}
	}
});



</script>


</div>
</div>

    </div>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>