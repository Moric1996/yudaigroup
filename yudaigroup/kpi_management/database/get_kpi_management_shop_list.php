<?php
include('../../inc/ybase.inc');

$ybase = new ybase();
$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');

$first_date = date('Y-m-01'); // 月初
$last_date = date('Y-m-t'); // 今月末
$shop_id = $_POST['shop_id'];

// 店舗毎全体データ取得（月選択）
$sql = <<<SQL
SELECT name,id
FROM shop_list
SQL;
$res = pg_query($conn, $sql);
$shop_data = pg_fetch_all($res);
echo json_encode($shop_data);

