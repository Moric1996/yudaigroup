<?
include "../inc/auth.inc";

include "../AdminLTE/class/ClassLoader.php";



$DBIO = new DBIO();
$yfd = new YudaiFoodData();


$shop_type = 1;
$year = ($_GET['year']) ? $_GET['year'] : "";

if(!$year){
    $month = date('n');
    if($month < 10){
        $year = date('Y',strtotime('-1 year'));
    } else {
        $year = date('Y');
    }
}


// $date = new DateTime($year.'-10-01');
// $display_start_date = $date->format('Y年');

// $st_date = $date->format('Y-10-01');
// $ed_date = $date->format('Y-12-31');

$target_st = $year."-10-01";

$st_date = date('Y-10-01', strtotime($target_st));
$ed_date = date('Y-09-30', strtotime("$st_date +1 year"));

$display_start_date = date("Y年 n月 j日", strtotime($st_date));
$display_end_date = date("Y年 n月 j日", strtotime($ed_date));
$display_semester = $year - 1985 . "期";
// @mail("y-kamata@yournet-jp.com", "yudai", "st_date = ".$st_date."\n"."ed_date = ".$ed_date);

// $yfd->setTargetData($DBIO->fetchDataTargetPeriodAnualSum($st_date, $ed_date, $shop_type));
$yfd->setTargetData($DBIO->fetchDataTargetPeriod($st_date, $ed_date, $shop_type));
// 昨年分
// $target_date = new DateTime($yfd->getLastDate());
// $display_end_date = $target_date->format('Y年 n月 j日');
// $target_date->modify('-1 year');

//DataTimeだとうまく1年前が取れなかったので
$st_date_last_year = date('Y-10-01', strtotime("$st_date -1 year"));
$ed_date_last_year = date('Y-09-30', strtotime("$ed_date -1 year"));

// @mail("y-kamata@yournet-jp.com", "yudai", "st_date = ".$st_date."\n"."ed_date = ".$ed_date);

// @mail("y-kamata@yournet-jp.com", "index.php st", "st_date = ".$st_date." : ed_date = ".$ed_date );
// @mail("y-kamata@yournet-jp.com", "index.php last", "st_date = ".$st_date_last_year." : ed_date = ".$ed_date_last_year );
// $yfd->setLastYearData($DBIO->fetchDataTargetPeriodAnualSum($st_date_last_year, $ed_date_last_year, $shop_type));
$yfd->setLastYearData($DBIO->fetchDataTargetPeriod($st_date_last_year, $ed_date_last_year, $shop_type));
// $yfd->setTargetDate();

// $yfd->formatNowData();
// $yfd->formatLastYearData();
$yfd->formatMonthlyData();
$yfd->formatLastYearDataforMonthly();


$chart_content = $yfd->getAnualChartHtml();
$chart_data = json_encode($yfd->getAnualChartData());

$year_month_option = array_reverse(getMonthRange2('20180101',$year));

include "../parts/header_monthly.php";

//Aタグ
$a_revenue_total = "./revenue_total.php?year=".$year;
$a_revenue = "./revenue.php?year=".$year;
$a_customer = "./customer.php?year=".$year;
$a_worktime = "./worktime.php?year=".$year;
$a_worktime_revenue = "./worktime_revenue.php?year=".$year;

function getMonthRange2 ($startUnixTime, $year)
{
/*    $start = (new DateTime)->setTimestamp($startUnixTime);
    $end   = (new DateTime)->setTimestamp($endUnixTime ? $endUnixTime : time());
    $next_month = new DateInterval('P1M');*/
    $start = new DateTime($startUnixTime);
    $end = new DateTime();

    $ymList = array();
    while ($start < $end) {

        if($start->format('Y') == $year){
            $ymList[] = array(
                'value' => $start->format('Y'),
                'text' => changeFormat($start),
                'selected' => 'selected'
            );
        } else {
            $ymList[] = array(
                'value' => $start->format('Y'),
                'text' => changeFormat($start));
        }



        
        $start->modify('+1 year');
    }

    return $ymList;
}

function changeFormat(DateTime $start){
    $year = $start->format('Y');

    $dispaly_val = $year - 1985 . "期";

    return $dispaly_val;

}

?>

<!--  自作css  -->
<link rel="stylesheet" href="./css/index.css">



<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper" style="margin-left: 0 !important;">


    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="h1">
            月次業績確認 売上累計
        </div>

        <div class="form-row">
            <div class="col-xs-10">
                <form method="get" action="./revenue_total.php" class="form-inline">

                    <select name="year" class="form-control">
                        <?php foreach ($year_month_option as $val): ?>
                            <option value="<? echo $val['value'] ?>" <? echo $val['selected'] ?>>
                            <? echo $val['text'] ?>
                            </option>
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

            <div class="row" style="margin-top:50px;">
                <div class="col-xs-12">                            
                    <a href="<?php echo $a_revenue_total;?>" role="button" class="btn bg-maroon">売上累計</a>
                    <a href="<?php echo $a_revenue;?>" role="button" class="btn btn-info">売上推移</a>
                    <a href="<?php echo $a_customer;?>" role="button" class="btn btn-warning">客数推移</a>
                    <a href="<?php echo $a_worktime?>" role="button" class="btn btn-danger">労働時間推移</a>
                    <a href="<?php echo $a_worktime_revenue?>" role="button" class="btn bg-olive">人時売上推移</a>
                </div>
            </div>


            <div class="row">

                
                
                <div class="col-xs-12">

                    <div class="box">

                        <div class="box-header">
                            <h3 class="box-title">対象期間: <?php echo $display_semester ?> (<?php echo $display_start_date; ?> ~ <?php echo $display_end_date; ?>)</h3>
                        </div>
                        <!-- /.box-header -->

                        <div class="box-body">

                            <? echo $chart_content; ?>

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.js">
<script type="text/javascript">

    // console.log($chart_data);

    const bgColorArr = [
        'rgba(255, 99, 132, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(255, 206, 86, 0.2)',
    ];

    const borderColorArr = [
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
    ];

    const transparent = 'rgba(0, 0, 0, 0)';

    var now = new Date();
    var nowYear = now.getFullYear();
    var nowMonth = now.getMonth() + 2;
    if(nowMonth > 12){
        nowYear = nowYear + 1;
        nowMonth = 1;
    }
    var comparisonYM = new Date(nowYear + "-" + nowMonth + "-01");

    var chartData = <?php echo $chart_data; ?>;
    for(var key in chartData){
        // console.log(key);
        // console.log(chartData[key]);

        var labelData = [];
        var monthlyDataArr = [];
        monthlyDataArr["売上"] = [];
        monthlyDataArr["売上予算"] = [];
        monthlyDataArr["売上前年"] = [];
        monthlyDataArr["客数"] = [];
        monthlyDataArr["労働時間"] = [];
        monthlyDataArr["人時売上"] = [];


        //積み上げ式売上のtmpデータ
        var saleTmp = 0;
        var saleYosanTmp = 0;
        var saleLastYearTmp = 0;
        for(var month in chartData[key]){

            // console.log("month = " + month);

            //現在月まで。
            var targetYM = new Date(month + "-01");
            if(comparisonYM <= targetYM){
                // console.log("comparisonYM = " + comparisonYM);
                // console.log("targetYM = " + targetYM);
                continue;
            }



            labelData.push(month);
            //売上
            saleTmp = saleTmp + chartData[key][month]["売上"][0];
            monthlyDataArr["売上"].push(saleTmp);
            //売上予算
            saleYosanTmp = saleYosanTmp + chartData[key][month]["売上"][1];
            monthlyDataArr["売上予算"].push(saleYosanTmp);
            //売上前年
            saleLastYearTmp = saleLastYearTmp + chartData[key][month]["売上"][2];
            monthlyDataArr["売上前年"].push(saleLastYearTmp);
            
            // monthlyDataArr["客数"].push(chartData[key][month]["客数"][0]);
            // monthlyDataArr["労働時間"].push(chartData[key][month]["労働時間"][0]);
            // monthlyDataArr["人時売上"].push(chartData[key][month]["人時売上"][0]);
        }

        // console.log(monthlyDataArr);
        
        for(var type in monthlyDataArr){

            var idName;
            var chartDataObj;
            switch(type){
                case "売上":
                    idName = key + "1";
                    chartDataObj = [
                        {
                            label: "前年",
                            data: monthlyDataArr["売上前年"],
                            borderColor: borderColorArr[2],
                            backgroundColor: bgColorArr[2],
                            fill: false,
                        },
                        {
                            label: "予算",
                            data: monthlyDataArr["売上予算"],
                            backgroundColor: transparent,
                            borderColor: borderColorArr[1],
                            backgroundColor: bgColorArr[1],
                            fill: false,
                        },
                        {
                            label: "売上",
                            data: monthlyDataArr["売上"],
                            borderColor: borderColorArr[0],
                            backgroundColor: bgColorArr[0],
                            fill: false,
                        },
                    ];
                    break;
                /*
                case "客数":
                    idName = key + "2";
                    chartDataObj = [
                        {
                            label: "客数",
                            data: monthlyDataArr["客数"],
                            backgroundColor: bgColorArr[1],
                            borderColor: borderColorArr[1],               
                        }
                    ];
                    break;
                case "労働時間":
                    idName = key + "3";
                    chartDataObj = [
                        {
                            label: "労働時間",
                            data: monthlyDataArr["労働時間"],
                            backgroundColor: bgColorArr[2],
                            borderColor: borderColorArr[2],               
                        }
                    ];
                    break;
                case "人時売上":
                    idName = key + "4";
                    chartDataObj = [
                        {
                            label: "人時売上",
                            data: monthlyDataArr["人時売上"],
                            backgroundColor: bgColorArr[3],
                            borderColor: borderColorArr[3],               
                        }
                    ];
                    break;
                */
            }

            // console.log(idName);

            

            var graph = document.getElementById(idName);
            if(!graph){
                console.log('graph area not exist ! : ' + idName);
                continue;
            }

            var ctx = graph.getContext('2d');
            ctx.canvas.height = 400;
            
            var chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labelData,
                    datasets: chartDataObj,
                },
                options: {
                    responsive: true,
                    maintainAspectRatio : false,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: function(label, index, labels){
                                    return label/ 1000000;
                                }
                            },
                            scaleLabel: {
                                display: true,
                                labelString: '(百万円)'
                            }
                        }]
                    },
                    tooltips : {
                        mode : 'index',
                        intersect : false,
                        callbacks : {
                            label : function(tooltipItem, data){
                                var val = tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                                var label;
                                switch(tooltipItem.datasetIndex){
                                    case 2:
                                        label = "売上 : " + val;
                                        break;
                                    case 1:
                                        label = "予算 : " + val;
                                        break;
                                    case 0:
                                        label = "前年 : " + val;
                                        break;
                                }
                                return label;
                            }
                        }
                    },
                }
            });
        }



    }



</script>
