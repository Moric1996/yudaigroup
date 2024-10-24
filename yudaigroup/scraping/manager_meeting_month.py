# coding: UTF-8
import datetime
import calendar

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


# ログイン画面にアクセスしてクッキーをもらう
session.post("https://www.bino-fs.com/")
# チェック画面にログイン情報を投げる ここでログインされた場合はcookieに残る
session.post("https://www.bino-fs.com/Logon/LogonCheck.asp", data=login_data)


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


# 店舗の取得
cursor2 = connection.cursor()
cursor2.execute('SELECT scraping_id FROM shop_list WHERE scraping_id IS NOT NULL')
shop_id_list_sql = cursor2.fetchall()
cursor2.close()

# idをフォーマット
shop_id_list = []
for rows in shop_id_list_sql:
    for row in rows:
        shop_id_list.append(row)

    # 取得する日付を決める 現在は昨日
    #tomonth = datetime.datetime(2019, 6, 4, 0, 0, 0)

date_list = [
    datetime.datetime(2018, 1, 4, 0, 0, 0), 
    datetime.datetime(2018, 2, 4, 0, 0, 0), 
    datetime.datetime(2018, 3, 4, 0, 0, 0), 
    datetime.datetime(2018, 4, 4, 0, 0, 0), 
    datetime.datetime(2018, 5, 4, 0, 0, 0), 
    datetime.datetime(2018, 6, 4, 0, 0, 0),
    datetime.datetime(2018, 7, 4, 0, 0, 0),
    datetime.datetime(2018, 8, 4, 0, 0, 0),
    datetime.datetime(2018, 9, 4, 0, 0, 0),
    datetime.datetime(2018, 10, 4, 0, 0, 0),
    datetime.datetime(2018, 11, 4, 0, 0, 0),
    datetime.datetime(2018, 12, 4, 0, 0, 0),
    datetime.datetime(2019, 1, 4, 0, 0, 0), 
    datetime.datetime(2019, 2, 4, 0, 0, 0), 
    datetime.datetime(2019, 3, 4, 0, 0, 0), 
    datetime.datetime(2019, 4, 4, 0, 0, 0), 
    datetime.datetime(2019, 5, 4, 0, 0, 0), 
    datetime.datetime(2019, 6, 4, 0, 0, 0),
    datetime.datetime(2019, 7, 4, 0, 0, 0),
    datetime.datetime(2019, 8, 4, 0, 0, 0),
    datetime.datetime(2019, 9, 4, 0, 0, 0),
    datetime.datetime(2019, 10, 4, 0, 0, 0),
    datetime.datetime(2019, 11, 4, 0, 0, 0),
    datetime.datetime(2019, 12, 4, 0, 0, 0),
]

for tomonth in date_list:



    st_target_date = tomonth.replace(day=1).strftime("%Y/%m/%d")
    target_date = tomonth.replace(day=1).strftime("%Y/%m/%d")
    ed_target_date = tomonth.replace(day=calendar.monthrange(tomonth.year, tomonth.month)[1]).strftime("%Y/%m/%d")






    # 店舗ごと処理を行う
    for shop_id in shop_id_list:

        # データを取得する
        res = session.post("https://www.bino-fs.com/list/MakeReport.asp?kubun=1&rptno=57&ShopCode="+shop_id+"&chkTanpin=&ShopNum=&InAll=&DateFrom="+st_target_date+"&DateTo="+ed_target_date+"&myGCCmode=0&curPage=0&FreeReportID=3&FreeRptKubun=0")

        # 文字化け対策
        res.encoding = res.apparent_encoding

        # parseする
        soup = BeautifulSoup(res.text, "html.parser")

        # テーブルを取得
        table = soup.findAll("table")[3]

        #
        shop_list = []
        for cell in table.select("tr:nth-last-child(2) td"):
            text = cell.get_text()
            if text == '-' :
                text = '0'

            shop_list.append(text.replace(',', ''))







        # まずupdateする
        cursor.execute("UPDATE manager_meeting SET date = %s, shop_id = %s, labor_cost=%s, purchase_cost=%s WHERE date = %s AND shop_id = %s", (target_date, shop_id, shop_list[12], shop_list[14], target_date, shop_id))
        # insert実行
        cursor.execute("INSERT INTO manager_meeting (date, shop_id, labor_cost, purchase_cost) SELECT %s, %s, %s, %s WHERE NOT EXISTS (SELECT 1 FROM manager_meeting WHERE date = %s AND shop_id = %s)", (target_date, shop_id, shop_list[12], shop_list[14], target_date, shop_id))




# デフォでトランザクションが走るためcommitしないと反映されない
connection.commit()


# カーソルをとじる
cursor.close() 
# コネクション切断
connection.close()
