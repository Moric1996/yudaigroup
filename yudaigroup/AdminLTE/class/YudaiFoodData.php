<?php

/**
 * Class YudaiFoodData
 */
class YudaiFoodData
{

    protected $now_data;
    protected $last_week_data;
    protected $last_year_data;
    protected $last_last_year_data;

    protected $result_array;
    protected $display_table;
    protected $target_date;

    protected $chart_data;

    protected $total_budget;


    protected $special_array = array(
        '105'=>array('date'=>'201909','category'=>9999),//ふたご
        '998'=>array('date'=>'202009','category'=>9999),//串家物語沼津
        '999'=>array('date'=>'202009','category'=>9999),//吉祥庵沼津
        '997'=>array('date'=>'202010','category'=>9999),//いまさん
        '701'=>array('date'=>'202105','category'=>9999),//vansan富士
    );

    protected $close_shop_array = array(
        '107'=>'202008',
        '100'=>'202010',
        '201' => '202108',
        '206' => '202407'
    );



    /**
     * @param $now_date
     */
    public function setTargetData($now_date)
    {
        $this->now_data = $now_date;
    }

    /**
     * @param $total_budget
     */
    public function setTotalBudget($total_budget)
    {
        $result = array();
        foreach ($total_budget as $val) {
            $result[$val['id']] = $val['total_budget'];
        }
        $this->total_budget = $result;
    }

    /**
     * @param $last_week_data
     */
    public function setLastWeekTargetData($last_week_data)
    {
        $this->last_week_data = $last_week_data;
    }

    /**
     * @param $last_year_data
     */
    public function setLastYearData($last_year_data)
    {
        $this->last_year_data = $last_year_data;
    }

    /**
     * @param $last_last_year_data
     */
    public function setLastLastYearData($last_last_year_data)
    {
        $this->last_last_year_data = $last_last_year_data;
    }

    /**
     *
     */
    public function setTargetDate()
    {
        $temp_date = new DateTime($this->getLastDate());
        $this->target_date = $temp_date->format('Ym');
    }

    /**
     * @return mixed
     */
    public function getLastDate()
    {
        $now_data = $this->now_data;
        $temp = end($now_data);
        return $temp['date'];
    }


    /**
     * @param $array
     * @param $name
     * @return array
     */
    private function getTotalColumn($array, $name)
    {
        $return_array = array();
        foreach ($array as $one_shop) {
            foreach ($one_shop as $key => $val) {
                $return_array[$key] += $val;
            }
        }
        return array($name => $return_array);
    }



    /**
     * 対象月のデータ
     * ここで複数のデータを統合する
     */
    public function formatNowData()
    {
        $last_date = new DateTime($this->getLastDate());
        $today = $last_date->format('Y-m-d');
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
            $result_array[$shop_data['category']][$shop_data['name']]['customers_num']
                = ($shop_data['date'] === $today) ? $shop_data['customers_num'] : 0;
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
     * 1年前のデータ
     */
    public function formatLastYearData()
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
            } else if ($shop_data['is_new_shop'] == 't') {
                $shop_data['category'] = 9999;
            }
            // 売上実績前年同日
            $result_array[$shop_data['category']][$shop_data['name']]['last_revenue'] = $shop_data['revenue'];
            // 売上実績前年同日累計
            $result_array[$shop_data['category']][$shop_data['name']]['last_total_revenue'] += $shop_data['revenue'];

            // 客数前年累計
            $result_array[$shop_data['category']][$shop_data['name']]['last_total_customers_num'] += $shop_data['customers_num'];

            // 昨年労働時間 表示は不要
            $result_array[$shop_data['category']][$shop_data['name']]['last_total_work_time'] += $shop_data['work_time'];
        }
        $this->result_array = $result_array;
    }


    /**
     * 2年前のデータ
     */
    public function formatLastLastYearData()
    {
        $last_last_year_data = $this->last_last_year_data;
        $result_array = $this->result_array;
        $special_array = $this->special_array;
        $target_date = $this->target_date;
        foreach ($last_last_year_data as $shop_data) {
            // 閉店した店は処理しない
            if ($this->isCloseShop($shop_data['shop_id'], $target_date)) {
                continue;
            }
            if ($special_array[$shop_data['name']]['date'] && $special_array[$shop_data['name']]['date'] >= $target_date) {
                $shop_data['category'] = $special_array[$shop_data['name']]['category'];
            } else if ($shop_data['is_new_shop'] == 't') {
                $shop_data['category'] = 9999;
            }
            // 売上実績前年同日
            $result_array[$shop_data['category']][$shop_data['name']]['last_last_revenue'] = $shop_data['revenue'];
            // 売上実績前年同日累計
            $result_array[$shop_data['category']][$shop_data['name']]['last_last_total_revenue'] += $shop_data['revenue'];

            // 客数前年累計
            $result_array[$shop_data['category']][$shop_data['name']]['last_last_total_customers_num'] += $shop_data['customers_num'];

            // 昨年労働時間 表示は不要
            $result_array[$shop_data['category']][$shop_data['name']]['last_last_total_work_time'] += $shop_data['work_time'];
        }
        $this->result_array = $result_array;
    }



    /**
     * @return string
     */
    public function getDisplayHtml()
    {
        $result_array = $this->result_array;


        foreach (range(1,8) as $num) {
            if (!empty($result_array[$num])) {
                $this->makeDisplayHtml($result_array[$num]);
                $this->makeDisplayHtml($this->getTotalColumn($result_array[$num], '計'), 1);
            }
        }

        $old_shop_array = array_merge(
            (array)$result_array[1],
            (array)$result_array[2],
            (array)$result_array[3],
            (array)$result_array[4],
            (array)$result_array[5],
            (array)$result_array[6],
            (array)$result_array[7],
            (array)$result_array[8]
        );
        $this->makeDisplayHtml($this->getTotalColumn($old_shop_array, '既存店合計'), 1);

        // 新店舗
        if (!empty($result_array[9999])) {
            $this->makeDisplayHtml($result_array[9999]);
            $this->makeDisplayHtml($this->getTotalColumn($result_array[9999], '新店合計'), 1);

            $all_shop_array = array_merge($old_shop_array, $result_array[9999]);
        } else {
            $all_shop_array = $old_shop_array;
        }

        $this->makeDisplayHtml($this->getTotalColumn($all_shop_array, '全店合計'), 2);

        return $this->display_table;
    }




    /**
     * 月用
     * @param array $result_array
     * @param bool $total_flag
     */
    public function makeDisplayHtml($result_array = array(), $total_flag = false)
    {
        if ($total_flag) {
            $total = "class='total'";
        }

        // 表示
        $display_table = $this->display_table;
        foreach ((array)$result_array as $name => $one_data) {
            $month_total_budget = $one_data['month_total_budget'];

            // 売上
            $revenue = $one_data['revenue'];
            $total_revenue = $one_data['total_revenue'];
            $total_budget = $one_data['total_budget'];
            $achievement_rate = ($one_data['total_budget']) ? round($one_data['total_revenue'] / $one_data['total_budget'] * 100, 1) : 0;
            $yesterday_achievement_rate = ($one_data['yesterday_total_budget']) ?
                round($one_data['yesterday_total_revenue'] / $one_data['yesterday_total_budget'] * 100, 1) : 0;
            // 予算対比の上下
            if ($achievement_rate > $yesterday_achievement_rate) {
                $achievement_rate_updown = "<span>↗︎</span>";
            } else if ($achievement_rate < $yesterday_achievement_rate) {
                $achievement_rate_updown = "<span style='color:red;'>↘︎︎</span>";
            } else {
                $achievement_rate_updown = "<span>→</span>";
            }
            $landing_prospect = ($achievement_rate/100*$month_total_budget);

            // 昨年
            $last_revenue = $one_data['last_revenue'];
            $last_total_revenue = $one_data['last_total_revenue'];
            $revenue_comparison = ($one_data['last_total_revenue']) ? round($one_data['total_revenue'] / $one_data['last_total_revenue'] * 100, 1) : 0;
            // 昨年売上との上下
            if ($revenue > $last_revenue) {
                $revenue_rate_updown = "<span>↗︎</span>";
            } else if ($revenue < $last_revenue) {
                $revenue_rate_updown = "<span style='color:red;'>↘︎︎</span>";
            } else {
                $revenue_rate_updown = "<span>→</span>";
            }

            // 前々年
            $last_last_revenue = $one_data['last_last_revenue'];
            $last_last_total_revenue = $one_data['last_last_total_revenue'];
            $last_last_revenue_comparison = ($one_data['last_last_total_revenue']) ? round($one_data['total_revenue'] / $one_data['last_last_total_revenue'] * 100, 1) : 0;
            // 昨年売上との上下
            if ($revenue > $last_revenue) {
                $last_last_revenue_rate_updown = "<span>↗︎</span>";
            } else if ($revenue < $last_revenue) {
                $last_last_revenue_rate_updown = "<span style='color:red;'>↘︎︎</span>";
            } else {
                $last_last_revenue_rate_updown = "<span>→</span>";
            }


            // 累計客数
            $customers_num = $one_data['customers_num'];
            $total_customers_num = $one_data['total_customers_num'];
            $last_total_customers_num = $one_data['last_total_customers_num'];
            $costomers_num_comparison = $total_customers_num - $last_total_customers_num;

            // 客単価
            $revenue_per_customer = ($one_data['customers_num']) ? round($one_data['revenue'] / $one_data['customers_num']) : 0;
            $total_revenue_per_customer = ($one_data['total_customers_num']) ? round($one_data['total_revenue'] / $one_data['total_customers_num']) : 0;
            $last_total_revenue_per_customer = ($one_data['last_total_customers_num']) ? round($one_data['last_total_revenue'] / $one_data['last_total_customers_num']) : 0;
            $last_total_revenue_per_customer_comparison = $total_revenue_per_customer - $last_total_revenue_per_customer;

            // 労働時間
            $work_time = round($one_data['work_time'], 1);
            $total_work_time = round($one_data['total_work_time'], 1);
            $last_total_work_time = round($one_data['last_total_work_time'], 1);
            $total_work_time_comparison = $total_work_time - $last_total_work_time;

            // 人時売上
            $revenue_per_work_time = ($one_data['work_time']) ? round($one_data['revenue'] / $one_data['work_time']) : 0;
            $total_revenue_per_work_time = ($one_data['total_work_time']) ? round($one_data['total_revenue'] / $one_data['total_work_time']) : 0;
            $last_total_revenue_per_work_time = ($one_data['last_total_work_time']) ? round($one_data['last_total_revenue'] / $one_data['last_total_work_time']) : 0;
            $total_revenue_per_work_time_comparison = $total_revenue_per_work_time - $last_total_revenue_per_work_time;
            // 昨年との人事売上との上下
            if ($total_revenue_per_work_time > $last_total_revenue_per_work_time) {
                $revenue_per_work_time_rate_updown = "<span>↗︎</span>";
            } else if ($total_revenue_per_work_time < $last_total_revenue_per_work_time) {
                $revenue_per_work_time_rate_updown = "<span style='color:red;'>↘︎︎</span>";
            } else {
                $revenue_per_work_time_rate_updown = "<span>→</span>";
            }

            // 値引
            $discount_ticket = $one_data['discount_ticket'];
            $total_discount_ticket = $one_data['total_discount_ticket'];
            $total_discount_ticket_rate = ($one_data['total_revenue']) ? round($one_data['total_discount_ticket'] / $one_data['total_revenue'] * 100, 1) : 0;

            $display_table .= <<<HTML
<tr {$total}>
    <!-- 店舗名 -->
    <th>{$name}</th>
    
    <!-- 週累計-->
    <td class="comma">{$month_total_budget}</td>
    
    <!-- 売上前日 -->
    <td class="comma bold">{$revenue}</td>
    <td class="comma last">{$total_revenue}</td>
    <td class="comma">{$total_budget}</td>
    <td class="percent comparison last brown_border">{$achievement_rate}</td>
    <td style="text-align: center">{$achievement_rate_updown}</td>
    <td class="comma division_line budget_up_down2">{$landing_prospect}</td>
    
    <!-- 前年 -->
    <td class="comma">{$last_revenue}</td>
    <td class="comma">{$last_total_revenue}</td>
    <td class="percent comparison last brown_border">{$revenue_comparison}</td>
    <td class="division_line" style="text-align: center">{$revenue_rate_updown}</td>    
    
    <!-- 前年 -->
    <td class="comma">{$last_last_revenue}</td>
    <td class="comma">{$last_last_total_revenue}</td>
    <td class="percent comparison last brown_border">{$last_last_revenue_comparison}</td>
    <td class="division_line" style="text-align: center">{$last_last_revenue_rate_updown}</td>
    
    <!-- 人時売上 -->
    <td class="comma">{$revenue_per_work_time}</td>
    <td class="comma">{$total_revenue_per_work_time}</td>
    <td class="comma">{$last_total_revenue_per_work_time}</td>
    <td class="comma plus_minus last brown_border">{$total_revenue_per_work_time_comparison}</td>
    <td class="division_line" style="text-align: center">{$revenue_per_work_time_rate_updown}</td>
    
    <!-- 累計客数 -->
    <td class="comma">{$customers_num}</td>
    <td class="comma">{$total_customers_num}</td>
    <td class="comma">{$last_total_customers_num}</td>
    <td class="comma plus_minus last division_line">{$costomers_num_comparison}</td>

    <!-- 客単価 -->
    <td class="comma">{$revenue_per_customer}</td>
    <td class="comma">{$total_revenue_per_customer}</td>
    <td class="comma">{$last_total_revenue_per_customer}</td>
    <td class="comma plus_minus last division_line">{$last_total_revenue_per_customer_comparison}</td>
    
    <!-- 労働時間 -->
    <td class="comma">{$work_time}</td>
    <td class="comma">{$total_work_time}</td>
    <td class="comma">{$last_total_work_time}</td>
    <td class="comma plus_minus last division_line">{$total_work_time_comparison}</td>
    
    <!-- 値引 -->
    <td class="comma">{$discount_ticket}</td>
    <td class="comma">{$total_discount_ticket}</td>
    <td class="percent division_line">{$total_discount_ticket_rate}</td>
</tr>
HTML;
        }
        $this->display_table = $display_table;
    }




     /**
     *
     */
    public function formatLastYearDataforMonthly()
    {
        $last_data = $this->last_year_data;
        $result_array = $this->result_array;
        $special_array = $this->special_array;
        $target_date = $this->target_date;

        // @mail("y-kamata@yournet-jp.com","formatLastYearDataforMonthly last_data",print_r($last_data, true));


        foreach ($last_data as $shop_data) {
            if ($special_array[$shop_data['name']]['date'] && $special_array[$shop_data['name']]['date'] >= $target_date) {
                $shop_data['category'] = $special_array[$shop_data['name']]['category'];
            }

            //月ごとデータに変更
            $date = $shop_data['date'];
            
            $year_month = date('Y-m', strtotime($date. ' +1 year'));

            
            // 売上実績前年同日
            $result_array[$shop_data['category']][$shop_data['name']][$year_month]['last_revenue'] = $shop_data['revenue'];
            // 売上実績前年同日累計
            $result_array[$shop_data['category']][$shop_data['name']][$year_month]['last_total_revenue'] += $shop_data['revenue'];
 
            // 客数前年累計
            $result_array[$shop_data['category']][$shop_data['name']][$year_month]['last_total_customers_num'] += $shop_data['customers_num'];
 
            // 昨年労働時間 表示は不要
            $result_array[$shop_data['category']][$shop_data['name']][$year_month]['last_total_work_time'] += $shop_data['work_time'];

        }
        $this->result_array = $result_array;
    }



    /**
     * 月次用HTML
     */
    public function getMonthlyChartHtml()
    {
        $result_array = $this->result_array;
        
        foreach ($result_array as $index) {
            foreach ($index as $name => $d) {
                foreach ($d as $month => $data) {
                    $budget = $data['total_budget'] ? $data['total_budget'] : 0;                                //予算
                    $revenue = $data['total_revenue'] ? $data['total_revenue'] : 0;                              //売上
                    $customer = $data['total_customers_num'] ? $data['total_customers_num'] : 0;                       //客数
                    $last_customer = $data['last_total_customers_num'] ? $data['last_total_customers_num'] : 0;             //昨年客数
                    $last_revenue = $data['last_total_revenue'] ? $data['last_total_revenue'] : 0;                    //昨年売上
                    $work_time = $data['total_work_time'] ? $data['total_work_time'] : 0;                          //労働時間
                    $last_work_time = $data['last_total_work_time'] ? $data['last_total_work_time'] : 0;                //昨年労働時間
                    $customer_unit_price = ($revenue && $customer) ? $revenue / $customer : 0;                    //客単価
                    $last_customer_unit_price = ($last_revenue && $last_customer) ? $last_revenue / $last_customer : 0;     //昨年客単価
                    $revenue_work = ($revenue && $work_time) ? $revenue / $work_time : 0;                          //人時売上
                    $last_revenue_work = ($last_revenue && $last_work_time) ? $last_revenue / $last_work_time : 0;           //昨年人時売上
                    
                    $tmp = array($revenue, $budget, $last_revenue);                 //売上：実績・予算・前年
                    $chart_data[$name][$month]['売上'] = $tmp;
                    $tmp = array($customer,0,$last_customer);                         //客数：実績・予算・前年
                    $chart_data[$name][$month]['客数'] = $tmp;
                    $tmp = array($customer_unit_price, 0, $last_customer_unit_price);  //労働時間：実績・予算・前年
                    $chart_data[$name][$month]['労働時間'] = $tmp;
                    $tmp = array($revenue_work, 0, $last_revenue_work);                //人時売上：実績・予算・前年
                    $chart_data[$name][$month]['人時売上'] = $tmp;
                }

                $chart1_id = $name."1";
                $chart2_id = $name."2";
                $chart3_id = $name."3";
                $chart4_id = $name."4";

                $content .= <<< HTML

                    <div class="row">
                        <p style="font-size:20px;margin-left:20px;font-weight:bold;">$name</p>    
                        <div class="col-md-3">
                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <p style="font-weight:bold;">売上</p>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                                        <i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <canvas id='$chart1_id'>
                                    </canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="box box-success">
                                <div class="box-header with-border">
                                    <p style="font-weight:bold;">客数</p>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                                        <i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <canvas id='$chart2_id'>
                                    </canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="box box-danger">
                                <div class="box-header with-border">
                                    <p style="font-weight:bold;">労働時間</p>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                                        <i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <canvas id='$chart3_id'>
                                    </canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="box box-warning">
                                <div class="box-header with-border">
                                    <p style="font-weight:bold;">人時売上</p>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                                        <i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <canvas id='$chart4_id'>
                                    </canvas>
                                </div>
                            </div>
                        </div>      
                    </div>

HTML;
            }
        }

        $this->chart_data = $chart_data;

        return $content;
    }



    /**
     * 取得したデータを月ごとに振り分ける
     */
    public function formatMonthlyData()
    {
        $now_data = $this->now_data;
        $result_array = $this->result_array;
        $special_array = $this->special_array;
        $target_date = $this->target_date;

        // @mail("y-kamata@yournet-jp.com","formatMonthlyData",print_r($now_data, true));


        foreach ($now_data as $shop_data) {

            if ($special_array[$shop_data['name']]['date'] && $special_array[$shop_data['name']]['date'] >= $target_date) {
                $shop_data['category'] = $special_array[$shop_data['name']]['category'];
            }

            //月ごとデータに変更
            $date = $shop_data['date'];

            $year_month = date('Y-m', strtotime($date));

            // 売上 一番新しいもの
            $result_array[$shop_data['category']][$shop_data['name']][$year_month]['revenue'] = $shop_data['revenue'];
            // 売上累計
            $result_array[$shop_data['category']][$shop_data['name']][$year_month]['total_revenue'] += $shop_data['revenue'];
            // 予算
            $result_array[$shop_data['category']][$shop_data['name']][$year_month]['total_budget'] += $shop_data['budget'];

            // 客数累計
            $result_array[$shop_data['category']][$shop_data['name']][$year_month]['total_customers_num'] += $shop_data['customers_num'];

            // 労働時間
            $result_array[$shop_data['category']][$shop_data['name']][$year_month]['work_time'] = $shop_data['work_time'];
            // 労働時間累計
            $result_array[$shop_data['category']][$shop_data['name']][$year_month]['total_work_time'] += $shop_data['work_time'];

            // 値引金額
            $result_array[$shop_data['category']][$shop_data['name']][$year_month]['discount_ticket'] = $shop_data['discount_ticket'];
            // 値引金額累計
            $result_array[$shop_data['category']][$shop_data['name']][$year_month]['total_discount_ticket'] += $shop_data['discount_ticket'];
        }
        $this->result_array = $result_array;
    }


    /**
     * 年次業績
     */
    /*
    public function getAnualChartHtml()
    {
        $result_array = $this->result_array;


        $content .= <<< HTML
            
HTML;
        

        foreach ($result_array as $index) {
            foreach ($index as $name => $data) {
                
                //ひとまず売上のみ
                
                $budget = $data['total_budget'] ? $data['total_budget'] : 0;                                //予算
                $revenue = $data['total_revenue'] ? $data['total_revenue'] : 0;                              //売上
                $customer = $data['total_customers_num'] ? $data['total_customers_num'] : 0;                       //客数
                $last_customer = $data['last_total_customers_num'] ? $data['last_total_customers_num'] : 0;             //昨年客数
                $last_revenue = $data['last_total_revenue'] ? $data['last_total_revenue'] : 0;                    //昨年売上
                $work_time = $data['total_work_time'] ? $data['total_work_time'] : 0;                          //労働時間
                $last_work_time = $data['last_total_work_time'] ? $data['last_total_work_time'] : 0;                //昨年労働時間
                $customer_unit_price = ($revenue && $customer) ? $revenue / $customer : 0;                    //客単価
                $last_customer_unit_price = ($last_revenue && $last_customer) ? $last_revenue / $last_customer : 0;     //昨年客単価
                $revenue_work = ($revenue && $work_time) ? $revenue / $work_time : 0;                          //人時売上
                $last_revenue_work = ($last_revenue && $last_work_time) ? $last_revenue / $last_work_time : 0;           //昨年人時売上
                
                $tmp = array($revenue, $budget, $last_revenue);                 //売上：実績・予算・前年
                $chart_data[$name]['売上'] = $tmp;
                $tmp = array($customer,0,$last_customer);                         //客数：実績・予算・前年
                $chart_data[$name]['客数'] = $tmp;
                $tmp = array($customer_unit_price, 0, $last_customer_unit_price);  //労働時間：実績・予算・前年
                $chart_data[$name]['労働時間'] = $tmp;
                $tmp = array($revenue_work, 0, $last_revenue_work);                //人時売上：実績・予算・前年
                $chart_data[$name]['人時売上'] = $tmp;


                $chart1_id = $name."1";
                $chart2_id = $name."2";
                $chart3_id = $name."3";
                $chart4_id = $name."4";

                $content .= <<< HTML

                    <div class="row">
                        <p style="font-size:20px;margin-left:20px;font-weight:bold;">$name</p>    
                        <div class="col-md-3">
                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <p style="font-weight:bold;">売上</p>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                                        <i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <canvas id='$chart1_id'>
                                    </canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="box box-success">
                                <div class="box-header with-border">
                                    <p style="font-weight:bold;">客数</p>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                                        <i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <canvas id='$chart2_id'>
                                    </canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="box box-danger">
                                <div class="box-header with-border">
                                    <p style="font-weight:bold;">労働時間</p>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                                        <i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <canvas id='$chart3_id'>
                                    </canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="box box-warning">
                                <div class="box-header with-border">
                                    <p style="font-weight:bold;">人時売上</p>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                                        <i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <canvas id='$chart4_id'>
                                    </canvas>
                                </div>
                            </div>
                        </div>      
                    </div>

HTML;
            }
        }

        $this->chart_data = $chart_data;

        return $content;
    }
    */

    /**
     * 年次のHTML取得
     */
    public function getAnualChartHtml()
    {
        $result_array = $this->result_array;
        
        foreach ($result_array as $index) {
            foreach ($index as $name => $d) {
                foreach ($d as $month => $data) {
                    $budget = $data['total_budget'] ? $data['total_budget'] : 0;                                //予算
                    $revenue = $data['total_revenue'] ? $data['total_revenue'] : 0;                              //売上
                    $customer = $data['total_customers_num'] ? $data['total_customers_num'] : 0;                       //客数
                    $last_customer = $data['last_total_customers_num'] ? $data['last_total_customers_num'] : 0;             //昨年客数
                    $last_revenue = $data['last_total_revenue'] ? $data['last_total_revenue'] : 0;                    //昨年売上
                    $work_time = $data['total_work_time'] ? $data['total_work_time'] : 0;                          //労働時間
                    $last_work_time = $data['last_total_work_time'] ? $data['last_total_work_time'] : 0;                //昨年労働時間
                    $customer_unit_price = ($revenue && $customer) ? $revenue / $customer : 0;                    //客単価
                    $last_customer_unit_price = ($last_revenue && $last_customer) ? $last_revenue / $last_customer : 0;     //昨年客単価
                    $revenue_work = ($revenue && $work_time) ? $revenue / $work_time : 0;                          //人時売上
                    $last_revenue_work = ($last_revenue && $last_work_time) ? $last_revenue / $last_work_time : 0;           //昨年人時売上
                    
                    $tmp = array($revenue, $budget, $last_revenue);                 //売上：実績・予算・前年
                    $chart_data[$name][$month]['売上'] = $tmp;
                    $tmp = array($customer,0,$last_customer);                         //客数：実績・予算・前年
                    $chart_data[$name][$month]['客数'] = $tmp;
                    $tmp = array($customer_unit_price, 0, $last_customer_unit_price);  //労働時間：実績・予算・前年
                    $chart_data[$name][$month]['労働時間'] = $tmp;
                    $tmp = array($revenue_work, 0, $last_revenue_work);                //人時売上：実績・予算・前年
                    $chart_data[$name][$month]['人時売上'] = $tmp;
                }

                $chart1_id = $name."1";
                $chart2_id = $name."2";
                $chart3_id = $name."3";
                $chart4_id = $name."4";

                $content .= <<< HTML

                    <div class="row">
                        <p style="font-size:20px;margin-left:20px;font-weight:bold;">$name</p>    
                        <div class="col-md-12">
                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <!-- <p style="font-weight:bold;">売上</p> -->
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                                        <i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <canvas id='$chart1_id'>
                                    </canvas>
                                </div>
                            </div>
                        </div>
                    </div>

HTML;
            }
        }

        $this->chart_data = $chart_data;

        return $content;
    }




    /**
     *
     */
    public function getAnualChartData()
    {
        return $this->chart_data;
    }

    /**
     *
     */
    public function getMonthlyChartData()
    {
        return $this->chart_data;
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

        $date = new DateTime($target_date);
        $date->modify('-1 year');

        if ($date->format('Ym') > $close_date) {
            return true;
        }
        return false;
    }



}
