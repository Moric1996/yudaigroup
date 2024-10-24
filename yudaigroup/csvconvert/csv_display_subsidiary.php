<?php

include "../AdminLTE/class/ClassLoader.php";


$shop_id = ($_GET['shop_id']) ? $_GET['shop_id'] : "3001";
$target_date = ($_GET['target_date']) ? $_GET['target_date'] : '2020-01';


$dbio = new DBIO();

// 子会社
$add_shop_list = array(
    array('id' => '3001', 'name' => '熱函ゴルフ'),
    array('id' => '3002', 'name' => '清水町ゴルフ'),
    array('id' => '3003', 'name' => 'ユアネット'),
    array('id' => '3004', 'name' => 'グランジャー'),
);
foreach ((array)$add_shop_list as $key => $one) {
    $shop_list[$key]['id'] = $one['id'];
    $shop_list[$key]['name'] = $one['name'];
    if ($shop_id == $one['id']) {
        $shop_list[$key]['selected'] = 'selected';
        $name = $one['name'];
    }
}


$date = new DateTime($target_date);
$this_date = $date->format('Ymd');
$display_date = $date->format('Y年n月1日');

$date->modify('-1 month');
$last_month_date = $date->format('Ymd');

$date->modify('-1 month');
$two_months_ago_date = $date->format('Ymd');

$date = new DateTime($target_date);
$date->modify('-1 year');
$last_year = $date->format('Ymd');


$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');


$ce = new CsvExport($conn);

$ce->setExportDataSub('target', $dbio->fetchShopPerformanceMonthSub($shop_id, $this_date));
$ce->setExportDataSub('last_month', $dbio->fetchShopPerformanceMonthSub($shop_id, $last_month_date));
$ce->setExportDataSub('two_months_ago', $dbio->fetchShopPerformanceMonthSub($shop_id, $two_months_ago_date));
$ce->setExportDataSub('last_year', $dbio->fetchShopPerformanceMonthSub($shop_id, $last_year));


$result = $ce->getExportData();



$display_array = array(
        // 熱函ゴルフ
    3001 => array(
        4111=>"プレイ売上高",
        4112=>"会員券売上高",
        4113=>"ゴルフ用品・雑貨売上高",
        4114=>"手数料収入",
        4115=>"ポイント値引・入会金返金(△)",
        10001=>"純売上高",

        5211=>"商品仕入高",
        10002=>"当期売上原価",

        10003=>"売上総利益",

        6311=>"雑給",
        6112=>"旅費交通費",
        6113=>"広告宣伝費",
        6115=>"接客サービス費",
        6116=>"コンペ参加費",
        6117=>"プレイ器具費",
        6211=>"役員報酬",
        6212=>"社員給与",
        6213=>"従業員賞与",
        6312=>"法定福利費",
        6226=>"厚生費",
        6214=>"減価償却費",
        6215=>"地代家賃・リース料",
        6216=>"修繕費",
        6217=>"事務用品消耗品費",
        6218=>"通信交通費",
        6219=>"水道光熱費",
        6221=>"租税公課",
        6223=>"接待交際費",
        6224=>"保険料",
        6225=>"備品消耗品",
        6227=>"管理諸費",
        6222=>"寄附金",
        6229=>"その他の一般管理費",
        6231=>"雑費",
        10004=>"販売費及び一般管理費計",
        10005=>"営業利益",
    ),
    // 清水町ゴルフ
    3002 => array(
        4111=>"プレイ売上高",
        4112=>"会員券売上高",
        4113=>"ゴルフ用品・雑貨売上高",
        4114=>"手数料収入",
        4115=>"ポイント値引・入会金返金(△)",
        10001=>"純売上高",

        5211=>"商品仕入高",
        10002=>"当期売上原価",

        10003=>"売上総利益",

        6111=>"プロ報酬",
        6311=>"雑給",
        6112=>"旅費交通費",
        6113=>"広告宣伝費",
        6115=>"接客サービス費",
        6116=>"コンペ参加費",
        6117=>"プレイ器具費",
        6211=>"役員報酬",
        6212=>"社員給与",
        6213=>"従業員賞与",
        6312=>"法定福利費",
        6226=>"厚生費",
        6214=>"減価償却費",
        6215=>"地代家賃・リース料",
        6216=>"修繕費",
        6217=>"事務用品消耗品費",
        6218=>"通信交通費",
        6219=>"水道光熱費",
        6221=>"租税公課",
        6223=>"接待交際費",
        6224=>"保険料",
        6225=>"備品消耗品",
        6227=>"管理諸費",
        6222=>"寄附金",
        6229=>"その他の一般管理費",
        6231=>"雑費",
        10004=>"販売費及び一般管理費計",
        10005=>"営業利益",
    ),
    // ユアネット
    3003 => array(
        4111=>"売上高",
        10001=>"純売上高",

        10002=>"当期売上原価",

        10003=>"売上総利益",

        6111=>"給与手当",
        6311=>"雑給",
        6112=>"旅費交通費",
        6113=>"広告宣伝費",
        6114=>"販売促進費",
        6115=>"荷造発送費",
        6116=>"外注費",
        6117=>"支払手数料",
        6211=>"役員報酬",
        6213=>"従業員賞与",
        6312=>"法定福利費",
        6226=>"厚生費",
        6214=>"減価償却費",
        6215=>"地代家賃",
        6217=>"事務用品消耗品費",
        6218=>"通信交通費",
        6219=>"水道光熱費",
        6221=>"租税公課",
        6223=>"接待交際費",
        6224=>"保険料",
        6225=>"備品消耗品",
        6227=>"管理諸費",
        6228=>"リース/レンタル料",
        6229=>"諸会費",
        10004=>"販売費及び一般管理費計",
        10005=>"営業利益",
    ),
    // グランジャー
    3004 => array(
        4111=>"授業料等収入",
        4112=>"寮費等収入",
        4114=>"民泊売上高",
        10001=>"純売上高",

        5111=>"期首たな卸高",
        5212=>"教材等",
        5215=>"紹介手数料",
        10002=>"当期売上原価",

        10003=>"売上総利益",

        6111=>"給与手当",
        6112=>"旅費交通費",
        6113=>"広告宣伝費",
        6117=>"支払手数料",

        6211=>"役員報酬",
        6213=>"従業員賞与",
        6312=>"法定福利費",
        6226=>"厚生費",
        6214=>"減価償却費",
        6215=>"地代家賃",
        6216=>"修繕費",
        6217=>"事務用品消耗品費",
        6218=>"通信交通費",
        6219=>"水道光熱費",
        6221=>"租税公課",
        6222=>"寄付金",
        6223=>"接待交際費",
        6224=>"保険料",
        6225=>"備品消耗品費",
        6227=>"管理諸費",
        6229=>"諸会費",
        6231=>"雑費",
        10004=>"販売費及び一般管理費計",
        10005=>"営業利益",
    ),
    // 通信
    2000 => array(
        4111 => '売上',
        4114 => '通信売上高',
        10001 => '純売上高',
        5111 => '期首棚卸し',
        5211 => '商品仕入れ',
        5311 => '期末棚卸し',
        10002 => '当期売上原価',
        10003 => '売上総利益',
        6111 => '給与',
        6311 => '雑給',
        6112 => '旅費交通費',
        6113 => '広告宣伝費',
        6114 => '販売促進費',
        6115 => '荷造発送費',
        6117 => '支払い手数料',
        6213 => '従業員賞与',
        6312 => '法定福利費',
        6226 => '厚生費',
        6214 => '減価償却費',
        6215 => '地代家賃',
        6216 => '修繕車両関係費',
        6218 => '通信交通費',
        6219 => '水道光熱費',
        6221 => '租税公課',
        6222 => '寄附金',
        6224 => '保険料',
        6225 => '備品消耗品',
        6227 => '管理諸費',
        6229 => '諸会費',
        10004 => '販売費及び一般管理費計',
        10005 => '営業利益(損失)',
    )
);


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

        table thead tr th:nth-child(5),
        table tbody tr td:nth-child(5) {
            border-right: 3px solid red;
        }
        table thead tr th:nth-child(7),
        table tbody tr td:nth-child(7) {
            border-right: 3px solid red;
        }

        table thead tr:first-child th:nth-child(6),
        table thead tr:first-child th:nth-child(7) {
            border-top: 3px solid red;
        }
        table tbody tr:last-child td:nth-child(6),
        table tbody tr:last-child td:nth-child(7) {
            border-bottom: 3px solid red;
        }

        table tbody tr:last-child td:nth-child(10) {
            border: 3px solid red;
        }
    </style>

</head>

<body>

<div class="col-xs-10">
    <h3>経営会議資料 子会社</h3>


    <div class="form-row">

        <form method="get" action="./csv_display_subsidiary.php" class="form-inline">

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

            <th>前々月</th>
            <th>売上対比</th>

            <th>前月</th>
            <th>売上対比</th>

            <th><?= $display_date ?></th>
            <th>売上対比</th>

            <th>昨年</th>
            <th>売上対比</th>

            <th>前年同月差</th>
            <th>差率</th>
        </tr>
        </thead>
        <? foreach ((array)$display_array[$shop_id] as $one_code => $name): ?>
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

                <td class="comma"><?= $result[$one_code]['two_months_ago'] ?></td>
                <td><?= ($result[10001]['two_months_ago']) ? round($result[$one_code]['two_months_ago'] / $result[10001]['two_months_ago'] * 100, 1) : 0 ?>
                    %
                </td>

                <td class="comma"><?= $result[$one_code]['last_month'] ?></td>
                <td><?= ($result[10001]['last_month']) ? round($result[$one_code]['last_month'] / $result[10001]['last_month'] * 100, 1) : 0 ?>
                    %
                </td>

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