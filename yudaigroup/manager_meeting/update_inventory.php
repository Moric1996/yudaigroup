<?php
include "../AdminLTE/class/ClassLoader.php";
$dbio = new DBIO();

$shop_id = $_POST['shop_id'];
$target_date = $_POST['target_date'];
$inventory = str_replace(',','',$_POST['inventory']);


$datetime = new datetime($target_date);
$datetime->modify('- 1 month');
$last_month_date = $datetime->format('Y-m-01');
$last_month_inventory = str_replace(',','',$_POST['last_month_inventory']);


$datetime2 = new datetime($target_date);
$datetime2->modify('- 1 year');
$last_year_date = $datetime2->format('Y-m-01');
$last_year_inventory = str_replace(',','',$_POST['last_year_inventory']);

$res1 = $dbio->updateInventory($shop_id, $inventory, $target_date);
$res2 = $dbio->updateInventory($shop_id, $last_month_inventory, $last_month_date);
$res3 = $dbio->updateInventory($shop_id, $last_year_inventory, $last_year_date);

if ($res1 && $res2 && $res3) {
    echo true;
    exit;
}

echo false;
