<?php

include "../AdminLTE/class/ClassLoader.php";


$DBIO = new DBIO();

$target_date = new DateTime('2020-11-01');
$shop_id = 101;

$st_date = $target_date->format('Y-m-01');
$ed_date = $target_date->format('Y-m-t');


$data = $DBIO->fetchDataTargetShop($st_date, $ed_date, $shop_id);

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
    $temp_date = new DateTime($val['date']);
    $chart['last_revenue_rate'][] = ($temp_date->format('d')) ? round(($val['revenue'] / $last_res[$temp_date->format('d')] * 100), 1) : 0;
    $chart['labels'][] = $temp_date->format('n/j') . $week[$temp_date->format('w')];
    $chart['budget'][] = $val['budget'];
    $chart['revenue'][] = $val['revenue'];
    $chart['work_time'][] = $val['work_time'];
    $chart['customers_num'][] = $val['customers_num'];
    $chart['budget_rate'][] = ($val['budget']) ? round($val['revenue'] / $val['budget'] * 100, 1) : 0;
    $chart['work_time_rate'][] = ($val['work_time']) ? round($val['revenue'] / $val['work_time'] * 100, 1) : 0;
}


$labels_json = json_encode($chart['labels']);

$revenue_json = json_encode($chart['revenue']);

$budget_json = json_encode($chart['budget']);

$work_time_json = json_encode($chart['work_time']);

$customers_num_json = json_encode($chart['customers_num']);

$budget_rate_json = json_encode($chart['budget_rate']);

$work_time_rate_json = json_encode($chart['work_time_rate']);

$last_revenue_rate_json = json_encode($chart['last_revenue_rate']);

?>
<meta charset="utf-8">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"
        integrity="sha512-d9xgZrVZpmmQlfonhQUvTR7lMPtO7NkZMkA0ABN3PHCbKA5nqylQ/yWlFAyY6hYgdF1Qh6nYiuADWwKB4C2WSw=="
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>


<canvas id="chart"></canvas>
<script>
    const date = <?= $labels_json ?>;

    const budget = <?= $budget_json ?>;
    const revenue = <?= $revenue_json ?>;
    const work_time = <?= $work_time_json ?>;
    const customers_num = <?= $customers_num_json ?>;

    const budget_rate = <?= $budget_rate_json ?>;
    const last_revenue_rate = <?= $last_revenue_rate_json ?>;


    let label = date;
    let line1 = budget_rate;
    let line2 = last_revenue_rate;
    let bar1 = budget;
    let bar2 = revenue;
    let bar3 = customers_num;

    var chartData = {
        labels: label,
        datasets: [{
            type: 'line',
            label: '達成率',
            borderColor: 'blue',
            borderWidth: 2,
            fill: false,
            lineTension: 0,
            data: line1,
            yAxisID: "y-axis-1",
            datalabels: {
                color: 'blue',
                formatter: function (value, context) {
                    return value + '%';
                },
            }
        },
            {
                type: 'line',
                label: '売上前年比',
                borderColor: 'red',
                borderWidth: 2,
                fill: false,
                lineTension: 0,
                data: line2,
                yAxisID: "y-axis-1",
                datalabels: {
                    color: 'red',
                    formatter: function (value, context) {
                        return value + '%';
                    },
                }
            },
            // {
            //     type: 'bar',
            //     label: '予算',
            //     backgroundColor: 'red',
            //     data: bar1,
            //     yAxisID: "y-axis-2",
            //     datalabels: {
            //         color: 'red',
            //         formatter: function (value, context) {
            //             return value.toLocaleString('ja') + '円';
            //         },
            //     },
            // },
            {
                type: 'bar',
                label: '売上',
                backgroundColor: 'green',
                data: bar2,
                yAxisID: "y-axis-2",
                datalabels: {
                    color: 'green',
                    formatter: function (value, context) {
                        return value.toLocaleString('ja') + '円';
                    },
                },
            },
            // {
            //     type: 'bar',
            //     label: '客数',
            //     backgroundColor: 'orange',
            //     data: bar3,
            //     yAxisID: "y-axis-3",
            //     datalabels: {
            //         color: 'orange',
            //         formatter: function (value, context) {
            //             return value.toLocaleString('ja') + '人';
            //         },
            //     },
            // }
        ]

    };
    window.onload = function () {
        var ctx = document.getElementById('chart').getContext('2d');
        window.myMixedChart = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: '沼津甲羅11月一覧'
                },
                tooltips: {
                    mode: 'index',
                    intersect: true
                },
                scales: {
                    yAxes: [{
                        id: "y-axis-1",   // Y軸のID
                        ticks: {
                            display: false
                        }
                    }, {
                        id: "y-axis-2",
                        type: "linear",
                        position: "left",
                    },
                    // {
                    //     id: "y-axis-3",
                    //     type: "linear",
                    //     position: "right",
                    // }
                    ],
                },
                plugins: {
                    datalabels: {
                        anchor: 'end', // データラベルの位置（'end' は上端）
                        align: 'end', // データラベルの位置（'end' は上側）
                        padding: {
                            bottom: 40
                        },
                        font: {
                            size: 14
                        }
                    }
                }
            }
        });
    };

</script>