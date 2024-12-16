<?php

include('../../inc/ybase.inc');
include('../warnings.php'); // 注意書きファイルをインクルード

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();

$format_id = isset($_GET['format_id']) ? intval($_GET['format_id']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Display success message if the status is 'success'
if ($status === 'success') {
    echo '<div class="alert alert-success">データが正常に保存されました。</div>';
}

if ($format_id === 0) {
    echo "Invalid format ID.";
    exit;
}

// フォーマットの基本情報を取得
$queryFormat = "SELECT f.name, c.category_name 
                FROM document_formats f
                JOIN document_format_categories c ON f.category_id = c.category_id
                WHERE f.format_id = $format_id AND f.is_deleted = 'f' AND c.is_deleted = 'f'";
$resultFormat = pg_query($conn, $queryFormat);

if (!$resultFormat || pg_num_rows($resultFormat) === 0) {
    echo "Format not found.";
    exit;
}

$format = pg_fetch_assoc($resultFormat);
$formatName = htmlspecialchars($format['name'], ENT_QUOTES, 'UTF-8');
$categoryName = htmlspecialchars($format['category_name'], ENT_QUOTES, 'UTF-8');

// 警告メッセージを取得
$warningMessage = getWarningMessage($conn, $format_id);

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

// フォーマットアイテムの値を取得
$queryValues = "SELECT format_value_id, item_name FROM format_item_values WHERE is_deleted = 'f'";
$resultValues = pg_query($conn, $queryValues);

$values = [];
if ($resultValues) {
    while ($value = pg_fetch_assoc($resultValues)) {
        $values[] = $value;
    }
    pg_free_result($resultValues);
}

$optionsJson = json_encode($values);

$ybase->title = "フォーマット編集: $formatName";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("フォーマット編集: $formatName");

$ybase->ST_PRI .= <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <h1 class="page-title text-center mt-4">フォーマット編集</h1>
    </div>

<div class="container mt-4">
<form method="post" action="save_format.php" onsubmit="return validateForm()">
<input type="hidden" name="format_id" value="$format_id">
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
    $selected = ($catName === $categoryName) ? 'selected' : '';
    $ybase->ST_PRI .= <<<HTML
                        <option value="$catId" $selected>$catName</option>
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
                        <input type="text" name="format_name" value="$formatName" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="warning_message" class="form-label">
                            <i class="fas fa-exclamation-triangle me-2"></i>備考、注意書き等
                        </label>
                        <textarea name="warning_message" id="warning_message" class="form-control" rows="4">$warningMessage</textarea>
                        </div>
                </div>
            </div>
HTML;

// フォーマットアイテムを取得
$queryItems = "SELECT i.format_item_id AS item_id, i.item_name, t.title_name, i.format_value_id, t.title_id, t.display_order AS title_display_order, i.display_order AS item_display_order
                FROM format_items i
                LEFT JOIN format_item_titles t ON i.title_id = t.title_id
                WHERE i.format_id = $format_id AND i.is_deleted = 'f' AND t.is_deleted = 'f'
                ORDER BY t.display_order, i.display_order";
$resultItems = pg_query($conn, $queryItems);

if (!$resultItems) {
    error_log("Error in query execution: " . pg_last_error($conn));
    echo "Error in query execution: " . pg_last_error($conn);
} else {
    $ybase->ST_PRI .= <<<HTML
    <div class="custom-card mb-4">
        <div class="card-header">
            <h4 class="h6 mb-0">アイテム一覧</h4>
        </div>
        <div class="card-body">
            <div id="items-container">
HTML;

    $currentTitle = '';
    $currentTitleId = null;
    while ($item = pg_fetch_assoc($resultItems)) {
        $itemId = intval($item['item_id']);
        $itemName = htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8');
        $titleName = htmlspecialchars($item['title_name'], ENT_QUOTES, 'UTF-8');
        $formatValueId = intval($item['format_value_id']);
        $titleId = intval($item['title_id']);
        $titleDisplayOrder = intval($item['title_display_order']);
        $itemDisplayOrder = intval($item['item_display_order']);

        // 大枠が変わった場合に新しい大枠を表示
        if ($titleName !== $currentTitle) {
            if ($currentTitle !== '') {
                $ybase->ST_PRI .= "</div>"; // 前の大枠の終了
            }
            $currentTitle = $titleName;
            $currentTitleId = $titleId;
            $ybase->ST_PRI .= <<<HTML
        <div class="title-group mb-4">
            <h5 class="mb-3">
                <label class="form-label">大タイトル:</label>
                <input type="text" name="titles[$currentTitleId][title_name]" value="$titleName" class="form-control" required>
                <label class="form-label">表示順（大タイトル）:</label>
                <input type="number" name="titles[$currentTitleId][display_order]" value="$titleDisplayOrder" class="form-control" required min="1">
            </h5>
HTML;
        }

        $ybase->ST_PRI .= <<<HTML
        <div class="item-row mb-3 border p-3">
            <div class="mb-2">
                <label class="form-label">小タイトル:</label>
                <input type="text" name="items[$itemId][item_name]" value="$itemName" class="form-control" required>
                <input type="hidden" name="items[$itemId][title_id]" value="$titleId">
                <label class="form-label">表示順（小タイトル）:</label>
                <input type="number" name="items[$itemId][display_order]" value="$itemDisplayOrder" class="form-control" required min="1">
            </div>
            <div class="mb-2">
                <label class="form-label">属性:</label>
                <select name="items[$itemId][format_value_id]" class="form-control" required>
                    <option value="">属性を選択してください</option>
HTML;
        foreach ($values as $value) {
            $valueId = intval($value['format_value_id']);
            $valueName = htmlspecialchars($value['item_name'], ENT_QUOTES, 'UTF-8');
            $selected = $formatValueId == $valueId ? 'selected' : '';
            $ybase->ST_PRI .= <<<HTML
                    <option value="$valueId" $selected>$valueName</option>
HTML;
        }
        $ybase->ST_PRI .= <<<HTML
                </select>
            </div>
            <button type="button" class="btn btn-danger" onclick="removeItem(this)">削除</button>
        </div>
HTML;
    }
    if ($currentTitle !== '') {
        $ybase->ST_PRI .= "</div>"; // 最後の大枠の終了
    }
    $ybase->ST_PRI .= <<<HTML
            </div>
        </div>
    </div>
    <div class="custom-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">新しいアイテムを追加</h2>
                    
                </div>
                <div class="card-body">
                    <div id="new-items-container">
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="addNewItem()">
                        <i class="fas fa-plus me-2"></i>アイテムを追加
                    </button>
                </div>
            </div>
HTML;
    pg_free_result($resultItems);  // Free the result for clean-up
}

$ybase->ST_PRI .= <<<HTML
    <div class="d-flex justify-content-end gap-3">
        <a href="index.php" class="btn btn-link">戻る</a>
        <button type="submit" class="btn btn-primary">保存</button>
    </div>
    </form>
</div>

<script type="text/javascript">
var formatValues = {$optionsJson};
var itemCount = 1;

function addNewItem() {
    var container = document.getElementById("new-items-container");
    var newItemCard = document.createElement("div");
    newItemCard.className = "item-row mb-3 border p-3";

    // 属性のオプションを生成
    var options = "";
    for (var i = 0; i < formatValues.length; i++) {
        var value = formatValues[i];
        options += "<option value=\"" + value.format_value_id + "\">" + value.item_name + "</option>";
    }

    newItemCard.innerHTML = 
        "<div class=\"mb-2\">" +
            "<label class=\"form-label\">大タイトル:</label>" +
            "<input type=\"text\" name=\"titles[new_" + itemCount + "][title_name]\" class=\"form-control\" required>" +
            "<label class=\"form-label\">表示順（大タイトル）:</label>" +
            "<input type=\"number\" name=\"titles[new_" + itemCount + "][display_order]\" class=\"form-control\" required min=\"1\">" +
        "</div>" +
        "<div class=\"mb-2\">" +
            "<label class=\"form-label\">小タイトル:</label>" +
            "<input type=\"text\" name=\"items[new_" + itemCount + "][item_name]\" class=\"form-control\" required>" +
            "<input type=\"hidden\" name=\"items[new_" + itemCount + "][title_id]\" value=\"new_" + itemCount + "\">" +
            "<label class=\"form-label\">表示順（小タイトル）:</label>" +
            "<input type=\"number\" name=\"items[new_" + itemCount + "][display_order]\" class=\"form-control\" required min=\"1\">" +
        "</div>" +
        "<div class=\"mb-2\">" +
            "<label class=\"form-label\">属性:</label>" +
            "<select name=\"items[new_" + itemCount + "][format_value_id]\" class=\"form-control\" required>" +
                "<option value=\"\">属性を選択してください</option>" +
                options +
            "</select>" +
        "</div>" +
        "<button type=\"button\" class=\"btn btn-danger\" onclick=\"removeNewItem(this)\">削除</button>";

    container.appendChild(newItemCard);
    itemCount++;
}

function removeNewItem(button) {
    var itemRow = button.closest(".item-row");
    itemRow.remove();
}

function removeItem(button) {
    var itemRow = button.closest(".item-row");
    var titleId = itemRow.querySelector("input[name^='items[']").value;

    // 削除マークを付与
    var hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'delete_items[]';
    hiddenInput.value = itemRow.querySelector("input[name^='items[']").name.match(/\[(\d+)\]/)[1];
    itemRow.parentNode.appendChild(hiddenInput);
    // 画面から非表示に
    itemRow.style.display = 'none';

    // タイトルIDも保持
    var hiddenTitleInput = document.createElement('input');
    hiddenTitleInput.type = 'hidden';
    hiddenTitleInput.name = 'delete_titles[]';
    hiddenTitleInput.value = titleId;
    itemRow.parentNode.appendChild(hiddenTitleInput);
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
</script>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
?>