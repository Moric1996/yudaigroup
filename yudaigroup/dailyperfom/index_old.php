<?
include "../inc/auth.inc";

include "../AdminLTE/class/ClassLoader.php";



$DBIO = new DBIO();
$yfd = new YudaiFoodData();


$shop_type = 1;
$year_month = ($_GET['year_month']) ? $_GET['year_month'] : "";
$date = new DateTime($year_month);
$display_start_date = $date->format('Y年 n月 1日');

$st_date = $date->format('Y-m-01');
$ed_date = $date->format('Y-m-t');

$yfd->setTargetData($DBIO->fetchDataTargetPeriod($st_date, $ed_date, $shop_type));

// 昨年分
$target_date = new DateTime($yfd->getLastDate());
$display_end_date = $target_date->format('Y年 n月 j日');
if ($target_date->format('m-d') == '02-29') {
    $target_date = new DateTime($target_date->format('Y-m').'-28');
}
$target_date->modify('-1 year');



$st_date_last_year = $target_date->format('Y-m-01');
$ed_date_last_year = $target_date->format('Y-m-d');

$yfd->setLastYearData($DBIO->fetchDataTargetPeriod($st_date_last_year, $ed_date_last_year, $shop_type));

$yfd->setTargetDate();

$yfd->formatNowData();
$yfd->formatLastYearData();




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

                                    <th colspan="4">当月売上</th>
                                    <th colspan="3">前年売上</th>
                                    <th colspan="3">客数</th>
                                    <th colspan="3">客単価</th>
                                    <th colspan="4">労働時間</th>
                                    <th colspan="3">人時売上</th>
                                    <th colspan="3">値引</th>
                                </tr>
                                <tr class="bg-primary">
                                    <th>昨日</th>
                                    <th>累計</th>
                                    <th>予算<br>累計</th>
                                    <th>予算<br>対比</th>

                                    <th>前年<br>同日</th>
                                    <th>前年同日<br>累計</th>
                                    <th>前年<br>対比</th>

                                    <th>累計</th>
                                    <th>前年同日<br>累計</th>
                                    <th>前年差</th>

                                    <th>累計</th>
                                    <th>前年同日<br>累計</th>
                                    <th>前年差</th>

                                    <th>昨日</th>
                                    <th>累計</th>
                                    <th>前年同日<br>累計</th>
                                    <th>前年差</th>

                                    <th>累計</th>
                                    <th>前年同日<br>累計</th>
                                    <th>前年差</th>

                                    <th>昨日</th>
                                    <th>累計額</th>
                                    <th>累計<br>値引率</th>
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
