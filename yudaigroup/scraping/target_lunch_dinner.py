# coding: UTF-8


# firefoxのゾンビプロセスを量産しないために
try:

    import datetime
    import time

    from selenium import webdriver

    from selenium.webdriver.common.by import By
    from selenium.webdriver.support.ui import WebDriverWait
    from selenium.webdriver.support import expected_conditions as EC
    from selenium.webdriver.support.ui import Select

    from bs4 import BeautifulSoup

    import psycopg2 
    import psycopg2.extras
    import os


    #options = webdriver.ChromeOptions()
    options = webdriver.FirefoxOptions()
    # headlessモード
    options.add_argument('--headless') 
    # UA
    options.add_argument('--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:62.0) Gecko/20100101 Firefox/62.0')

    # ドライバーを読ませる
    #driver = webdriver.Chrome('C:/Users/WEB-Kageshima/Desktop/python/test/chromedriver.exe',options=options) 
    driver = webdriver.Firefox(executable_path='/usr/local/htdocs/yudaigroup/scraping/geckodriver', options=options) 
    #driver = webdriver.Firefox(executable_path='/usr/local/htdocs/kageshima/yudai_admin/scraping/geckodriver', options=options) 


    # Timeout 50秒（最大待ち時間） なかなか要素が出てこないときがあるので長めに
    wait = WebDriverWait(driver, 50)


    # 月末
    def end_of_month(date):
        import calendar
        # calendar.monthrangeは年と月を渡すと、月初と月末の日をタプルで返す
        last_day = calendar.monthrange(date.year, date.month)[1]
        date = date.replace(day=last_day)
        return date

    target_date = datetime.date.today() - datetime.timedelta(days=15)
    #target_date = datetime.datetime(2018, 12, 1)

    fifteen_days_ago = (target_date).replace(day=1)

    target_first_date = '2019/01/01'
    target_last_date = '2019/01/31'



    # ログインページへアクセス
    driver.get('https://www.bino-fs.com/') 
    xpath = "//input[@name='UserID']"
    login_id = wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )
    login_id.send_keys('yudaigroup')
    xpath = "//input[@name='password']"
    login_pass = wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )
    login_pass.send_keys('61dsku')

    xpath = "//input[@name='Submit']"
    wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    ).click()


    time.sleep(5)



    #postgreSQLに接続（接続情報は環境変数、PG_XXX）
    connection = psycopg2.connect(
        host='localhost',
        user='yournet',
        database='yudai_admin',
        port=5432)
    # クライアントプログラムのエンコードを設定（DBの文字コードから自動変換してくれる）
    connection.set_client_encoding('utf-8')
    # select結果を辞書形式で取得するように設定
    connection.cursor_factory=psycopg2.extras.DictCursor
    # カーソルの取得
    cursor = connection.cursor()
    cursor2 = connection.cursor()

    # 店舗の取得
    cursor2 = connection.cursor()
    cursor2.execute('SELECT id, scraping_id FROM shop_list WHERE scraping_id IS NOT NULL')
    shop_id_list = cursor2.fetchall()
    cursor2.close()




    for shop in shop_id_list:
        shop_id = shop[0]
        scraping_id = shop[1]

        # 年月指定
        driver.get('https://www.bino-fs.com/list/MakeReport.asp?kubun=1&rptno=44&ShopCode='+scraping_id+'&chkTanpin=&ShopNum=&InAll=&DateFrom='+target_first_date+'&DateTo='+target_last_date+'&myGCCmode=0&curPage=0')

        # テーブル
        xpath = "/html/body/div[2]/table/tbody/tr/td/table"
        wait.until(
            EC.visibility_of_element_located((By.XPATH, xpath))
        )

        html=driver.page_source.encode('utf-8')
        soup = BeautifulSoup(html, "html.parser")
        index = 1
        lunch_line = ''
        for theader in soup.select('body > div:nth-child(2) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(1) > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(1) > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(1) > td') :
            if (theader.get_text() == 'ランチ') :
                lunch_line = index
                break
            index+=1

        # 売上
        index = 0
        lunch_revenue = 0
        dinner_revenue = 0
        for revenue in soup.select('body > div:nth-child(2) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(1) > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(1) > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(2) > td') :
            value = revenue.get_text().replace(',', '')
            if (value == '-') :
                value = 0
            if (index == lunch_line) :
                lunch_revenue = int(value)
            elif (index >= 2 and index != 7) :
                dinner_revenue += int(value)
            index += 1


        # 客数
        if (int(scraping_id) > 22) :
            customers_row_num = '4'
        else :
            customers_row_num = '9'
        index = 1
        lunch_customers_num = 0
        dinner_customers_num = 0
        for customers_num in soup.select('body > div:nth-child(2) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(1) > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(1) > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child('+customers_row_num+') > td') :
            value = customers_num.get_text().replace(',', '')
            if (value == '-') :
                value = 0
            if (index == lunch_line) :
                lunch_customers_num = int(value)
            elif (index >= 2 and index != 7) :
                dinner_customers_num += int(value)
            index += 1




            # 客数
        if (int(scraping_id) > 22) :
            work_time_row_num = '7'
        else :
            work_time_row_num = '13'
        index = 1
        lunch_work_time = 0
        dinner_work_time = 0
        for work_time in soup.select('body > div:nth-child(2) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(1) > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(1) > td:nth-child(1) > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child('+work_time_row_num+') > td') :
            value = work_time.get_text().replace(',', '')
            if (value == '-') :
                value = 0
            if (index == lunch_line) :
                lunch_work_time = float(value)
            elif (index >= 2 and index != 7) :
                dinner_work_time += float(value)
            index += 1


        if (lunch_work_time) :
            lunch_human_time_sales = round(lunch_revenue/lunch_work_time)
        else :
            lunch_human_time_sales = 0

        if (lunch_work_time) :
            dinner_human_time_sales = round(dinner_revenue/dinner_work_time)
        else :
            dinner_human_time_sales = 0



        # まずupdateする
        cursor.execute(
            "UPDATE sales_by_time SET date = %s, shop_id = %s, lunch_revenue=%s, lunch_customers_num=%s, lunch_human_time_sales=%s, dinner_revenue=%s, dinner_customers_num=%s, dinner_human_time_sales=%s WHERE date = %s AND shop_id = %s",
            (target_first_date, shop_id, lunch_revenue, lunch_customers_num, lunch_human_time_sales, dinner_revenue, dinner_customers_num, dinner_human_time_sales, target_first_date, shop_id)
            )
        # insert実行
        cursor.execute(
            "INSERT INTO sales_by_time (date, shop_id, lunch_revenue, lunch_customers_num, lunch_human_time_sales, dinner_revenue, dinner_customers_num, dinner_human_time_sales) SELECT %s, %s, %s, %s, %s, %s, %s, %s WHERE NOT EXISTS (SELECT 1 FROM sales_by_time WHERE date = %s AND shop_id = %s)",
            (target_first_date, shop_id, lunch_revenue, lunch_customers_num, lunch_human_time_sales, dinner_revenue, dinner_customers_num, dinner_human_time_sales, target_first_date, shop_id)
            )

    # デフォでトランザクションが走るためcommitしないと反映されない
    connection.commit()


    # カーソルをとじる
    cursor.close() 
    # コネクション切断
    connection.close()

    driver.quit()

    print(target_first_date)
    print('end')

except Exception as e:
    driver.quit()
    # ほんとはメール投げたい