# coding: UTF-8

# API呼び出しに必要なパッケージのimport
import requests, math

import datetime
import calendar

import psycopg2
import psycopg2.extras
import os

import sys

# 何日遡るかの引数受取
args = sys.argv
return_num = args[1]

# 対象の月の日付リスト作成
target_date = (datetime.date.today() - datetime.timedelta(days=int(return_num)))
first = target_date.replace(day=1)
end = target_date.replace(day=calendar.monthrange(target_date.year, target_date.month)[1])
days_num = (end - first).days + 1

date_list = []
for i in range(days_num):
    date_list.append((first + datetime.timedelta(days=i)).strftime("%Y-%m-%d"))

# アクセストークンの取得をする
url = "https://api-auth-kintai.jobcan.jp/oauth/token"
payload='grant_type=client_credentials'
headers = {
  'Content-Type': 'application/x-www-form-urlencoded',
  'Authorization': 'Basic OGQ5NTRmOTgtNzc2Yy00MzI2LTk2NjQtYzJhZDZkNjg4ZjhmOk8kQGwyRktncmxMZm49aldSeHVfRC1IQGxyZ2VnNnZjTnpLSENMbUgjI0tQRjJqd3BTYVYjaWtiaks0ZXIjckZCI2xiOUVnMTB1YUstJD90N0hHWUUkZi1SRlpHelNWSnVld08tISQhb09oLWo0ZFdLeTMkUSR4djJ6SWZTSFIk'
}

auth_response = requests.request("POST", url, headers=headers, data=payload)
#
# print(auth_response.text)

auth_json = auth_response.json()

# トークン
# print(auth_json['access_token'])

# グループリスト グループはjobcanに入って調べてください
group_list = [
    3,  # 松福呉服町 113
    4,  # ラジオ三島 202
    5,  # ラジオ沼北 204
    6,  # ラジオ函南 205
    7,  # 串家ららぽーと沼津 404
    8,  # ゆうが沼津 103
    9,  # ゆうが三島 104
    10, # 大衆酒場イマさん 115
    11, # 大衆食堂イマさん 116
    12, # 雄大フェスタ 110
    13, # 甲羅沼津 101
    14, # 甲羅富士 102
    15, # えびす家 111
    16, # 吉祥庵ららぽーと沼津 401
    17, # 蕎麦鷹乃 403
    18, # 赤から三島 108
    19, # 赤から沼津 501
    20, # 赤から富士 502
    21, # 赤から御殿場 503
    22, # 赤から函南 504
    23, # ふたご呉服町 105
    24, # カルビ沼津 106
    25, # カルビ大仁 109
    26, # カルビ御殿場 112
    27, # 吉祥庵ららぽーと愛知東郷 402
    28, # 大韓食堂 107
]

# グループのID
group_shop_format_list = {
     3: '00024',
     4: '00006',
     5: '00005',
     6: '00023',
     7: '00027',
     8: '00004',
     9: '00009',
    10: '00028',
    11: '00031',
    12: '00019',
    13: '00001',
    14: '00002',
    15: '00020',
    16: '00026',
    17: '00403',
    18: '00010',
    19: '00011',
    20: '00015',
    21: '00014',
    22: '00016',
    23: '00025',
    24: '00003',
    25: '00012',
    26: '00021',
    27: '00030',
    28: '00032',
}

headers = {
  'Authorization': auth_json['access_token']
}

for date in date_list:
    print(date)

    group_work_count = {}
    group_dict = {}

    last_employee_id = 0

    while 1:
        adits_url = "https://api-kintai.jobcan.jp/attendance/v1/adits?count=100&date=" + date +'&last_id=' + str(last_employee_id)
        adits_res = requests.request("GET", adits_url, headers=headers)
    #     print(adits_res.text)
        adits_json = adits_res.json()

        if len(adits_json['adits']) == 0:
            break

        for adit in adits_json['adits']:
            last_employee_id = adit['employee_id']
            group_dict[adit['employee_id']] = adit['group_id']


    last_employee_id = 0

    while 1:
        attend_url = "https://api-kintai.jobcan.jp/attendance/v1/summaries/daily?count=100&date=" + date +'&last_id=' + str(last_employee_id)
        attend_response = requests.request("GET", attend_url, headers=headers)

        attend_json = attend_response.json()

        if len(attend_json['daily_summaries']) == 0:
            break

        for attend in attend_json['daily_summaries']:
            last_employee_id = attend['employee_id']
            if attend['employee_id'] in group_dict:
                group_id = group_dict[attend['employee_id']]
            else:
                continue

            temp = group_work_count.get(group_id, 0)
            group_work_count[group_id] = temp + attend['work']


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


    for group_id, sum in group_work_count.items():

        hours = math.floor(sum/60)
        minutes = round(sum%60/60*100)

        if group_id in group_shop_format_list:
            scraping_id = group_shop_format_list[group_id]
        else:
            continue

        worktime = float(str(hours) + '.' + str(minutes))

        # まずupdateする
        cursor.execute(
            "UPDATE yudai_data_news SET work_time = %s WHERE date = %s AND shop_id = %s",
            (worktime, date, scraping_id)
            )
        # insert実行
        cursor.execute(
            "INSERT INTO yudai_data_news (date, shop_id, work_time) SELECT %s, %s, %s WHERE NOT EXISTS (SELECT 1 FROM yudai_data_news WHERE date = %s AND shop_id = %s)",
            (date, scraping_id, worktime, date, scraping_id)
            )

    # デフォでトランザクションが走るためcommitしないと反映されない
    connection.commit()


    # カーソルをとじる
    cursor.close()
    # コネクション切断
    connection.close()
