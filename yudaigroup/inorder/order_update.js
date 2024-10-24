/*!
 * input からのajax　DB UPDATE
 */
$(function() {
	$('input,select,textarea').not('#attachfile').change(function() {
	$.ajax('order_ajax1.php',
	{
	type: 'post',
	data: {
		var_name: $(this).attr('name'),
		var_val: $(this).val(),
		target_order_id: $('#t_order_id').val(),
		target_tool_id: $(this).attr('tool_id'),
		target_shooting_id: $(this).attr('shooting_id'),
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
	$.ajax('order_ajax1.php',
	{
	type: 'post',
	data: {
		var_name: $(this).attr('name'),
		var_val: $(this).val(),
		target_order_id: $(this).attr('t_order_id'),
		target_tool_id: $(this).attr('tool_id'),
		target_shooting_id: $(this).attr('shooting_id'),
		target_check: $(this).prop('checked')
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