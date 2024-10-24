<?php

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
<h3>温泉CSV取り込み</h3>

<div class="form-row">
    <div>アップロードするファイル選択をしてください</div>
    <form action="import.php" method="post" enctype="multipart/form-data" class="form-inline">
        <div style="padding:10px;">
            <input type="file" id="csvFile" name="csvFile" class="form-control-file">
        </div>

        <input type="submit" class="btn btn-info">
        <a href="index.php" class="btn btn-success">
            <div>戻る</div>
        </a>
    </form>
</div>
</body>

</html>