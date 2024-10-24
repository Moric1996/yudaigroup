/*!
 * input からのajax　DB UPDATE
 */
$(function() {
	$('table input,table select').change(function() {
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
		target_item_id: $(this).attr('item_id'),
		target_month: $('#target_month').val(),
		target_shop_id: $('#target_shop_id').val(),
		target_day: $('#target_day').val(),
		target_employee_id: $('#target_employee_id').val(),
		target_employee_id2: $(this).attr('target_employee_id'),
		target_group_id: $(this).attr('target_group_id'),
		target_check: $(this).prop('checked')
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

(function($){
$.fn.ajaxchange_js = function(){
	var script_no = $('#scriptno').val();
	$.ajax('ajaxchange' + script_no + '.php',
	{
	type: 'post',
	data: {
		var_name: $(this).attr('name'),
		var_val: $(this).val(),
		target_item_id: $(this).attr('item_id'),
		target_month: $('#target_month').val(),
		target_shop_id: $('#target_shop_id').val(),
		target_day: $('#target_day').val(),
		target_employee_id: $('#target_employee_id').val(),
		target_employee_id2: $(this).attr('target_employee_id'),
		target_group_id: $(this).attr('target_group_id')
	},
	dataType: 'text'
	}
	)	// 成功時にはページに結果を反映
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
};
})( jQuery );

///////////////////////////////////////////////////////////////////////////