<?php

include "../AdminLTE/class/ClassLoader.php";

$DBIO = new DBIO();

function getMonthRange2($startUnixTime)
{
    /*    $start = (new DateTime)->setTimestamp($startUnixTime);
        $end   = (new DateTime)->setTimestamp($endUnixTime ? $endUnixTime : time());
        $next_month = new DateInterval('P1M');*/
    $start = new DateTime($startUnixTime);
    $end = new DateTime();
    $end->modify('+1 month');

    $ymList = array();
    while ($start < $end) {
        $ymList[] = array('value' => $start->format('Y-m'), 'text' => $start->format('Y年m月'));
        $start->modify('+1 month');
    }

    return $ymList;
}

function getDateFromWeekInfo($year, $month, $no, $week)
{
    /*
     * 以下の記述は PHP 5.3.0 以降で可能
     * それ以前の PHP では setDate が成功した場合は null がリターンされるので、
     * 2行に分けて $date->format('w'); のように記述すること
     */
    // 指定年月の１日の曜日を抽出する
    $date = new DateTime("{$year}{$month}01");
    $first_week = $date->format('w'); // 0 (日曜)から 6 (土曜) を取得する

    // 1日の曜日の指定週の日付を求める
    $day = ($no - 1) * 7 + 1;


    // 指定曜日と1日の曜日の差分（日数）を求め、指定の日付を計算する
    $diff = $week - $first_week;

    if ($diff < 0) {
        $day += $diff; // 1日の曜日より前の曜日の場合
    } else {
        $day += $diff; // 1日の曜日より後の曜日の場合
    }

    // 組み立てた日付が月の最終日（日数）よりも大きい場合は false リターン
    if ($date->format('t') < $day) {
        return false;
    }

    $new_date = new DateTime();
    $new_date->setDate($year, $month, $day);
    return $new_date;
}

function getBetweenDate($st, $ed)
{
    $s = new DateTime($st);
    $e = new DateTime($ed);

    $result[] = $s->format('Y-m-d');
    $count = 0;
    while ($s->format('Y-m-d') !== $e->format('Y-m-d')) {
        $s->modify('+ 1 day');
        $result[] = $s->format('Y-m-d');
        $count++;
        if ($count > 100) {
            return array();
        }
    }
    return $result;
}

function getWeekRange($date)
{
    // 戻り値
    $start_date = "";
    $end_date = "";
    // UNIXタイムスタンプに変換
    $t = strtotime($date);
    // 開始の日曜を計算
    if (date('w', $t) == 0) {

    } else {
        $t = strtotime("last sunday", $t);
    }
    $start_date = date("Y-m-d", $t);
    // 終了の土曜を計算
    $t = strtotime("next saturday", $t);
    $end_date = date("Y-m-d", $t);

    return array($start_date, $end_date);
}

function get_daily_report_data($category_id, $reports, $between)
{
    $sum = 0;
    $data = array();
    foreach ($between as $target_date) {
        $value = isset($reports[$category_id][$target_date]) ? $reports[$category_id][$target_date] : 0;
        $data[] = array('value' => (int)$value, 'key' => $category_id, 'date' => $target_date, 'class' => 'text-right');
        $sum += $value;
    }
    $data[] = array('value' => $sum, 'class' => 'text-right');
    return $data;
}

// パラメータ処理
$year_month = ($_GET['year_month']) ? $_GET['year_month'] : "";
$target_year_month = new DateTime($year_month);

$week_count = ($_GET['week_count']) ? $_GET['week_count'] : "";

$is_month = ($_GET['is_month']) ? $_GET['is_month'] : false;
$day_count = 0;
// 月の処理
if ($is_month) {
    $week_count = 0;
    $st = $target_year_month->format('Y-m-01');
    // 今月だったら
    $now = new DateTime();
    if ($target_year_month->format('Y-m') === $now->format('Y-m')) {
        $ed = $now->format('Y-m-d');
        $day_count = $now->format('d');
    } else {
        $ed = $target_year_month->format('Y-m-t');
        $day_count = $target_year_month->format('t');
    }
} else { // 週の処理
    // 週指定がない場合直近日曜日
    if (!$week_count) {
        $t = strtotime($target_year_month->format('Y-m-d'));
        // 開始の日曜を計算
        if (date('w', $t) != 0) {
            $t = strtotime("last sunday", $t);
        }
        $week_count = floor(date('j', $t) / 7) + 1;
        $target_year_month = new DateTime(date('Y-m-d', $t));
        $year_month = $target_year_month->format('Y-m');
    }

    // 対象週の日曜日を取得する
    $target_sunday = getDateFromWeekInfo($target_year_month->format('Y'), $target_year_month->format('m'), $week_count, 0);
    // もしなかったら第1日曜日
    if (!$target_sunday) {
        $week_count = 1;
        $target_sunday = getDateFromWeekInfo($target_year_month->format('Y'), $target_year_month->format('m'), $week_count, 0);
    }
    // 開始日終了取得
    list($st, $ed) = getWeekRange($target_sunday->format('Y-m-d'));
    $day_count = 7;
}


// フォームで使う年月の配列を取得する
$year_month_option = array_reverse(getMonthRange2('20220101'));

// 月予算取得
$month_start = $target_year_month->format('Y-m-01');
$month_end = $ed;
$revenue = $DBIO->fetchOnsenDailyWorkReportSum($month_start, $month_end, 12);
$last_date = $DBIO->fetchOnsenDailyWorkReportLastDate($month_start, $month_end, 12);
$month_budget = $DBIO->fetchOnsenMonthBudgetSum($month_start, $last_date);

// 天気データ取得
$weather_array = array();
foreach ($DBIO->fetchTargetWeather($st, $ed) as $weather) {
    $weather_array[$weather['date']] = $weather['weather'];
}

// 目標データ取得
$objective_array = array();
foreach ($DBIO->fetchOnsenBudget($st, $ed) as $objective) {
    // 温浴？将来的に変えるかも？ なぜかstring
    if ((integer)$objective['type'] === 1) {
        $objective_array[$objective['date']] = (integer)$objective['value'];
    }
}

// 日報データ取得
$daily_report_array = array();
foreach ($DBIO->fetchOnsenDailyWorkReport($st, $ed) as $daily_report) {
    $daily_report_array[$daily_report['category_id']][$daily_report['date']]
        = $daily_report['value'];
}

// 月データ取得
$monthly_report_sum_array = array();
foreach ($DBIO->fetchOnsenMonthlyReportSum($month_start, $month_end) as $monthly_report) {
    $monthly_report_sum_array[$monthly_report['category_id']] = $monthly_report['sum'];
}

$month_budget_all = $DBIO->fetchOnsenMonthBudgetSum($month_start, $target_year_month->format('Y-m-t'));
// 達成率
$achievement_rate = round(($month_budget > 0) ? $revenue / $month_budget * 100 : 0, 2);
// 売上予測取得
$month_predict = round($achievement_rate / 100 * $month_budget_all);

// 表示対象の日付
$target_date_array = getBetweenDate($st, $ed);

// データの縦軸
$row_list = array(
    'day' => '日',
    'weekday' => '曜日',
    'weather' => '天気',
    'objective' => '目標',
    // category_id
    1 => '入館',
    2 => '入館割引',
    3 => '延長料金',
    4 => '個室・家族風呂',
    5 => '回数券販売',
    6 => '館内着・タオル類',
    7 => '物販10％',
    8 => '物販8％',
    9 => '温浴小計',
    10 => '飲食小計',
    11 => 'その他小計',
    12 => '合計',

    'admission_fee_title' => '入館料明細',

    13 => '売上',
    14 => '客数',
    15 => '客単価',
    16 => '売上',
    17 => '客数',
    18 => '客単価',

    42 => '売上',
    43 => '客数',
    44 => '客単価',

    19 => '売上',
    20 => '客数',
    21 => '客単価',
    22 => '売上',
    23 => '客数',
    24 => '客単価',

    45 => '売上',
    46 => '客数',
    47 => '客単価',

    25 => '回数券　時間制',
    26 => '回数券　フリー',
    27 => '売上',
    28 => '客数',
    29 => '客単価',

    'customer_sum' => '来館者数(計)',

    30 => '特別割引',
    31 => 'アソビュー',
    32 => 'ニフティ　時間制',
    33 => 'ニフティ　会員フリー',
    34 => 'ゴルフ割引',
    35 => 'レジャパス',
    36 => 'JTB電子チケット',
    37 => 'JAF割引',
    38 => 'その他',
    39 => '入館割引',
    40 => 'その他(未手続き)',
    41 => '合計',
);


$customer_sum_array = array();
$all_customer_weekly_sum = 0;
foreach ($target_date_array as $target_date) {
    $temp = 0;
    foreach (array(14, 17, 20, 23, 25, 26, 28) as $key) {
        $temp += $daily_report_array[$key][$target_date];
    }
    $customer_sum_array[$target_date] += $temp;
    $all_customer_weekly_sum += $temp;
}
$all_customer_monthly_sum = 0;
foreach ($monthly_report_sum_array as $key => $monthly_report) {
    if (in_array($key, array(14, 17, 20, 23, 25, 26, 28))) {
        $all_customer_monthly_sum += $monthly_report;
    }
}

$table_data = array();
$budget_sum = 0;
foreach ($row_list as $row_key => $row_name) {
    $headers = array();
    $data = array();
    switch (true) {
        case $row_key === 'day':
            $headers = array(
                array('colspan' => 1, 'rowspan' => 3, 'value' => '売上明細'),
                array('colspan' => 1, 'rowspan' => 3, 'value' => '当月予算
                着地予測'),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            foreach ($target_date_array as $target_date) {
                $datetime = new DateTime($target_date);
                $data[] = array('value' => $datetime->format('n月j日'),
                    'key' => $row_key, 'date' => $target_date, 'class' => 'text-center');
            }
            $data[] = array('value' => $is_month ? '月計' : '週計', 'class' => 'text-center');
            $data[] = array('value' => '月計', 'class' => 'text-center');
            break;

        case $row_key === 'weekday':
            $headers = array(
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            foreach ($target_date_array as $target_date) {
                $datetime = new DateTime($target_date);
                $week = array('日', '月', '火', '水', '木', '金', '土');
                $data[] = array('value' => $week[$datetime->format('w')], 'key' => $row_key, 'date' => $target_date, 'class' => 'text-center');
            }
            $data[] = array('value' => '');
            break;

        case $row_key === 'weather':
            $headers = array(
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            foreach ($target_date_array as $target_date) {
                $data[] = array('value' => $weather_array[$target_date], 'key' => $row_key, 'date' => $target_date, 'class' => 'text-center');
            }
            $data[] = array('value' => '');
            break;

        case $row_key === 'objective':
            $headers = array(
                array('colspan' => 1, 'rowspan' => 10, 'value' => '温浴'),
                array('colspan' => 1, 'rowspan' => 6, 'value' => ''),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            foreach ($target_date_array as $target_date) {
                $value = isset($objective_array[$target_date]) ? $objective_array[$target_date] : 0;
                $data[] = array('value' => $value, 'key' => $row_key, 'date' => $target_date, 'class' => 'text-right');
                $budget_sum += $value;
            }
            $data[] = array('value' => $budget_sum, 'class' => 'text-right');
            // 月予算
            $data[] = array('value' => $month_budget, 'class' => 'text-right');
            break;

        case in_array($row_key, array(1, 2, 3, 4, 5, 14, 15, 17, 18, 20, 21, 23, 24, 28, 29, 43, 44, 46, 47)):
            $headers = array(
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            break;
        case $row_key === 6:
            $headers = array(
                array('colspan' => 1, 'rowspan' => 1, 'value' => '目標予算'),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            break;
        case $row_key === 7:
            $headers = array(
                array('colspan' => 1, 'rowspan' => 1, 'value' => number_format($month_budget_all), 'class' => 'text-right'),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            break;
        case $row_key === 8:
            $headers = array(
                array('colspan' => 1, 'rowspan' => 1, 'value' => '着地予測'),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            break;
        case $row_key === 9:
            $headers = array(
                array('colspan' => 1, 'rowspan' => 1, 'value' => number_format((int)$month_predict), 'class' => 'text-right'),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            break;
        case $row_key === 10:
            $headers = array(
                array('colspan' => 1, 'rowspan' => 1, 'value' => '飲食'),
                array('colspan' => 1, 'rowspan' => 1, 'value' => ''),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            break;
        case $row_key === 11:
            $headers = array(
                array('colspan' => 1, 'rowspan' => 1, 'value' => 'その他'),
                array('colspan' => 1, 'rowspan' => 1, 'value' => ''),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            break;
        case $row_key === 12:
            $headers = array(
                array('colspan' => 3, 'rowspan' => 1, 'value' => $row_name),
            );
            break;
        case $row_key === 'admission_fee_title':
            $headers = array(
                array('colspan' => 5 + $day_count, 'rowspan' => 1, 'value' => $row_name, 'class' => 'text-left'),
                array('colspan' => 1, 'rowspan' => 1, 'value' => '来館者比率(週)'),
            );
            break;

        case $row_key === 13:
            $headers = array(
                array('colspan' => 2, 'rowspan' => 3, 'value' => '大人 時間割'),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            break;
        case $row_key === 16:
            $headers = array(
                array('colspan' => 2, 'rowspan' => 3, 'value' => '大人 フリー'),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            break;
        case $row_key === 42:
            $headers = array(
                array('colspan' => 2, 'rowspan' => 3, 'value' => '大人 ゆうだいコース'),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            break;

        case $row_key === 19:
            $headers = array(
                array('colspan' => 2, 'rowspan' => 3, 'value' => '子供 時間割'),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            break;
        case $row_key === 22:
            $headers = array(
                array('colspan' => 2, 'rowspan' => 3, 'value' => '子供 フリー'),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            break;
        case $row_key === 45:
            $headers = array(
                array('colspan' => 2, 'rowspan' => 3, 'value' => '子供 ゆうだいコース'),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            break;


        case $row_key === 'customer_sum':
        case in_array($row_key, array(25, 26)):
            $headers = array(
                array('colspan' => 2, 'rowspan' => 1, 'value' => $row_name),
                array('colspan' => 1, 'rowspan' => 1, 'value' => '客数'),
            );
            break;
        case $row_key === 27:
            $headers = array(
                array('colspan' => 2, 'rowspan' => 3, 'value' => '雄大関係'),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            break;
        case in_array($row_key, range(30, 40)):
            $headers = array(
                array('colspan' => 2, 'rowspan' => 1, 'value' => $row_name),
                array('colspan' => 1, 'rowspan' => 1, 'value' => '売上'),
            );
            break;
        case $row_key === 41:
            $headers = array(
                array('colspan' => 2, 'rowspan' => 3, 'value' => ''),
                array('colspan' => 1, 'rowspan' => 1, 'value' => $row_name),
            );
            break;
    }
    // カテゴリーIDの場合はデータ処理共通
    if ($row_key == 12) { // 温浴集計を予算と比較させるため苦肉の策
        $sum = 0;
        $data = array();
        $budget_sum = 0;
        foreach ($target_date_array as $target_date) {
            $value = isset($daily_report_array[$row_key][$target_date]) ? $daily_report_array[$row_key][$target_date] : 0;
            $add_class = '';
            if ($objective_array[$target_date] > 0 && $value > 0) {
                $add_class = ($objective_array[$target_date] <= $value) ? ' backblue' : ' backred';
            }
            $data[] = array('value' => (int)$value, 'key' => $row_key, 'date' => $target_date, 'class' => 'text-right' . $add_class);
            $sum += $value;
            if ($value > 0) {
                $budget_sum += $objective_array[$target_date];
            }
        }
        $add_class = '';
        if ($budget_sum > 0) {
            $add_class = ($budget_sum <= $sum) ? ' backblue' : ' backred';
        }
        $data[] = array('value' => $sum, 'class' => 'text-right' . $add_class);

        $value = ($monthly_report_sum_array[$row_key]) ? (int)$monthly_report_sum_array[$row_key] : 0;
        $add_class = '';
        if ($month_budget > 0) {
            $add_class = ($month_budget <= $value) ? ' backblue' : ' backred';
        }
        $data[] = array('value' => $value, 'class' => 'text-right' . $add_class);
    } else if ($row_key === 'customer_sum') {
        $sum = 0;
        foreach ($target_date_array as $target_date) {
            $value = isset($customer_sum_array[$target_date]) ? $customer_sum_array[$target_date] : 0;
            $data[] = array('value' => (int)$value, 'key' => $row_key, 'class' => 'text-right');
            $sum += $value;
        }
        $data[] = array('value' => $sum, 'key' => $row_key, 'class' => 'text-right');
        $data[] = array('value' => $all_customer_monthly_sum, 'key' => $row_key, 'class' => 'text-right');
    } else if (in_array($row_key, array(14, 17, 20, 23, 25, 26, 28))) { // 客数
        $week_customer_sum = 0;
        foreach ($target_date_array as $target_date) {
            $value = isset($daily_report_array[$row_key][$target_date]) ? $daily_report_array[$row_key][$target_date] : 0;
            $data[] = array('value' => (int)$value, 'key' => $row_key, 'class' => 'text-right');
            $week_customer_sum += isset($daily_report_array[$row_key][$target_date]) ? $daily_report_array[$row_key][$target_date] : 0;
        }
        $data[] = array('value' => $week_customer_sum, 'class' => 'text-right');
        $data[] = array('value' => ($monthly_report_sum_array[$row_key]) ? (int)$monthly_report_sum_array[$row_key] : 0, 'class' => 'text-right');
        $data[] = array('value' => ($all_customer_weekly_sum
                ? round($week_customer_sum / $all_customer_weekly_sum * 100, 2) : 0) . '%', 'class' => 'text-right');

    } else if (in_array($row_key, array(15, 18, 21, 24, 29))) { // 客単価
        $data = array();
        $week_revenue_sum = 0;
        $week_customer_sum = 0;
        foreach ($target_date_array as $target_date) {
            $value = isset($daily_report_array[$row_key][$target_date]) ? $daily_report_array[$row_key][$target_date] : 0;
            $data[] = array('value' => (int)$value, 'key' => $row_key, 'date' => $target_date, 'class' => 'text-right');
            $week_revenue_sum += isset($daily_report_array[$row_key - 2][$target_date]) ? $daily_report_array[$row_key - 2][$target_date] : 0;
            $week_customer_sum += isset($daily_report_array[$row_key - 1][$target_date]) ? $daily_report_array[$row_key - 1][$target_date] : 0;
        }

        $data[] = array('value' => $week_customer_sum ? (int)round($week_revenue_sum / $week_customer_sum) : 0, 'class' => 'text-right');

        $value = ($monthly_report_sum_array[$row_key - 1])
            ? (int)round($monthly_report_sum_array[$row_key - 2] / $monthly_report_sum_array[$row_key - 1]) : 0;
        $data[] = array('value' => $value, 'class' => 'text-right');
    } else if (is_numeric($row_key)) { // 通常日報データ
        $data = get_daily_report_data($row_key, $daily_report_array, $target_date_array);
        $data[] = array('value' => ($monthly_report_sum_array[$row_key]) ? (int)$monthly_report_sum_array[$row_key] : 0, 'class' => 'text-right');
    } elseif ($row_key !== 'objective' && $row_key !== 'day') { // 予算と日付以外はデータなしを入れる
        $data[] = array('value' => '');
    }

    // つけたし
    if ($row_key === 11) {
        $data[] = array('value' => '月累計達成率', 'class' => 'text-center');
    }
    if ($row_key === 12) {
        $add_class = ($achievement_rate >= 100) ? 'backblue' : 'backred';
        $data[] = array('value' => $achievement_rate . '%', 'class' => 'text-right '.$add_class);
    }


    $table_data[] = array('headers' => $headers, 'data' => $data, 'key' => $row_key);
}
$json = json_encode($table_data);

?>
<!doctype html>
<html lang="ja">

<head>

    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>会議資料</title>

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

    <style>
        [v-cloak] {
            display: none;
        }

        .input-budget {
            margin: 1px;
            border-color: #ff0000;
        }

        .table-div {
            overflow: auto;
            width: 100%;
        }

        .table-div table {
            margin: 0;
            border-spacing: 0;
        }

        th {
            text-align: center;
            white-space: nowrap;
            padding: 0 5px;
        }

        td {
            white-space: nowrap;
            padding: 0 5px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .plus {
            color: #0000ff;
        }

        .minus {
            color: #ff0000;
        }

        .backred {
            background: rgb(255, 124, 128);
        }

        .backblue {
            background: rgb(102, 255, 255);
        }

        tr:nth-child(16) {
            background: #CCFFFF;
        }

        tr:last-child {
            background: #FFC000;
        }

        tr:nth-child(16),
        tr:last-child {
            border-bottom: double 3px;
        }

        table {
            width: 100%;
            position: relative;
            border-collapse: collapse;
            margin-bottom: 40px;
            margin-left: 20px;
        }

        tr:nth-child(1) {
            background: white;
            position: sticky;
            top: 0;
            z-index: 3;
        }

        tr:nth-child(2) {
            background: white;
            position: sticky;
            top: 20px;
            z-index: 2;
        }

        tr:nth-child(3) {
            background: white;
            position: sticky;
            top: 40px;
            z-index: 1;
        }

        tr:nth-child(17) > th, tr:nth-child(17) > td,
        tr:nth-child(20) > th, tr:nth-child(20) > td,
        tr:nth-child(23) > th, tr:nth-child(23) > td,
        tr:nth-child(26) > th, tr:nth-child(26) > td,
        tr:nth-child(29) > th, tr:nth-child(29) > td,
        tr:nth-child(32) > th, tr:nth-child(32) > td,
        tr:nth-child(35) > th, tr:nth-child(35) > td,
        tr:nth-child(36) > th, tr:nth-child(36) > td,
        tr:nth-child(37) > th, tr:nth-child(37) > td,
        tr:nth-child(n+40) > th, tr:nth-child(n+40) > td,
        tr:nth-child(18) > th:first-child,
        tr:nth-child(21) > th:first-child,
        tr:nth-child(24) > th:first-child,
        tr:nth-child(27) > th:first-child,
        tr:nth-child(30) > th:first-child,
        tr:nth-child(33) > th:first-child,
        tr:nth-child(38) > th:first-child
        {
            border-bottom: solid 2px;
        }

        tr:nth-child(36) > td:last-child,
        tr:nth-child(37) > td:last-child {
            border-bottom: solid 1px;
        }
    </style>

</head>

<body>

<div class="col-xs-12" id="app" v-cloak>
    <h3>温泉資料</h3>

    <div class="form-row">
        <div class="form-inline">
            <select v-model="year_month" name="year_month" class="form-control">
                <?php foreach ($year_month_option as $val): ?>
                    <option value="<?php echo $val['value'] ?>"><?php echo $val['text'] ?></option>
                <?php endforeach; ?>
            </select>

            <button class="btn btn-primary" @click="clickMonth">月表示</button>

            <select v-model="week_count" name="week_count" class="form-control">
                <?php foreach (range(1, 5) as $val): ?>
                    <option value="<?php echo $val ?>">
                        第<?php echo $val ?>週
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="btn btn-primary" @click="clickWeek">週表示</button>
        </div>
    </div>
    <div style="display: flex">
        <div class="margin">
            <a href="form.php" class="btn btn-danger">
                CSVインポート
            </a>
        </div>
        <div class="margin">
            <button v-if='!isEdit' @click="toggleEditMode" class="btn btn-success">予算入力</button>
            <span v-else>
            <button @click="toggleEditMode" class="btn btn-secondary">キャンセル</button>
            <button @click="updateOnsenBudget" class="btn btn-primary">予算保存</button>
        </span>
        </div>
        <div class="margin">
            <a href="./chart.php" class="btn btn-info">
                グラフ
            </a>
        </div>
        <div class="margin">
            <a href="../index.php" class="btn btn-warning">
                トップに戻る
            </a>
        </div>
    </div>

    <div class="table-div">
        <table border="2">
            <tr v-for="(row, rowNum) in tableData" :key="rowNum">
                <!-- ヘッダー部分 -->
                <th v-for="(headerCell, cellNum) in row.headers"
                    :colspan="headerCell.colspan" :rowspan="headerCell.rowspan" :key="rowNum+'_'+cellNum"
                    :class="headerCell.class">
                    {{ headerCell.value }}
                </th>
                <!-- データ部分 -->
                <td v-for="(dataCell, dataCellNum) in row.data" :key="dataCellNum" :class="dataCell.class">
                    <!-- 予算の場合だけここを使う -->
                    <span v-if="dataCell.key === 'objective'">
                    <input v-if="isEdit" type="number" v-model="dataCell.value" class="input-budget">
                    <span v-else>
                        {{ dataCell.value | addComma }}
                    </span>
                </span>
                    <span v-else :class="{'minus': dataCell.value < 0}">
                    {{ dataCell.value | addComma }}
                </span>
                </td>
            </tr>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/es6-promise@4/dist/es6-promise.auto.min.js"></script>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            isEdit: false,
            tableData: <?= $json ?>,
            year_month: '<?= $year_month ?>',
            week_count: <?= $week_count ?>,
        },
        mounted() {
            console.log(this.tableData);
        },
        methods: {
            // 月遷移
            clickMonth() {
                window.location.href
                    = 'index.php?year_month=' + this.year_month + '&is_month=1';
            },
            // 週遷移
            clickWeek() {
                window.location.href
                    = 'index.php?year_month=' + this.year_month
                    + '&week_count=' + this.week_count;
            },

            // 予算編集モードにする
            toggleEditMode() {
                this.isEdit = !this.isEdit;
            },
            // 温泉予算更新
            updateOnsenBudget() {
                const self = this;
                const filterData = this.tableData.filter(datum => {
                    return datum.key === 'objective'
                });
                console.log(filterData);
                filterData[0].data.forEach((cell) => {
                    if (cell.key === 'objective') {
                        console.log('テスト');
                        console.log({cell});
                        self.updateBudget(cell.date, 1, cell.value);
                    }
                });
                alert("保存完了");
                this.toggleEditMode();
                location.reload();
            },
            // 予算更新
            updateBudget(date, type, value) {
                const params = new URLSearchParams();
                params.append('date', date);
                params.append('type', type);
                params.append('value', value);
                axios.post("update_budget.php", params);
            },
        },
        filters: {
            addComma: function (value = 0) {
                return value && isFinite(value) ? value.toLocaleString() : value;
            },
        }
    });
</script>
</body>
</html>