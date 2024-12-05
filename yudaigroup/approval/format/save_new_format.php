<?php

include('../../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = isset($_POST['category_name']) ? $_POST['category_name'] : '';
    $categorySelect = isset($_POST['category_select']) ? intval($_POST['category_select']) : 0;
    $formatName = $_POST['format_name'];
    $warningMessage = isset($_POST['warning_message']) ? $_POST['warning_message'] : '';
    $items = $_POST['items'];

    // トランザクション開始
    pg_query($conn, "BEGIN");

    try {
        // カテゴリIDの決定
        if ($categorySelect > 0) {
            $categoryId = $categorySelect;
        } elseif ($categoryName) {
            // 新しいカテゴリを挿入
            $queryCategory = "INSERT INTO document_format_categories (category_name) VALUES ($1) RETURNING category_id";
            $resultCategory = pg_query_params($conn, $queryCategory, array($categoryName));
            if (!$resultCategory) {
                throw new Exception("Error in category insertion: " . pg_last_error($conn));
            }
            $categoryId = pg_fetch_result($resultCategory, 0, 'category_id');
            pg_free_result($resultCategory);
        } else {
            throw new Exception("カテゴリ名または既存のカテゴリを選択してください。");
        }

        // フォーマットを挿入
        $queryFormat = "INSERT INTO document_formats (category_id, name) VALUES ($1, $2) RETURNING format_id";
        $resultFormat = pg_query_params($conn, $queryFormat, array($categoryId, $formatName));
        if (!$resultFormat) {
            throw new Exception("Error in format insertion: " . pg_last_error($conn));
        }
        $formatId = pg_fetch_result($resultFormat, 0, 'format_id');
        pg_free_result($resultFormat);

        // 警告メッセージの挿入
        if ($warningMessage) {
            $insertWarningQuery = "INSERT INTO format_warnings (format_id, warning_message, is_deleted, updated_at) 
                                   VALUES ($1, $2, false, CURRENT_TIMESTAMP)";
            $resultWarning = pg_query_params($conn, $insertWarningQuery, array($formatId, $warningMessage));
            if (!$resultWarning) {
                throw new Exception("Warning message insertion error: " . pg_last_error($conn));
            }
            pg_free_result($resultWarning);
        }

        // アイテムを挿入
        foreach ($items as $item) {
            $titleName = $item['title_name'];
            $itemName = $item['item_name'];
            $formatValueId = $item['format_value_id'];

            // タイトルを挿入
            $queryTitle = "INSERT INTO format_item_titles (format_id, title_name) VALUES ($1, $2) RETURNING title_id";
            $resultTitle = pg_query_params($conn, $queryTitle, array($formatId, $titleName));
            if (!$resultTitle) {
                throw new Exception("Error in title insertion: " . pg_last_error($conn));
            }
            $titleId = pg_fetch_result($resultTitle, 0, 'title_id');
            pg_free_result($resultTitle);

            // アイテムを挿入
            $queryItem = "INSERT INTO format_items (format_id, item_name, format_value_id, title_id) VALUES ($1, $2, $3, $4)";
            $resultItem = pg_query_params($conn, $queryItem, array($formatId, $itemName, $formatValueId, $titleId));
            if (!$resultItem) {
                throw new Exception("Error in item insertion: " . pg_last_error($conn));
            }
            pg_free_result($resultItem);
        }

        // トランザクションコミット
        pg_query($conn, "COMMIT");

        // 成功メッセージと共にリダイレクト
        header("Location: create.php?status=success");
        exit;

    } catch (Exception $e) {
        // トランザクションロールバック
        pg_query($conn, "ROLLBACK");
        error_log($e->getMessage());
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>