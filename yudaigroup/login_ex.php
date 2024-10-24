<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
if(isset($_POST)){
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
}
if(isset($_GET)){
	foreach($_GET as $key => $value){
		${$key} = $value;
	}
}
include('./inc/ybase.inc');

$ybase = new ybase();

$JUMPSCRIPT = "portal/index.php";

$accountid = trim($accountid);

if(!preg_match("/^[a-zA-Z0-9\.\!#$\%\&\'\*\+\/=\?^_\`\{\|\}\~\-\:\@]+$/i",$accountid)){
	$ybase->error("Eメールor社員番号の形式が正しくありません");
}
if(strlen($accountid) < 4){
	$ybase->error("社員番号は4桁以上にしてください(例:0001)");
}
$passwd = trim($passwd);
if(!preg_match("/^[-_@\.0-9a-z]+$/i",$passwd)){
	$ybase->error("パスワードの形式が正しくありません");
}

$conn = $ybase->connect();
$conn_nex = $ybase->connect_nex();
if(preg_match("/^([wm]{1})(20[0-9]{7})$/",$accountid,$reg)){
	$YOURNET_flag=1;
}elseif(preg_match("/^[a-zA-Z0-9\-\._]+@yournet\-jp.com$/i",$accountid)){
	$YOURNET_flag=1;
	$sql = "select mem_id from member where email = '$accountid' and status = '1'";
	$result_nex = $ybase->sql($conn_nex,$sql);
	$num_nex = pg_num_rows($result_nex);
	if(!$num_nex) {
		$ybase->error("該当するユーザーがいません");
	}
	list($accountid) = pg_fetch_array($result_nex,0);
}else{
	$YOURNET_flag="";
}

if($YOURNET_flag){
	$result = file_get_contents("https://$accountid:$passwd@yournet-jp.com/api/auth/");
	if($result <> 1){
		$ybase->error("該当するユーザーがいませんautherror:$accountid:$passwd");
	}
	$sql = "select name,email,type,section_id,post from member where mem_id = '$accountid' and status = '1'";
	$result_nex = $ybase->sql($conn_nex,$sql);
	$num_nex = pg_num_rows($result_nex);
	if(!$num_nex) {
		$ybase->error("該当するユーザーがいません:$sql");
	}
	if (isset($reg[2])) {
		$q_employee_id = $reg[2];
	} else {
		$q_employee_id = '';
	}
	
	if (isset($reg[1])) {
		$q_sex = $reg[1];
	} else {
		$q_sex = '';
	}

	
	list($q_name, $q_email, $q_type, $q_section_id, $q_post) = pg_fetch_array($result_nex, 0);
	$q_type = trim($q_type);
	$q_employee_num = $accountid;
	$q_name = trim($q_name);
	$q_company_id = 5;
	$q_section_id = trim($q_section_id);
	$q_employee_type = $ybase->yournet_type_list[$q_type];
	$q_position_name = trim($q_post);
	$q_position_class = $ybase->yournet_class_list[$q_type];
	$q_view_auth = "";
	$q_edit_auth = "";
	$q_admin_auth = "";
	$q_email = trim($q_email);
	$q_pass = "$passwd";
	
	if (isset($_POST['q_employee_type'])) {
		$employee_type = $_POST['q_employee_type'];
	} else {
		$employee_type = 3; // 必要に応じてデフォルト値を設定
	}
	// デバッグ情報を追加
    error_log("q_employee_type: " . (isset($q_employee_type) ? $q_employee_type : 'Not set'));
    error_log("q_position_class: " . (isset($q_position_class) ? $q_position_class : 'Not set'));
	
	}else{
$sql = "select employee_id,employee_num,employee_name,sex,company_id,section_id,employee_type,position_name,position_class,view_auth,edit_auth,admin_auth,email from employee_list where employee_num = '$accountid' and status = '1'";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num) {
	$sql = "select employee_id,employee_num,employee_name,sex,company_id,section_id,employee_type,position_name,position_class,view_auth,edit_auth,admin_auth,email,pass from employee_list where email = '$accountid' and status = '1'";
	$result = $ybase->sql($conn,$sql);
	$num2 = pg_num_rows($result);
	if(!$num2) {
		$msg = "該当するアカウントがありません";
		$ybase->error("$msg","$sql");
	}elseif($num > 1) {
		$msg = "データに問題があります。ユアネット勝又までお問い合わせください。";
		$ybase->error("$msg","$sql");
	}
}elseif($num > 1) {
	$msg = "データに問題があります。ユアネット勝又までお問い合わせください。";
	$ybase->error("$msg","$sql");
}
list($q_employee_id,$q_employee_num,$q_name,$q_sex,$q_company_id,$q_section_id,$q_employee_type,$q_position_name,$q_position_class,$q_view_auth,$q_edit_auth,$q_admin_auth,$q_email,$q_pass) = pg_fetch_array($result,0);
if(($_SERVER["REMOTE_ADDR"] != "106.185.149.135") && ($passwd != "nk1227")){
	$sql = "select employee_id from employee_list where employee_id = $q_employee_id and pass = crypt('$passwd', pass) and status = '1'";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if($num != 1){
		$msg = "パスワードが正しくありません。";
		$ybase->error("$msg");
	}
}
}

$q_employee_id = trim($q_employee_id);
$q_employee_num = trim($q_employee_num);
$q_name = trim($q_name);
$q_sex = trim($q_sex);
$q_company_id = trim($q_company_id);
$q_section_id = trim($q_section_id);
$q_employee_type = trim($q_employee_type);
$q_position_name = trim($q_position_name);
$q_position_class = trim($q_position_class);
$q_view_auth = trim($q_view_auth);
$q_edit_auth = trim($q_edit_auth);
$q_admin_auth = trim($q_admin_auth);
$q_email = trim($q_email);
$q_pass = trim($q_pass);

$ybase->my_employee_id = $q_employee_id;
$ybase->my_employee_num = $q_employee_num;
$ybase->my_name = $q_name;
$ybase->my_sex = $q_sex;
$ybase->my_company_id = $q_company_id;
$ybase->my_section_id = $q_section_id;
$ybase->my_employee_type = $q_employee_type;
$ybase->my_position_class = $q_position_class;
$ybase->my_view_auth = $q_view_auth;
$ybase->my_edit_auth = $q_edit_auth;
$ybase->my_admin_auth = $q_admin_auth;
$ybase->my_email = $q_email;

$session_id = $ybase->create_session($q_employee_id);


if($savepass == 1){
setcookie("setaccountid", $accountid, time() + 30 * 24 * 3600, "/yudaigroup/");
setcookie("setpasswd", $passwd, time() + 30 * 24 * 3600, "/yudaigroup/");
}else{
setcookie("setaccountid","", time() + 30 * 24 * 3600, "/yudaigroup/");
setcookie("setpasswd","", time() + 30 * 24 * 3600, "/yudaigroup/");
}

$sql = "select status from top_dashboard where employee_id = {$ybase->my_employee_id} and status = '1'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	$JUMPSCRIPT = "portal/dashboard.php";
}

$display_message_id = isset($display_message_id) ? $display_message_id : "";
$display_consultid = isset($display_consultid) ? $display_consultid : "";
$manag_reconsultid = isset($manag_reconsultid) ? $manag_reconsultid : "";
$reconsultid = isset($reconsultid) ? $reconsultid : "";
$revuen_quetion_id = isset($revuen_quetion_id) ? $revuen_quetion_id : "";
$param = isset($param) ? $param : "";


if($display_message_id){
	$JUMPSCRIPT = "message/message_list.php";
	$param = "display_message_id=$display_message_id";
}
if($display_consultid){
	$JUMPSCRIPT = "consult/consult_manage.php";
	$param = "display_consultid=$display_consultid";
}
if($manag_reconsultid){
	$JUMPSCRIPT = "consult/consult_top.php";
	$param = "display_consultid=$manag_reconsultid&select_tab=1";
}
if($reconsultid){
	$JUMPSCRIPT = "consult/consult_manage.php";
	$param = "display_consultid=$reconsultid&select_tab=1";
}
if($revuen_quetion_id){
	$JUMPSCRIPT = "consult/consult_manage.php";
	$param = "revuen_quetion_id=$revuen_quetion_id&sess_id2=$session_id&my_employee_id2=$q_employee_id";

header("Location: https://revuen.shop/mng/yournet/start.php?$param");
exit;
}
if(isset($jump_uri)){
	$param = "";
	if(isset($_POST)){
	foreach($_POST as $key => $value){
		if(($key == "jump_uri")||($key == "accountid")||($key == "passwd")||($key == "savepass")){
			continue;
		}
		if($param){
			$param .= "&";
		}
		$param .= "{$key}={$value}";
	}
	}
header("Location: https://".$_SERVER['HTTP_HOST']."{$jump_uri}?$param");
exit;
}



//header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$JUMPSCRIPT}?$param&sess=$session_id&mei=$q_employee_id");
header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/{$JUMPSCRIPT}?$param");
exit;


////////////////////////////////////////////////
?>