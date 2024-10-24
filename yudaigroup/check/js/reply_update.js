/*!
 * input からのajax　DB UPDATE
 */
$(function() {
	$('textarea').change(function() {
	var script_no = $('#scriptno').val();
	var checkid = $(this).attr('id');
		if(checkid == 'target_day_select'){
		return;
	}
	$.ajax('ajaxchange' + script_no + '.php',
	{
	type: 'post',
	data: {
		var_name: $(this).attr('name'),
		var_val: $(this).val(),
		target_ckaction_list_id: $(this).attr('ckaction_list_id'),
		t_ckaction_id: $('#t_ckaction_id').val(),
		target_ckaction_id: $('#target_ckaction_id').val(),
		target_shop_id: $('#target_shop_id').val()
	},
	dataType: 'text'
	}
	)
	// 成功時にはページに結果を反映
	.done(function(data) {
		if(data != 'OK'){
		window.alert('エラーが発生しました\n今入力したデータは反映されていません');
		}
		console.log(data);
	})
	 // 失敗時には、その旨をダイアログ表示
	.fail(function(data) {
		window.alert('エラーが発生しました\n今入力したデータは反映されていません');
	});

	});
});

///////////////////////////////////////////////////////////////////////////