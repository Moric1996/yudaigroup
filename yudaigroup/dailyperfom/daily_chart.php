<?php

include "../AdminLTE/class/ClassLoader.php";


$DBIO = new DBIO();

$st_date = '2020-11-01';
$ed_date = '2020-11-30';
$shop_type = 1;
$data = $DBIO->fetchDataTargetPeriod($st_date, $ed_date, $shop_type);
$res = array();
foreach ($data as $val) {
    $res[$val['shop_id']]['name'] = $val['name'];
    $res[$val['shop_id']]['budget'] += $val['budget'];
    $res[$val['shop_id']]['revenue'] += $val['revenue'];
}

$chart = array();
foreach ($res as $val) {
    $chart['labels'][] = $val['name'];
    $chart['budget'][] = $val['budget'];
    $chart['revenue'][] = $val['revenue'];
    $chart['budget_rate'][] = ($val['budget']) ? round($val['revenue']/$val['budget']*100,1) : 0;
}

$shop_chart = new ShopChart();
$res = $shop_chart->makeAllShopRevenueChart($chart);

?>
<meta charset="utf-8">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" integrity="sha512-d9xgZrVZpmmQlfonhQUvTR7lMPtO7NkZMkA0ABN3PHCbKA5nqylQ/yWlFAyY6hYgdF1Qh6nYiuADWwKB4C2WSw==" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>
<?= $res ?>
