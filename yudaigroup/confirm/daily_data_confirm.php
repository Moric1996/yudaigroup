<?php

$to = 'd-kageshima@yournet-jp.com, katsumata@yournet-jp.com';
$subject = '雄大日報データチェック';

$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');
$datetime = new DateTime();
$datetime->modify('-2 days');
$date = $datetime->format('Y-m-d');
$sql =<<<SQL
SELECT 
    SUM(revenue) as revenue,
    SUM(budget) as budget,
    SUM(work_time) as work_time,
    SUM(customers_num) as customers_num,
    SUM(discount_ticket) as discount_ticket
FROM yudai_data_news
WHERE date = '{$date}'
SQL;
$res = pg_query($conn, $sql);

if (pg_num_rows($res) == 0) {
    mail($to, $subject, 'レコードを取得できませんでした。確認してください。');
    exit;
}

$array = pg_fetch_array($res);
//$text = array('discount_ticket'=>'割引',);
foreach ($array as $val) {
    if ($val == 0) {
        mail($to, $subject, "{$date}のデータがないようです。取得できているか、確認してください。");
        exit;
    }
}
