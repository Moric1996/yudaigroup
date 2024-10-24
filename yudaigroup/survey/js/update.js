/*!
 * input からのajax　DB UPDATE
 */
$(function() {
	$('input,select[id^=number],textarea').change(function() {

	$.ajax('ajaxchange_survey.php',
	{
	type: 'post',
	data: {
		var_name: $(this).attr('name'),
		var_val: $(this).val(),
		survey_set_id: $('#survey_set_id').val(),
		shop_id: $('#shop_id').val(),
		colname: $(this).attr('colname'),
		card_type: $(this).attr('card_type'),
		card_id: $(this).attr('card_id'),
		option_id: $(this).attr('option_id')
	},
	dataType: 'text'
	}
	)
	// 成功時にはページに結果を反映
	.done(function(data) {
		if(data != 'OK'){
		window.alert('エラーが発生しました\\n今入力したデータは反映されていません');
		}
		console.log(data);
	})
	 // 失敗時には、その旨をダイアログ表示
	.fail(function(data) {
		window.alert('エラーが発生しました\\n今入力したデータは反映されていません');
	});

	});
});

$(function() {
	$('button[id^=requirecg]').click(function() {
	var survey_set_id = $('#survey_set_id').val();
	$.ajax('ajaxchange_survey_require.php',
	{
	type: 'post',
	data: {
		survey_set_id: $('#survey_set_id').val(),
		shop_id: $('#shop_id').val(),
		card_id: $(this).attr('card_id'),
		torequire: $(this).attr('torequire')
	},
	dataType: 'text'
	}
	)
	// 成功時にはページに結果を反映
	.done(function(data) {
		if(data != 'OK'){
		window.alert('エラーが発生しました\\n今入力したデータは反映されていません');
		}
		console.log(data);
		location.reload();
	})
	 // 失敗時には、その旨をダイアログ表示
	.fail(function(data) {
		window.alert('エラーが発生しました\\n今入力したデータは反映されていません');
	});

	});
});

$(function() {
	$('button[id^=optionadd]').click(function() {
	var survey_set_id = $('#survey_set_id').val();
	$.ajax('ajaxchange_survey_optionadd.php',
	{
	type: 'post',
	data: {
		survey_set_id: $('#survey_set_id').val(),
		shop_id: $('#shop_id').val(),
		card_id: $(this).attr('card_id')
	},
	dataType: 'text'
	}
	)
	// 成功時にはページに結果を反映
	.done(function(data) {
		if(data != 'OK'){
		window.alert('エラーが発生しました\\nデータは反映されていません');
		}
		console.log(data);
		location.reload();
	})
	 // 失敗時には、その旨をダイアログ表示
	.fail(function(data) {
		window.alert('エラーが発生しました\\nデータは反映されていません');
	});

	});
});

$(function() {
	$('button[id^=optiondel]').click(function() {
	var survey_set_id = $('#survey_set_id').val();
	$.ajax('ajaxchange_survey_optiondel.php',
	{
	type: 'post',
	data: {
		survey_set_id: $('#survey_set_id').val(),
		shop_id: $('#shop_id').val(),
		card_id: $(this).attr('card_id'),
		option_id: $(this).attr('option_id')
	},
	dataType: 'text'
	}
	)
	// 成功時にはページに結果を反映
	.done(function(data) {
		if(data != 'OK'){
		window.alert('エラーが発生しました\\nデータは削除されていません');
		}
		console.log(data);
		location.reload();
	})
	 // 失敗時には、その旨をダイアログ表示
	.fail(function(data) {
		window.alert('エラーが発生しました\\nデータは削除されていません');
	});

	});
});

$(function() {
	$('button[id^=optionother]').click(function() {
	var survey_set_id = $('#survey_set_id').val();
	$.ajax('ajaxchange_survey_optionother.php',
	{
	type: 'post',
	data: {
		survey_set_id: $('#survey_set_id').val(),
		shop_id: $('#shop_id').val(),
		card_id: $(this).attr('card_id'),
		option_id: $(this).attr('option_id')
	},
	dataType: 'text'
	}
	)
	// 成功時にはページに結果を反映
	.done(function(data) {
		if(data != 'OK'){
		window.alert('エラーが発生しました\\nデータは反映されていません');
		}
		console.log(data);
		location.reload();
	})
	 // 失敗時には、その旨をダイアログ表示
	.fail(function(data) {
		window.alert('エラーが発生しました\\nデータは反映されていません');
	});

	});
});

$(function() {
	$('select[id^=type_]').change(function() {
	var survey_set_id = $('#survey_set_id').val();
	$.ajax('ajaxchange_survey_cardtype.php',
	{
	type: 'post',
	data: {
		var_name: $(this).attr('name'),
		var_val: $(this).val(),
		survey_set_id: $('#survey_set_id').val(),
		shop_id: $('#shop_id').val(),
		card_id: $(this).attr('card_id')
	},
	dataType: 'text'
	}
	)
	// 成功時にはページに結果を反映
	.done(function(data) {
		if(data != 'OK'){
		window.alert('エラーが発生しました\\nデータは反映されていません');
		}
		console.log(data);
		location.reload();
	})
	 // 失敗時には、その旨をダイアログ表示
	.fail(function(data) {
		window.alert('エラーが発生しました\\nデータは反映されていません');
	});

	});
});

///////////////////////////////////////////////////////////////////////////