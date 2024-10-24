<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();
$ybase->make_employee_list();

$kensu=50;
$kind = 1;
$send_emp_id = $ybase->chat_receive_employee_id;

if($ybase->chat_receive_employee_id == $ybase->my_employee_id){
	$send_emp_id = 10005;
}
/////////////////////////////////////////

$conn = $ybase->connect();

$sql = "update chatbox set r_flag = '2' where send_id = $send_emp_id and receive_id ={$ybase->my_employee_id} and status = '1' and kind = $kind";
$result = $ybase->sql($conn,$sql);

$sql = "select chat_id,send_id,receive_id,s_flag,r_flag,mess,to_char(add_date,'YYYY/MM/DD HH24:MI:SS') from chatbox where send_id in ($send_emp_id,{$ybase->my_employee_id}) and receive_id in ($send_emp_id,{$ybase->my_employee_id}) and status = '1' and kind = $kind order by add_date";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
////////////////////////

$ybase->title = "相談・提案";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("$ybase->title");


$ybase->ST_PRI .= <<<HTML
<link rel="stylesheet" href="./popup.css?3" type="text/css">
<script>
function esc(s) {
	return s.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;');
}

$(function(){
	$('textarea').on('change keyup keydown paste cut', function(){
		var change_val = $(this).attr('rows');
		if ($(this).outerHeight() > this.scrollHeight){
			$(this).height(change_val);
		}
		while ($(this).outerHeight() < this.scrollHeight){
			$(this).height($(this).height() + 1);
		}
	});
	$('#txtbutton').click(function(){
		var inputText = $('#txt').val();
		if(!inputText){
			return;
		}
		var now = new Date();
		var Hour = now.getHours();
		var Min = now.getMinutes();
		var messageMine = $("<div class='chatBox'><div class='usr chatBalloon'>" + esc(inputText) + "</div><span class='usrdate'>未読<br>" + Hour + ":" + Min + "</span></div>");
		$('#chat').append(messageMine);
		$.ajax('ajaxchatin.php',
		{
		type: 'post',
		data: {
			var_val: $('#txt').val(),
			send_emp_id: $('#send_emp_id').val(),
			kind: $('#kind').val(),
			last_chat_id: $('#last_chat_id').val()
		},
		dataType: 'text'
		});

		$('#txt').val('').focus();
	});
});
</script>

<div class="container">
<div class="panel panel-default">
<div class="panel-heading">
<h2 class="panel-title">{$ybase->title}</h2>
</div>
<div class="panel-body">
<div id='chat'>
HTML;
$order   = array("\r\n", "\n", "\r");
$replace = '<br>';
$checkdate = 0;
for($i=0;$i<$num;$i++){
	list($q_chat_id,$q_send_id,$q_receive_id,$q_s_flag,$q_r_flag,$q_mess,$q_add_date) = pg_fetch_array($result,$i);
	if($q_r_flag == '1'){
		$d_flag= "未読";
	}else{
		$d_flag= "既読";
	}
	$tardate = substr($q_add_date,0,4).substr($q_add_date,5,2).substr($q_add_date,8,2);
	$d_date = substr($q_add_date,11,2).":".substr($q_add_date,14,2);
	if($checkdate != $tardate){
		$mm = (int)substr($q_add_date,5,2);
		$dd = (int)substr($q_add_date,8,2);
		$checkdate = $tardate;
		$date_change = "<div class=\"dateline\">{$mm}月{$dd}日</div>";
	}else{
		$date_change = "";
	}
	$q_mess = htmlspecialchars(str_replace($order, $replace, $q_mess));
	if($q_send_id == $ybase->my_employee_id){
		$boxclass = "usr";
		$dateclass = "usrdate";
		$botname = "";
	}else{
		$boxclass = "bot";
		$dateclass = "botdate";
		$d_flag= "";
		$botname = "<div class=\"botname\">".$ybase->employee_name_list[$q_send_id]."</div>";
	}
$ybase->ST_PRI .= <<<HTML
$date_change
$botname
<div class="chatBox"><div class="$boxclass chatBalloon">$q_mess</div><span class="{$dateclass}">$d_flag<br>$d_date</span></div>
HTML;

}

$ybase->ST_PRI .= <<<HTML
</div>
</div>
</div>
<div class='inputText'>
	<input type="hidden" name="send_emp_id" id="send_emp_id" value="$send_emp_id">
	<input type="hidden" name="kind" id="kind" value="$kind">
	<input type="hidden" name="last_chat_id" id="last_chat_id" value="$q_chat_id">
        <textarea rows="1" id="txt" class="form-control" placeholder="Shift+Enterで送信"></textarea>
        <button type="button" id="txtbutton" class="btn btn-info chat-btn">送信</button>
</div>
</div>
<br><br>
<p></p>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>