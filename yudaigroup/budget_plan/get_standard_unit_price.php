<?php

include "../AdminLTE/class/ClassLoader.php";
$dbio = new DBIO();

$shop_id = $_POST['shop_id'];
$year = $_POST['year'];
$next = $year + 1;

$res = array();

$target_date = $year.'-10-01';
$res['month10'] = $dbio->fetchStandardUnitPrice($shop_id, $target_date);

$target_date = $year.'-11-01';
$res['month11'] = $dbio->fetchStandardUnitPrice($shop_id, $target_date);

$target_date = $year.'-12-01';
$res['month12'] = $dbio->fetchStandardUnitPrice($shop_id, $target_date);

$target_date = $next.'-01-01';
$res['month1'] = $dbio->fetchStandardUnitPrice($shop_id, $target_date);

$target_date = $next.'-02-01';
$res['month2'] = $dbio->fetchStandardUnitPrice($shop_id, $target_date);

$target_date = $next.'-03-01';
$res['month3'] = $dbio->fetchStandardUnitPrice($shop_id, $target_date);

$target_date = $next.'-04-01';
$res['month4'] = $dbio->fetchStandardUnitPrice($shop_id, $target_date);

$target_date = $next.'-05-01';
$res['month5'] = $dbio->fetchStandardUnitPrice($shop_id, $target_date);

$target_date = $next.'-06-01';
$res['month6'] = $dbio->fetchStandardUnitPrice($shop_id, $target_date);

$target_date = $next.'-07-01';
$res['month7'] = $dbio->fetchStandardUnitPrice($shop_id, $target_date);

$target_date = $next.'-08-01';
$res['month8'] = $dbio->fetchStandardUnitPrice($shop_id, $target_date);

$target_date = $next.'-09-01';
$res['month9'] = $dbio->fetchStandardUnitPrice($shop_id, $target_date);

$json = json_encode($res);

echo $json;

