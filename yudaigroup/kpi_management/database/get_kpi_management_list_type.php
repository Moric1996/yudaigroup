<?php
include('../../inc/ybase.inc');

$ybase = new ybase();
$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');

$shop_id = $_POST['shop_id'];

//$first_date = date('Y-m-01', strtotime($_POST['choice_month']));
//$last_date = date('Y-m-t', strtotime($_POST['choice_month']));
// 「翌月25日前後」「翌月末」に入るから選択された月の一ヶ月後のデータ（例:2023年5月のデータは2023年4月の速報値、確定値）
$first_date = date('Y-m-01', strtotime($_POST['choice_month']));
$last_date = date('Y-m-t', strtotime($first_date));

$shop_name_like = json_decode($_POST['shop_name_like']);
$where_text = "WHERE is_scraping=TRUE AND";
$count = 0; // 配列の一番最初か判定
foreach($shop_name_like as $shop){
    if($count !== 0) $where_text = $where_text . " OR";
    $where_text = $where_text . " abbreviation LIKE '%" . $shop . "%'";
    $count++;
}

// 店舗毎全体データ取得
$sql = <<<SQL
SELECT shop_list.id AS shop_data_id,shop_list.*,kpi_managements.*
FROM shop_list
LEFT JOIN kpi_managements
ON kpi_managements.shop_id = shop_list.id
AND data_date BETWEEN '$first_date' AND '$last_date'
$where_text
SQL;
$res = pg_query($conn, $sql);
$shop_data = pg_fetch_all($res);
echo json_encode($shop_data);
