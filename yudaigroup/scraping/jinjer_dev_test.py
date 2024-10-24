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
#     driver = webdriver.Firefox(executable_path='./geckodriver', options=options)


    # Timeout 50秒（最大待ち時間） なかなか要素が出てこないときがあるので長めに
    wait = WebDriverWait(driver, 50)


    # 対象日は昨日
    yesterday = (datetime.date.today() - datetime.timedelta(days=20))
    target_y = int(yesterday.strftime('%Y'))
    target_m = int(yesterday.strftime('%m'))
    # フォーム操作用
    target_month = yesterday.strftime('%Y年%m月')


    print('開始')
    shop_id_list = [
    '5d6f094c6135340d1221d721',
    '5d6f094c6135340d123dc851',
    '5d56381964333475d2bf75f1',
    '5d6f094c6135340d121b41e5',
    '5d6f094c6135340d12845ca7',
    '5d6f094c6135340d123bf6e9',
    '5d6f094c6135340d12264241',
    '5d6f094c6135340d122c4d6b',
    '5d6f094c6135340d1230f10d',
    '5d6f094c6135340d121f8e83',
    '5d6f094c6135340d121c99bf',
    '5d6f094c6135340d124f5807',
    '5d6f094c6135340d126715cf',
    '5d6f094c6135340d121b999b',
    '5d6f094c6135340d12af2adf',
    '5d6f094c6135340d123ddb3b',
    '5d6f094c6135340d123ee5e1',
    '5d6f094c6135340d1257eae7',
    '5d6f094c6135340d1251a1c3',
    '5d6f094c6135340d1298e713',
    '5dbf8baf6534363d25055a8b',
    '5d6f094c6135340d12e97d3d',
    'bb96f71c653436db5e45a96b',
    '854970b0653436c8b70e2bf7',
    'f7142e046534367426572dd7',
    '5d6f094c6135340d1201838d',
    '5d6f094c6135340d12e97d3d',
    ]

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
    time.sleep(5)

    driver.get("https://kintai.jinjer.biz/manager/budgets")

    time.sleep(5)

    driver.get("https://kintai.jinjer.biz/manager/budgets/month")

    print('ページに移動')

    xpath = "//input[@id='year_month_start']"
    input_ym = wait.until(
        EC.visibility_of_element_located((By.XPATH, xpath))
    )#.click()

    input_ym.clear()
    input_ym.send_keys(target_month)


    print('ループ前')


    for shop_id in shop_id_list:


        print(shop_id)

#         xpath = '/html/body/div[1]/div[1]/div[3]/div/div/div/div/div[2]/div/div[2]/div[2]/div/span/span[1]/span'
#         wait.until(
#             EC.visibility_of_element_located((By.XPATH, xpath))
#         ).click()

        print(driver.current_url)

#         xpath = "//li[contains(@id, '"+shop_id+"')]"
#         wait.until(
#             EC.visibility_of_element_located((By.XPATH, xpath))
#         ).click()


        html=driver.page_source.encode('utf-8')
        soup = BeautifulSoup(html, "html.parser")
        print(soup)


        # 確定
#         xpath = "//*[@id='main_search_btn']"
#         wait.until(
#             EC.visibility_of_element_located((By.XPATH, xpath))
#         ).click()
#
#
#         table
#         xpath = "//div[@id='js-scroll01']"
#         wait.until(
#             EC.visibility_of_element_located((By.XPATH, xpath))
#         )






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
                date = insert_datetime.strftime('%Y-%m-%d')

                print(date)
                print(worktime)
                print(shop_id)

            else :
                print(line.find_all('td')[0].get_text())




    driver.quit()

except Exception as e:
    print('error')
    print(e)
#     driver.quit()
    # ほんとはメール投げたい