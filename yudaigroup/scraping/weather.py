# coding: UTF-8

# firefoxのゾンビプロセスを量産しないために
try:

    import random

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
    # driver = webdriver.Firefox(executable_path='./geckodriver', options=options)


    # Timeout 50秒（最大待ち時間） なかなか要素が出てこないときがあるので長めに
    wait = WebDriverWait(driver, 50)


    # 対象日は昨日
    yesterday = (datetime.date.today() - datetime.timedelta(days=1))
    target_y = yesterday.strftime('%Y')
    target_m = yesterday.strftime('%m')
    target_d = yesterday.strftime('%d')

    yesterday_date = yesterday.strftime('%Y%m%d')

    print(yesterday_date)


    # 店舗一覧
    #postgreSQLに接続
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


    # ログインページへアクセス
    driver.get('https://tenki.jp/past/'+target_y+'/'+target_m+'/'+target_d+'/weather/5/25/47657/')


    # table
    xpath = "//table[@class='past-live-point-table']"
    wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )


    html = driver.page_source.encode('utf-8')
    soup = BeautifulSoup(html, "html.parser")


    tbody = soup.select('.past-live-point-table tbody')
    weather_tr = tbody[0].select("tr")[3]


    # 1行づつ入れていくしかないか？
    for index, cell in enumerate(weather_tr):
        if (index == 5 or index == 7):
            if (index == 5):
                timezone = 1
            elif (index == 7):
                timezone = 2

            weather = cell.get_text()

            print(weather)

            # まずupdateする
            cursor.execute(
                "UPDATE date_weather SET weather = %s WHERE date = %s AND timezone_status = %s",
                (weather, yesterday_date, timezone)
                )
            # insert実行
            cursor.execute(
                "INSERT INTO date_weather (weather, date, timezone_status) SELECT %s, %s, %s WHERE NOT EXISTS (SELECT 1 FROM date_weather WHERE date = %s AND timezone_status = %s)",
                (weather, yesterday_date, timezone, yesterday_date, timezone)
                )
        else :
            print(index)






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