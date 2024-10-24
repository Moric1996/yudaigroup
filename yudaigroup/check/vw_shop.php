<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');
include('./inc/check.inc');
include('./inc/check_list.inc');

$ybase = new ybase();
$check = new check();
$ybase->session_get();

$ybase->make_employee_list();
$sec_employee_list = $ybase->employee_name_list;
$ybase->make_now_section_list();

$category_list = $check->category_make();
$item_list = $check->item_make();

$MAXCOL = 12;
/////////////////////////////////////////
if(!$ybase->my_position_class || ($ybase->my_position_class > 40)){
	$check->protect = 1;
}

$conn = $ybase->connect();

function mb_str_split( $string ) {
	return preg_split('/(?<!^)(?!$)/u', $string );
}
//////////////////////////////////////////条件
$param = "t_shop_id=$t_shop_id";
$addsql = "section_id = $t_shop_id";
//////////////////////////////////////////
if($t_shop_id){
	$sql = "select ckset_id,action_date from ck_check_action where section_id = '$t_shop_id' and status = '1' order by action_date desc limit 1";
	$result = $ybase->sql($conn,$sql);
	$num = pg_num_rows($result);
	if(!$num){
		$ybase->error("該当するデータがありません");
	}
	list($q_ckset_id,$q_action_date) = pg_fetch_array($result,0);
	$subject_list_arr = $check->check_set_make($t_shop_id,$q_ckset_id);
	$subject_list_cate_arr = $check->check_set_item_make($subject_list_arr);

/////////////////////データ範囲取得
$sql = "select ckaction_id,ckset_id,action_date,employee_id from ck_check_action where section_id = '$t_shop_id' and status = '1' order by action_date desc limit $MAXCOL";
$result = $ybase->sql($conn,$sql);
$colnum = pg_num_rows($result);
$kk = 0;
$t_ckaction_id_list = "";
for($i=$colnum -1;$i>=0;$i--){
	list($q_ckaction_id,$q_ckset_id,$q_action_date,$q_employee_id) = pg_fetch_array($result,$i);
	$t_ckaction_id_arr[$kk] = $q_ckaction_id;
	$t_ckaction_action_date_arr[$q_ckaction_id] = $q_action_date;
	$t_ckaction_employee_arr[$q_ckaction_id] = $q_employee_id;
	if($t_ckaction_id_list){
		$t_ckaction_id_list .= ",";
	}
	$t_ckaction_id_list .= $q_ckaction_id;
	$kk++;
}

//////////////////
/////////////////////データ取得
$sql = "select ckaction_list_id,ckaction_id,item_id,action,com from ck_check_action_list where ckaction_id in ($t_ckaction_id_list) and section_id = '$t_shop_id' and status = '1' order by ckaction_id,item_id";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if($num){
	for($i=0;$i<$num;$i++){
	list($q_ckaction_list_id,$q_ckaction_id,$q_item_id,$q_action,$q_com) = pg_fetch_array($result,$i);
	$t_check_action_arr[$q_ckaction_id][$q_item_id] = $q_action;
//	print "$q_ckaction_id:$q_item_id:$q_action<br>";
	$t_check_action_com_arr[$q_ckaction_id][$q_item_id] = $q_com;
	}
}
/////////
}else{
	foreach($category_list as $key => $val){
		$subject_list_cate_arr[$key]=array();
	}
	$t_ckaction_id_arr=array();
}

//////////////////


$ybase->title = "店舗チェック-店舗別入力推移";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("店舗別入力推移");
$ybase->ST_PRI .= $check->check_menu("5","$t_shop_id");

$ybase->ST_PRI .= <<<HTML
<script type="text/javascript">
$(function(){
	$('select').change(function(){
		$("#Form1").submit();
	});
});

</script>
<script src="./js/check_update.js?102"></script>
<input type="hidden" name="target_ckaction_id" value="$target_ckaction_id" id="target_ckaction_id">
<input type="hidden" name="target_shop_id" value="$t_shop_id" id="target_shop_id">
<input type="hidden" name="scriptno" value="1" id="scriptno">

<div class="container">
<h5 style="text-align:center;">店舗別入力推移</h5>

<p></p>
<form action="./vw_shop.php" method="post" id="Form1">

<div class="row">
<div class="col-sm-8 offset-sm-2" style="text-align:center;">
HTML;
$auth_edit = 1;

$ybase->ST_PRI .= <<<HTML
【対象店舗】<select name="t_shop_id">
<option value="">選択してください</option>
HTML;
foreach($ybase->section_list as $key => $val){
if($key < 100){continue;}
if($key > 1000){continue;}
if(preg_match("/^3[0-9]{2}$/",$key)){continue;}
if($key == $t_shop_id){
	$selected = " selected";
}else{
	$selected = "";
}

$ybase->ST_PRI .= <<<HTML
<option value="$key"{$selected}>$val</option>
HTML;

}
$ybase->ST_PRI .= <<<HTML
</select>
HTML;

$ybase->ST_PRI .= <<<HTML
</div>
</div>
</form>

<p></p>


<p></p>
<table border="0" width="100%">
<tr><td>
</td>
<td align="right">
<nobr>
<!---<a href="check_in.php?$param&target_ckaction_id=$bf_ckaction_id" class="btn btn-sm btn-outline-secondary{$bf_disable}">前回</a>---->
<!---<a href="check_in.php?$param&target_ckaction_id=$nx_ckaction_id" class="btn btn-sm btn-outline-secondary{$nx_disable}">次回</a>---->
</nobr>

</td></tr>
</table>
<div class="table-responsive">
<table class="table table-bordered table-sm" style="font-size:75%;">
<tbody>
HTML;

$ac_cnt=array();
$ac_sum=array();

$ybase->ST_PRI .= <<<HTML
<tr align="center" bgcolor="#aaaaaa">
<th>
</th>
<th>
項目
</th>
HTML;

foreach($t_ckaction_id_arr as $key3 => $val3){
$ybase->ST_PRI .= <<<HTML
<th>
{$t_ckaction_action_date_arr[$val3]}
</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
HTML;


foreach($category_list as $key => $val){
$ybase->ST_PRI .= <<<HTML
<tr align="center" bgcolor="#cccccc">
<th colspan="2">
$val
</th>
HTML;

foreach($t_ckaction_id_arr as $key3 => $val3){
$ybase->ST_PRI .= <<<HTML
<th>
</th>
HTML;
}
$ybase->ST_PRI .= <<<HTML
</tr>
HTML;
$kk=0;
	if(!isset($subject_list_cate_arr[$key])){
		$subject_list_cate_arr[$key] = array();
	}

	foreach($subject_list_cate_arr[$key] as $key2 => $val2){

$kk++;
if($kk%2 == 0){
	$hbgcolor = "#fafafa";
}else{
	$hbgcolor = "#ffffff";
}
$analysis_rows = intval(strlen($t_action_com[$key2])/30) + 1;
$analysis_cr = substr_count($t_action_com[$key2],"\n") + 1;
if($analysis_rows < $analysis_cr){
	$analysis_rows = $analysis_cr;
}
$allot_cnt[$key] += $check->allot_by_subject[$key2];
$t_cnt[$key] += $t_action_num[$key2];
$forallotno++;
$ybase->ST_PRI .= <<<HTML
<tr bgcolor="$hbgcolor">
<td align="center">
$kk
</td>
<td>
$val2
</td>
HTML;
$predate = "";
		foreach($t_ckaction_id_arr as $key3 => $val3){
	$t_ckaction_employee_arr[$q_ckaction_id] = $q_employee_id;
			$d_action = $t_check_action_arr[$val3][$key2];
			$d_comn = $t_check_action_com_arr[$val3][$key2];
			if(!$predate){
				$add_color = "#000000";
			}elseif($d_action > $predate){
				$add_color = "#0000ff";
			}elseif($d_action < $predate){
				$add_color = "#ff0000";
			}else{
				$add_color = "#000000";
			}
			$predate = $d_action;
			$ac_cnt[$val3] += $d_action;
			$ac_sum[$val3] += $d_action;
$ybase->ST_PRI .= <<<HTML
<td align="center">
<span style="color:{$add_color};">
$d_action
</span>
</td>
HTML;
		}
$ybase->ST_PRI .= <<<HTML
</tr>
HTML;

	}

$ybase->ST_PRI .= <<<HTML
<tr bgcolor="#eeeeee">
<td colspan="2" align="center">
小計
</td>
HTML;
$predate = "";
		foreach($t_ckaction_id_arr as $key3 => $val3){
			$kei = $ac_cnt[$val3];
			if(!$predate){
				$add_color = "#000000";
			}elseif($kei > $predate){
				$add_color = "#0000ff";
			}elseif($kei < $predate){
				$add_color = "#ff0000";
			}else{
				$add_color = "#000000";
			}
			$predate = $kei;

$ybase->ST_PRI .= <<<HTML
<td align="center">
<span style="color:{$add_color};">
$kei
</span>
</td>
HTML;
		}
$ybase->ST_PRI .= <<<HTML

</tr>
HTML;

$ac_cnt = array();
}

$ybase->ST_PRI .= <<<HTML
<tr bgcolor="#dddddd">
<td colspan="2" align="center">
合計
</td>
HTML;
$predate = "";
		foreach($t_ckaction_id_arr as $key3 => $val3){
			$kei = $ac_sum[$val3];
			if(!$predate){
				$add_color = "#000000";
			}elseif($kei > $predate){
				$add_color = "#0000ff";
			}elseif($kei < $predate){
				$add_color = "#ff0000";
			}else{
				$add_color = "#000000";
			}
			$predate = $kei;
$ybase->ST_PRI .= <<<HTML
<td align="center">
<span style="color:{$add_color};">
$kei
</span>
</td>
HTML;
}

$ybase->ST_PRI .= <<<HTML
</tr>
<tr bgcolor="#efefef">
<td colspan="2" align="center">
入力者
</td>
HTML;
foreach($t_ckaction_id_arr as $key3 => $val3){
	$tar_employee_id = $t_ckaction_employee_arr[$val3];
	$tar_employee_name = $ybase->employee_name_list[$tar_employee_id];
$ybase->ST_PRI .= <<<HTML
<td align="center">
$tar_employee_name
</td>
HTML;
}

$ybase->ST_PRI .= <<<HTML
</tr>

 </tbody>
</table>
</div>
<div class="text-center">
HTML;

$ybase->ST_PRI .= <<<HTML
</div>
</div>
</div>
<p></p>

HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>