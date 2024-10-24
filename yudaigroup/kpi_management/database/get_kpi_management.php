<?php
include('../../inc/ybase.inc');

$ybase = new ybase();
$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');

// 「翌月25日前後」「翌月末」に入るから選択された月の一ヶ月後のデータ（例:2023年5月のデータは2023年4月の速報値、確定値）
$first_date = date('Y-m-01', strtotime($_POST['choice_month']));
$last_date = date('Y-m-t', strtotime($first_date));

$shop_id = $_POST['shop_id'];

// 店舗選択して選択された月のデータ取得
$sql = <<<SQL
SELECT *
FROM kpi_managements
WHERE data_date BETWEEN '$first_date' AND '$last_date'
AND shop_id = $shop_id
SQL;
$res = pg_query($conn, $sql);
$shop_data = pg_fetch_all($res);
echo json_encode($shop_data);
