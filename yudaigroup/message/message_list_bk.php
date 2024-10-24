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
$cate = 1;
/////////////////////////////////////////

$conn = $ybase->connect();

$sql = "select email from employee_list where employee_id = $ybase->my_employee_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$q_email = pg_fetch_result($result,0,0);
	$q_email = trim($q_email);
}
if($q_email){
$sql = "select config from mail_send where employee_id = $ybase->my_employee_id and cate = $cate";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$q_config = pg_fetch_result($result,0,0);
}
	$disabled = "";
	$sendcheck_mess = "メールでの通知";
}else{
	$disabled = " disabled";
	$sendcheck_mess = "<a href=\"../user/pass_cg.php\">メールでの通知にはメールアドレスの登録が必要です</a>";
}
if($q_config == "1"){
	$send_checked = " checked";
}else{
	$send_checked = "";
}

$sql = "select a.message_id,a.employee_id,a.title,a.content,to_char(a.add_date,'YYYY/MM/DD HH24:MI'),b.status from message as a LEFT OUTER JOIN message_log as b ON a.message_id = b.message_id and b.employee_id = $ybase->my_employee_id and b.status <> '0' where a.status = '1'".$add_sql." order by a.add_date desc";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
$allpage = ceil($num / $kensu);
if(!$page){
	$page = 1;
}
$st = ($page - 1) * $kensu;
$end = $st + $kensu;
if($end > $num){
	$end = $num;
}

if(!$num){
	$q_content0 = "<br><div style=\"text-align:center;text-size:110%;\">お知らせはありません</div><br>";
}else{
	if(preg_match("/^[0-9]+$/",$display_message_id)){
		$sqld = "select a.message_id,a.employee_id,a.title,a.content,to_char(a.add_date,'YYYY/MM/DD HH24:MI'),b.status from message as a LEFT OUTER JOIN message_log as b ON a.message_id = b.message_id and b.employee_id = $ybase->my_employee_id and b.status <> '0' where a.status = '1' and a.message_id = $display_message_id".$add_sql." order by a.add_date desc";
		$resultd = $ybase->sql($conn,$sqld);
		$numd = pg_num_rows($resultd);
		if($numd){
		list($q_message_id0,$q_regi_employee_id0,$q_title0,$q_content0,$q_add_date0,$q_status0) = pg_fetch_array($resultd,0);
		}
	}else{
		list($q_message_id0,$q_regi_employee_id0,$q_title0,$q_content0,$q_add_date0,$q_status0) = pg_fetch_array($result,$st);
	}
	$q_content0 = trim($q_content0);
	$q_content0 = ereg_replace("([\r|\n|\r\n]+)","<br>",$q_content0);
//	$q_content0 = htmlspecialchars($q_content0);
	$q_title0 = htmlspecialchars($q_title0);

	if($q_status0 != "2"){
		$sqlc = "select message_id from message_log where message_id = $q_message_id0 and employee_id = $ybase->my_employee_id";
		$resultc = $ybase->sql($conn,$sqlc);
		$numc = pg_num_rows($resultc);
		if($numc){
			$sql2 = "update message_log set status = '2' where message_id = $q_message_id0 and employee_id = $ybase->my_employee_id";
		}else{
			$sql2 = "insert into message_log values ($q_message_id0,$ybase->my_employee_id,'now','2')";

		}
		$result2 = $ybase->sql($conn,$sql2);
		$q_status0 = "2";
	}
}

/////////////////////////投稿完了後の表示
if($new_mess_sent == 1){
	$new_mess_dis = "<div style=\"text-align:center;text-size:110%;color:red;\">投稿完了!</div>";
}else{
	$new_mess_dis = "";
}
////////////////////////

$ybase->title = "お知らせ";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("お知らせ");


$ybase->ST_PRI .= <<<HTML
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
<script type="text/javascript">
$(function($) {
    $('#new_button').click(function () {
        $('#new_form').toggle();
    });
});
</script>

<div class="container">
<p></p>
<button type="button" class="btn btn-secondary btn-sm" id="new_button">新規投稿</button>

<p></p>

<div id="new_form" style="display: none">
<div class="card-body bg-light lead">
<div style="text-align:center;font-size:80%;margin:0 auto;">新規投稿</div>
<form action="message_ex.php" method="post" style="font-size:80%;">
<div class="form-group">
<label>【タイトル】</label>
<input type="text" name="new_title" class="form-control" id="Input1" placeholder="最大50字まで" maxlength="50" required>
</div>
<div class="form-group">
<label>【投稿内容】</label>
<textarea name="new_mess" class="form-control" rows="5" required>
</textarea>
</div>
<button type="submit" class="btn btn-outline-primary btn-sm"> 　送　信　 </button>
<button type="reset" class="btn btn-outline-primary btn-sm">クリア</button>
</form>
</div>
<hr>
</div>
<p></p>
$new_mess_dis
<p></p>
<div class="card border border-dark" style="padding: 2px 5px;">

<div class="card-body bg-light lead" style="padding: 2px 5px;">
<div style="text-align:right;font-size:50%;margin:0 auto;">【投稿者】{$ybase->employee_name_list[$q_regi_employee_id0]}
【日付】{$q_add_date0}</div>
<div style="text-align:center;font-size:75%;margin:0 auto;">{$q_title0}</div>
<hr style="margin: 0px;padding: 2px;">
<div style="font-size:85%;">{$q_content0}</div>

<!-----------------
<hr>
<div class="row">
<div class="col">
<a href="" class="btn btn-sm btn-outline-secondary float-left">前＜＜＜</a>
</div>
<div class="col">
<a href="" class="btn btn-sm btn-outline-secondary float-right">＞＞＞次</a>
</div>
</div>
-------------------->
</div>
</div>
<p></p>

<div class="checkbox" style="text-align:right;">
<span style="font-size:80%;">$sendcheck_mess</span>
<input type="checkbox" data-toggle="toggle" data-onstyle="info" data-offstyle="light" data-size="small" id="msendtoggle"{$send_checked}{$disabled}>
</div>
<script type="text/javascript">
$(function($) {
	$('#msendtoggle').change(function() {
		var flag = $(this).prop('checked');
		$.ajax({
			type: "POST",
			url: "../ajax/mail_send_cg.php",
			data: {
				"my_employee_id":"{$ybase->my_employee_id}",
				"config":flag,
				"cate":$cate
			}
	        });
	});
});
</script>


<p class="h6 text-center">お知らせ一覧</p>

<table class="table table-bordered table-hover table-striped table-sm" style="font-size:85%;">
  <thead>
    <tr align="center" class="table-secondary">
      <td scope="col">NO.</td>
      <td scope="col">タイトル</thd>
      <td scope="col">投稿者</td>
      <td scope="col">日付</td>
      <td scope="col">未読・既読</td>
      <td scope="col">削除</td>
    </tr>
  </thead>
  <tbody>
HTML;

for($i=$st;$i<$end;$i++){
	list($q_message_id,$q_regi_employee_id,$q_title,$q_content,$q_add_date,$q_status) = pg_fetch_array($result,$i);
	$no=$i+1;
	$q_status = trim($q_status);
	if($q_status == ""){
		$q_status = 1;
	}
	if($q_message_id == $q_message_id0){
		$q_status = 2;
		$style="background-color:#ffeecc;font-weight: bold;";
	}else{
		$style="";
	}
	if($q_regi_employee_id == $ybase->my_employee_id){
		$delclass = "";
	}else{
		$delclass = " disabled";
	}
	$q_title = htmlspecialchars($q_title);
	$linkto = "message_list.php?display_message_id=$q_message_id&page=$page";
$ybase->ST_PRI .= <<<HTML

<tr align="center" style="$style" id="trno{$q_message_id}">
<td class="position-relative">
<a href="$linkto" class="stretched-link text-body">
$no
</a>
</td>
<td class="position-relative">
<a href="$linkto" class="stretched-link text-body">
$q_title
</a>
</td>
<td class="position-relative">
<a href="$linkto" class="stretched-link text-body">
{$ybase->employee_name_list[$q_regi_employee_id]}
</a>
</td>
<td class="position-relative">
<a href="$linkto" class="stretched-link text-body">
$q_add_date
</a>
</td>
<td class="position-relative">
<a href="$linkto" class="stretched-link text-body">
{$ybase->message_status_list[$q_status]}
</a>
</td>
<td>
<a href="#" class="btn btn-sm btn-secondary{$delclass}" role="button" id="delete{$q_message_id}">
削除
</a>
</td>
</tr>

<script>
$(function($) {
$('#delete{$q_message_id}').click(function(){
    if(!confirm('[{$q_title}]を本当に削除しますか？')){
        /* キャンセルの時の処理 */
        return false;
    }else{
        /*　OKの時の処理 */
        location.href = './message_del.php?del_message_id=$q_message_id&page=$page';
    }
});
});
</script>
HTML;


}



$bf=$page-1;
$nx=$page+1;
if($bf < 1){
	$addbfclass=" disabled";
}else{
	$addbfclass="";
}
if($nx > $allpage){
	$addnxclass=" disabled";
}else{
	$addnxclass="";
}
$ybase->ST_PRI .= <<<HTML

</tbody>
</table>
<div class="row">
<div class="col float-right">
<span class="float-right">
{$page}／{$allpage}
<a href="message_list.php?page=$bf" class="btn btn-sm btn-outline-secondary{$addbfclass}">＜</a>
<a href="message_list.php?page=$nx" class="btn btn-sm btn-outline-secondary{$addnxclass}">＞</a>
</span>
</div>
</div>

</div>
<p></p>
HTML;
$k = 10 - $num;

if($k > 0){
for($i=0;$i<$k;$i++){
$ybase->ST_PRI .= "<br><br>";

}

}
$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>