<?
/**
 * Class DBIO
 */
class DBIO
{
    //const CONN_DATA = 'host=192.168.11.99 user=yournet password=finedrink dbname=yudai_admin port=5432';
    const CONN_DATA = 'host=localhost user=yournet dbname=yudai_admin port=5432';
    private $conn;

    /**
     * DBIO constructor.
     */
    public function __construct()
    {
        $this->conn = pg_connect(self::CONN_DATA);
    }

    /**
     * @return array
     */
    public function fetchShopList()
    {
        $sql =<<<SQL
SELECT * 
FROM shop_list
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }


    /**
     * 売上が存在するデータのみ取得する
     * @param $st_date
     * @param $ed_date
     * @param $shop_type
     * @return array
     */
    public function fetchDataTargetPeriod($st_date, $ed_date, $shop_type)
    {
        $sql = <<<SQL
    SELECT 
      sl.scraping_id as id, 
      sl.abbreviation as name, 
      sl.id as shop_id,
      sl.category, 
      sl.is_new_shop, 
      sl.display_order, 
      yd.date,
      yd.revenue, 
      yd.budget,
      yd.customers_num,
      yd.work_time,
      yd.discount_ticket,
      yd.shop_id
    FROM yudai_data_news AS yd
    LEFT OUTER JOIN shop_list AS sl
    ON yd.shop_id = sl.scraping_id
    WHERE yd.date >= $1
    AND yd.date <= $2
    AND sl.shop_type = $3
    AND revenue IS NOT NULL
    ORDER BY yd.date, sl.category, sl.display_order, yd.shop_id
SQL;
        $res = pg_query_params($this->conn, $sql, array($st_date, $ed_date, $shop_type));
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }



    /**
     * 売上がないものも取得する
     * @param $st_date
     * @param $ed_date
     * @param $shop_type
     * @return array
     */
    public function fetchDataTargetPeriodAllData($st_date, $ed_date, $shop_type)
    {
        $sql = <<<SQL
    SELECT 
      sl.scraping_id as id, 
      sl.abbreviation as name, 
      sl.id as shop_id,
      sl.category, 
      sl.is_new_shop, 
      yd.date,
      yd.revenue, 
      yd.budget,
      yd.customers_num,
      yd.work_time,
      yd.discount_ticket
    FROM yudai_data_news AS yd
    LEFT OUTER JOIN shop_list AS sl
    ON yd.shop_id = sl.scraping_id
    WHERE yd.date >= $1
    AND yd.date <= $2
    AND sl.shop_type = $3
    ORDER BY yd.date, sl.category,  yd.shop_id
SQL;
        $res = pg_query_params($this->conn, $sql, array($st_date, $ed_date, $shop_type));
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }


    /**
     * @param $st_date
     * @param $ed_date
     * @param $shop_type
     * @return array
     */
    public function fetchDataTargetTotalBudgetPeriod($st_date, $ed_date, $shop_type)
    {
        $sql = <<<SQL
    SELECT 
      sl.scraping_id as id, 
      SUM(yd.budget) as total_budget
    FROM yudai_data_news AS yd
    LEFT OUTER JOIN shop_list AS sl
    ON yd.shop_id = sl.scraping_id
    WHERE yd.date >= $1
    AND yd.date <= $2
    AND sl.shop_type = $3
    GROUP BY sl.scraping_id
    ORDER BY id
SQL;
        $res = pg_query_params($this->conn, $sql, array($st_date, $ed_date, $shop_type));
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }


    /**
     * @param $st_date
     * @param $ed_date
     * @param $shop_id
     * @return array
     */
    public function fetchDataTargetPeriodSum($st_date, $ed_date, $shop_id)
    {
        $sql = <<<SQL
    SELECT SUM(yd.budget) as budget, SUM(yd.revenue) as revenue, SUM(yd.customers_num) as customers_num, SUM(yd.work_time) as work_time, SUM(yd.discount_ticket) as discount_ticket
    FROM yudai_data_news as yd
    LEFT OUTER JOIN shop_list AS sl
    ON yd.shop_id = sl.scraping_id
    WHERE date >= $1
    AND date <= $2
    AND sl.id = $3
SQL;
        $res = pg_query_params($this->conn, $sql, array($st_date, $ed_date, $shop_id));
        return (pg_num_rows($res) > 0) ? pg_fetch_array($res) : array();

    }

    /**
     * @param $st_date
     * @param $ed_date
     * @param $shop_id
     * @return array
     */
    public function fetchDataTargetShop($st_date, $ed_date, $shop_id)
    {
        $sql = <<<SQL
    SELECT 
      sl.scraping_id as id, 
      sl.abbreviation as name, 
      sl.id as shop_id,
      yd.date,
      yd.revenue, 
      yd.budget,
      yd.customers_num,
      yd.work_time,
      yd.discount_ticket
    FROM yudai_data_news AS yd
    LEFT OUTER JOIN shop_list AS sl
    ON yd.shop_id = sl.scraping_id
    WHERE yd.date >= $1
    AND yd.date <= $2
    AND sl.id = $3
    AND revenue IS NOT NULL
    ORDER BY yd.date
SQL;
        $res = pg_query_params($this->conn, $sql, array($st_date, $ed_date, $shop_id));
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();

    }

    /**
     * ���g�p ����
     * @param $target_date
     * @param $shop_id
     * @return array
     */
    public function fetchManagerMeeting($target_date, $shop_id)
    {
        $sql =<<<SQL
    SELECT labor_cost, purchase_cost, drink_cost
    FROM manager_meeting
    WHERE date = $1
    AND shop_id = $2
SQL;
        $res = pg_query_params($this->conn, $sql, array($target_date, $shop_id));
        return (pg_num_rows($res) > 0) ? pg_fetch_array($res) : array();
    }

    /**
     * @param $target_date
     * @param $shop_id
     * @return array
     */
    public function fetchInfomartInventory($target_date, $shop_id)
    {
        $sql = <<<SQL
    SELECT 
      sl.id,
      info.date,
      info.end_inventory,
      info.inventory,
      info.beginning_inventory
    FROM infomart_inventory AS info
    LEFT OUTER JOIN shop_list AS sl
    ON info.shop_id = sl.infomart_id
    WHERE info.date = $1
    AND sl.id = $2
SQL;
        $res = pg_query_params($this->conn, $sql, array($target_date, $shop_id));

        return (pg_num_rows($res) > 0) ? pg_fetch_array($res) : array();
    }

    /**
     * @param $target_date
     * @param $shop_id
     * @return array
     */
    public function fetchStoreSurvey($target_date, $shop_id)
    {

        $sql = <<<SQL
SELECT
  ss.all_score,
  ss.revisit,
  ss.reception,
  ss.offer,
  ss.cuisine,
  ss.cleanliness,
  ss.all_score_comment,
  ss.revisit_comment,
  ss.reception_comment,
  ss.offer_comment,
  ss.cuisine_comment,
  ss.cleanliness_comment
FROM store_survey as ss 
LEFT OUTER JOIN
shop_list as sl 
ON ss.shop_id = sl.fankuru_id
WHERE ss.date = $1
AND sl.id = $2
SQL;

        $res = pg_query_params($this->conn, $sql, array($target_date,$shop_id));
        return (pg_num_rows($res) > 0) ? pg_fetch_array($res) : array();
    }

    /**
     * �t�@������̃f�[�^�̕��ϒl
     * @param $shop_id
     * @return array
     */
    public function fetchStoreSurveyAverage($shop_id)
    {
        $sql = <<<SQL
    SELECT 
      avg(ss.all_score) as all_score,
      avg(ss.revisit) as revisit,
      avg(ss.reception) as reception,
      avg(ss.offer) as offer,
      avg(ss.cuisine) as cuisine,
      avg(ss.cleanliness) as cleanliness
    FROM store_survey AS ss
    LEFT OUTER JOIN shop_list AS sl
    ON ss.shop_id = sl.fankuru_id
    WHERE sl.id = $1
SQL;
        $res = pg_query_params($this->conn, $sql, array($shop_id));

        return (pg_num_rows($res) > 0) ? pg_fetch_array($res) : array();
    }


    /**
     * @param $shop_id
     * @param $inventory
     * @return resource
     */
    public function updateInventory($shop_id, $inventory, $target_date)
    {
        $sql =<<<SQL
SELECT infomart_id FROM shop_list WHERE id = $1 LIMIT 1
SQL;
        $res = pg_query_params($this->conn, $sql, array($shop_id));
        $infomart_id = pg_fetch_result($res, 0,0);


        $sql =<<<SQL
UPDATE infomart_inventory
SET inventory = $1
WHERE shop_id = $2
AND date = $3
SQL;
        pg_query_params($this->conn, $sql, array($inventory, $infomart_id, $target_date));




        $sql =<<<SQL
INSERT INTO infomart_inventory 
(inventory, shop_id, date) 
SELECT $1, $2, $3
WHERE NOT EXISTS (
    SELECT 1 
    FROM infomart_inventory 
    WHERE shop_id = $2
    AND date = $3
)
SQL;
        pg_query_params($this->conn, $sql, array($inventory, $infomart_id, $target_date));
    }


    /**
     * @param $shop_id
     * @param $target_date
     * @param $fdcost
     */
    public function updateFDCost($shop_id, $target_date, $fdcost)
    {
        $sql =<<<SQL
UPDATE fd_cost
SET 
    food_revenue = $1, 
    food_beginning_inventory = $2,
    food_inventory = $3,
    food_end_inventory = $4,
    drink_revenue = $5,
    drink_beginning_inventory = $6,
    drink_inventory = $7,
    drink_end_inventory = $8
WHERE shop_id = $9
AND date = $10
SQL;
        pg_query_params($this->conn, $sql, array(
            $fdcost['food_revenue'], $fdcost['food_beginning_inventory'], $fdcost['food_purchase'], $fdcost['food_end_inventory'],
            $fdcost['drink_revenue'],$fdcost['drink_beginning_inventory'], $fdcost['drink_purchase'],$fdcost['drink_end_inventory'],
            $shop_id, $target_date));
        $sql =<<<SQL
INSERT INTO fd_cost 
( 
    food_revenue, food_beginning_inventory, food_inventory, food_end_inventory,
    drink_revenue, drink_beginning_inventory, drink_inventory, drink_end_inventory,
    shop_id, date 
) 
SELECT $1, $2, $3, $4, $5, $6, $7, $8, $9, $10
WHERE NOT EXISTS (
    SELECT 1 
    FROM fd_cost 
    WHERE shop_id = $9
    AND date = $10
)
SQL;
        pg_query_params($this->conn, $sql, array(
            $fdcost['food_revenue'], $fdcost['food_beginning_inventory'], $fdcost['food_purchase'], $fdcost['food_end_inventory'],
            $fdcost['drink_revenue'],$fdcost['drink_beginning_inventory'], $fdcost['drink_purchase'],$fdcost['drink_end_inventory'],
            $shop_id, $target_date));
    }


    public function fetchFDCost($target_date, $shop_id)
    {
        $sql =<<<SQL
    SELECT food_revenue, food_beginning_inventory, food_inventory, food_end_inventory,
    drink_revenue, drink_beginning_inventory, drink_inventory, drink_end_inventory
    FROM fd_cost
    WHERE date = $1
    AND shop_id = $2
SQL;
        $res = pg_query_params($this->conn, $sql, array($target_date, $shop_id));
        return (pg_num_rows($res) > 0) ? pg_fetch_array($res) : array();
    }


    /**
     * @param $shop_id
     * @param $target_date
     * @param $direct_cost
     * @param $indirect_cost
     */
    public function upsertLaborCost($shop_id, $target_date, $direct_cost, $indirect_cost)
    {
        $sql =<<<SQL
UPDATE labor_cost
SET direct_labor_cost = $1, 
indirect_labor_cost = $2
WHERE shop_id = $3
AND date = $4
SQL;
        pg_query_params($this->conn, $sql, array($direct_cost, $indirect_cost, $shop_id, $target_date));
        $sql =<<<SQL
INSERT INTO labor_cost 
(direct_labor_cost, indirect_labor_cost, shop_id, date) 
SELECT $1, $2, $3, $4 
WHERE NOT EXISTS (
    SELECT 1 
    FROM labor_cost 
    WHERE shop_id = $3
    AND date = $4
)
SQL;
        pg_query_params($this->conn, $sql, array($direct_cost, $indirect_cost, $shop_id, $target_date));
    }

    /**
     * @param $target_date
     * @param $shop_id
     * @return array
     */
    public function fetchLaborCost($target_date, $shop_id)
    {
        $sql =<<<SQL
    SELECT direct_labor_cost, indirect_labor_cost
    FROM labor_cost
    WHERE date = $1
    AND shop_id = $2
SQL;
        $res = pg_query_params($this->conn, $sql, array($target_date, $shop_id));
        return (pg_num_rows($res) > 0) ? pg_fetch_array($res) : array();

    }


    /**
     * @param $shop_id
     * @param $target_date
     * @param $comment_list
     * @return resource
     */
    public function updateFankuruComment($shop_id, $target_date, $comment_list)
    {
        $sql =<<<SQL
    UPDATE store_survey
    SET 
        all_score_comment = $1,
        revisit_comment = $2,
        reception_comment = $3,
        offer_comment = $4,
        cuisine_comment = $5,
        cleanliness_comment = $6
    WHERE shop_id = (
      SELECT fankuru_id FROM shop_list WHERE id = $7
    )
    AND date = $8
SQL;
        return pg_query_params($this->conn, $sql, array($comment_list['all_score'], $comment_list['revisit'], $comment_list['reception'],
            $comment_list['offer'], $comment_list['cuisine'], $comment_list['cleanliness'],
            $shop_id, $target_date));
    }

    /**
     * @param $shop_id
     * @param $target_date
     * @param $comment_list
     */
    public function upsertDescComment($shop_id, $target_date, $comment_list)
    {

        $sql =<<<SQL
UPDATE desc_comment
    SET 
        revenue_comment = $1,
        food_purchase_comment = $2,
        labor_cost_comment = $3,
        other_comment = $4
WHERE shop_id = $5
AND date = $6
SQL;
        pg_query_params($this->conn, $sql, array($comment_list['revenue_comment'], $comment_list['food_purchase_comment'],
            $comment_list['labor_cost_comment'], $comment_list['other_comment'], $shop_id, $target_date));
        $sql =<<<SQL
INSERT INTO desc_comment 
(revenue_comment, food_purchase_comment, labor_cost_comment, other_comment, shop_id, date) 
SELECT $1, $2, $3, $4, $5, $6 
WHERE NOT EXISTS (
    SELECT 1 
    FROM desc_comment 
    WHERE shop_id = $5
    AND date = $6
)
SQL;
        pg_query_params($this->conn, $sql, array($comment_list['revenue_comment'], $comment_list['food_purchase_comment'],
            $comment_list['labor_cost_comment'], $comment_list['other_comment'], $shop_id, $target_date));
    }

    /**
     * @param $shop_id
     * @param $target_date
     * @return array
     */
    public function fetchDescComment($target_date, $shop_id)
    {
        $sql =<<<SQL
SELECT 
    revenue_comment,
    food_purchase_comment,
    labor_cost_comment,
    other_comment
FROM desc_comment
WHERE shop_id = $1
AND date = $2
SQL;
        $res = pg_query_params($this->conn, $sql, array($shop_id, $target_date));
        return (pg_num_rows($res) > 0) ? pg_fetch_array($res) : array();
    }

    /**
     * @param $target_date
     * @param $shop_id
     * @return array
     */
    public function fetchSalesByTime($target_date, $shop_id)
    {
        $sql =<<<SQL
SELECT 
    lunch_revenue,
    lunch_customers_num,
    lunch_human_time_sales,
    dinner_revenue,
    dinner_customers_num,
    dinner_human_time_sales,
    reservation_revenue,
    reservation_customers_num,
    reservation_human_time_sales,
    free_revenue,
    free_customers_num,
    free_human_time_sales
FROM sales_by_time
WHERE shop_id = $1
AND date = $2
SQL;
        $res = pg_query_params($this->conn, $sql, array($shop_id, $target_date));
        return (pg_num_rows($res) > 0) ? pg_fetch_array($res) : array();
    }

    /**
     * @param $shop_id
     * @param $target_date
     * @param $sales_list
     */
    public function updateSalesByTime($shop_id, $target_date, $sales_list)
    {
        $sql =<<<SQL
    UPDATE sales_by_time
    SET 
        reservation_revenue = $1,
        reservation_customers_num = $2,
        reservation_human_time_sales = $3,
        free_revenue = $4,
        free_customers_num = $5,
        free_human_time_sales = $6
    WHERE shop_id = $7
    AND date = $8
SQL;
        pg_query_params($this->conn, $sql, array($sales_list['reservation_revenue'], $sales_list['reservation_customers_num'], $sales_list['reservation_human_time_sales'],
            $sales_list['free_revenue'], $sales_list['free_customers_num'], $sales_list['free_human_time_sales'],
            $shop_id, $target_date));
        $sql =<<<SQL
INSERT INTO sales_by_time 
(reservation_revenue, reservation_customers_num, reservation_human_time_sales, free_revenue, free_customers_num, free_human_time_sales, shop_id, date) 
SELECT $1, $2, $3, $4, $5, $6, $7, $8 
WHERE NOT EXISTS (
    SELECT 1 
    FROM sales_by_time 
    WHERE shop_id = $7
    AND date = $8
)
SQL;
        pg_query_params($this->conn, $sql, array($sales_list['reservation_revenue'], $sales_list['reservation_customers_num'], $sales_list['reservation_human_time_sales'],
            $sales_list['free_revenue'], $sales_list['free_customers_num'], $sales_list['free_human_time_sales'],
            $shop_id, $target_date));
    }


    /**
     * @param $shop_id
     * @param $target_date
     * @param $code_string
     * @return array
     */
    public function fetchShopPerformanceMonth($shop_id, $target_date, $code_string)
    {
        $sql = <<<SQL
SELECT el.code, el.name, sp.value, el,type
FROM shop_performance_month as sp
INNER JOIN expense_list as el
ON sp.expense_code = el.code
WHERE sp.shop_id = '{$shop_id}'
AND sp.month = '{$target_date}' 
AND sp.expense_code IN ({$code_string})
ORDER BY sp.expense_code
SQL;
        $res = pg_query($this->conn, $sql);

        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }

    /**
     * @param $shop_id
     * @param $target_date
     * @return array
     */
    public function fetchShopPerformanceMonthSub($shop_id, $target_date)
    {
        $sql = <<<SQL
SELECT el.code, el.name, sp.value, el,type
FROM shop_performance_month as sp
INNER JOIN expense_list as el
ON sp.expense_code = el.code
WHERE sp.shop_id = '{$shop_id}'
AND sp.month = '{$target_date}' 
ORDER BY sp.expense_code
SQL;
        $res = pg_query($this->conn, $sql);

        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }


    /**
     * @param $st_date
     * @return array
     */
    public function fetchShopAllPerformanceMonthAll($target_date)
    {
        $sql = <<<SQL
SELECT el.code, el.name, SUM(sp.value) as value, MAX(el.type) as type
FROM shop_performance_month as sp
INNER JOIN expense_list as el
ON sp.expense_code = el.code
WHERE sp.month = '{$target_date}' 
GROUP BY el.code, el.name, sp.expense_code
ORDER BY sp.expense_code
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }


    /**
     * @param $st_date
     * @param $ed_date
     * @return array
     */
    public function fetchShopAllPerformanceMonth($st_date, $ed_date)
    {
        $sql = <<<SQL
SELECT el.code, el.name, SUM(sp.value) as value, MAX(el.type) as type
FROM shop_performance_month as sp
INNER JOIN expense_list as el
ON sp.expense_code = el.code
WHERE sp.month >= '{$st_date}' AND sp.month <= '{$ed_date}'
GROUP BY el.code, el.name, sp.expense_code
ORDER BY sp.expense_code
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }

    /**
     * @param $shop_id
     * @param $st_date
     * @param $ed_date
     * @return array
     */
    public function fetchShopPerformanceYear($shop_id, $st_date, $ed_date)
    {
        $sql = <<<SQL
SELECT el.code, el.name, sum(sp.value) as value, MAX(el.type) as type
FROM shop_performance_month as sp
INNER JOIN expense_list as el
ON sp.expense_code = el.code
WHERE sp.shop_id = '{$shop_id}'
AND sp.month >= '{$st_date}' AND sp.month <= '{$ed_date}'
GROUP BY el.code,el.name, sp.expense_code
ORDER BY el.code
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }


    public function fetchGroupRevenueMonth($shop_id_string, $target_date) {
        $sql = <<<SQL
SELECT el.code, el.name, sum(sp.value) as value, MAX(el.type) as type
FROM shop_performance_month as sp
INNER JOIN expense_list as el
ON sp.expense_code = el.code
WHERE sp.shop_id IN ({$shop_id_string})
AND sp.month = '{$target_date}'
GROUP BY el.code,el.name, sp.expense_code
ORDER BY el.code
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }


    public function fetchGroupRevenueAll($shop_id_string, $st_date, $ed_date) {
        $sql = <<<SQL
SELECT el.code, el.name, sum(sp.value) as value, MAX(el.type) as type
FROM shop_performance_month as sp
INNER JOIN expense_list as el
ON sp.expense_code = el.code
WHERE sp.shop_id IN ({$shop_id_string})
AND sp.month >= '{$st_date}' AND sp.month <= '{$ed_date}'
GROUP BY el.code,el.name, sp.expense_code
ORDER BY el.code
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }


    /**
     * @param $shop_id
     * @param $target_date
     * @param $work_revenue
     */
    public function upsertTargetManHour($shop_id, $target_date, $work_revenue)
    {
        $sql =<<<SQL
UPDATE man_hour
SET shop_id = $1, 
date = $2,
target_man_hour = $3
WHERE shop_id = $1
AND date = $2
SQL;
        pg_query_params($this->conn, $sql, array($shop_id, $target_date, $work_revenue));
        $sql =<<<SQL
INSERT INTO man_hour 
(shop_id, date, target_man_hour) 
SELECT $1, $2, $3
WHERE NOT EXISTS (
    SELECT 1 
    FROM man_hour 
    WHERE shop_id = $1
    AND date = $2
)
SQL;
        pg_query_params($this->conn, $sql, array($shop_id, $target_date, $work_revenue));
    }

    /**
     * @param $target_date
     * @return array
     */
    public function fetchTargetMonthManHour($target_date)
    {
        $sql = <<<SQL
SELECT shop_id, target_man_hour
FROM man_hour 
WHERE date = '{$target_date}'
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }


    /**
     * @param $st
     * @param $ed
     * @return array
     */
    public function fetchTargetWeather($st, $ed)
    {
        $sql = <<<SQL
SELECT * 
FROM date_weather
WHERE date >=  '{$st}'
AND date <= '{$ed}'
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }

    /**
     * @return array
     */
    public function fetchManHour()
    {
        $sql = <<<SQL
SELECT shop_id, target_man_hour, date
FROM man_hour
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }


    /**
     * ランチ売上
     * @param $st
     * @param $ed
     * @param $shop_id
     * @return array
     */
    public function fetchLunchRevenue($st, $ed, $shop_id)
    {
        $sql =<<<SQL
SELECT sale_date, SUM(sale_notax_price) as revenue
FROM lunch_dinner
WHERE shop_id = '{$shop_id}'
AND ld_type = 1
AND status = '1'
AND sale_date >=  '{$st}'
AND sale_date <= '{$ed}'
GROUP BY sale_date
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }


    /**
     * ランチ売上合計
     * @param $st
     * @param $ed
     * @param $shop_id
     * @return string
     */
    public function fetchLunchRevenueSum($st, $ed, $shop_id)
    {
        $sql =<<<SQL
SELECT SUM(sale_notax_price)
FROM lunch_dinner
WHERE shop_id = '{$shop_id}'
AND ld_type = 1
AND status = '1'
AND sale_date >=  '{$st}'
AND sale_date <= '{$ed}'
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_result($res, 0, 0) : 0;
    }

    /**
     * ランチ客数
     * @param $st
     * @param $ed
     * @param $shop_id
     * @return string
     */
    public function fetchLunchCustomerSum($st, $ed, $shop_id)
    {
        $sql =<<<SQL
SELECT SUM(num_custom)
FROM lunch_dinner
WHERE shop_id = '{$shop_id}'
AND ld_type = 1
AND status = '1'
AND sale_date >=  '{$st}'
AND sale_date <= '{$ed}'
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_result($res, 0, 0) : 0;
    }

    /**
     * ディナー売上
     * @param $st
     * @param $ed
     * @param $shop_id
     * @return string
     */
    public function fetchDinnerRevenue($st, $ed, $shop_id)
    {
        $sql =<<<SQL
SELECT sale_date, SUM(sale_notax_price) as revenue
FROM lunch_dinner
WHERE shop_id = '{$shop_id}'
AND ld_type = 2
AND status = '1'
AND sale_date >=  '{$st}'
AND sale_date <= '{$ed}'
GROUP BY sale_date
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }


    /**
     * ディナー売上
     * @param $st
     * @param $ed
     * @param $shop_id
     * @return string
     */
    public function fetchDinnerRevenueSum($st, $ed, $shop_id)
    {
        $sql =<<<SQL
SELECT SUM(sale_notax_price)
FROM lunch_dinner
WHERE shop_id = '{$shop_id}'
AND ld_type = 2
AND status = '1'
AND sale_date >=  '{$st}'
AND sale_date <= '{$ed}'
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_result($res, 0, 0) : 0;
    }


    /**
     * ディナー客数
     * @param $st
     * @param $ed
     * @param $shop_id
     * @return string
     */
    public function fetchDinnerCustomerSum($st, $ed, $shop_id)
    {
        $sql =<<<SQL
SELECT SUM(num_custom)
FROM lunch_dinner
WHERE shop_id = '{$shop_id}'
AND ld_type = 2
AND status = '1'
AND sale_date >=  '{$st}'
AND sale_date <= '{$ed}'
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_result($res, 0, 0) : 0;
    }


    /**
     * 月別の店舗予算を取得する
     * @param $shop_id
     * @param $target_date
     * @return array
     */
    public function fetchShopBudgetMonthPlan($shop_id, $target_date)
    {
        $sql = <<<SQL
SELECT el.code, el.name, sp.value, el,type
FROM shop_budget_month as sp
INNER JOIN expense_list as el
ON sp.expense_code = el.code
WHERE sp.shop_id = '{$shop_id}'
AND sp.month = '{$target_date}' 
AND sp.category = 1
ORDER BY sp.expense_code
SQL;
        $res = pg_query($this->conn, $sql);

        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }


    /**
     * 月別の店舗予測を取得する
     * @param $shop_id
     * @param $target_date
     * @return array
     */
    public function fetchShopBudgetMonthPredict($shop_id, $target_date)
    {
        $sql = <<<SQL
SELECT el.code, el.name, sp.value, el,type
FROM shop_budget_month as sp
INNER JOIN expense_list as el
ON sp.expense_code = el.code
WHERE sp.shop_id = '{$shop_id}'
AND sp.month = '{$target_date}' 
AND sp.category = 2 
ORDER BY sp.expense_code
SQL;
        $res = pg_query($this->conn, $sql);

        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }


    /**
     * 対象店舗対象月の標準単価を取得する
     * @param $shop_id
     * @param $target_date
     * @return string
     */
    public function fetchStandardUnitPrice($shop_id, $target_date)
    {
        $sql = <<<SQL
SELECT value
FROM standard_unit_price
WHERE shop_id = '{$shop_id}'
AND month = '{$target_date}' 
LIMIT 1
SQL;
        $res = pg_query($this->conn, $sql);

        return (pg_num_rows($res) > 0) ? pg_fetch_result($res, 0, 0) : 0;
    }

    /**
     * 対象店舗対象月の標準単価を作成更新する
     * @param $shop_id
     * @param $target_date
     * @param $value
     * @return string
     */
    public function upsertStandardUnitPrice($shop_id, $target_date, $value)
    {
        $sql =<<<SQL
UPDATE standard_unit_price
SET value = $1
WHERE shop_id = $2
AND month = $3
SQL;
        pg_query_params($this->conn, $sql, array($value, $shop_id, $target_date));
        $sql =<<<SQL
INSERT INTO standard_unit_price 
(value, shop_id, month) 
SELECT $1, $2, $3
WHERE NOT EXISTS (
    SELECT 1 
    FROM standard_unit_price 
    WHERE shop_id = $2
    AND month = $3
)
SQL;
        pg_query_params($this->conn, $sql, array($value, $shop_id, $target_date));
    }


    /**
     * 対象期間の温泉日報データ取得
     * @param $st
     * @param $ed
     * @return array
     */
    public function fetchOnsenDailyWorkReport($st, $ed)
    {
        $sql =<<<SQL
SELECT date, category_id, value
FROM onsen_daily_work_report
WHERE date >=  '{$st}'
AND date <= '{$ed}'
order by date
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }

    /**
     * 温泉の予算を取得する
     * @param $st
     * @param $ed
     * @return array
     */
    public function fetchOnsenBudget($st, $ed)
    {
        $sql =<<<SQL
SELECT date, type, value
FROM onsen_budget
WHERE date >=  '{$st}'
AND date <= '{$ed}'
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }

    /**
     * 温泉合計予算取得
     * @param $st
     * @param $ed
     * @return int
     */
    public function fetchOnsenMonthBudgetSum($st, $ed) {
        $sql =<<<SQL
SELECT sum(value)
FROM onsen_budget
WHERE date >=  '{$st}'
AND date <= '{$ed}'
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? (integer)pg_fetch_result($res, 0, 0) : 0;
    }

    /**
     * 売上合計取得
     * @param $st
     * @param $ed
     * @return int
     */
    public function fetchOnsenDailyWorkReportSum($st, $ed, $category_id) {
        $sql =<<<SQL
SELECT sum(value)
FROM onsen_daily_work_report
WHERE date >=  '{$st}'
AND date <= '{$ed}'
AND category_id = '{$category_id}'
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? (integer)pg_fetch_result($res, 0, 0) : 0;
    }

    /**
     * 温泉売上がある最終日
     * @param $st
     * @param $ed
     * @return int
     */
    public function fetchOnsenDailyWorkReportLastDate($st, $ed, $category_id) {
        $sql =<<<SQL
SELECT max(date)
FROM onsen_daily_work_report
WHERE date >=  '{$st}'
AND date <= '{$ed}'
AND category_id = '{$category_id}'
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_result($res, 0, 0) : '';
    }

    /**
     * 月間のカテゴリーごとの合計取得
     * @param $st
     * @param $ed
     * @return array
     */
    public function fetchOnsenMonthlyReportSum($st, $ed) {
        $sql =<<<SQL
SELECT category_id, sum(value)
FROM onsen_daily_work_report
WHERE date >=  '{$st}'
AND date <= '{$ed}'
GROUP BY category_id
SQL;
        $res = pg_query($this->conn, $sql);
        return (pg_num_rows($res) > 0) ? pg_fetch_all($res) : array();
    }

    /**
     * 温泉予算の更新
     * @param $date
     * @param $type
     * @param $value
     * @return void
     */
    public function upsertOnsenBudget($date, $type, $value) {
        $sql =<<<SQL
UPDATE onsen_budget
SET value = $1
WHERE date = $2
AND type = $3
SQL;
        pg_query_params($this->conn, $sql, array($value, $date, $type));
        $sql =<<<SQL
INSERT INTO onsen_budget 
(value, date, type) 
SELECT $1, $2, $3
WHERE NOT EXISTS (
    SELECT 1 
    FROM onsen_budget 
    WHERE date = $2
    AND type = $3
)
SQL;
        pg_query_params($this->conn, $sql, array($value, $date, $type));
    }
}
