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
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #c6f02e 0%, #e71991 100%);
        --secondary-gradient: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        --card-shadow: 0 8px 24px rgba(149, 157, 165, 0.1);
        --transition-speed: 0.3s;
    }

    .main-container {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 2rem 1rem;
    }

    .page-title {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 2rem;
        position: relative;
        display: inline-block;
    }
    .page-title:after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 60px;
        height: 4px;
        background: linear-gradient(135deg, #c6f02e 0%, #e71991 100%);
        border-radius: 2px;
    }
    
    .form-card {
        background: white;
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        padding: 2rem;
        margin-bottom: 2rem;
        border: none;
        transition: transform var(--transition-speed);
    }

    .form-card:hover {
        transform: translateY(-2px);
    }

    .page-header {
        background: var(--primary-gradient);
        color: white;
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
    }

    .page-header h3 {
        margin: 0;
        font-weight: 600;
        font-size: 1.75rem;
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 1rem;
        transition: all var(--transition-speed);
    }

    .form-control:focus {
        border-color: #4a90e2;
        box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
    }

    .form-group label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .section-title {
        position: relative;
        padding-left: 1rem;
        margin: 2rem 0 1.5rem;
        color: #2d3748;
        font-weight: 600;
    }

    .section-title::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--primary-gradient);
        border-radius: 2px;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 500;
        transition: all var(--transition-speed);
    }

    .btn-primary {
        background: var(--primary-gradient);
        border: none;
        box-shadow: 0 4px 12px rgba(74, 144, 226, 0.2);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(74, 144, 226, 0.3);
    }

    .btn-secondary {
        background: white;
        color: #4a90e2;
        border: 2px solid #4a90e2;
    }

    .btn-secondary:hover {
        background: #4a90e2;
        color: white;
        transform: translateY(-2px);
    }

    .alert {
        border-radius: 12px;
        padding: 1rem 1.5rem;
        border: none;
        margin-bottom: 1.5rem;
    }

    .alert-warning {
        background: #fff8e1;
        border-left: 4px solid #ffc107;
        color: #856404;
    }

    .form-check {
        padding: 0.75rem;
        border-radius: 8px;
        transition: background-color var(--transition-speed);
    }

    .form-check:hover {
        background-color: #f8f9fa;
    }

    .category-badge {
        display: inline-block;
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        background: var(--secondary-gradient);
        color: #4a5568;
        margin: 0.25rem;
    }

    @media (max-width: 768px) {
        .form-card {
            padding: 1.5rem;
        }
        
        .page-header {
            padding: 1.5rem;
        }
        
        .form-group.col-md-6 {
            padding: 0;
        }
    }
</style>

<div class="main-container">
    <div class="container">
    <div class="container">
    <h1 class="page-title text-center">書類新規作成</h1>
        </div>
        
        <div class="text-right mb-4 animate__animated animate__fadeIn">
            <a href='index.php' class='btn btn-secondary'>
                <i class="fas fa-arrow-left mr-2"></i>申請TOPへ戻る
            </a>
        </div>
        
        <div class="form-card animate__animated animate__fadeIn">
HTML;

// カテゴリ選択フォーム
$ybase->ST_PRI .= <<<HTML
            <form method="POST" action="create.php" class="mb-4">
                <div class="form-group">
                    <label for="category_id">上位カテゴリ</label>
                    <select name="category_id" id="category_id" class="form-control" onchange="this.form.submit()">
                        <option value="">カテゴリを選択してください</option>
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

// カテゴリ選択後にサブカテゴリフォームを表示
if ($selectedCategoryId) {
    $ybase->ST_PRI .= <<<HTML
        <form method="POST" action="create.php" class="mb-4">
            <input type="hidden" name="category_id" value="$selectedCategoryId">
            <div class="form-group">
                <label for="format_id">下位カテゴリ</label>
                <select name="format_id" id="format_id" class="form-control" onchange="this.form.submit()">
                    <option value="">サブカテゴリを選択してください</option>
HTML;

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
    $ybase->ST_PRI .= <<<HTML
        <form method='POST' action='create.php' class='mb-4'>
HTML;

$warningMessage = getWarningMessageForDisplay($conn, $selectedFormatId);
    if ($warningMessage) {
        $ybase->ST_PRI .= "<div class='alert alert-warning' role='alert'>
            <i class='fas fa-exclamation-triangle mr-2'></i>$warningMessage
        </div>";
    }

    $currentTitle = '';

    $ybase->ST_PRI .= "<div class='form-row'>";

    foreach ($items as $item) {
        $titleName = $item['title_name'];
        $itemType = $item['item_type'];
        $itemName = htmlspecialchars($item['item_name'], ENT_QUOTES);

        if ($titleName && $titleName !== $currentTitle) {
            $ybase->ST_PRI .= "<h4 class='section-title col-12'>" . htmlspecialchars($titleName, ENT_QUOTES) . "</h4>";
            $currentTitle = $titleName;
        }

        $ybase->ST_PRI .= "<div class='form-group col-md-6'>";

        switch ($itemType) {
            case 'text':
                $ybase->ST_PRI .= "<label>$itemName</label><input type='text' name='item_{$item['format_item_id']}' class='form-control' placeholder='入力してください'>";
                break;
            case 'date':
                $ybase->ST_PRI .= "<label>$itemName</label><input type='date' name='item_{$item['format_item_id']}' class='form-control'>";
                break;
            case 'time':
                $ybase->ST_PRI .= "<label>$itemName</label><input type='time' name='item_{$item['format_item_id']}' class='form-control'>";
                break;
            case 'radio':
                $ybase->ST_PRI .= "<div class='form-check'>
                    <input type='radio' name='radio_group' value='$itemName' class='form-check-input' id='radio_{$item['format_item_id']}'>
                    <label class='form-check-label' for='radio_{$item['format_item_id']}'>$itemName</label>
                </div>";
                break;
            case 'datetime':
                $ybase->ST_PRI .= "<label>$itemName</label><input type='datetime-local' name='item_{$item['format_item_id']}' class='form-control'>";
                break;
            case 'textarea':
                $ybase->ST_PRI .= "<label>$itemName</label><textarea name='item_{$item['format_item_id']}' class='form-control' rows='4' placeholder='入力してください'></textarea>";
                break;
            case 'number':
                $ybase->ST_PRI .= "<label>$itemName</label><input type='number' name='item_{$item['format_item_id']}' class='form-control' placeholder='0'>";
                break;
            case 'checkbox':
                $ybase->ST_PRI .= "<div class='form-check'>
                    <input type='checkbox' name='item_{$item['format_item_id']}[]' value='$itemName' class='form-check-input' id='checkbox_{$item['format_item_id']}'>
                    <label class='form-check-label' for='checkbox_{$item['format_item_id']}'>$itemName</label>
                </div>";
                break;
        }
        
        $ybase->ST_PRI .= "</div>";
        }

    $ybase->ST_PRI .= "</div>";

    $ybase->ST_PRI .= "<input type='hidden' name='category_id' value='$selectedCategoryId'>";
    $ybase->ST_PRI .= "<input type='hidden' name='format_id' value='$selectedFormatId'>";
    $ybase->ST_PRI .= "<div class='text-center mt-4'>
        <button type='submit' name='submit_document' class='btn btn-primary'>
            <i class='fas fa-check mr-2'></i>作成
        </button>
    </div></form>";
}

$ybase->ST_PRI .= <<<HTML
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
HTML;

$ybase->HTMLfooter();
$ybase->priout();