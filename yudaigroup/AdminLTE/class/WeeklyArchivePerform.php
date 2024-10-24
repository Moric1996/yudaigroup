<?php

/**
 * Class WeeklyPerform
 */
class WeeklyArchivePerform extends YudaiFoodData
{

    // 一週間の日付
    private $target_week_date_list = array();

    // 一週間前の日付
    private $last_week_date_list = array();

    // 目標人時
    private $target_man_hour = array();


    // 閉店データ
    private $target_special_array = array();

    // この日を対象に閉店扱いにする日付
    protected $target_date;

    ////////////ターゲット



    /**
     * ターゲットの日付リスト
     * @param $target_week_date_list
     */
    public function setTargetWeekList($target_week_date_list)
    {
        $this->target_week_date_list = $target_week_date_list;
    }


    /**
     * ターゲットの日付リスト
     * @param $target_week_date_list
     */
    public function setLastWeekList($last_week_date_list)
    {
        $this->last_week_date_list = $last_week_date_list;
    }


    /**
     * 1週間のデータを出す
     * @param $week_data
     */
    public function setWeekData($week_data, $week_st_date)
    {
        $special_array = $this->target_special_array;

        $result_array = $this->result_array;

        foreach ($week_data as $key => $shop_data) {
            // 閉店した店は処理しない
            if ($this->isCloseShop($shop_data['shop_id'], $this->target_date)) {
                continue;
            }

            // 設定されたよりも前は新店舗扱いにする
            if ($special_array[$shop_data['shop_id']]) {
                $shop_data['category'] = $special_array[$shop_data['shop_id']];
                // 新店舗だったらnewにする
            } else if ($shop_data['is_new_shop'] == 't') {
                $shop_data['category'] = 9999;
            }

            $result_array[$shop_data['category']][$shop_data['name']]['shop_id'] = $shop_data['id'];

            // 予算
            $result_array[$shop_data['category']][$shop_data['name']]['week_budget' . $shop_data['date']] = $shop_data['budget'];

            // 売上
            $result_array[$shop_data['category']][$shop_data['name']]['week_revenue' . $shop_data['date']] = $shop_data['revenue'];

            // 人時売上
            $result_array[$shop_data['category']][$shop_data['name']]['week_man_hour' . $shop_data['date']] =
                $shop_data['work_time'] > 0 ? round($shop_data['revenue'] / $shop_data['work_time']) : 0;

            // 週間予算累計
            $result_array[$shop_data['category']][$shop_data['name']]['week_total_budget' . $week_st_date] += $shop_data['budget'];

            // 週間売上累計
            $result_array[$shop_data['category']][$shop_data['name']]['week_total_revenue' . $week_st_date] += $shop_data['revenue'];
        }
        $this->result_array = $result_array;
    }

    /**
     * 比較用に週を揃える
     * @param $week_data
     */
    public function setWeekDiffData($week_data, $week_st_date)
    {
        $special_array = $this->target_special_array;

        $result_array = $this->result_array;

        foreach ($week_data as $key => $shop_data) {
            // 閉店した店は処理しない
            if ($this->isCloseShop($shop_data['shop_id'], $this->target_date)) {
                continue;
            }

            // 設定されたよりも前は新店舗扱いにする
            if ($special_array[$shop_data['shop_id']]) {
                $shop_data['category'] = $special_array[$shop_data['shop_id']];
                // 新店舗だったらnewにする
            } else if ($shop_data['is_new_shop'] == 't') {
                $shop_data['category'] = 9999;
            }

            // 週間売上累計
            $result_array[$shop_data['category']][$shop_data['name']]['week_diff_revenue' . $week_st_date] += $shop_data['revenue'];


        }

        $this->result_array = $result_array;
    }

    /**
     * とにかく月全日の予算合計
     * @param $month_data
     */
    public function setMonthBudget($month_data, $month_st_date)
    {
        $special_array = $this->target_special_array;

        $result_array = $this->result_array;

        foreach ($month_data as $key => $shop_data) {
            // 閉店した店は処理しない
            if ($this->isCloseShop($shop_data['shop_id'], $this->target_date)) {
                continue;
            }

            // 設定されたよりも前は新店舗扱いにする
            if ($special_array[$shop_data['shop_id']]) {
                $shop_data['category'] = $special_array[$shop_data['shop_id']];
                // 新店舗だったらnewにする
            } else if ($shop_data['is_new_shop'] == 't') {
                $shop_data['category'] = 9999;
            }

            // 予算
            $result_array[$shop_data['category']][$shop_data['name']]['month_all_total_budget' . $month_st_date] += $shop_data['budget'];
        }

        $this->result_array = $result_array;
    }


    /**
     * 1ヶ月の売上の存在する日のデータ合計
     * @param $month_data
     */
    public function setMonthData($month_data, $month_st_date)
    {
        $special_array = $this->target_special_array;

        $result_array = $this->result_array;

        $man_hour = array();
        foreach ($month_data as $key => $shop_data) {
            // 閉店した店は処理しない
            if ($this->isCloseShop($shop_data['shop_id'], $this->target_date)) {
                continue;
            }

            // 設定されたよりも前は新店舗扱いにする
            if ($special_array[$shop_data['shop_id']]) {
                $shop_data['category'] = $special_array[$shop_data['shop_id']];
                // 新店舗だったらnewにする
            } else if ($shop_data['is_new_shop'] == 't') {
                $shop_data['category'] = 9999;
            }
            $result_array[$shop_data['category']][$shop_data['name']]['shop_data_id'] = $shop_data['shop_id'];

            // 予算
            $result_array[$shop_data['category']][$shop_data['name']]['month_total_budget' . $month_st_date] += $shop_data['budget'];
            // 売上
            $result_array[$shop_data['category']][$shop_data['name']]['month_total_revenue' . $month_st_date] += $shop_data['revenue'];
            // 労働時間
            $man_hour[$shop_data['name']]['work_time'] += $shop_data['work_time'];
            $man_hour[$shop_data['name']]['revenue'] += $shop_data['revenue'];
            $man_hour[$shop_data['name']]['category'] = $shop_data['category'];
        }
        foreach ($man_hour as $key => $val) {
            // 人時
            $result_array[$val['category']][$key]['month_total_man_hour' . $month_st_date] = ($val['work_time'])
                ? round($val['revenue'] / $val['work_time'])
                : 0;
        }


        $this->result_array = $result_array;
    }


    /**
     * ターゲット人時売上
     * @param $target_man_hour
     * @param $target_man_hour_date
     */
    public function setMonthTargetManHour($target_man_hour, $target_man_hour_date)
    {
        $res = $this->target_man_hour;
        foreach ($target_man_hour as $val) {
            $res[$val['shop_id']][$target_man_hour_date] = $val['target_man_hour'];
        }
        $this->target_man_hour = $res;
    }


    /**
     * 前年
     * @param $last_year_data
     * @param $last_year_date
     */
    public function setLastYearData($last_year_data)
    {
        $special_array = $this->target_special_array;

        $result_array = $this->result_array;

        foreach ($last_year_data as $key => $shop_data) {
            // 閉店した店は処理しない
            if ($this->isCloseShop($shop_data['shop_id'], $this->target_date)) {
                continue;
            }

            // 設定されたよりも前は新店舗扱いにする
            if ($special_array[$shop_data['shop_id']]) {
                $shop_data['category'] = $special_array[$shop_data['shop_id']];
                // 新店舗だったらnewにする
            } else if ($shop_data['is_new_shop'] == 't') {
                $shop_data['category'] = 9999;
            }

            $result_array[$shop_data['category']][$shop_data['name']]['last_year_total_revenue'] += $shop_data['revenue'];
        }
        $this->result_array = $result_array;
    }


    /**
     * ターゲット日付
     * @param $target_date
     */
    public function setTargetDate($target_date)
    {
        $special_array = $this->special_array;
        $target_special_array = array();

        $comparison_date = new DateTime($target_date);

        foreach ($special_array as $key_id => $val) {
            if ($val['date'] >= $comparison_date->format('Ym')) {
                $target_special_array[$key_id] = $val['category'];
            }
        }
        $this->target_date = $target_date;
        $this->target_special_array = $target_special_array;
    }


    /**
     * @param array $result_array
     * @param bool $total_flag
     */
    public function makeDisplayHtml($result_array = array(), $total_flag = false)
    {
        // 合計用色変え
        if ($total_flag) {
            $total = "class='total'";
        }

        $shop_row = ($total_flag == 2) ? 4 : 3;


        $target_week_date_list = $this->target_week_date_list;
        $last_week_date_list = $this->last_week_date_list;

        // 表示
        $display_table = $this->display_table;
        foreach ((array)$result_array as $name => $one_data) {

            /** 1行目 */
            // 当月予算
            $last_week_month_total_budget = $one_data['month_all_total_budget' . $last_week_date_list[0]];

            // 週間予算
            $last_week_day1_budget = $one_data['week_budget' . $last_week_date_list[0]];
            $last_week_day2_budget = $one_data['week_budget' . $last_week_date_list[1]];
            $last_week_day3_budget = $one_data['week_budget' . $last_week_date_list[2]];
            $last_week_day4_budget = $one_data['week_budget' . $last_week_date_list[3]];
            $last_week_day5_budget = $one_data['week_budget' . $last_week_date_list[4]];
            $last_week_day6_budget = $one_data['week_budget' . $last_week_date_list[5]];
            $last_week_day7_budget = $one_data['week_budget' . $last_week_date_list[6]];

            // 週間累計予算
            $last_week_total_budget = $one_data['week_total_budget' . $last_week_date_list[0]];

            // 当月予算
            $target_month_total_budget = $one_data['month_all_total_budget' . $target_week_date_list[0]];

            // 週間予算
            $target_week_day1_budget = $one_data['week_budget' . $target_week_date_list[0]];
            $target_week_day2_budget = $one_data['week_budget' . $target_week_date_list[1]];
            $target_week_day3_budget = $one_data['week_budget' . $target_week_date_list[2]];
            $target_week_day4_budget = $one_data['week_budget' . $target_week_date_list[3]];
            $target_week_day5_budget = $one_data['week_budget' . $target_week_date_list[4]];
            $target_week_day6_budget = $one_data['week_budget' . $target_week_date_list[5]];
            $target_week_day7_budget = $one_data['week_budget' . $target_week_date_list[6]];
            // 累計
            $target_week_total_budget = $one_data['week_total_budget' . $target_week_date_list[0]];

            // 今月の現時点の経過した累計予算
            $target_month_budget = $one_data['month_total_budget' . $target_week_date_list[0]];
            $last_week_month_budget = $one_data['month_total_budget' . $last_week_date_list[0]];


            $display_table .= <<<HTML
<!--line1-->
<tr {$total} style="border-top: 2px black solid !important;">
    <!-- 店舗名 -->
    <th rowspan="{$shop_row}" class="division_line">{$name}</th>
    
    <!-- 当月見込(予算？)-->
    <td class="comma">{$last_week_month_total_budget}</td>
    
    <!-- 週間予算 -->
    <td class="comma">{$last_week_day1_budget}</td>
    <td class="comma">{$last_week_day2_budget}</td>
    <td class="comma">{$last_week_day3_budget}</td>
    <td class="comma">{$last_week_day4_budget}</td>
    <td class="comma">{$last_week_day5_budget}</td>
    <td class="comma">{$last_week_day6_budget}</td>
    <td class="comma">{$last_week_day7_budget}</td>
    <!-- 週間累計予算 -->
    <td class="comma division_line">{$last_week_total_budget}</td>
    
    <!-- 当月見込(予算？)-->
    <td class="comma">{$target_month_total_budget}</td>
    <!-- 今週予算 -->
    <td class="comma">{$target_week_day1_budget}</td>
    <td class="comma">{$target_week_day2_budget}</td>
    <td class="comma">{$target_week_day3_budget}</td>
    <td class="comma">{$target_week_day4_budget}</td>
    <td class="comma">{$target_week_day5_budget}</td>
    <td class="comma">{$target_week_day6_budget}</td>
    <td class="comma">{$target_week_day7_budget}</td>
    <!-- 週間累計予算 -->
    <td class="comma left_border">{$target_week_total_budget}</td>
    
    <td></td>
    
    <!-- 今月累計予算 -->
    <td class="comma">{$target_month_budget}</td>
    
    <td></td>
    <td class="right_border"></td>
</tr>
HTML;

            /** 2行目 */
            // 週間売上
            $last_week_day1_revenue = $one_data['week_revenue' . $last_week_date_list[0]];
            $last_week_day2_revenue = $one_data['week_revenue' . $last_week_date_list[1]];
            $last_week_day3_revenue = $one_data['week_revenue' . $last_week_date_list[2]];
            $last_week_day4_revenue = $one_data['week_revenue' . $last_week_date_list[3]];
            $last_week_day5_revenue = $one_data['week_revenue' . $last_week_date_list[4]];
            $last_week_day6_revenue = $one_data['week_revenue' . $last_week_date_list[5]];
            $last_week_day7_revenue = $one_data['week_revenue' . $last_week_date_list[6]];
            // 週間累計売上
            $last_week_total_revenue = $one_data['week_total_revenue' . $last_week_date_list[0]];

            // 週間売上
            $target_week_day1_revenue = $one_data['week_revenue' . $target_week_date_list[0]];
            $target_week_day2_revenue = $one_data['week_revenue' . $target_week_date_list[1]];
            $target_week_day3_revenue = $one_data['week_revenue' . $target_week_date_list[2]];
            $target_week_day4_revenue = $one_data['week_revenue' . $target_week_date_list[3]];
            $target_week_day5_revenue = $one_data['week_revenue' . $target_week_date_list[4]];
            $target_week_day6_revenue = $one_data['week_revenue' . $target_week_date_list[5]];
            $target_week_day7_revenue = $one_data['week_revenue' . $target_week_date_list[6]];
            // 週間累計売上
            $target_week_total_revenue = $one_data['week_total_revenue' . $target_week_date_list[0]];


            // 前週差
            $last_week_diff = $one_data['week_diff_revenue' . $target_week_date_list[0]] - $one_data['week_diff_revenue' . $last_week_date_list[0]];

            // 今月累計売上
            $target_month_revenue = $one_data['month_total_revenue' . $target_week_date_list[0]];

            $last_week_month_revenue = $one_data['month_total_revenue' . $last_week_date_list[0]];

            // 予算比
            $target_month_revenue_rate = ($target_month_budget)
                ? round($target_month_revenue / $target_month_budget * 100, 1) : 0;
            // 対象週の予想される売上
            $target_week_expected_revenue = round($target_month_total_budget * $target_month_revenue_rate / 100);


            // 前の週の予算比
            $last_week_revenue_rate = (($last_week_month_budget) > 0)
                ? round($last_week_month_revenue
                    / $last_week_month_budget * 100, 1) : 0;
            // 前の週の予想される売上
            $last_week_expected_revenue = round($last_week_month_total_budget * $last_week_revenue_rate / 100);




            // 前年比
            $last_year_revenue_rate = ($one_data['last_year_total_revenue'])
                ? round($target_month_revenue / $one_data['last_year_total_revenue'] * 100, 1) : 0;


            $display_table .= <<<HTML
<!-- line2 -->
<tr {$total}>
    <!-- 予想売上-->
    <td class="comma budget_up_down">{$last_week_expected_revenue}</td>
    
    <!-- 週間売上 -->
    <td class="comma budget_up_down">{$last_week_day1_revenue}</td>
    <td class="comma budget_up_down">{$last_week_day2_revenue}</td>
    <td class="comma budget_up_down">{$last_week_day3_revenue}</td>
    <td class="comma budget_up_down">{$last_week_day4_revenue}</td>
    <td class="comma budget_up_down">{$last_week_day5_revenue}</td>
    <td class="comma budget_up_down">{$last_week_day6_revenue}</td>
    <td class="comma budget_up_down">{$last_week_day7_revenue}</td>
    <!-- 週間累計売上 -->
    <td class="comma budget_up_down division_line">{$last_week_total_revenue}</td>
    

    <!-- 予想売上-->
    <td class="comma budget_up_down">{$target_week_expected_revenue}</td>
    <!-- 今週売上 -->
    <td class="comma budget_up_down">{$target_week_day1_revenue}</td>
    <td class="comma budget_up_down">{$target_week_day2_revenue}</td>
    <td class="comma budget_up_down">{$target_week_day3_revenue}</td>
    <td class="comma budget_up_down">{$target_week_day4_revenue}</td>
    <td class="comma budget_up_down">{$target_week_day5_revenue}</td>
    <td class="comma budget_up_down">{$target_week_day6_revenue}</td>
    <td class="comma budget_up_down">{$target_week_day7_revenue}</td>
    
    <!-- 週間累計売上 -->
    <td class="comma budget_up_down left_border">{$target_week_total_revenue}</td>
    
    <!-- 前週差-->
    <td class="comma plus_minus">{$last_week_diff}</td>
    
    <!-- 今月累計売上 -->
    <td class="comma budget_up_down">{$target_month_revenue}</td>
    
    <!-- 予算比 -->
    <td class="comparison percent">{$target_month_revenue_rate}</td>
    <!-- 前年比 -->
    <td class="comparison percent right_border">{$last_year_revenue_rate}</td>
</tr>
HTML;

            /** 3行目はトータルの場合はいらない → ひつようになりました */
            if (!$total_flag) {

                $last_week_man_hour = ($this->target_man_hour[$one_data['shop_data_id']][$last_week_date_list[0]])
                ? $this->target_man_hour[$one_data['shop_data_id']][$last_week_date_list[0]] : 0;


                $last_week_day1_man_hour = $one_data['week_man_hour'. $last_week_date_list[0]];
                $last_week_day2_man_hour = $one_data['week_man_hour'. $last_week_date_list[1]];
                $last_week_day3_man_hour = $one_data['week_man_hour'. $last_week_date_list[2]];
                $last_week_day4_man_hour = $one_data['week_man_hour'. $last_week_date_list[3]];
                $last_week_day5_man_hour = $one_data['week_man_hour'. $last_week_date_list[4]];
                $last_week_day6_man_hour = $one_data['week_man_hour'. $last_week_date_list[5]];
                $last_week_day7_man_hour = $one_data['week_man_hour'. $last_week_date_list[6]];

                $last_week_total_man_hour = $one_data['month_total_man_hour'.$last_week_date_list[0]];

                $target_man_hour = ($this->target_man_hour[$one_data['shop_data_id']][$target_week_date_list[0]])
                ? $this->target_man_hour[$one_data['shop_data_id']][$target_week_date_list[0]] : 0;

                $target_week_day1_man_hour = $one_data['week_man_hour'.$target_week_date_list[0]];
                $target_week_day2_man_hour = $one_data['week_man_hour'.$target_week_date_list[1]];
                $target_week_day3_man_hour = $one_data['week_man_hour'.$target_week_date_list[2]];
                $target_week_day4_man_hour = $one_data['week_man_hour'.$target_week_date_list[3]];
                $target_week_day5_man_hour = $one_data['week_man_hour'.$target_week_date_list[4]];
                $target_week_day6_man_hour = $one_data['week_man_hour'.$target_week_date_list[5]];
                $target_week_day7_man_hour = $one_data['week_man_hour'.$target_week_date_list[6]];

                $target_week_total_man_hour = $one_data['month_total_man_hour'.$target_week_date_list[0]];

                $man_hour_diff = $target_week_total_man_hour - $last_week_total_man_hour;

                $revenue_diff = $target_month_revenue - $target_month_budget;

                $display_table .= <<<HTML
<!-- line3 -->
<tr {$total}>
    <td class="man_hour_budget">{$last_week_man_hour}</td>
    
    <!-- 人時売上 -->
    <td class="comma man_hour_budget_up_down">{$last_week_day1_man_hour}</td>
    <td class="comma man_hour_budget_up_down">{$last_week_day2_man_hour}</td>
    <td class="comma man_hour_budget_up_down">{$last_week_day3_man_hour}</td>
    <td class="comma man_hour_budget_up_down">{$last_week_day4_man_hour}</td>
    <td class="comma man_hour_budget_up_down">{$last_week_day5_man_hour}</td>
    <td class="comma man_hour_budget_up_down">{$last_week_day6_man_hour}</td>
    <td class="comma man_hour_budget_up_down">{$last_week_day7_man_hour}</td>
    
    <!-- 累計人時 -->
    <td class="comma man_hour_budget_up_down division_line">{$last_week_total_man_hour}</td>
    

    
    <td class="comma man_hour_budget2">{$target_man_hour}</td>
    <!-- 人時売上 -->
    <td class="comma man_hour_budget_up_down2">{$target_week_day1_man_hour}</td>
    <td class="comma man_hour_budget_up_down2">{$target_week_day2_man_hour}</td>
    <td class="comma man_hour_budget_up_down2">{$target_week_day3_man_hour}</td>
    <td class="comma man_hour_budget_up_down2">{$target_week_day4_man_hour}</td>
    <td class="comma man_hour_budget_up_down2">{$target_week_day5_man_hour}</td>
    <td class="comma man_hour_budget_up_down2">{$target_week_day6_man_hour}</td>
    <td class="comma man_hour_budget_up_down2">{$target_week_day7_man_hour}</td>
    <!-- 累計人時 -->
    <td class="comma man_hour_budget_up_down left_border">{$target_week_total_man_hour}</td>
    
        <!-- 前週差-->
    <td class="comma plus_minus">{$man_hour_diff}</td>
    
    <!-- 予算売上差 -->
    <td class="comma plus_minus">{$revenue_diff}</td>
    
    <td></td>
    <td class="right_border"></td>
</tr>
HTML;
                /* トータルの時も表示するらしいです...*/
            } else {

                $last_week_diff_total = $last_week_total_revenue - $last_week_total_budget;
                $target_week_diff_total = $target_week_total_revenue - $target_week_total_budget;
                $revenue_diff = $target_month_revenue - $target_month_budget;
                $display_table .= <<<HTML
<!-- line3 -->
<tr {$total}>
    <td></td>
    
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    
    <!-- 週実績 -->
    <td class="comma plus_minus division_line">{$last_week_diff_total}</td>

    
    
    <td></td>
    
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    
    <!-- 週実績 -->
    <td class="comma plus_minus left_border">{$target_week_diff_total}</td>
        
    <td></td>
    <td class="comma plus_minus">{$revenue_diff}</td>
    <td></td>
    <td class="right_border"></td>
</tr>
HTML;
            }


            // 最後の合計だけ4段目が出てくる
            if ($total_flag == 2) {

                $last_day1_rate = $last_week_day1_budget ? round($last_week_day1_revenue/$last_week_day1_budget*100) : 0;
                $last_day2_rate = $last_week_day2_budget ? round($last_week_day2_revenue/$last_week_day2_budget*100) : 0;
                $last_day3_rate = $last_week_day3_budget ? round($last_week_day3_revenue/$last_week_day3_budget*100) : 0;
                $last_day4_rate = $last_week_day4_budget ? round($last_week_day4_revenue/$last_week_day4_budget*100) : 0;
                $last_day5_rate = $last_week_day5_budget ? round($last_week_day5_revenue/$last_week_day5_budget*100) : 0;
                $last_day6_rate = $last_week_day6_budget ? round($last_week_day6_revenue/$last_week_day6_budget*100) : 0;
                $last_day7_rate = $last_week_day7_budget ? round($last_week_day7_revenue/$last_week_day7_budget*100) : 0;

                $last_total_rate = $last_week_total_budget ? round($last_week_total_revenue/$last_week_total_budget*100) : 0;

                $target_day1_rate = $target_week_day1_budget ? round($target_week_day1_revenue/$target_week_day1_budget*100) : 0;
                $target_day2_rate = $target_week_day2_budget ? round($target_week_day2_revenue/$target_week_day2_budget*100) : 0;
                $target_day3_rate = $target_week_day3_budget ? round($target_week_day3_revenue/$target_week_day3_budget*100) : 0;
                $target_day4_rate = $target_week_day4_budget ? round($target_week_day4_revenue/$target_week_day4_budget*100) : 0;
                $target_day5_rate = $target_week_day5_budget ? round($target_week_day5_revenue/$target_week_day5_budget*100) : 0;
                $target_day6_rate = $target_week_day6_budget ? round($target_week_day6_revenue/$target_week_day6_budget*100) : 0;
                $target_day7_rate = $target_week_day7_budget ? round($target_week_day7_revenue/$target_week_day7_budget*100) : 0;

                $target_total_rate = $target_week_total_budget ? round($target_week_total_revenue/$target_week_total_budget*100) : 0;

                $display_table .= <<<HTML
<!-- line4 -->
<tr {$total}>
    <td></td>
    
    <td class="comparison percent">{$last_day1_rate}</td>
    <td class="comparison percent">{$last_day2_rate}</td>
    <td class="comparison percent">{$last_day3_rate}</td>
    <td class="comparison percent">{$last_day4_rate}</td>
    <td class="comparison percent">{$last_day5_rate}</td>
    <td class="comparison percent">{$last_day6_rate}</td>
    <td class="comparison percent">{$last_day7_rate}</td>
    

    <td class="comparison percent division_line">{$last_total_rate}</td>

    
    <td></td>
    
    <td class="comparison percent">{$target_day1_rate}</td>
    <td class="comparison percent">{$target_day2_rate}</td>
    <td class="comparison percent">{$target_day3_rate}</td>
    <td class="comparison percent">{$target_day4_rate}</td>
    <td class="comparison percent">{$target_day5_rate}</td>
    <td class="comparison percent">{$target_day6_rate}</td>
    <td class="comparison percent">{$target_day7_rate}</td>
    
    <!-- 週実績 -->
    <td  class="comparison percent left_border">{$target_total_rate}</td>
        
    <td></td>
    <td></td>
    <td></td>
    <td class="right_border"></td>
</tr>
HTML;
            }

        }
        $this->display_table = $display_table;
    }


    /**
     * @param $shop_id
     * @param $target_date
     */
    protected function isCloseShop($shop_id, $target_date)
    {
        $close_shop_array = $this->close_shop_array;
        if (!$close_shop_array[$shop_id]) {
            return false;
        }

        $close_date = $close_shop_array[$shop_id];
        $datetime = new DateTime($target_date);

        if ($datetime->format('Ym') > $close_date) {
            return true;
        }
        return false;
    }

}
