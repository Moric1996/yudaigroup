<?
include "../inc/auth.inc";

include "../AdminLTE/class/ClassLoader.php";


$DBIO = new DBIO();
$yfd = new WeeklyArchivePerform();


function getDateFromWeekInfo($year, $month, $no, $week)
{
    /*
     * 以下の記述は PHP 5.3.0 以降で可能
     * それ以前の PHP では setDate が成功した場合は null がリターンされるので、
     * 2行に分けて $date->format('w'); のように記述すること
     */
    // 指定年月の１日の曜日を抽出する
    $date = new DateTime("{$year}{$month}01");
    $first_week = $date->format('w'); // 0 (日曜)から 6 (土曜) を取得する

    // 1日の曜日の指定週の日付を求める
    $day = ($no - 1) * 7 + 1;


    // 指定曜日と1日の曜日の差分（日数）を求め、指定の日付を計算する
    $diff = $week - $first_week;

    if ($diff < 0) {
        $day += $diff; // 1日の曜日より前の曜日の場合
    } else {
        $day += $diff; // 1日の曜日より後の曜日の場合
    }

    // 組み立てた日付が月の最終日（日数）よりも大きい場合は false リターン
    if ($date->format('t') < $day) {
        return false;
    }

    $new_date = new DateTime();
    $new_date->setDate($year, $month, $day);
    return $new_date;
}

function getWeekRange($date)
{
    // 戻り値
    $start_date = "";
    $end_date = "";
    // UNIXタイムスタンプに変換
    $t = strtotime($date);
    // 開始の日曜を計算
    if (date('w', $t) == 0) {

    } else {
        $t = strtotime("last sunday", $t);
    }
    $start_date = date("Y-m-d", $t);
    // 終了の土曜を計算
    $t = strtotime("next saturday", $t);
    $end_date = date("Y-m-d", $t);

    return array($start_date, $end_date);
}


function getBetweenDate($st, $ed)
{
    $s = new DateTime($st);
    $e = new DateTime($ed);

    $result[] = $s->format('Y-m-d');
    $count = 0;
    while ($s->format('Y-m-d') !== $e->format('Y-m-d')) {
        $s->modify('+ 1 day');
        $result[] = $s->format('Y-m-d');
        $count++;
        if ($count > 100) {
            return array();
        }
    }
    return $result;
}


function getMonthRange2($startUnixTime)
{
    /*    $start = (new DateTime)->setTimestamp($startUnixTime);
        $end   = (new DateTime)->setTimestamp($endUnixTime ? $endUnixTime : time());
        $next_month = new DateInterval('P1M');*/
    $start = new DateTime($startUnixTime);
    $end = new DateTime();

    $ymList = array();
    while ($start < $end) {
        $ymList[] = array('value' => $start->format('Y-m'), 'text' => $start->format('Y年m月'));
        $start->modify('+1 month');
    }

    return $ymList;
}

function get_next_week_button_link($target_date, $week_count) {
    $next_date = new DateTime($target_date->format('Y-m-d'));
    $next_date->modify('+1 week');
    $next_date->modify('+6 days');
    $temp_next_week_count = $week_count;

// 月が変わっている場合は1に
    if ($target_date->format('Y-m') != $next_date->format('Y-m')) {
        $temp_next_week_count = 1;
    } else {
        $temp_next_week_count++;
    }
    return "./index3.php?year_month=" . $next_date->format('Y-m') . "&week_count=" . $temp_next_week_count;
}

function get_last_week_button_link($target_date, $week_count) {
    $last_date = new DateTime($target_date->format('Y-m-d'));
    $last_date->modify('-1 week');
    $temp_last_week_count = $week_count;
    while (true) {
        if ($temp_last_week_count == 1) {
            $temp_last_week_count = 5;
        } else {
            $temp_last_week_count -= 1;
        }
        $target_last_button_date = getDateFromWeekInfo($last_date->format('Y'), $last_date->format('m'), $temp_last_week_count, 0);
        if ($target_last_button_date) {
            break;
        }
    }
    return "./index3.php?year_month=" . $last_date->format('Y-m') . "&week_count=" . $temp_last_week_count;
}


/****** ここから本編 ******/

$shop_type = 1;
$year_month = ($_GET['year_month']) ? $_GET['year_month'] : "";
$target_year_month = new DateTime($year_month);

$week_count = ($_GET['week_count']) ? $_GET['week_count'] : "";


// 週指定がない場合直近日曜日
if (!$week_count) {
    $t = strtotime($target_year_month->format('Y-m-d'));
    // 開始の日曜を計算
    if (date('w', $t) != 0) {
        $t = strtotime("last sunday", $t);
    }
    $week_count = floor(date('j', $t) / 7) + 1;
    $target_year_month = new DateTime(date('Y-m-d', $t));
    $year_month = $target_year_month->format('Y-m');
}


// 対象週の日曜日を取得する
$target_date = getDateFromWeekInfo($target_year_month->format('Y'), $target_year_month->format('m'), $week_count, 0);
// もしなかったら第1日曜日
if (!$target_date) {
    $week_count = 1;
    $target_date = getDateFromWeekInfo($target_year_month->format('Y'), $target_year_month->format('m'), $week_count, 0);
}


// 次週前週ボタン作成用
$last_week_link = get_last_week_button_link($target_date, $week_count);
$next_week_link = get_next_week_button_link($target_date, $week_count);



// 対象週
list($target_week_st, $target_week_ed) = getWeekRange($target_date->format('Y-m-d'));

// この日を新店閉店の基準にする
$yfd->setTargetDate($target_week_st);

// 1週間の売上があるかにかかわらず表示する予算取得
$yfd->setWeekData($DBIO->fetchDataTargetPeriodAllData($target_week_st, $target_week_ed, $shop_type), $target_week_st);

// 1週間の売上、労働時間、予算
$target_week_data = $DBIO->fetchDataTargetPeriod($target_week_st, $target_week_ed, $shop_type);
$yfd->setWeekDiffData($target_week_data, $target_week_st);

// データのある最終日を取得
$data_last_target = array_pop($target_week_data);
$target_week_last_data_date = new DateTime($data_last_target['date']);

$target_week_month_st = $target_week_last_data_date->format('Y-m-01');
$target_week_month_ed = $target_week_last_data_date->format('Y-m-d');
$target_week_month_last_day = $target_week_last_data_date->format('Y-m-t');

$temp = new DateTime($target_week_st);

// 目標人時取得
$yfd->setMonthTargetManHour($DBIO->fetchTargetMonthManHour($temp->format('Y-m-01')), $target_week_st);

// 1ヶ月の予算すべてを取得する
$yfd->setMonthBudget($DBIO->fetchDataTargetPeriodAllData(
        $target_week_month_st, $target_week_month_last_day, $shop_type), $target_week_st);

// 1ヶ月の売上が存在する売上、予算、労働時間
$yfd->setMonthData($DBIO->fetchDataTargetPeriodAllData(
    $target_week_month_st, $target_week_month_ed, $shop_type), $target_week_st);







// 対象週の前の週
$target_date->modify('-1 week');
list($last_week_st, $last_week_ed) = getWeekRange($target_date->format('Y-m-d'));

// 1週間の売上があるかにかかわらず表示する予算取得
$yfd->setWeekData($DBIO->fetchDataTargetPeriodAllData($last_week_st, $last_week_ed, $shop_type), $last_week_st);


// データのある最終日を取得
$last_week_last_data_date = new DateTime($target_week_last_data_date->format('Y-m-d'));
$last_week_last_data_date->modify('-1 week');

$last_week_month_st = $last_week_last_data_date->format('Y-m-01');
$last_week_month_ed = $last_week_last_data_date->format('Y-m-d');
$last_week_month_last_day = $last_week_last_data_date->format('Y-m-t');

$temp2 = new DateTime($last_week_st);

// 目標人時取得
$yfd->setMonthTargetManHour($DBIO->fetchTargetMonthManHour($temp2->format('Y-m-01')), $last_week_st);

// 1週間の売上、労働時間、予算
$last_week_data = $DBIO->fetchDataTargetPeriod($last_week_st, $last_week_month_ed, $shop_type);
$yfd->setWeekDiffData($last_week_data, $last_week_st);



// 1ヶ月の予算すべてを取得する
$yfd->setMonthBudget($DBIO->fetchDataTargetPeriodAllData(
    $last_week_month_st, $last_week_month_last_day, $shop_type), $last_week_st);

// 1ヶ月の売上が存在する売上、予算、労働時間
$yfd->setMonthData($DBIO->fetchDataTargetPeriodAllData(
    $last_week_month_st, $last_week_month_ed, $shop_type), $last_week_st);



$last_year_date = new DateTime($target_week_last_data_date->format('Y-m-d'));
if ($last_year_date->format('t') == "29") {
    $last_year_date->modify('-1 day');
}
$last_year_date->modify('-1 year');

// 前年データ
$yfd->setLastYearData($DBIO->fetchDataTargetPeriod($last_year_date->format('Y-m-01'), $last_year_date->format('Y-m-d'), $shop_type));






// 表示する1週間
$target_week_date_list = getBetweenDate($target_week_st, $target_week_ed);
$last_week_date_list = getBetweenDate($last_week_st, $last_week_ed);

$yfd->setTargetWeekList($target_week_date_list);
$yfd->setLastWeekList($last_week_date_list);

// 画面表示用
$target_week_st_date = new DateTime($target_week_st);
$display_st_date = $target_week_st_date->format('Y-n-j');
$target_week_ed_date = new DateTime($target_week_ed);
$display_ed_date = $target_week_ed_date->format('Y-n-j');


// 最終表示の出力
$display_table_body = $yfd->getDisplayHtml();

/**** ここからフォームとか表示とか *****/

$year_month_option = array_reverse(getMonthRange2('20190101'));


// 天気取得
$last_weathers = $DBIO->fetchTargetWeather($last_week_st, $last_week_ed);
$this_weathers = $DBIO->fetchTargetWeather($target_week_st, $target_week_ed);
$weather_dates = array();
foreach ($last_weathers as $weather) {
    $weather_dates[$weather['date']][$weather['timezone_status']] = $weather['weather'];
}
foreach ($this_weathers as $weather) {
    $weather_dates[$weather['date']][$weather['timezone_status']] = $weather['weather'];
}

$week_array = array(
    '0' => '日',
    '1' => '月',
    '2' => '火',
    '3' => '水',
    '4' => '木',
    '5' => '金',
    '6' => '土',
);

include "../parts/header.php";

?>

<!--  自作css  -->
<link rel="stylesheet" href="./css/index.css">

<style>
    th,td {
        font-size: 15px !important;
    }
</style>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper" style="margin-left: 0 !important;">


    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="h1">
            週次日別実績確認
        </div>
        <div class="col-xs-8">
            <div class="form-row">
                <div>
                    <form method="get" action="./index3.php" class="form-inline">

                        <select name="year_month" class="form-control">
                            <?php foreach ($year_month_option as $val): ?>
                                <option value="<? echo $val['value'] ?>" <? echo $val['value'] == $year_month ? 'selected' : '' ?>><? echo $val['text'] ?></option>
                            <?php endforeach; ?>
                        </select>

                        <select name="week_count" class="form-control">
                            <?php foreach (range(1, 5) as $val): ?>
                                <option value="<? echo $val ?>" <? echo $val == $week_count ? 'selected' : '' ?>>
                                    第<? echo $val ?>日曜
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button class="btn btn-primary" type="submit">送信</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xs-2">
            <a href="form_work_revenue.php" role="button" class="btn btn-success btn-lg">目標人時入力</a>
        </div>

        <div class="col-xs-2">
            <a href="../index.php" role="button" class="btn btn-danger btn-lg">トップへ戻る</a>
        </div>


    </section>


    <!-- Main content -->
    <section class="content">


        <div class="row">

            <div class="col-xs-12">

                <div class="box">

                    <div class="box-header">
                        <h3 class="box-title">対象期間: <?php echo $display_st_date . "～" . $display_ed_date; ?></h3>
                    </div>
                    <!-- /.box-header -->


                    <div style="display:flex;justify-content:space-between">
                        <a class="btn btn-secondary" href="<?= $last_week_link ?>">前週</a>
                        <a class="btn btn-secondary" href="<?= $next_week_link ?>">次週</a>
                    </div>


                    <div class="box-body">

                        <table id="example1" class="table table-bordered ">
                            <thead>
                            <tr class="bg-primary">
                                <th class="division_line">業態<br>店舗</th>
                                <th>当月予算<br>着地予測<br>当月目標人時</th>
                                <?php foreach ($last_week_date_list as $val): ?>
                                    <th><?= date('n/j', strtotime($val)) . "<br>(" . $week_array[date('w', strtotime($val))] . ")" ?></th>
                                <?php endforeach; ?>
                                <th class="division_line">週予算<br>週実績<br>累計人時</th>

                                <th>当月予算<br>着地予測<br>当月目標人時</th>
                                <?php foreach ($target_week_date_list as $val): ?>
                                    <th><?= date('n/j', strtotime($val)) . "<br>(" . $week_array[date('w', strtotime($val))] . ")" ?></th>
                                <?php endforeach; ?>
                                <th class="left_border top_border">週予算<br>週実績<br>累計人時</th>
                                <th class="top_border">曜日同時点<br>前週差</th>
                                <th class="top_border">累計予算<br>累計実績<br>貯金借金</th>
                                <th class="top_border">予算比</th>
                                <th class="right_border top_border">前年比</th>
                            </tr>
                            </thead>

                            <tbody class="text-right">
                            <tr>
                                <th class="division_line">天気(12:00/18:00)</th>
                                <td></td>
                                <?php foreach ($last_week_date_list as $val): ?>
                                    <th><?= "<span class='weather'>" . $weather_dates[$val][1] . "</span>/<span class='weather'>" . $weather_dates[$val][2] . "</span>" ?></th>
                                <?php endforeach; ?>
                                <td class="division_line"></td>
                                <td></td>
                                <?php foreach ($target_week_date_list as $val): ?>
                                    <th><?= "<span class='weather'>" . $weather_dates[$val][1] . "</span>/<span class='weather'>" . $weather_dates[$val][2] . "</span>" ?></th>
                                <?php endforeach; ?>
                                <td class="left_border"></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="right_border"></td>
                            </tr>

                            <? echo $display_table_body; ?>

                            </tbody>

                        </table>

                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- 自作js -->

<? include "../parts/footer.php"; ?>

<script src="./js/index.js?9"></script>
