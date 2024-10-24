<?

$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');
$sql=<<<SQL
INSERT INTO shop_list
VALUES (998, '串家物語ららぽーと沼津', 1, 7 , true, '串家物語', '00027')
SQL;
pg_query($conn,$sql);