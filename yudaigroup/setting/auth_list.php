<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();


/////////////////////////////////////////

$conn = $ybase->connect();
$sql = "select employee_id,employee_num,f_name || ' ' || g_name,sex,company_id,section_id,employee_type,position_name,position_class,view_auth,edit_auth,admin_auth,email,add_date,status from employee_list where status = '1' order by employee_id";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("対象者がおりません");
}

$ybase->title = "閲覧権限管理";

$ybase->HTMLheader();


$ybase->ST_PRI .= <<<HTML
<header>
<nav class="navbar navbar-expand-md navbar-dark" style="background-color: #63baa8;">
<a class="navbar-brand" href="{$ybase->PATH}portal/index.php">雄大グループ SYSTEM ポータル</a>

</nav>
</header>
<div class="container">
<p></p>
<p class="text-center">閲覧権限管理</p>

<p></p>
<p class="h5 text-center">閲覧権限管理</p>
<table class="table table-bordered table-hover table-sm">
  <thead>
    <tr align="center" class="table-primary">
      <th scope="col">コンテンツ</th>
      <th scope="col">役職による権限設定</th>
    </tr>
  </thead>
  <tbody>
HTML;

foreach($ybase->content_list as $key => $val){
$ybase->ST_PRI .= <<<HTML

<tr>
<td align="center" valign="middle">
$val
</td>
<td align="left">
HTML;
$k=0;
	foreach($ybase->position_class_list as $key2 => $val2){
	$k++;
	if(($k%3) == 0){
		$addbr="<br>";
	}else{
		$addbr="";
	}
$ybase->ST_PRI .= <<<HTML

<input type="checkbox" name="posi{$key}" value="$key2">{$val2}　{$addbr}

HTML;
	}
$ybase->ST_PRI .= <<<HTML

</td>
</tr>










HTML;
}



$ybase->ST_PRI .= <<<HTML

 </tbody>
</table>

</div>
<p></p>
HTML;










$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>