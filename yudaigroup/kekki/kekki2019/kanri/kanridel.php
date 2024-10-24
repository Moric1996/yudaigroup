<?php
include("../base.inc");
include("../item_list.inc");

$base            = new base();
$number = 1;
$conn = $base->connect();
if(!$entry_id){
	print "error<br>";
	exit;
}

$sql = "delete from answerdatakekki2019 where entry_id = $entry_id";
$result = $base->sql($conn,$sql);

header("Location: https://yournet-jp.com/kekki2019/kanri/kanri.php");
exit;
?>