<?php

$shop_id = $_GET['shop_id'];
$conn = pg_connect('host=localhost user=yournet dbname=yudai_admin port=5432');
include "../csvconvert/add_shop_list.php";

foreach ($add_shop_list as $value) {
    $selected = ($value['id'] == $shop_id) ? 'selected' : '';
    $select_option .= <<<HTML
<option value="{$value['id']}" {$selected}>{$value['name']}</option>
HTML;

}

$year_format_array = array(
    array('key' => '2020', 'value' => "2020年10月~2021年9月"),
    array('key' => '2021', 'value' => "2021年10月~2022年9月"),
    array('key' => '2022', 'value' => "2022年10月~2023年9月"),
    array('key' => '2023', 'value' => "2023年10月~2024年9月"),
    array('key' => '2024', 'value' => "2024年10月~2025年9月"),
    array('key' => '2025', 'value' => "2025年10月~2026年9月"),
    array('key' => '2026', 'value' => "2026年10月~2027年9月"),
    array('key' => '2027', 'value' => "2027年10月~2028年9月"),
    array('key' => '2028', 'value' => "2028年10月~2029年9月"),
    array('key' => '2029', 'value' => "2029年10月~2030年9月"),
    array('key' => '2030', 'value' => "2030年10月~2031年9月"),
    array('key' => '2031', 'value' => "2031年10月~2032年9月"),
    array('key' => '2032', 'value' => "2032年10月~2033年9月"),
    array('key' => '2033', 'value' => "2033年10月~2034年9月"),
    array('key' => '2034', 'value' => "2034年10月~2035年9月"),
    array('key' => '2035', 'value' => "2035年10月~2036年9月"),
    array('key' => '2036', 'value' => "2036年10月~2037年9月"),
    array('key' => '2037', 'value' => "2037年10月~2038年9月"),
    array('key' => '2038', 'value' => "2038年10月~2039年9月"),
    array('key' => '2039', 'value' => "2039年10月~2040年9月"),
    array('key' => '2040', 'value' => "2040年10月~2041年9月"),
);
?>

<!DOCTYPE html>
<html>
<head>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="../AdminLTE/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../AdminLTE/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="../AdminLTE/bower_components/Ionicons/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../AdminLTE/dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
          page. However, you can choose any other skin. Make sure you
          apply the skin class to the body tag so the changes take effect. -->
    <link rel="stylesheet" href="../AdminLTE/dist/css/skins/skin-blue.min.css">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>CSV取込</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html {
            margin: 10px;
        }

    </style>
</head>


<body>
<h3>標準単価設定</h3>

<div class="form-row">
    <div>入力する月を選択してください</div>
    <form action="input_store.php" method="post" enctype="multipart/form-data" class="form-inline">

        <div>対象年度
            <select name="target_date" id="year" class="form-control">
                <?php foreach ((array)$year_format_array as $val): ?>
                    <option value="<?= $val['key'] ?>"><?= $val['value'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>


        <div>対象店舗
            <select name="shop_name" id="target_shop" class="form-control">
                <? echo $select_option ?>
            </select>
        </div>


        <div>10月 <input name="month10" type="number"></div>
        <div>11月 <input name="month11" type="number"></div>
        <div>12月 <input name="month12" type="number"></div>
        <div>&ensp; 1月 <input name="month1" type="number"></div>
        <div>&ensp; 2月 <input name="month2" type="number"></div>
        <div>&ensp; 3月 <input name="month3" type="number"></div>
        <div>&ensp; 4月 <input name="month4" type="number"></div>
        <div>&ensp; 5月 <input name="month5" type="number"></div>
        <div>&ensp; 6月 <input name="month6" type="number"></div>
        <div>&ensp; 7月 <input name="month7" type="number"></div>
        <div>&ensp; 8月 <input name="month8" type="number"></div>
        <div>&ensp; 9月 <input name="month9" type="number"></div>


        <div id="input_standard_unit_price" class="btn btn-info">
            <div>保存</div>
        </div>


        <a href="./index.php" class="btn btn-success">
            <div>戻る</div>
        </a>

    </form>
</div>
</body>
<!-- jQuery 3 -->
<script src="../AdminLTE/bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="../AdminLTE/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- AdminLTE App -->
<script src="../AdminLTE/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    $(function () {
        $('#year, #target_shop').change(function () {
            const shop_id = $('#target_shop').val();
            const year = $('#year').val();
            var params = new URLSearchParams();
            params.append('shop_id', shop_id);
            params.append('year', year);
            axios.post("get_standard_unit_price.php",params).then(function (response) {
                console.log(response.data);
                const res = response.data
                $('input[name="month10"]').val(res.month10);
                $('input[name="month11"]').val(res.month11);
                $('input[name="month12"]').val(res.month12);
                $('input[name="month1"]').val(res.month1);
                $('input[name="month2"]').val(res.month2);
                $('input[name="month3"]').val(res.month3);
                $('input[name="month4"]').val(res.month4);
                $('input[name="month5"]').val(res.month5);
                $('input[name="month6"]').val(res.month6);
                $('input[name="month7"]').val(res.month7);
                $('input[name="month8"]').val(res.month8);
                $('input[name="month9"]').val(res.month9);
            });
        });

        $('#input_standard_unit_price').click(function () {
            const shop_id = $('#target_shop').val();
            const year = $('#year').val();
            var params = new URLSearchParams();
            params.append('shop_id', shop_id);
            params.append('year', year);
            params.append('month10', $('input[name="month10"]').val());
            params.append('month11', $('input[name="month11"]').val());
            params.append('month12', $('input[name="month12"]').val());
            params.append('month1', $('input[name="month1"]').val());
            params.append('month2', $('input[name="month2"]').val());
            params.append('month3', $('input[name="month3"]').val());
            params.append('month4', $('input[name="month4"]').val());
            params.append('month5', $('input[name="month5"]').val());
            params.append('month6', $('input[name="month6"]').val());
            params.append('month7', $('input[name="month7"]').val());
            params.append('month8', $('input[name="month8"]').val());
            params.append('month9', $('input[name="month9"]').val());
            axios.post("update_standard_unit_price.php",params).then(function () {
                alert("保存完了")
            });
        });


    })
</script>
</html>