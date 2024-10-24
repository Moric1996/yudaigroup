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

    $ymList = array();
    while ($start < $end) {
        $ymList[] = array('value' => $start->format('Y-m'), 'text' => $start->format('Y年m月'));
        $start->modify('+1 month');
    }

    return $ymList;
}

$year_month = ($_GET['year_month']) ? $_GET['year_month'] : "";
$date_type = ($_GET['date_type']) ? $_GET['date_type'] : 1;
$target_year_month = new DateTime($year_month);

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

switch ($date_type) {
    case 2:
        $st_datetime = new DateTime($st);
        $st_datetime->modify('-2 month');
        $st = $st_datetime->format('Y-m-01');
        break;
    case 3:
        $st_datetime = new DateTime($st);
        $st_datetime->modify('-5 month');
        $st = $st_datetime->format('Y-m-01');
        break;
    case 4:
        $st_datetime = new DateTime($st);
        $st_datetime->modify('-1 year');
        $st = $st_datetime->format('Y-m-01');
        break;
}

// 日報データ取得
$temp_array = array();
$customer_temp_array = array();
$report = $DBIO->fetchOnsenDailyWorkReport($st, $ed);
foreach ($report as $daily_report) {
    $temp_array[$daily_report['date']][] = array('category_id' => $daily_report['category_id'], 'value' => $daily_report['value']);
    if (in_array($daily_report['category_id'], array(14, 17, 20, 23, 25, 26, 28))) {
        $customer_temp_array[$daily_report['date']] += $daily_report['value'] ? $daily_report['value'] : 0;
    }
}
$date_array = array();
$onsen_array = array();
$food_array = array();
$customer_array = array();
$time_array = array();
$free_array = array();
foreach ($temp_array as $date => $array) {
    $datetime_obj = new DateTime($date);
    $date_array[] = $datetime_obj->format('n/j');
    foreach ($array as $val) {
        // 温浴売上
        if ($val['category_id'] == '9') {
            $onsen_array[] = isset($val['value']) ? $val['value'] : '';
        }
        // 飲食売上
        if ($val['category_id'] == '10') {
            $food_array[] = isset($val['value']) ? $val['value'] : '';
        }
        // 大人時間制客単価
        if ($val['category_id'] == '15') {
            $time_array[] = isset($val['value']) ? $val['value'] : '';
        }
        // 大人フリー客単価
        if ($val['category_id'] == '18') {
            $free_array[] = isset($val['value']) ? $val['value'] : '';
        }
    }
    $customer_array[] = $customer_temp_array[$date];
}
$year_month_option = array_reverse(getMonthRange2('20220101'));
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
        .table-div {
            overflow: auto;
            width: 100%;
        }

        .table-div table {
            margin: 0;
            border-spacing: 0;
            white-space: nowrap;
        }

        td {
            text-align: right;
            padding: 0 3px;
        }

        th {
            text-align: center;
            padding: 0 3px;
        }
    </style>

</head>

<body>
<h1>温泉日報グラフ</h1>

<div class="form-row">
    <form action="chart.php" method="get" class="form-inline">
        <select name="year_month" class="form-control">
            <?php foreach ($year_month_option as $val): ?>
                <option value="<?php echo $val['value'] ?>" <?php echo $year_month == $val['value'] ? 'selected' : '' ?>><?php echo $val['text'] ?></option>
            <?php endforeach; ?>
        </select>

        <input type="radio" name="date_type" value="1" id="month" <?= $date_type == 1 ? 'checked' : '' ?>>
        <label for="month">1ヶ月</label>

        <input type="radio" name="date_type" value="2" id="month3" <?= $date_type == 2 ? 'checked' : '' ?>>
        <label for="month3">3ヶ月</label>

        <input type="radio" name="date_type" value="3" id="month6" <?= $date_type == 3 ? 'checked' : '' ?>>
        <label for="month6">6ヶ月</label>

        <input type="radio" name="date_type" value="4" id="year" <?= $date_type == 4 ? 'checked' : '' ?>>
        <label for="year">1年</label>

        <button class="btn btn-primary">送信</button>
    </form>
</div>

<div style="display: flex">
    <div class="margin">
        <a href="./index.php" class="btn btn-info">
            温泉日報
        </a>
    </div>
    <div class="margin">
        <a href="../index.php" class="btn btn-warning">
            トップに戻る
        </a>
    </div>
</div>

<div style="display: flex;">
    <div style="width: 50%">
        <canvas id="chart1"></canvas>
    </div>
    <div style="width: 50%">
        <canvas id="chart2"></canvas>
    </div>
</div>

<div class="table-div">
    <table border="1">
        <tr>
            <th>日</th>
            <?php foreach ($date_array as $val): ?>
                <th><?= $val ?></th>
            <?php endforeach; ?>
        </tr>
        <tr>
            <th>温浴売上</th>
            <?php foreach ($onsen_array as $val): ?>
                <td><?= number_format($val) ?></td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <th>飲食売上</th>
            <?php foreach ($food_array as $val): ?>
                <td><?= number_format($val) ?></td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <th>客数</th>
            <?php foreach ($customer_array as $val): ?>
                <td><?= number_format($val) ?></td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <th>単価(大人時間割)</th>
            <?php foreach ($time_array as $val): ?>
                <td><?= number_format($val) ?></td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <th>単価(大人フリー)</th>
            <?php foreach ($free_array as $val): ?>
                <td><?= number_format($val) ?></td>
            <?php endforeach; ?>
        </tr>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = <?= json_encode($date_array) ?>

    const data1 = {
        labels: labels,
        datasets: [
            {
                label: '売上客数',
                backgroundColor: 'rgb(133,255,99)',
                borderColor: 'rgb(133,255,99)',
                data: <?= json_encode($customer_array) ?>,
                type: 'line',
                yAxisID: 'z',
            },
            {
                label: '温浴売上',
                backgroundColor: 'rgb(99, 132, 255)',
                borderColor: 'rgb(99, 132, 255)',
                data: <?= json_encode($onsen_array) ?>,
            },
            {
                label: '飲食売上',
                backgroundColor: 'rgb(255, 99, 132)',
                borderColor: 'rgb(255, 99, 132)',
                data: <?= json_encode($food_array) ?>,
            },
        ]
    };

    const data2 = {
        labels: labels,
        datasets: [
            {
                label: '単価(大人時間割)',
                backgroundColor: 'rgb(133,255,99)',
                borderColor: 'rgb(133,255,99)',
                data: <?= json_encode($time_array) ?>,
                type: 'line'
            },
            {
                label: '単価(大人フリー)',
                backgroundColor: 'rgb(250,111,98)',
                borderColor: 'rgb(250,111,98)',
                data: <?= json_encode($free_array) ?>,
                type: 'line'
            },
        ]
    };

    const config1 = {
        type: 'bar',
        data: data1,
        options: {
            plugins: {
                title: {
                    display: true,
                    text: '売上・客数'
                }
            },
            scales: {
                x: {
                    stacked: true
                },
                y: {
                    stacked: true
                },
                z: {
                    position: 'right',
                }
            },
        }
    };

    const config2 = {
        type: 'line',
        data: data2,
        options: {
            plugins: {
                title: {
                    display: true,
                    text: '客単価'
                }
            },
        }
    };

    const chart1 = new Chart(
        document.getElementById('chart1'),
        config1
    );
    const chart2 = new Chart(
        document.getElementById('chart2'),
        config2
    );
</script>
</body>
</html>
