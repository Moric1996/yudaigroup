<?php
include('../../inc/ybase.inc');

$ybase = new ybase();
$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');

//$first_date = date('Y-m-01'); // 月初
//$last_date = date('Y-m-t'); // 今月末
$scraping_id = $_POST['scraping_id'];

$first_date = date('Y-m-01', strtotime($_POST['choice_month']));
$last_date = date('Y-m-t', strtotime($_POST['choice_month']));

// 売上関連
$sql = <<<SQL
SELECT SUM(CASE WHEN date BETWEEN '$first_date' AND '$last_date' THEN revenue ELSE 0 END) as revenue_sum,
SUM(CASE WHEN date BETWEEN '$first_date' AND '$last_date' THEN budget ELSE 0 END) as budget_sum,
SUM(CASE WHEN date BETWEEN '$first_date' AND '$last_date' THEN work_time ELSE 0 END) as work_time_sum,shop_id
FROM yudai_data_news
WHERE shop_id::integer = $scraping_id
GROUP BY shop_id
SQL;
$res = pg_query($conn, $sql);
$shop_data = pg_fetch_all($res);
echo json_encode($shop_data);