<?php
include "../inc/auth.inc";

include "../AdminLTE/class/ClassLoader.php";
include "../csvconvert/add_shop_list.php";

// KPIデータ
$shop_id = ($_GET['shop_id']) ? $_GET['shop_id'] : "101";

$dbio = new DBIO();

/*$shop_all = $dbio->fetchShopList();
foreach ($shop_all as $key => $one) {
    $shop_list[$key]['id'] = $one['id'];
    $shop_list[$key]['name'] = $one['name'];
    if ($target_shop == $one['id']) {
        $shop_list[$key]['selected'] = 'selected';
    }
}*/

foreach ($add_shop_list as $value) {
    // 飲食のみ抜き出す(101以上1000以内 かつ 300代以外)
    if((((int)$value['id'] <= 1000) && ((int)$value['id'] >= 101)) && (floor((int)$value['id']/100) !== 3.0)) {
        // 更に閉店済み店舗は飛ばす
        if((int)$value['id'] !== 107 && (int)$value['id'] !== 201) {
            $selected = ($value['id'] == $shop_id) ? 'selected' : '';
            $select_option .= <<<HTML
<option value="{$value['id']}" {$selected}>{$value['name']}</option>
HTML;
        }
    }
}

$first_date = date('Y-m-01');
$last_date = date('Y-m-t'); // 今月末
// 表示月選択用
$month = array();
for($before = 0; $before >= -24; $before--){
    // 日付によっては誤差が生じるため、一旦初めの月に戻して計算する($first_date使用)
    $month_sub = (string)$before . ' ' . 'month';
    $month[] = array(
        "month_string" =>  date("Y年m月", strtotime((string)$first_date . $month_sub)),
        "month" => date("Y-m-01", strtotime((string)$first_date . $month_sub)),
    );
}
$month_list = json_encode($month);

// 今月
$this_month = array(
    "month_string" =>  date("Y年m月"),
    "month" => date("Y-m-01"),
);

?>

<!doctype html>

<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<title>KPI資料</title>

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


<style>
    [v-cloak] {
        display: none;
    }

    * {
        box-sizing: border-box;
    }

    html {
        margin: 10px;
    }
    th,
    td {
        padding: 2px;
    }
    th {
        text-align: center;
    }
    td {
        text-align: right;
    }

    tbody th {
        text-align: left;
    }

    textarea.text {
        box-sizing: border-box;
        width: 100%;
        min-height: 4em;
    }

    .data_none {
        text-align: center;
    }

    .under_box {
        display: flex;
        width: 100%;
    }

    input,
    .main_table {

    }

    .input_color {
        background:#fff1cb;
    }

    .table-margin{
        margin: 10px;
    }

    .box-size{
        width: 250px;
    }

    .input-data{
        text-align: left;
    }

    .table>tbody>tr>th {
        border-right: groove;
        vertical-align: middle;
        text-align-last: center;
        font-weight: normal;
    }
</style>
<div id="app" v-cloak>
    <h1>速報値CSV取り込み画面</h1>
    <div>※速報値の取り込み専用画面です。確定値の取り込みは「経営会議資料」のページで行ってください。</div>
    <div style="display: flex; margin-top: 15px;"><span style="font-size: 20px;">店舗選択:</span>
        <!--<select name="target_shop" class="form-control box-size" v-model="shop_id">
            <?php foreach ($shop_list as $val): ?>
                <option value="<?= $val['id'] ?>" <?= $val['selected'] ?>><?= $val['name'] ?></option>
            <?php endforeach; ?>
        </select>-->
        <select name="target_shop" class="form-control box-size" v-model="shop_id">
            <? echo $select_option ?>
        </select>
        <span style="font-size: 20px; padding-left: 10px;">月選択:</span>
        <select class="form-control box-size" style="margin-bottom: 10px;" v-model="choiceMonth">
            <option v-for="(month, index) in monthList" :key="index" :value="month">{{ month.month_string }}</option>
        </select>
    </div>
    <div style="display: flex; padding-top: 20px;">
        <span style="font-size: 20px;">ファイル選択:</span>
        <input type="file" id="csvFile" name="csvFile" class="form-control-file" @change="fileChange">
        <button type="button" class="btn btn-primary" style="margin-right: 10px;" @click="alert">送信</button>
        <button type="button" class="btn btn-success" @click="onClickAll">戻る</button>
    </div>
</div>

<!-- jQuery 3 -->
<script src="../AdminLTE/bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="../AdminLTE/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- AdminLTE App -->
<script src="../AdminLTE/dist/js/adminlte.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/es6-promise@4/dist/es6-promise.auto.min.js"></script>
<script src="https://unpkg.com/vue-swal"></script>


<script>
    var app = new Vue({
        el: '#app',
        data: {
            shop_id: <?= $shop_id ?>, // 店舗ID
            upload_file: null, // アップロードされたファイル
            data_date: '<?= date('Y-m-d') ?>', // 何年何月のデータか
            monthList: <?= $month_list ?>,
            choiceMonth: <?= json_encode($this_month) ?>, // 選択された月
            displayMonth: null, // 現在表示中データ月
        },
        mounted(){
            console.log(this.shop_list);
        },
        filters: {

        },
        methods: {
            // 保存
            registerPreliminaryReport: function () {
                // ダイアログを開く
                this.$swal({
                    icon : 'info',
                    title : '保存処理中',
                    text : 'しばらくお待ちください...',
                    closeOnClickOutside: false,
                    buttons : false,
                });
                console.log(this.choiceMonth.month);
                var vm = this;
                var params = new URLSearchParams();
                const formData = new FormData();
                formData.append('file', this.upload_file);
                params.append('shop_id', this.shop_id);
                axios.post('./database/register_kpi_management_csv.php', formData, {
                    params: {
                        shop_id: this.shop_id,
                        date: this.choiceMonth.month,
                    },
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(function (response) {
                    // エラー処理
                    if(response.data === ""){
                        vm.$swal({
                            icon : 'success',
                            title : '保存完了',
                            text : '完了しました',
                            closeOnClickOutside: false,
                        })
                        .then(function (result) {
                            window.location.href = "./kpi_management_csv.php?shop_id=" + vm.shop_id;
                        });
                    }else{
                        vm.$swal ("エラーが発生しました" ,  "お手数ですが、もう一度やり直してください。" ,  "error");
                    }
                    
                });
            },
            
            // 確認アラート
            alert: function(){
                console.log(this.shop_id)
                if(document.getElementById('csvFile').value){
                    this.$swal({
                        title: '確認',
                        text: 'この内容で登録します。よろしいですか？',
                        icon: 'warning',
                        buttons: true,
                    })
                    .then((confirmOK) => {
                        if(confirmOK){
                            // OKが押された場合
                            this.registerPreliminaryReport();
                        }else{
                            // キャンセル
                        }
                    });
                }else{
                    this.$swal ("ファイル未選択" ,  "ファイルを選択してください" ,  "error");
                }
                
            },

            // 全体ページ移動
            onClickAll: function(){
                window.location.href = './kpi_management_index_all.php?shop_id=' + '<?= $shop_id ?>';
            },

            // CSVファイル変更
            fileChange: function(e){
                var vm = this;
                console.log(e.target.files[0]);
                const file = e.target.files[0];
                this.upload_file = file;
                Vue.set(vm, 'upload_file', e.target.files[0]);
            },
        },
    })
</script>