<?php

include('../../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();

// POSTデータのチェック
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // フォーマットIDの取得
    $format_id = isset($_POST['format_id']) ? intval($_POST['format_id']) : 0;

    if ($format_id === 0) {
        echo "Invalid format ID.";
        exit;
    }

    // カテゴリ名とフォーマット名の取得
    $categoryName = isset($_POST['category_name']) ? pg_escape_string($conn, $_POST['category_name']) : '';
    $categorySelect = isset($_POST['category_select']) ? intval($_POST['category_select']) : 0;
    $formatName = isset($_POST['format_name']) ? pg_escape_string($conn, $_POST['format_name']) : '';

    // itemsとtitlesのデータを取得
    $items = isset($_POST['items']) ? $_POST['items'] : [];
    $titles = isset($_POST['titles']) ? $_POST['titles'] : [];

    // 必要なデータが揃っているかを確認
    if ($formatName && $items) {
        // トランザクション開始
        pg_query($conn, "BEGIN");

        // カテゴリIDの決定
        if ($categorySelect > 0) {
            $categoryId = $categorySelect;
        } elseif ($categoryName) {
            // 新しいカテゴリを挿入
            $queryInsertCategory = "INSERT INTO document_format_categories (category_name) VALUES ($1) RETURNING category_id";
            $resultInsertCategory = pg_query_params($conn, $queryInsertCategory, array($categoryName));

            if (!$resultInsertCategory) {
                error_log("Category insert error: " . pg_last_error($conn));
                pg_query($conn, "ROLLBACK");
                echo "新しいカテゴリの挿入に失敗しました。";
                exit;
            }

            $categoryId = pg_fetch_result($resultInsertCategory, 0, 'category_id');
            pg_free_result($resultInsertCategory);
        } else {
            echo "カテゴリ名または既存のカテゴリを選択してください。";
            exit;
        }

        // フォーマット名とカテゴリIDの更新クエリ
        $queryFormat = "UPDATE document_formats 
                        SET name = $1, category_id = $2
                        WHERE format_id = $3 AND deleted_at IS NULL";
        $resultFormat = pg_query_params($conn, $queryFormat, array($formatName, $categoryId, $format_id));

        if (!$resultFormat) {
            error_log("Format update error: " . pg_last_error($conn));
            pg_query($conn, "ROLLBACK");
            echo "フォーマットの更新に失敗しました。";
            exit;
        }

        // タイトル情報の更新または挿入
        foreach ($titles as $titleId => $titleData) {
            $titleName = pg_escape_string($conn, $titleData['title_name']);
            $displayOrder = intval($titleData['display_order']);

            if (strpos($titleId, 'new_') === 0) {
                // 新しいタイトルを挿入
                $queryInsertTitle = "INSERT INTO format_item_titles (format_id, title_name, display_order) VALUES ($1, $2, $3) RETURNING title_id";
                $resultInsertTitle = pg_query_params($conn, $queryInsertTitle, array($format_id, $titleName, $displayOrder));

                if (!$resultInsertTitle) {
                    error_log("Title insert error: " . pg_last_error($conn));
                    pg_query($conn, "ROLLBACK");
                    echo "新しいタイトルの挿入に失敗しました。";
                    exit;
                }

                $newTitleId = pg_fetch_result($resultInsertTitle, 0, 'title_id');
                pg_free_result($resultInsertTitle);

                // 新しいアイテムにタイトルIDを設定
                foreach ($items as $itemId => $itemData) {
                    if (strpos($itemId, 'new_') === 0 && $itemData['title_name'] === $titleName) {
                        $items[$itemId]['title_id'] = $newTitleId;
                    }
                }
            } else {
                // 既存のタイトルを更新
                $queryTitle = "UPDATE format_item_titles 
                               SET title_name = $1, display_order = $2 
                               WHERE title_id = $3 AND is_deleted = 'f'";
                $resultTitle = pg_query_params($conn, $queryTitle, array($titleName, $displayOrder, $titleId));

                if (!$resultTitle) {
                    error_log("Title update error (title ID: $titleId): " . pg_last_error($conn));
                    pg_query($conn, "ROLLBACK");
                    echo "タイトル名の更新に失敗しました。";
                    exit;
                }
            }
        }

        // アイテム情報の更新または挿入
        foreach ($items as $itemId => $itemData) {
            $itemName = pg_escape_string($conn, $itemData['item_name']);
            $formatValueId = intval($itemData['format_value_id']);
            $titleId = isset($itemData['title_id']) ? 
    (strpos($itemData['title_id'], 'new_') === 0 ? $newTitleId : intval($itemData['title_id'])) : 
    0;
            $displayOrder = isset($itemData['display_order']) ? intval($itemData['display_order']) : 0;

            if ($titleId === 0) {
                error_log("Title ID is missing for item ID: $itemId");
                pg_query($conn, "ROLLBACK");
                echo "タイトルIDが不足しています。";
                exit;
            }

            if (strpos($itemId, 'new_') === 0) {
                // 新しいアイテムを挿入
                $queryInsertItem = "INSERT INTO format_items (format_id, item_name, format_value_id, title_id, display_order) VALUES ($1, $2, $3, $4, $5) RETURNING format_item_id";
                $resultInsertItem = pg_query_params($conn, $queryInsertItem, array($format_id, $itemName, $formatValueId, $titleId, $displayOrder));
            
                if (!$resultInsertItem) {
                    error_log("Item insert error: " . pg_last_error($conn));
                    pg_query($conn, "ROLLBACK");
                    echo "新しいアイテムの挿入に失敗しました。";
                    exit;
                }
            
                $newItemId = pg_fetch_result($resultInsertItem, 0, 'format_item_id');
                pg_free_result($resultInsertItem);
            } else {
                // アイテム情報の更新クエリを修正
                $queryItem = "UPDATE format_items 
                SET item_name = $1, format_value_id = $2, title_id = $3, display_order = $4
                WHERE format_item_id = $5 AND is_deleted = 'f'";
                $resultItem = pg_query_params($conn, $queryItem, 
                array($itemName, $formatValueId, $titleId, $itemData['display_order'], $itemId));
            
                if (!$resultItem) {
                    error_log("Item update error (item ID: $itemId): " . pg_last_error($conn));
                    pg_query($conn, "ROLLBACK");
                    echo "アイテム情報の更新に失敗しました。";
                    exit;
                }
            }
        }

        // 削除対象のアイテムを処理
        if (isset($_POST['delete_items']) && is_array($_POST['delete_items'])) {
            foreach ($_POST['delete_items'] as $itemId) {
                $queryDeleteItem = "UPDATE format_items 
                                   SET is_deleted = 't' 
                                   WHERE format_item_id = $1";
                $resultDeleteItem = pg_query_params($conn, $queryDeleteItem, array($itemId));

                if (!$resultDeleteItem) {
                    error_log("Item deletion error (item ID: $itemId): " . pg_last_error($conn));
                    pg_query($conn, "ROLLBACK");
                    echo "アイテムの削除に失敗しました。";
                    exit;
                }
            }
        }

        // トランザクションのコミット
        pg_query($conn, "COMMIT");
        // データが正常に保存された場合のリダイレクト
        echo "データが正常に保存されました。";
        exit;
    } else {
        echo "カテゴリ名、フォーマット名、またはアイテムデータが不足しています。";
    }
} else {
    echo "無効なリクエストです。";
}
?>