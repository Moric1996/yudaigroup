<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

//$kind = 1;
$company_id = 1;
/////////////////////////////////////////

if($fstate != 'open'){
	$fstate = 'closed';
}
$conn = $ybase->connect();
$base_sql = "select file_id,company_id,kind,parent_id,type,displayname,filename,employee_id,section_auth,employee_type_auth,position_class_auth,sortno,add_date,ext from docu_manage where kind = $kind and company_id = $company_id and status = '1'";

$sql = "{$base_sql} and parent_id = 0 order by sortno";


$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("対象文書がありません");
}

$arr =array();
/////////////////////////第1階層start
for($i=0;$i<$num;$i++){
	list($q_file_id,$q_company_id,$q_kind,$q_parent_id,$q_type,$q_displayname,$q_filename,$q_employee_id,$q_section_auth,$q_employee_type_auth,$q_position_class_auth,$q_sortno,$q_add_date,$q_ext) = pg_fetch_array($result,$i);
if($q_type == "1"){
	$arr[$i]["id"] = "$q_file_id";
	$arr[$i]["text"] = "$q_displayname";
	$arr[$i]["types"] = "folder";

	$ybase->ST_PRI .= "<li class=\"opened\"><span class=\"folder\">{$q_displayname}</span>\n<ul>\n";
/////////////////////////第2階層start
	$sql = "{$base_sql} and parent_id = $q_file_id order by sortno";
	$result2 = $ybase->sql($conn,$sql);
	$num2 = pg_num_rows($result2);
	if(!$num2){
		$arr[$i]["children"][0]["id"] = "x";
		$arr[$i]["children"][0]["text"] = "なし";
		$arr[$i]["children"][0]["types"] = "folder";
		$arr[$i]["children"][0]["state"] = "open";
		$arr[$i]["state"] = "closed";
	}
for($i2=0;$i2<$num2;$i2++){
	list($q_file_id2,$q_company_id2,$q_kind2,$q_parent_id2,$q_type2,$q_displayname2,$q_filename2,$q_employee_id2,$q_section_auth2,$q_employee_type_auth2,$q_position_class_auth2,$q_sortno2,$q_add_date2,$q_ext2) = pg_fetch_array($result2,$i2);
if($q_type2 == "1"){
	$arr[$i]["children"][$i2]["id"] = "$q_file_id2";
	$arr[$i]["children"][$i2]["text"] = "$q_displayname2";
	$arr[$i]["children"][$i2]["types"] = "folder";
	$arr[$i]["children"][$i2]["state"] = "$fstate";
/////////////////////////第3階層start
	$sql = "{$base_sql} and parent_id = $q_file_id2 order by sortno";
	$result3 = $ybase->sql($conn,$sql);
	$num3 = pg_num_rows($result3);
	if(!$num3){
		$arr[$i]["children"][$i2]["children"][0]["id"] = "x";
		$arr[$i]["children"][$i2]["children"][0]["text"] = "なし";
		$arr[$i]["children"][$i2]["children"][0]["types"] = "folder";
		$arr[$i]["children"][$i2]["children"][0]["state"] = "open";
		$arr[$i]["children"][$i2]["state"] = "closed";
	}
for($i3=0;$i3<$num3;$i3++){
	list($q_file_id3,$q_company_id3,$q_kind3,$q_parent_id3,$q_type3,$q_displayname3,$q_filename3,$q_employee_id3,$q_section_auth3,$q_employee_type_auth3,$q_position_class_auth3,$q_sortno3,$q_add_date3,$q_ext3) = pg_fetch_array($result3,$i3);
if($q_type3 == "1"){
	$arr[$i]["children"][$i2]["children"][$i3]["id"] = "$q_file_id3";
	$arr[$i]["children"][$i2]["children"][$i3]["text"] = "$q_displayname3";
	$arr[$i]["children"][$i2]["children"][$i3]["types"] = "folder";
	$arr[$i]["children"][$i2]["children"][$i3]["state"] = "$fstate";
/////////////////////////第4階層start
	$sql = "{$base_sql} and parent_id = $q_file_id3 order by sortno";
	$result4 = $ybase->sql($conn,$sql);
	$num4 = pg_num_rows($result4);
	if(!$num4){
		$arr[$i]["children"][$i2]["children"][$i3]["children"][0]["id"] = "x";
		$arr[$i]["children"][$i2]["children"][$i3]["children"][0]["text"] = "なし";
		$arr[$i]["children"][$i2]["children"][$i3]["children"][0]["types"] = "folder";
		$arr[$i]["children"][$i2]["children"][$i3]["children"][0]["state"] = "open";
		$arr[$i]["children"][$i2]["children"][$i3]["state"] = "closed";
	}

for($i4=0;$i4<$num4;$i4++){
	list($q_file_id4,$q_company_id4,$q_kind4,$q_parent_id4,$q_type4,$q_displayname4,$q_filename4,$q_employee_id4,$q_section_auth4,$q_employee_type_auth4,$q_position_class_auth4,$q_sortno4,$q_add_date4,$q_ext4) = pg_fetch_array($result4,$i4);
if($q_type4 == "1"){
	$arr[$i]["children"][$i2]["children"][$i3]["children"][$i4]["id"] = "$q_file_id4";
	$arr[$i]["children"][$i2]["children"][$i3]["children"][$i4]["text"] = "$q_displayname4";
	$arr[$i]["children"][$i2]["children"][$i3]["children"][$i4]["types"] = "folder";
	$arr[$i]["children"][$i2]["children"][$i3]["children"][$i4]["state"] = "$fstate";
}elseif($q_type4 == "2"){
	$arr[$i]["children"][$i2]["children"][$i3]["children"][$i4]["id"] = "$q_file_id4";
	$arr[$i]["children"][$i2]["children"][$i3]["children"][$i4]["text"] = "$q_displayname4";
	$arr[$i]["children"][$i2]["children"][$i3]["children"][$i4]["ext"] = "$q_ext4";
}
}
/////////////////////////第4階層end

}elseif($q_type3 == "2"){
	$arr[$i]["children"][$i2]["children"][$i3]["id"] = "$q_file_id3";
	$arr[$i]["children"][$i2]["children"][$i3]["text"] = "$q_displayname3";
	$arr[$i]["children"][$i2]["children"][$i3]["ext"] = "$q_ext3";
}
}
/////////////////////////第3階層end

}elseif($q_type2 == "2"){
	$arr[$i]["children"][$i2]["id"] = "$q_file_id2";
	$arr[$i]["children"][$i2]["text"] = "$q_displayname2";
	$arr[$i]["children"][$i2]["ext"] = "$q_ext2";
}
}
/////////////////////////第2階層end
}elseif($q_type == "2"){
	$arr[$i]["id"] = "$q_file_id";
	$arr[$i]["text"] = "$q_displayname";
	$arr[$i]["ext"] = "$q_ext";
}
}

$json = json_encode($arr);


// ヘッダーを指定
header( "Content-Type: application/json; charset=utf-8" ) ;

// JSONを出力
echo $json ;

////////////////////////////////////////////////
?>