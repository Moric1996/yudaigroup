<?php
include "../AdminLTE/class/ClassLoader.php";
$dbio = new DBIO();

$shop_id = $_POST['target_shop'];
$target_date = $_POST['target_date'];
$work_revenue = $_POST['work_revenue'];



$dbio->upsertTargetManHour($shop_id, $target_date, $work_revenue);

header('Location: ./index3.php');
exit;