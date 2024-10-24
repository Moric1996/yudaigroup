<?php
include('../../inc/ybase.inc');

$ybase = new ybase();
$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');

$shop_id = $_POST['shop_id'];


// 「翌月25日前後」「翌月末」に入るから選択された月の一ヶ月後のデータ（例:2023年5月のデータは2023年4月の速報値、確定値）
$first_date = date('Y-m-01', strtotime($_POST['choice_month']));
$last_date = date('Y-m-t', strtotime($first_date));

// 店舗毎全体データ取得（月選択）
$sql = <<<SQL
SELECT shop_list.id AS shop_data_id,shop_list.*,kpi_managements.*
FROM shop_list
LEFT JOIN kpi_managements
ON kpi_managements.shop_id = shop_list.id
AND data_date BETWEEN '$first_date' AND '$last_date'
WHERE is_scraping = true
ORDER BY
(SELECT CASE category
WHEN 1 THEN 1
WHEN 2 THEN 2
WHEN 3 THEN 3
WHEN 4 THEN 4
WHEN 5 THEN 5
WHEN 6 THEN 6
WHEN 7 THEN 7
ELSE 8
END
), scraping_id
SQL;
$res = pg_query($conn, $sql);
$shop_data = pg_fetch_all($res);
echo json_encode($shop_data);

