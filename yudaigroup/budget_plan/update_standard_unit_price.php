<?php

include "../AdminLTE/class/ClassLoader.php";
$dbio = new DBIO();

$shop_id = $_POST['shop_id'];
$year = $_POST['year'];
$next = $year + 1;

$res = array();


$target_date = $year.'-10-01';

$dbio->upsertStandardUnitPrice($shop_id, $target_date, $_POST['month10']);

$target_date = $year.'-11-01';
$dbio->upsertStandardUnitPrice($shop_id, $target_date, $_POST['month11']);

$target_date = $year.'-12-01';
$dbio->upsertStandardUnitPrice($shop_id, $target_date, $_POST['month12']);

$target_date = $next.'-01-01';
$dbio->upsertStandardUnitPrice($shop_id, $target_date, $_POST['month1']);

$target_date = $next.'-02-01';
$dbio->upsertStandardUnitPrice($shop_id, $target_date, $_POST['month2']);

$target_date = $next.'-03-01';
$dbio->upsertStandardUnitPrice($shop_id, $target_date, $_POST['month3']);

$target_date = $next.'-04-01';
$dbio->upsertStandardUnitPrice($shop_id, $target_date, $_POST['month4']);

$target_date = $next.'-05-01';
$dbio->upsertStandardUnitPrice($shop_id, $target_date, $_POST['month5']);

$target_date = $next.'-06-01';
$dbio->upsertStandardUnitPrice($shop_id, $target_date, $_POST['month6']);

$target_date = $next.'-07-01';
$dbio->upsertStandardUnitPrice($shop_id, $target_date, $_POST['month7']);

$target_date = $next.'-08-01';
$dbio->upsertStandardUnitPrice($shop_id, $target_date, $_POST['month8']);

$target_date = $next.'-09-01';
$dbio->upsertStandardUnitPrice($shop_id, $target_date, $_POST['month9']);


echo true;

