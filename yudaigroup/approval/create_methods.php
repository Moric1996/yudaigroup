<?php

function getCategories($conn) {
    $categoryQuery = "SELECT category_id, category_name FROM document_format_categories WHERE is_deleted = 'f' ORDER BY category_name ASC";
    $categoryResult = pg_query($conn, $categoryQuery);

    $categories = [];
    while ($row = pg_fetch_assoc($categoryResult)) {
        $categories[] = $row;
    }
    return $categories;
}

function getSubcategories($conn, $selectedCategoryId) {
    $subcategories = [];
    if ($selectedCategoryId) {
        $subcategoryQuery = "SELECT format_id, name FROM document_formats WHERE category_id = $selectedCategoryId";
        $subcategoryResult = pg_query($conn, $subcategoryQuery);

        while ($row = pg_fetch_assoc($subcategoryResult)) {
            $subcategories[] = $row;
        }
    }
    return $subcategories;
}

function getItems($conn, $selectedFormatId) {
    $items = [];
    if ($selectedFormatId) {
        $itemQuery = "
        SELECT 
            fi.format_item_id,
            fi.item_name,
            fiv.item_type,
            fi.display_order AS item_display_order,
            fiv.format_value_id,
            ft.title_name,
            ft.display_order AS title_display_order
        FROM format_items AS fi
        LEFT JOIN format_item_values AS fiv ON fi.format_value_id = fiv.format_value_id
        LEFT JOIN format_item_titles AS ft ON fi.title_id = ft.title_id
        WHERE fi.format_id = $1 AND fi.is_deleted = 'f'
        ORDER BY ft.display_order, fi.display_order";
        
        $result = pg_query_params($conn, $itemQuery, [$selectedFormatId]);

        while ($row = pg_fetch_assoc($result)) {
            $items[] = $row;
        }
    }
    return $items;
}

function insertDocument($conn, $applicantId, $selectedFormatId, $items) {
    $statusCodes = 1;
    $documentInsertQuery = "INSERT INTO documents (applicant_id, format_id, status_codes, created_at, updated_at) VALUES ('$applicantId', $selectedFormatId, '$statusCodes', NOW(), NOW()) RETURNING document_id";
    $documentResult = pg_query($conn, $documentInsertQuery);

    if ($documentResult) {
        $documentRow = pg_fetch_assoc($documentResult);
        $documentId = $documentRow['document_id'];

        // document_valuesテーブルに値を挿入
        foreach ($items as $item) {
            $formatItemId = $item['format_item_id'];
            $formatValueId = $item['format_value_id'];
            $itemType = $item['item_type'];

            if ($itemType === 'radio') {
                // ラジオボタンの値を特別に処理
                $value = isset($_POST["radio_group"]) ? $_POST["radio_group"] : '';
            } else {
                // その他の項目の値を処理
                $value = isset($_POST["item_$formatItemId"]) ? $_POST["item_$formatItemId"] : '';
            }

            $documentValuesQuery = "INSERT INTO document_values (format_value_id, document_id, format_item_id, value, is_deleted, updated_at) VALUES ($formatValueId, $documentId, $formatItemId, '$value', FALSE, NOW())";
            pg_query($conn, $documentValuesQuery);
        }

        // 承認ステータスの初期化
        $query_init_approval_status = "
        INSERT INTO approval_status (document_id, step_number, status)
        VALUES ($1, 1, 0)  -- 0: pending
        ";
        pg_query_params($conn, $query_init_approval_status, [$documentId]);

        // 最大ステップ数を取得
        $query_max_step = "
        SELECT COUNT(DISTINCT ar.approval_order) as max_step
        FROM approval_routes ar
        JOIN group_members gm ON ar.group_id = gm.group_id
        JOIN documents d ON d.applicant_id = gm.applicant_id
        WHERE d.document_id = $1
        AND ar.is_deleted = false
        ";
        $result_max_step = pg_query_params($conn, $query_max_step, [$documentId]);
        $max_step_row = pg_fetch_assoc($result_max_step);
        $max_step = $max_step_row['max_step'] ?: 1;  // デフォルトは1

        // ドキュメントの最大ステップを更新
        $query_update_max_step = "
        UPDATE documents 
        SET max_step = $1, 
            current_step = 1 
        WHERE document_id = $2
        ";
        pg_query_params($conn, $query_update_max_step, [$max_step, $documentId]);

        return $documentId;
    } else {
        error_log("Error in inserting document: " . pg_last_error($conn));
        return false;
    }
}
?>