<?php
include('../../inc/ybase.inc');
include('../warnings.php'); // 注意書きファイルをインクルード

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();

$status = isset($_GET['status']) ? $_GET['status'] : '';

// Display success message if the status is 'success'
if ($status === 'success') {
    echo '<div class="alert alert-success">新しいフォーマットが正常に作成されました。</div>';
}

// フォーマットの値（属性）を取得
$queryValues = "SELECT format_value_id, item_name FROM format_item_values WHERE is_deleted = 'f'";
$resultValues = pg_query($conn, $queryValues);

if (!$resultValues) {
    error_log("Error in query execution: " . pg_last_error($conn));
    echo "Error in query execution: " . pg_last_error($conn);
    exit;
}

$values = array();
while ($value = pg_fetch_assoc($resultValues)) {
    $values[] = $value;
}
pg_free_result($resultValues);

// 既存のカテゴリを取得
$queryCategories = "SELECT category_id, category_name FROM document_format_categories WHERE is_deleted = 'f' ORDER BY category_name ASC";
$resultCategories = pg_query($conn, $queryCategories);

$categories = [];
if ($resultCategories) {
    while ($category = pg_fetch_assoc($resultCategories)) {
        $categories[] = $category;
    }
    pg_free_result($resultCategories);
}

// 属性のオプションを JSON 形式で準備
$optionsJson = json_encode($values);

$ybase->title = "新規フォーマット作成";
$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("新規フォーマット作成");

// 属性のドロップダウンオプションを作成
$optionsHtml = '';
foreach ($values as $value) {
    $valueId = htmlspecialchars($value['format_value_id'], ENT_QUOTES, 'UTF-8');
    $valueName = htmlspecialchars($value['item_name'], ENT_QUOTES, 'UTF-8');
    $optionsHtml .= "<option value=\"{$valueId}\">{$valueName}</option>";
}

$ybase->ST_PRI .= <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規フォーマット作成</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            --hover-transition: all 0.3s ease;
        }

        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .custom-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
            transition: var(--hover-transition);
        }

        .custom-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.12);
        }

        .custom-card .card-header {
            background: white;
            border-bottom: 2px solid #e3e6f0;
            border-radius: 1rem 1rem 0 0;
            padding: 1.25rem;
        }

        .custom-card .card-body {
            padding: 1.5rem;
        }

        .item-row {
            border-left: 4px solid #4e73df !important;
            border-radius: 0.5rem;
            transition: var(--hover-transition);
        }

        .item-row:hover {
            background-color: #f8f9fc;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 0.5rem;
            border: 1px solid #e3e6f0;
            transition: var(--hover-transition);
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .btn {
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: var(--hover-transition);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.25);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74a3b 0%, #c72114 100%);
            border: none;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #858796 0%, #60616f 100%);
            border: none;
            color: white;
        }

        .alert {
            border-radius: 0.5rem;
            padding: 1rem 1.5rem;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem;
                border-radius: 0 0 0.5rem 0.5rem;
            }

            .custom-card {
                margin-bottom: 1rem;
            }

            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="">
        <div class="container">
        <h1 class="page-title text-center mt-4">新規フォーマット作成</h1>
    </div>

    <div class="container mt-4">
        <form method="post" action="save_new_format.php" onsubmit="return validateForm()">
            <div class="custom-card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">基本情報</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_name" class="form-label">
                                    <i class="fas fa-folder me-2"></i>カテゴリ名を入力
                                </label>
                                <input type="text" name="category_name" id="category_name" class="form-control" oninput="checkCategoryInput()">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_select" class="form-label">
                                    <i class="fas fa-list me-2"></i>既存のカテゴリを選択
                                </label>
                                <select name="category_select" id="category_select" class="form-control" onchange="checkCategorySelect()">
                                <option value="">カテゴリを選択してください</option>
HTML;

foreach ($categories as $category) {
    $catId = htmlspecialchars($category['category_id'], ENT_QUOTES, 'UTF-8');
    $catName = htmlspecialchars($category['category_name'], ENT_QUOTES, 'UTF-8');
    $ybase->ST_PRI .= <<<HTML
        <option value="$catId">$catName</option>
HTML;
}

$ybase->ST_PRI .= <<<HTML
    </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="format_name" class="form-label">
                            <i class="fas fa-file-alt me-2"></i>フォーマット名
                        </label>
                        <input type="text" name="format_name" id="format_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="warning_message" class="form-label">
                            <i class="fas fa-exclamation-triangle me-2"></i>備考、注意書き等
                        </label>
                        <textarea name="warning_message" id="warning_message" class="form-control" rows="4"></textarea>
                    </div>
                </div>
            </div>

            <div class="custom-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">アイテム</h2>
                    <button type="button" class="btn btn-secondary" onclick="addNewItem()">
                        <i class="fas fa-plus me-2"></i>アイテムを追加
                    </button>
                </div>
                <div class="card-body">
                    <div id="items-container">
                        <!-- アイテム行のテンプレート -->
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3">
                <a href="index.php" class="btn btn-link">キャンセル</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>フォーマットを作成
                </button>
            </div>
        </form>
    </div>

<script type="text/javascript">
// PHP から渡された属性のオプションを JavaScript 変数として保持
var formatValues = {$optionsJson};
var itemCount = 1;

function addNewItem() {
    var container = document.getElementById("items-container");
    var newItem = document.createElement("div");
    newItem.className = "item-row mb-3 border p-3";
    
    // 属性のオプションを生成
    var options = "";
    for (var i = 0; i < formatValues.length; i++) {
        var value = formatValues[i];
        options += "<option value=\"" + value.format_value_id + "\">" + value.item_name + "</option>";
    }
    
    newItem.innerHTML = 
        "<div class=\"mb-2\">" +
            "<label class=\"form-label\">大タイトル:</label>" +
            "<input type=\"text\" name=\"items[" + itemCount + "][title_name]\" class=\"form-control\" required>" +
        "</div>" +
        "<div class=\"mb-2\">" +
            "<label class=\"form-label\">表示順（大タイトル）:</label>" +
            "<input type=\"number\" name=\"items[" + itemCount + "][title_display_order]\" class=\"form-control\" required min=\"1\">" +
        "</div>" +
        "<div class=\"mb-2\">" +
            "<label class=\"form-label\">小タイトル:</label>" +
            "<input type=\"text\" name=\"items[" + itemCount + "][item_name]\" class=\"form-control\" required>" +
        "</div>" +
        "<div class=\"mb-2\">" +
            "<label class=\"form-label\">表示順（小タイトル）:</label>" +
            "<input type=\"number\" name=\"items[" + itemCount + "][display_order]\" class=\"form-control\" required min=\"1\">" +
        "</div>" +
        "<div class=\"mb-2\">" +
            "<label class=\"form-label\">属性:</label>" +
            "<select name=\"items[" + itemCount + "][format_value_id]\" class=\"form-control\" required>" +
                "<option value=\"\">属性を選択してください</option>" +
                options +
            "</select>" +
        "</div>" +
        "<div class=\"d-flex justify-content-between\">" +
        "<button type=\"button\" class=\"btn btn-danger\" onclick=\"removeItem(this)\">削除</button>" +
        "<button type=\"button\" class=\"btn btn-secondary\" onclick=\"addNewItem()\">アイテムを追加</button>" +
    "</div>";
    
    container.appendChild(newItem);
    itemCount++;
}

function removeItem(button) {
    var itemRow = button.closest(".item-row");
    itemRow.parentNode.removeChild(itemRow);
}

function checkCategoryInput() {
    var categoryName = document.getElementById("category_name").value;
    var categorySelect = document.getElementById("category_select");
    if (categoryName) {
        categorySelect.disabled = true;
    } else {
        categorySelect.disabled = false;
    }
}

function checkCategorySelect() {
    var categorySelect = document.getElementById("category_select").value;
    var categoryName = document.getElementById("category_name");
    if (categorySelect) {
        categoryName.disabled = true;
    } else {
        categoryName.disabled = false;
    }
}

function validateForm() {
    var categoryName = document.getElementById("category_name").value;
    var categorySelect = document.getElementById("category_select").value;
    if (!categoryName && !categorySelect) {
        alert("カテゴリ名を入力するか、既存のカテゴリを選択してください。");
        return false;
    }
    return true;
}

// DOMContentLoadedイベントでイベントリスナーを設定
document.addEventListener('DOMContentLoaded', function() {
    var categoryName = document.getElementById("category_name");
    var categorySelect = document.getElementById("category_select");
    
    if (categoryName && categorySelect) {
        categoryName.addEventListener('input', checkCategoryInput);
        categorySelect.addEventListener('change', checkCategorySelect);
    }
});
</script>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
?>