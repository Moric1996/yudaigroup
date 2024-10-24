<?php
if(empty($_SERVER['HTTPS'])) {
    header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
    exit;
}
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('./inc/ybase.inc');

$ybase = new ybase();
if(preg_match("/^[0-9]+$/",$messageid)){
	$addhtml="<input type=\"hidden\" name=\"display_message_id\" value=\"$messageid\">\n";

}
if(preg_match("/^[0-9]+$/",$consultid)){
	$addhtml="<input type=\"hidden\" name=\"display_consultid\" value=\"$consultid\">\n";
}
if(preg_match("/^[0-9]+$/",$manag_reconsultid)){
	$addhtml="<input type=\"hidden\" name=\"manag_reconsultid\" value=\"$manag_reconsultid\">\n";
}
if(preg_match("/^[0-9]+$/",$reconsultid)){
	$addhtml="<input type=\"hidden\" name=\"reconsultid\" value=\"$reconsultid\">\n";
}
if(preg_match("/^[0-9]+$/",$revuen_quetion_id)){
	$addhtml="<input type=\"hidden\" name=\"revuen_quetion_id\" value=\"$revuen_quetion_id\">\n";
}
if(isset($_COOKIE[setaccountid])){
	$accountid = $_COOKIE[setaccountid];
}
if(isset($_COOKIE[setpasswd])){
	$passwd = $_COOKIE[setpasswd];
}

$ybase->title = "雄大業務管理ポータル 利用規約";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri(1);
$ybase->ST_PRI .= <<<HTML

<br>
<br>
<div class="container">
    <main>
        <section class="lp-sec-list">
            <div class="lp-block">
                <h3 class="lp-title mb-3">サービス利用規約</h3>
                <div class="list-term">
                    <h4>
                        第１条（本サービス利用規約の適用）
                    </h4>
                    <p>
                        <span>
                            1. 本「サービス利用規約」（以下、「本規約」といいます）は、雄大グループ業務管理ポータル（以下、「本サービス」といいます）の利用等について、本サービスを利用するお客様（以下「甲」といいます）と当社の間で交わされる合意文書となります。甲が利用するすべての本サービスについて本規約が適用されるものとします。
                        </span>
                        <span>
                            2. 本規約以外に個別に定められた規約を含む特約がある場合、当該特約を優先するものとします。
                        </span>
                        <span>
                            3. 甲は、本規約に従い本サービスを利用するものとします。
                        </span>
                        <span>
                            4. 甲は、本規約のすべての記載事項について同意した上で、当社所定の手続きにより本サービス利用の申込みを行うものとします。
                        </span>
                        <span>
                            5. 当社は、甲から本サービス利用の申込みがあった場合、当社の基準に従ってこれを審査し、承諾する場合は、甲が本サービスを利用できるようにいたします。
                        </span>
                    </p>
                    <h4>
                        第２条（通知）
                    </h4>
                    <p>
                        <span>
                            1. 本サービスにおける当社から甲への通知は、本規約に別途定めのない限り、電子メール、ＦＡＸ、郵送または、本サービスを甲が利用するための管理用ウェブサイト（以下、「本ウェブサイト」といいます）上に掲載するなど、当社が適当と判断する方法により行います。
                        </span>
                        <span>
                            2. 前項の規定に基づき、当社から甲への通知を行う場合には、甲に対する当該通知の効力発生は、電子メールの送信、ＦＡＸの送信、郵送物の発送または本ウェブサイトへの掲載がなされた時点とします。
                        </span>
                    </p>
                    <h4>
                        第３条（利用許諾とその対価の支払）
                    </h4>
                    <p>
                        <span>
                            1. 当社は、甲が本規約等の諸条件を承諾することを条件として、本サービスを日本国内で非独占的に利用する権利を付与するものとします。
                        </span>
                        <span>
                            2. 甲は、前項の利用権付与の対価として、当社が指定する利用料を、指定期日までに指定方法にて当社に対して支払うものとします。また、支払いの際の振込手数料は甲の負担とします。
                        </span>

                    </p>

                    <h4>
                        第４条（最低利用期間及び利用休止・終了）
                    </h4>
                    <p>
                        <span>
                            本サービスの最低利用期間は1年間とし、以後、自動的に月毎に更新するものとする。甲が本サービスの利用を休止または終了する場合は、利用休止または終了日の30日前までに当社に書面で通知するものとします。その場合、利用休止または終了日を含む月の利用料金は日割計算をせずに1ヶ月分を甲が当社に支払うものとします。
                            <span><br>
                                (1) 既に本サービスに会員登録済みで、重複の登録となる場合<br>
                                (2) 会員登録申込み時において利用規約違反等により会員資格が停止処分中であったり、過去に利用規約違反等により会員資格取消の処分を受けたことがある場合<br>
                                (3) 弊社への申告内容や本サービスへの登録内容に虚偽の記載があった場合<br>
                                (4) その他弊社が会員として不適切と判断した場合
                            </span>
                        </span>
                    </p>

                    <h4>
                        第５条（アカウント管理）
                    </h4>
                    <p>
                        <span>
                            1. 甲及び甲の顧客のアカウントに関して、その使用及び保管は、甲が自らの責任において管理するものとし、第三者の使用や第三者への貸与・譲渡及び紛失や漏洩が発生しない様、その使用に際し、善良な管理者としての注意義務を負うものとします。
                        </span>
                        <span>
                            2. 当社は、当該アカウント及びパスワードの一致を確認した場合、当該アカウント及びパスワードを保有するものとして登録された利用者が本サービスを利用したものとみなします。
                        </span>
                    </p>
                    <h4>
                        第６条（禁止事項）
                    </h4>
                    <p>
                        <span>
                            甲は、本サービスの利用にあたり、次の各号に該当する行為をしてはならないものとし、甲の役員そして従業員または甲の管理下にある第三者、及び甲との契約の元、本サービスを利用する第三者にも次の各号に該当する行為をさせてはならないものとします。
                            <span><br>
                                (1) 利用許可のない第三者のアカウント情報を不正に入手し、使用する行為<br>
                                (2) 自分のアカウント情報を第三者に開示する行為<br>
                                (3) 当社からの許可を得ずに第三者に対する広告・宣伝・勧誘の目的で利用する行為<br>
                                (4) 選挙の事前運動、選挙運動またはこれらに類似する行為<br>
                                (5) 本サービスを提供するサーバーへの不正アクセス行為<br>
                                (6) 本サービスのプログラムやアプリ、マニュアル，仕様書，資料等の複製<br>
                                (7) 本サービスのプログラムやアプリの改変，変更、解析、逆コンパイルや逆アセンブルする行為<br>
                                (8) 当社及び第三者の財産権、著作権、特許権その他の知的財産権及びその他法律上保護された利益を侵害する行為<br>
                                (9) 他人のプライバシーもしくは肖像権を侵害する行為、または、侵害するおそれのある行為<br>
                                (10) 当社、他のお客様、その他の第三者又は本サービスに損害を与える行為<br>
                                (11) 当社の信用を毀損する行為<br>
                                (12) 当社及び他のお客様を誹謗又は中傷する行為<br>
                                (13) 本サービスの運営を妨げるような行為<br>
                                (14) 法令又は公序良俗に反する目的で利用する行為<br>
                                (15) 前各号に定める行為を助長する行為<br>
                                (16) その他、当社が不適当と判断する行為
                            </span>
                        </span>
                    </p>
                    <h4>
                        第７条（甲の業務データ）
                    </h4>
                    <p>
                        <span>
                            1. 甲及び甲の顧客が本サービスのサーバー上にアップロードしたデータや動画、画像等のコンテンツ（以下「業務データ」という）は、特段の合意がない限り、甲に著作権が帰属することに同意します。
                        </span>
                        <span>
                            2. 甲の業務データについては、甲の責任で管理するものとし、当社は甲のデータの内容の検閲、確認及び第三者への開示を行いません。但し、以下の各号のいずれかの場合はこの限りではありません。
                            <span><br>
                                (1) お客様又は公衆の生命、健康、財産等の重大な利益を保護するために必要だと合理的に判断できる場合<br>
                                (2) 本サービスの不具合改善や機能向上を目的としたシステムの改善や変更・追加の為に最低必要限必要である甲の業務データの利用をする場合<br>
                                (3) 本サービスの機能向上、サービス改善の為の分析・解析に利用する場合<br>
                                (4) 法令等に基づく開示請求があった場合
                            </span>
                        </span>
                        <span>
                            3. 業務データが本規約や公序良俗に反する場合、当社は、甲に通知の上、業務データの利用中止または削除を行うことができるものとします。ただし、緊急の場合は後日報告することを条件に当該業務データを利用中止または削除することができるものとします。
                        </span>
                        <span>
                            4. 業務データの正確性、正当性、合法性、信頼性、適切性、品質、著作権処理等については、甲が全責任を負うものとします。
                        </span>
                        <span>
                            5. 甲は、自らの責任で甲の業務データを管理するものとし、当社は定期的なデータ等のバックアップ(一部データを除く)を実施しますが、当該データの消失、改ざん、及び不正アクセス等による外部流出に関しては、当社は、法令の定めにより明示的に責任を負うものとされる場合を除き一切の責任を負わないものとします。
                        </span>
                        <span>
                            6. 当社は、甲が行った業務データ抹消、改変、複製、破壊、漏えい、損失ほか、不正アクセス、もしくは保存の失敗に関して、当社の故意または重過失がある場合を除き、その責任を負わないものとします。
                        </span>
                        <span>
                            7. 当社は、製品やサービスにおける品質向上を目的として、業務データを統計的に処理して利用することができるものとします。
                        </span>
                    </p>
                    <h4>
                        第８条（本サービス利用終了後の処理）
                    </h4>
                    <p>
                        <span>
                            1. 当社は、甲の本サービス利用が終了した場合（利用期間の満了、甲または当社からの解約、当社の甲に対する利用停止処分等、あらゆる理由により本サービスの利用が終了した場合を含みます。）、甲の本サービス利用終了後直ちに当社の一存で業務データを破棄し、当社の管理下にあるインターネットサーバーその他の設備に記録された業務データ、資料等についても、当社の一存で消去・削除するものとします。また、当社によるこれら破棄・消去・削除の処置について、甲は一切の異議を申し立てることができないものとします。
                        </span>
                        <span>
                            2. 甲は、本サービスの利用を終了した場合であって、本サービスの利用にあたって当社から提供を受けたデータ、ソフトウェア及びそれに関わる全ての資料等（それらの全部または一部の複製物を含みます。以下同じとします。）がある場合、これらを本サービスの利用終了後直ちに当社に返還または当社の指示により破棄し、甲の設備などに格納されたソフトウェア及び資料等については、甲の責任で消去するものとします。
                        </span>
                    </p>
                    <h4>
                        第９条（会員への通知方法）
                    </h4>
                    <p>
                        <span>
                            1. 当社は、甲が本サービスを利用する上で必要なサーバー及びプログラムの保守を、次の各号に指定する内容で、定期的もしくは適宜行うものとします。
                            <span><br>
                                (1) 本サービスを利用する上で支障となるバグや、プログラム上の問題の修正及び管理・改善<br>
                                (2) 本サービスを運営する上で必要となるシステム及びアプリのバージョンアップ<br>
                                (3) 別紙掲載の、期間毎の定期的な業務データバックアップ<br>
                                (4) 本サービスを利用する上で支障が生じないためのサーバーメンテナンス及び管理
                            </span>
                        </span>
                        <span>
                            2. 前項各号において、本サービスを利用する上で支障のない範囲の軽微な作業を実行する場合、当社は事前に甲にその旨を通知しなくても良いものとします。
                        </span>
                        <span>
                            3. 保守作業にあたり、当社は、その必要に応じて甲に通知のうえ、本サービスの運用を一時停止することができるものとします。
                        </span>
                        <span>
                            4. 甲の個別の事情により、通常の保守作業を超えて、当社において対応作業が必要な場合、当社は、別途作業範囲、作業費用を見積もり、甲の承諾のもとに作業を行うものとします。
                        </span>
                    </p>
                    <h4>
                        第10条（デザインの変更）
                    </h4>
                    <p>
                        <span>
                            当社は、甲への事前の通知なくして、本サービスのウェブサイトやアプリのデザインを変更することができるものとします。
                        </span>
                    </p>
                    <h4>
                        第11条（サービスの中断）
                    </h4>
                    <p>
                        <span>
                            1. 当社は、次の各号に該当する場合には、本サービスの提供の全部又は一部を中断することができるものとします。
                            <span><br>
                                (1) 本サービスを提供するための設備の保守、点検、整備、改良又は拡張等を実施する場合<br>
                                (2) 地震、台風、洪水、嵐等の自然災害、感染症の発生、戦争、内乱、暴動等の不可抗力により本サービスの提供ができなくなった場合<br>
                                (3) 行政機関または司法機関による業務を停止する旨の命令またはその指導・要請があった場合<br>
                                (4) 本サービスのサーバー等設備機器の不具合が生じた場合<br>
                                (5) 本サービスに関連するネットワーク回線の不具合が生じた場合<br>
                                (6) 本サービスのサーバーで動作するOS等ソフトウェア及び本サービス用ソフトウェアの不具合（エラー・バグの発生による場合を含みます）が生じた場合<br>
                                (7) アプリプラットフォーム（APPLE及びGOOGLE）の審査によるリジェクト及び審査による遅延、またはアカウント削除があった場合<br>
                                (8) 本サービスで利用する外部API等の不具合<br>
                                (9) 利用者による不正または誤った操作により本サービスの提供に支障が生じた場合<br>
                                (10) 本サービス用設備に対する第三者からの攻撃または不正アクセスがあった場合<br>
                                (11) 本サービス用設備または本サービス用ソフトウェアを再起動する必要が生じた場合<br>
                                (12) 本サービスへのアクセスが著しく増加し、本サービス提供用施設に過度の負荷を与えている場合もしくはそのおそれのある場合で、当社がその任意の裁量においてすべてのお客様に対して安定した本サービスの提供を確保するために必要と判断した場合<br>
                                (13) その他当社が本サービスの運営上一時的な中断が必要と判断した場合
                            </span>
                        </span>
                        <span>
                            2. 当社は、前項の規定により本サービスの提供の全部又は一部を中断するときは、オンライン通知等により事前にお客様に通知するものとします。但し、当社は、当社が緊急やむを得ないと判断した場合、事前のオンライン通知等を行わずに本サービスの提供の全部又は一部を中断することができるものとします。
                        </span>
                        <span>
                            3. 当社は、前二項の規定に基づく措置を講じたことにより甲が損害を被った場合であっても、当該損害につき一切の責任を負わないものとします。
                        </span>
                    </p>
                    <h4>
                        第12条（サービスの廃止・譲渡）
                    </h4>
                    <p>
                        <span>
                            1. 当社は、次の各号のいずれかに該当する場合、本サービスの全部または一部を廃止または譲渡することができるものとし、廃止または譲渡日をもって本契約は当然に終了し、当社は本契約に基づき本サービスを甲に利用させる義務を免れるものとします。
                            <span>
                                (1) 廃止または譲渡日の２か月前までに甲に通知した場合。<br>
                                (2) 天災地変等不可抗力により本サービスを提供できない場合。
                            </span>
                        </span>
                        <span>
                            2. 第1項に基づき本サービスの全部または一部を廃止または譲渡する場合、当社は、既に支払われている利用代金等のうち、廃止または譲渡する本サービスについて提供しない日数に対応する額を日割計算にて甲に返還するものとします。
                        </span>
                        <span>
                            3. 甲が本サービスの廃止または譲渡で被った、甲および第三者の損害については、当社はその責を負わないものとします。
                        </span>
                    </p>
                    <h4>
                        第13条（損害賠償）
                    </h4>
                    <p>
                        <span>
                            本規約の履行に際し、当社が甲に対する損害賠償義務を負う場合、当社は甲に現実に生じた通常の直接損害に対して、直接損害があった期間に甲が当社に支払った利用代金の総額を限度額として責任を負うものとします。当社は当社の予見の有無を問わず、甲の逸失利益及び間接損害等の特別の事情により生じた損害については、甲に対する賠償責任を負わないものとします。
                        </span>
                    </p>
                    <h4>
                        第14条（免責事項）
                    </h4>
                    <p>
                        <span>
                            1. 当社は、本サービスにおいて、当社の管理下にあるサーバーに記録される甲のデータのバックアップを保証するものではありません。
                        </span>
                        <span>
                            2. 本サービスの利用において、甲の責により当社その他の第三者に損害が発生した場合または権利が侵害された場合、甲は自己の費用と責任においてこれを解決するものとし、当社に対する金銭その他の請求を行わないものとします。
                        </span>
                        <span>
                            3. 甲は、本サービスの利用にあたり、当社に対して一切の迷惑損害をかけないものとします。
                        </span>
                        <span>
                            4. 第11条第1項各号、その他当事者の責に帰し得ない事由による本規約に基づく債務の履行の遅滞または不能が生じた場合は、当該当事者はその責を負わないものとします。
                        </span>
                        <span>
                            5. 当社は、甲に対し、以下の各号に該当する損害の責任ならびに以下の各号に付随する２次的なデータの漏洩、損失、損害にかかる責任を負わないものとします。
                            <span>
                                (1) 当社が提供していないプログラムによって生じる損害。<br>
                                (2) 当社以外の第三者による不正な行為によって生じる損害。<br>
                                (3) ハッカー及びクラッカーによるサーバーへの侵入または攻撃等の行為による損害。<br>
                                (4) 当社が善良な管理者の注意をもって業務を行ったにもかかわらず発生した本サービス上のバグによって生じる損害。
                            </span>
                        </span>
                        <span>
                            6. 当社は、本サービスにおいて甲に提供する情報（以下、「提供情報」といいます。）については、合理的な範囲内で正確性を確保するよう努力する義務を負いますが、その正確性、適時性、特定目的適合性、その他内容に関しては一切保証しないものとします。万一、提供情報の利用に起因して甲に損害が生じたとしても、当社は一切責任を負わないものとします。
                        </span>
                        <span>
                            7. 甲が本サービスを利用する為に甲側で要するハードウェア、ソフトウェア、通信環境、その他これらに付随して必要となる全ての機器・ソフトウェア等にかかる費用は、すべて甲の負担とします。また、それら機器・ソフトウェア等の利用に関しては甲の責任の範囲内において利用するものとし、当社は、その責任を負わず、それに起因して甲その他の第三者に損害が生じた場合であっても、一切責任を負わないものとします。
                        </span>
                        <span>
                            8. 当社は、本サービスに甲がアップロードするデータ・情報に関しては、一切責任を負わないものとします。
                        </span>
                    </p>
                    <h4>
                        第15条（秘密保持）
                    </h4>
                    <p>
                        <span>
                            1. 当社は、本サービスの利用または提供をしている期間中及び期間終了後においても、甲より開示または提供された秘密情報（甲及び顧客のメールアドレス等の個人情報を含みます。）を第三者に開示または漏洩しないものとします。但し、その情報が次の各号に該当する場合についてはこの限りではありません。
                            <span>
                                (1) 開示されたときに既に自ら所持していた情報。<br>
                                (2) 開示されたときに既に公知または公用であった情報。<br>
                                (3) 甲から開示を受けた後に自己の責に帰すべき事由によることなく公知または公用となった情報。<br>
                                (4) 甲から開示を受けた後に開示された情報と関係なく独自に開発した情報。<br>
                                (5) 第三者から秘密保持義務を負うことなく合法的に入手した情報。<br>
                                (6) 犯罪捜査等の目的で、官公署から開示を要求された情報。
                            </span>
                        </span>
                        <span>
                            2. 当社は、甲から提供を受けた秘密情報を本サービスの利用目的範囲内でのみ使用するものとします。
                        </span>
                        <span>
                            3. 当社は、サービスの開発・改善のため当社のスタッフ（プログラマー等を含む）に甲及び顧客の情報を開示する場合、当社と秘密保持契約を締結させ、当社と同等の秘密保持義務を課します。
                        </span>
                        <span>
                            4. 当社は、甲の要請があったときは、秘密情報に関する資料等を返還または消去するものとします。
                        </span>
                    </p>

                    <h4>
                        第16条（有効期間）
                    </h4>
                    <p>
                        <span>
                            本規約の有効期間は、本規約の締結日より１年間とし、本規約満了１ヶ月前までに甲または当社のいずれからも、相手方に対して本規約を更新しない旨の通知がなければ、本規約は自動的に満了日から１年間更新され、以後も同様とします。
                        </span>
                    </p>
                    <h4>
                        第17条（本規約の変更）
                    </h4>
                    <p>
                        <span>
                            1. 当社は、以下の場合に、当社の裁量により、本規約を変更することができます。
                            <span>
                                (1) 本規約の変更が、甲の一般の利益に適合するとき。<br>
                                (2) 本規約の変更が、契約をした目的に反せず、かつ、変更の必要性、変更後の内容の相当性、変更の内容その他の変更に係る事情に照らして合理的なものであるとき。
                            </span>
                        </span>
                        <span>
                            2. 当社は前項による本規約の変更にあたり、変更後の本規約の効力発生日の１か月前までに、本規約を変更する旨及び変更後の本規約の内容とその効力発生日を本ウェブサイトに掲示し、または甲に電子メールで通知します。
                        </span>
                        <span>
                            3. 変更後の本規約の効力発生日以降に甲が本サービスを利用したときは、甲は、本規約の変更に同意したものとみなします。
                        </span>
                    </p>
                    <h4>
                        第18条（契約の解除等）
                    </h4>
                    <p>
                        <span>
                            1. 甲または当社は、相手方が本規約に違反した場合、相手方に相当期間の催告をなした上、本規約を解除することができるものとします。
                        </span>
                        <span>
                            2. 甲または当社は、相手方に次の各号の事由の一が生じたときは何等の催告なしに，本規約を直ちに解除することができるものとします。
                            <span><br>
                                (1) 重大な過失または背信行為があったとき<br>
                                (2) 銀行取引停止処分を受けたとき<br>
                                (3) 手形の不渡りが生じたとき<br>
                                (4) 第三者から仮差押え、仮処分、差押え、滞納処分その他の強制執行処分を申し立てられたとき<br>
                                (5) 破産、民事再生手続き、会社更生手続きまたは特別清算手続きの申立をなした、あるいは申立をなされたとき<br>
                                (6) 反社会的勢力との関係を疑わせる事由があったとき<br>
                                (7) その他著しく不正な行為があったとき<br>
                            </span>
                        </span>
                        <span>
                            3. 甲または当社は，相手方の債務不履行が相当期間の催告後も是正されないときは，本規約を解除することができるものとします。

                        </span>
                    </p>
                    <h4>
                        第19条（変更の届出）
                    </h4>
                    <p>
                        <span>
                            甲は、当社に届け出た内容に変更が生じた場合、速やかに当社に対し所定の方法で当該変更の届出をするものとします。なお、当該届出を怠ったことにより、甲が損害を被ったとしても、当社は甲に対して一切の責任を負わないものとします。
                        </span>
                    </p>
                    <h4>
                        第20条（権利の譲渡）
                    </h4>
                    <p>
                        <span>
                            甲は、当社による事前の書面による承諾を得ることなく本規約上の地位又は権利義務の全部もしくは一部を第三者に譲渡し、又は担保に供してはなりません。
                        </span>
                    </p>
                    <h4>
                        第21条（権利の帰属）
                    </h4>
                    <p>
                        <span>
                            1. 本サービスを構成するプログラム・ソフトウェア・その他の付随的技術ならびに本サービス上に表示される当社側が用意した画像、文章等に関する一切の知的財産権は、全て当社または当社にライセンスを許諾している者に帰属します。甲は、これらを本サービスの利用以外の目的で利用することについては、当社の事前の承諾を得ることなく、転用し、第三者に提供し、または、自己もしくは第三者の営業のために利用することはできません。
                        </span>
                        <span>
                            2. 本サービス上に表示される、甲がアップロードした画像、文章等のデータにかかる著作権は、甲またはこれらの提供元（甲の顧客を含む）に帰属します。当社は、これらを、甲またはこれらの提供元（甲の顧客を含む）の事前の承諾を得ることなく他に転用し、第三者に提供し、または、自己もしくは第三者の営業のために利用いたしません。</span>
                        <span>
                            3. 当社は、甲（甲の顧客を含む）がアップロードした画像、文章等のデータに起因する直接的または間接的な著作権損害に関して、一切責任を負いません。
                        </span>
                    </p>

                    <h4>
                        第22条（反社会的勢力の排除）
                    </h4>
                    <p>
                        <span>
                            1. 甲及び当社は、その役員（取締役、執行役、執行役員、監査役又はこれらに準ずる者をいう。）又は従業員において、暴力団、暴力団員、暴力団準構成員、暴力団関係企業、総会屋等、社会運動等標榜ゴロ又は特殊知能暴力集団等、その他これらに準ずる者（以下「反社会的勢力等」という。）に該当しないこと、及び次の各号のいずれにも該当せず、かつ将来にわたっても該当しないことを確約し、これを保証するものとする。
                            <span>
                                (1) 反社会的勢力等が経営を支配していると認められる関係を有すること<br>
                                (2) 反社会的勢力等が経営に実質的に関与していると認められる関係を有すること<br>
                                (3) 自己、自社若しくは第三者の不正の利益を図る目的又は第三者に損害を加える目的をもってするなど、不当に反社会的勢力等を利用していると認められる関係を有すること<br>
                                (4) 反社会的勢力等に対して暴力団員等であることを知りながら資金等を提供し、又は便宜を供与するなどの関与をしていると認められる関係を有すること<br>
                                (5) 役員又は経営に実質的に関与している者が反社会的勢力等と社会的に非難されるべき関係を有すること
                            </span>
                        </span>
                        <span>
                            2. 甲及び当社は、自ら又は第三者を利用して、暴力的な要求行為、法的責任を超えた不当要求行為、取引に関して脅迫的な言動や暴力を用いる行為、風説・偽計・威力を用いて会社の信用を棄損し又は会社の業務を妨害する行為、反社会的勢力の活動を助長し又はその運営に資する行為、反社会的勢力への利益供与等その他これらに準ずる行為を行わないことを確約します。
                        </span>
                    </p>
                    <h4>
                        第23条（分離可能性）
                    </h4>
                    <p>
                        <span>
                            1. 本規約のいずれかの条項またはその一部が、法令等により無効または執行不能と判断された場合であっても、当該判断は他の部分に影響を及ぼさず、本規約の残りの部分は、引き続き有効かつ執行力を有するものとします。当社及び甲は、当該無効または執行不能と判断された条項またはその一部の趣旨に従い、これと同等の効果を確保できるよう努めるとともに、修正された本規約に拘束されることに同意するものとします。
                        </span>
                        <span>
                            2. 本規約のいずれかの条項またはその一部が、ある契約者との関係で無効または執行不能と判断された場合であっても、他の契約者との関係における有効性等には影響を及ぼさないものとします。
                        </span>
                    </p>
                    <h4>
                        第24条（準拠法）
                    </h4>
                    <p>
                        <span>
                            本規約は日本法を準拠法とし、かつ、同法に従い解釈されるものとします。
                        </span>
                    </p>
                    <h4>
                        第25条（合意管轄）
                    </h4>
                    <p>
                        <span>
                            本規約の履行に関し紛争が生じたときは、静岡地方裁判所沼津支部を第一審の専属的合意管轄裁判所とします。
                        </span>
                    </p>
                    <h4>
                        第26条（協議等）
                    </h4>
                    <p>
                        <span>
                            本規約に定めない事項または解釈に疑義の生じた事項については、当社と甲は信義誠実に協議の上、これを解決するものとします。
                        </span>
                    </p>

                    <p>
                    </p>
                    <p>
                        <span>株式会社ユアネット</span>
                    </p>
                </div>
            </div>
        </section>
    </main>
</div>
<br>
<br>


HTML;

$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
?>