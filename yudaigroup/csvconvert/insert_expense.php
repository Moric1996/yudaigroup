<?php
// 初期のインポート用のやつ
$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');
$array = array(
/*
    array(4111,"売上高",1,4111),
    array(4112,"賃貸料収入",1,4112),
    array(4113,"ＬＬＰ収益",1,4113),
    array(4114,"通信売上高",1,4114),
    array(4115,"売上値引戻り高    (△)",1,4115),
    array(4116,"児発放デイ売上高",1,4116),

    array(5111,"期首たな卸高",2,5111),
    array(5211,"商品仕入高",2,5211),
    array(5212,"通信商品仕入高",2,5212),
    array(5215,"ｺﾐｯｼｮﾝ清掃代",2,5215),
    array(5216,"ＬＬＰ原価",2,5216),
    array(5213,"仕入値引戻し高    (△)",2,5213),
    array(5273,"他勘定振替高      (△)",2,5273),
    array(5311,"期末たな卸高      (△)",2,5311),

    array(6111,"給与手当",3,6111),
    array(6311,"雑給",3,6311),
    array(6112,"旅費交通費",3,6112),
    array(6113,"広告宣伝費",3,6113),
    array(6114,"販売促進費",3,6114),
    array(6115,"荷造発送費",3,6115),
    array(6116,"外注費",3,6116),
    array(6117,"支払手数料",3,6117),
    array(6118,"",3,6118),
    array(6210,"処遇改善手当",3,6210),
    array(6211,"役員報酬",3,6211),
    array(6232,"",3,6232),
    array(6212,"事務員給与",3,6212),
    array(6213,"従業員賞与",3,6213),
    array(6312,"法定福利費",3,6312),
    array(6226,"厚生費",3,6226),
    array(6119,"退職金",3,6119),
    array(6214,"減価償却費",3,6214),
    array(6234,"",3,6234),
    array(6215,"地代家賃",3,6215),
    array(6216,"修繕／車両関係費",3,6216),
    array(6217,"事務用消耗品費",3,6217),
    array(6218,"通信交通費",3,6218),
    array(6219,"水道光熱費",3,6219),
    array(6221,"租税公課",3,6221),
    array(6222,"寄付金",3,6222),
    array(6223,"接待交際費",3,6223),
    array(6224,"保険料",3,6224),
    array(6225,"備品消耗品費",3,6225),
    array(6227,"管理諸費",3,6227),
    array(6228,"リース／レンタル料",3,6228),
    array(6229,"諸会費",3,6229),
    array(6313,"",3,6313),
    array(6233,"",3,6233),
    array(6314,"貸倒償却",3,6314),
    array(6231,"雑費",3,6231),

    array(7111,"受取利息",4,7111),
    array(7112,"為替差益",4,7112),
    array(7113,"ＬＬＰ収入",4,7113),
    array(7114,"受取配当金",4,7114),
    array(7118,"雑収入",4,7118),

    array(7511,"支払利息",5,7511),
    array(7518,"手形売却損",5,7518),
    array(7512,"為替差損",5,7512),
    array(7513,"現金過不足",5,7513),
    array(7514,"ＬＬＰ損失",5,7514),
    array(7515,"繰延資産償却",5,7515),
    array(7519,"雑損失",5,7519)
*/
    array(500,"売上高",1,4111),
    array(501,"売上高II",1,null),
    array(502,"売上高(保育)",1,null),
    array(503,"売上高(飲食)",1,null),
    array(510,"家賃収入",1,4112),

    array(620,"期首材料棚卸高",2,5111),
    array(623,"原材料仕入",2,5211),
    array(635,"期末材料棚卸高",2,5311),

    array(740,"役員報酬",3,6211),
    array(741,"給料",3,6111),
    array(742,"賞与",3,6213),
    array(744,"法定福利費",3,6312),
    array(745,"外注給与",3,null),
    array(750,"福利厚生費",3,6226),
    array(751,"会議費",3,null),
    array(752,"工具器具備品",3,null),
    array(753,"消耗品費",3,null),
    array(754,"事務用品費",3,6217),
    array(756,"通勤費",3,null),
    array(757,"旅費交通費",3,6112),
    array(758,"採用費",3,null),// todo
    array(761,"通信費",3,6218),
    array(762,"水道光熱費",3,6219),
    array(765,"教育研修費",3,null),
    array(766,"保険料",3,6224),
    array(768,"減価償却費",3,6214),
    array(769,"家賃",3,6215),
    array(770,"賃借料",3,null),
    array(771,"修繕費",3,6216),
    array(772,"清掃整備費",3,null),
    array(773,"租税公課",3,6221),
    array(774,"外注費",3,6116),
    array(775,"広告宣伝費",3, 6113),//todo
    array(776,"接待交際費",3,6223),
    array(777,"諸会費",3,6229),
    array(778,"支払手数料",3,6117),
    array(779,"雑費",3,6231),

    array(800,"受取利息",4,7111),
    array(801,"受取配当金",4,7114),
    array(802,"受取保険金",4,null),
    array(810,"投資有価証券売却益",4,null),
    array(820,"雑収入",4,7118),

    array(830,"支払利息",5,7511),
    array(840,"雑損失",5,7519),
);

foreach ($array as $val) {
    $sql =<<<SQL
INSERT INTO expense_list (code, name, type, yudai_code) VALUES ($1, $2, $3, $4);
SQL;
    pg_query_params($conn, $sql, array($val[0], $val[1], $val[2], $val[3]));
}