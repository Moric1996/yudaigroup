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
    $documentInsertQuery = "INSERT INTO documents (applicant_id, format_id, status_codes, updated_at) VALUES ('$applicantId', $selectedFormatId, '$statusCodes', NOW()) RETURNING document_id";
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
            } elseif ($itemType === 'checkbox') {
                // チェックボックスの値を特別に処理
                if (isset($_POST["item_$formatItemId"])) {
                    foreach ($_POST["item_$formatItemId"] as $checkboxValue) {
                        $documentValuesQuery = "INSERT INTO document_values (format_value_id, document_id, format_item_id, value, is_deleted, updated_at) VALUES ($formatValueId, $documentId, $formatItemId, '$checkboxValue', FALSE, NOW())";
                        pg_query($conn, $documentValuesQuery);
                    }
                    continue; // チェックボックスの場合、ループ内で処理を完了するため、次のアイテムに進む
                } else {
                    $value = '';
                }
            } else {
                // その他の項目の値を処理
                $value = isset($_POST["item_$formatItemId"]) ? $_POST["item_$formatItemId"] : '';
            }

            if ($itemType !== 'checkbox') {
                $documentValuesQuery = "INSERT INTO document_values (format_value_id, document_id, format_item_id, value, is_deleted, updated_at) VALUES ($formatValueId, $documentId, $formatItemId, '$value', FALSE, NOW())";
                pg_query($conn, $documentValuesQuery);
            }
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
        AND ar.group_id = gm.group_id  -- 申請者のグループIDに基づいてフィルタリング
        AND ar.is_deleted = false
        AND gm.is_deleted = false  -- 追加: 削除されていないグループメンバーのみを考慮
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

        // 申請者の名前を取得
        $queryApplicantName = "SELECT name FROM member WHERE mem_id = '$applicantId'";
        $resultApplicantName = pg_query($conn, $queryApplicantName);
        $applicantNameRow = pg_fetch_assoc($resultApplicantName);
        $applicant_name = $applicantNameRow['name'];

        // 申請書のフォーマット名を取得
        $queryDocumentFormat = "
            SELECT name 
            FROM document_formats 
            WHERE format_id = $selectedFormatId
        ";
        $resultDocumentFormat = pg_query($conn, $queryDocumentFormat);
        $documentFormatRow = pg_fetch_assoc($resultDocumentFormat);
        $document_format_name = $documentFormatRow['name'];

        // 次の承認者を取得
        $queryNextApprover = "
            SELECT ar.applicant_id, m.name
            FROM approval_routes ar
            JOIN member m ON ar.applicant_id = m.mem_id
            JOIN group_members gm ON ar.group_id = gm.group_id
            WHERE ar.group_id = (SELECT group_id FROM group_members WHERE applicant_id = '$applicantId' AND is_deleted = false LIMIT 1)
            AND ar.approval_order = (SELECT current_step FROM documents WHERE document_id = $documentId)
            AND ar.is_deleted = false
            AND gm.is_deleted = false
            LIMIT 1
        ";
        $resultNextApprover = pg_query($conn, $queryNextApprover);
        if ($nextApprover = pg_fetch_assoc($resultNextApprover)) {
            $next_approver_name = $nextApprover['name'];
            $message_text = "$applicant_name さんが $document_format_name を作成しました。$next_approver_name さんは申請を確認してください。";
        } else {
            $message_text = "$applicant_name さんが $document_format_name を作成しましたが、承認者が見つかりません。";
        }

        // Slack通知の送信
        $slack_webhook_url = 'https://hooks.slack.com/services/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';
        $message = array('text' => $message_text);

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-Type: application/json',
                'content' => json_encode($message),
            ),
        );

        $context  = stream_context_create($options);
        file_get_contents($slack_webhook_url, false, $context);

        return $documentId;
    } else {
        error_log("Error in inserting document: " . pg_last_error($conn));
        return false;
    }
}
?>