# coding: UTF-8
import datetime

import requests
from bs4 import BeautifulSoup

import psycopg2 
import psycopg2.extras
import os



# セッション
session = requests.session()

# ログインデータ
import login_info
login_data = login_info.login_info()


# ログイン画面にアクセス
session.post("https://www.bino-fs.com/")
# チェック画面にログイン情報を投げる
session.post("https://www.bino-fs.com/Logon/LogonCheck.asp", data=login_data)


#postgreSQLに接続（接続情報は環境変数、PG_XXX）
connection = psycopg2.connect(
    host='localhost',
    user='yournet',
    database='yudai_admin',
    port=5432) 

#クライアントプログラムのエンコードを設定（DBの文字コードから自動変換してくれる）
connection.set_client_encoding('utf-8') 
#select結果を辞書形式で取得するように設定 
connection.cursor_factory=psycopg2.extras.DictCursor

#店舗の取得
cursor2 = connection.cursor()
cursor2.execute('SELECT scraping_id FROM shop_list WHERE scraping_id IS NOT NULL')
shop_name_list_sql = cursor2.fetchall()
cursor2.close()



shop_name_list = []
for rows in shop_name_list_sql:
    for row in rows:
        shop_name_list.append(row)


today = datetime.date.today()
yestarday = today - datetime.timedelta(days=1)
target_date = yestarday.strftime('%Y-%m-%d')
post_target_date = yestarday.strftime("%Y/%m/%d")










#カーソルの取得
cursor = connection.cursor()

# 店舗ごと処理を行う
for shop_id in shop_name_list:

    # データを取得する
    res = session.post("https://www.bino-fs.com/list/MakeReport.asp?kubun=1&rptno=57&ShopCode="+shop_id+"&chkTanpin=&ShopNum=&InAll=&DateFrom="+post_target_date+"&DateTo="+post_target_date+"&myGCCmode=0&curPage=0&FreeReportID=47&FreeRptKubun=0")

    # 文字化け対策
    res.encoding = res.apparent_encoding


    soup = BeautifulSoup(res.text, "html.parser")

    # テーブルを取得
    table = soup.findAll("table")[1]
    # 行に分解
    row = table.findAll("tr")[3]
    shop_list = []


    for cell in row.findAll('td'):
        text = cell.get_text()
        if text == '-' :
            text = '0'
    
        shop_list.append(text.replace(',', ''))


    # まずupdateする
    cursor.execute("UPDATE yudai_data_news SET date = %s, shop_id = %s, revenue=%s, budget=%s, customers_num=%s, work_time=%s, discount_ticket=%s WHERE date = %s AND shop_id = %s", (target_date, shop_id, shop_list[2], shop_list[4], shop_list[9], shop_list[13], shop_list[16], target_date, shop_id))
    # insert実行
    cursor.execute("INSERT INTO yudai_data_news (date, shop_id, revenue, budget, customers_num, work_time, discount_ticket) SELECT %s, %s, %s, %s, %s, %s, %s WHERE NOT EXISTS (SELECT 1 FROM yudai_data_news WHERE date = %s AND shop_id = %s)", (target_date, shop_id, shop_list[2], shop_list[4], shop_list[9], shop_list[13], shop_list[16], target_date, shop_id))



#strings = [曜日 売上 売上（累計） 	売上予算（累計） 	売上達成率（累計） 	昨年同日売上実績 	昨年同日売上実績（累計） 	昨年同日対比（累計） 	客数（累計） 	昨年同日客数（累計） 	客単価 	昨年同日客単価 	労働時間（計） 	労働時間（計・累計） 	人時売上高 	販促券金額（合計） 	販促券金額累計]

    

# commitしないと反映されないそうです
connection.commit()


# カーソルをとじる
cursor.close() 
# 切断
connection.close()