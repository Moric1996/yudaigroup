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

//print_r($file_list2) ;



$ybase->title = "スクパス特定商表示管理";

$ybase->HTMLheader();

$path = $_SERVER['HTTP_HOST'].$ybase->PATH."inc/easyui";

$ybase->ST_PRI .= <<<HTML
<link rel="stylesheet" href="pdfdoc.css?4">
<link rel="stylesheet" href="fileupload.css?4">
<link rel="stylesheet" type="text/css" href="https://{$path}/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="https://{$path}/themes/icon.css">
<script type="text/javascript" src="https://{$path}/jquery.easyui.min.js"></script>

HTML;

$ybase->ST_PRI .= $ybase->header_pri($ybase->title);

$ybase->ST_PRI .= <<<HTML
<div class="row">

<div class="col-md-4">
<div class="sidebar-nav affix" role="complementary" style="padding: 10px;">


<span class="boldtxt">{$ybase->docu_kind_list[$kind]}</span>
<ul id="filelist" class="easyui-tree" data-options="url:'tree_json.php?fstate=open',method:'get',animate:true,dnd:true,lines:true">

</ul>

<script>
$(document).ready(function(){
	$("#filelist").tree();
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
	onDblClick: function(node){
		$(this).tree('beginEdit',node.target);
	}
});
$('#filelist').tree({
	onAfterEdit: function(node){
//		$(this).tree('endEdit',node.target);
		var parentobj =$(this).tree('getParent',node.target);
		if(parentobj){
			var parentID = parentobj.id;
		}else{
			var parentID = "";
		}
		$.ajax({
			type: "GET",
			url: "treeedit_ex.php",
			dataType: "text",
			data:{
				'nodeid': node.id,
				'newtext': node.text,
				'parentid': parentID
                    }
		});
	}

});
//$('#filelist').tree({
//	onDrop: function(targetNode,source,point){
//		var targetId = $('#filelist').tree('getNode', targetNode).id;
//		alert(targetId + source.id + point);  // alert node text property when clicked
//		$.ajax({
//			type: "GET",
//			url: "treednd_ex.php",
//			dataType: "text",
//			data:{
//				'sourceid': source.id,
//				'targetid': targetId,
//				'droppoint': point
  //                  }
//		});
//	}
//});
$('#filelist').tree({
	onClick: function(node){
		var parentobj =$(this).tree('getParent',node.target);
		if(parentobj){
			var parentID = parentobj.id;
		}else{
			var parentID = "";
		}
		$('#form_insert_nodeid').val(node.id);
		$('#form_insert_parentid').val(parentID);

		if($("#deletemode").prop("checked") == true) {
			if(!confirm('「' + node.text +'」を削除しますか？')){
				return false;
			}else{
				$(this).tree('remove',node.target);

				$.ajax({
					type: "GET",
					url: "treedelete_ex.php",
					dataType: "text",
					data:{
						'nodeid': node.id,
						'parentid': parentID
					}
				});
			}
		}
	}
});
$(function() {
	$('#folderinsert').click(function() {
		$.ajax({
			type: "GET",
			url: "treeinsertfolder_ex.php",
			dataType: "text",
		});
		var nodes = $('#filelist').tree('reload');
	});
});
$(function() {
	$('#formupfilesubmit').click(function() {
//		var targetId = $('#filelist').tree('getSelected');
//		if(targetId){
//			targetId = targetId.id;
//		}else{
//			targetId = "";
//		}
//		var parentobj =$('#filelist').tree('getParent',targetId.target);
//		if(parentobj){
//			var parentID = parentobj.id;
//		}else{
//			var parentID = "";
//		}
//		$('#form_insert_nodeid').val(targetId);
//		$('#form_insert_parentid').val(parentID);
		$('#formupfile').submit();
	});
});
</script>


</div>
</div>

<div class="col-md-8" id="iframediv">

<br>
<br>
<div class="form-check">
  <input class="form-check-input" type="checkbox" id="deletemode">
  <label class="form-check-label" for="deletemode">削除モード</label>
</div>
<br>
<br>

<a class="btn btn-outline-secondary btn-sm" id="folderinsert">フォルダ追加</a>
<br>
<br>

<div class="col-md-6">
<form method="post" action="treeinsertfile_ex.php" id="formupfile" enctype="multipart/form-data">
<input type="hidden" name="insert_nodeid" value="" id="form_insert_nodeid">
<input type="hidden" name="insert_parentid" value="" id="form_insert_parentid">
<div class="form-group files">
<label>ファイル追加</label>
<input type="file" name="uploadfile[]" class="form-control" multiple="multiple" accept=".pdf,.html,.htm,.gif,.jpg,.png,.txt">
</div>
</form>
<div style="text-align: center;"><button class="btn btn-outline-secondary btn-sm" id="formupfilesubmit">ファイルアップロード</button></div>

</div>
<br>
<small>※フォルダの追加方法-「フォルダ追加」ボタンを押すと一番上に追加されます</small><br>
<small>※名前修正方法-該当のファイルまたはフォルダをダブルクリックすると編集可能となります</small><br>
<small>※ファイル・フォルダの移動は無効です</small><br>
<small>※削除方法-「削除モード」にチェックを入れて、該当のフォルダを削除すると削除されます。フォルダを削除すると中のファイルも削除されます</small><br>
<small>※「http://afc.yournet-jp.com/〇〇〇/」とフォルダ名を〇〇〇に入れたURLになります</small><br><br>

<div class="col-md-6">
<div style="text-align: center;">
<a href="./dl.php" type="button" class="btn btn-sm btn-dark">サンプルindex.htmlダウンロード</a>
</div></div>

    </div>
    </div>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>