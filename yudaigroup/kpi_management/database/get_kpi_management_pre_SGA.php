<?php
include('../../inc/ybase.inc');

$ybase = new ybase();
$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');

// 速報値は「翌月25日前後」に入るから選択された月の一ヶ月後のデータ（例:2023-05-25のデータは2023年4月の速報値）
$first_date = date('Y-m-01', strtotime($_POST['choice_month'] . ' +1 month'));
$last_date = date('Y-m-t', strtotime($first_date));

// 確定値の場合はこちらを使う
//$first_date = date('Y-m-01', strtotime($_POST['choice_month']));
//$last_date = date('Y-m-t', strtotime($_POST['choice_month']));

$where = ''; // 店舗別,業態別表示の時に使用する
if($_POST['shop_id']){
    $where = 'AND shop_id=' . $_POST['shop_id'];
}
/** 
 * 速報値='shop_preliminary_report'
 * 確定値='shop_performance_month'
 */

// 速報値 販管費取得
$sql = <<<SQL
SELECT *
FROM shop_preliminary_report
WHERE (expense_code, shop_id, date)
IN (SELECT expense_code, shop_id, MAX(date)
FROM shop_preliminary_report
WHERE expense_code
IN (6111, 6311, 6212, 6213, 6312, 6226, 6119, 6112, 6113, 6114, 6115, 6116, 6117, 6211, 6214, 6215, 6216, 6217, 6218, 6219, 6221, 6222, 6223, 6224, 6225, 6227, 6228, 6229, 6314, 6231, 6232)
AND date BETWEEN '$first_date' AND '$last_date'
$where
GROUP BY expense_code, shop_id
)
SQL;
$res = pg_query($conn, $sql);
$shop_data = pg_fetch_all($res);
echo json_encode($shop_data);