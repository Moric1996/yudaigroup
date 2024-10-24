<?php
include "../inc/auth.inc";

include "../AdminLTE/class/ClassLoader.php";

$dbio = new DBIO();

function getMonthRange2($startUnixTime)
{
    /*    $start = (new DateTime)->setTimestamp($startUnixTime);
        $end   = (new DateTime)->setTimestamp($endUnixTime ? $endUnixTime : time());
        $next_month = new DateInterval('P1M');*/
    $start = new DateTime($startUnixTime);
    $end = new DateTime();
    $end->modify('+2 months');

    $ymList = array();
    while ($start < $end) {
        $ymList[] = array('value' => $start->format('Y-m-01'), 'text' => $start->format('Y年m月'));
        $start->modify('+1 month');
    }

    return $ymList;
}

$year_month_option = array_reverse(getMonthRange2('20190101'));

$shop_all = $dbio->fetchShopList();
foreach ($shop_all as $key => $one) {
    $shop_list[$key]['id'] = $one['id'];
    $shop_list[$key]['name'] = $one['name'];
    if ($target_shop == $one['id']) {
        $shop_list[$key]['selected'] = 'selected';
    }
}

$man_hour_all = $dbio->fetchManHour();
foreach ($man_hour_all as $key => $val) {
    $temp_date = new DateTime($val['date']);
    $man_hour_all[$key]['date'] = $temp_date->format('Y-m-d');
}
$json_man_hour = json_encode($man_hour_all);

include "../parts/header.php";
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper" style="margin-left: 0 !important;">


    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="h1">
            目標人時入力
        </div>


        <div class="" style="text-align: right">
            <a href="./index3.php" role="button" class="btn btn-danger btn-lg">戻る</a>
        </div>
    </section>

    <form method="post" action="./insert_work_revenue.php">
        <div class="card-body" style="width: 70%; margin: 10px auto">
            <div class="form-horizontal">

                <div class="form-group row">
                    <div class="col-sm-2">
                        <label class="col-form-label">対象月</label>
                    </div>

                    <div class="col-sm-10">
                        <select name="target_date" id="target_date" class="form-control">
                            <?php foreach ($year_month_option as $val): ?>
                                <option value="<?= $val['value'] ?>" <?= ($val['value'] == $get_date) ? 'selected' : "" ?>><?= $val['text'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>


                <div class="form-group row">
                    <div class="col-sm-2">
                        <label class="col-form-label">店舗</label>
                    </div>

                    <div class="col-sm-10">
                        <select name="target_shop" id="target_shop"  class="form-control">
                            <?php foreach ($shop_list as $val): ?>
                                <option value="<?= $val['id'] ?>" <?= $val['selected'] ?>><?= $val['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-2">
                        <label class="col-form-label">目標人時</label>
                    </div>

                    <div class="col-sm-10">
                        <input class="form-control" id="work_revenue" type="number" name="work_revenue">
                    </div>
                </div>


                <button style="width: 30%;margin: 10px auto;" class="btn btn-primary" type="submit">確定</button>


            </div>
        </div>
    </form>


</div>
<script>
    var man_hour = <?= $json_man_hour ?>;


    const insert_work_revenue = () => {
        const date = document.getElementById("target_date").value;
        const shop_id = document.getElementById("target_shop").value;

        if (!target_date || !target_shop) {
            return;
        }

        console.log(shop_id);
        console.log(date);
        console.log(man_hour);
        res_array = man_hour.filter(val => (val.shop_id == shop_id && val.date == date));

        console.log(res_array);
        if (res_array.length > 0) {
            document.getElementById("work_revenue").value = res_array[0]['target_man_hour'];
        }
    };

    insert_work_revenue();

    document.getElementById("target_date").addEventListener('change', insert_work_revenue);
    document.getElementById("target_shop").addEventListener('change', insert_work_revenue);

</script>
<? include "../parts/footer.php"; ?>
