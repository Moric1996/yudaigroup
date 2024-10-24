<?php

echo "<meta charset='utf-8'>";

$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');
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


// 店舗id
if (!$_POST['shop_name']) {
    echo "店舗が選択されていません。";
    exit;
}
$shop_id = $_POST['shop_name'];


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

    foreach ($line as $column_num => $value) {
        $value = mb_convert_encoding($value, "UTF-8", "sjis-win");
        // 1行目なら年月の配列を作る
        if ($line_num == 0 && $column_num > 1) {
            $temp_array = explode(" ", trim($value));
            $temp_year = trim($temp_array[0], "年");
            $temp_month = ($temp_array[1]) ? trim($temp_array[1], "月") : 0;
            $temp_month .= ($temp_array[2]) ? trim($temp_array[2], "月") : "";
            if (!is_numeric($temp_month)) {
                break;
            }
            $temp_month = sprintf('%02d', $temp_month);
            $temp_date = $year_format_array[$temp_year] . $temp_month . "01";
            $target_date_array[$column_num] = $temp_date;
            continue;
        } elseif ($line_num === 0 || $column_num == 1) {
            continue;
        }

        // 各行1列目ならコードを取得
        if ($column_num == 0) {
            // 値が入ってなければ合計項目です
            if (!empty($value)) {
                $code = $value;
                continue;
            } else {
                break;
            }
        }
        if (!$target_date_array[$column_num]) {
            continue;
        }
        // 2列以降
        // 値がnull等であれば0とする
        if (empty($value)) {
            $value = 0;
        }
        // データが存在していなかったらインサート 1つ1つチェックするのは微妙か？
        if (!is_target_shop_data($conn, $shop_id, $target_date_array[$column_num], $code)) {
            insert_data($conn, $shop_id, $target_date_array[$column_num], $code, $value);
        } else {
            update_data($conn, $shop_id, $target_date_array[$column_num], $code, $value);
        }

    }

}

/*echo "<pre>";
print_r($records);
echo "</pre>";*/
//foreach (glob("./files/*.csv") as $delFile) unlink($delFile); // ファイル削除処理

function is_target_shop_data($conn, $shop_id, $date, $code)
{
    $sql = <<<SQL
SELECT shop_id
FROM shop_performance_month
WHERE shop_id = {$shop_id}
AND month = '{$date}'
AND expense_code = {$code}
SQL;
    $res = pg_query($conn, $sql);
    if (pg_num_rows($res) > 0) {
        return true;
    }
    return false;
}

function insert_data($conn, $shop_id, $date, $code, $value)
{
    $value = str_replace(',', '', $value);
    $sql = <<<SQL
INSERT INTO shop_performance_month
(shop_id, month, expense_code, value)
VALUES
($shop_id, '$date', $code, '$value')
SQL;
    pg_query($conn, $sql);

}

function update_data($conn, $shop_id, $date, $code, $value)
{
    $value = str_replace(',', '', $value);
    $sql = <<<SQL
UPDATE shop_performance_month
SET
value = '$value'
WHERE shop_id = $shop_id 
AND month = '$date'
AND expense_code = $code
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
    <a href="csv_import.php?shop_id="<?= $shop_id ?> class="btn btn-info">
        <div>インポートへ戻る</div>
    </a>

    <a href="csv_display.php" class="btn btn-success">
        <div>一覧</div>
    </a>
</head>