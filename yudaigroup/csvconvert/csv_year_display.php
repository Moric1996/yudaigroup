<?php

include "../AdminLTE/class/ClassLoader.php";
include "add_shop_list.php";

$default = new DateTime('-1 month');

$shop_id = ($_GET['shop_id']) ? $_GET['shop_id'] : "3005";
$target_date = ($_GET['target_date']) ? $_GET['target_date'] : $default->format('Y-m');

$st_default = new DateTime($target_date);
$st_default->modify('-9 month');

$start_date = ($_GET['start_date']) ? $_GET['start_date'] : $st_default->format('Y-10');



$dbio = new DBIO();

foreach ((array)$add_shop_list as $key => $one) {
    if ($shop_id == $one['id']) {
        $name = $one['name'];
        $shop_list[] = array('id'=>$one['id'],'name'=>$one['name'],'selected'=>'selected');
    } else {
        $shop_list[] = array('id'=>$one['id'],'name'=>$one['name']);
    }
}

$st_date = new DateTime($start_date);
$target_st_date = $st_date->format('Ymd');
$date = new DateTime($target_date);
$this_date = $date->format('Ymd');
$display_date = $date->format('Y年n月');


$st_date->modify('-1 year');
$st_last_year = $st_date->format('Ymd');
$date->modify('-1 year');
$last_year = $date->format('Ymd');


$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');


$ce = new CsvExport($conn);




    $ce->setExportDataSub('target', $dbio->fetchShopPerformanceYear($shop_id, $target_st_date, $this_date));

    $ce->setExportDataSub('last_year', $dbio->fetchShopPerformanceYear($shop_id, $st_last_year, $last_year));

$result = $ce->getExportData();


include "display_column_list.php";
$display_array = get_display_array($shop_id);


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

$year_month_option = array_reverse(getMonthRange2('20180101'));



?>
<!doctype html>
<html>

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
            border-right: 3px solid red;
        }
        table thead tr th:nth-child(3),
        table tbody tr td:nth-child(3) {
            border-right: 3px solid red;
        }

        table thead tr:first-child th:nth-child(2),
        table thead tr:first-child th:nth-child(3) {
            border-top: 3px solid red;
        }
        table tbody tr:last-child td:nth-child(2),
        table tbody tr:last-child td:nth-child(3) {
            border-bottom: 3px solid red;
        }

        table tbody tr:last-child td:nth-child(6) {
            border: 3px solid red;
        }
    </style>

</head>

<body>

<div class="col-xs-10">
    <h3>経営会議資料 累計</h3>


    <div class="form-row">

        <form method="get" action="./csv_year_display.php" class="form-inline">


            開始月:
            <select name="start_date" class="form-control">
                <?php foreach ((array)$year_month_option as $val): ?>
                    <option value="<?= $val['value'] ?>"
                        <?= ($val['value'] == $start_date) ? 'selected' : "" ?>><?= $val['text'] ?></option>
                <?php endforeach; ?>
            </select>
            終了月:
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
            <a href="csv_form.php?shop_id=" <?= $shop_id ?> class="btn btn-info">
                <div>入力</div>
            </a>

            <a href="./csv_display.php?shop_id=<?= $shop_id ?>&target_date=<?= $target_date ?>" class="btn btn-warning">
                <div>月実績</div>
            </a>

            <a href="./all_shop_data.php" class="btn btn-danger">
                <div>グループ全社</div>
            </a>

            <a href="../portal/index.php" class="btn btn-success">
                <div>戻る</div>
            </a>

        </form>

    </div>
    <h3><?= $name ?></h3>
    <table border="1">
        <thead>
        <tr>
            <th>勘定科目名</th>

            <th><?= $display_date ?></th>
            <th>売上対比</th>

            <th>昨年</th>
            <th>売上対比</th>

            <th>前年同月差</th>
            <th>差率</th>
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
            ?>
            <tr <?= $tr_color ?>>
                <th style="text-align: left"><?= $name ?></th>

                <td class="comma"><?= $result[$one_code]['target'] ?></td>
                <td><?= ($result[10001]['target']) ? round($result[$one_code]['target'] / $result[10001]['target'] * 100, 1) : 0 ?>
                    %
                </td>

                <td class="comma"><?= $result[$one_code]['last_year'] ?></td>
                <td><?= ($result[10001]['last_year']) ? round($result[$one_code]['last_year'] / $result[10001]['last_year'] * 100, 1) : 0 ?>
                    %
                </td>

                <td class="comma"><?= $result[$one_code]['target'] - $result[$one_code]['last_year'] ?></td>
                <td><?= ($result[$one_code]['last_year']) ? round(($result[$one_code]['target'] - $result[$one_code]['last_year']) / $result[$one_code]['last_year'] * 100, 1) : 0 ?>
                    %
                </td>
            </tr>
        <? endforeach; ?>
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