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


    fifteen_days_ago = (datetime.date.today() - datetime.timedelta(days=15)).replace(day=1)
    target_month = fifteen_days_ago.strftime('%m')
    target_year = fifteen_days_ago.strftime('%Y')

    target_date = fifteen_days_ago.strftime('%Y-%m-%d')






    # ログインページへアクセス
    driver.get('https://newadmin.fancrew.jp/managers/login/')
    xpath = "//input[@id='login']"
    login_id = wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )
    login_id.send_keys('yudaico')
    xpath = "//input[@id='password']"
    login_pass = wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )
    login_pass.send_keys('v2LbExfr')

    xpath = "//*[@id='login-box']/div/form/div/div[2]/input[1]"
    wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    ).click()

    # 年月指定
    xpath = "//select[@id='select']"
    wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )
    select_month = Select(driver.find_element_by_id("select"))
    select_month.select_by_value(target_date)

    xpath = "/html/body/table/tbody/tr[2]/td[2]/table/tbody/tr/td[2]/table/tbody/tr[4]/td/input"
    wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    ).click()

    # テーブル
    xpath = "//table[@id='qscAllShopSummaryTable']"
    wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )

    html=driver.page_source.encode('utf-8')
    soup = BeautifulSoup(html, "html.parser")


    tbody = soup.select('#qscAllShopSummaryTable > tbody:nth-child(2)')
    tr = tbody[0].select("tr")




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



    for line in tr:
        td = line.select('td')
        name = td[2].get_text()

        all_score = td[5].get_text()#総合点
        revisit = td[7].get_text()#再来店
        reception = td[9].get_text()#接客
        offer = td[11].get_text()#提供
        cuisine = td[13].get_text()#料理
        cleanliness = td[15].get_text()#清潔感
        ambience = td[17].get_text()#空間雰囲気
        cost_performance = td[19].get_text()#コストP

        # まずupdateする
        cursor.execute(
            "UPDATE store_survey SET date = %s, shop_id = %s, all_score=%s, revisit=%s, reception=%s, offer=%s, cuisine=%s, cleanliness=%s, ambience=%s, cost_performance=%s WHERE date = %s AND shop_id = %s",
            (target_date, name, all_score, revisit, reception, offer, cuisine, cleanliness, ambience, cost_performance, target_date, name)
            )
        # insert実行
        cursor.execute(
            "INSERT INTO store_survey (date, shop_id, all_score, revisit, reception, offer, cuisine, cleanliness, ambience, cost_performance) SELECT %s, %s, %s, %s, %s, %s, %s, %s, %s, %s WHERE NOT EXISTS (SELECT 1 FROM store_survey WHERE date = %s AND shop_id = %s)",
            (target_date, name, all_score, revisit, reception, offer, cuisine, cleanliness, ambience, cost_performance, target_date, name)
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