<?
include "../inc/auth.inc";

include "../AdminLTE/class/ClassLoader.php";



$DBIO = new DBIO();
$yfd = new YudaiFoodData();


$shop_type = 1;
$year_month = ($_GET['year_month']) ? $_GET['year_month'] : "";

$date = new DateTime($year_month);
$display_start_date = $date->format('Y年 n月 1日');

$last_date = $_GET['last_date'] && $_GET['last_date'] <= $date->format('t') ? $_GET['last_date'] : "";

$st_date = $date->format('Y-m-01');
$ed_date = $last_date ? $date->format('Y-m-'.$last_date) : $date->format('Y-m-t');

// 今年のデータを入れる
$yfd->setTargetData($DBIO->fetchDataTargetPeriod($st_date, $ed_date, $shop_type));

// 今月の予算を入れる
$yfd->setTotalBudget($DBIO->fetchDataTargetTotalBudgetPeriod($st_date, $date->format('Y-m-t'), $shop_type));

// 昨年分
$target_date = new DateTime($yfd->getLastDate());
$display_end_date = $target_date->format('Y年 n月 j日');
$end_day = $target_date->format('j日');
if ($target_date->format('m-d') == '02-29') {
    $target_date = new DateTime($target_date->format('Y-m').'-28');
}
$target_date->modify('-1 year');

if (!$last_date) {
    $last_date = $target_date->format('j');
}

$st_date_last_year = $target_date->format('Y-m-01');
$ed_date_last_year = $target_date->format('Y-m-d');

$yfd->setLastYearData($DBIO->fetchDataTargetPeriod($st_date_last_year, $ed_date_last_year, $shop_type));


$target_date->modify('-1 year');

$st_date_last_last_year = $target_date->format('Y-m-01');
$ed_date_last_last_year = $target_date->format('Y-m-d');

$yfd->setLastLastYearData($DBIO->fetchDataTargetPeriod($st_date_last_last_year, $ed_date_last_last_year, $shop_type));


$yfd->setTargetDate();

$yfd->formatNowData();
$yfd->formatLastYearData();
$yfd->formatLastLastYearData();




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
            日次業績確認
        </div>

        <div class="form-row">
            <div class="col-xs-10">
                <form method="get" action="./index.php" class="form-inline">

                    <select name="year_month" class="form-control">
                        <?php foreach ($year_month_option as $val): ?>
                            <option value="<? echo $val['value'] ?>" <? echo $val['value'] == $year_month ? 'selected' : '' ?>><? echo $val['text'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="last_date" class="form-control">
                        <?php foreach (range(1, 31) as $val): ?>
                            <option value="<? echo $val ?>" <? echo $val == $last_date ? 'selected' : '' ?>><? echo $val ?>日</option>
                        <?php endforeach; ?>
                    </select>
                    まで

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

                                    <th rowspan="2">当月予算</th>

                                    <th colspan="6" class="division_line">当月売上</th>
                                    <th colspan="4" class="division_line">前年売上</th>
                                    <th colspan="4" class="division_line">前々年売上</th>
                                    <th colspan="5" class="division_line">人時売上</th>
                                    <th colspan="4" class="division_line">客数</th>
                                    <th colspan="4" class="division_line">客単価</th>
                                    <th colspan="4" class="division_line">労働時間</th>
                                    <th colspan="3" class="division_line">値引</th>
                                </tr>
                                <tr class="bg-primary">
                                    <th><?php echo $end_day ?>実績</th>
                                    <th>実績累計</th>
                                    <th>予算同日<br>累計</th>
                                    <th class="brown_border">予算対比</th>
                                    <th>予算対比<br>上下</th>
                                    <th class="division_line">当月着地見込</th>

                                    <th>前年<br>同日</th>
                                    <th>前年同日<br>累計</th>
                                    <th class="brown_border">前年<br>対比</th>
                                    <th class="division_line">売上<br>上下</th>

                                    <th>前々年<br>同日</th>
                                    <th>前々年同日<br>累計</th>
                                    <th class="brown_border">前々年<br>対比</th>
                                    <th class="division_line">売上<br>上下</th>

                                    <th><?php echo $end_day ?>実績</th>
                                    <th>累計</th>
                                    <th>前年同日<br>累計</th>
                                    <th class="brown_border division_line">前年差</th>
                                    <th class="division_line">人事売上<br>上下</th>

                                    <th><?php echo $end_day ?>実績</th>
                                    <th>累計</th>
                                    <th>前年同日<br>累計</th>
                                    <th class="division_line">前年差</th>

                                    <th><?php echo $end_day ?>実績</th>
                                    <th>累計</th>
                                    <th>前年同日<br>累計</th>
                                    <th class="division_line">前年差</th>

                                    <th><?php echo $end_day ?></th>
                                    <th>累計</th>
                                    <th>前年同日<br>累計</th>
                                    <th class="division_line">前年差</th>

                                    <th><?php echo $end_day ?></th>
                                    <th>累計額</th>
                                    <th class="division_line">累計<br>値引率</th>
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
                <div class="pull-right">
                    <a href="../entry/set_sale.php" role="button" class="btn btn-warning btn-sm">データ入力</a>
                </div>

                <div style="margin: 0 10px">
                    <div class="h3">雄大PSの業績管理取得タイミング</div>
                    <ul>
                        <li>binoから予算取得1ヶ月分</li>
                        <ul>
                            <li>1日23:40</li>
                            <li>6日23:40</li>
                            <li>15日23:40</li>
                        </ul>

                        <li>bino売上実績</li>
                        <ul>
                            <li>毎時30分に前日分取得</li>
                            <li>毎日23:50に5日前分取得</li>
                        </ul>


                        <li>ジョブカンから勤怠時間数</li>
                        <ul>
                            <li>毎日7:00<br>
                                1日～前日までデータ更新</li>
                            <li>6日と15日<br>
                                前月分データ更新</li>
                        </ul>

                        <li>天気</li>
                        <ul>
                            <li>毎日12:00</li>
                        </ul>

                        <li>ランチディナー売上</li>
                        <ul>
                            <li>毎日23:35</li>
                        </ul>
                    </ul>

                    <div>※上記以後にBINOのデータを変えても反映されませんのでご注意ください。</div>
                </div>
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <!-- 自作js -->

<? include "../parts/footer.php"; ?>

<script src="./js/index.js"></script>
