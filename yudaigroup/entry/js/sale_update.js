/*!
 * input からのajax　DB UPDATE
 */
$(function() {
	$('table input').change(function() {
	$.ajax('ajax_sale.php',
	{
	type: 'post',
	data: {
		var_name: $(this).attr('name'),
		var_val: $(this).val(),
		colum: $(this).attr('colum'),
		day: $(this).attr('day'),
		selyymm: $('#inmonth').val(),
		shop_id: $('#in_shop_id').val()
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