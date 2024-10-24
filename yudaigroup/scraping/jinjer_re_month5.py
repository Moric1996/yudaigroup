# coding: UTF-8

# firefoxのゾンビプロセスを量産しないために
try:

    import random
    
    

    import datetime
    import time

#     time.sleep(random.randint(0, 600))

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
    #driver = webdriver.Firefox(executable_path='./geckodriver', options=options)


    # Timeout 50秒（最大待ち時間） なかなか要素が出てこないときがあるので長めに
    wait = WebDriverWait(driver, 50)


    # 対象日は昨日
    yesterday = (datetime.date.today() - datetime.timedelta(days=30))
    target_y = int(yesterday.strftime('%Y'))
    target_m = int(yesterday.strftime('%m'))
    # フォーム操作用
    target_month = yesterday.strftime('%Y年%m月')


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
    cursor2 = connection.cursor()


    cursor.execute('SELECT scraping_id, jinjer_id FROM shop_list WHERE jinjer_id IS NOT NULL AND is_scraping = true')
    shop_id_list = cursor.fetchall()
    cursor.close()


    # ログインページへアクセス
    driver.get('https://jinji.jinjer.biz/auth/login')
    xpath = "//input[@name='company_code']"
    login_id = wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )
    login_id.send_keys('6945')

    xpath = "//input[@name='code_or_email']"
    login_id2 = wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )
    login_id2.send_keys('950')

    xpath = "//input[@name='password']"
    login_pass = wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )
    login_pass.send_keys('0021339550')

    xpath = "//button[@class='button login-button']"
    wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    ).click()
#     xpath = "//a[@href='https://kintai.jinjer.biz/manager']"
#     wait.until(
#         EC.visibility_of_element_located((By.XPATH, xpath))
#     ).click()
#
#     wait.until(lambda d: len(driver.window_handles) > 1)
#     # ウィンドウハンドルを取得する
#     handle_array = driver.window_handles
#
#     # seleniumで操作可能なdriverを切り替える
#     driver.switch_to.window(handle_array[1])


    # これどうなの
    time.sleep(10)

    driver.get("https://kintai.jinjer.biz/manager/top")

    time.sleep(5)

    driver.get("https://kintai.jinjer.biz/manager/budgets/month")



    xpath = "//input[@id='year_month_start']"
    input_ym = wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )#.click()

    input_ym.clear()
    input_ym.send_keys(target_month)





    for shop_id in shop_id_list:


        print(shop_id)

        xpath = "/html/body/div[1]/div/div[3]/div/div/div[1]/div[1]/div[1]/div[2]/div/span/span[1]/span"
        wait.until(
            EC.visibility_of_element_located((By.XPATH, xpath))
        ).click()

        xpath = "//*[contains(@id, '"+shop_id['jinjer_id']+"')]"
        wait.until(
            EC.visibility_of_element_located((By.XPATH, xpath))
        ).click()









        # 確定
        xpath = "/html/body/div[1]/div/div[3]/div/div/div[1]/button"
        wait.until(
            EC.visibility_of_element_located((By.XPATH, xpath))
        ).click()


        # table
        xpath = "//div[@id='js-scroll01']"
        wait.until(
            EC.visibility_of_element_located((By.XPATH, xpath))
        )






        html=driver.page_source.encode('utf-8')
        soup = BeautifulSoup(html, "html.parser")


        tbody = soup.select('.datalist tbody')
        tr = tbody[0].select("tr")


        # 1行づつ入れていくしかないか？
        for index, line in enumerate(tr):
            if ((index+1)%2 == 0):
                worktimeArray = line.find_all('td')[1].get_text().split(':')
                worktime =  int(worktimeArray[0]) + round((int(worktimeArray[1])/60), 2) if worktimeArray[1] else 0.00

                insert_datetime = datetime.datetime(target_y, target_m, int((index+1)/2))
                date = insert_datetime.strftime('%Y%m%d')
                print(date)
                print(worktime)

                # まずupdateする
                cursor2.execute(
                    "UPDATE yudai_data_news SET work_time = %s WHERE date = %s AND shop_id = %s",
                    (worktime, date, shop_id['scraping_id'])
                    )
                # insert実行
                cursor2.execute(
                    "INSERT INTO yudai_data_news (date, shop_id, work_time) SELECT %s, %s, %s WHERE NOT EXISTS (SELECT 1 FROM yudai_data_news WHERE date = %s AND shop_id = %s)",
                    (date, shop_id['scraping_id'], worktime, date, shop_id['scraping_id'])
                    )
            else :
                print(line.find_all('td')[0].get_text())





    # デフォでトランザクションが走るためcommitしないと反映されない
    connection.commit()


    # カーソルをとじる
    cursor2.close()
    # コネクション切断
    connection.close()

    driver.quit()

except Exception as e:
    driver.quit()
    # ほんとはメール投げたい