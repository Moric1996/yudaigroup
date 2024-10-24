<?php
include "../AdminLTE/class/ClassLoader.php";
$dbio = new DBIO();

$shop_id = $_POST['shop_id'];
$target_date = $_POST['target_date'];


$fdcost['food_revenue'] = str_replace(',','', $_POST['food_revenue']);
$fdcost['food_beginning_inventory'] = str_replace(',','', $_POST['food_beginning_inventory']);
$fdcost['food_purchase'] = str_replace(',','', $_POST['food_purchase']);
$fdcost['food_end_inventory'] = str_replace(',','', $_POST['food_end_inventory']);


$fdcost['drink_revenue'] = str_replace(',','', $_POST['drink_revenue']);
$fdcost['drink_beginning_inventory'] = str_replace(',','', $_POST['drink_beginning_inventory']);
$fdcost['drink_purchase'] = str_replace(',','', $_POST['drink_purchase']);
$fdcost['drink_end_inventory'] = str_replace(',','', $_POST['drink_end_inventory']);


$dbio->updateFDCost($shop_id, $target_date, $fdcost);


echo true;

