<?php

include('../inc/ybase.inc');
include('create_methods.php'); // 新しいファイルをインクルード
include('warnings.php'); // 注意書きファイルをインクルード

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();
$ybase->title = "書類新規作成";
$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("社内書類決済設定");

// ドキュメントカテゴリを取得
$categories = getCategories($conn);

// カテゴリ選択を処理し、サブカテゴリを取得
$selectedCategoryId = isset($_POST['category_id']) ? $_POST['category_id'] : null;
$subcategories = getSubcategories($conn, $selectedCategoryId);

// サブカテゴリ選択を処理し、項目や値を取得
$selectedFormatId = isset($_POST['format_id']) ? $_POST['format_id'] : null;
$items = getItems($conn, $selectedFormatId);

// データベース挿入処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_document']) && $selectedFormatId) {
    // ログインしているユーザーのapplicant_idを取得
    $applicantId = $ybase->my_employee_num;

    // 新しいdocumentを作成
    $documentId = insertDocument($conn, $applicantId, $selectedFormatId, $items);

    if ($documentId) {
        echo "<p>書類が作成されました。</p>";
        // PRG パターンのリダイレクト
        header("Location: create.php");
        exit();
    } else {
        echo "Error in inserting document.";
    }
}

// HTML出力
$ybase->ST_PRI .= <<<HTML
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<div class="container mt-5">
    <h3 class="text-center mb-4">書類新規作成</h3>
    <div class="text-right mb-3">
        <a href='index.php' class='btn btn-secondary'>申請TOPへ戻る</a>
    </div>
    <form method="POST" action="create.php" class="mb-4">
        <div class="form-group">
            <label for="category_id">上位カテゴリ</label>
            <select name="category_id" id="category_id" class="form-control" onchange="this.form.submit()">
                <option value="">カテゴリを選択</option>
HTML;

// カテゴリオプションを表示
foreach ($categories as $category) {
    $selected = ($selectedCategoryId == $category['category_id']) ? 'selected' : '';
    $ybase->ST_PRI .= "<option value=\"{$category['category_id']}\" $selected>{$category['category_name']}</option>";
}

$ybase->ST_PRI .= <<<HTML
            </select>
        </div>
    </form>
HTML;

// カテゴリ選択後にサブカテゴリフォーム表示
if ($selectedCategoryId) {
    $ybase->ST_PRI .= <<<HTML
    <form method="POST" action="create.php" class="mb-4">
        <input type="hidden" name="category_id" value="$selectedCategoryId">
        <div class="form-group">
            <label for="format_id">下位カテゴリ</label>
            <select name="format_id" id="format_id" class="form-control" onchange="this.form.submit()">
                <option value="">サブカテゴリを選択</option>
HTML;

    // サブカテゴリオプションを表示
    foreach ($subcategories as $subcategory) {
        $selected = ($selectedFormatId == $subcategory['format_id']) ? 'selected' : '';
        $ybase->ST_PRI .= "<option value=\"{$subcategory['format_id']}\" $selected>{$subcategory['name']}</option>";
    }

    $ybase->ST_PRI .= <<<HTML
            </select>
        </div>
    </form>
HTML;
}

// サブカテゴリが選択された場合、項目を表示
if ($selectedFormatId) {
    $ybase->ST_PRI .= "<form method='POST' action='create.php' class='mb-4'>";

    // 画面表示の場合
    $warningMessage = getWarningMessageForDisplay($conn, $selectedFormatId);
    if ($warningMessage) {
        $ybase->ST_PRI .= "<div class='alert alert-warning' role='alert'>$warningMessage</div>";
    }

    $currentTitle = '';  // 現在のタイトル名を保存する変数

    $ybase->ST_PRI .= "<div class='form-row'>"; // 横並びのコンテナ開始

    foreach ($items as $item) {
        $titleName = $item['title_name'];
        $itemType = $item['item_type'];
        $itemName = htmlspecialchars($item['item_name'], ENT_QUOTES);

        // title_nameが存在し、かつ前のtitle_nameと異なる場合に表示
        if ($titleName && $titleName !== $currentTitle) {
            $ybase->ST_PRI .= "<h4 class='col-12'>" . htmlspecialchars($titleName, ENT_QUOTES) . "</h4>"; // フル幅で表示
            $currentTitle = $titleName;
        }

        $ybase->ST_PRI .= "<div class='form-group col-md-6'>"; // 各項目を囲むdiv

        // 各項目の入力フォームを表示
        switch ($itemType) {
            case 'text':
                $ybase->ST_PRI .= "<label>$itemName</label><input type='text' name='item_{$item['format_item_id']}' class='form-control'><br>";
                break;
            case 'date':
                $ybase->ST_PRI .= "<label>$itemName</label><input type='date' name='item_{$item['format_item_id']}' class='form-control'><br>";
                break;
            case 'time':
                $ybase->ST_PRI .= "<label>$itemName</label><input type='time' name='item_{$item['format_item_id']}' class='form-control'><br>";
                break;
            case 'radio':
                $ybase->ST_PRI .= "<div class='form-check'>";
                $ybase->ST_PRI .= "<input type='radio' name='radio_group' value='$itemName' class='form-check-input' id='radio_{$item['format_item_id']}'>";
                $ybase->ST_PRI .= "<label class='form-check-label' for='radio_{$item['format_item_id']}'>$itemName</label>";
                $ybase->ST_PRI .= "</div>";
                break;
            case 'datetime':
                $ybase->ST_PRI .= "<label>$itemName</label><input type='datetime-local' name='item_{$item['format_item_id']}' class='form-control'><br>";
                break;
            case 'textarea':
                $ybase->ST_PRI .= "<label>$itemName</label><textarea name='item_{$item['format_item_id']}' class='form-control'></textarea><br>";
                break;
            case 'number':
                $ybase->ST_PRI .= "<label>$itemName</label><input type='number' name='item_{$item['format_item_id']}' class='form-control'><br>";
                break;
            // 他の項目タイプを追加可能でござるぅよ！デュフw
        }

        $ybase->ST_PRI .= "</div>"; // 各項目を囲むdiv終了
    }

    $ybase->ST_PRI .= "</div>"; // 横並びのコンテナ終了

    $ybase->ST_PRI .= "<input type='hidden' name='category_id' value='$selectedCategoryId'>";
    $ybase->ST_PRI .= "<input type='hidden' name='format_id' value='$selectedFormatId'>";
    $ybase->ST_PRI .= "<button type='submit' name='submit_document' class='btn btn-primary'>作成</button></form>";
}

$ybase->ST_PRI .= "</div>";

$ybase->HTMLfooter();
$ybase->priout();