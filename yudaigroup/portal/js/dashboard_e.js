$(function() {
	$("#sortable1,#sortable2").sortable({
		connectWith: ".connectedSortable",
		update: function(e,ui){
			var parid = $(this).attr('id');
			var myid  = ui.item.attr("id");
			$.post("ajaxchange_dashboard_sort.php",{parentid: parid,uiid: myid,sort: $(this).sortable("toArray")},
				function(data){
					if(data != 'OK'){
					window.alert('エラーが発生しました\\n更新してください');
					}
					console.log(data);
			});
		}
	}).disableSelection();
});
$(function() {
	$("#boardon").change(function() {
	$.ajax('ajaxchange_top_dashboard.php',
	{
	type: 'post',
	data: {
		var_name: $(this).attr('name'),
		var_val: $(this).val(),
		var_check: $(this).prop('checked')
	},
	dataType: 'text'
	}
	)
	// 成功時にはページに結果を反映
	.done(function(data) {
		if(data != 'OK'){
		window.alert('エラーが発生しました\\n今の変更は反映されていません');
		}
		console.log(data);
	})
	 // 失敗時には、その旨をダイアログ表示
	.fail(function(data) {
		window.alert('エラーが発生しました\\n今の変更は反映されていません');
	});

	});
});