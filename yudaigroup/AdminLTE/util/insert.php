<?php


$shop_name_list = array(
    '00001' => '沼津甲羅',#沼津甲羅本店八宏園
    '00002' => '沼津甲羅',#富士甲羅本店八宏園
    '00003' => 'カルビ沼津',#カルビ一丁沼津店
    '00004' => 'ゆうが沼津',#創作料理ゆうが沼津
    '00005' => 'ラジオ沼津',#ラジオシティー　沼津駅北店
    '00006' => 'ラジオ三島',#ラジオシティー三島駅前店
    '00007' => 'ゆうが富士',#創作料理ゆうが富士
    '00008' => 'わが家',#わが家の台所
    '00009' => 'ゆうが三島',#創作料理ゆうが三島
    '00010' => '赤から三島',#赤から三島店
    '00011' => '赤から沼津',#赤から沼津店
    '00012' => 'カルビ大仁',#カルビ一丁大仁店
    '00013' => '熱函ゴルフ',#熱函ゴルフセンター
    '00014' => '赤から御殿場',#赤から　御殿場店
    '00015' => '赤から富士',#赤から富士店
    '00016' => '赤から函南',#赤から函南店
    '00017' => '御殿場甲羅',#御殿場甲羅本店　八宏園
    '00018' => 'ラジオ御殿場',#ラジオシティー御殿場店
    '00019' => 'フェスタ',#雄大フェスタ
    '00020' => 'えびす家',#えびす家富士店
    '00021' => 'カルビ御殿場',#カルビ一丁御殿場店
    '00022' => 'ゴルフ清水町',#雄大ゴルフセンター清水町
    '00023' => 'ラジオ函南',#ラジオシティー函南店
    '00024' => '松福',#松福呉服町通り店
    '00025' => 'ふたご',#ふたご呉服町
    //'00026',#吉祥庵ららぽーと沼津
    //'00027',#串家物語ららぽーと沼津
);

$CONN_DATA = 'host=localhost user=yournet dbname=yudai_admin port=5432';

foreach ($shop_name_list as $key => $val) {
    $sql =<<<SQL
UPDATE shop_list 
SET abbreviation = '{$val}'
WHERE id = '{$key}'
SQL;
    $res = pg_query(pg_connect($CONN_DATA), $sql);
}

