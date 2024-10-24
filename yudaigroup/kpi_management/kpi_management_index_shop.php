<?php
include "../inc/auth.inc";

include "../AdminLTE/class/ClassLoader.php";
include "../csvconvert/add_shop_list.php";

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
// 今月
$this_month = array(
    "month_string" =>  date("Y年m月"),
    "month" => date("Y-m-01"),
);

$shop_all = $dbio->fetchShopList();
foreach ($shop_all as $key => $one) {
    $shop_list[$key]['id'] = $one['id'];
    $shop_list[$key]['name'] = $one['name'];
    $shop_list[$key]['scraping_id'] = $one['scraping_id'];
    if ($target_shop == $one['id']) {
        $shop_list[$key]['selected'] = 'selected';
    }
}
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

// どこまで登録したかを記録するためのshop_id
$shop_record = $_GET['shop_id'];

foreach ($add_shop_list as $value) {
    // 飲食のみ抜き出す(101以上1000以内 かつ 300代以外)
    if((((int)$value['id'] <= 1000) && ((int)$value['id'] >= 101)) && (floor((int)$value['id']/100) !== 3.0)) {
        // 更に閉店済み店舗は飛ばす
        if((int)$value['id'] !== 107 && (int)$value['id'] !== 201) {
            $selected = ($value['id'] == $shop_id) ? 'selected' : '';
            $select_option .= <<<HTML
<option value="{$value['id']}" {$selected}>{$value['name']}</option>
HTML;
        }
    }
}
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

    input,
    .main_table {

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
    }

    .input-list-header {
        position:sticky;
        top: 0;
        background-color: #63baa8;
    }
</style>
<div id="app" v-cloak>
    <h1>店舗別データ</h1>
    <div>
        <button type="button" class="btn btn-primary" @click="onClickCreate">手動入力画面</button>
        <button type="button" class="btn btn-info" style="margin-right: 30px;" @click="onClickCSV">速報値CSV取込</button>
        <button type="button" class="btn btn-danger" @click="onClickAll">全体</button>
        <button type="button" class="btn btn-warning" @click="onClickBusinessType">業態別</button>
    </div>
    <div style="display: flex; margin-top: 10px;">
        <span style="font-size: 20px;">店舗選択:</span>
        <!--<select name="target_shop" class="form-control box-size" v-model="shop_id">
            <?php foreach ($shop_list as $val): ?>
                <option id value="<?= $val['id'] ?>" <?= $val['selected'] ?>><?= $val['name'] ?></option>
            <?php endforeach; ?>
        </select>-->
        <select name="target_shop" class="form-control box-size" v-model="shop_id">
            <? echo $select_option ?>
        </select>
        <span style="font-size: 20px; padding-left: 10px;">月選択:</span>
        <select class="form-control box-size" style="margin-bottom: 10px;" v-model="choiceMonth" @change="changeMonth">
            <option v-for="(month, index) in monthList" :key="index" :value="month">{{ month.month_string }}</option>
        </select>
        <button type="button" style="margin-left: 10px;" class="btn btn-info" @click="onClickDisplay">データ表示</button>
    </div>
    <div>現在表示中のデータ: {{ displayShop }}({{ displayMonth }})</div>
    <!-- 入力欄 -->
    <div style="width: 500px;">
        <figure>
            <table class="table table-margin" style="border: solid; overflow:auto; max-height: 80vh;">
                <colgroup>
                    <col class="kpi-index" style="background-color: #66cdaa; width: 150px;"></col>
                    <col class="item-name" style="background-color: #66cdaa; width: 150px;"></col>
                    <col class="item-input" style="width: 150px;"></col>
                    <col class="item-input" style="width: 150px;"></col>
                </colgroup>
                <thead class="input-list-header" style="background-color: #fff1cb;">
                    <th style="border-bottom: solid;">指標</th>
                    <th style="border-bottom: solid;">項目</th>
                    <th style="border-bottom: solid;">速報</th>
                    <th style="border-bottom: solid;">確定</th>
                </thead>
                <tbody>
                    <!-- 売上 -->
                    <tr>
                        <th rowspan="3">売上</th>
                        <td style="border-bottom: dotted; border-right: solid;">予算</td>
                        <td class="input-data" style="border-right: dotted; border-bottom: dotted;" colspan="2">{{ revenue[0]?.budget_sum | addComma }}</td>
                    </tr>
                    <tr>
                        <td style="border-bottom: dotted; border-right: solid;">速報/確定</td>
                        <td class="input-data" style="border-bottom: dotted; border-right: dotted;" :style="(pre_earnings_achievement <= revenue[0]?.budget_sum) ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'">
                            {{ pre_earnings_achievement | addComma }}
                        </td>
                        <td class="input-data" style="border-bottom: dotted; border-right: solid;" :style="(earnings_achievement <= revenue[0]?.budget_sum) ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'">
                            {{ earnings_achievement | addComma }}
                        </td>
                    </tr>
                    <tr style="border-bottom: double;">
                        <td style="border-bottom: dotted; border-right: solid;">予算比</td>
                        <td class="input-data" style="border-bottom: dotted; border-right: dotted;">
                            {{ ((pre_earnings_achievement / revenue[0]?.budget_sum) * 100) | percent }}%</td>
                        <td class="input-data">
                            {{ ((earnings_achievement / revenue[0]?.budget_sum) * 100) | percent }}%
                        </td>
                    </tr>
                    <!-- 店舗利益 -->
                    <tr>
                        <th rowspan="3">店舗利益</th>
                        <td style="border-bottom: dotted; border-right: solid;">予算</td>
                        <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: solid;">{{ profit_target | addComma }}</td>
                    </tr>
                    <tr>
                        <td style="border-bottom: dotted; border-right: solid;">速報/確定</td>
                        <td
                            class="input-data"
                            style="border-bottom: dotted; border-right: dotted;"
                            :style="pre_profit_achievement <= profit_target ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'"
                        >{{ pre_profit_achievement | addComma }}</td>
                        <td
                            class="input-data"
                            style="border-bottom: dotted; border-right: solid;"
                            :style="profit_achievement <= profit_target ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'"
                        >{{ profit_achievement | addComma }}</td>
                    </tr>
                    <tr style="border-bottom: double;">
                        <td style="border-bottom: dotted; border-right: solid;">予算比</td>
                        <td class="input-data" style="border-bottom: dotted; border-right: dotted;">
                            {{ isFinite(Number(pre_profit_achievement) / Number(pre_profit_target)) ? (Number(pre_profit_achievement) / Number(pre_profit_target)) * 100 : 0 | percent }}%
                        </td>
                        <td class="input-data" style="border-bottom: dotted; border-right: solid;">
                            {{ isFinite(Number(profit_achievement) / Number(profit_target)) ? (Number(profit_achievement) / Number(profit_target)) * 100 : 0 | percent }}%
                        </td>
                    </tr>
                    <!-- 客数 -->
                    <tr>
                        <th rowspan="3">客数</th>
                        <td style="border-bottom: dotted; border-right: solid;">目標</td>
                        <td class="input-data" style="border-bottom: dotted; border-right: solid;" colspan="2">
                            {{ number_of_customer_target | addComma }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border-bottom: dotted; border-right: solid;">実績</td>
                        <td
                            class="input-data"
                            style="border-bottom: dotted; border-right: dotted;"
                            :style="Number(number_of_customer_target) >= Number(customerTarget?.customers_num) ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'"
                            colspan="2"
                        >
                            {{ customerTarget?.customers_num | addComma }}
                        </td>
                    </tr>
                    <tr style="border-bottom: double;">
                        <td style="border-bottom: dotted; border-right: solid;">目標実績差</td>
                        <!--<td class="input-data" style="border-right: dotted;">
                            {{ Number(customerTarget?.customers_num) - Number(pre_number_of_customer_target) | addComma }}
                        </td>-->
                        <td class="input-data" style="border-bottom: dotted; border-right: solid;" colspan="2">
                            {{ Number(customerTarget?.customers_num) - Number(number_of_customer_target) | addComma }}
                        </td>
                    </tr>
                    <!-- 原価率 -->
                    <tr>
                        <th rowspan="3">原価率</th>
                        <td style="border-bottom: dotted; border-right: solid;">目標</td>
                        <td class="input-data" style="border-bottom: dotted;">
                            {{ isNaN(pre_cost / revenue[0]?.budget_sum)
                                ? 0
                                : ((pre_cost / revenue[0]?.budget_sum) * 100) | percent }}%
                        </td>
                        <td class="input-data" style="border-bottom: dotted; border-right: solid;">
                            {{ isNaN(cost / revenue[0]?.budget_sum)
                                ? 0
                                : ((cost / revenue[0]?.budget_sum) * 100) | percent }}%
                        </td>
                    </tr>
                    <tr>
                        <td style="border-bottom: dotted; border-right: solid;">速報/確定</td>
                        <td
                            class="input-data"
                            style="border-bottom: dotted; border-right: dotted;"
                            :style="((isNaN(pre_cost / pre_earnings_achievement) ? 0 : ((pre_cost / pre_earnings_achievement) * 100))) <= (isNaN(pre_cost / revenue[0]?.budget_sum) ? 0 : ((pre_cost / revenue[0]?.budget_sum) * 100)) ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'"
                        >
                            {{ (isNaN(pre_cost / pre_earnings_achievement)
                                ? 0
                                : ((pre_cost / pre_earnings_achievement) * 100)) | percent }}%
                        </td>
                        <td
                            class="input-data"
                            style="border-bottom: dotted; border-right: solid;"
                            :style="((isNaN(cost / earnings_achievement) ? 0 : ((cost / earnings_achievement) * 100))) <= (isNaN(cost / revenue[0]?.budget_sum) ? 0 : ((cost / revenue[0]?.budget_sum) * 100)) ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'"
                        >
                            {{ (isNaN(cost / earnings_achievement)
                                ? 0
                                : ((cost / earnings_achievement) * 100)) | percent }}%
                        </td>
                    </tr>
                    <tr style="border-bottom: double;">
                        <td style="border-bottom: dotted; border-right: solid;">目標実績差</td>
                        <td class="input-data" style="border-bottom: dotted; border-right: dotted;">
                            {{ (isNaN(pre_cost / pre_earnings_achievement)
                                ? 0
                                : ((pre_cost / pre_earnings_achievement) * 100))
                                - (isNaN(pre_cost / revenue[0]?.budget_sum)
                                ? 0
                                : ((pre_cost / revenue[0]?.budget_sum) * 100)) | percent }}%
                        </td>
                        <td class="input-data" style="border-bottom: dotted; border-right: solid;">
                            {{ (isNaN(cost / earnings_achievement)
                                ? 0
                                : ((cost / earnings_achievement) * 100))
                                - (isNaN(cost / revenue[0]?.budget_sum)
                                ? 0
                                : ((cost / revenue[0]?.budget_sum) * 100)) | percent }}%
                        </td>
                    </tr>
                    <!-- 人件費 -->
                    <tr>
                        <th rowspan="4">人件費</th>
                        <td style="border-bottom: dotted; border-right: solid;">人件費率 目標</td>
                        <td class="input-data" style="border-bottom: dotted; border-right: dotted;">
                            {{ labor_cost_target ?? 0 }}%
                        </td>
                        <td class="input-data" style="border-bottom: dotted; border-right: solid;">
                            {{ labor_cost_target ?? 0 }}%
                        </td>
                    </tr>
                    <tr>
                        <td style="border-bottom: dotted; border-right: solid;">実績</td>
                        <td
                            class="input-data"
                            style="border-bottom: dotted; border-right: dotted;"
                            :style="((isNaN((pre_labor_cost / pre_earnings_achievement) * 100) ? 0 : ((pre_labor_cost / pre_earnings_achievement) * 100)) <= (labor_cost_target ?? 0)) ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'"
                        >
                            {{ isNaN((pre_labor_cost / pre_earnings_achievement) * 100) ? 0 : ((pre_labor_cost / pre_earnings_achievement) * 100) | percent }}%
                        </td>
                        <td
                            class="input-data"
                            style="border-bottom: dotted; border-right: solid;"
                            :style="((isNaN((labor_cost / earnings_achievement) * 100) ? 0 : ((labor_cost / earnings_achievement) * 100)) <= (labor_cost_target ?? 0)) ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'"
                        >
                            {{ isNaN((labor_cost / earnings_achievement) * 100) ? 0 : ((labor_cost / earnings_achievement) * 100) | percent }}%
                        </td>
                    </tr>
                    <tr>
                        <td style="border-bottom: dotted; border-right: solid;">人時売上 目標</td>
                        <!--<td class="input-data" style="border-right: dotted;">
                            {{ man_hour_sales_target ?? 0 }}
                        </td>-->
                        <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: solid;">
                            {{ man_hour_sales_target ?? 0 }}
                        </td>
                    </tr>
                    <tr style="border-bottom: double;">
                        <td style="border-bottom: dotted; border-right: solid;">実績</td>
                        <!--<td class="input-data" style="border-right: dotted;">
                            {{ (isNaN(Number(revenue[0]?.work_time_sum)) || revenue[0]?.work_time_sum === "0") ? 0 : (pre_earnings_achievement / Number(revenue[0]?.work_time_sum)) | addComma }}
                        </td>-->
                        <td
                            class="input-data"
                            colspan="2"
                            style="border-bottom: dotted; border-right: solid;"
                            :style="(((isNaN(Number(revenue[0]?.work_time_sum)) || revenue[0]?.work_time_sum === '0') ? 0 : (earnings_achievement / Number(revenue[0]?.work_time_sum))) <= (man_hour_sales_target ?? 0)) ? 'background-color: #efbdca;' : 'background-color: #bde7ef;'"
                        >
                            {{ (isNaN(Number(revenue[0]?.work_time_sum)) || revenue[0]?.work_time_sum === "0") ? 0 : (earnings_achievement / Number(revenue[0]?.work_time_sum)) | addComma }}
                        </td>
                    </tr>
                    <!-- 労務 -->
                    <tr style="border-bottom: double;">
                        <th>労務</th>
                        <td style="border-bottom: dotted; border-right: solid;">残業時間</td>
                        <td class="input-data" colspan="2" style="border-right: dotted;">{{ pre_overtime_hours }}</td>
                        <!--<td class="input-data">{{ overtime_hours }}</td>-->
                    </tr>
                    <!-- MS -->
                    <tr style="border-bottom: double;">
                        <th>MS</th>
                        <td style="border-bottom: dotted; border-right: solid;">得点</td>
                        <td class="input-data" colspan="2" style="border-right: dotted;">{{ pre_ms_score }}</td>
                        <!--<td class="input-data">{{ ms_score }}</td>-->
                    </tr>
                    <!-- アンケート -->
                    <tr>
                        <th rowspan="2">アンケート</th>
                        <td style="border-bottom: dotted; border-right: solid;">取得率</td>
                        <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: dotted;">{{ pre_survey_acquisition_rate }}</td>
                        <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ survey_acquisition_rate }}</td>-->
                    </tr>
                    <tr style="border-bottom: double;">
                        <td style="border-bottom: dotted; border-right: solid;">総合</td>
                        <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: dotted;">{{ pre_survey_overall }}</td>
                        <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ survey_overall }}</td>-->
                    </tr>
                    <!-- クレーム -->
                    <tr style="border-bottom: double;">
                        <th>クレーム</th>
                        <td style="border-bottom: dotted; border-right: solid;">件数</td>
                        <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: dotted;">{{ pre_number_of_complaints }}</td>
                        <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ number_of_complaints }}</td>-->
                    </tr>
                    <!-- 衛生検査 -->
                    <tr>
                        <th rowspan="2">衛生検査</th>
                        <td style="border-bottom: dotted; border-right: solid;">適合率</td>
                        <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: dotted;">{{ pre_sanitation_inspection_rate }}</td>
                        <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ sanitation_inspection_rate }}</td>-->
                    </tr>
                    <tr style="border-bottom: double;">
                        <td style="border-bottom: dotted; border-right: solid;">評価</td>
                        <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: dotted;">{{ pre_sanitation_inspection_evaluate }}</td>
                        <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ sanitation_inspection_evaluate }}</td>-->
                    </tr>
                    <!-- 加減 -->
                    <tr>
                        <th rowspan="2">加減</th>
                        <td style="border-bottom: dotted; border-right: solid;">積極取り組み</td>
                        <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: dotted;">{{ pre_proactive_approach }}</td>
                        <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ proactive_approach }}</td>-->
                    </tr>
                    <tr style="border-bottom: solid;">
                        <td style="border-bottom: dotted; border-right: solid;">ルール遵守</td>
                        <td class="input-data" colspan="2" style="border-bottom: dotted; border-right: dotted;">{{ pre_compliance_with_rules }}</td>
                        <!--<td class="input-data" style="border-bottom: dotted; border-right: solid;">{{ compliance_with_rules }}</td>-->
                    </tr>
                </tbody>
            </table>
        </figure>
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
            shop_id: <?= $shop_id ?>, // 店舗ID
            /** 速報値 */
            // 利益
            pre_profit_achievement: null, // 実績(営業利益)
            pre_profit_target: null, // 目標
            // 売上
            pre_earnings_achievement: null, // 実績
            pre_earnings_rate: null, // 予算比
            pre_cost: null, // 原価
            pre_cost_rate_achievement: null, // 原価実績
            pre_cost_target: null, // 原価目標
            pre_labor_cost: null, // 人件費
            pre_overtime_hours: null, // 残業時間
            pre_ms_score: null, // MS得点
            pre_survey_acquisition_rate: null, // アンケート取得率
            pre_survey_overall: null, // アンケート総合
            pre_number_of_complaints: null, // クレーム件数
            pre_sanitation_inspection_rate: null, // 衛生検査適合率
            pre_sanitation_inspection_evaluate: null, // 衛生検査評価
            pre_proactive_approach: null, // 積極取り組み
            pre_compliance_with_rules: null, // ルール遵守
            pre_number_of_customer_target: null,
            /** 確定値 */
            // 利益
            profit_achievement: null, // 実績(営業利益)
            profit_target: null, // 目標
            // 売上
            earnings_achievement: null, // 実績
            earnings_rate: null, // 予算比
            cost: null, // 原価
            cost_rate_achievement: null, // 原価実績
            cost_target: null, // 原価目標
            labor_cost: null, // 人件費
            overtime_hours: null, // 残業時間
            ms_score: null, // MS得点
            survey_acquisition_rate: null, // アンケート取得率
            survey_overall: null, // アンケート総合
            number_of_complaints: null, // クレーム件数
            sanitation_inspection_rate: null, // 衛生検査適合率
            sanitation_inspection_evaluate: null, // 衛生検査評価
            proactive_approach: null, // 積極取り組み
            compliance_with_rules: null, // ルール遵守
            number_of_customer_target: null,
            // 客数
            customerTarget: null,
            // 人件費　目標
            labor_cost_target: null,
            man_hour_sales_target: null,

            data_date: '<?= date('Y-m-d') ?>', // 何年何月のデータか
            shop_list: <?= json_encode($shop_list) ?>,
            revenue: [],
            monthList: <?= $month_list ?>,
            choiceMonth: <?= json_encode($this_month) ?>, // 選択された月
            // 現在表示中データ
            displayShop: null,
            displayMonth: null,
        },
        mounted(){
            this.onClickDisplay();
        },
        filters: {
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
            // 店舗別月別データ表示
            onClickDisplay: function(){
                var vm = this; // vueのインスタンスを参照
                // 選択された店舗のデータ取得
                var params = new URLSearchParams();
                /** 初期化処理 */
                // 速報値
                Vue.set(vm, 'pre_profit_target', null);
                Vue.set(vm, 'pre_cost_rate_achievement', null);
                Vue.set(vm, 'pre_cost_target', null);
                Vue.set(vm, 'pre_overtime_hours', null);
                Vue.set(vm, 'pre_ms_score', null);
                Vue.set(vm, 'pre_survey_acquisition_rate', null);
                Vue.set(vm, 'pre_survey_overall', null);
                Vue.set(vm, 'pre_number_of_complaints', null);
                Vue.set(vm, 'pre_sanitation_inspection_rate', null);
                Vue.set(vm, 'pre_sanitation_inspection_evaluate', null);
                Vue.set(vm, 'pre_proactive_approach', null);
                Vue.set(vm, 'pre_compliance_with_rules', null);
                Vue.set(vm, 'pre_number_of_customer_target', null);
                // 確定値
                Vue.set(vm, 'profit_target', null);
                Vue.set(vm, 'cost_rate_achievement', null);
                Vue.set(vm, 'cost_target', null);
                Vue.set(vm, 'overtime_hours', null);
                Vue.set(vm, 'ms_score', null);
                Vue.set(vm, 'survey_acquisition_rate', null);
                Vue.set(vm, 'survey_overall', null);
                Vue.set(vm, 'number_of_complaints', null);
                Vue.set(vm, 'sanitation_inspection_rate', null);
                Vue.set(vm, 'sanitation_inspection_evaluate', null);
                Vue.set(vm, 'proactive_approach', null);
                Vue.set(vm, 'compliance_with_rules', null);
                Vue.set(vm, 'number_of_customer_target', null);
                Vue.set(vm, 'labor_cost_target', null);
                Vue.set(vm, 'man_hour_sales_target', null);
                /** 初期化処理終了 */

                params.append('shop_id', this.shop_id);
                params.append('choice_month', this.choiceMonth.month);
                axios.post("./database/get_kpi_management.php",params).then(function (response) {
                    const inputData = response;
                    if(response.data){
                        response.data.forEach((data) => {
                            if(data.value_type === '2'){
                                // 速報値
                                Vue.set(vm, 'pre_profit_target', data.profit_target);
                                Vue.set(vm, 'pre_cost_rate_achievement', data.cost_rate_achievement);
                                Vue.set(vm, 'pre_cost_target', data.cost_target);
                                Vue.set(vm, 'pre_overtime_hours', data.overtime_hours);
                                Vue.set(vm, 'pre_ms_score', data.ms_score);
                                Vue.set(vm, 'pre_survey_acquisition_rate', data.survey_acquisition_rate);
                                Vue.set(vm, 'pre_survey_overall', data.survey_overall);
                                Vue.set(vm, 'pre_number_of_complaints', data.number_of_complaints);
                                Vue.set(vm, 'pre_sanitation_inspection_rate', data.sanitation_inspection_rate);
                                Vue.set(vm, 'pre_sanitation_inspection_evaluate', data.sanitation_inspection_evaluate);
                                Vue.set(vm, 'pre_proactive_approach', data.proactive_approach);
                                Vue.set(vm, 'pre_compliance_with_rules', data.compliance_with_rules);
                                Vue.set(vm, 'pre_number_of_customer_target', data.number_of_customer_target);
                            }else{
                                // 確定値
                                Vue.set(vm, 'profit_target', data.profit_target);
                                Vue.set(vm, 'cost_rate_achievement', data.cost_rate_achievement);
                                Vue.set(vm, 'cost_target', data.cost_target);
                                Vue.set(vm, 'overtime_hours', data.overtime_hours);
                                Vue.set(vm, 'ms_score', data.ms_score);
                                Vue.set(vm, 'survey_acquisition_rate', data.survey_acquisition_rate);
                                Vue.set(vm, 'survey_overall', data.survey_overall);
                                Vue.set(vm, 'number_of_complaints', data.number_of_complaints);
                                Vue.set(vm, 'sanitation_inspection_rate', data.sanitation_inspection_rate);
                                Vue.set(vm, 'sanitation_inspection_evaluate', data.sanitation_inspection_evaluate);
                                Vue.set(vm, 'proactive_approach', data.proactive_approach);
                                Vue.set(vm, 'compliance_with_rules', data.compliance_with_rules);
                                Vue.set(vm, 'number_of_customer_target', data.number_of_customer_target);
                                Vue.set(vm, 'labor_cost_target', data.labor_cost_target);
                                Vue.set(vm, 'man_hour_sales_target', data.man_hour_sales_target);
                            }
                        })
                    }
                });
                // 売上データの取得もし直す
                this.getShopRevenue();
                this.getShopCSVData();
                this.getPreliminaryReport();
                this.customersDataCreate();
                Vue.set(this, 'displayShop', this.shop_list.find((shop)=>Number(shop.id) === Number(this.shop_id)).name);
                Vue.set(this, 'displayMonth', this.choiceMonth.month_string);
            },

            // 売上データ取得
            getShopRevenue: function(){
                var vm = this; // vueのインスタンスを参照
                var params = new URLSearchParams();
                const shopData = this.shop_list.find((shop)=>shop.id === String(this.shop_id));
                params.append('scraping_id', shopData.scraping_id);
                params.append('choice_month', this.choiceMonth['month']);

                return new Promise((resolve, reject) => {
                    axios.post("./database/get_kpi_management_shop_revenue.php",params).then(function (response) {
                        console.log(response.data);
                        Vue.set(vm, 'revenue', response.data ? response.data : null);
                    }).catch(function(error){
                        reject(error);
                    });
                })
                
            },

            // 確定値:売上、原価、利益、人件費CSVインポートデータ取得
            async getShopCSVData(){
                var vm = this; // vueのインスタンスを参照
                var params = new URLSearchParams();
                // 初期化
                Vue.set(vm, 'cost', null);
                Vue.set(vm, 'profit_achievement', null);
                Vue.set(vm, 'labor_cost', null);
                Vue.set(vm, 'earnings_achievement', null);
                params.append('choice_month', this.choiceMonth['month']);
                params.append('shop_id', this.shop_id);

                // ここでまず販管費のデータを取得する
                let sgaSum = 0 // 販管費合計
                await axios.post("./database/get_kpi_management_SGA.php",params).then(function (response) {
                    console.log('確定値')
                    console.log(response.data)
                    if(response.data){
                        response.data.forEach((sga)=>{
                            sgaSum = sgaSum + Number(sga.value)
                        })
                    }
                });

                await axios.post("./database/get_kpi_management_csv.php",params).then(function (response) {
                    console.log(response.data);
                    // foreachで回して場合分けして一個一個登録した方が後が早い説
                    let cost = 0; // 原価計算結果
                    let laborCost = 0; // 人件費計算
                    if(response.data){
                        response.data.forEach((data)=>{
                            // 指定店舗id一致且つ各CSVのコードが一致するもの
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '4111'){
                                Vue.set(vm, 'earnings_achievement', data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '5111'){
                                cost = cost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '5211'){
                                cost = cost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '5311'){
                                cost = cost - Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '6111'){
                                laborCost = laborCost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '6311'){
                                laborCost = laborCost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '6212'){
                                laborCost = laborCost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '6213'){
                                laborCost = laborCost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '6312'){
                                laborCost = laborCost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '6226'){
                                laborCost = laborCost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '6119'){
                                laborCost = laborCost + Number(data.value);
                            }
                        })
                        console.log(laborCost);
                        Vue.set(vm, 'cost', cost);
                        // 営業利益計算
                        Vue.set(vm, 'profit_achievement', (vm.earnings_achievement-cost) - sgaSum); // 利益 = 売上:4111 - 原価:(仕入高:5211 + 期首棚卸:5111 - 期末棚卸:5311)
                        Vue.set(vm, 'labor_cost', laborCost);
                    }
                });
            },

            // 速報値:売上、原価、利益、人件費CSVインポートデータ取得
            async getPreliminaryReport(){
                var vm = this; // vueのインスタンスを参照
                var params = new URLSearchParams();
                // 初期化
                Vue.set(vm, 'pre_cost', null);
                Vue.set(vm, 'pre_profit_achievement', null);
                Vue.set(vm, 'pre_labor_cost', null);
                Vue.set(vm, 'pre_earnings_achievement', null);
                params.append('choice_month', this.choiceMonth['month']);
                params.append('shop_id', this.shop_id);

                // ここでまず販管費のデータを取得する
                let sgaSum = 0 // 販管費合計
                await axios.post("./database/get_kpi_management_pre_SGA.php",params).then(function (response) {
                    console.log('速報値')
                    console.log(response.data)
                    if(response.data){
                        response.data.forEach((sga)=>{
                            sgaSum = sgaSum + Number(sga.value)
                        })
                    }
                });

                await axios.post("./database/get_kpi_management_preliminary_report_csv.php",params).then(function (response) {
                    console.log(response.data);
                    // foreachで回して場合分けして一個一個登録した方が後が早い説
                    let cost = 0; // 原価計算結果
                    let laborCost = 0; // 人件費計算
                    if(response.data){
                        response.data.forEach((data)=>{
                            // 指定店舗id一致且つ各CSVのコードが一致するもの
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '4111'){
                                Vue.set(vm, 'pre_earnings_achievement', data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '5111'){
                                cost = cost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '5211'){
                                cost = cost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '5311'){
                                cost = cost - Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '6111'){
                                laborCost = laborCost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '6311'){
                                laborCost = laborCost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '6212'){
                                laborCost = laborCost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '6213'){
                                laborCost = laborCost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '6312'){
                                laborCost = laborCost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '6226'){
                                laborCost = laborCost + Number(data.value);
                            }
                            if(data.shop_id === String(vm.shop_id) && data.expense_code === '6119'){
                                laborCost = laborCost + Number(data.value);
                            }
                        })
                        Vue.set(vm, 'pre_cost', cost);
                        Vue.set(vm, 'pre_profit_achievement', (vm.pre_earnings_achievement - cost) - sgaSum); // 利益 = 売上:4111 - 原価:(仕入高:5211 + 期首棚卸:5111 - 期末棚卸:5311)
                        Vue.set(vm, 'pre_labor_cost', laborCost);
                    }
                });
            },

            // 客数データ加工
            customersDataCreate: function(){
                var vm = this; // vueのインスタンスを参照
                var params = new URLSearchParams();
                params.append('choice_month', this.choiceMonth['month']);
                // スクレイピングIDで管理されている
                const id = this.shop_list.find((shop) =>{
                    return Number(shop.id) === Number(this.shop_id)
                })
                params.append('shop_id', id.scraping_id);
                axios.post("./database/get_kpi_management_customers.php",params).then(function (response) {
                    // リストの初期化
                    Vue.set(vm, 'customerTarget', null);
                    const idx = response.data.findIndex((shop) =>{
                        return shop.shop_id === id.scraping_id
                    })
                    if(response.data){
                        Vue.set(vm, 'customerTarget', response.data[idx]);
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

            // 全体ページ移動
            onClickAll: function(){
                window.location.href = './kpi_management_index_all.php?shop_id=' + '<?= $shop_record ?>';
            },

            // 業態別ページ移動
            onClickBusinessType: function(){
                window.location.href = './kpi_management_index_type.php?shop_id=' + '<?= $shop_record ?>';
            },

            // 月変更
            changeMonth: function(){
                console.log(this.choiceMonth['month']);
            }
        },

    })
</script>
