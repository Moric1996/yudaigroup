<?php

include "../AdminLTE/class/ClassLoader.php";
include "../csvconvert/add_shop_list.php";

$default = new DateTime('-1 month');


$shop_id = ($_GET['shop_id']) ? $_GET['shop_id'] : "3005";
$target_date = ($_GET['target_date']) ? $_GET['target_date'] : $default->format('Y-m');


$dbio = new DBIO();


foreach ((array)$add_shop_list as $key => $one) {
    if ($shop_id == $one['id']) {
        $name = $one['name'];
        $shop_list[] = array('id' => $one['id'], 'name' => $one['name'], 'selected' => 'selected');
    } else {
        $shop_list[] = array('id' => $one['id'], 'name' => $one['name']);
    }
}


$date = new DateTime($target_date);
$this_date = $date->format('Ymd');
$display_date = $date->format('Y年n月');


$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');


$ce = new CsvExport($conn);
// 予算
$plan_data = $dbio->fetchShopBudgetMonthPlan($shop_id, $this_date);
$ce->setExportDataSub('plan', $plan_data);

// 売上見込み 昨日までのデータとする
$today = new DateTime();
$today->modify('-1 day');
$end_date = ($date->format('Y-m') == $today->format('Y-m')) ? $today->format('Y-m-d') : $date->format('Y-m-t');
$revenue = $dbio->fetchDataTargetPeriodSum($date->format('Y-m-01'), $end_date, $shop_id);

$total_revenue = $dbio->fetchDataTargetPeriodSum($date->format('Y-m-01'), $date->format('Y-m-t'), $shop_id);


// その日までのデータを出したいらしいので何日進んでいるかを計算する
$daily_rate = $date->format('Y-m') == $today->format('Y-m')
    ? $today->format('d') / $date->format('t') : 1;

$predict_data = array();
// 予測
foreach ($plan_data as $val) {

    $code = $val['code'];
    $value = $val['value'];
    $type = $val['type'];

    if (in_array($code, array(5111, 5311))) {
        $value = 0;
    }
    if ($code == 4111) {
        $predict_data[] = array('code' => $code, 'value' => $revenue['budget'], 'type' => $type);
        continue;
    }

    $predict_data[] = array('code' => $code, 'value' => round($value * $daily_rate), 'type' => $type);
}
$ce->setExportDataSub('predict', $predict_data);

// 何割予算達成できてるか
$achievement_rate = ($revenue['budget']) ? round($revenue['revenue'] / $revenue['budget'] * 100, 1) : 0;


// 1日辺りの労働時間
$work_time = round($revenue['work_time'], 1);

// 時給
$hourly_wage = $dbio->fetchStandardUnitPrice($shop_id, $date->format('Y-m-01'));

//// 期首在庫
//$inventory = $dbio->fetchInfomartInventory($date->format('Y-m-01'), $shop_id);
//
//// 期末在庫
//$date2 = new DateTime($target_date);
//$date2->modify('+1 month');
//$inventory_next_month = $dbio->fetchInfomartInventory($date->format('Y-m-01'), $shop_id);


// 実績 ここで色々いじくりまわす
$actual = array();
// 売上を見込みにする
$sales_forecast = $revenue['revenue'];
$actual[] = array('code' => 4111, 'value' => $sales_forecast, 'type' => 1);
// 給与と雑給
$actual[] = array('code' => 6111, 'value' => round($work_time * $hourly_wage), 'type' => 3);
// 雑給は空
$actual[] = array('code' => 6311, 'value' => 0, 'type' => 3);

foreach ($predict_data as $val) {
    $code = $val['code'];
    $value = $val['value'];
    $type = $val['type'];

    if (in_array($code, array(4111, 6111, 6311))) {
        continue;
    }


    $actual[] = array('code' => $code, 'value' => $value, 'type' => $type);
}




$ce->setExportDataSub('actual', $actual);


$result_temp = $ce->getExportData();
$ce->resetSum('actual');

foreach ($actual as $key => $val) {
    if ($val['code'] == 6113) {
        $actual[$key] = array('code' => 6113, 'value' => round(
            round(($result_temp[10001]['predict']) ? $result_temp[6113]['predict'] / $result_temp[10001]['predict'] : 0, 3)
            * $result_temp[10001]['actual']
        ), 'type' => 3);
    }
    if ($val['code'] == 6114) {
        $actual[$key] = array('code' => 6114, 'value' => round(
            round(($result_temp[10001]['predict']) ? $result_temp[6114]['predict'] / $result_temp[10001]['predict'] : 0, 3)
            * $result_temp[10001]['actual']
        ), 'type' => 3);
    }
    if ($val['code'] == 6219) {
        $actual[$key] = array('code' => 6219, 'value' => round(
            round(($result_temp[10001]['predict']) ? $result_temp[6219]['predict'] / $result_temp[10001]['predict'] : 0, 3)
            * $result_temp[10001]['actual']
        ), 'type' => 3);
    }
}

$ce->setExportDataSub('actual', $actual);


$result = $ce->getExportData();


// 資料が固定だったので表示するのは固定で
include "../csvconvert/display_column_list.php";
$display_array = get_display_array(3005);




$shiire_rate = ($result[10001]['plan']) ? round($result[5211]['plan'] / $result[10001]['plan'], 3) : 0;
$result[5211]['actual'] = round($shiire_rate * $revenue['revenue']);
$result[5211]['predict'] = round($shiire_rate * $result[10001]['predict']);


$genka_rate = ($result[10001]['plan']) ? round($result[10002]['plan'] / $result[10001]['plan'], 3) : 0;
$result[10002]['actual'] = $result[5111]['actual'] + $result[5211]['actual'] - $result[5311]['actual'];
$result[10002]['predict'] = $result[5111]['predict'] + $result[5211]['predict'] - $result[5311]['predict'];

$result[10003]['actual'] = $result[10001]['actual'] - $result[10002]['actual'];
$result[10005]['actual'] = $result[10003]['actual'] - $result[10004]['actual'];
$result[10003]['predict'] = $result[10001]['predict'] - $result[10002]['predict'];
$result[10005]['predict'] = $result[10003]['predict'] - $result[10004]['predict'];

// 当月着地売上
$revenue_mikomi = ($result[10001]['predict'])
    ? round(round($result[10001]['actual'] / $result[10001]['predict'], 3) * $result[10001]['plan'])
    : 0;

// 着地見込み営業利益の予算
$budget_mikomi = ($date->format('Y-m') == $today->format('Y-m'))
    ? round($result[10005]['actual'] / $today->format('d') * $date->format('t'))
    : $result[10005]['actual'];

$mikomi = $budget_mikomi - $result[10005]['plan'];
$mikomi_rate = ($result[10005]['plan']) ? round($mikomi / $result[10005]['plan'] * 100, 1) : 0;

function getMonthRange2($startUnixTime)
{
    /*    $start = (new DateTime)->setTimestamp($startUnixTime);
        $end   = (new DateTime)->setTimestamp($endUnixTime ? $endUnixTime : time());
        $next_month = new DateInterval('P1M');*/
    $start = new DateTime($startUnixTime);
    $end = new DateTime();
    $end->modify('+13 months');

    $ymList = array();
    while ($start < $end) {
        $ymList[] = array('value' => $start->format('Y-m'), 'text' => $start->format('Y年m月'));
        $start->modify('+1 month');
    }

    return $ymList;
}

$year_month_option = array_reverse(getMonthRange2('20210101'));


?>
<!doctype html>
<html>

<head>

    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>予算資料</title>

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
        * {
            box-sizing: border-box;
        }

        html {
            margin: 10px;
        }

        th,
        td {
            padding: 2px;
        }

        th {
            text-align: center;
        }

        td {
            text-align: right;
        }

        tbody th {
            text-align: left;
        }


        table thead tr th:nth-child(1),
        table tbody tr th:nth-child(1) {
            border-left: 3px solid black;
        }

        table thead tr th:nth-child(5),
        table tbody tr td:nth-child(5),
        table thead tr th:nth-child(7),
        table tbody tr td:nth-child(7),
        table thead tr th:nth-child(9),
        table tbody tr td:nth-child(9) {
            border-right: 3px solid black;
        }


        table thead tr:first-child th {
            border-top: 3px solid black;
        }

        table tbody tr:last-child td,
        table tbody tr:last-child th {
            border-bottom: 3px solid black;
        }

        .head th {
            /* 縦スクロール時に固定する */
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            background: #fff;
            /* tbody内のセルより手前に表示する */
            z-index: 1;
        }

    </style>

</head>

<body>

<div class="col-xs-10">
    <h3>予算資料</h3>

    <div class="form-row">

        <form method="get" action="./index.php" class="form-inline">

            <select name="target_date" class="form-control">
                <?php foreach ((array)$year_month_option as $val): ?>
                    <option value="<?= $val['value'] ?>"
                        <?= ($val['value'] == $target_date) ? 'selected' : "" ?>><?= $val['text'] ?></option>
                <?php endforeach; ?>
            </select>

            <select name="shop_id" class="form-control">
                <?php foreach ((array)$shop_list as $val): ?>
                    <option value="<?= $val['id'] ?>" <?= $val['selected'] ?>><?= $val['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <button class="btn btn-primary" type="submit">更新</button>
            <a href="create.php?shop_id=<?= $shop_id ?>" class="btn btn-info">
                <div>CSV取込</div>
            </a>

            <a href="input.php?shop_id=<?= $shop_id ?>" class="btn btn-info">
                <div>標準単価入力</div>
            </a>


            <a href="../portal/index.php" class="btn btn-success">
                <div>戻る</div>
            </a>
        </form>

    </div>
    <div>給与手当は労働時間×標準単価です。設定されている標準単価: <?= $hourly_wage ?>円</div>
    <h3><?= $name ?></h3>
    <table border="1">
        <thead class="head">
        <tr>
            <th>勘定科目名</th>

            <th><?= $display_date ?>予算<br>月次金額</th>
            <th>売上<br>対比</th>

            <th><?= $display_date ?>予算<br>日次累計額</th>
            <th>売上<br>対比</th>


            <th><?= $display_date ?>見込<br>日次累計額</th>
            <th>売上<br>対比</th>


            <th>目標見込差異</th>
            <th>達成率<br>対比</th>
            <th>当月着地見込売上高</th>
        </tr>
        </thead>
        <? foreach ((array)$display_array as $one_code => $name): ?>
            <?
            $tr_color = '';
            if ($one_code === 10001) {
                $result[10001]['name'] = '純売上高';
                $tr_color = 'style="background-color:#bde7ef;"';
            }
            if ($one_code === 10002) {
                $result[10002]['name'] = '当期売上原価';
            }
            if ($one_code === 10003) {
                $result[10003]['name'] = '売上総利益';
                $tr_color = 'style="background-color:#efbdca;"';
            }
            if ($one_code === 10004) {
                $result[10004]['name'] = '販売費及び一般管理費計';
            }
            if ($one_code === 10005) {
                $result[10005]['name'] = '営業利益(損失)';
                $tr_color = 'style="background-color:#efbdca;"';
            }
            $rowspan = '';
            if ($one_code === 6111) {
                $rowspan = 'rowspan="2"';
            }

            ?>
            <tr <?= $tr_color ?>>
                <th style="text-align: left"><?= $name ?></th>

                <td class="comma"><?= $result[$one_code]['plan'] ?></td>
                <td><?= ($result[10001]['plan']) ? round($result[$one_code]['plan'] / $result[10001]['plan'] * 100, 1) : 0 ?>
                    %
                </td>

                <td class="comma"><?= $result[$one_code]['predict'] ?></td>
                <td><?= ($result[10001]['predict']) ? round($result[$one_code]['predict'] / $result[10001]['predict'] * 100, 1) : 0 ?>
                    %
                </td>

                <? if ($one_code !== 6311): ?>
                    <td <?= $rowspan ?> class="comma"><?= $result[$one_code]['actual'] ?></td>
                    <td <?= $rowspan ?>><?= ($result[10001]['actual']) ? round($result[$one_code]['actual'] / $result[10001]['actual'] * 100, 1) : 0 ?>
                        %
                    </td>

                    <? if ($one_code === 6111): ?>
                        <td <?= $rowspan ?>
                                class="comma"><?= $result[$one_code]['actual'] - ($result[6111]['predict'] + $result[6311]['predict']) ?></td>
                        <td <?= $rowspan ?>><?= ($result[6111]['predict'] + $result[6311]['predict'])
                                ? round($result[$one_code]['actual'] / ($result[6111]['predict'] + $result[6311]['predict']) * 100, 1) : 0 ?>
                            %
                        </td>
                    <? else: ?>
                        <td class="comma"><?= $result[$one_code]['actual'] - $result[$one_code]['predict'] ?></td>
                        <td><?= ($result[$one_code]['predict']) ? round($result[$one_code]['actual'] / $result[$one_code]['predict'] * 100, 1) : 0 ?>
                            %
                        </td>
                    <? endif ?>
                <? endif ?>

                <? if ($one_code === 10001): ?>
                    <td class="comma"><?= $revenue_mikomi ?></td>
                <? endif ?>

            </tr>
        <? endforeach; ?>
        <tr>
            <th style="text-align: right">着地見込営業利益</th>
            <td></td>
            <td></td>
            <td></td>
            <td>【営業利益】月次着地見込</td>
            <td class="comma"><?= $budget_mikomi ?></td>
            <td>【営業利益】着地見込－目標差</td>
            <td class="comma"><?= $mikomi ?></td>
            <td><?= $mikomi_rate ?>%</td>
        </tr>
    </table>
</div>
<script
        src="https://code.jquery.com/jquery-1.12.4.min.js"
        integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
        crossorigin="anonymous"></script>
<script>
    $(function () {

        $(".comma").each(function (i, o) {
            var num = Number($(o).text());
            $(o).text(num.toLocaleString('ja'));
        });

    });
</script>
</body>
</html>