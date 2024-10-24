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
$company_id = 5;
$dirname = "/usr/local/htdocs/afc";
$deldir = array('.','..','app','css','asfs','webAd','');

//引数 $file_list2 配列の[N][1] でソートする関数
function order_by_desc($a, $b){
	if ( strtotime($a[1]) > strtotime($b[1]) ){
		return -1;
	}elseif(strtotime($a[1]) < strtotime($b[1])) {
		return 1;
	}else{
		return 0;
	}
}

/////////////////////////////////////////
$dir_h = opendir("$dirname");

while (false !== ($file_list[] = readdir($dir_h))) ;
closedir( $dir_h ) ;

$file_list = array_diff($file_list,$deldir);
$file_list2 = array();

$i = 0 ;
foreach($file_list as $file_name){
//$file_list2[N] の [0]にファイル名、[1]にファイル更新日
	$file_list2[$i][0] = "$file_name";
// ファイルの更新日時を取得
	$file_list2[$i][1] = date("Y/m/d H:i", filemtime( "$dirname"."/".$file_name )) ;
	$i++ ;
}

//$file_list2 をファイルの更新日時でソート
usort($file_list2,'order_by_desc');

//print_r($file_list2) ;

/////////////////////////////////////////

if($fstate != 'open'){
	$fstate = 'closed';
}

$arr =array();
/////////////////////////第1階層start
$i=0;
foreach($file_list2 as $file_n){
$q_type = "1";
$q_file_id = $file_n[0];
$q_displayname = $file_n[0];


	$arr[$i]["id"] = "$q_file_id";
	$arr[$i]["text"] = "$q_displayname";
	$arr[$i]["types"] = "folder";
	$arr[$i]["state"] = "closed";

	$ybase->ST_PRI .= "<li class=\"opened\"><span class=\"folder\">{$q_displayname}</span>\n<ul>\n";
/////////////////////////第2階層start
	$dir_h2nd = opendir("$dirname"."/"."$q_file_id");
	$file_list2nd = array();
	while (false !== ($file_list2nd[] = readdir($dir_h2nd))) ;
	closedir($dir_h2nd) ;

	$file_list2nd = array_diff($file_list2nd,$deldir);

	if(count($file_list2nd) < 1){
		$arr[$i]["children"][0]["id"] = "x";
		$arr[$i]["children"][0]["text"] = "なし";
		$arr[$i]["children"][0]["types"] = "folder";
		$arr[$i]["children"][0]["state"] = "open";
		$arr[$i]["state"] = "closed";
	}
$i2=0;
foreach($file_list2nd as $file_n2nd){
	$q_type2 = "2";
	$q_file_id2 = $file_n2nd;
	$q_displayname2 = $file_n2nd;
	$q_ext2 = substr($file_n2nd, strrpos($file_n2nd, '.') + 1);

if($q_type2 == "1"){
	$arr[$i]["children"][$i2]["id"] = "$q_file_id2";
	$arr[$i]["children"][$i2]["text"] = "$q_displayname2";
	$arr[$i]["children"][$i2]["types"] = "folder";
	$arr[$i]["children"][$i2]["state"] = "$fstate";
}elseif($q_type2 == "2"){
	$arr[$i]["children"][$i2]["id"] = "$q_file_id2";
	$arr[$i]["children"][$i2]["text"] = "$q_displayname2";
	$arr[$i]["children"][$i2]["ext"] = "$q_ext2";
}
$i2++;
}
/////////////////////////第2階層end
$i++;
}

$json = json_encode($arr);


// ヘッダーを指定
header( "Content-Type: application/json; charset=utf-8" ) ;

// JSONを出力
echo $json ;

////////////////////////////////////////////////
?>