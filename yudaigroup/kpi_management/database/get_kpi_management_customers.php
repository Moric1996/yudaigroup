<?php
include('../../inc/ybase.inc');

$ybase = new ybase();
$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');

$shop_id = $_POST['shop_id'];

$first_date = date('Y-m-01', strtotime($_POST['choice_month']));
$last_date = date('Y-m-t', strtotime($first_date));

// 客数
$sql =<<<SQL
SELECT shop_id, SUM(customers_num) AS customers_num
FROM yudai_data_news
WHERE date >=  '$first_date'
AND date <= '$last_date'
GROUP BY shop_id
SQL;
$res = pg_query($conn, $sql);
$shop_data = pg_fetch_all($res);
echo json_encode($shop_data);
