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
include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/slip.inc');

$ybase = new ybase();
$slip = new slip();
$ybase->session_get();

$ybase->my_company_id = 5;

$ybase->make_yournet_employee_list("1");
$slip->supplier_make();

$conn = $ybase->connect(3);


/////////////////////////////////////////
$skip_suppliders = array(3951,3859,3984,4000,4017);

////////////////////////////////


if(!is_uploaded_file($_FILES["attachfile"]["tmp_name"])) {
	$ybase->error("ファイルを選択してください。ERROR_CODE:23012");
}

if($fp = fopen($_FILES["attachfile"]["tmp_name"],"r")){
	$contents = fread($fp,$_FILES["attachfile"]["size"]);
}
fclose($fp);

$order = array("\r\n","\r");
$replace = "\n";
$contents = trim($contents);
$contents = str_replace($order, $replace, $contents);

$str = explode("\n",$contents);
$allcnt = 0;
$incnt = 0;
$upcnt = 0;
$fail = "";
foreach($str as $key => $val){
	$dat = explode("\t",$val);
	$dat[0] = mb_convert_encoding($dat[0], "UTF-8", "SJIS-win");
	$dat[1] = mb_convert_encoding($dat[1], "UTF-8", "SJIS-win");
	$dat[2] = mb_convert_encoding($dat[2], "UTF-8", "SJIS-win");
	$dat[0] = trim(str_replace('"','',$dat[0]));
	$dat[1] = trim(str_replace('"','',$dat[1]));
	$dat[2] = trim(str_replace('"','',$dat[2]));
//	$dat[2] = trim(str_replace('㈱','(株)',$dat[2]));
//	$dat[2] = trim(str_replace('㈲','(有)',$dat[2]));
	if(!$dat[2]){continue;}
	if(!$dat[0]){continue;}
	if(!preg_match("/^[0-9]+$/",$dat[0])){
		continue;
	}
	$allcnt++;
	$sql = "select supplier_id,name from slip_supplier where company_id = $ybase->my_company_id and code = '{$dat[0]}' and status = '1'";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$dat[1] = pg_escape_string($dat[1]);
		$dat[2] = pg_escape_string($dat[2]);
		$sql = "insert into slip_supplier (supplier_id,company_id,code,kana,name,count,add_date,status) values (nextval('supplier_id_seq'),{$ybase->my_company_id},{$dat[0]},'{$dat[1]}','{$dat[2]}',0,'now','1')";
		$result = $ybase->sql($conn,$sql);
		if(!$result){
			$fail .= $dat[2]." ";
		}
		$incnt++;

	}else{
		list($q_supplier_id,$q_name) = pg_fetch_array($result,0);
		if($q_name != $dat[2]){
			if(in_array($q_supplier_id,$skip_suppliders)){
				continue;
			}
			$sql = "update slip_supplier set name='{$dat[2]}',kana='{$dat[1]}' where supplier_id = $q_supplier_id";
			$result = $ybase->sql($conn,$sql);
			$upcnt++;
		}

	}


}



/////////////////////////////////
////////////////////////////////
$ybase->title = "新規伝票作成完了";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("新規伝票管理");

$ybase->ST_PRI .= <<<HTML
<div class="container">
<table class="table table-bordered table-sm mx-auto small text-center">
<tbody>
<tr>
<td>
<a href="./newslip_fm.php">新規伝票作成</a>
</td>
<td>
<a href="./slip_list.php">伝票管理</a>
</td>
<td class="table-active">
<a href="./insupplier_fm.php">取引先取込</a>
</td>
</tr>
</tbody>
</table>

<p></p>
<div class="card border border-dark mx-auto">
<div class="card-header border-dark alert-info text-center">取引先取込完了</div>
<div class="card-body">

<p></p>
<p></p>
{$allcnt}件中{$incnt}件取込完了しました
<p></p>
({$upcnt}件更新)
<p></p>

HTML;

if($fail){
$ybase->ST_PRI .= <<<HTML
データ取込失敗<br>
$fail
<br>
HTML;
}

$ybase->ST_PRI .= <<<HTML
<p></p>
<div style="text-align:center;"><a class="btn btn-secondary" href="./insupplier_fm.php" role="button">戻る</a></div>
</div>
</div>
<p></p>


</div>
<p></p>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>