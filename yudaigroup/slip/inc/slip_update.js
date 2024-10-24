/*!
 * input からのajax　DB UPDATE
 */
$(function() {
	$("[id^=acok_]").click(function(){
	var my_id = $(this).attr('id');
	var slip_id = $(this).attr('slip_id');
	var accept_list_id = $(this).attr('accept_list_id');
	var myaccept_count = $(this).attr('myaccept_count');
	var no = $(this).attr('value');
	var my_name = $('#my_name').attr('value');
	var now = new Date();
	var mm = now.getMonth() + 1;
	var dd = now.getDate();
	var mm = ('0' + mm).slice(-2);
	var dd = ('0' + dd).slice(-2);
	var nowdate = mm + '/' + dd;
	var htmltxt = nowdate + ' ' + my_name;
	$.ajax('slip_ajax' + no + '.php',
	{
	type: 'post',
	data: {
		slip_id: $(this).attr('slip_id'),
		accept_list_id: $(this).attr('accept_list_id'),
		myaccept_count: $(this).attr('myaccept_count')
	},
	dataType: 'text'
	}
	)
	// 成功時にはページに結果を反映
	.done(function(data) {
		if(data == 'NG'){
		window.alert('エラーが発生しました\nデータは反映されていません');
		}else if(data == 'OK1'){
			$('#' + my_id).removeClass("btn-outline-secondary");
			$('#' + my_id).addClass("btn-success");
			$('#datename_' + slip_id + '_' + accept_list_id).text(htmltxt);
		console.log(htmltxt);
			var val = $('#' + my_id).attr('value','2');
		}else if(data == 'ALLOK'){
			$('#' + my_id).removeClass("btn-outline-secondary");
			$('#' + my_id).addClass("btn-success");
			$('#datename_' + slip_id + '_' + accept_list_id).text(htmltxt);
		console.log(htmltxt);
			$('#stat_' + slip_id ).text('完了');
			var val = $('#' + my_id).attr('value','2');
		}else if(data == 'OK2'){
			$('#' + my_id).removeClass("btn-success");
			$('#' + my_id).addClass("btn-outline-secondary");
			$('#datename_' + slip_id + '_' + accept_list_id).text('');
			$('#stat_' + slip_id ).text('手続中');
			var val = $('#' + my_id).attr('value','1');
		}
		console.log(data);
	})
	 // 失敗時には、その旨をダイアログ表示
	.fail(function(data) {
		window.alert('エラーが発生しました\nデータは反映されていません');
	});

	});
});

$(function() {
	$('textarea').change(function() {
	var my_id = $(this).attr('id');
	$.ajax('slip_ajax3.php',
	{
	type: 'post',
	data: {
		var_name: $(this).attr('name'),
		var_val: $(this).val(),
		slip_id: $(this).attr('slip_id')
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