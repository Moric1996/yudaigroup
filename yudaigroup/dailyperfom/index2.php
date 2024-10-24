<?
include "../inc/auth.inc";

include "../AdminLTE/class/ClassLoader.php";


function getDateFromWeekInfo($year, $month, $no, $week)
{
    // 最初の一週間分の曜日を求める
    // 1日の曜日を求める

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
    if($diff < 0) {
        $day += $diff + 7; // 1日の曜日より前の曜日の場合
    } else {
        $day += $diff; // 1日の曜日より後の曜日の場合
    }
    // 組み立てた日付が月の最終日（日数）よりも大きい場合は false リターン
    if($date->format('t') < $day) { return false; }

    $new_date = new DateTime();
    $new_date->setDate($year, $month, $day);
    return $new_date;
}



$DBIO = new DBIO();
$yfd = new WeeklyPerform();


$shop_type = 1;
$year_month = ($_GET['year_month']) ? $_GET['year_month'] : "";
$week_count = ($_GET['week_count']) ? $_GET['week_count'] : "";

$date = new DateTime($year_month);

// していなかった場合
if (!$week_count) {
    $t = strtotime($date->format('Y-m-d'));
    // 開始の日曜を計算
    if (date('w', $t) != 0) {
        $t = strtotime("last monday", $t);
    }
    $week_count = floor(date('j', $t) / 7) + 1;
    $date = new DateTime(date('Y-m-d', $t));
    $year_month = $date->format('Y-m');
}
// 第何かの月曜日の日付を取得する
$target_week_st = getDateFromWeekInfo($date->format('Y'), $date->format('m'), $week_count, 1);
if (!$target_week_st) {
    $target_week_st = getDateFromWeekInfo($date->format('Y'), $date->format('m'), 1, 1);
}

$display_start_date = $target_week_st->format('Y年 n月 j日');
$st_date = $target_week_st->format('Y-m-d');
$target_week_st->modify('+ 7 days');

$display_end_date = $target_week_st->format('Y年 n月 j日');
$ed_date = $target_week_st->format('Y-m-d');

$yfd->setTargetDate($target_week_st->format('Ym'));

// 今年のデータを入れる
$yfd->setTargetData($DBIO->fetchDataTargetPeriod($st_date, $ed_date, $shop_type));


// 前の週のデータを取得する
$target_week_st->modify('- 14 days');
$last_week_st = $target_week_st->format('Y-m-d');
$target_week_st->modify('+ 7 days');
$last_week_ed = $target_week_st->format('Y-m-d');
$yfd->setLastWeekTargetData($DBIO->fetchDataTargetPeriod($last_week_st, $last_week_ed, $shop_type));


// 昨年分
$date->modify('-1 year');
$last_year = getDateFromWeekInfo($date->format('Y'), $date->format('m'), $week_count, 1);
if (!$last_year) {
    $last_year = getDateFromWeekInfo($date->format('Y'), $date->format('m'), 1, 1);
}
$st_date_last_year = $last_year->format('Y-m-d');
$last_year->modify('+ 7 days');
$ed_date_last_year = $last_year->format('Y-m-d');
$yfd->setLastYearData($DBIO->fetchDataTargetPeriod($st_date_last_year, $ed_date_last_year, $shop_type));



$yfd->formatNowData();
$yfd->formatLastWeekData();
$yfd->formatWeeklyLastYearData();




$display_table_body = $yfd->getDisplayHtml();



function getMonthRange2 ($startUnixTime)
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

$year_month_option = array_reverse(getMonthRange2('20190101'));



include "../parts/header.php";

?>

<!--  自作css  -->
<link rel="stylesheet" href="./css/index.css">



<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper" style="margin-left: 0 !important;">


    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="h1">
            週業績確認
        </div>

        <div class="form-row">
            <div class="col-xs-10">
                <form method="get" action="./index2.php" class="form-inline">

                    <select name="year_month" class="form-control">
                        <?php foreach ($year_month_option as $val): ?>
                            <option value="<? echo $val['value'] ?>" <? echo $val['value'] == $year_month ? 'selected' : '' ?>><? echo $val['text'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="week_count" class="form-control">
                        <?php foreach (range(1, 5) as $val): ?>
                            <option value="<? echo $val ?>" <? echo $val == $week_count ? 'selected' : '' ?>>第<? echo $val ?>月曜</option>
                        <?php endforeach; ?>
                    </select>

                    <button class="btn btn-primary" type="submit">送信</button>
                </form>
            </div>
        </div>

        <!--<ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
            <li class="active">Here</li>
        </ol>-->

        <div class="pull-right">
            <a href="../index.php" role="button" class="btn btn-success btn-lg">トップへ戻る</a>
        </div>

    </section>


    <!-- Main content -->
    <section class="content">


        <div class="row">

            <div class="col-xs-12">

                <div class="box">

                    <div class="box-header">
                        <h3 class="box-title">対象期間: <?php echo $display_start_date."～".$display_end_date; ?></h3>
                    </div>
                    <!-- /.box-header -->

                    <div class="box-body">

                        <table id="example1" class="table table-bordered ">
                            <thead>
                            <tr class="bg-primary">
                                <th rowspan="2">店舗名</th>

                                <th rowspan="2" class="division_line">週計</th>

                                <th colspan="4" class="division_line">前週<br>対比</th>
                                <th colspan="4" class="division_line">週予算<br>対比</th>
                                <th colspan="4" class="division_line">前年<br>対比</th>
                                <th colspan="5" class="division_line">客数<br>前週対比</th>
                                <th colspan="5" class="division_line">人時売上<br>前週対比</th>
                                <th colspan="4" class="division_line">週成績</th>
                            </tr>
                            <tr class="bg-primary">
                                <th>前週</th>
                                <th>前週差</th>
                                <th>前週<br>対比%</th>
                                <th class="division_line">前週対比<br>上下</th>

                                <th>週予算</th>
                                <th>週予算<br>達成率</th>
                                <th>差比%</th>
                                <th class="division_line">前週対比<br>上下</th>

                                <th>前年同週計</th>
                                <th>前年<br>対比</th>
                                <th>差比%</th>
                                <th class="division_line">前週対比<br>上下</th>

                                <th>週計</th>
                                <th>前週</th>
                                <th>前週差</th>
                                <th>前週<br>対比%</th>
                                <th class="division_line">前週対比<br>上下</th>

                                <th>累計</th>
                                <th>前週</th>
                                <th>前週差</th>
                                <th>前週<br>対比%</th>
                                <th class="division_line">前週対比<br>上下</th>

                                <th>A.売上UP<br>E.人時UP</th>
                                <th>A.売上UP<br>E.人時DOWN</th>
                                <th>A.売上DOWN<br>E.人時UP</th>
                                <th class="division_line">A.売上DOWN<br>E.人時DOWN</th>
                            </tr>
                            </thead>

                            <tbody class="text-right">

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

<script src="./js/index.js"></script>
