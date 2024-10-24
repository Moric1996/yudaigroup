<?php
include('../../inc/ybase.inc');

$ybase = new ybase();
$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');
// パラメーター
$shop_id = $_POST['shop_id'];
$data_date = $_POST['data_date'];
// 速報値
$pre_data_list = array(
    'pre_profit_target' => $_POST['pre_profit_target'],
    'pre_cost_rate_achievement' => $_POST['pre_cost_rate_achievement'],
    'pre_cost_target' => $_POST['pre_cost_target'],
    'pre_overtime_hours' => $_POST['pre_overtime_hours'],
    'pre_ms_score' => $_POST['pre_ms_score'],
    'pre_survey_acquisition_rate' => $_POST['pre_survey_acquisition_rate'],
    'pre_survey_overall' => $_POST['pre_survey_overall'],
    'pre_number_of_complaints' => $_POST['pre_number_of_complaints'],
    'pre_sanitation_inspection_rate' => $_POST['pre_sanitation_inspection_rate'],
    'pre_sanitation_inspection_evaluate' => $_POST['pre_sanitation_inspection_evaluate'] ? $_POST['pre_sanitation_inspection_evaluate'] : '',
    'pre_proactive_approach' => $_POST['pre_proactive_approach'],
    'pre_compliance_with_rules' => $_POST['pre_compliance_with_rules'],
    'number_of_customer_target' => $_POST['number_of_customer_target'],
    'labor_cost_target' => $_POST['labor_cost_target'],
    'man_hour_sales_target' => $_POST['man_hour_sales_target'],
);
// 確定値
$con_data_list = array(
    'profit_target' => $_POST['profit_target'],
    'cost_rate_achievement' => $_POST['cost_rate_achievement'],
    'cost_target' => $_POST['cost_target'],
    'overtime_hours' => $_POST['overtime_hours'],
    'ms_score' => $_POST['ms_score'],
    'survey_acquisition_rate' => $_POST['survey_acquisition_rate'],
    'survey_overall' => $_POST['survey_overall'],
    'number_of_complaints' => $_POST['number_of_complaints'],
    'sanitation_inspection_rate' => $_POST['sanitation_inspection_rate'],
    'sanitation_inspection_evaluate' => $_POST['sanitation_inspection_evaluate'] ? $_POST['sanitation_inspection_evaluate'] : '',
    'proactive_approach' => $_POST['proactive_approach'],
    'compliance_with_rules' => $_POST['compliance_with_rules'],
    'number_of_customer_target' => $_POST['number_of_customer_target'],
    'labor_cost_target' => $_POST['labor_cost_target'],
    'man_hour_sales_target' => $_POST['man_hour_sales_target'],
);

/** 
 * テーブル挿入 
 */
// とりあえず今は「確定値:value_type=1, 速報値:value_type=2（速報値のほうが細かく分かれる場合に後ろに増えていくと思われるので後者に）」で行く

// 今月のデータ存在チェック
// 速報値処理
function registerPreliminate($conn, $shop_id, $data_date, $list){
    $first_date = date('Y-m-01', strtotime($data_date . ' 00:00:00')); // 月初
    $last_date = date('Y-m-t', strtotime($data_date . ' 00:00:00')); // 今月末
    $this_month_preliminary = <<<SQL
SELECT id,shop_id,data_date
FROM kpi_managements
WHERE data_date BETWEEN '$first_date' AND '$last_date'
AND value_type=2
AND shop_id=$shop_id
SQL;
    $res_pre = pg_query($conn, $this_month_preliminary);
    $pre_data = pg_fetch_all($res_pre);
    if(!empty($pre_data)){
        foreach($pre_data as $data){
            // データ登録しようとしている店舗が今月のデータに存在している場合はUPDATE
            if($shop_id === $data['shop_id']){
                $data_id = $data['id'];
                $sql = <<<SQL
UPDATE kpi_managements
SET profit_target= {$list['pre_profit_target']},
cost_rate_achievement= {$list['pre_cost_rate_achievement']},
cost_target= {$list['pre_cost_target']},
overtime_hours = {$list['pre_overtime_hours']},
ms_score = {$list['pre_ms_score']},
survey_acquisition_rate = {$list['pre_survey_acquisition_rate']},
survey_overall = {$list['pre_survey_overall']},
number_of_complaints = {$list['pre_number_of_complaints']},
sanitation_inspection_rate = {$list['pre_sanitation_inspection_rate']},
sanitation_inspection_evaluate = '{$list["pre_sanitation_inspection_evaluate"]}',
proactive_approach = {$list['pre_proactive_approach']},
compliance_with_rules = {$list['pre_compliance_with_rules']},
number_of_customer_target = {$list['number_of_customer_target']},
labor_cost_target = {$list['labor_cost_target']},
man_hour_sales_target = {$list['man_hour_sales_target']}
WHERE id = $data_id
SQL;
                pg_query($conn, $sql);
            }
        }
    }else{
        $sql = <<<SQL
INSERT INTO kpi_managements
VALUES
(nextval('kpi_managements_id_seq'),
$shop_id,
{$list['pre_cost_rate_achievement']},
{$list['pre_overtime_hours']},
{$list['pre_ms_score']},
{$list['pre_survey_acquisition_rate']},
{$list['pre_survey_overall']},
{$list['pre_number_of_complaints']},
{$list['pre_sanitation_inspection_rate']},
'{$list["pre_sanitation_inspection_evaluate"]}',
{$list['pre_proactive_approach']},
{$list['pre_compliance_with_rules']},
'{$data_date}',
2,
{$list['pre_profit_target']},
{$list['pre_cost_target']},
{$list['number_of_customer_target']},
{$list['labor_cost_target']},
{$list['man_hour_sales_target']})
SQL;
        pg_query($conn, $sql);
    }
}


// 確定値処理
function registerConfirm($conn, $shop_id, $data_date, $list){
    $exist = false; // データ存在フラグ
    $first_date = date('Y-m-01', strtotime($data_date . ' 00:00:00')); // 月初
    $last_date = date('Y-m-t', strtotime($data_date . ' 00:00:00')); // 今月末
    $this_month_confirm = <<<SQL
SELECT id,shop_id,data_date
FROM kpi_managements
WHERE data_date BETWEEN '$first_date' AND '$last_date'
AND value_type=1
AND shop_id=$shop_id
SQL;
    $res_con = pg_query($conn, $this_month_confirm);
    $con_data = pg_fetch_all($res_con);
    if(!empty($con_data)){
        foreach($con_data as $data){
            // データ登録しようとしている店舗が今月のデータに存在している場合はUPDATE
            if($shop_id === $data['shop_id']){
                $data_id = $data['id'];
                $exist = true;
                $sql = <<<SQL
UPDATE kpi_managements
SET profit_target= {$list['profit_target']},
cost_rate_achievement = {$list['cost_rate_achievement']},
cost_target= {$list['cost_target']},
overtime_hours = {$list['overtime_hours']},
ms_score = {$list['ms_score']},
survey_acquisition_rate = {$list['survey_acquisition_rate']},
survey_overall = {$list['survey_overall']},
number_of_complaints = {$list['number_of_complaints']},
sanitation_inspection_rate = {$list['sanitation_inspection_rate']},
sanitation_inspection_evaluate = '{$list["sanitation_inspection_evaluate"]}',
proactive_approach = {$list['proactive_approach']},
compliance_with_rules = {$list['compliance_with_rules']},
number_of_customer_target = {$list['number_of_customer_target']},
labor_cost_target = {$list['labor_cost_target']},
man_hour_sales_target = {$list['man_hour_sales_target']}
WHERE id = $data_id
SQL;
                pg_query($conn, $sql);
            }
        }
    }else{
        $sql = <<<SQL
INSERT INTO kpi_managements
VALUES
(nextval('kpi_managements_id_seq'),
$shop_id,
{$list['cost_rate_achievement']},
{$list['overtime_hours']},
{$list['ms_score']},
{$list['survey_acquisition_rate']},
{$list['survey_overall']},
{$list['number_of_complaints']},
{$list['sanitation_inspection_rate']},
'{$list["sanitation_inspection_evaluate"]}',
{$list['proactive_approach']},
{$list['compliance_with_rules']},
'{$data_date}',
1,
{$list['profit_target']},
{$list['cost_target']},
{$list['number_of_customer_target']},
{$list['labor_cost_target']},
{$list['man_hour_sales_target']})
SQL;
        pg_query($conn, $sql);
    }
}

// 保存処理関数をそれぞれ呼び出す
registerPreliminate($conn, $shop_id, $data_date, $pre_data_list);
registerConfirm($conn, $shop_id, $data_date, $con_data_list);
