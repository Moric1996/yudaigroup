<?php
include('../../inc/ybase.inc');

$ybase = new ybase();
$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');

$first_date = date('Y-m-01', strtotime($_POST['choice_month']));
$last_date = date('Y-m-t', strtotime($_POST['choice_month']));

$where = ''; // 店舗別,業態別表示の時に使用する
if($_POST['shop_id']){
    $where = 'AND shop_id=' . $_POST['shop_id'];
}

// 売上関連
// [売上高,期首棚卸高,商品仕入高,期末棚卸高,給与手当,雑給,事務員給与,従業員給与,法定福利費,厚生費,退職金] 
$sql = <<<SQL
SELECT *
FROM shop_performance_month
WHERE expense_code IN (4111, 5111, 5211, 5311, 6111, 6311, 6212, 6213, 6312, 6226, 6119)
AND month = '$first_date'
$where
SQL;
$res = pg_query($conn, $sql);
$shop_data = pg_fetch_all($res);
echo json_encode($shop_data);
