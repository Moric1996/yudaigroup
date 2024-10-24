<?

$conn = pg_connect("host=192.168.11.99 user=yournet password=finedrink dbname=yudai_sales port=5432");
$conn2 = pg_connect("host=localhost port=5432 dbname=yudai_admin user=yournet");
$sql =<<<SQL
SELECT *
FROM yudai_data
SQL;
$res = pg_query($conn, $sql);
$data = pg_fetch_all($res);

foreach ($data as $val) {
    $sql =<<<SQL
INSERT INTO yudai_shop_news
VALUES ($1, $2, $3, $4, $5, $6, $7)
SQL;
    pg_query_params($conn2, $sql, $val);
}

echo 'ok';