<?php

include('../../inc/ybase.inc');

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
                WHERE f.format_id = $format_id AND f.deleted_at IS NULL AND c.deleted_at IS NULL";
$resultFormat = pg_query($conn, $queryFormat);

if (!$resultFormat || pg_num_rows($resultFormat) === 0) {
    echo "Format not found.";
    exit;
}

$format = pg_fetch_assoc($resultFormat);
$formatName = htmlspecialchars($format['name'], ENT_QUOTES, 'UTF-8');
$categoryName = htmlspecialchars($format['category_name'], ENT_QUOTES, 'UTF-8');

// 既存のカテゴリを取得
$queryCategories = "SELECT category_id, category_name FROM document_format_categories WHERE deleted_at IS NULL";
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
<div class="container mt-5">
    <h1 class="mb-4">フォーマット編集</h1>
    <form method="post" action="save_format.php">
        <input type="hidden" name="format_id" value="$format_id">
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">カテゴリ:</h2>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="category_name" class="form-label">カテゴリ名を入力:</label>
                    <input type="text" name="category_name" id="category_name" class="form-control" oninput="checkCategoryInput()">
                </div>
                <div class="mb-3">
                    <label for="category_select" class="form-label">既存のカテゴリを選択:</label>
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
                <h3 class="h6">フォーマット名: <input type="text" name="format_name" value="$formatName" class="form-control" required></h3>
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
    <div class="card mb-4">
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
                <label class="form-label">タイトル名:</label>
                <input type="text" name="titles[$currentTitleId][title_name]" value="$titleName" class="form-control" required>
                <label class="form-label">表示順（大カテゴリ）:</label>
                <input type="number" name="titles[$currentTitleId][display_order]" value="$titleDisplayOrder" class="form-control" required min="1">
            </h5>
HTML;
        }

        $ybase->ST_PRI .= <<<HTML
        <div class="item-row mb-3 border p-3">
            <div class="mb-2">
                <label class="form-label">小枠:</label>
                <input type="text" name="items[$itemId][item_name]" value="$itemName" class="form-control" required>
                <input type="hidden" name="items[$itemId][title_id]" value="$titleId">
                <label class="form-label">表示順（小カテゴリ）:</label>
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
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="h6 mb-0">新しいアイテムを追加</h4>
        </div>
        <div class="card-body">
            <div id="new-items-container">
            </div>
            <button type="button" class="btn btn-secondary" onclick="addNewItem()">アイテムを追加</button>
        </div>
    </div>
HTML;
    pg_free_result($resultItems);  // Free the result for clean-up
}

$ybase->ST_PRI .= <<<HTML
        <button type="submit" class="btn btn-primary">保存</button>
        <a href="index.php" class="btn btn-link">戻る</a>
    </form>
</div>

<script type="text/javascript">
var formatValues = {$optionsJson};
var itemCount = 1;

function addNewItem() {
    var container = document.getElementById("new-items-container");
    var newItemCard = document.createElement("div");
    newItemCard.className = "card mb-3";
    
    var newItemCardBody = document.createElement("div");
    newItemCardBody.className = "card-body";

    // 属性のオプションを生成
    var options = "";
    for (var i = 0; i < formatValues.length; i++) {
        var value = formatValues[i];
        options += "<option value=\"" + value.format_value_id + "\">" + value.item_name + "</option>";
    }
    
    newItemCardBody.innerHTML = 
        "<div class=\"mb-2\">" +
            "<label class=\"form-label\">タイトル名:</label>" +
            "<input type=\"text\" name=\"titles[new_" + itemCount + "][title_name]\" class=\"form-control\" required>" +
            "<label class=\"form-label\">表示順（大カテゴリ）:</label>" +
            "<input type=\"number\" name=\"titles[new_" + itemCount + "][display_order]\" class=\"form-control\" required min=\"1\">" +
        "</div>" +
        "<div class=\"mb-2\">" +
            "<label class=\"form-label\">小枠:</label>" +
            "<input type=\"text\" name=\"items[new_" + itemCount + "][item_name]\" class=\"form-control\" required>" +
            "<input type=\"hidden\" name=\"items[new_" + itemCount + "][title_id]\" value=\"new_" + itemCount + "\">" +
            "<label class=\"form-label\">表示順（小カテゴリ）:</label>" +
            "<input type=\"number\" name=\"items[new_" + itemCount + "][display_order]\" class=\"form-control\" required min=\"1\">" +
        "</div>" +
        "<div class=\"mb-2\">" +
            "<label class=\"form-label\">属性:</label>" +
            "<select name=\"items[new_" + itemCount + "][format_value_id]\" class=\"form-control\" required>" +
                "<option value=\"\">属性を選択してください</option>" +
                options +
            "</select>" +
        "</div>" +
        "<button type=\"button\" class=\"btn btn-danger\" onclick=\"removeItem(this)\">削除</button>";
    
    newItemCard.appendChild(newItemCardBody);
    container.appendChild(newItemCard);
    itemCount++;
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