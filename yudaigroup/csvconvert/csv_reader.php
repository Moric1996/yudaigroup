<?php

$array = array(
    '111'=>'えびす家',
);
$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');
foreach ($array as $id => $shop) {
    $filepath = "csv/{$shop}3106.csv";
    $file = new SplFileObject($filepath);
    $file->setFlags(SplFileObject::READ_CSV);

    $shop_id = $id;

    $array = array(
        2=>'20180701',
        3=>'20180801',
        4=>'20180901',
        5=>'20181001',
        6=>'20181101',
        7=>'20181201',
        8=>'20190101',
        9=>'20190201',
        10=>'20190301',
        11=>'20190401',
        12=>'20190501',
        13=>'20190601'
    );

    foreach ($file as $line_num => $line) {
        if ($line_num == 0) {
            continue;
        }
        $code = null;
        foreach ($line as $cell_num => $cell) {
            if ($cell_num == 0 && empty($cell)){
                break;
            } elseif($cell_num == 0) {
                $code = $cell;
                continue;
            } elseif ($cell_num == 1) {
                continue;
            }

            insert_data($conn, $shop_id, $array[$cell_num], $code, $cell);

        }
    }
}

function insert_data($conn, $shop_id, $date, $code, $value) {
    $sql =<<<SQL
INSERT INTO shop_performance_month
(shop_id, month, expense_code, value)
VALUES
($shop_id, '$date', $code, '$value')
SQL;
    pg_query($conn, $sql);

}
