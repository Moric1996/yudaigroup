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

$shop_all = $dbio->fetchShopList();
foreach ($shop_all as $key => $one) {
    $shop_list[$key]['id'] = $one['id'];
    $shop_list[$key]['name'] = $one['name'];
    if ($target_shop == $one['id']) {
        $shop_list[$key]['selected'] = 'selected';
    }
}

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

// どこまで登録したかを記録するためのshop_id
$shop_record = $_GET['shop_id'];

$first_date = date('Y-m-01');
$last_date = date('Y-m-t'); // 今月末
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
        text-align: left;
    }

    .table>tbody>tr>th {
        border-right: groove;
        vertical-align: middle;
        text-align-last: center;
        font-weight: normal;
    }
</style>
<div id="app" v-cloak>
    <h1>入力画面</h1>
    <div>CSV取り込みデータ以外のデータを入力する画面です。</div>
    <div>ここに入力されたデータは{{ choiceMonth.month_string }}のデータになります。</div>
    <div style="display: flex; margin-top: 10px;">
        <span style="font-size: 20px;">店舗選択:</span>
        <!--<select name="target_shop" class="form-control box-size" v-model="shop_id" @change="shopChange">
            <?php foreach ($shop_list as $val): ?>
                <option value="<?= $val['id'] ?>" <?= $val['selected'] ?>><?= $val['name'] ?></option>
            <?php endforeach; ?>
        </select>-->
        <select name="target_shop" class="form-control box-size" v-model="shop_id" @change="shopChange">
            <? echo $select_option ?>
        </select>
        <span style="font-size: 20px; padding-left: 10px;">月選択:</span>
        <select class="form-control box-size" style="margin-bottom: 10px;" v-model="choiceMonth" @change="shopChange">
            <option v-for="(month, index) in monthList" :key="index" :value="month">{{ month.month_string }}</option>
        </select>
    </div>
    <!-- 入力欄 -->
    <div style="width: 550px;">
        <figure>
            <table class="table table-margin" style="border: solid;">
                <colgroup>
                    <col class="kpi-index" style="background-color: #efbdca; border-right: outset;"></col>
                    <col class="item-name" style="background-color: #efbdca; border-right: outset;"></col>
                    <col class="item-input" style="width: 180px; border-right: outset;"></col>
                    <!--<col class="item-input" style="width: 180px;"></col>-->
                </colgroup>
                <thead class="input-list-header" style="background-color: #fff1cb;">
                    <th style="border-bottom: solid;">指標</th>
                    <th style="border-bottom: solid;">項目</th>
                    <th style="border-bottom: solid;">入力</th>
                    <!--<th style="border-bottom: solid;">確定値入力</th>-->
                </thead>
                <tbody>
                    <!-- 店舗利益 -->
                    <tr style="border-bottom: double;">
                        <th>店舗利益</th>
                        <td>目標</td>
                        <td class="input-data">
                            <input
                                :type="inputTypePrePro"
                                v-model="pre_profit_target"
                                @blur="inputComma('inputTypePrePro', 'pre_profit_target', 'text')"
                                @focus="inputComma('inputTypePrePro', 'pre_profit_target', 'number')"
                            >
                        </td>
                        <!--
                        <td class="input-data">
                            <input
                                :type="inputTypePro"
                                v-model="profit_target"
                                @blur="inputComma('inputTypePro', 'profit_target', 'text')"
                                @focus="inputComma('inputTypePro', 'profit_target', 'number')"
                            >
                        -->
                        </td>
                    </tr>
                    <!-- 客数 -->
                    <tr style="border-bottom: double;">
                        <th>客数</th>
                        <td>目標</td>
                        <td class="input-data">
                            <input
                                :type="inputTypeCustomer"
                                v-model="number_of_customer_target"
                                @blur="inputComma('inputTypeCustomer', 'number_of_customer_target', 'text')"
                                @focus="inputComma('inputTypeCustomer', 'number_of_customer_target', 'number')"
                            >
                        </td>
                    </tr>
                    <!-- 原価率 -->
                    <!--
                    <tr>
                        <th>原価率</th>
                        <td>目標</td>
                        <td class="input-data">
                            <input
                                :type="inputTypePreCost"
                                v-model="pre_cost_rate_achievement"
                                @blur="inputComma('inputTypePreCost', 'pre_cost_rate_achievement', 'text')"
                                @focus="inputComma('inputTypePreCost', 'pre_cost_rate_achievement', 'number')"
                            >
                        </td>
                        <td class="input-data">
                            <input
                                :type="inputTypeCost"
                                v-model="cost_rate_achievement"
                                @blur="inputComma('inputTypeCost', 'cost_rate_achievement', 'text')"
                                @focus="inputComma('inputTypeCost', 'cost_rate_achievement', 'number')"
                            >
                        </td>
                    </tr>
                    -->
                    <tr style="border-bottom: double;">
                        <th>原価率</th>
                        <td>原価目標</td>
                        <td class="input-data">
                            <input
                                :type="inputTypePreCostTarget"
                                v-model="pre_cost_target"
                                @blur="inputComma('inputTypePreCostTarget', 'pre_cost_target', 'text')"
                                @focus="inputComma('inputTypePreCostTarget', 'pre_cost_target', 'number')"
                            >
                        </td>
                        <!--
                        <td class="input-data">
                            <input
                                :type="inputTypeCostTarget"
                                v-model="cost_target"
                                @blur="inputComma('inputTypeCostTarget', 'cost_target', 'text')"
                                @focus="inputComma('inputTypeCostTarget', 'cost_target', 'number')"
                            >
                        </td>
                        -->
                    </tr>
                    <tr>
                        <th rowspan="2">人件費</th>
                        <td>人件費率 目標</td>
                        <td class="input-data">
                            <input
                                :type="inputTypeLaborCost"
                                v-model="labor_cost_target"
                                @blur="inputComma('inputTypeLaborCost', 'labor_cost_target', 'text')"
                                @focus="inputComma('inputTypeLaborCost', 'labor_cost_target', 'number')"
                            >
                        </td>
                    </tr>
                    <tr style="border-bottom: double;">
                        <td>人時売上 目標</td>
                        <td class="input-data">
                            <input
                                :type="inputTypeManHourSales"
                                v-model="man_hour_sales_target"
                                @blur="inputComma('inputTypeManHourSales', 'man_hour_sales_target', 'text')"
                                @focus="inputComma('inputTypeManHourSales', 'man_hour_sales_target', 'number')"
                            >
                        </td>
                    </tr>
                    <!-- 労務 -->
                    <tr style="border-bottom: double;">
                        <th>労務</th>
                        <td>残業時間</td>
                        <td class="input-data">
                            <input
                                :type="inputTypePreOvertime"
                                v-model="pre_overtime_hours"
                                @blur="inputComma('inputTypePreOvertime', 'pre_overtime_hours', 'text')"
                                @focus="inputComma('inputTypePreOvertime', 'pre_overtime_hours', 'number')"
                            >
                        </td>
                        <!--<td class="input-data">
                            <input
                                :type="inputTypeOvertime"
                                v-model="overtime_hours"
                                @blur="inputComma('inputTypeOvertime', 'overtime_hours', 'text')"
                                @focus="inputComma('inputTypeOvertime', 'overtime_hours', 'number')"
                            >
                        </td>-->
                    </tr>
                    <!-- MS -->
                    <tr style="border-bottom: double;">
                        <th>MS</th>
                        <td>得点</td>
                        <td class="input-data">
                            <input
                                :type="inputTypePreMS"
                                v-model="pre_ms_score"
                                @blur="inputComma('inputTypePreMS', 'pre_ms_score', 'text')"
                                @focus="inputComma('inputTypePreMS', 'pre_ms_score', 'number')"
                            >
                        </td>
                        <!--<td class="input-data">
                            <input
                                :type="inputTypeMS"
                                v-model="ms_score"
                                @blur="inputComma('inputTypeMS', 'ms_score', 'text')"
                                @focus="inputComma('inputTypeMS', 'ms_score', 'number')"
                            >
                        </td>-->
                    </tr>
                    <!-- アンケート -->
                    <tr>
                        <th rowspan="2">アンケート</th>
                        <td>取得率</td>
                        <td class="input-data">
                            <input
                                :type="inputTypePreSurveyRate"
                                v-model="pre_survey_acquisition_rate"
                                @blur="inputComma('inputTypePreSurveyRate', 'pre_survey_acquisition_rate', 'text')"
                                @focus="inputComma('inputTypePreSurveyRate', 'pre_survey_acquisition_rate', 'number')"
                            >
                        </td>
                        <!--<td class="input-data">
                            <input
                                :type="inputTypeSurveyRate"
                                v-model="survey_acquisition_rate"
                                @blur="inputComma('inputTypeSurveyRate', 'survey_acquisition_rate', 'text')"
                                @focus="inputComma('inputTypeSurveyRate', 'survey_acquisition_rate', 'number')"
                            >
                        </td>-->
                    </tr>
                    <tr style="border-bottom: double;">
                        <td>総合</td>
                        <td class="input-data">
                            <input
                                :type="inputTypePreSurvey"
                                v-model="pre_survey_overall"
                                @blur="inputComma('inputTypePreSurvey', 'pre_survey_overall', 'text')"
                                @focus="inputComma('inputTypePreSurvey', 'pre_survey_overall', 'number')"
                            >
                        </td>
                        <!--<td class="input-data">
                            <input
                                :type="inputTypeSurvey"
                                v-model="survey_overall"
                                @blur="inputComma('inputTypeSurvey', 'survey_overall', 'text')"
                                @focus="inputComma('inputTypeSurvey', 'survey_overall', 'number')"
                            >
                        </td>-->
                    </tr>
                    <!-- クレーム -->
                    <tr style="border-bottom: double;">
                        <th>クレーム</th>
                        <td>件数</td>
                        <td class="input-data">
                            <input
                                :type="inputTypePreComp"
                                v-model="pre_number_of_complaints"
                                @blur="inputComma('inputTypePreComp', 'pre_number_of_complaints', 'text')"
                                @focus="inputComma('inputTypePreComp', 'pre_number_of_complaints', 'number')"
                            >
                        </td>
                        <!--<td class="input-data">
                            <input
                                :type="inputTypeComp"
                                v-model="number_of_complaints"
                                @blur="inputComma('inputTypeComp', 'number_of_complaints', 'text')"
                                @focus="inputComma('inputTypeComp', 'number_of_complaints', 'number')"
                            >
                        </td>-->
                    </tr>
                    <!-- 衛生検査 -->
                    <tr>
                        <th rowspan="2">衛生検査</th>
                        <td>適合率</td>
                        <td class="input-data">
                            <input
                                :type="inputTypePreSanitation"
                                v-model="pre_sanitation_inspection_rate"
                                @blur="inputComma('inputTypePreSanitation', 'pre_sanitation_inspection_rate', 'text')"
                                @focus="inputComma('inputTypePreSanitation', 'pre_sanitation_inspection_rate', 'number')"
                            >
                        </td>
                        <!--<td class="input-data">
                            <input
                                :type="inputTypeSanitation"
                                v-model="sanitation_inspection_rate"
                                @blur="inputComma('inputTypeSanitation', 'sanitation_inspection_rate', 'text')"
                                @focus="inputComma('inputTypeSanitation', 'sanitation_inspection_rate', 'number')"
                            >
                        </td>-->
                    </tr>
                    <tr style="border-bottom: double;">
                        <td>評価</td>
                        <td class="input-data"><input type="text" v-model="pre_sanitation_inspection_evaluate"></td>
                        <!--<td class="input-data"><input type="text" v-model="sanitation_inspection_evaluate"></td>-->
                    </tr>
                    <!-- 加減 -->
                    <tr>
                        <th rowspan="2">加減</th>
                        <td>積極取り組み</td>
                        <td class="input-data">
                            <input
                                type="text"
                                v-model="pre_proactive_approach"
                            >
                        </td>
                        <!--<td class="input-data">
                            <input
                                type="text"
                                v-model="proactive_approach"
                            >
                        </td>-->
                    </tr>
                    <tr style="border-bottom: solid;">
                        <td>ルール遵守</td>
                        <td class="input-data">
                            <input
                                type="text"
                                v-model="pre_compliance_with_rules"
                            >
                        </td>
                        <!--<td class="input-data">
                            <input
                                type="text"
                                v-model="compliance_with_rules"
                            >
                        </td>-->
                    </tr>
                </tbody>
            </table>
            <figcaption>
                <button type="button" class="btn btn-success"  style="float: right;" @click="onClickAll">戻る</button>
                <button type="button" class="btn btn-primary" style="float: right; margin-right: 10px;" @click="alert">保存</button>
            </figcaption>
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
            data_date: '<?= date('Y-m-d') ?>', // 何年何月のデータか
            monthList: <?= $month_list ?>,
            choiceMonth: <?= json_encode($this_month) ?>, // 選択された月
            displayMonth: null, // 現在表示中データ月
            // 速報値
            pre_profit_target: null, // 店舗利益目標
            pre_cost_rate_achievement: null, // 原価率実績
            pre_cost_target: null, // 原価目標
            pre_overtime_hours: null, // 残業時間
            pre_ms_score: null, // MS得点
            pre_survey_acquisition_rate: null, // アンケート取得率
            pre_survey_overall: null, // アンケート総合
            pre_number_of_complaints: null, // クレーム件数
            pre_sanitation_inspection_rate: null, // 衛生検査適合率
            pre_sanitation_inspection_evaluate: null, // 衛生検査評価
            pre_proactive_approach: null, // 積極取り組み
            pre_compliance_with_rules: null, // ルール遵守
            // 確定値
            profit_target: null, // 店舗利益目標
            cost_rate_achievement: null, // 原価率実績
            cost_target: null, // 原価目標
            overtime_hours: null, // 残業時間
            ms_score: null, // MS得点
            survey_acquisition_rate: null, // アンケート取得率
            survey_overall: null, // アンケート総合
            number_of_complaints: null, // クレーム件数
            sanitation_inspection_rate: null, // 衛生検査適合率
            sanitation_inspection_evaluate: null, // 衛生検査評価
            proactive_approach: null, // 積極取り組み
            compliance_with_rules: null, // ルール遵守
            number_of_customer_target: null, // 客数目標
            labor_cost_target: null, // 人件費率目標
            man_hour_sales_target: null, // 人時売上目標
            // 数値入力欄カンマ区切り
            inputTypePrePro: 'text',
            inputTypePro: 'text',
            inputTypePreCost: 'text',
            inputTypeCost: 'text',
            inputTypePreCostTarget: 'text',
            inputTypeCostTarget: 'text',
            inputTypePreOvertime: 'text',
            inputTypeOvertime: 'text',
            inputTypePreMS: 'text',
            inputTypeMS: 'text',
            inputTypePreSurveyRate: 'text',
            inputTypeSurveyRate: 'text',
            inputTypePreSurvey: 'text',
            inputTypeSurvey: 'text',
            inputTypePreComp: 'text',
            inputTypeComp: 'text',
            inputTypePreSanitation: 'text',
            inputTypeSanitation: 'text',
            inputTypePreProactive: 'text',
            inputTypeProactive: 'text',
            inputTypePreRule: 'text',
            inputTypeRule: 'text',
            inputTypeCustomer: 'text',
            inputTypeLaborCost: 'text',
            inputTypeManHourSales: 'text',
        },
        mounted(){
            this.shopChange();
        },
        filters: {

        },
        methods: {
            // 保存
            registarKpiManagement: function () {
                // ダイアログを開く
                this.$swal({
                    icon : 'info',
                    title : '保存処理中',
                    text : 'しばらくお待ちください...',
                    closeOnClickOutside: false,
                    buttons : false,
                });
                console.log(this.data_date);
                console.log(this.shop_id);
                var vm = this;
                var params = new URLSearchParams();
                params.append('shop_id', this.shop_id);
                params.append('data_date', this.choiceMonth.month);
                // 速報値
                params.append('pre_profit_target', this.pre_profit_target ? parseFloat(this.pre_profit_target.replace(/,/g, "")) : null);
                params.append('pre_cost_rate_achievement', this.pre_cost_rate_achievement ? parseFloat(this.pre_cost_rate_achievement.replace(/,/g, "")) : null);
                params.append('pre_cost_target', this.pre_cost_target ? parseFloat(this.pre_cost_target.replace(/,/g, "")) : null);
                params.append('pre_overtime_hours', this.pre_overtime_hours ? parseFloat(this.pre_overtime_hours.replace(/,/g, "")) : null);
                params.append('pre_ms_score', this.pre_ms_score ? parseFloat(this.pre_ms_score.replace(/,/g, "")) : null);
                params.append('pre_survey_acquisition_rate', this.pre_survey_acquisition_rate ? parseFloat(this.pre_survey_acquisition_rate.replace(/,/g, "")) : null);
                params.append('pre_survey_overall', this.pre_survey_overall ? parseFloat(this.pre_survey_overall.replace(/,/g, "")) : null);
                params.append('pre_number_of_complaints', this.pre_number_of_complaints ? parseFloat(this.pre_number_of_complaints.replace(/,/g, "")) : null);
                params.append('pre_sanitation_inspection_rate', this.pre_sanitation_inspection_rate ? parseFloat(this.pre_sanitation_inspection_rate.replace(/,/g, "")) : null);
                params.append('pre_sanitation_inspection_evaluate', this.pre_sanitation_inspection_evaluate === null ? '' : this.pre_sanitation_inspection_evaluate);
                params.append('pre_proactive_approach', this.pre_proactive_approach ? parseFloat(this.pre_proactive_approach.replace(/,/g, "")) : null);
                params.append('pre_compliance_with_rules', this.pre_compliance_with_rules ? parseFloat(this.pre_compliance_with_rules.replace(/,/g, "")) : null);
                // 確定値
                params.append('profit_target', this.pre_profit_target ? parseFloat(this.pre_profit_target.replace(/,/g, "")) : null);
                //params.append('profit_target', this.profit_target ? parseFloat(this.profit_target.replace(/,/g, "")) : null);
                params.append('cost_rate_achievement', this.pre_cost_rate_achievement ? parseFloat(this.pre_cost_rate_achievement.replace(/,/g, "")) : null);
                //params.append('cost_rate_achievement', this.cost_rate_achievement ? parseFloat(this.cost_rate_achievement.replace(/,/g, "")) : null);
                params.append('cost_target', this.pre_cost_target ? parseFloat(this.pre_cost_target.replace(/,/g, "")) : null);
                //params.append('cost_target', this.cost_target ? parseFloat(this.cost_target.replace(/,/g, "")) : null);
                params.append('overtime_hours', this.overtime_hours ? parseFloat(this.overtime_hours.replace(/,/g, "")) : null);
                params.append('ms_score', this.ms_score ? parseFloat(this.ms_score.replace(/,/g, "")) : null);
                params.append('survey_acquisition_rate', this.survey_acquisition_rate ? parseFloat(this.survey_acquisition_rate.replace(/,/g, "")) : null);
                params.append('survey_overall', this.survey_overall ? parseFloat(this.survey_overall.replace(/,/g, "")) : null);
                params.append('number_of_complaints', this.number_of_complaints ? parseFloat(this.number_of_complaints.replace(/,/g, "")) : null);
                params.append('sanitation_inspection_rate', this.sanitation_inspection_rate ? parseFloat(this.sanitation_inspection_rate.replace(/,/g, "")) : null);
                params.append('sanitation_inspection_evaluate', this.sanitation_inspection_evaluate === null ? '' : this.sanitation_inspection_evaluate);
                params.append('proactive_approach', this.proactive_approach ? parseFloat(this.proactive_approach.replace(/,/g, "")) : null);
                params.append('compliance_with_rules', this.compliance_with_rules ? parseFloat(this.compliance_with_rules.replace(/,/g, "")) : null);

                // 客数合計目標
                params.append('number_of_customer_target', this.number_of_customer_target ? parseFloat(this.number_of_customer_target.replace(/,/g, "")) : null);
                // 人件費
                params.append('labor_cost_target', this.labor_cost_target ? parseFloat(this.labor_cost_target.replace(/,/g, "")) : null);
                params.append('man_hour_sales_target', this.man_hour_sales_target ? parseFloat(this.man_hour_sales_target.replace(/,/g, "")) : null);
                
                axios.post("./database/register_kpi_management.php", params).then(function () {
                    vm.$swal({
                        icon : 'success',
                        title : '保存完了',
                        text : '完了しました',
                        closeOnClickOutside: false,
                    })
                    .then(function (result) {
                        // トップに戻したい
                        window.location.href = "./kpi_management_index_all.php?shop_id=" + '<?= $shop_record ?>';
                    });
                });
            },
            
            // 確認アラート
            alert: function(){
                if(this.pre_sanitation_inspection_evaluate !== null && !this.pre_sanitation_inspection_evaluate.match(/^[A-Z]*$/)){
                    this.$swal ("無効な値" ,  "衛生検査の「評価」は半角大文字アルファベットで入力してください。" ,  "error");
                }else{
                    console.log(this.data_date)
                    this.$swal({
                        title: '確認',
                        text: 'この内容で登録します。よろしいですか？',
                        icon: 'warning',
                        buttons: true,
                    })
                    .then((confirmOK) => {
                        if(confirmOK){
                            // OKが押された場合
                            this.registarKpiManagement();
                        }else{
                            // キャンセル
                        }
                    });
                }
            },
            // 店舗選択
            shopChange: function(){
                var vm = this; // vueのインスタンスを参照
                // 選択された店舗のデータ取得
                console.log(this.shop_id);
                var params = new URLSearchParams();
                params.append('shop_id', this.shop_id);
                params.append('choice_month', this.choiceMonth.month);
                axios.post("./database/get_kpi_management.php",params).then(function (response) {
                    console.log(response.data);
                    const preInputData = response.data ? response.data.find(data => data.value_type === '2') : null;// 速報値(value_type=2)
                    const conInputData = response.data ? response.data.find(data => data.value_type === '1') : null;// 確定値(value_type=1)
                    console.log(preInputData);
                    console.log(conInputData);

                    const formatter = new Intl.NumberFormat("ja-JP")
                    // 速報値
                    Vue.set(vm, 'pre_profit_target', preInputData ? (!preInputData.profit_target ? null : formatter.format(preInputData.profit_target)) : null);
                    Vue.set(vm, 'pre_cost_rate_achievement', preInputData ? (!preInputData.cost_rate_achievement ? null : formatter.format(preInputData.cost_rate_achievement)) : null);
                    Vue.set(vm, 'pre_cost_target', preInputData ? (!preInputData.cost_target ? null : formatter.format(preInputData.cost_target)) : null);
                    Vue.set(vm, 'pre_overtime_hours', preInputData ? (!preInputData.overtime_hours ? null : formatter.format(preInputData.overtime_hours)) : null);
                    Vue.set(vm, 'pre_ms_score', preInputData ? (!preInputData.ms_score ? null : formatter.format(preInputData.ms_score)) : null);
                    Vue.set(vm, 'pre_survey_acquisition_rate', preInputData ? (!preInputData.survey_acquisition_rate ? null : formatter.format(preInputData.survey_acquisition_rate)) : null);
                    Vue.set(vm, 'pre_survey_overall', preInputData ? (!preInputData.survey_overall ? null : formatter.format(preInputData.survey_overall)) : null);
                    Vue.set(vm, 'pre_number_of_complaints', preInputData ? (!preInputData.number_of_complaints ? null : formatter.format(preInputData.number_of_complaints)) : null);
                    Vue.set(vm, 'pre_sanitation_inspection_rate', preInputData ? (!preInputData.sanitation_inspection_rate ? null : formatter.format(preInputData.sanitation_inspection_rate)) : null);
                    Vue.set(vm, 'pre_sanitation_inspection_evaluate', preInputData ? preInputData.sanitation_inspection_evaluate : null);
                    Vue.set(vm, 'pre_proactive_approach', preInputData ? (!preInputData.proactive_approach ? null : formatter.format(preInputData.proactive_approach)) : null);
                    Vue.set(vm, 'pre_compliance_with_rules', preInputData ? (!preInputData.compliance_with_rules ? null : formatter.format(preInputData.compliance_with_rules)) : null);
                    // 確定値
                    Vue.set(vm, 'profit_target', conInputData ? (!conInputData.profit_target ? null : formatter.format(conInputData.profit_target)) : null);
                    Vue.set(vm, 'cost_rate_achievement', conInputData ? (!conInputData.cost_rate_achievement ? null : formatter.format(conInputData.cost_rate_achievement)) : null);
                    Vue.set(vm, 'cost_target', conInputData ? (!conInputData.cost_target ? null : formatter.format(conInputData.cost_target)) : null);
                    Vue.set(vm, 'overtime_hours', conInputData ? (!conInputData.overtime_hours ? null : formatter.format(conInputData.overtime_hours)) : null);
                    Vue.set(vm, 'ms_score', conInputData ? (!conInputData.ms_score ? null : formatter.format(conInputData.ms_score)) : null);
                    Vue.set(vm, 'survey_acquisition_rate', conInputData ? (!conInputData.survey_acquisition_rate ? null : formatter.format(conInputData.survey_acquisition_rate)) : null);
                    Vue.set(vm, 'survey_overall', conInputData ? (!conInputData.survey_overall ? null : formatter.format(conInputData.survey_overall)) : null);
                    Vue.set(vm, 'number_of_complaints', conInputData ? (!conInputData.number_of_complaints ? null : formatter.format(conInputData.number_of_complaints)) : null);
                    Vue.set(vm, 'sanitation_inspection_rate', conInputData ? (!conInputData.sanitation_inspection_rate ? null : formatter.format(conInputData.sanitation_inspection_rate)) : null);
                    Vue.set(vm, 'sanitation_inspection_evaluate', conInputData ? conInputData.sanitation_inspection_evaluate : null);
                    Vue.set(vm, 'proactive_approach', conInputData ? (!conInputData.proactive_approach ? null : formatter.format(conInputData.proactive_approach)) : null);
                    Vue.set(vm, 'compliance_with_rules', conInputData ? (!conInputData.compliance_with_rules ? null : formatter.format(conInputData.compliance_with_rules)) : null);
                    // 客数目標
                    Vue.set(vm, 'number_of_customer_target', conInputData ? (!conInputData.number_of_customer_target ? null : formatter.format(conInputData.number_of_customer_target)) : null);
                    // 人件費
                    Vue.set(vm, 'labor_cost_target', conInputData ? (!conInputData.labor_cost_target ? null : formatter.format(conInputData.labor_cost_target)) : null);
                    Vue.set(vm, 'man_hour_sales_target', conInputData ? (!conInputData.man_hour_sales_target ? null : formatter.format(conInputData.man_hour_sales_target)) : null);
                });
            },

            // inputでカンマ区切り
            inputComma: function(inputContent, value, type){
                this[inputContent] = type
                // フォーカスが外れたらtext,している間number
                if(type === 'text'){
                    const formatter = new Intl.NumberFormat("ja-JP")
                    this[value] = !this[value] ? null : formatter.format(this[value])
                    console.log(this[value])
                }else{
                    this[value] = !this[value] ? null : parseFloat(this[value].replace(/,/g, ''))
                }
            },

            // 全体ページ移動
            onClickAll: function(){
                window.location.href = './kpi_management_index_all.php?shop_id=' + '<?= $shop_record ?>';
            },
        },

    })
</script>
