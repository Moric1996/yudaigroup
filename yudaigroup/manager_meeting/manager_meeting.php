<?php
include "../inc/auth.inc";

include "../AdminLTE/class/ClassLoader.php";



$shop_id = ($_GET['target_shop']) ? $_GET['target_shop'] : "101";
$get_date = ($_GET['target_date']) ? $_GET['target_date'] : date('Y-m-01');


$dbio = new DBIO();


// 今
$target_date1 = new datetime($get_date);
$start_date = $target_date1->format('Y-m-01');
$end_date = $target_date1->format('Y-m-t');
$news = $dbio->fetchDataTargetPeriodSum($start_date, $end_date, $shop_id);

$inventory = $dbio->fetchInfomartInventory($start_date, $shop_id);

$store_survey = $dbio->fetchStoreSurvey($start_date, $shop_id);

$labor_cost = $dbio->fetchLaborCost($start_date, $shop_id);

$fdcost = $dbio->fetchFDCost($start_date, $shop_id);

$desc_comment = $dbio->fetchDescComment($start_date, $shop_id);

$sales_by_time = $dbio->fetchSalesByTime($start_date, $shop_id);

$lunch_revenue = $dbio->fetchLunchRevenueSum($start_date, $end_date, $shop_id);
$dinner_revenue = $dbio->fetchDinnerRevenueSum($start_date, $end_date, $shop_id);

$lunch_customer = $dbio->fetchLunchCustomerSum($start_date, $end_date, $shop_id);
$dinner_customer = $dbio->fetchDinnerCustomerSum($start_date, $end_date, $shop_id);


// 前月
$target_date1->modify('- 1 month');
$start_date = $target_date1->format('Y-m-01');
$end_date = $target_date1->format('Y-m-t');
$last_month_news = $dbio->fetchDataTargetPeriodSum($start_date, $end_date, $shop_id);

$last_month_inventory = $dbio->fetchInfomartInventory($start_date, $shop_id);

$last_month_store_survey = $dbio->fetchStoreSurvey($start_date, $shop_id);

$last_year_lunch_revenue = $dbio->fetchLunchRevenueSum($start_date, $end_date, $shop_id);
$last_year_dinner_revenue = $dbio->fetchDinnerRevenueSum($start_date, $end_date, $shop_id);

$last_year_lunch_customer = $dbio->fetchLunchCustomerSum($start_date, $end_date, $shop_id);
$last_year_dinner_customer = $dbio->fetchDinnerCustomerSum($start_date, $end_date, $shop_id);

// 前年
$target_date = new datetime($get_date);
$target_date->modify('- 1 year');
$start_date = $target_date->format('Y-m-01');
$last_year_date = $target_date->format('Y-m-d');
$end_date = $target_date->format('Y-m-t');
$last_year_news = $dbio->fetchDataTargetPeriodSum($start_date, $end_date, $shop_id);

$last_year_inventory = $dbio->fetchInfomartInventory($start_date, $shop_id);

$last_year_sales_by_time = $dbio->fetchSalesByTime($start_date, $shop_id);

// 前々年
$target_date->modify('- 1 year');
$start_date = $target_date->format('Y-m-01');
$end_date = $target_date->format('Y-m-t');
$two_years_ago = $dbio->fetchDataTargetPeriodSum($start_date, $end_date, $shop_id);


// 前月
$target_date = new datetime($get_date);
$target_date->modify('- 2 months');
$start_date = $target_date->format('Y-m-01');
$two_months_ago_store_survey = $dbio->fetchStoreSurvey($start_date, $shop_id);

// 平均
$average_store_survey = $dbio->fetchStoreSurveyAverage($shop_id);


function calc_rate($deno, $nume, $digit = 0, $pasen = 1)
{
    return ($nume) ? round(($deno / $nume * $pasen), $digit) : 0;
}


function getMonthRange2 ($startUnixTime)
{
    /*    $start = (new DateTime)->setTimestamp($startUnixTime);
        $end   = (new DateTime)->setTimestamp($endUnixTime ? $endUnixTime : time());
        $next_month = new DateInterval('P1M');*/
    $start = new DateTime($startUnixTime);
    $end = new DateTime();

    $ymList = array();
    while ($start < $end) {
        $ymList[] = array('value' => $start->format('Y-m-01'), 'text' => $start->format('Y年m月'));
        $start->modify('+1 month');
    }

    return $ymList;
}

$year_month_option = array_reverse(getMonthRange2('20190101'));

$shop_all = $dbio->fetchShopList();
foreach ($shop_all as $key => $one) {
    $shop_list[$key]['id'] = $one['id'];
    $shop_list[$key]['name'] = $one['name'];
    if ($target_shop == $one['id']) {
        $shop_list[$key]['selected'] = 'selected';
    }
}

?>
<!doctype html>

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

    textarea.text {
        box-sizing: border-box;
        width: 100%;
        min-height: 4em;
    }

    .data_none {
        text-align: center;
    }

    .under_box {
        display: flex;
        width: 100%;
    }

    input,
    .main_table {

    }

    .input_color {
        background:#fff1cb;
    }
</style>

<div class="form-row">
    <div class="col-xs-10">
        <form method="get" action="./manager_meeting.php" class="form-inline">

            <select name="target_date" class="form-control">
                <?php foreach ($year_month_option as $val): ?>
                    <option value="<?= $val['value'] ?>" <?= ($val['value'] == $get_date) ? 'selected' : "" ?>><?= $val['text'] ?></option>
                <?php endforeach; ?>
            </select>

            <select name="target_shop" class="form-control">
                <?php foreach ($shop_list as $val): ?>
                    <option value="<?= $val['id'] ?>" <?= $val['selected'] ?>><?= $val['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <button class="btn btn-primary" type="submit">送信</button>
        </form>
    </div>
</div>

<div id="app" v-cloak>
    <figure>
<!--        <figcaption>【--><?//= $shop_name ?><!--】</figcaption>-->
        <table border="1" class="main_table">
            <thead>
            <tr>
                <th></th>
                <th>当月(円)</th>
                <th>予算比(%)</th>
                <th>前月(円)</th>
                <th>前月比(%)</th>
                <th>前年(円)</th>
                <th>前年比(%)</th>
                <th>前々年(円)</th>
                <th>前々年比(%)</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th>売上高</th>
                <td>{{ <?= $news['revenue'] ?> | addComma }}</td>
                <td>{{ <?= calc_rate($news['revenue'], $news['budget'], 1, 100) ?> | addPercent }}</td>
                <td>{{ <?= $last_month_news['revenue'] ?> | addComma }}</td>
                <td>{{ <?= calc_rate($news['revenue'], $last_month_news['revenue'], 1, 100) ?> | addPercent }}</td>
                <td>{{ <?= $last_year_news['revenue'] ?> | addComma }}</td>
                <td>{{ <?= calc_rate($news['revenue'], $last_year_news['revenue'], 1, 100) ?> | addPercent }}</td>
                <td>{{ <?= $two_years_ago['revenue'] ?> | addComma }}</td>
                <td>{{ <?= calc_rate($news['revenue'], $two_years_ago['revenue'], 1, 100) ?> | addPercent }}</td>
            </tr>
            <tr>
                <th>客数</th>
                <td>{{ <?= $news['customers_num'] ?> | addComma }}</td>
                <td></td>
                <td>{{ <?= $last_month_news['customers_num'] ?> | addComma }}</td>
                <td>{{ <?= calc_rate($news['customers_num'], $last_month_news['customers_num'], 1, 100) ?> | addPercent }}</td>
                <td>{{ <?= $last_year_news['customers_num'] ?> | addComma }}</td>
                <td>{{ <?= calc_rate($news['customers_num'], $last_year_news['customers_num'], 1, 100) ?> | addPercent }}</td>
                <td>{{ <?= $two_years_ago['customers_num'] ?> | addComma }}</td>
                <td>{{ <?= calc_rate($news['customers_num'], $two_years_ago['customers_num'], 1, 100) ?> | addPercent }}</td>
            </tr>
            <tr>
                <th>客単価</th>
                <td>{{ <?= calc_rate($news['revenue'], $news['customers_num']) ?> | addComma }}</td>
                <td></td>
                <td>{{ <?= calc_rate($last_month_news['revenue'], $last_month_news['customers_num']) ?> | addComma }}</td>
                <td>{{ <?= calc_rate(calc_rate($news['revenue'], $news['customers_num']),
                        calc_rate($last_month_news['revenue'], $last_month_news['customers_num']), 1, 100) ?> | addPercent }}</td>
                <td>{{ <?= calc_rate($last_year_news['revenue'], $last_year_news['customers_num']) ?> | addComma }}</td>
                <td>{{ <?= calc_rate(calc_rate($news['revenue'], $news['customers_num']),
                        calc_rate($last_year_news['revenue'], $last_year_news['customers_num']), 1, 100) ?> | addPercent }}</td>
                <td>{{ <?= calc_rate($two_years_ago['revenue'], $two_years_ago['customers_num']) ?> | addComma }}</td>
                <td>{{ <?= calc_rate(calc_rate($news['revenue'], $news['customers_num']),
                        calc_rate($two_years_ago['revenue'], $two_years_ago['customers_num']), 1, 100) ?> | addPercent }}</td>
            </tr>
            <tr>
                <th>期首在庫金額</th>
                <td>{{ inventory.beginningInventory | addComma }}</td>
                <td class="data_none">-</td>
                <td>{{ inventory.lastMonthBeginningInventory | addComma }}</td>
                <td class="data_none">-</td>
                <td>{{ inventory.lastYearBeginningInventory | addComma }}</td>
                <td class="data_none">-</td>
            </tr>
            <tr class="input_color">
                <th>当月仕入金額</th>
                <td>
                    <input v-if="inventory.isInput" type="number" v-model.number="inventory.purchase">
                    <span v-else>{{ inventory.purchase | addComma }}</span>
                </td>
                <td>{{ calcRate(inventory.purchase, <?= $news['revenue'] ?>) * 100 | calcRound(1) | addPercent }}</td>
                <td>
                    <input v-if="inventory.isInput" type="number" v-model.number="inventory.lastMonthPurchase">
                    <span v-else>{{ inventory.lastMonthPurchase | addComma }}</span>
                </td>
                <td>{{ calcRate(inventory.lastMonthPurchase, <?= $last_month_news['revenue'] ?>) * 100 | calcRound(1) | addPercent }}</td>
                <td>
                    <input v-if="inventory.isInput" type="number" v-model.number="inventory.lastYearPurchase">
                    <span v-else>{{ inventory.lastYearPurchase | addComma }}</span>
                </td>
                <td>{{ calcRate(inventory.lastYearPurchase, <?= $last_year_news['revenue'] ?>) * 100 | calcRound(1) | addPercent }}</td>
                <td>
                    <button v-if="inventory.isInput" v-on:click="updateInventory" class="btn btn-primary">保存</button>
                    <button v-else v-on:click="inventory.isInput=true" class="btn btn-primary">変更</button>
                </td>
            </tr>
            <tr>
                <th>期末在庫金額</th>
                <td>{{ inventory.endInventory | addComma }}</td>
                <td class="data_none">-</td>
                <td>{{ inventory.lastMonthEndInventory | addComma }}</td>
                <td class="data_none">-</td>
                <td>{{ inventory.lastYearEndInventory | addComma }}</td>
                <td class="data_none">-</td>
            </tr>
            <tr>
                <th>原価金額</th>
                <td>{{ inventory.beginningInventory + inventory.purchase - inventory.endInventory | addComma }}</td>
                <td>{{ calcRate(inventory.beginningInventory + inventory.purchase - inventory.endInventory, <?= $news['revenue'] ?>) * 100 | calcRound(1) | addPercent }}</td>

                <td>{{ inventory.lastMonthBeginningInventory + inventory.lastMonthPurchase - inventory.lastMonthEndInventory | addComma }}</td>
                <td>{{ calcRate(inventory.lastMonthBeginningInventory + inventory.lastMonthPurchase - inventory.lastMonthEndInventory, <?= $last_month_news['revenue'] ?>) * 100 | calcRound(1) | addPercent }}</td>

                <td>{{ inventory.lastYearBeginningInventory + inventory.lastYearPurchase - inventory.lastYearEndInventory | addComma }}</td>
                <td>{{ calcRate(inventory.lastYearBeginningInventory + inventory.lastYearPurchase - inventory.lastYearEndInventory, <?= $last_year_news['revenue'] ?>) * 100 | calcRound(1) | addPercent }}</td>
            </tr>
            <tr>
                <th>人件費(間接込)</th>
                <td>{{ laborCost.directLaborCost + laborCost.indirectLaborCost | addComma }}</td>
                <td>{{ calcRate(laborCost.directLaborCost + laborCost.indirectLaborCost, <?= $news['revenue'] ?>) * 100 | calcRound(1) | addPercent }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <th>人時売上</th>
                <td>{{ <?= calc_rate($news['revenue'], $news['work_time']) ?> | addComma }}</td>
                <td class="data_none">-</td>
                <td>{{ <?= calc_rate($last_month_news['revenue'], $last_month_news['work_time']) ?> | addComma }}</td>
                <td class="data_none">-</td>
                <td>{{ <?= calc_rate($last_year_news['revenue'], $last_year_news['work_time']) ?> | addComma }}</td>
                <td class="data_none">-</td>
            </tr>
            <tr>
                <th>値引率</th>
                <td>{{ <?= $news['discount_ticket'] ?> | addComma }}</td>
                <td>{{ <?= calc_rate($news['discount_ticket'], $news['revenue'], 1, 100) ?> | addPercent }}</td>
                <td>{{ <?= $last_month_news['discount_ticket'] ?> | addComma }}</td>
                <td>{{ <?= calc_rate($last_month_news['discount_ticket'], $last_month_news['revenue'], 1, 100) ?> | addPercent }}</td>
                <td>{{ <?= $last_year_news['discount_ticket'] ?> | addComma }}</td>
                <td>{{ <?= calc_rate($last_year_news['discount_ticket'], $last_year_news['revenue'], 1, 100) ?> | addPercent }}</td>
            </tr>
            </tbody>
        </table>
    </figure>

<div class="under_box">
    <div class="left_box">

        <figure>
            <figcaption>【原価】</figcaption>
            <table border="1">
                <tbody>
                <tr class="input_color">
                    <th>F売上</th>
                    <td>
                        <input v-if="FDCost.isInput" type="number" v-model.number="FDCost.foodRevenue">
                        <span v-else>{{ FDCost.foodRevenue | addComma }}</span>
                    </td>
                    <td>{{ calcRate(FDCost.foodRevenue, <?= $news['revenue'] ?>)*100 | calcRound(1) | addPercent }}</td>
                </tr>
                <tr class="input_color">
                    <th>F期首在庫金額</th>
                    <td>
                        <input v-if="FDCost.isInput" type="number" v-model.number="FDCost.foodBeginningInventory">
                        <span v-else>{{ FDCost.foodBeginningInventory | addComma }}</span>
                    </td>
                    <td class="data_none">-</td>
                </tr>
                <tr class="input_color">
                    <th>F期中仕入金額</th>
                    <td>
                        <input v-if="FDCost.isInput" type="number" v-model.number="FDCost.foodPurchase">
                        <span v-else>{{ FDCost.foodPurchase | addComma }}</span>
                    </td>
                    <td>{{ calcRate(FDCost.foodPurchase, FDCost.foodRevenue)*100 | calcRound(1) | addPercent }}</td>
                </tr>
                <tr class="input_color">
                    <th>F期末在庫金額</th>
                    <td>
                        <input v-if="FDCost.isInput" type="number" v-model.number="FDCost.foodEndInventory">
                        <span v-else>{{ FDCost.foodEndInventory | addComma }}</span>
                    </td>
                    <td class="data_none">-</td>
                </tr>
                <tr>
                    <th>F原価金額</th>
                    <td>{{ FDCost.foodBeginningInventory + FDCost.foodPurchase - FDCost.foodEndInventory | addComma }}</td>
                    <td>{{ calcRate(FDCost.foodBeginningInventory + FDCost.foodPurchase - FDCost.foodEndInventory, FDCost.foodRevenue)*100 | calcRound(1) | addPercent }}</td>
                </tr>
                <tr class="input_color">
                    <th>D売上</th>
                    <td>
                        <input v-if="FDCost.isInput" type="number" v-model.number="FDCost.drinkRevenue">
                        <span v-else>{{ FDCost.drinkRevenue | addComma }}</span>
                    </td>
                    <td>{{ calcRate(FDCost.drinkRevenue, <?= $news['revenue'] ?>)*100 | calcRound(1) | addPercent }}</td>
                </tr>
                <tr class="input_color">
                    <th>D期首在庫金額</th>
                    <td>
                        <input v-if="FDCost.isInput" type="number" v-model.number="FDCost.drinkBeginningInventory">
                        <span v-else>{{ FDCost.drinkBeginningInventory | addComma }}</span>
                    </td>
                    <td class="data_none">-</td>
                </tr>
                <tr class="input_color">
                    <th>D期中仕入金額</th>
                    <td>
                        <input v-if="FDCost.isInput" type="number" v-model.number="FDCost.drinkPurchase">
                        <span v-else>{{ FDCost.drinkPurchase | addComma }}</span>
                    </td>
                    <td>{{ calcRate(FDCost.drinkPurchase, FDCost.drinkRevenue)*100 | calcRound(1) | addPercent }}</td>
                </tr>
                <tr class="input_color">
                    <th>D期末在庫金額</th>
                    <td>
                        <input v-if="FDCost.isInput" type="number" v-model.number="FDCost.drinkEndInventory">
                        <span v-else>{{ FDCost.drinkEndInventory | addComma }}</span>
                    </td>
                    <td class="data_none">-</td>
                </tr>
                <tr>
                    <th>D原価金額</th>
                    <td>{{ FDCost.drinkBeginningInventory + FDCost.drinkPurchase - FDCost.drinkEndInventory | addComma }}</td>
                    <td>{{ calcRate( FDCost.drinkBeginningInventory + FDCost.drinkPurchase - FDCost.drinkEndInventory, FDCost.drinkRevenue)*100 | calcRound(1) | addPercent }}</td>
                    <td>
                        <button v-if="FDCost.isInput" v-on:click="updateFDCost" class="btn btn-primary">保存</button>
                        <button v-else v-on:click="FDCost.isInput = true" class="btn btn-primary">変更</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </figure>

        <figure>
            <figcaption>【人件費】</figcaption>
            <table border="1">
                <tbody>
                <tr>
                    <th>総労働時間</th>
                    <td>{{ <?= $news['work_time'] ?> | addComma }}</td>
                    <td>-</td>
                </tr>
                <tr class="input_color">
                    <th>直接人件費</th>
                    <td>
                        <input v-if="laborCost.isInput" type="number" v-model.number="laborCost.directLaborCost">
                        <span v-else>{{ laborCost.directLaborCost | addComma }}</span>
                    </td>
                    <td>{{ calcRate(laborCost.directLaborCost, <?= $news['revenue'] ?>)*100 | calcRound(1) | addPercent }}</td>
                </tr>
                <tr class="input_color">
                    <th>間接人件費</th>
                    <td>
                        <input v-if="laborCost.isInput" type="number" v-model.number="laborCost.indirectLaborCost">
                        <span v-else>{{ laborCost.indirectLaborCost | addComma }}</span>
                    </td>
                    <td>{{ calcRate(laborCost.indirectLaborCost, <?= $news['revenue'] ?>)*100 | calcRound(1) | addPercent }}</td>
                </tr>
                <tr>
                    <th>人件費合計</th>
                    <td>{{ laborCost.directLaborCost + laborCost.indirectLaborCost | addComma }}</td>
                    <td>{{ calcRate(laborCost.directLaborCost + laborCost.indirectLaborCost, <?= $news['revenue'] ?>)*100| calcRound(1) | addPercent }}</td>
                    <td>
                        <button v-if="laborCost.isInput" v-on:click="updateLaborCost" class="btn btn-primary">保存</button>
                        <button v-else v-on:click="laborCost.isInput=true" class="btn btn-primary">変更</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </figure>



<!--        <figure>-->
<!--            <figcaption>【予算】</figcaption>-->
<!--            <table border="1">-->
<!--                <tbody>-->
<!--                <tr>-->
<!--                    <th>売上</th>-->
<!--                    <td>{{ --><?//= $news['budget'] ?><!-- | addComma }}</td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <th>客数</th>-->
<!--                    <td>{{ --><?//= $news['budget'] ?><!-- | addComma }}</td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <th>客単価</th>-->
<!--                    <td>{{ --><?//= $news['budget'] ?><!-- | addComma }}</td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <th>直接人件費</th>-->
<!--                    <td>-->
<!--                        <input v-if="laborCost.isInput" type="number" v-model.number="laborCost.directLaborCost">-->
<!--                        <span v-else>{{ laborCost.directLaborCost }}</span>-->
<!--                    </td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <th>間接人件費</th>-->
<!--                    <td>-->
<!--                        <input v-if="laborCost.isInput" type="number" v-model.number="laborCost.indirectLaborCost">-->
<!--                        <span v-else>{{ laborCost.indirectLaborCost }}</span>-->
<!--                    </td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <th>人件費合計</th>-->
<!--                    <td>{{ laborCost.directLaborCost + laborCost.indirectLaborCost | addComma}}</td>-->
<!--                    <td>-->
<!--                        <button v-if="laborCost.isInput" v-on:click="updateLaborCost" class="btn btn-primary">保存</button>-->
<!--                        <button v-else v-on:click="laborCost.isInput=true" class="btn btn-primary">変更</button>-->
<!--                    </td>-->
<!--                </tr>-->
<!--                </tbody>-->
<!--            </table>-->
<!--        </figure>-->




    </div>


    <div class="right_box">

        <figure>
            <figcaption>【注記事項】</figcaption>
            <div>※binoの時間帯売上（区分別）でランチという文字列が入っている区分をランチ、それ以外をディナーとしています</div>
            <table border="1">
                <thead>
                <tr>
                    <th>時間帯別内訳</th>
                    <th>当月売上</th>
                    <th>前年売上</th>
                    <th>当月客数</th>
                    <th>前年客数</th>
                    <th>当月客単価</th>
                    <th>前年客単価</th>
                    <th>当月人時売上</th>
                    <th>前年人時売上</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th>ランチ</th>
                    <td>{{ salesByTime.lunchRevenue | addComma }}</td>
                    <td>{{ salesByTime.lastYearLunchRevenue | addComma }}</td>
                    <td>{{ salesByTime.lunchCustomersNum | addComma }}</td>
                    <td>{{ salesByTime.lastYearLunchCustomersNum | addComma }}</td>
                    <td>{{ calcRate(salesByTime.lunchRevenue, salesByTime.lunchCustomersNum) | calcRound | addComma }}</td>
                    <td>{{ calcRate(salesByTime.lastYearLunchRevenue, salesByTime.lastYearLunchCustomersNum) | calcRound | addComma }}</td>
                    <td>{{ salesByTime.lunchHumanTimeSales | addComma }}</td>
                    <td>{{ salesByTime.lastYearLunchHumanTimeSales | addComma }}</td>
                </tr>
                <tr>
                    <th>ディナー</th>
                    <td>{{ salesByTime.dinnerRevenue | addComma }}</td>
                    <td>{{ salesByTime.lastYearDinnerRevenue | addComma }}</td>
                    <td>{{ salesByTime.dinnerCustomersNum | addComma }}</td>
                    <td>{{ salesByTime.lastYearDinnerCustomersNum | addComma }}</td>
                    <td>{{ calcRate(salesByTime.dinnerRevenue, salesByTime.dinnerCustomersNum) | calcRound | addComma }}</td>
                    <td>{{ calcRate(salesByTime.lastYearDinnerRevenue, salesByTime.lastYearDinnerCustomersNum) | calcRound | addComma }}</td>
                    <td>{{ salesByTime.dinnerHumanTimeSales | addComma }}</td>
                    <td>{{ salesByTime.lastYearDinnerHumanTimeSales | addComma }}</td>
                </tr>
                <tr class="input_color">
                    <th>予約売上</th>
                    <td>
                        <input v-if="salesByTime.isInput" type="number" v-model.number="salesByTime.reservationRevenue">
                        <span v-else>{{ salesByTime.reservationRevenue | addComma }}</span>
                    </td>
                    <td>
                        <input v-if="salesByTime.isInput" type="number" v-model.number="salesByTime.lastYearReservationRevenue">
                        <span v-else>{{ salesByTime.lastYearReservationRevenue | addComma }}</span>
                    </td>
                    <td>
                        <input v-if="salesByTime.isInput" type="number" v-model.number="salesByTime.reservationCustomersNum">
                        <span v-else>{{ salesByTime.reservationCustomersNum | addComma }}</span>
                    </td>
                    <td>
                        <input v-if="salesByTime.isInput" type="number" v-model.number="salesByTime.lastYearReservationCustomersNum">
                        <span v-else>{{ salesByTime.lastYearReservationCustomersNum | addComma }}</span>
                    </td>
                    <td>{{ calcRate(salesByTime.reservationRevenue, salesByTime.reservationCustomersNum) | calcRound | addComma }}</td>
                    <td>{{ calcRate(salesByTime.lastYearReservationRevenue, salesByTime.lastYearReservationCustomersNum) | calcRound | addComma }}</td>
                    <td>
                        <input v-if="salesByTime.isInput" type="number" v-model.number="salesByTime.reservationHumanTimeSales">
                        <span v-else>{{ salesByTime.reservationHumanTimeSales | addComma }}</span>
                    </td>
                    <td>
                        <input v-if="salesByTime.isInput" type="number" v-model.number="salesByTime.lastYearReservationHumanTimeSales">
                        <span v-else>{{ salesByTime.lastYearReservationHumanTimeSales | addComma }}</span>
                    </td>
                </tr>
                <tr class="input_color">
                    <th>フリー売上</th>
                    <td>
                        <input v-if="salesByTime.isInput" type="number" v-model.number="salesByTime.freeRevenue">
                        <span v-else>{{ salesByTime.freeRevenue | addComma }}</span>
                    </td>
                    <td>
                        <input v-if="salesByTime.isInput" type="number" v-model.number="salesByTime.lastYearFreeRevenue">
                        <span v-else>{{ salesByTime.lastYearFreeRevenue | addComma }}</span>
                    </td>
                    <td>
                        <input v-if="salesByTime.isInput" type="number" v-model.number="salesByTime.freeCustomersNum">
                        <span v-else>{{ salesByTime.freeCustomersNum | addComma }}</span>
                    </td>
                    <td>
                        <input v-if="salesByTime.isInput" type="number" v-model.number="salesByTime.lastYearFreeCustomersNum">
                        <span v-else>{{ salesByTime.lastYearFreeCustomersNum | addComma }}</span>
                    </td>
                    <td>{{ calcRate(salesByTime.freeRevenue, salesByTime.freeCustomersNum) | calcRound | addComma }}</td>
                    <td>{{ calcRate(salesByTime.lastYearFreeRevenue, salesByTime.lastYearFreeCustomersNum) | calcRound | addComma }}</td>
                    <td>
                        <input v-if="salesByTime.isInput" type="number" v-model.number="salesByTime.freeHumanTimeSales">
                        <span v-else>{{ salesByTime.freeHumanTimeSales | addComma }}</span>
                    </td>
                    <td>
                        <input v-if="salesByTime.isInput" type="number" v-model.number="salesByTime.lastYearFreeHumanTimeSales">
                        <span v-else>{{ salesByTime.lastYearFreeHumanTimeSales | addComma }}</span>
                    </td>
                    <td>
                        <button v-if="salesByTime.isInput" v-on:click="updateSalesByTime" class="btn btn-primary">保存</button>
                        <button v-else v-on:click="salesByTime.isInput=true" class="btn btn-primary">変更</button>
                    </td>
                </tr>
                <tr>
                    <th colspan="9">業績説明-売上高</th>
                </tr>
                <tr>
                    <td colspan="9"><textarea v-model="descComment.revenueComment" class="text" :rows="descComment.revenueComment.split(/\n/).length"></textarea></td>
                </tr>
                <tr>
                    <th colspan="9">業績説明-原価</th>
                </tr>
                <tr>
                    <td colspan="9"><textarea v-model="descComment.foodPurchaseComment" class="text" :rows="descComment.foodPurchaseComment.split(/\n/).length"></textarea></td>
                </tr>
                <tr>
                    <th colspan="9">業績説明-人件費</th>
                </tr>
                <tr>
                    <td colspan="9"><textarea v-model="descComment.laborCostComment" class="text" :rows="descComment.laborCostComment.split(/\n/).length"></textarea></td>
                </tr>
                <tr>
                    <th colspan="9">業績説明-販売費その他</th>
                </tr>
                <tr>
                    <td colspan="9"><textarea v-model="descComment.otherComment" class="text" :rows="descComment.otherComment.split(/\n/).length"></textarea></td>
                </tr>
                <tr>
                    <td><button v-on:click="updateDescComment" class="btn btn-primary">保存</button></td>
                </tr>
                </tbody>
            </table>
        </figure>


        <figure>
            <table border="1">
                <thead>
                <tr>
                    <th>ファンくる</th>
                    <th>当月</th>
                    <th>前月</th>
                    <th>前々月</th>
                    <th>累計平均</th>
                    <th>前月対比コメント</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th>総合点</th>
                    <td><?= $store_survey['all_score'] ?></td>
                    <td><?= $last_month_store_survey['all_score'] ?></td>
                    <td><?= $two_months_ago_store_survey['all_score'] ?></td>
                    <td><?= round($average_store_survey['all_score'], 1) ?></td>
                    <td><textarea class="text" v-model="fankuruComment.allScoreComment"></textarea></td>
                </tr>
                <tr>
                    <th>再来店</th>
                    <td><?= $store_survey['revisit'] ?></td>
                    <td><?= $last_month_store_survey['revisit'] ?></td>
                    <td><?= $two_months_ago_store_survey['revisit'] ?></td>
                    <td><?= round($average_store_survey['revisit'], 1) ?></td>
                    <td><textarea class="text" v-model="fankuruComment.revisitComment"></textarea></td>
                </tr>
                <tr>
                    <th>接客</th>
                    <td><?= $store_survey['reception'] ?></td>
                    <td><?= $last_month_store_survey['reception'] ?></td>
                    <td><?= $two_months_ago_store_survey['reception'] ?></td>
                    <td><?= round($average_store_survey['reception'], 1) ?></td>
                    <td><textarea class="text" v-model="fankuruComment.receptionComment"></textarea></td>
                </tr>
                <tr>
                    <th>提供</th>
                    <td><?= $store_survey['offer'] ?></td>
                    <td><?= $last_month_store_survey['offer'] ?></td>
                    <td><?= $two_months_ago_store_survey['offer'] ?></td>
                    <td><?= round($average_store_survey['offer'], 1) ?></td>
                    <td><textarea class="text" v-model="fankuruComment.offerComment"></textarea></td>
                </tr>
                <tr>
                    <th>料理</th>
                    <td><?= $store_survey['cuisine'] ?></td>
                    <td><?= $last_month_store_survey['cuisine'] ?></td>
                    <td><?= $two_months_ago_store_survey['cuisine'] ?></td>
                    <td><?= round($average_store_survey['cuisine'], 1) ?></td>
                    <td><textarea class="text" v-model="fankuruComment.cuisineComment"></textarea></td>
                </tr>
                <tr>
                    <th>清潔感</th>
                    <td><?= $store_survey['cleanliness'] ?></td>
                    <td><?= $last_month_store_survey['cleanliness'] ?></td>
                    <td><?= $two_months_ago_store_survey['cleanliness'] ?></td>
                    <td><?= round($average_store_survey['cleanliness'], 1) ?></td>
                    <td><textarea class="text" v-model="fankuruComment.cleanlinessComment"></textarea></td>
                    <td><button v-on:click="updateFankuruComment" class="btn btn-primary">保存</button></td>
                </tr>
                </tbody>
            </table>
        </figure>
    </div>
</div>
</div>

<!-- jQuery 3 -->
<script src="../AdminLTE/bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="../AdminLTE/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- AdminLTE App -->
<script src="../AdminLTE/dist/js/adminlte.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/es6-promise@4/dist/es6-promise.auto.min.js"></script>

<script>
    var app = new Vue({
        el: '#app',
        data: {
            shop_id: <?= $shop_id ?>,
            target_date: '<?= $get_date ?>',
            last_month_date: '<?= $last_month_date ?>',
            last_year_date: '<?= $last_year_date ?>',
            inventory: {
                isInput: false,
                beginningInventory: <?= ($inventory['beginning_inventory']) ? $inventory['beginning_inventory'] : 0 ?>,
                purchase: <?= ($inventory['inventory']) ? $inventory['inventory'] : 0 ?>,
                endInventory: <?= ($inventory['end_inventory']) ? $inventory['end_inventory'] : 0 ?>,

                lastMonthBeginningInventory: <?= ($last_month_inventory['beginning_inventory']) ? $last_month_inventory['beginning_inventory'] : 0 ?>,
                lastMonthPurchase: <?= ($last_month_inventory['inventory']) ? $last_month_inventory['inventory'] : 0 ?>,
                lastMonthEndInventory: <?= ($last_month_inventory['end_inventory']) ? $last_month_inventory['end_inventory'] : 0 ?>,

                lastYearBeginningInventory: <?= ($last_year_inventory['beginning_inventory']) ? $last_year_inventory['beginning_inventory'] : 0 ?>,
                lastYearPurchase: <?= ($last_year_inventory['inventory']) ? $last_year_inventory['inventory'] : 0 ?>,
                lastYearEndInventory: <?= ($last_year_inventory['end_inventory']) ? $last_year_inventory['end_inventory'] : 0 ?>,
            },
            FDCost: {
                isInput: false,
                foodRevenue: <?= ($fdcost['food_revenue']) ? $fdcost['food_revenue'] : 0 ?>,
                foodBeginningInventory: <?= ($fdcost['food_beginning_inventory']) ? $fdcost['food_beginning_inventory'] : 0 ?>,
                foodPurchase: <?= ($fdcost['food_inventory']) ? $fdcost['food_inventory'] : 0 ?>,
                foodEndInventory: <?= ($fdcost['food_end_inventory']) ? $fdcost['food_end_inventory'] : 0 ?>,

                drinkRevenue: <?= ($fdcost['drink_revenue']) ? $fdcost['drink_revenue'] : 0 ?>,
                drinkBeginningInventory: <?= ($fdcost['drink_beginning_inventory']) ? $fdcost['drink_beginning_inventory'] : 0 ?>,
                drinkPurchase: <?= ($fdcost['drink_inventory']) ? $fdcost['drink_inventory'] : 0 ?>,
                drinkEndInventory: <?= ($fdcost['drink_end_inventory']) ? $fdcost['drink_end_inventory'] : 0 ?>,
            },
            laborCost: {
                isInput: false,
                directLaborCost: <?= ($labor_cost['direct_labor_cost']) ? $labor_cost['direct_labor_cost'] : 0 ?>,
                indirectLaborCost: <?= ($labor_cost['indirect_labor_cost']) ? $labor_cost['indirect_labor_cost'] : 0 ?>,
            },
            salesByTime: {
                isInput: false,

                lunchRevenue: <?= $lunch_revenue ? $lunch_revenue : 0 ?>,
                lunchCustomersNum: <?= $lunch_customer ? $lunch_customer : 0 ?>,
                lunchHumanTimeSales: <?= ($sales_by_time['lunch_human_time_sales']) ? $sales_by_time['lunch_human_time_sales'] : 0 ?>,

                dinnerRevenue: <?= $dinner_revenue ? $dinner_revenue : 0 ?>,
                dinnerCustomersNum: <?= $dinner_customer ? $dinner_customer : 0 ?>,
                dinnerHumanTimeSales: <?= ($sales_by_time['dinner_human_time_sales']) ? $sales_by_time['dinner_human_time_sales'] : 0?>,

                reservationRevenue: <?= ($sales_by_time['reservation_revenue']) ? $sales_by_time['reservation_revenue'] : 0 ?>,
                reservationCustomersNum: <?= ($sales_by_time['reservation_customers_num']) ? $sales_by_time['reservation_customers_num'] : 0 ?>,
                reservationHumanTimeSales: <?= ($sales_by_time['reservation_human_time_sales']) ? $sales_by_time['reservation_human_time_sales'] : 0 ?>,

                freeRevenue: <?= ($sales_by_time['free_revenue']) ? $sales_by_time['free_revenue'] : 0 ?>,
                freeCustomersNum: <?= ($sales_by_time['free_customers_num']) ? $sales_by_time['free_customers_num'] : 0 ?>,
                freeHumanTimeSales: <?= ($sales_by_time['free_human_time_sales']) ? $sales_by_time['free_human_time_sales'] : 0 ?>,


                lastYearLunchRevenue: <?= $last_year_lunch_revenue ? $last_year_lunch_revenue : 0 ?>,
                lastYearLunchCustomersNum: <?= $last_year_lunch_customer ? $last_year_lunch_customer : 0 ?>,
                lastYearLunchHumanTimeSales: <?= ($last_year_sales_by_time['lunch_human_time_sales']) ? $last_year_sales_by_time['lunch_human_time_sales'] : 0 ?>,

                lastYearDinnerRevenue: <?= $last_year_dinner_revenue ? $last_year_dinner_revenue : 0 ?>,
                lastYearDinnerCustomersNum: <?= $last_year_dinner_customer ? $last_year_dinner_customer : 0 ?>,
                lastYearDinnerHumanTimeSales: <?= ($last_year_sales_by_time['dinner_human_time_sales']) ? $last_year_sales_by_time['dinner_human_time_sales'] : 0 ?>,

                lastYearReservationRevenue: <?= ($last_year_sales_by_time['reservation_revenue']) ? $last_year_sales_by_time['reservation_revenue'] : 0 ?>,
                lastYearReservationCustomersNum: <?= ($last_year_sales_by_time['reservation_customers_num']) ? $last_year_sales_by_time['reservation_customers_num'] : 0 ?>,
                lastYearReservationHumanTimeSales: <?= ($last_year_sales_by_time['reservation_human_time_sales']) ? $last_year_sales_by_time['reservation_human_time_sales'] : 0 ?>,

                lastYearFreeRevenue: <?= ($last_year_sales_by_time['free_revenue']) ? $last_year_sales_by_time['free_revenue'] : 0 ?>,
                lastYearFreeCustomersNum: <?= ($last_year_sales_by_time['free_customers_num']) ? $last_year_sales_by_time['free_customers_num'] : 0 ?>,
                lastYearFreeHumanTimeSales: <?= ($last_year_sales_by_time['free_human_time_sales']) ? $last_year_sales_by_time['free_human_time_sales'] : 0 ?>,

            },
            descComment: {
                revenueComment: `<?= str_replace('`', '', $desc_comment['revenue_comment']) ?>`,
                foodPurchaseComment: `<?= str_replace('`', '', $desc_comment['food_purchase_comment']) ?>`,
                laborCostComment: `<?= str_replace('`', '', $desc_comment['labor_cost_comment']) ?>`,
                otherComment: `<?= str_replace('`', '', $desc_comment['other_comment']) ?>`,
            },
            fankuruComment: {
                allScoreComment: `<?= $store_survey['all_score_comment'] ?>`,
                revisitComment: `<?= $store_survey['revisit_comment'] ?>`,
                receptionComment: `<?= $store_survey['reception_comment'] ?>`,
                offerComment: `<?= $store_survey['offer_comment'] ?>`,
                cuisineComment: `<?= $store_survey['cuisine_comment'] ?>`,
                cleanlinessComment: `<?= $store_survey['cleanliness_comment'] ?>`
            },
        },
        filters: {
            addComma: function (value = 0) {
                return value.toLocaleString();
            },
            calcRound: function (number, precision=0) {

                var shift = function (number, precision, reverseShift) {
                    if (reverseShift) {
                        precision = -precision;
                    }
                    var numArray = ("" + number).split("e");
                    return +(numArray[0] + "e" + (numArray[1] ? (+numArray[1] + precision) : precision));
                };
                return shift(Math.round(shift(number, precision, false)), precision, true);
            },
            addPercent: function (value = 0) {
                if (value) {
                    return value+'%';
                }
            },
        },
        methods: {
            calcRate: function (val, val2) {
                return (val2) ? val / val2 : 0;
            },
            // 仕入
            updateInventory: function () {
                var self = this;
                var params = new URLSearchParams();
                params.append('shop_id', this.shop_id);
                params.append('target_date', this.target_date);
                params.append('inventory', this.inventory.purchase);
                params.append('last_month_inventory', this.inventory.lastMonthPurchase);
                params.append('last_year_inventory', this.inventory.lastYearPurchase);
                axios.post("update_inventory.php", params).then(function () {
                    alert("保存完了");
                    self.inventory.isInput = false;
                });
            },
            // FD仕入売上
            updateFDCost: function () {
                var self = this;
                var params = new URLSearchParams();
                params.append('shop_id', this.shop_id);
                params.append('target_date', this.target_date);
                params.append('food_revenue', this.FDCost.foodRevenue);
                params.append('food_beginning_inventory', this.FDCost.foodBeginningInventory);
                params.append('food_purchase', this.FDCost.foodPurchase);
                params.append('food_end_inventory', this.FDCost.foodEndInventory);
                params.append('drink_revenue', this.FDCost.drinkRevenue);
                params.append('drink_beginning_inventory', this.FDCost.drinkBeginningInventory);
                params.append('drink_purchase', this.FDCost.drinkPurchase);
                params.append('drink_end_inventory', this.FDCost.drinkEndInventory);
                params.append('FD_cost', this.FDCost);
                axios.post("update_FD_cost.php", params).then(function () {
                    alert("保存完了");
                    self.FDCost.isInput = false;
                });
            },
            updateLaborCost: function () {
                var self = this;
                var params = new URLSearchParams();
                params.append('shop_id', this.shop_id);
                params.append('target_date', this.target_date);
                params.append('direct_labor_cost', this.laborCost.directLaborCost);
                params.append('indirect_labor_cost', this.laborCost.indirectLaborCost);
                axios.post("update_labor_cost.php", params).then(function () {
                    alert("保存完了");
                    self.laborCost.isInput = false;
                });
            },
            // 時間帯別売上
            updateSalesByTime: function () {
                var self = this;
                var params = new URLSearchParams();
                params.append('shop_id', this.shop_id);
                params.append('target_date', this.target_date);
                params.append('last_year_date', this.last_year_date);

                params.append('reservation_revenue', this.salesByTime.reservationRevenue);
                params.append('reservation_customers_num', this.salesByTime.reservationCustomersNum);
                params.append('reservation_human_time_sales', this.salesByTime.reservationHumanTimeSales);

                params.append('free_revenue', this.salesByTime.freeRevenue);
                params.append('free_customers_num', this.salesByTime.freeCustomersNum);
                params.append('free_human_time_sales', this.salesByTime.freeHumanTimeSales);

                params.append('last_year_reservation_revenue', this.salesByTime.lastYearReservationRevenue);
                params.append('last_year_reservation_customers_num', this.salesByTime.lastYearReservationCustomersNum);
                params.append('last_year_reservation_human_time_sales', this.salesByTime.lastYearReservationHumanTimeSales);

                params.append('last_year_free_revenue', this.salesByTime.lastYearFreeRevenue);
                params.append('last_year_free_customers_num', this.salesByTime.lastYearFreeCustomersNum);
                params.append('last_year_free_human_time_sales', this.salesByTime.lastYearFreeHumanTimeSales);
                axios.post("update_sales_by_time.php", params).then(function () {
                    alert("保存完了");
                    self.salesByTime.isInput = false;
                });
            },
            updateDescComment: function () {
                var params = new URLSearchParams();
                params.append('shop_id', this.shop_id);
                params.append('target_date', this.target_date);
                params.append('revenueComment', this.descComment.revenueComment);
                params.append('foodPurchaseComment', this.descComment.foodPurchaseComment);
                params.append('laborCostComment', this.descComment.laborCostComment);
                params.append('otherComment', this.descComment.otherComment);
                axios.post("update_desc_comment.php", params).then(function () {
                    alert("保存完了");
                });
            },
            updateFankuruComment: function () {
                var params = new URLSearchParams();
                params.append('shop_id', this.shop_id);
                params.append('target_date', this.target_date);
                params.append('allScoreComment', this.fankuruComment.allScoreComment);
                params.append('revisitComment', this.fankuruComment.revisitComment);
                params.append('receptionComment', this.fankuruComment.receptionComment);
                params.append('offerComment', this.fankuruComment.offerComment);
                params.append('cuisineComment', this.fankuruComment.cuisineComment);
                params.append('cleanlinessComment', this.fankuruComment.cleanlinessComment);
                axios.post("update_fankuru_comment.php", params).then(function () {
                    alert("保存完了");
                });
            },

        },

    })
</script>