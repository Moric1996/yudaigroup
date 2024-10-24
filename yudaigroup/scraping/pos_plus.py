# coding: UTF-8



# firefoxのゾンビプロセスを量産しないために
try:


    import datetime

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


    # Timeout 50秒（最大待ち時間） なかなか要素が出てこないときがあるので長めに
    wait = WebDriverWait(driver, 50)







    # ログインページへアクセス
    driver.get('https://ptr-01.postas.asia/time_management/c_login/init?resp_type=pc&company_code=')

    xpath = "//*[@id='company_code']"
    company_code = wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )
    company_code.send_keys('u00131')

    xpath = "//*[@id='user_id']"
    user_id = wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )
    user_id.send_keys('vha9999')

    xpath = "//*[@id='password']"
    password = wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )
    password.send_keys('p12345678')

    xpath = "//*[@id='login']"
    wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    ).click()


    driver.get('https://ptr-01.postas.asia/time_management/c_daily_attendance/init')




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

    target_days = [datetime.date.today() - datetime.timedelta(days=1), datetime.date.today() - datetime.timedelta(days=2),datetime.date.today() - datetime.timedelta(days=3)]
    for set_date in target_days:

        search_date = set_date.strftime('%Y%m%d')
        sql_date = set_date.strftime('%Y-%m-%d')

        xpath = '//*[@id="target_date"]'
        target_date = wait.until(
            EC.visibility_of_element_located((By.XPATH, xpath))
        )
        target_date.clear()
        target_date.send_keys(search_date)

        xpath = "//*[@id='btn-search']"
        wait.until(
            EC.visibility_of_element_located((By.XPATH, xpath))
        ).click()


        # データ確認
        xpath = "/html/body/div[1]/div[5]/form/div[4]/div/div[4]/div[2]/table[1]/tbody/tr[2]/td[9]"
        wait.until(
            EC.visibility_of_element_located((By.XPATH, xpath))
        )




        html = driver.page_source.encode('utf-8')
        soup = BeautifulSoup(html, "html.parser")


        tbody = soup.select('#daily_main > div:nth-child(3) > table:nth-child(1)')
        tr = tbody[0].select("tr")


        work_time_Array = tr[1].select('td')[8].get_text().split(':')
        work_time =  int(work_time_Array[0]) + round((int(work_time_Array[1])/60), 2) if work_time_Array[1] else 0.00



        # まずupdateする
        cursor.execute(
            "UPDATE yudai_data_news SET work_time = %s WHERE date = %s AND shop_id = %s",
            (work_time, sql_date, '00029')
            )
        # insert実行
        cursor.execute(
            "INSERT INTO yudai_data_news (date, shop_id, work_time) SELECT %s, %s, %s WHERE NOT EXISTS (SELECT 1 FROM yudai_data_news WHERE date = %s AND shop_id = %s)",
            (sql_date, '00029', work_time, sql_date, '00029')
            )


    # デフォでトランザクションが走るためcommitしないと反映されない
    connection.commit()


    # カーソルをとじる
    cursor.close()
    # コネクション切断
    connection.close()

    driver.quit()


except Exception as e:
    driver.quit()
    # ほんとはメール投げたい