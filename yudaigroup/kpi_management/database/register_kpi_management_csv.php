<?php
include('../../inc/ybase.inc');

$ybase = new ybase();
$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');

$shop_id = $_GET['shop_id'];
$file = $_FILES['file'];
//$date = date('Y-m-d');
$date = $_GET['date'];

// 1~10日登録のデータだったら"2"みたいな感じで細切れで速報値を確認したい時のための場合分け用カラム（今はとりあえず1を入れておく）
$capture_period = 1;

$fileHandle = fopen($file['tmp_name'], 'r');
if ($fileHandle !== false) {
    // ヘッダー行をスキップ
    fgetcsv($fileHandle);
    // 行ごとに処理
    while (($data = fgetcsv($fileHandle)) !== false) {
        // $data[0]にコードが書いてある。csvの最終列が最新のもの（速報値）なので配列の一番後ろを登録すれば良い
        $last_item = count($data) - 1; // 最新データ取得用（配列の最後尾）
        $value = str_replace(',', '', $data[$last_item]);
        if($data[0] === ''){
            continue;
        }else{
            try{
                // レコードの有無を確認(shop_idかつdate,expense_codeが同一のデータがあったらupdate,なければinsert)
                $is_exist = false;
                $exist =<<<SQL
SELECT *
FROM shop_preliminary_report
WHERE shop_id = $shop_id
AND date = '{$date}'
AND expense_code = $data[0]
SQL;
                $exist_res = pg_query($conn, $exist);
                if (pg_num_rows($exist_res) > 0) {
                    $is_exist = true;
                }

                // あったらupdate,なければinsert
                if($is_exist){
                    $sql =<<<SQL
UPDATE shop_preliminary_report
SET
value = $value
WHERE shop_id = $shop_id
AND date = '{$date}'
AND expense_code = $data[0]
SQL;
                    $res = pg_query($conn, $sql);
                }else{
                    $sql =<<<SQL
INSERT INTO shop_preliminary_report
(shop_id, date, expense_code, value, capture_period)
VALUES
($shop_id, '{$date}', $data[0], $value, $capture_period)
SQL;
                    $res = pg_query($conn, $sql);
                }
            }catch(Exception $e){
                echo "Error";
            }
        }
    }
    fclose($fileHandle);
}