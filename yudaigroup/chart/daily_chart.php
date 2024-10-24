<?php

include "../AdminLTE/class/ClassLoader.php";


$DBIO = new DBIO();

$target_shop = $_GET['target_shop'] ? $_GET['target_shop'] : '101';
$year_month = $_GET['year_month'];


$target_date = new DateTime($year_month);
$shop_id = $target_shop;

$st_date = $target_date->format('Y-m-01');
$ed_date = $target_date->format('Y-m-t');


$display_date = $target_date->format('Y年n月');


$data = $DBIO->fetchDataTargetShop($st_date, $ed_date, $shop_id);

// ランチディナー用にデータを取得する
$lunch_data = $DBIO->fetchLunchRevenue($st_date, $ed_date, $shop_id);
foreach ($lunch_data as $val) {
    $temp_date = new DateTime($val['sale_date']);
    $lunch_res[$temp_date->format('d')] = $val['revenue'];
}
$dinner_data = $DBIO->fetchDinnerRevenue($st_date, $ed_date, $shop_id);
foreach ($dinner_data as $val) {
    $temp_date = new DateTime($val['sale_date']);
    $dinner_res[$temp_date->format('d')] = $val['revenue'];
}

$target_date->modify('-1 year');
$last_st_date = $target_date->format('Y-m-01');
$last_ed_date = $target_date->format('Y-m-t');
$last_data = $DBIO->fetchDataTargetShop($last_st_date, $last_ed_date, $shop_id);

$last_res = array();
foreach ($last_data as $val) {
    $temp_date = new DateTime($val['date']);
    $last_res[$temp_date->format('d')] = $val['revenue'];
}

$chart = array();
$week = array(
    '0' => '(日)',
    '1' => '(月)',
    '2' => '(火)',
    '3' => '(水)',
    '4' => '(木)',
    '5' => '(金)',
    '6' => '(土)',
);
foreach ($data as $val) {
    $name = $val['name'];
    $temp_date = new DateTime($val['date']);
    $chart['last_revenue_rate'][] = ($temp_date->format('d') && $last_res[$temp_date->format('d')]) ? round(($val['revenue'] / $last_res[$temp_date->format('d')] * 100), 1) : 0;
    $chart['labels'][] = $temp_date->format('n/j') . $week[$temp_date->format('w')];
    $chart['budget'][] = $val['budget'];
    $chart['revenue'][] = $val['revenue'];
    $chart['work_time'][] = $val['work_time'];
    $chart['customers_num'][] = $val['customers_num'];
    $chart['budget_rate'][] = ($val['budget']) ? round($val['revenue'] / $val['budget'] * 100, 1) : 0;
    $chart['work_time_rate'][] = ($val['work_time']) ? round($val['revenue'] / $val['work_time']) : 0;
    $chart['lunch'][] = (($temp_date->format('d') && $lunch_res[$temp_date->format('d')])) ? $lunch_res[$temp_date->format('d')]: 0;
    $chart['dinner'][] = (($temp_date->format('d') && $dinner_res[$temp_date->format('d')])) ? $dinner_res[$temp_date->format('d')]: 0;
}





$labels_json = json_encode($chart['labels']);

$revenue_json = json_encode($chart['revenue']);

$budget_json = json_encode($chart['budget']);

$work_time_json = json_encode($chart['work_time']);

$customers_num_json = json_encode($chart['customers_num']);

$budget_rate_json = json_encode($chart['budget_rate']);

$work_time_rate_json = json_encode($chart['work_time_rate']);

$work_time_rate = json_encode($chart['work_time_rate']);

$last_revenue_rate_json = json_encode($chart['last_revenue_rate']);

$lunch_json = json_encode($chart['lunch']);
$dinner_json = json_encode($chart['dinner']);


$year_month_option = array_reverse(getMonthRange2('20190101'));
function getMonthRange2($startUnixTime)
{
    $start = new DateTime($startUnixTime);
    $end = new DateTime();

    $ymList = array();
    while ($start < $end) {
        $ymList[] = array('value' => $start->format('Y-m'), 'text' => $start->format('Y年m月'));
        $start->modify('+1 month');
    }

    return $ymList;
}

$shop_all = $DBIO->fetchShopList();
foreach ($shop_all as $key => $one) {
    $shop_list[$key]['id'] = $one['id'];
    $shop_list[$key]['name'] = $one['name'];
    if ($target_shop == $one['id']) {
        $shop_list[$key]['selected'] = 'selected';
    }
}


?>

    <!DOCTYPE html>
    <!--
    This is a starter template page. Use this page to start your new project from
    scratch. This page gets rid of all links and provides the needed markup only.
    -->
    <html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>日次業績確認</title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
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


        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

        <![endif]-->

        <!-- Google Font -->
        <link rel="stylesheet"
              href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
        <!-- ファビコン -->
        <link rel="icon" type="image/png" href="../portal/img/yudai_favicon.ico">
    </head>
<body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"
        integrity="sha512-d9xgZrVZpmmQlfonhQUvTR7lMPtO7NkZMkA0ABN3PHCbKA5nqylQ/yWlFAyY6hYgdF1Qh6nYiuADWwKB4C2WSw=="
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>


<form method="get" action="./daily_chart.php">

    <select name="year_month">
        <?php foreach ($year_month_option as $val): ?>
            <option value="<? echo $val['value'] ?>" <? echo $val['value'] == $year_month ? 'selected' : '' ?>><? echo $val['text'] ?></option>
        <?php endforeach; ?>
    </select>

    <select name="target_shop">
        <?php foreach ($shop_list as $val): ?>
            <option value="<?= $val['id'] ?>" <?= $val['selected'] ?>><?= $val['name'] ?></option>
        <?php endforeach; ?>
    </select>


    <button class="btn btn-primary" type="submit">送信</button>
</form>


<button class="btn btn-warning" id="revenue_chart">売上実績</button>
<button class="btn btn-warning" id="fl_chart">FL実績</button>
<button class="btn btn-warning" id="work_chart">人時売上高</button>
<canvas id="chart"></canvas>
<script>
    const date = <?= $labels_json ?>;

    const budget = <?= $budget_json ?>;
    const revenue = <?= $revenue_json ?>;
    const work_time = <?= $work_time_json ?>;
    const customers_num = <?= $customers_num_json ?>;

    const budget_rate = <?= $budget_rate_json ?>;
    const work_time_rate = <?= $work_time_rate ?>;
    const last_revenue_rate = <?= $last_revenue_rate_json ?>;
    const lunch_revenue = <?= $lunch_json ?>;
    const dinner_revenue = <?= $dinner_json ?>;


    const work_data = {
        labels: date,
        datasets: [
            {
                type: 'line',
                label: '人時売上高',
                borderColor: '#527fd9',
                borderWidth: 2,
                fill: false,
                lineTension: 0,
                data: work_time_rate,
                yAxisID: "y-axis-1",
                datalabels: {
                    color: '#527fd9',
                    formatter: function (value, context) {
                        return value.toLocaleString('ja') + ' 円';
                    },
                }
            },
            {
                type: 'bar',
                label: '労働時間',
                backgroundColor: '#d95252',
                data: work_time,
                yAxisID: "y-axis-2",
                datalabels: {
                    color: '#d95252',
                    formatter: function (value, context) {
                        return value + ' 時間';
                    },
                },
            },
        ],
    };


    const work_option = {
        responsive: true,
        title: {
            display: true,
            text: '<?= $name ?> <?= $display_date ?>'
        },
        tooltips: {
            mode: 'index',
            intersect: true
        },
        plugins: {
            datalabels: {
                anchor: 'end', // データラベルの位置（'end' は上端）
                align: 'end', // データラベルの位置（'end' は上側）
                padding: {
                    bottom: 30
                },
                font: {
                    size: 14
                }
            }
        },
        scales: {
            yAxes: [
                {
                    id: "y-axis-1",   // Y軸のID
                    type: "linear",
                    position: "right",
                },
                {
                    id: "y-axis-2",
                    type: "linear",
                    position: "left",
                },
            ],
        },
    };


    const revenue_data = {
        labels: date,
        datasets: [
            {
                type: 'line',
                label: '予算比',
                borderColor: '#527fd9',
                borderWidth: 2,
                fill: false,
                lineTension: 0,
                data: budget_rate,
                yAxisID: "y-axis-1",
                datalabels: {
                    color: '#527fd9',
                    formatter: function (value, context) {
                        return value.toLocaleString('ja') + ' %';
                    },
                }
            },
            {
                label: 'ランチ',
                borderColor: '#527fd9',
                borderWidth: 2,
                fill: false,
                lineTension: 0,
                data: lunch_revenue,
                yAxisID: "y-axis-2",
                datalabels: {
                    color: '#527fd9',
                    formatter: function (value, context) {
                        return value.toLocaleString('ja') + ' 円';
                    },
                }
            },
            {
                label: 'ディナー',
                backgroundColor: '#d95252',
                data: dinner_revenue,
                yAxisID: "y-axis-2",
                datalabels: {
                    color: '#d95252',
                    formatter: function (value, context) {
                        return value.toLocaleString('ja') + ' 円';
                    },
                },
            },
        ],
    };

    const revenue_option = {
        responsive: true,
        title: {
            display: true,
            text: '<?= $name ?> <?= $display_date ?>'
        },
        tooltips: {
            mode: 'index',
            intersect: true
        },
        plugins: {
            datalabels: {
                anchor: 'end', // データラベルの位置（'end' は上端）
                align: 'end', // データラベルの位置（'end' は上側）
                padding: {
                    bottom: 30
                },
                font: {
                    size: 14
                }
            }
        },
        scales: {
            xAxes: [
                {
                    stacked: true,
                }
            ],
            yAxes: [
                {
                    id: "y-axis-1",   // Y軸のID
                    type: "linear",
                    position: "right",
                },
                {
                    id: "y-axis-2",
                    type: "linear",
                    position: "left",
                    stacked: true
                },
            ],
        },

    };


    const fl_data = {
        labels: date,
        datasets: [
            {
                type: 'line',
                label: '予算比',
                borderColor: '#527fd9',
                borderWidth: 2,
                fill: false,
                lineTension: 0,
                data: budget_rate,
                yAxisID: "y-axis-1",
                datalabels: {
                    color: '#527fd9',
                    formatter: function (value, context) {
                        return value.toLocaleString('ja') + ' %';
                    },
                }
            },
            {
                label: '売上',
                borderColor: '#527fd9',
                borderWidth: 2,
                fill: false,
                lineTension: 0,
                data: revenue,
                yAxisID: "y-axis-2",
                datalabels: {
                    color: '#527fd9',
                    formatter: function (value, context) {
                        return value.toLocaleString('ja') + ' 円';
                    },
                }
            },
            {
                label: '売上',
                backgroundColor: '#d95252',
                data: revenue,
                yAxisID: "y-axis-2",
                datalabels: {
                    color: '#d95252',
                    formatter: function (value, context) {
                        return value.toLocaleString('ja') + ' 円';
                    },
                },
            },
        ],
    };

    const fl_option = {
        responsive: true,
        title: {
            display: true,
            text: '<?= $name ?> <?= $display_date ?>'
        },
        tooltips: {
            mode: 'index',
            intersect: true
        },
        plugins: {
            datalabels: {
                anchor: 'end', // データラベルの位置（'end' は上端）
                align: 'end', // データラベルの位置（'end' は上側）
                padding: {
                    bottom: 30
                },
                font: {
                    size: 14
                }
            }
        },
        scales: {
            xAxes: [
                {
                    stacked: true,
                }
            ],
            yAxes: [
                {
                    id: "y-axis-1",   // Y軸のID
                    type: "linear",
                    position: "right",
                },
                {
                    id: "y-axis-2",
                    type: "linear",
                    position: "left",
                    stacked: true
                },
            ],
        },

    };



    var ctx = document.getElementById('chart').getContext('2d');
    document.getElementById('revenue_chart').addEventListener('click', setRevenueChart);
    document.getElementById('fl_chart').addEventListener('click', setFlChart);
    document.getElementById('work_chart').addEventListener('click', setWorkChart);

    chart = new Chart(ctx, {
        type: 'bar',
        data: work_data,
        options: work_option
    });
    window.onload = function () {
        window.myMixedChart = chart;
    };

    function setRevenueChart() {
        chart.destroy();
        chart = new Chart(ctx, {
            type: 'bar',
            data: revenue_data,
            options: revenue_option
        });
    }

    function setFlChart() {
        chart.destroy();
        chart = new Chart(ctx, {
            type: 'bar',
            data: fl_data,
            options: fl_option
        });
    }

    function setWorkChart() {
        chart.destroy();
        chart = new Chart(ctx, {
            type: 'bar',
            data: work_data,
            options: work_option
        });
    }

</script>

<? include "../parts/footer.php"; ?>