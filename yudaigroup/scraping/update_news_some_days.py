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
login_info = {
    "UserID" : "yudaigroup",
    "password" : "61dsku"
}

# ログイン画面にアクセス
session.post("https://www.bino-fs.com/")
# チェック画面にログイン情報を投げる
session.post("https://www.bino-fs.com/Logon/LogonCheck.asp", data=login_info)


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
#カーソルの取得
cursor = connection.cursor() 



# 店舗の取得
# cursor2 = connection.cursor()
# cursor2.execute('SELECT scraping_id FROM shop_list WHERE scraping_id IS NOT NULL')
# shop_id_list_sql = cursor2.fetchall()
# cursor2.close()

# idをフォーマット
shop_id_list = ['00034']
# for rows in shop_id_list_sql:
#     for row in rows:
#         shop_id_list.append(row)



today_time = datetime.date.today()
three_days_ago = (today_time - datetime.timedelta(days=21)).strftime('%Y%m%d')
yesterday = (today_time - datetime.timedelta(days=1)).strftime('%Y%m%d')

start_dt = datetime.datetime.strptime(three_days_ago, "%Y%m%d")
end_dt = datetime.datetime.strptime(yesterday, "%Y%m%d")

lst = []
t = start_dt
while t <= end_dt:
    lst.append(t)
    t += datetime.timedelta(days=1)



for date in lst:
    print(date)
    target_date = date.strftime('%Y-%m-%d')
    post_target_date = date.strftime("%Y/%m/%d")
    # 店舗ごと処理を行う
    for shop_id in shop_id_list:

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
        cursor.execute("UPDATE yudai_data_news SET date = %s, shop_id = %s, revenue=%s, budget=%s, customers_num=%s, discount_ticket=%s WHERE date = %s AND shop_id = %s", (target_date, shop_id, shop_list[2], shop_list[4], shop_list[9], shop_list[16], target_date, shop_id))
        # insert実行
        cursor.execute("INSERT INTO yudai_data_news (date, shop_id, revenue, budget, customers_num, discount_ticket) SELECT %s, %s, %s, %s, %s, %s WHERE NOT EXISTS (SELECT 1 FROM yudai_data_news WHERE date = %s AND shop_id = %s)", (target_date, shop_id, shop_list[2], shop_list[4], shop_list[9], shop_list[16], target_date, shop_id))

        # commitしないと反映されないそうです
        connection.commit()






# カーソルをとじる
cursor.close() 
# 切断
connection.close() 
