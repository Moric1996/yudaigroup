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

$addtitle = "　<a href=\"./view.php?kind=$kind&company_id=$company_id\" class=\"btn btn-secondary btn-sm\">編集終了</a>　<span style=\"color:#cc7755;font-size:120%\">編集ページ</span>";

$ybase->HTMLheader();

$path = $_SERVER['HTTP_HOST'].$ybase->PATH."inc/easyui";

$ybase->ST_PRI .= <<<HTML
<link rel="stylesheet" href="pdfdoc.css?4">
<link rel="stylesheet" href="fileupload.css?4">
<link rel="stylesheet" type="text/css" href="https://{$path}/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="https://{$path}/themes/icon.css">
<script type="text/javascript" src="https://{$path}/jquery.easyui.min.js"></script>

HTML;

$ybase->ST_PRI .= $ybase->header_pri($ybase->title.$addtitle);

$ybase->ST_PRI .= <<<HTML
<div class="row">

<div class="col-md-4">
<div class="sidebar-nav affix" role="complementary" style="padding: 10px;">


<span class="boldtxt">{$ybase->docu_kind_list[$kind]}</span>
<ul id="filelist" class="easyui-tree" data-options="url:'tree_json.php?fstate=open&kind=$kind&company_id=$company_id',method:'get',animate:true,dnd:true,lines:true">

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
	onDblClick: function(node){
		$(this).tree('beginEdit',node.target);
	}
});
$('#filelist').tree({
	onAfterEdit: function(node){
//		$(this).tree('endEdit',node.target);
		$.ajax({
			type: "GET",
			url: "treeedit_ex.php",
			dataType: "text",
			data:{
				'nodeid': node.id,
				'newtext': node.text,
				'kind': $kind,
				'company_id': $company_id
                    }
		});
	}

});
$('#filelist').tree({
	onDrop: function(targetNode,source,point){
		var targetId = $('#filelist').tree('getNode', targetNode).id;
//		alert(targetId + source.id + point);  // alert node text property when clicked
		$.ajax({
			type: "GET",
			url: "treednd_ex.php",
			dataType: "text",
			data:{
				'sourceid': source.id,
				'targetid': targetId,
				'droppoint': point,
				'kind': $kind,
				'company_id': $company_id
                    }
		});
	}
});
$('#filelist').tree({
	onClick: function(node){
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
						'kind': $kind,
						'company_id': $company_id
					}
				});
			}
		}
	}
});
$(function() {
	$('#folderinsert').click(function() {
		var targetId = $('#filelist').tree('getSelected');
		if(targetId){
			targetId = targetId.id;
		}else{
			targetId = "";
		}
		$.ajax({
			type: "GET",
			url: "treeinsertfolder_ex.php",
			dataType: "text",
			data:{
				'insert_nodeid': targetId,
				'kind': $kind,
				'company_id': $company_id
			}
		});
		var nodes = $('#filelist').tree('reload');
	});
});
$(function() {
	$('#formupfilesubmit').click(function() {
		var targetId = $('#filelist').tree('getSelected');
		if(targetId){
			targetId = targetId.id;
		}else{
			targetId = "";
		}
		$('#form_insert_nodeid').val(targetId);
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
<input type="hidden" name="kind" value="$kind">
<input type="hidden" name="company_id" value="$company_id">
<input type="hidden" name="insert_nodeid" value="" id="form_insert_nodeid">
<div class="form-group files">
<label>ファイル追加</label>
<input type="file" name="uploadfile[]" class="form-control" multiple="multiple" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.mp4,.mov,.avi,.webm,.mpeg,.mpg,.txt">
</div>
<input type="checkbox" name="topdf_flag" value="1">WORD,EXCEL,画像,TEXTファイルをPDFに変換
</form>
<div style="text-align: center;"><button class="btn btn-outline-secondary btn-sm" id="formupfilesubmit">ファイルアップロード</button></div>

</div>
<br>
<small>※追加できるファイルはPDFファイル(.pdf)、ワードファイル(.doc,.docx)、エクセルファイル(.xls,.xlsx)、パワーポイントファイル(.ppt,.pptx)、イメージファイル(.jpg,.png,.gif)、テキストファイル(.txt)、動画ファイル(.mp4,.mpeg,.mpg,.mov,.avi,.webm)に限ります</small><br>
<small>※PDFに変換できるのは、(.doc,.docx,.xls,.xlsx,.jpg,.png,.gif,.txt)のファイルのみになります</small><br>
<small>※(.doc,.docx,.xls,.xlsx,.ppt,.pptx)のファイルはダウンロード形式となります(中身の表示はされません)</small><br>
<small>※フォルダの追加方法-「フォルダ追加」ボタンを押すとツリーの選択された場所に追加されます(選択が無い場合は一番上)</small><br>
<small>※名前修正方法-該当のファイルまたはフォルダをダブルクリックすると編集可能となります</small><br>
<small>※順番の移動方法-該当のファイルまたはフォルダをドラッグ＆ドロップすることで移動できます</small><br>
<small>※削除方法-「削除モード」にチェックを入れて、該当のファイルまたはフォルダを削除すると削除されます。フォルダを削除すると中のファイルも削除されます</small><br>


    </div>
    </div>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>