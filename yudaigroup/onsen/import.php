<?php

echo "<meta charset='utf-8'>";

$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');
// 行と内容
$data_format = array(
    1 => 1,     // 入館
    2 => 2,     // 入館割引
    3 => 3,     // 延長料金
    4 => 4,     // 個室家族風呂
    5 => 5,     // 回数券販売
    6 => 6,     // 館内着タオル
    7 => 7,     // 10%
    8 => 8,     // 8%
    9 => 9,     // 温浴小計
    10 => 10,   // 飲食小計
    11 => 11,   // その他小計
    12 => 12,   // 合計
    13 => 13,   // 大人時間制売上
    14 => 14,   // 大人時間制人
    15 => 15,   // 大人時間制客単価
    16 => 16,   // 大人フリー売上
    17 => 17,   // 大人フリー人
    18 => 18,   // 大人フリー客単価
    19 => 42,   // 大人ゆうだいコース売上
    20 => 43,   // 大人ゆうだいコース人
    21 => 44,   // 大人ゆうだいコース客単価
    22 => 19,   // 子供時間制売上
    23 => 20,   // 子供時間制人
    24 => 21,   // 子供時間制客単価
    25 => 22,   // 子供フリー売上
    26 => 23,   // 子供フリー人
    27 => 24,   // 子供フリー客単価
    28 => 45,   // 子供ゆうだいコース売上
    29 => 46,   // 子供ゆうだいコース人
    30 => 47,   // 子供ゆうだいコース客単価
    31 => 25,   // 回数券時間制
    32 => 26,   // 回数券フリー
    33 => 27,   // 雄大関係売上
    34 => 28,   // 雄大関係人
    35 => 29,   // 雄大関係客単価
    36 => 30,   // 特別割引
    37 => 31,   // アソビュー
    38 => 32,   // ニフティ 時間割
    39 => 33,   // ニフティ 会員フリー
    40 => 34,   // ゴルフ割引
    41 => 35,   // レジャパス
    42 => 36,   // JTB電子チケット
    43 => 37,   // JAF割引
    44 => 38,   // その他
    45 => 39,   // その他入館割引
    46 => 40,   // その他未手続き
    47 => 41,   // 合計
);

// 10年あればまあいいか
$year_format_array = array(
    "23" => "2011",
    "24" => "2012",
    "25" => "2013",
    "26" => "2014",
    "27" => "2015",
    "28" => "2016",
    "29" => "2017",
    "30" => "2018",
    "31" => "2019",
    "1" => "2019",
    "2" => "2020",
    "3" => "2021",
    "4" => "2022",
    "5" => "2023",
    "6" => "2024",
    "7" => "2025",
    "8" => "2026",
    "9" => "2027",
    "10" => "2028",
    "11" => "2029",
    "12" => "2030",
    "13" => "2031",
    "14" => "2032",
    "15" => "2033",
    "16" => "2034",
    "17" => "2035",
    "18" => "2036",
    "19" => "2037",
    "20" => "2038",
    "21" => "2039",
    "22" => "2040",
);

// アップロードされたファイル移動
$filePath = "./csv/" . $_FILES["csvFile"]["name"];
if (move_uploaded_file($_FILES["csvFile"]["tmp_name"], $filePath)) {
    chmod($filePath, 0644); // ファイルアップロード成功
} else {
    // ファイルアップロード失敗
    echo "ファイルアップロードに失敗しました。もう1度やりなおしてください。";
    exit;
}

$objFile = new SplFileObject($filePath);
$objFile->setFlags(SplFileObject::READ_CSV);
//$objFile->setCsvControl("\t" /* 区切り文字 */, "\"" /* 囲い文字 */);


foreach ($objFile as $line_num => $line) {
    // 1行目はヘッダーなので処理しない
    if ($line_num === 0) {
        continue;
    }
    $target_date = '';
    foreach ($line as $column_num => $value) {
        $value = mb_convert_encoding($value, "UTF-8", "sjis-win");
        // 1列名ならば日付対応
        if ($column_num == 0) {
            // 年月日の各パーツを分割する
            preg_match( "/([0-9]*)年([0-9]*)月([0-9]*)日/", $value, $data );
            if (!$data[1] || !$data[2] || !$data[3]) {
                break;
            }
            // 先頭0埋めでYYYYMMDD形式の日付文字列に変換する
            $target_date = sprintf( "%04.4d%02.2d%02.2d", $data[1], $data[2], $data[3]);
            continue;
        }

        // 2列以降
        // 値がnull等であれば0とする
        if (empty($value) || !is_numeric($value)) {
            $value = 0;
        }
        // データが存在していなかったらインサート 1つ1つチェックするのは微妙か？
        if (!is_target_data($conn, $target_date, $column_num)) {
            insert_data($conn, $target_date, $data_format[$column_num], $value);
        } else {
            update_data($conn, $target_date, $data_format[$column_num], $value);
        }

    }

}

function is_target_data($conn, $date, $code)
{
    $sql = <<<SQL
SELECT id
FROM onsen_daily_work_report
WHERE date = '{$date}'
AND category_id = {$code}
SQL;
    $res = pg_query($conn, $sql);
    if (pg_num_rows($res) > 0) {
        return true;
    }
    return false;
}

function insert_data($conn, $date, $code, $value)
{
    $value = str_replace(',', '', $value);
    $sql = <<<SQL
INSERT INTO onsen_daily_work_report
(date, category_id, value)
VALUES
('$date', $code, '$value')
SQL;
    pg_query($conn, $sql);

}

function update_data($conn, $date, $code, $value)
{
    $value = str_replace(',', '', $value);
    $sql = <<<SQL
UPDATE onsen_daily_work_report
SET
value = '$value'
WHERE date = '$date'
AND category_id = $code
SQL;
    pg_query($conn, $sql);
}

?>

<!DOCTYPE html>
<html>
<head>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="../AdminLTE/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../AdminLTE/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="../AdminLTE/bower_components/Ionicons/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../AdminLTE/dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
          page. However, you can choose any other skin. Make sure you
          apply the skin class to the body tag so the changes take effect. -->
    <link rel="stylesheet" href="../AdminLTE/dist/css/skins/skin-blue.min.css">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>CSV取込</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html {
            margin: 10px;
        }
    </style>

    <h3>アップロード完了</h3>
    <a href="index.php" class="btn btn-success">
        <div>戻る</div>
    </a>
</head>
