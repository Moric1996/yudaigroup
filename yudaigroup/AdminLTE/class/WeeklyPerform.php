<?php

/**
 * Class WeeklyPerform
 */
class WeeklyPerform extends YudaiFoodData
{


    public function setTargetDate($target_date)
    {
        $this->target_date = $target_date;
    }

    /**
     * 対象月のデータ
     * ここで複数のデータを統合する
     */
    public function formatLastWeekData()
    {
        $last_date = new DateTime($this->getLastDate());
        $last_date->modify('-1 days');
        $yesterday = $last_date->format('Y-m-d');

        $last_week_data = $this->last_week_data;
        $result_array = $this->result_array;
        $special_array = $this->special_array;
        $target_date = $this->target_date;
        foreach ($last_week_data as $shop_data) {
            // 閉店した店は処理しない
            if ($this->isCloseShop($shop_data['shop_id'], $target_date)) {
                continue;
            }

            // 設定されたよりも前は新店舗扱いにする
            if ($special_array[$shop_data['shop_id']]['date'] && $special_array[$shop_data['shop_id']]['date'] >= $target_date) {
                $shop_data['category'] = $special_array[$shop_data['shop_id']]['category'];
                // 新店舗だったらnewにする
            } else if ($shop_data['is_new_shop'] == 't') {
                $shop_data['category'] = 9999;
            }


            // 売上累計
            $result_array[$shop_data['category']][$shop_data['name']]['last_week_revenue'] += $shop_data['revenue'];
            // 予算
            $result_array[$shop_data['category']][$shop_data['name']]['last_week_budget'] += $shop_data['budget'];

            // 客数累計
            $result_array[$shop_data['category']][$shop_data['name']]['last_week_customers_num'] += $shop_data['customers_num'];

            // 労働時間
            $result_array[$shop_data['category']][$shop_data['name']]['last_week_work_time'] += $shop_data['work_time'];
        }
        $this->result_array = $result_array;
    }



    /**
     * 週間処理用のフォーマット
     *
     */
    public function formatWeeklyData()
    {
        $last_date = new DateTime($this->getLastDate());
        $last_date->modify('-1 days');
        $yesterday = $last_date->format('Y-m-d');

        $now_data = $this->now_data;
        $result_array = $this->result_array;
        $special_array = $this->special_array;
        $target_date = $this->target_date;
        $total_budget = $this->total_budget;
        foreach ($now_data as $shop_data) {
            // 閉店した店は処理しない
            if ($this->isCloseShop($shop_data['shop_id'], $target_date)) {
                continue;
            }

            // 設定されたよりも前は新店舗扱いにする
            if ($special_array[$shop_data['shop_id']]['date'] && $special_array[$shop_data['shop_id']]['date'] >= $target_date) {
                $shop_data['category'] = $special_array[$shop_data['shop_id']]['category'];
                // 新店舗だったらnewにする
            } else if ($shop_data['is_new_shop'] == 't') {
                $shop_data['category'] = 9999;
            }

            // 月合計予算
            $result_array[$shop_data['category']][$shop_data['name']]['month_total_budget'] = $total_budget[$shop_data['id']];

            // 最終日前日のデータ
            if ($yesterday == $shop_data['date']) {
                $result_array[$shop_data['category']][$shop_data['name']]['yesterday_total_revenue'] = $result_array[$shop_data['category']][$shop_data['name']]['total_revenue'];
                $result_array[$shop_data['category']][$shop_data['name']]['yesterday_total_budget'] = $result_array[$shop_data['category']][$shop_data['name']]['total_budget'];
            }

            // 売上 一番新しいもの
            $result_array[$shop_data['category']][$shop_data['name']]['revenue'] = $shop_data['revenue'];
            // 売上累計
            $result_array[$shop_data['category']][$shop_data['name']]['total_revenue'] += $shop_data['revenue'];
            // 予算
            $result_array[$shop_data['category']][$shop_data['name']]['total_budget'] += $shop_data['budget'];

            // 客数累計
            $result_array[$shop_data['category']][$shop_data['name']]['total_customers_num'] += $shop_data['customers_num'];

            // 労働時間
            $result_array[$shop_data['category']][$shop_data['name']]['work_time'] = $shop_data['work_time'];
            // 労働時間累計
            $result_array[$shop_data['category']][$shop_data['name']]['total_work_time'] += $shop_data['work_time'];

            // 値引金額
            $result_array[$shop_data['category']][$shop_data['name']]['discount_ticket'] = $shop_data['discount_ticket'];
            // 値引金額累計
            $result_array[$shop_data['category']][$shop_data['name']]['total_discount_ticket'] += $shop_data['discount_ticket'];
        }
        $this->result_array = $result_array;
    }


    /**
     * 週間処理の1年前のデータ
     */
    public function formatWeeklyLastYearData()
    {
        $last_year_data = $this->last_year_data;
        $result_array = $this->result_array;
        $special_array = $this->special_array;
        $target_date = $this->target_date;
        foreach ($last_year_data as $shop_data) {
            // 閉店した店は処理しない
            if ($this->isCloseShop($shop_data['shop_id'], $target_date)) {
                continue;
            }
            if ($special_array[$shop_data['name']]['date'] && $special_array[$shop_data['name']]['date'] >= $target_date) {
                $shop_data['category'] = $special_array[$shop_data['name']]['category'];
            }
            // 売上実績前年同日
            $result_array[$shop_data['category']][$shop_data['name']]['last_revenue'] = $shop_data['revenue'];
            // 売上実績前年同日累計
            $result_array[$shop_data['category']][$shop_data['name']]['last_total_revenue'] += $shop_data['revenue'];

            $result_array[$shop_data['category']][$shop_data['name']]['last_total_budget'] += $shop_data['budget'];

            // 客数前年累計
            $result_array[$shop_data['category']][$shop_data['name']]['last_total_customers_num'] += $shop_data['customers_num'];

            // 昨年労働時間 表示は不要
            $result_array[$shop_data['category']][$shop_data['name']]['last_total_work_time'] += $shop_data['work_time'];
        }
        $this->result_array = $result_array;
    }



    /**
     * @param $result_array
     */
    public function makeDisplayHtml($result_array = array(), $total_flag = false)
    {
        if ($total_flag) {
            $total = "class='total'";
        }

        // 表示
        $display_table = $this->display_table;
        foreach ((array)$result_array as $name => $one_data) {

            // 売上
            $revenue = $one_data['total_revenue'];
            $last_week_revenue = $one_data['last_week_revenue'];
            $revenue_difference = $revenue - $last_week_revenue;
            $revenue_rate = ($last_week_revenue) ? round($revenue / $last_week_revenue * 100, 1) : 0;
            // 売上対比の上下
            if ($revenue > $last_week_revenue) {
                $revenue_rate_updown = "<span>↗</span>";
            } else if ($revenue < $last_week_revenue) {
                $revenue_rate_updown = "<span style='color:red;'>↘</span>";
            } else {
                $revenue_rate_updown = "<span>→</span>";
            }

            // 予算
            $budget = $one_data['total_budget'];
            $budget_rate = ($budget) ? round($revenue / $budget * 100, 1) : 0;
            $last_week_budget = $one_data['last_week_budget'];
            $last_week_budget_rate = ($last_week_budget) ? round($last_week_revenue / $last_week_budget * 100, 1) : 0;
            $budget_rate_rate = $budget_rate - $last_week_budget_rate;
            // 予算対比の上下
            if ($budget_rate_rate > 100) {
                $budget_rate_updown = "<span>↗</span>";
            } else if ($budget_rate_rate < 100) {
                $budget_rate_updown = "<span style='color:red;'>↘</span>";
            } else {
                $budget_rate_updown = "<span>→</span>";
            }

            // 昨年
            $last_total_revenue = $one_data['last_total_revenue'];
            $last_total_budget = $one_data['last_total_budget'];
            $last_revenue_difference = $revenue - $last_total_revenue;
            $last_revenue_rate = ($last_total_revenue) ? round($revenue / $last_total_revenue * 100, 1) : 0;
            $last_revenue_rate_rate = $budget_rate - (($last_total_budget) ? round($last_total_revenue / $last_total_budget * 100, 1) : 0);
            // 売上対比の上下
            if ($revenue > $last_total_revenue) {
                $last_revenue_rate_updown = "<span>↗</span>";
            } else if ($revenue < $last_total_revenue) {
                $last_revenue_rate_updown = "<span style='color:red;'>↘</span>";
            } else {
                $last_revenue_rate_updown = "<span>→</span>";
            }

            // 累計客数
            $customers_num = $one_data['total_customers_num'];
            $last_week_customers_num = $one_data['last_week_customers_num'];
            $customers_num_difference = $customers_num - $last_week_customers_num;
            $customers_num_rate = ($last_week_customers_num) ? round($customers_num / $last_week_customers_num * 100, 1) : 0;
            // 客数対比の上下
            if ($customers_num_rate > 100) {
                $customers_num_rate_updown = "<span>↗</span>";
            } else if ($customers_num_rate < 100) {
                $customers_num_rate_updown = "<span style='color:red;'>↘︎</span>";
            } else {
                $customers_num_rate_updown = "<span>→</span>";
            }

            // 労働時間
            $total_work_time = round($one_data['total_work_time'], 1);
            $last_week_work_time = $one_data['last_week_work_time'];
            // 人時売上
            $revenue_per_work_time = ($total_work_time) ? round($revenue / $total_work_time) : 0;
            $last_week_revenue_per_work_time = ($last_week_work_time) ? round($last_week_revenue / $last_week_work_time) : 0;
            $revenue_per_work_time_difference = $revenue_per_work_time - $last_week_revenue_per_work_time;
            $revenue_per_work_time_rate = ($last_week_revenue_per_work_time) ? round($revenue_per_work_time / $last_week_revenue_per_work_time * 100, 1) : 0;
            // 人時売上の上下
            if ($revenue_per_work_time_rate > 100) {
                $revenue_per_work_time_updown = "<span>↗︎</span>";
            } else if ($revenue_per_work_time_rate < 100) {
                $revenue_per_work_time_updown = "<span style='color:red;'>↘︎</span>";
            } else {
                $revenue_per_work_time_updown = "<span>→</span>";
            }

            // 週成績
            // 売上上がった維持
            $uu = $ud = $du = $dd = '';
            if ($revenue >= $last_week_revenue) {
                // 人時売上上がった維持
                if ($revenue_per_work_time_rate >= 100) {
                    $uu = '○';
                } else {
                    $ud = '○';
                }
                // 売上下がった
            } else {
                // 人時売上上がった維持
                if ($revenue_per_work_time_rate >= 100) {
                    $du = '○';
                } else {
                    $dd = '○';
                }
            }

            $display_table .= <<<HTML
<tr {$total}>
    <!-- 店舗名 -->
    <th>{$name}</th>
    
    <td class="comma division_line">{$revenue}</td>
    
    <!-- 売上 -->
    <td class="comma">{$last_week_revenue}</td>
    <td class="comma plus_minus">{$revenue_difference}</td>
    <td class="percent comparison">{$revenue_rate}</td>
    <td class="division_line" style="text-align: center">{$revenue_rate_updown}</td>
    
    <!-- 予算 -->
    <td class="comma">{$budget}</td>
    <td class="percent comparison">{$budget_rate}</td>
    <td class="percent_plus_minus">{$budget_rate_rate}</td>
    <td class="division_line" style="text-align: center">{$budget_rate_updown}</td>
    
    <!-- 前年 -->
    <td class="comma">{$last_total_revenue}</td>
    <td class="percent comparison">{$last_revenue_rate}</td>
    <td class="percent_plus_minus">{$last_revenue_rate_rate}</td>
    <td class="division_line" style="text-align: center">{$last_revenue_rate_updown}</td>
    
    <!-- 客数 -->
    <td class="comma">{$customers_num}</td>
    <td class="comma">{$last_week_customers_num}</td>
    <td class="comma plus_minus">{$customers_num_difference}</td>
    <td class="percent comparison">{$customers_num_rate}</td>
    <td class="division_line" style="text-align: center">{$customers_num_rate_updown}</td>

    <!-- 人事売上 -->
    <td class="comma">{$revenue_per_work_time}</td>
    <td class="comma">{$last_week_revenue_per_work_time}</td>
    <td class="comma plus_minus">{$revenue_per_work_time_difference}</td>
    <td class="percent comparison">{$revenue_per_work_time_rate}</td>
    <td class="division_line" style="text-align: center">{$revenue_per_work_time_updown}</td>
    
    <!-- 週成績 -->
    <td class="" style="text-align: center">{$uu}</td>
    <td class="" style="text-align: center">{$ud}</td>
    <td class="" style="text-align: center">{$du}</td>
    <td class="" style="text-align: center">{$dd}</td>
</tr>
HTML;
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

        if ($target_date > $close_date) {
            return true;
        }
        return false;
    }

}