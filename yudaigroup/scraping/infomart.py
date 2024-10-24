# coding: UTF-8

# firefoxのゾンビプロセスを量産しないために
try:
    import datetime

    from selenium import webdriver

    from selenium.webdriver.common.by import By
    from selenium.webdriver.support.ui import WebDriverWait
    from selenium.webdriver.support import expected_conditions as EC
    from selenium.webdriver.support.ui import Select
    # パース
    from bs4 import BeautifulSoup
    # postgresql
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



    # トップへアクセス
    # driver.get('https://www.infomart.co.jp/') 
    driver.get('https://www.infomart.co.jp/scripts/logon.asp') 
    # xpath = "//a[@class='login']"
    # login_button = wait.until(
    #     EC.visibility_of_element_located((By.XPATH, xpath))
    # )
    # # ログインボタンを押す
    # login_button.click() 

    # # 別タブで開くので切り替える必要がある
    # driver.switch_to.window(driver.window_handles[-1])

    # driver.save_screenshot('search_results.png')

    # ログイン情報入力画面
    # id入力
    xpath = "//input[@class='x16-large h25 cbox']"
    login_id = wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )
    login_id.send_keys('honbu@yudai.co.jp')
    # pass入力
    xpath = "//input[@id='ID_PWD']"
    login_pass = wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )
    login_pass.send_keys('yudai12000')
    # submit
    xpath = "//input[@name='Logon']"
    wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    ).click()

    # ログイン後トップ
    xpath = "//*[@class='mym-tana']"
    wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    ).click()

    # 棚卸し
    xpath = "//*[@id='lnkStore1']"
    wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    ).click()


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


    fifteen_days_ago = (datetime.date.today() - datetime.timedelta(days=15)).replace(day=1)
    target_month = fifteen_days_ago.strftime('%m')
    target_year = fifteen_days_ago.strftime('%Y')

    target_date2 = fifteen_days_ago.strftime('%Y-%m-%d')


    # ここから実処理

    # 棚卸し詳細
    # 年指定
    xpath = "//select[@id='cmbYear']"
    wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )
    select_year = Select(driver.find_element_by_id("cmbYear"))
    select_year.select_by_value(target_year)

    # 月指定
    xpath = "//select[@id='cmbMonth']"
    wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )
    select_month = Select(driver.find_element_by_id("cmbMonth"))
    select_month.select_by_value(target_month)

    # テーブル
    xpath = "//*[@id='gridContainer']"
    wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )


    html=driver.page_source.encode('utf-8')
    soup = BeautifulSoup(html, "html.parser")


    table = soup.find("table", attrs={"id": "gridContainer"})
    tbodys = table.find_all("tbody")[1:]

    for tbody_one in tbodys :

        name = tbody_one.select("#lblGroupName")[0].get_text()

        beginning_inventory = tbody_one.select("#preTanaSum")[0].get_text().replace(',', '')
        end_inventory = tbody_one.select("#tanaSum")[0].get_text().replace(',', '')
        print(name)
        print(beginning_inventory)
        print(end_inventory)
        # まずupdateする
        cursor.execute(
            "UPDATE infomart_inventory SET date = %s, shop_id = %s, beginning_inventory=%s, end_inventory=%s WHERE date = %s AND shop_id = %s", 
            (target_date2, name, beginning_inventory, end_inventory, target_date2, name)
            )
        # insert実行
        cursor.execute(
            "INSERT INTO infomart_inventory (date, shop_id, beginning_inventory, end_inventory) SELECT %s, %s, %s, %s WHERE NOT EXISTS (SELECT 1 FROM infomart_inventory WHERE date = %s AND shop_id = %s)", 
            (target_date2, name, beginning_inventory, end_inventory, target_date2, name)
            )

    # デフォでトランザクションが走るためcommitしないと反映されない
    connection.commit()


    # カーソルをとじる
    cursor.close() 
    # コネクション切断
    connection.close()

    # タブ閉じる
    driver.quit()

except Exception as e:
    driver.quit()
    # ほんとはメール投げたい