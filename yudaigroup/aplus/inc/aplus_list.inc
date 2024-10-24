<?php

$SERVICE_ID = 1;
/////////slip_typeリスト
/////////////////////////paper_banktrans status
$paper_banktrans_status_list[0] = "削除";
$paper_banktrans_status_list[1] = "通常";
$paper_banktrans_status_list[2] = "依頼書受付中";
$paper_banktrans_status_list[3] = "申請手続き中";
$paper_banktrans_status_list[4] = "不備あり再受付中";
$paper_banktrans_status_list[5] = "再申請中";
$paper_banktrans_status_list[6] = "審査不通過";
$paper_banktrans_status_list[9] = "別口座に変更";

/////////////////////////paper_banktrans_log result
$paper_banktrans_result_list[0] = "振替完了";
$paper_banktrans_result_list[1] = "資金不足";
$paper_banktrans_result_list[2] = "預金取引なし";
$paper_banktrans_result_list[3] = "預金者都合停止";
$paper_banktrans_result_list[4] = "依頼書なし";
$paper_banktrans_result_list[8] = "委託者都合停止";
$paper_banktrans_result_list[9] = "その他";

/////////////////////////決済結果status
$claim_status_list[0] = "削除";
$claim_status_list[9] = "請求中(CSVDL前)";
$claim_status_list[1] = "請求中(CSVDL済)";
$claim_status_list[2] = "決済完了";
$claim_status_list[3] = "決済失敗";

/////////////////////////ログprogress
$logprogress_list[1] = "access";
$logprogress_list[2] = "accessForm";
$logprogress_list[3] = "FormIput";
$logprogress_list[4] = "DLcomp";
$logprogress_list[99] = "regist";

/////////////////////////口座種類
$bank_type_list[1] = "普通";
$bank_type_list[2] = "当座";
$bank_type_list[4] = "貯蓄";

/////////////////////////口座新規コード
$bank_new_list[1] = "初回請求";
$bank_new_list[0] = "2回目以降";
$bank_new_list[2] = "変更あり";


///////////////////////////////////////////////////
?>