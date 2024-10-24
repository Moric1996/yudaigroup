<?php

////////////////////////////////////////////////////////////////////// START class slip
include(dirname(__FILE__).'/aplus_list.inc');
class aplus extends ybase{
////////////////////////////////////////////////////////////////////// END function construct
public function navi_head_pri($no = 0) {
	$active[$no] = "table-active";
$ST_PRI = <<<HTML
<link href="/schoolinkbeta/school/css/tablesorter/theme.default.css" rel="stylesheet">
<link href="/schoolinkbeta/school/css/tablesorter/theme.blue.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.0/js/jquery.tablesorter.min.js"></script>
<div class="container">
<table class="table table-bordered table-sm mx-auto small text-center">
<tbody>
<tr>
<td class="{$active[1]}">
<a href="./new_company.php">新規契約者登録</a>
</td>
<td class="{$active[2]}">
<a href="./company_list.php">契約者一覧</a>
</td>
<td class="{$active[3]}">
<a href="./paper_banktrans_list.php">口座一覧</a>
</td>
<td class="{$active[4]}">
<a href="./paper_banktrans_log_list.php">口座振替実行結果一覧</a>
</td>
<td class="{$active[5]}">
<a href="./claim_make.php">請求データ作成</a>
</td>
<td class="{$active[5]}">
<a href="./claim_list.php">請求一覧</a>
</td>

</tr>
</tbody>
</table>
</div>
<p></p>
HTML;

	return $ST_PRI;
}
////////////////////////////////////////////////////////////////////// END group_const_make
public function company_list_make($status=""){				//HTMLデータ作成
	$conn = $this->connect(4);
	if(preg_match("/^[0-9]+$/",$status)){
		$add_sql = " where status = '$status'";
	}else{
		$add_sql = "";
	}
	$sql = "select company_id,company_name from company_list{$add_sql} order by company_id";

	$result = $this->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$this->company_name_list){
		$this->company_name_list = array();
	}
	for($i=0;$i<$num;$i++){
		list($q_company_id,$q_company_name) = pg_fetch_array($result,$i);
		$this->company_name_list[$q_company_id] = $q_company_name;
	}
	return $this->company_name_list;
}
////////////////////////////////////////////////////////////////////// END group_const_make
public function no_release_check($my_id="0"){
	$release_id_arr = array('10005','201201512','200416643');
	if(in_array("$my_id",$release_id_arr, true)){
		$ck_flag = "";
	}else{
		$ck_flag = "1";
	}
	return $ck_flag;
}
////////////////////////////////////////////////////////////////////// END no_release_check
public function slack_send() {

	$url = "https://hooks.slack.com/services/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";

//	$message = array(
//		"channel" => "{$this->mess_channel}",
//		"text" => "{$this->mess_text}"
//	);
/*
	$ch = curl_init();

	$options = array(
		CURLOPT_URL => $url,
// 返り値を文字列で返す
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
  // POST
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => http_build_query(array(
		'payload' => json_encode($message)
		))
	);

	curl_setopt_array($ch, $options);
	curl_exec($ch);
	curl_close($ch);
*/
	$cmd = "curl -X POST --data-urlencode \"payload={\\\"channel\\\": \\\"{$this->mess_channel}\\\",\\\"text\\\": \\\"{$this->mess_text}\\\"}\" $url";
	$res = exec($cmd);



	return $res;
}
////////////////////////////////////////////////////////////////////// END group_const_make


}
////////////////////////////////////////////////////////////////////// END class rank
?>