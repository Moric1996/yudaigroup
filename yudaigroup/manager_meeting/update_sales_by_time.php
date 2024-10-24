<?php
include "../AdminLTE/class/ClassLoader.php";
$dbio = new DBIO();

$shop_id = $_POST['shop_id'];
$target_date = $_POST['target_date'];



$sales_list['reservation_revenue'] = htmlspecialchars($_POST['reservation_revenue']);
$sales_list['reservation_customers_num'] = htmlspecialchars($_POST['reservation_customers_num']);
$sales_list['reservation_human_time_sales'] = htmlspecialchars($_POST['reservation_human_time_sales']);

$sales_list['free_revenue'] = htmlspecialchars($_POST['free_revenue']);
$sales_list['free_customers_num'] = htmlspecialchars($_POST['free_customers_num']);
$sales_list['free_human_time_sales'] = htmlspecialchars($_POST['free_human_time_sales']);

$dbio->updateSalesByTime($shop_id, $target_date, $sales_list);

$last_year_date = $_POST['last_year_date'];
$last_year_sales_list['reservation_revenue'] = htmlspecialchars($_POST['last_year_reservation_revenue']);
$last_year_sales_list['reservation_customers_num'] = htmlspecialchars($_POST['last_year_reservation_customers_num']);
$last_year_sales_list['reservation_human_time_sales'] = htmlspecialchars($_POST['last_year_reservation_human_time_sales']);

$last_year_sales_list['free_revenue'] = htmlspecialchars($_POST['last_year_free_revenue']);
$last_year_sales_list['free_customers_num'] = htmlspecialchars($_POST['last_year_free_customers_num']);
$last_year_sales_list['free_human_time_sales'] = htmlspecialchars($_POST['last_year_free_human_time_sales']);

$dbio->updateSalesByTime($shop_id, $last_year_date, $last_year_sales_list);


echo true;