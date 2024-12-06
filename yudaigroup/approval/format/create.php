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
<div class="container mt-5">
    <h1 class="mb-4">新規フォーマット作成</h1>
    <form method="post" action="save_new_format.php" onsubmit="return validateForm()">
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">基本情報</h2>
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
    $ybase->ST_PRI .= <<<HTML
                        <option value="$catId">$catName</option>
HTML;
}

$ybase->ST_PRI .= <<<HTML
                    </select>
                </div>
                <div class="mb-3">
                    <label for="format_name" class="form-label">フォーマット名:</label>
                    <input type="text" name="format_name" id="format_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="warning_message" class="form-label">備考、注意書き等あれば:</label>
                    <textarea name="warning_message" id="warning_message" class="form-control" rows="4"></textarea>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">アイテム</h2>
            </div>
            <div class="card-body">
                <div id="items-container">
                    <div class="item-row mb-3 border p-3">
                        <div class="mb-2">
                            <label class="form-label">大枠:</label>
                            <input type="text" name="items[0][title_name]" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">小枠:</label>
                            <input type="text" name="items[0][item_name]" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">属性:</label>
                            <select name="items[0][format_value_id]" class="form-control" required>
                                <option value="">属性を選択してください</option>
                                {$optionsHtml}
                            </select>
                        </div>
                        <button type="button" class="btn btn-danger" onclick="removeItem(this)">削除</button>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" onclick="addNewItem()">アイテムを追加</button>
            </div>
        </div>

        <div class="mb-4">
            <button type="submit" class="btn btn-primary">フォーマットを作成</button>
            <a href="index.php" class="btn btn-link">キャンセル</a>
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
            "<label class=\"form-label\">大枠:</label>" +
            "<input type=\"text\" name=\"items[" + itemCount + "][title_name]\" class=\"form-control\" required>" +
        "</div>" +
        "<div class=\"mb-2\">" +
            "<label class=\"form-label\">小枠:</label>" +
            "<input type=\"text\" name=\"items[" + itemCount + "][item_name]\" class=\"form-control\" required>" +
        "</div>" +
        "<div class=\"mb-2\">" +
            "<label class=\"form-label\">属性:</label>" +
            "<select name=\"items[" + itemCount + "][format_value_id]\" class=\"form-control\" required>" +
                "<option value=\"\">属性を選択してください</option>" +
                options +
            "</select>" +
        "</div>" +
        "<button type=\"button\" class=\"btn btn-danger\" onclick=\"removeItem(this)\">削除</button>";
    
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
</script>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
?>