<?php
include "../AdminLTE/class/ClassLoader.php";
$dbio = new DBIO();

$shop_id = $_POST['shop_id'];
$target_date = $_POST['target_date'];



$direct = str_replace(',','',$_POST['direct_labor_cost']);
$indirect = str_replace(',','',$_POST['indirect_labor_cost']);



$dbio->upsertLaborCost($shop_id, $target_date, $direct, $indirect);

echo true;
