<?php

include('../../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formatId = intval($_POST['format_id']);

    // トランザクション開始
    pg_query($conn, "BEGIN");

    try {
        // フォーマットに関連するアイテムを削除
        $queryDeleteItems = "UPDATE format_items SET is_deleted = 't' WHERE format_id = $1";
        $resultDeleteItems = pg_query_params($conn, $queryDeleteItems, array($formatId));
        if (!$resultDeleteItems) {
            throw new Exception("Error in deleting format items: " . pg_last_error($conn));
        }

        // フォーマットに関連するタイトルを削除
        $queryDeleteTitles = "UPDATE format_item_titles SET is_deleted = 't' WHERE format_id = $1";
        $resultDeleteTitles = pg_query_params($conn, $queryDeleteTitles, array($formatId));
        if (!$resultDeleteTitles) {
            throw new Exception("Error in deleting format item titles: " . pg_last_error($conn));
        }

        // フォーマットに関連する警告メッセージを削除
        $queryDeleteWarnings = "UPDATE format_warnings SET is_deleted = 't' WHERE format_id = $1";
        $resultDeleteWarnings = pg_query_params($conn, $queryDeleteWarnings, array($formatId));
        if (!$resultDeleteWarnings) {
            throw new Exception("Error in deleting format warnings: " . pg_last_error($conn));
        }

        // フォーマットを削除
        $queryDeleteFormat = "UPDATE document_formats SET is_deleted = 't' WHERE format_id = $1";
        $resultDeleteFormat = pg_query_params($conn, $queryDeleteFormat, array($formatId));
        if (!$resultDeleteFormat) {
            throw new Exception("Error in deleting format: " . pg_last_error($conn));
        }

        // カテゴリIDを取得
        $queryCategoryId = "SELECT category_id FROM document_formats WHERE format_id = $1";
        $resultCategoryId = pg_query_params($conn, $queryCategoryId, array($formatId));
        if (!$resultCategoryId) {
            throw new Exception("Error in fetching category ID: " . pg_last_error($conn));
        }
        $categoryId = pg_fetch_result($resultCategoryId, 0, 'category_id');
        pg_free_result($resultCategoryId);

        // カテゴリに関連するすべてのフォーマットが削除されているかチェック
        $queryCheckFormats = "SELECT COUNT(*) AS count FROM document_formats WHERE category_id = $1 AND is_deleted = 'f'";
        $resultCheckFormats = pg_query_params($conn, $queryCheckFormats, array($categoryId));
        if (!$resultCheckFormats) {
            throw new Exception("Error in checking formats: " . pg_last_error($conn));
        }
        $count = pg_fetch_result($resultCheckFormats, 0, 'count');
        pg_free_result($resultCheckFormats);

        // すべてのフォーマットが削除されている場合、カテゴリを削除
        if ($count == 0) {
            $queryDeleteCategory = "UPDATE document_format_categories SET is_deleted = 't' WHERE category_id = $1";
            $resultDeleteCategory = pg_query_params($conn, $queryDeleteCategory, array($categoryId));
            if (!$resultDeleteCategory) {
                throw new Exception("Error in deleting category: " . pg_last_error($conn));
            }
        }

        // トランザクションコミット
        pg_query($conn, "COMMIT");

        // 成功メッセージと共にリダイレクト
        header("Location: index.php?status=deleted");
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