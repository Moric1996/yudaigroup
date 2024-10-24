<?php

include "../AdminLTE/class/ClassLoader.php";

$default = new DateTime('-1 month');

$target_date = ($_GET['target_date']) ? $_GET['target_date'] : $default->format('Y-m');

$st_default = new DateTime($target_date);
$st_default->modify('-9 month');

$start_date = ($_GET['start_date']) ? $_GET['start_date'] : $st_default->format('Y-10');
$range_flag = ($_GET['range_flag']) ? $_GET['range_flag'] : 0;

$dbio = new DBIO();

$st_date = new DateTime($start_date);
$target_st_date = $st_date->format('Ymd');

$date = new DateTime($target_date);
$this_date = $date->format('Ymd');
$display_date = $date->format('Y年n月');


$st_date->modify('-1 year');
$st_last_year = $st_date->format('Ymd');
$date->modify('-1 year');
$last_year = $date->format('Ymd');
$display_last_year = $date->format('Y年n月');


$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');


$ce = new CsvExport($conn);


// 飲食店
$shop_all = $dbio->fetchShopList();
foreach ((array)$shop_all as $key => $one) {
    $food_shop_list .= "'".$one['id']."',";
}
$food_shop_list = trim($food_shop_list, ',');


$shop_id_list = array(
    'yudai_group'=>'3014',
    'food'=>'2003',
    'carrier'=>'2000',
//    'sales'=>'2001',
    'head_office'=>'2002',

    'nekkan_golf'=>'3001',
    'shimizu_golf'=>'3002',
    'yournet' =>'3003',
    'granjer'=>'3004',
    'all' =>'3005',
    'yudai_golf' =>'3006',
    'hoiku' =>'3012',
    'onsen' =>'3010',
    'ucom' =>'3013',
);


foreach ($shop_id_list as $key => $shop_list_id_string) {
    if ($range_flag) {
        if ($key == 'granjer') {
            $granjer_date = new DateTime($target_date);
            $granjer_date->modify('-5 month');
            $granjer_date_5 = $granjer_date->format('Y-06');


            $granjer_datetime = new DateTime($granjer_date_5);
            $granjer_st_date = $granjer_datetime->format('Ymd');

            $granjer_datetime->modify('-1 year');
            $granjer_st_date_last_year = $granjer_datetime->format('Ymd');

            $ce->setExportAll('target', $key, $dbio->fetchGroupRevenueAll($shop_list_id_string, $granjer_st_date, $this_date));
            $ce->setExportAll('last_year', $key, $dbio->fetchGroupRevenueAll($shop_list_id_string, $granjer_st_date_last_year, $last_year));
        } else {
            $ce->setExportAll('target', $key, $dbio->fetchGroupRevenueAll($shop_list_id_string, $target_st_date, $this_date));
            $ce->setExportAll('last_year', $key, $dbio->fetchGroupRevenueAll($shop_list_id_string, $st_last_year, $last_year));
        }

    } else {
        $ce->setExportAll('target', $key, $dbio->fetchGroupRevenueMonth($shop_list_id_string, $this_date));
        $ce->setExportAll('last_year', $key, $dbio->fetchGroupRevenueMonth($shop_list_id_string, $last_year));
    }
}

$main_order = array('yudai_group', 'food','carrier', /*'sales',*/'head_office', 'all');
$sub_order = array('shimizu_golf', 'nekkan_golf', 'yournet','granjer', 'hoiku', 'onsen', 'ucom');

// 時期によって合計を変更する用
$old_total_comparison_date = new DateTime('2023-09-01');
$this_year_main_shop_st_diff = new DateTime($start_date);
$this_year_main_shop_id = ($this_year_main_shop_st_diff <= $old_total_comparison_date)
    ? array('food', 'carrier', 'head_office')
    : array('yudai_group', 'all');

$last_year_main_shop_st_diff = new DateTime($st_last_year);
$last_year_main_shop_id = ($last_year_main_shop_st_diff <= $old_total_comparison_date)
    ? array('food', 'carrier', 'head_office')
    : array('yudai_group', 'all');

$result = $ce->getExportData();

$display_array = array(
    10001=>'純売上高',
    10002=>'売上原価',
    10003=>'売上総利益',
    10004=>'販管費',
    10005=>'営業損益',
    10006 => "営業外収益",
    10007 => "営業外費用",
    10008 => "経常損益"
);



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
            white-space: nowrap
        }

        td {
            text-align: right;
        }

        tbody th {
            text-align: left;
        }



        table thead:first-child tr:first-child {
            border-top: 3px solid red;
            border-left: 3px solid red;
            border-right: 3px solid red;
        }

        table tbody:nth-child(2) tr:nth-child(8) {
            border-bottom: 3px solid red;
        }
        table tbody:nth-child(2) tr:nth-child(1),
        table tbody:nth-child(2) tr:nth-child(2),
        table tbody:nth-child(2) tr:nth-child(3),
        table tbody:nth-child(2) tr:nth-child(4),
        table tbody:nth-child(2) tr:nth-child(5),
        table tbody:nth-child(2) tr:nth-child(6),
        table tbody:nth-child(2) tr:nth-child(7),
        table tbody:nth-child(2) tr:nth-child(8) {
            border-left: 3px solid red;
            border-right: 3px solid red;
        }

        table tbody:last-child tr:last-child {
            border: 3px solid red;
        }

        /* 黒太線縦 */
        td:nth-child(1), td:nth-child(2),
        th:nth-child(1), th:nth-child(2),
        td:nth-child(5), td:nth-child(6),
        th:nth-child(5), th:nth-child(6),
        td:nth-child(13), td:nth-child(14),
        th:nth-child(13), th:nth-child(14){
            border-right: 3px solid black;
        }
        /* 黒太線横上下 */
        table thead:first-child th:nth-child(2),
        table thead:first-child th:nth-child(6),
        table thead:first-child th:nth-child(14){
            border-top: 3px solid black;
        }
        table tbody:last-child tr:last-child td:nth-child(2),
        table tbody:last-child tr:last-child td:nth-child(6),
        table tbody:last-child tr:last-child td:nth-child(14){
            border-bottom: 3px solid black;
        }
        thead {
            white-space: nowrap;
        }
    </style>

</head>

<body style="overflow: scroll;">

<div>
    <h3>経営会議資料 グループ全社</h3>


    <div class="form-row">

        <form method="get" action="./all_shop_data.php" class="form-inline">
            <input type="hidden" name="target_date" value="<?= $target_date ?>">
            <input type="hidden" name="range_flag" value="<?= $range_flag ?>">
            対象月:
            <select name="target_date" class="form-control">
                <?php foreach ((array)$year_month_option as $val): ?>
                    <option value="<?= $val['value'] ?>"
                        <?= ($val['value'] == $target_date) ? 'selected' : "" ?>><?= $val['text'] ?></option>
                <?php endforeach; ?>
            </select>

            <button class="btn btn-primary" type="submit">更新</button>
            <a href="csv_form.php?shop_id=" <?= $shop_id ?> class="btn btn-info">
                <div>入力</div>
            </a>

            <? if (!$range_flag): ?>
                <a href="./all_shop_data.php?range_flag=1" class="btn btn-warning">
                    <div>累計</div>
                </a>
            <? else : ?>
                <a href="./all_shop_data.php" class="btn btn-warning">
                    <div>月実績</div>
                </a>
            <? endif; ?>

            <a href="./csv_display.php" class="btn btn-danger">
                <div>店舗毎</div>
            </a>

            <a href="../portal/index.php" class="btn btn-success">
                <div>戻る</div>
            </a>

        </form>

    </div>

    <table border="1">
        <thead  style="background: #65f1e7;">
        <tr>
            <th><?= $display_date ?></th>

            <th>雄大グループ㈱</th>
            <th>飲食事業</th>
            <th>通信事業</th>
<!--            <th>営業部</th>-->
            <th>本社</th>
            <th>雄大（株）</th>

            <th>清水町GC</th>
            <th>熱函GC</th>
            <th>ユアネット</th>
            <th>グランジャー</th>
            <th>保育事業合算</th>
            <th>ゆうだい温泉</th>
            <th>ユーコム</th>
            <th>子会社計</th>

            <th>グループ計</th>
        </tr>
        </thead>
        <? foreach ((array)$display_array as $one_code => $name): ?>
            <tr>
                <th style="text-align: left"><?= $name ?></th>
                <? $main_sum = $sub_sum = 0; ?>
                <? foreach ((array)$main_order as $shop_id_code): ?>
                    <?
                        // 雄大の合計値は時期によって変更する必要があるため
                        if (in_array($shop_id_code, $this_year_main_shop_id, true)) {
                            $main_sum += $result[$one_code]['target'][$shop_id_code];
                        }
                    ?>
                    <td class="comma"><?= $result[$one_code]['target'][$shop_id_code] ?></td>
                <? endforeach; ?>

                <? foreach ((array)$sub_order as $shop_id_code): ?>
                    <? $sub_sum += $result[$one_code]['target'][$shop_id_code]; ?>
                    <td class="comma"><?= $result[$one_code]['target'][$shop_id_code] ?></td>
                <? endforeach; ?>

                <td class="comma"><?= $sub_sum ?></td>

                <td class="comma"><?= $main_sum + $sub_sum ?></td>
            </tr>
        <? endforeach; ?>


        <thead  style="background: #c6f15f;">
        <tr>
            <th><?= $display_last_year ?></th>

            <th>雄大グループ㈱</th>
            <th>飲食事業</th>
            <th>通信事業</th>
<!--            <th>営業部</th>-->
            <th>本社</th>
            <th>雄大（株）</th>

            <th>清水町GC</th>
            <th>熱函GC</th>
            <th>ユアネット</th>
            <th>グランジャー</th>
            <th>保育事業合算</th>
            <th>ゆうだい温泉</th>
            <th>ユーコム</th>
            <th>子会社計</th>

            <th>グループ計</th>
        </tr>
        </thead>
        <? foreach ((array)$display_array as $one_code => $name): ?>
            <tr>
                <th style="text-align: left"><?= $name ?></th>
                <? $main_sum = $sub_sum = 0; ?>
                <? foreach ((array)$main_order as $shop_id_code): ?>
                    <?
                    // 雄大の合計値は時期によって変更する必要があるため
                    if (in_array($shop_id_code, $last_year_main_shop_id, true)) {
                        $main_sum += $result[$one_code]['last_year'][$shop_id_code];
                    }
                    ?>
                    <td class="comma"><?= $result[$one_code]['last_year'][$shop_id_code] ?></td>
                <? endforeach; ?>

                <? foreach ((array)$sub_order as $shop_id_code): ?>
                    <? $sub_sum += $result[$one_code]['last_year'][$shop_id_code]; ?>
                    <td class="comma"><?= $result[$one_code]['last_year'][$shop_id_code] ?></td>
                <? endforeach; ?>

                <td class="comma"><?= $sub_sum ?></td>

                <td class="comma"><?= $main_sum + $sub_sum ?></td>
            </tr>
        <? endforeach; ?>


        <thead  style="background: #f1ae76;">
        <tr>
            <th>前年差額</th>

            <th>雄大グループ㈱</th>
            <th>飲食事業</th>
            <th>通信事業</th>
<!--            <th>営業部</th>-->
            <th>本社</th>
            <th>雄大（株）</th>

            <th>清水町GC</th>
            <th>熱函GC</th>
            <th>ユアネット</th>
            <th>グランジャー</th>
            <th>保育事業合算</th>
            <th>ゆうだい温泉</th>
            <th>ユーコム</th>
            <th>子会社計</th>

            <th>グループ計</th>
        </tr>
        </thead>
        <? foreach ((array)$display_array as $one_code => $name): ?>
            <tr>
                <th style="text-align: left"><?= $name ?></th>
                <?
                $main_sum = $sub_sum = 0;
                ?>
                <? foreach ((array)$main_order as $shop_id_code): ?>

                    <?
                        // 雄大の合計値は時期によって変更する必要があるため
                        if (in_array($shop_id_code, $this_year_main_shop_id, true)) {
                            $main_sum += $result[$one_code]['target'][$shop_id_code];
                        }
                        if (in_array($shop_id_code, $last_year_main_shop_id, true)) {
                            $main_sum -= $result[$one_code]['last_year'][$shop_id_code];
                        }
                    ?>
                    <td class="comma"><?= $result[$one_code]['target'][$shop_id_code] - $result[$one_code]['last_year'][$shop_id_code] ?></td>
                <? endforeach; ?>

                <? foreach ((array)$sub_order as $shop_id_code): ?>
                    <? $sub_sum += $result[$one_code]['target'][$shop_id_code] - $result[$one_code]['last_year'][$shop_id_code]; ?>
                    <td class="comma"><?= $result[$one_code]['target'][$shop_id_code] - $result[$one_code]['last_year'][$shop_id_code] ?></td>
                <? endforeach; ?>

                <td class="comma"><?= $sub_sum ?></td>

                <td class="comma"><?= $main_sum + $sub_sum ?></td>
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