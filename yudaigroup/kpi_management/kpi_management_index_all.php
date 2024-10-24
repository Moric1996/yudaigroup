<?php
include "../inc/auth.inc";
include "../AdminLTE/class/ClassLoader.php";
include "../AdminLTE/class/WeeklyPerform.php";

// KPIデータ
$shop_id = ($_GET['target_shop']) ? $_GET['target_shop'] : "101";
$cost_rate_achievement = null;
$deviation_rate = null;
$labor_cost_rate = null;
$sales_per_hour = null;
$overtime_hours = null;
$ms_score = null;
$survey_acquisition_rate = null;
$survey_overall = null;
$number_of_complaints = null;
$sanitation_inspection_rate = null;
$sanitation_inspection_evaluate = null;
$proactive_approach = null;
$compliance_with_rules = null;

$dbio = new DBIO();

$first_date = date('Y-m-01');
$last_date = date('Y-m-t'); // 今月末

// どこまで登録したかを記録するためのshop_id
$shop_record = $_GET['shop_id'];

// 表示月選択用
$month = array();
for($before = 0; $before >= -24; $before--){
    // 日付によっては誤差が生じるため、一旦初めの月に戻して計算する($first_date使用)
    $month_sub = (string)$before . ' ' . 'month';
    $month[] = array(
        "month_string" =>  date("Y年m月", strtotime((string)$first_date . $month_sub)),
        "month" => date("Y-m-01", strtotime((string)$first_date . $month_sub)),
    );
}
$month_list = json_encode($month);

$yfd = new WeeklyPerform();
$yfd->setTargetData($dbio->fetchDataTargetPeriod($first_date, $last_date, 1));

$yfd->formatNowData();
$yfd_array = array();
foreach((array)$yfd as $item){
    $yfd_array[] = (array)$item;
}
$result = json_encode($yfd);

// 今月
$this_month = array(
    "month_string" =>  date("Y年m月"),
    "month" => date("Y-m-01"),
);
?>


<!doctype html>

<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<title>KPI資料</title>

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

    .input_color {
        background:#fff1cb;
    }

    .table-margin{
        margin: 10px;
    }

    .box-size{
        width: 250px;
    }

    .input-data{
        text-align: center;
    }

    .table>tbody>tr>th {
        border-right: groove;
        vertical-align: middle;
        text-align-last: center;
        font-weight: normal;
        position:sticky;
        left: 0;
    }

    .fixed-item {
        position:sticky;
        left: 0;
        background-color: #66cdaa;
    }

    .fixed-item-col2 {
        position:sticky;
        left: 100px;
        background-color: #66cdaa;
    }

    .fixed-item-header {
        position:sticky;
        left: 0;
        background-color: #63baa8;
    }

    .fixed-item-col2-header {
        position:sticky;
        left: 100px;
        background-color: #63baa8;
    }

    .index-col {
        background-color: #66cdaa;
        width: 100px;
        border-right: groove;
    }

    .item-col{
        background-color: #66cdaa;
        width: 150px;
        border-right: solid;
    }
    .input-list-header {
        position:sticky;
        top: 0;
        background-color: #63baa8;
    }

    .fixed-item-col3-header {
        position:sticky;
        background-color: #fff1cb;
    }
</style>

<div id="app" v-cloak>
    <h1>全体データ</h1>
    <div>
        <button type="button" class="btn btn-primary" @click="onClickCreate">手動入力画面</button>
        <button type="button" class="btn btn-info" style="margin-right: 30px;" @click="onClickCSV">速報値CSV取込</button>
        <button type="button" class="btn btn-success" @click="onClickShop">店舗別</button>
        <button type="button" class="btn btn-warning" @click="onClickBusinessType">業態別</button>
    </div>
    <div style="display: flex; margin-top: 10px;">
        <span style="font-size: 20px; padding-left: 10px;">月選択:</span>
        <select class="form-control box-size" style="margin-bottom: 10px;" v-model="choiceMonth">
            <option v-for="(month, index) in monthList" :key="index" :value="month">{{ month.month_string }}</option>
        </select>
        <button type="button" style="margin-left: 10px;" class="btn btn-info" @click="onClickDisplay">データ表示</button>
    </div>
    <div>現在表示中のデータ: {{ displayMonth }}</div>
    <div style="overflow-x: scroll; max-height: 80vh;">
        <table class="table table-margin" style="border: solid; overflow-x: scroll; width: 700px; table-layout: fixed; overflow:auto;">
        <colgroup>
            <col class="kpi-index" style="background-color: #66cdaa; width: 100px; border-right: groove;"></col>
            <col class="item-name" style="background-color: #66cdaa; width: 150px; border-right: solid;"></col>
            <col v-for="(data, index) in kpiDataList" :key="'col1-' + index"  class="shop-data" style="width: 100px; border-right: dotted; overflow-x: scroll; overflow-y: scroll;"></col>
            <col v-for="(data, index) in kpiDataList" :key="'col2-' + index"  class="shop-data" style="width: 100px; border-right: dotted; overflow-x: scroll; overflow-y: scroll;"></col>
        </colgroup>
        <thead class="input-list-header" style="background-color: #fff1cb; z-index: 100;">
            <tr>
                <td class="fixed-item-header fixed-item-col3-header" style="border-bottom: solid; text-align: center; vertical-align: middle; z-index: 100;" rowspan="2">指標</td>
                <td class="fixed-item-col2-header fixed-item-col3-header" style="border-bottom: solid; text-align: center; vertical-align: middle; z-index: 100;" rowspan="2">項目</td>
                <td class="fixed-item-col3-header" v-for="(data, index) in kpiDataList" :key="index" style="text-align: center; border-bottom: double; border-right: solid;" colspan="2">{{ data.name }}</td>
            </tr>
            <tr>
                <template v-for="(data, index) in kpiDataList">
                    <td style="text-align: center; border-bottom: solid;">速報</td>
                    <td style="text-align: center; border-bottom: solid; border-right: solid;">確定</td>
                </template>
            </tr>
        </thead>
        <tbody>
            <!-- 売上 -->
            <tr>
                <th style="background-color: #66cdaa;" rowspan="3">売上</th>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">予算</td>
                <template v-for="(data, index) in kpiDataList">
                    <td style="border-bottom: dotted; border-right: solid;" class="input-data" colspan="2">
                        {{ (revenueList ? (revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id)))?.budget_sum : null) | addComma }}
                    </td>
                    <!--<td class="input-data">
                        {{ (revenueList ? (revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id)))?.budget_sum : null) | addComma }}
                    </td>-->
                </template>
            </tr>
            <tr>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">速報/確定</td>
                <template v-for="(data, index) in kpiDataList">
                    <td
                        class="input-data"
                        style="border-bottom: dotted;"
                        :style="((preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0) < Number(revenueList ? (revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id)))?.budget_sum : 0)) ? 'background-color: #efbdca;': 'background-color: #bde7ef;'"
                    >
                        {{ preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0 | addComma }}
                    </td>
                    <td
                        class="input-data"
                        style="border-bottom: dotted; border-right: solid;"
                        :style="((earningList.find((earning)=>earning.shop_id === data.shop_data_id) ? earningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0) < Number(revenueList ? (revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id)))?.budget_sum : 0)) ? 'background-color: #efbdca;': 'background-color: #bde7ef;'"
                    >
                        {{ earningList.find((earning)=>earning.shop_id === data.shop_data_id) ? earningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0 | addComma }}
                    </td>
                </template>
            </tr>
            <tr style="border-bottom: double;">
                <td class="fixed-item-col2">予算比</td>
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" style="border-bottom: dotted;">
                        {{ preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning_rate : 0 | percent }}%
                    </td>
                    <td class="input-data" style="border-bottom: dotted; border-right: solid;">
                        {{ earningList.find((earning)=>earning.shop_id === data.shop_data_id) ? earningList.find((earning)=>earning.shop_id === data.shop_data_id).earning_rate : 0 | percent }}%
                    </td>
                </template>
            </tr>
            <!-- 店舗利益 -->
            <tr>
                <th style="background-color: #66cdaa;" rowspan="3" style="border-bottom: dotted; border-right: solid;">店舗利益</th>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">予算</td>
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" style="border-bottom: dotted; border-right: solid;" colspan="2">{{ data.value_data.find(item => item.value_type === '1')?.profit_target ?? 0 | addComma }}</td>
                    <!--<td class="input-data">{{ data.value_data.find(item => item.value_type === '2')?.profit_target ?? 0 | addComma }}</td>-->
                </template>
            </tr>
            <tr>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">速報/確定</td>
                <template v-for="(data, index) in kpiDataList">
                    <!--
                    <td class="input-data">
                        {{ preCostsList.find((cost)=>cost.shop_id === data.shop_data_id) ? preCostsList.find((cost)=>cost.shop_id === data.shop_data_id)?.profit : 0 | addComma }}
                    </td>
                    <td class="input-data">
                        {{ costsList.find((cost)=>cost.shop_id === data.shop_data_id) ? costsList.find((cost)=>cost.shop_id === data.shop_data_id)?.profit : 0 | addComma }}
                    </td>
                    -->
                    <td
                        class="input-data"
                        style="border-bottom: dotted;"
                        :style="((preCostsList.find((cost)=>cost.shop_id === data.shop_data_id) && preSGAList.find((sga)=>sga.shop_id === data.shop_data_id)
                            ? preCostsList.find((cost)=>cost.shop_id === data.shop_data_id)?.profit - preSGAList.find((sga)=>sga.shop_id === data.shop_data_id)?.value
                            : 0) <= (data.value_data.find(item => item.value_type === '2')?.profit_target ?? 0)) ? 'background-color: #efbdca;': 'background-color: #bde7ef;'"
                    >
                        {{ preCostsList.find((cost)=>cost.shop_id === data.shop_data_id) && preSGAList.find((sga)=>sga.shop_id === data.shop_data_id)
                            ? preCostsList.find((cost)=>cost.shop_id === data.shop_data_id)?.profit - preSGAList.find((sga)=>sga.shop_id === data.shop_data_id)?.value
                            : 0 | addComma }}
                    </td>
                    <td
                        class="input-data"
                        style="border-bottom: dotted; border-right: solid;"
                        :style="((costsList.find((cost)=>cost.shop_id === data.shop_data_id) && SGAList.find((sga)=>sga.shop_id === data.shop_data_id)
                            ? costsList.find((cost)=>cost.shop_id === data.shop_data_id)?.profit - SGAList.find((sga)=>sga.shop_id === data.shop_data_id)?.value
                            : 0) <= (data.value_data.find(item => item.value_type === '2')?.profit_target ?? 0)) ? 'background-color: #efbdca;': 'background-color: #bde7ef;'"
                    >
                        {{ costsList.find((cost)=>cost.shop_id === data.shop_data_id) && SGAList.find((sga)=>sga.shop_id === data.shop_data_id)
                            ? costsList.find((cost)=>cost.shop_id === data.shop_data_id)?.profit - SGAList.find((sga)=>sga.shop_id === data.shop_data_id)?.value
                            : 0 | addComma }}
                    </td>
                </template>
            </tr>
            <tr style="border-bottom: double;">
                <td class="fixed-item-col2">予算比</td>
                <template v-for="(data, index) in kpiDataList">
                    <!--<td class="input-data">
                        {{ isFinite(preCostsList.find((cost)=>cost.shop_id === data.shop_data_id)?.profit / data.value_data.find(item => item.value_type === '2')?.profit_target)
                            ? (preCostsList.find((cost)=>cost.shop_id === data.shop_data_id)?.profit / data.value_data.find(item => item.value_type === '2')?.profit_target) * 100 : 0 | percent }}%
                    </td>
                    <td class="input-data">
                        {{ isFinite(costsList.find((cost)=>cost.shop_id === data.shop_data_id)?.profit / data.value_data.find(item => item.value_type === '1')?.profit_target)
                            ? (costsList.find((cost)=>cost.shop_id === data.shop_data_id)?.profit / data.value_data.find(item => item.value_type === '1')?.profit_target) * 100 : 0 | percent }}%
                    </td>-->
                    <td class="input-data" style="border-bottom: dotted;">
                        {{ isFinite((preCostsList.find((cost)=>cost.shop_id === data.shop_data_id)?.profit - preSGAList.find((sga)=>sga.shop_id === data.shop_data_id)?.value) / data.value_data.find(item => item.value_type === '1')?.profit_target)
                            ? ((preCostsList.find((cost)=>cost.shop_id === data.shop_data_id)?.profit - preSGAList.find((sga)=>sga.shop_id === data.shop_data_id)?.value) / data.value_data.find(item => item.value_type === '1')?.profit_target) * 100 : 0 | percent }}%
                    </td>
                    <td class="input-data" style="border-bottom: dotted; border-right: solid;">
                        {{ isFinite((costsList.find((cost)=>cost.shop_id === data.shop_data_id)?.profit - SGAList.find((sga)=>sga.shop_id === data.shop_data_id)?.value) / data.value_data.find(item => item.value_type === '1')?.profit_target)
                            ? ((costsList.find((cost)=>cost.shop_id === data.shop_data_id)?.profit - SGAList.find((sga)=>sga.shop_id === data.shop_data_id)?.value) / data.value_data.find(item => item.value_type === '1')?.profit_target) * 100 : 0 | percent }}%
                    </td>
                </template>
            </tr>
            
            <!-- 客数 -->
            <tr>
                <th style="background-color: #66cdaa;" rowspan="3" style="border-bottom: dotted; border-right: solid;">客数</th>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">目標</td>
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: solid;">
                        {{ data.value_data.find(item => item.value_type === '2')?.number_of_customer_target ?? 0 | addComma }}
                    </td>
                    <!--<td class="input-data">
                        {{ data.value_data.find(item => item.value_type === '1')?.number_of_customer_target ?? 0 | addComma }}
                    </td>-->
                </template>
            </tr>
            <tr>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">実績</td>
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" style="border-bottom: dotted; border-right: solid;" colspan="2" :style="((customerTargetList.find((target) => target.shop_id === data.scraping_id)?.customers_num) < (data.value_data.find(item => item.value_type === '2')?.number_of_customer_target ?? 0)) ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'">
                        {{ customerTargetList.find((target) => target.shop_id === data.scraping_id)?.customers_num | addComma }}
                    </td>
                    <!--<td class="input-data">
                        {{ customerTargetList.find((target) => target.shop_id === data.scraping_id)?.customers_num | addComma }}
                    </td>-->
                </template>
            </tr>
            <tr style="border-bottom: double;">
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">目標実績差</td>
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: solid;">
                    {{ (customerTargetList.find((target) => target.shop_id === data.scraping_id)?.customers_num ?? 0)
                        - (isFinite(Number(data.value_data.find(item => item.value_type === '2')?.number_of_customer_target)) ? Number(data.value_data.find(item => item.value_type === '2')?.number_of_customer_target) : 0) | addComma }}
                    </td>
                    <!--<td class="input-data">
                    {{ (customerTargetList.find((target) => target.shop_id === data.scraping_id)?.customers_num ?? 0)
                        - (isFinite(Number(data.value_data.find(item => item.value_type === '1')?.number_of_customer_target)) ? Number(data.value_data.find(item => item.value_type === '1')?.number_of_customer_target) : 0) | addComma }}
                    </td>-->
                </template>
            </tr>
            <!-- 原価率 -->
            <tr>
                <th style="background-color: #66cdaa;" style="border-bottom: dotted; border-right: solid;" rowspan="3">原価率</th>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">目標</td>
                <template v-for="(data, index) in kpiDataList">
                    <!--<td class="input-data">
                        {{ isFinite((data.value_data.find(item => item.value_type === '2')?.cost_target ?? 0) / (preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0))
                            ? ((data.value_data.find(item => item.value_type === '2')?.cost_target ?? 0) / (preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 1)) * 100
                            : 0
                            | percent 
                        }}%
                    </td>-->
                    <td class="input-data" style="border-bottom: dotted;">
                        {{ (((preCostsList.find((cost)=>cost.shop_id === data.shop_data_id) ? preCostsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (revenueList ? (revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id)))?.budget_sum : 1)) * 100)
                            | percent }}%
                    </td>
                    <td class="input-data" style="border-bottom: dotted; border-right: solid;">
                        {{ (((costsList.find((cost)=>cost.shop_id === data.shop_data_id) ? costsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (revenueList ? (revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id)))?.budget_sum : 1)) * 100)
                            | percent }}%
                    </td>
                    <!--<td class="input-data">
                        {{ isFinite((data.value_data.find(item => item.value_type === '1')?.cost_target ?? 0) / (preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0))
                            ? ((data.value_data.find(item => item.value_type === '1')?.cost_target ?? 0) / (preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 1)) * 100
                            : 0
                            | percent 
                        }}%
                    </td>-->
                </template>
            </tr>
            <tr>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">速報/確定</td>
                <template v-for="(data, index) in kpiDataList">
                    <td
                        class="input-data"
                        id="pre_cost"
                        style="border-bottom: dotted;"
                        :style="(isFinite((preCostsList.find((cost)=>cost.shop_id === data.shop_data_id) ? preCostsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0))
                            ? ((preCostsList.find((cost)=>cost.shop_id === data.shop_data_id) ? preCostsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 1)) * 100
                            : 0) <= (((preCostsList.find((cost)=>cost.shop_id === data.shop_data_id) ? preCostsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (revenueList ? (revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id)))?.budget_sum : 1)) * 100) ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'"
                    >
                        {{ (isFinite((preCostsList.find((cost)=>cost.shop_id === data.shop_data_id) ? preCostsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0))
                            ? ((preCostsList.find((cost)=>cost.shop_id === data.shop_data_id) ? preCostsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 1)) * 100
                            : 0)
                            | percent }}%
                    </td>
                    <td
                        class="input-data"
                        id="cost"
                        style="border-bottom: dotted; border-right: solid;"
                        :style="(isFinite((costsList.find((cost)=>cost.shop_id === data.shop_data_id) ? costsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (earningList.find((earning)=>earning.shop_id === data.shop_data_id) ? earningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0))
                            ? ((costsList.find((cost)=>cost.shop_id === data.shop_data_id) ? costsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (earningList.find((earning)=>earning.shop_id === data.shop_data_id) ? earningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 1)) * 100
                            : 0) <= (((costsList.find((cost)=>cost.shop_id === data.shop_data_id) ? costsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (revenueList ? (revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id)))?.budget_sum : 1)) * 100) ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'"
                    >
                        {{ (isFinite((costsList.find((cost)=>cost.shop_id === data.shop_data_id) ? costsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (earningList.find((earning)=>earning.shop_id === data.shop_data_id) ? earningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0))
                            ? ((costsList.find((cost)=>cost.shop_id === data.shop_data_id) ? costsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (earningList.find((earning)=>earning.shop_id === data.shop_data_id) ? earningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 1)) * 100
                            : 0)
                            | percent }}%
                    </td>
                </template>
            </tr>
            <tr style="border-bottom: double;">
                <td class="fixed-item-col2">目標実績差</td>
                <!-- 目標データがある？見つかったら処理するため今は0 -->
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" id="pre_cost" style="border-bottom: dotted;">
                        {{ (isFinite((preCostsList.find((cost)=>cost.shop_id === data.shop_data_id) ? preCostsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0))
                            ? ((preCostsList.find((cost)=>cost.shop_id === data.shop_data_id) ? preCostsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 1)) * 100
                            : 0)
                            - (((preCostsList.find((cost)=>cost.shop_id === data.shop_data_id) ? preCostsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (revenueList ? (revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id)))?.budget_sum : 1)) * 100)
                            | percent }}%
                    </td>
                    <td class="input-data" id="cost" style="border-bottom: dotted; border-right: solid;">
                        {{ (isFinite((costsList.find((cost)=>cost.shop_id === data.shop_data_id) ? costsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (earningList.find((earning)=>earning.shop_id === data.shop_data_id) ? earningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0))
                            ? ((costsList.find((cost)=>cost.shop_id === data.shop_data_id) ? costsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (earningList.find((earning)=>earning.shop_id === data.shop_data_id) ? earningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 1)) * 100
                            : 0)
                            - (((costsList.find((cost)=>cost.shop_id === data.shop_data_id) ? costsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (revenueList ? (revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id)))?.budget_sum : 1)) * 100)
                            | percent }}%
                    </td>
                    <!--<td class="input-data" id="pre_cost">
                        {{ (isFinite((preCostsList.find((cost)=>cost.shop_id === data.shop_data_id) ? preCostsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0))
                            ? ((preCostsList.find((cost)=>cost.shop_id === data.shop_data_id) ? preCostsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 1)) * 100
                            : 0)
                            - (isFinite((data.value_data.find(item => item.value_type === '2')?.cost_target ?? 0) / (preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0))
                            ? ((data.value_data.find(item => item.value_type === '2')?.cost_target ?? 0) / (preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 1)) * 100
                            : 0)
                            | percent }}%
                    </td>
                    <td class="input-data" id="cost">
                        {{ (isFinite((costsList.find((cost)=>cost.shop_id === data.shop_data_id) ? costsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (earningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0))
                            ? ((costsList.find((cost)=>cost.shop_id === data.shop_data_id) ? costsList.find((cost)=>cost.shop_id === data.shop_data_id).cost : 0) / (earningList.find((earning)=>earning.shop_id === data.shop_data_id) ? preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 1)) * 100
                            : 0)
                            - (isFinite((data.value_data.find(item => item.value_type === '1')?.cost_target ?? 0) / (earningList.find((earning)=>earning.shop_id === data.shop_data_id) ? earningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 0))
                            ? ((data.value_data.find(item => item.value_type === '1')?.cost_target ?? 0) / (earningList.find((earning)=>earning.shop_id === data.shop_data_id) ? earningList.find((earning)=>earning.shop_id === data.shop_data_id).earning : 1)) * 100
                            : 0)
                            | percent }}%
                    </td>-->
                </template>
            </tr>
            <!-- 人件費 -->
            <tr>
                <th style="background-color: #66cdaa;" rowspan="4">人件費</th>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">人件費率 目標</td>
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" style="border-bottom: dotted;">
                        {{ data.value_data.find(item => item.value_type === '2')?.labor_cost_target ?? 0 }}%
                    </td>
                    <td class="input-data" style="border-bottom: dotted; border-right: solid;">
                        {{ data.value_data.find(item => item.value_type === '1')?.labor_cost_target ?? 0 }}%
                    </td>
                </template>
            </tr>
            <tr>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">実績</td>
                <template v-for="(data, index) in kpiDataList">
                    <td
                        class="input-data"
                        style="border-bottom: dotted;"
                        :style="((preLaborCostList.find((labor)=>labor.shop_id === data.shop_data_id)
                            ? preLaborCostList.find((labor)=>labor.shop_id === data.shop_data_id).labor_cost : 0) <= (data.value_data.find(item => item.value_type === '2')?.labor_cost_target ?? 0)) ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'"
                    >
                        {{ preLaborCostList.find((labor)=>labor.shop_id === data.shop_data_id) ? preLaborCostList.find((labor)=>labor.shop_id === data.shop_data_id).labor_cost : 0 | percent }}%
                    </td>
                    <td
                        class="input-data"
                        style="border-bottom: dotted; border-right: solid;"
                        :style="((laborCostList.find((labor)=>labor.shop_id === data.shop_data_id)
                            ? laborCostList.find((labor)=>labor.shop_id === data.shop_data_id).labor_cost : 0) <= (data.value_data.find(item => item.value_type === '1')?.labor_cost_target ?? 0)) ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'"
                    >
                        {{ laborCostList.find((labor)=>labor.shop_id === data.shop_data_id) ? laborCostList.find((labor)=>labor.shop_id === data.shop_data_id).labor_cost : 0 | percent }}%
                    </td>
                </template>
            </tr>
            <tr>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">人時売上 目標</td>
                <template v-for="(data, index) in kpiDataList">
                    <!--<td class="input-data" colspan="2">
                        {{ data.value_data.find(item => item.value_type === '2')?.man_hour_sales_target ?? 0 | addComma }}
                    </td>-->
                    <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: solid;">
                        {{ data.value_data.find(item => item.value_type === '1')?.man_hour_sales_target ?? 0 | addComma }}
                    </td>
                </template>
            </tr>
            <tr style="border-bottom: double;">
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">実績</td>
                <template v-for="(data, index) in kpiDataList">
                    <!--<td class="input-data">
                        {{ preEarningList.find((earning)=>earning.shop_id === data.shop_data_id) && revenueList
                            ? revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id))?.work_time_sum !== "0" ? Number(preEarningList.find((earning)=>earning.shop_id === data.shop_data_id).earning) / Number(revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id))?.work_time_sum) : 0
                            : 0 | addComma }}
                    </td>-->
                    <td
                        class="input-data"
                        colspan="2"
                        style="border-bottom: dotted; border-right: solid;"
                        :style="((earningList.find((earning)=>earning.shop_id === data.shop_data_id) && revenueList
                            ? revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id))?.work_time_sum !== '0' ? Number(earningList.find((earning)=>earning.shop_id === data.shop_data_id).earning) / Number(revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id))?.work_time_sum) : 0
                            : 0) <= (data.value_data.find(item => item.value_type === '1')?.man_hour_sales_target ?? 0)) ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'"
                    >
                        {{ earningList.find((earning)=>earning.shop_id === data.shop_data_id) && revenueList
                            ? revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id))?.work_time_sum !== "0" ? Number(earningList.find((earning)=>earning.shop_id === data.shop_data_id).earning) / Number(revenueList.find((revenue)=>String(data.scraping_id) === String(revenue.shop_id))?.work_time_sum) : 0
                            : 0 | addComma }}
                    </td>
                </template>
            </tr>
            <!-- 労務 -->
            <tr style="border-bottom: double;">
                <th style="background-color: #66cdaa;">労務</th>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">残業時間</td>
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '2')?.overtime_hours }}</td>
                    <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '1')?.overtime_hours }}</td>-->
                </template>
            </tr>
            <!-- MS -->
            <tr style="border-bottom: double;">
                <th style="background-color: #66cdaa;">MS</th>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">得点</td>
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '2')?.ms_score }}</td>
                    <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '1')?.ms_score }}</td>-->
                </template>
            </tr>
            <!-- アンケート -->
            <tr>
                <th style="background-color: #66cdaa;" rowspan="2">アンケート</th>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">取得率</td>
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '2')?.survey_acquisition_rate }}</td>
                    <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '1')?.survey_acquisition_rate }}</td>-->
                </template>
            </tr>
            <tr style="border-bottom: double;">
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">総合</td>
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '2')?.survey_overall }}</td>
                    <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '1')?.survey_overall }}</td>-->
                </template>
            </tr>
            <!-- クレーム -->
            <tr style="border-bottom: double;">
                <th style="background-color: #66cdaa;">クレーム</th>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">件数</td>
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '2')?.number_of_complaints }}</td>
                    <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '1')?.number_of_complaints }}</td>-->
                </template>
            </tr>
            <!-- 衛生検査 -->
            <tr>
                <th style="background-color: #66cdaa;" rowspan="2">衛生検査</th>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">適合率</td>
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '2')?.sanitation_inspection_rate }}</td>
                    <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '1')?.sanitation_inspection_rate }}</td>-->
                </template>
            </tr>
            <tr style="border-bottom: double;">
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">評価</td>
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '2')?.sanitation_inspection_evaluate }}</td>
                    <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '1')?.sanitation_inspection_evaluate }}</td>-->
                </template>
            </tr>
            <!-- 加減 -->
            <tr>
                <th style="background-color: #66cdaa;" rowspan="2">加減</th>
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">積極取り組み</td>
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '2')?.proactive_approach }}</td>
                    <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '1')?.proactive_approach }}</td>-->
                </template>
            </tr>
            <tr style="border-bottom: solid;">
                <td class="fixed-item-col2" style="border-bottom: dotted; border-right: solid;">ルール遵守</td>
                <template v-for="(data, index) in kpiDataList">
                    <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '2')?.compliance_with_rules }}</td>
                    <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ data.value_data.find(item => item.value_type === '1')?.compliance_with_rules }}</td>-->
                </template>
            </tr>
        </tbody>
    </table>
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
<script src="https://unpkg.com/vue-swal"></script>


<script>
    var app = new Vue({
        el: '#app',
        data: {
            shopList: [], // 店舗データ
            kpiDataList: [], // 一覧データ格納用
            shop_id: <?= $shop_id ?>, // 店舗ID
            revenueList: [], // 売上データ
            monthList: <?= $month_list ?>,
            choiceMonth: <?= json_encode($this_month) ?>, // 選択された月
            displayMonth: null, // 現在表示中データ月
            // 速報値
            preliminaryReportList: [],
            preEarningList: [],
            preCostsList: [],
            preLaborCostList: [],
            preSGAList: [],
            // 確定値
            csvDataList: [], // CSVデータ（確定値）
            earningList: [], // 売上データリスト
            costsList: [], // 原価データリスト
            laborCostList: [], // 人件費データリスト
            SGAList: [], // 販管費合計店舗毎リスト
            // 客数データ連携
            customerTargetList: [], // 客数データ格納
        },
        mounted(){
            this.getKpiDataList()
            .then(() => this.getShopList())
            .then(() => this.getRevenueList())
            .then(() => this.getSGA())
            .then(() => {
                this.getShopCSVData();
                this.getPreliminaryReport();
                this.customersDataCreate();
            });
        },
        filters:{
            percent(value){
                return Math.round(value * Math.pow(10, 2)) / Math.pow(10, 2);
            },
            addComma: function (value = 0) {
                if(value !== null){
                    return parseInt(value).toLocaleString();
                }
            },
        },
        methods: {
            // 全体データ取得
            getKpiDataList: function(){
                // 初期値は今月
                var vm = this; // vueのインスタンスを参照
                var params = new URLSearchParams();
                params.append('choice_month', this.choiceMonth['month']);
                axios.post("./database/get_kpi_management_list_all.php",params).then(function (response) {
                    // 速報値と確定値で別々のデータとして保存されているので、同じshop_idで分別してデータ加工
                    if(response.data){
                        var kpiDataList = []; // 加工後データ格納所
                        response.data.forEach((data) => {
                            const existData = kpiDataList.find(list => list.shop_data_id === data.shop_data_id);
                            if(existData){
                                existData.value_data.push({
                                    profit_target: data.profit_target,
                                    cost_target: data.cost_target,
                                    cost_rate_achievement: data.cost_rate_achievement,
                                    overtime_hours: data.overtime_hours,
                                    ms_score: data.ms_score,
                                    survey_acquisition_rate: data.survey_acquisition_rate,
                                    survey_overall: data.survey_overall,
                                    number_of_complaints: data.number_of_complaints,
                                    sanitation_inspection_rate: data.sanitation_inspection_rate,
                                    sanitation_inspection_evaluate: data.sanitation_inspection_evaluate,
                                    proactive_approach: data.proactive_approach,
                                    compliance_with_rules: data.compliance_with_rules,
                                    number_of_customer_target: data.number_of_customer_target,
                                    labor_cost_target: data.labor_cost_target,
                                    man_hour_sales_target: data.man_hour_sales_target,
                                    data_date: data.data_date,
                                    value_type: data.value_type, 
                                })
                            }else{
                                // ない場合は新たなデータを作成
                                kpiDataList.push({
                                    name: data.name,
                                    shop_data_id: data.shop_data_id,
                                    scraping_id: data.scraping_id,
                                    value_data: [{
                                        profit_target: data.profit_target,
                                        cost_target: data.cost_target,
                                        cost_rate_achievement: data.cost_rate_achievement,
                                        overtime_hours: data.overtime_hours,
                                        ms_score: data.ms_score,
                                        survey_acquisition_rate: data.survey_acquisition_rate,
                                        survey_overall: data.survey_overall,
                                        number_of_complaints: data.number_of_complaints,
                                        sanitation_inspection_rate: data.sanitation_inspection_rate,
                                        sanitation_inspection_evaluate: data.sanitation_inspection_evaluate,
                                        proactive_approach: data.proactive_approach,
                                        compliance_with_rules: data.compliance_with_rules,
                                        number_of_customer_target: data.number_of_customer_target,
                                        labor_cost_target: data.labor_cost_target,
                                        man_hour_sales_target: data.man_hour_sales_target,
                                        data_date: data.data_date,
                                        value_type: data.value_type, 
                                    }]
                                })
                            }
                        })
                        vm.kpiDataList = kpiDataList;
                    }
                    // vansan富士、鷹乃、ラジオがどうにもならないので力技
                    const radio_mishima = vm.kpiDataList.findIndex(element => element.name === "ラジオシティー三島駅前店");
                    if(radio_mishima !== -1){
                        const element = vm.kpiDataList.splice(radio_mishima, 1)[0];
                        const aka_kan = vm.kpiDataList.findIndex(element => element.name === "赤から函南店");
                        vm.kpiDataList.splice(aka_kan+1, 0, element); // 先頭に要素を挿入
                        console.log(vm.kpiDataList); // 要素が挿入された配列
                    }
                    const van_fuji = vm.kpiDataList.findIndex(element => element.name === "VANSAN富士店");
                    if(van_fuji !== -1){
                        const element = vm.kpiDataList.splice(van_fuji, 1)[0];
                        const van_hama = vm.kpiDataList.findIndex(element => element.name === "VANSAN ザザシテイ浜松店");
                        vm.kpiDataList.splice(van_hama+1, 0, element); // 先頭に要素を挿入
                        console.log(vm.kpiDataList); // 要素が挿入された配列
                    }
                    const kan = vm.kpiDataList.findIndex(element => element.name === "大韓食堂");
                    if(kan !== -1){
                        const element = vm.kpiDataList.splice(kan, 1)[0];
                        const ki_lala = vm.kpiDataList.findIndex(element => element.name === "VANSAN沼津");
                        vm.kpiDataList.splice(ki_lala+1, 0, element); // 先頭に要素を挿入
                        console.log(vm.kpiDataList); // 要素が挿入された配列
                    }
                });
                Vue.set(this, 'displayMonth', this.choiceMonth.month_string);
                return new Promise((resolve, reject) => {
                    resolve(); // Promiseが完了したことを通知
                });
            },

            // 店舗データ取得
            getShopList: function(){
                var vm = this; // vueのインスタンスを参照
                var params = new URLSearchParams();
                axios.post("./database/get_kpi_management_shop_list.php",params).then(function (response) {
                    Vue.set(vm, 'shopList', response.data ? response.data : null);
                });
                return new Promise((resolve, reject) => {
                    resolve(); // Promiseが完了したことを通知
                });
            },

            // 売上データ取得
            getRevenueList: function(){
                var vm = this; // vueのインスタンスを参照
                var params = new URLSearchParams();
                params.append('choice_month', this.choiceMonth['month']); // 今月のデータ取得
                
                return new Promise((resolve, reject) => {
                    axios.post("./database/get_kpi_management_revenue.php",params).then(function (response) {
                        vm.revenueList = response.data;
                        resolve();
                    }).catch(function(error){
                        reject(error);
                    });
                })
            },
            
            // 速報値:売上、原価、利益、人件費CSVインポートデータ取得
            getPreliminaryReport: function(){
                var vm = this; // vueのインスタンスを参照
                var params = new URLSearchParams();
                params.append('choice_month', this.choiceMonth['month']);
                axios.post("./database/get_kpi_management_preliminary_report_csv.php",params).then(function (response) {
                    Vue.set(vm, 'preliminaryReportList', response.data ? response.data : null);
                    // 計算結果リストの初期化
                    Vue.set(vm, 'preEarningList', []);
                    Vue.set(vm, 'preCostsList', []);
                    Vue.set(vm, 'preLaborCostList', []);
                    if(response.data){
                        let preEarnings = [];
                        let preCosts = [];
                        let preLaborCosts = [];
                        // foreachで一気に計算させてデータをリストに登録
                        vm.kpiDataList.forEach((shop)=>{
                            // 売上(データがないときはとりあえず0)
                            const earningFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '4111');
                            console.log(earningFind)
                            const budgetSum = vm.revenueList.find((data)=>shop.scraping_id === data.shop_id);
                            preEarnings.push({
                                'shop_id': shop.shop_data_id,
                                'earning': earningFind ? earningFind.value : 0,
                                'earning_rate': (earningFind && budgetSum) ? (Number(earningFind.value) / Number(budgetSum.budget_sum) * 100) : 0,
                            });
                            // 原価(売上と計算させて利益を出すためのデータ)
                            const purchaseFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '5211'); // 仕入高(5211)
                            const beginningInventoryFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '5111'); // 期首棚卸(5111)
                            const endingInventoryFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '5311'); // 期末棚卸(5311)
                            const cost = (Number(purchaseFind ? purchaseFind.value : 0) + 
                                        (Number(beginningInventoryFind ? beginningInventoryFind.value : 0) - Number(endingInventoryFind ? endingInventoryFind.value : 0))); // 原価の実績
                            preCosts.push({
                                'shop_id': shop.shop_data_id,
                                'profit': Number(earningFind ? earningFind.value : 0) - cost,
                                //'profit_rate': shop.profit_target ? (Number(earningFind ? earningFind.value : 0) - cost) / Number(shop.profit_target) : 0, // 予算比(profit/profit_target)
                                'cost': cost, // 当期売上原価
                            });
                            // 人件費率(SUM(給与手当,雑給,事務員給与,従業員給与,法定福利費,厚生費,退職金)/売上*100)6111, 6311, 6212, 6213, 6312, 6226, 6119
                            const salaryFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '6111'); // 給与手当(6111)
                            const miscellaneousWagesFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '6311'); // 雑給(6311)
                            const clerkSalaryFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '6212'); // 事務員給与(6212)
                            const employeeSalaryFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '6213'); // 従業員給与,(6213)
                            const welfareFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '6312'); // 法定福利費(6312)
                            const publicWelfareFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '6226'); // 厚生費(6226)
                            const retirementFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '6119'); // 退職金(6119)
                            preLaborCosts.push({
                                'shop_id': shop.shop_data_id,
                                'labor_cost': earningFind
                                                ? ((Number(salaryFind ? salaryFind.value : 0) + Number(miscellaneousWagesFind ? miscellaneousWagesFind.value : 0) +
                                                Number(clerkSalaryFind ? clerkSalaryFind.value : 0) + Number(employeeSalaryFind ? employeeSalaryFind.value : 0) + 
                                                Number(welfareFind ? welfareFind.value : 0) + Number(publicWelfareFind ? publicWelfareFind.value : 0) + 
                                                Number(retirementFind ? retirementFind.value : 0)) / Number(earningFind ? earningFind.value : 0)) * 100
                                                : 0,
                            });
                        })
                        // 計算結果をそれぞれのリストに登録
                        Vue.set(vm, 'preEarningList', preEarnings);
                        Vue.set(vm, 'preCostsList', preCosts);
                        Vue.set(vm, 'preLaborCostList', preLaborCosts);
                    }
                });
                
            },

            // 確定値:売上、原価、利益、人件費CSVインポートデータ取得
            getShopCSVData: function(){
                var vm = this; // vueのインスタンスを参照
                var params = new URLSearchParams();
                params.append('choice_month', this.choiceMonth['month']);
                axios.post("./database/get_kpi_management_csv.php",params).then(function (response) {
                    Vue.set(vm, 'csvDataList', response.data ? response.data : null);
                    // 計算結果リストの初期化
                    Vue.set(vm, 'earningList', []);
                    Vue.set(vm, 'costsList', []);
                    Vue.set(vm, 'laborCostList', []);
                    if(response.data){
                        let earnings = [];
                        let costs = [];
                        let laborCosts = [];
                        // foreachで一気に計算させてデータをリストに登録
                        vm.kpiDataList.forEach((shop)=>{
                            // 売上(データがないときはとりあえず0)
                            const earningFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '4111');
                            const budgetSum = vm.revenueList.find((data)=>shop.scraping_id === data.shop_id);
                            earnings.push({
                                'shop_id': shop.shop_data_id,
                                'earning': earningFind ? earningFind.value : 0,
                                'earning_rate': (earningFind && budgetSum) ? (Number(earningFind.value) / Number(budgetSum.budget_sum) * 100) : 0,
                            });
                            // 原価(売上と計算させて利益を出すためのデータ)
                            const purchaseFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '5211'); // 仕入高(5211)
                            const beginningInventoryFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '5111'); // 期首棚卸(5111)
                            const endingInventoryFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '5311'); // 期末棚卸(5311)
                            const cost = (Number(purchaseFind ? purchaseFind.value : 0) + 
                                        (Number(beginningInventoryFind ? beginningInventoryFind.value : 0) - Number(endingInventoryFind ? endingInventoryFind.value : 0)));
                            costs.push({
                                'shop_id': shop.shop_data_id,
                                'profit': Number(earningFind ? earningFind.value : 0) - cost,
                                //'profit_rate': shop.profit_target ? (Number(earningFind ? earningFind.value : 0) - cost) / Number(shop.profit_target) : 0, // 予算比(profit/profit_target)
                                'cost': cost,
                                'currentCostOfSales': Number(beginningInventoryFind) + Number(purchaseFind) - Number(endingInventoryFind), // 当期売上原価
                            });
                            // 人件費率(SUM(給与手当,雑給,事務員給与,従業員給与,法定福利費,厚生費,退職金)/売上*100)6111, 6311, 6212, 6213, 6312, 6226, 6119
                            const salaryFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '6111'); // 給与手当(6111)
                            const miscellaneousWagesFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '6311'); // 雑給(6311)
                            const clerkSalaryFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '6212'); // 事務員給与(6212)
                            const employeeSalaryFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '6213'); // 従業員給与,(6213)
                            const welfareFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '6312'); // 法定福利費(6312)
                            const publicWelfareFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '6226'); // 厚生費(6226)
                            const retirementFind = response.data.find((data)=>shop.shop_data_id === data.shop_id && data.expense_code === '6119'); // 退職金(6119)
                            laborCosts.push({
                                'shop_id': shop.shop_data_id,
                                // 0の時は必然的に0にしてしまう
                                'labor_cost': earningFind
                                                ? ((Number(salaryFind ? salaryFind.value : 0) + Number(miscellaneousWagesFind ? miscellaneousWagesFind.value : 0) +
                                                Number(clerkSalaryFind ? clerkSalaryFind.value : 0) + Number(employeeSalaryFind ? employeeSalaryFind.value : 0) + 
                                                Number(welfareFind ? welfareFind.value : 0) + Number(publicWelfareFind ? publicWelfareFind.value : 0) + 
                                                Number(retirementFind ? retirementFind.value : 0)) / Number(earningFind ? earningFind.value : 0)) * 100
                                                : 0,
                            });
                        })
                        Vue.set(vm, 'earningList', earnings);
                        Vue.set(vm, 'costsList', costs);
                        Vue.set(vm, 'laborCostList', laborCosts);
                        console.log(costs);
                    }
                });
            },

            // 販管費取得
            getSGA: function(){
                var vm = this; // vueのインスタンスを参照
                var params = new URLSearchParams();
                params.append('choice_month', this.choiceMonth['month']);
                console.log('販管費')
                // 速報値
                axios.post("./database/get_kpi_management_pre_SGA.php",params).then(function (response) {
                    // リストの初期化
                    Vue.set(vm, 'preSGAList', []);
                    console.log('速報値')
                    console.log(response.data)
                    if(response.data){
                        response.data.forEach((sga)=>{
                            const exist_data = vm.preSGAList.findIndex((list)=>{
                                return list.shop_id === sga.shop_id
                            })
                            if(exist_data === -1){
                                // データがない場合作成
                                vm.preSGAList.push({
                                    'shop_id': sga.shop_id,
                                    'value': Number(sga.value),
                                })
                            }else{
                                vm.preSGAList[exist_data].value = vm.preSGAList[exist_data].value + Number(sga.value)
                            }
                        })
                        console.log(vm.preSGAList)
                    }
                });

                // 確定値
                axios.post("./database/get_kpi_management_SGA.php",params).then(function (response) {
                    console.log('確定値')
                    // リストの初期化
                    Vue.set(vm, 'SGAList', []);
                    if(response.data){
                        response.data.forEach((sga)=>{
                            const exist_data = vm.SGAList.findIndex((list)=>{
                                return list.shop_id === sga.shop_id
                            })
                            if(exist_data === -1){
                                // データがない場合作成
                                vm.SGAList.push({
                                    'shop_id': sga.shop_id,
                                    'value': Number(sga.value),
                                })
                            }else{
                                vm.SGAList[exist_data].value = vm.SGAList[exist_data].value + Number(sga.value)
                            }
                        })
                        console.log(vm.SGAList)
                    }
                });
                return new Promise((resolve, reject) => {
                    resolve(); // Promiseが完了したことを通知
                });
            },

            // 客数データ加工
            customersDataCreate: function(){
                var vm = this; // vueのインスタンスを参照
                var params = new URLSearchParams();
                params.append('choice_month', this.choiceMonth['month']);
                axios.post("./database/get_kpi_management_customers.php",params).then(function (response) {
                    // リストの初期化
                    Vue.set(vm, 'customerTargetList', []);
                    console.log(response)
                    if(response.data){
                        Vue.set(vm, 'customerTargetList', response.data);
                    }
                });
            },

            // 新規作成画面移動
            onClickCreate: function(){
                window.location.href = './kpi_management_create.php?shop_id=' + '<?= $shop_record ?>';
            },

            // CSV取り込み画面移動
            onClickCSV: function(){
                window.location.href = './kpi_management_csv.php?shop_id=' + '<?= $shop_record ?>';
            },

            // 店舗別ページ移動
            onClickShop: function(){
                window.location.href = './kpi_management_index_shop.php?shop_id=' + '<?= $shop_record ?>';
            },

            // 業態別ページ移動
            onClickBusinessType: function(){
                window.location.href = './kpi_management_index_type.php?shop_id=' + '<?= $shop_record ?>';
            },

            // 月選択表示
            onClickDisplay: function(){
                this.getKpiDataList()
                .then(() => this.getShopList())
                .then(() => this.getRevenueList())
                .then(() => this.getSGA())
                .then(() => {
                    this.getShopCSVData();
                    this.getPreliminaryReport();
                    this.customersDataCreate();
                });
            }
        },

    })
</script>
