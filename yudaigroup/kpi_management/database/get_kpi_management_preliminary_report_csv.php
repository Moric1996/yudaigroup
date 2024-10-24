<?php
include('../../inc/ybase.inc');

$ybase = new ybase();
$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');

// 速報値は「翌月25日前後」に入るから選択された月の一ヶ月後のデータ（例:2023-05-25のデータは2023年4月の速報値）
$first_date = date('Y-m-01', strtotime($_POST['choice_month'] . ' +1 month'));
$last_date = date('Y-m-t', strtotime($first_date));

$where = ''; // 店舗別,業態別表示の時に使用する
if($_POST['shop_id']){
    $where = 'AND shop_id=' . $_POST['shop_id'];
}

// 速報値の取得（その月に何回も保存されていることがあるのでとりあえず日付を見て最新のものを取るようにする）
// [売上高,期首棚卸高,商品仕入高,期末棚卸高,給与手当,雑給,事務員給与,従業員給与,法定福利費,厚生費,退職金] 
$sql = <<<SQL
SELECT *
FROM shop_preliminary_report
WHERE (expense_code, shop_id, date)
IN (SELECT expense_code, shop_id, MAX(date)
FROM shop_preliminary_report
WHERE expense_code
IN (4111, 5111, 5211, 5311, 6111, 6311, 6212, 6213, 6312, 6226, 6119)
AND date BETWEEN '$first_date' AND '$last_date'
$where
GROUP BY expense_code, shop_id)
SQL;
$res = pg_query($conn, $sql);
$shop_data = pg_fetch_all($res);
echo json_encode($shop_data);
