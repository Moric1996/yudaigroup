<?php
include("../../yudaigroup/inc/auth.inc");
include("../base.inc");
include("../item_list.inc");
if($ybase->my_admin_auth != "1"){
	$ybase->error("Œ ŒÀ‚ª‚ ‚è‚Ü‚¹‚ñ");
}

$base            = new base();
$conn = $base->connect();
if(!$entry_id){
	print "error<br>";
	exit;
}

$sql = "delete from answerdatakekki2019 where entry_id = $entry_id";
$result = $base->sql($conn,$sql);

header("Location: https://yournet-jp.com/kekki/kanri/kanri.php");
exit;
?>