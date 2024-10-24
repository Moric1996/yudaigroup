<?php

/////////////////////////// 定数設定  ///////////////
//第ⅹ回モラールサーベイアンケート
$MS_NUMBER = 1;//1回
//主項目数
$MAIN_ITEM_CNT = 5;//A～Eまで
///////////////////////////////////////////配列設定////////////////////////////


$large_alfa[1] = "A";
$large_alfa[2] = "B";
$large_alfa[3] = "C";
$large_alfa[4] = "D";
$large_alfa[5] = "E";
$large_alfa[6] = "F";
$large_alfa[7] = "G";
$large_alfa[8] = "H";
$large_alfa[9] = "I";

$small_alfa[1] = "a";
$small_alfa[2] = "b";
$small_alfa[3] = "c";
$small_alfa[4] = "d";
$small_alfa[5] = "e";
$small_alfa[6] = "f";
$small_alfa[7] = "g";
$small_alfa[8] = "h";
$small_alfa[9] = "iI";

//////////所属部署一覧
$belong_arr[1] = "雄大本部";
$belong_arr[2] = "雄大営業部";
$belong_arr[3] = "甲羅沼津";
$belong_arr[4] = "甲羅富士";
$belong_arr[5] = "御甲羅殿場";
$belong_arr[6] = "ゆうが沼津";
$belong_arr[7] = "ゆうが三島";
$belong_arr[8] = "カルビ沼津";
$belong_arr[9] = "カルビ大仁";
$belong_arr[10] = "カルビ御殿場";
$belong_arr[11] = "わが家の台所";
$belong_arr[12] = "赤から三島";
$belong_arr[13] = "赤から沼津";
$belong_arr[14] = "赤から御殿場";
$belong_arr[15] = "赤から富士";
$belong_arr[16] = "赤から函南";
$belong_arr[17] = "フェスタ";
$belong_arr[18] = "えびす家富士";
$belong_arr[19] = "松福静岡";
$belong_arr[20] = "ラジオシティー沼津";
$belong_arr[21] = "ラジオシティー三島";
$belong_arr[22] = "ラジオシティー御殿場";
$belong_arr[23] = "ラジオシティー函南";
$belong_arr[24] = "雄大ゴルフセンター熱函";
$belong_arr[25] = "雄大ゴルフセンター清水町";
$belong_arr[26] = "通信事業部通信本店";
$belong_arr[27] = "通信事業部ドコモ";
$belong_arr[28] = "通信事業部au原";
$belong_arr[29] = "通信事業部SB大仁・下田";
$belong_arr[30] = "グランジャー";
$belong_arr[31] = "ユアネット";



///////主項目
$item_arr[1] = "所属部署の雰囲気について";
$item_arr[2] = "現在担当している仕事の満足度及び適正";
$item_arr[3] = "処遇";
$item_arr[4] = "会社全体の評価";
$item_arr[5] = "上司との関係性";

///////副項目
$sub_item_arr[1][1] = "協力度について";
$sub_item_arr[1][2] = "楽しさについて";
$sub_item_arr[1][3] = "仕事に対する意欲について";
$sub_item_arr[1][4] = "部署全体の規律について";
$sub_item_arr[1][5] = "部署全体のコミュニケーションについて";
$sub_item_arr[1][6] = "相対評価";

$sub_item_arr[2][1] = "仕事内容を質的に見て";
$sub_item_arr[2][2] = "仕事量について";
$sub_item_arr[2][3] = "性格から判断して";
$sub_item_arr[2][4] = "自己の能力について";
$sub_item_arr[2][5] = "担当している仕事への興味について";
$sub_item_arr[2][6] = "担当している仕事へのやりがいについて";
$sub_item_arr[2][7] = "自己能力開発の余地について";
$sub_item_arr[2][8] = "担当している仕事の満足度について";

$sub_item_arr[3][1] = "自己の能力に対しての現在の給与について";
$sub_item_arr[3][2] = "昇進について";
$sub_item_arr[3][3] = "残業について";
$sub_item_arr[3][4] = "処遇面相対満足度について";

$sub_item_arr[4][1] = "会社の将来性について";
$sub_item_arr[4][2] = "現在の会社評判について";

$sub_item_arr[5][1] = "意見の取り上げ方について";
$sub_item_arr[5][2] = "自分への理解度";
$sub_item_arr[5][3] = "仕事の指導監督力";
$sub_item_arr[5][4] = "上司の自分に対する評価";
$sub_item_arr[5][5] = "上司とのコミュニケーション";

///////選択項目

$option_arr[101][1] = "全員協力的でない";
$option_arr[101][2] = "大部分の人が協力的でない";
$option_arr[101][3] = "普通";
$option_arr[101][4] = "大部分の人が協力的である";
$option_arr[101][5] = "全員協力的である";

$option_arr[102][1] = "全く楽しくない";
$option_arr[102][2] = "あまり楽しくない";
$option_arr[102][3] = "普通";
$option_arr[102][4] = "比較的楽しい";
$option_arr[102][5] = "非常に楽しい";

$option_arr[103][1] = "全くやる気がない";
$option_arr[103][2] = "少しやる気にかけている";
$option_arr[103][3] = "普通";
$option_arr[103][4] = "相当やる気がある";
$option_arr[103][5] = "やる気に満ち溢れている";

$option_arr[104][1] = "非常に甘い";
$option_arr[104][2] = "やや厳しさが不足している";
$option_arr[104][3] = "普通";
$option_arr[104][4] = "やや厳し過ぎるが納得できる";
$option_arr[104][5] = "納得できる";

$option_arr[105][1] = "非常に悪い";
$option_arr[105][2] = "やや悪い";
$option_arr[105][3] = "普通";
$option_arr[105][4] = "比較的に良い";
$option_arr[105][5] = "非常に良い";

$option_arr[106][1] = "大いに改善を要する部署";
$option_arr[106][2] = "多少改善を要する部署";
$option_arr[106][3] = "普通";
$option_arr[106][4] = "比較的良い部署";
$option_arr[106][5] = "非常に良い部署";


$option_arr[201][1] = "不満";
$option_arr[201][2] = "やや不満";
$option_arr[201][3] = "調度良い";
$option_arr[201][4] = "まずまず納得";
$option_arr[201][5] = "納得できる";

$option_arr[202][1] = "不満";
$option_arr[202][2] = "やや不満";
$option_arr[202][3] = "調度良い";
$option_arr[202][4] = "まずまず納得";
$option_arr[202][5] = "納得できる";

$option_arr[203][1] = "全く合っていない";
$option_arr[203][2] = "あまり合っていない";
$option_arr[203][3] = "普通";
$option_arr[203][4] = "大体あっている";
$option_arr[203][5] = "ぴったり合っている";

$option_arr[204][1] = "全く活用できない";
$option_arr[204][2] = "あまり活用できない";
$option_arr[204][3] = "普通";
$option_arr[204][4] = "相当活用できる";
$option_arr[204][5] = "充分活用できる";

$option_arr[205][1] = "全くない";
$option_arr[205][2] = "あまりない";
$option_arr[205][3] = "普通";
$option_arr[205][4] = "相当ある";
$option_arr[205][5] = "大いにある";

$option_arr[206][1] = "全くない";
$option_arr[206][2] = "比較的ある";
$option_arr[206][3] = "普通";
$option_arr[206][4] = "相当ある";
$option_arr[206][5] = "大いにある";

$option_arr[207][1] = "限界を超えている";
$option_arr[207][2] = "現在が限界である";
$option_arr[207][3] = "もう少しある";
$option_arr[207][4] = "かなりある";
$option_arr[207][5] = "まだ大いにある";

$option_arr[208][1] = "非常に不満である";
$option_arr[208][2] = "多少不満である";
$option_arr[208][3] = "普通";
$option_arr[208][4] = "相当満足している";
$option_arr[208][5] = "非常に満足している";


$option_arr[301][1] = "非常に不満である";
$option_arr[301][2] = "多少不満である";
$option_arr[301][3] = "普通";
$option_arr[301][4] = "まずまず納得できる";
$option_arr[301][5] = "納得できる";

$option_arr[302][1] = "非常に不満である";
$option_arr[302][2] = "多少不満である";
$option_arr[302][3] = "普通";
$option_arr[302][4] = "まずまず納得できる";
$option_arr[302][5] = "充分納得できる";

$option_arr[303][1] = "非常に不満である";
$option_arr[303][2] = "やや不満である";
$option_arr[303][3] = "普通";
$option_arr[303][4] = "やむを得ない";
$option_arr[303][5] = "現状で納得できる";

$option_arr[304][1] = "非常に不満である";
$option_arr[304][2] = "多少不満である";
$option_arr[304][3] = "普通";
$option_arr[304][4] = "まずまず納得できる";
$option_arr[304][5] = "充分納得できる";


$option_arr[401][1] = "期待できない";
$option_arr[401][2] = "あまり期待できない";
$option_arr[401][3] = "期待できる";
$option_arr[401][4] = "かなり期待できる";
$option_arr[401][5] = "非常に期待できる";

$option_arr[402][1] = "かなり低いと思う";
$option_arr[402][2] = "未だ低いと思う";
$option_arr[402][3] = "普通";
$option_arr[402][4] = "比較的上位にあると思う";
$option_arr[402][5] = "トップクラスにあると思う";


$option_arr[501][1] = "意見を聞いてくれない";
$option_arr[501][2] = "意見をあまり聞いてくれない";
$option_arr[501][3] = "意見を聞いてくれる";
$option_arr[501][4] = "意見を大体良く聞いてくれる";
$option_arr[501][5] = "意見をよく聞いてくれる";
$option_arr[501][6] = "回答しない";

$option_arr[502][1] = "理解されていない";
$option_arr[502][2] = "もう少し理解して欲しい";
$option_arr[502][3] = "普通";
$option_arr[502][4] = "大体理解してくれている";
$option_arr[502][5] = "自分の考えをよく理解している";
$option_arr[502][6] = "回答しない";

$option_arr[503][1] = "指導不足である";
$option_arr[503][2] = "やや指導不足である";
$option_arr[503][3] = "普通";
$option_arr[503][4] = "指導は良い方である";
$option_arr[503][5] = "大体よく指導してくれる";
$option_arr[503][6] = "回答しない";

$option_arr[504][1] = "全く納得できない";
$option_arr[504][2] = "やや納得できない";
$option_arr[504][3] = "普通";
$option_arr[504][4] = "ほぼ納得している";
$option_arr[504][5] = "納得している";
$option_arr[504][6] = "回答しない";

$option_arr[505][1] = "とれていない";
$option_arr[505][2] = "あまりとれていない";
$option_arr[505][3] = "普通";
$option_arr[505][4] = "とれている";
$option_arr[505][5] = "良くとれている";
$option_arr[505][6] = "回答しない";

//////////チェックフラグ
$chk_arr[2][1] = "1";
$chk_arr[2][2] = "1";


///////////////////////////////////////////配列設定完了////////////////////////////
?>