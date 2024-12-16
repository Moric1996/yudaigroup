<?php

function deleteDocument($conn, $document_id) {
    // トランザクションを開始
    pg_query($conn, "BEGIN");

    // approval_statusテーブルの関連レコードを論理削除
    $updateStatusQuery = "UPDATE approval_status SET is_deleted = 't' WHERE document_id = $1";
    $updateStatusResult = pg_query_params($conn, $updateStatusQuery, array($document_id));

    if ($updateStatusResult) {
        // document_valuesテーブルの関連レコードを論理削除
        $updateValuesQuery = "UPDATE document_values SET is_deleted = 't' WHERE document_id = $1";
        $updateValuesResult = pg_query_params($conn, $updateValuesQuery, array($document_id));

        if ($updateValuesResult) {
            // documentsテーブルのレコードを論理削除
            $updateDocumentsQuery = "UPDATE documents SET is_deleted = 't' WHERE document_id = $1";
            $updateDocumentsResult = pg_query_params($conn, $updateDocumentsQuery, array($document_id));

            if ($updateDocumentsResult) {
                // トランザクションをコミット
                pg_query($conn, "COMMIT");
                return true;
            } else {
                error_log("Error in update documents query execution: " . pg_last_error($conn));
                pg_query($conn, "ROLLBACK");
                return false;
            }
        } else {
            error_log("Error in update values query execution: " . pg_last_error($conn));
            pg_query($conn, "ROLLBACK");
            return false;
        }
    } else {
        error_log("Error in update status query execution: " . pg_last_error($conn));
        pg_query($conn, "ROLLBACK");
        return false;
    }
}

function getDocumentFormat($conn, $document_id) {
    $query = "
        SELECT df.name AS format_name, dfc.category_name
        FROM documents d
        JOIN document_formats df ON d.format_id = df.format_id
        JOIN document_format_categories dfc ON df.category_id = dfc.category_id
        WHERE d.document_id = $1 AND d.is_deleted = 'f' AND df.is_deleted = 'f' AND dfc.is_deleted = 'f'
    ";
    $result = pg_query_params($conn, $query, array($document_id));
    if ($result && pg_num_rows($result) > 0) {
        return pg_fetch_assoc($result);
    }
    return false;
}

function getDocumentValues($conn, $document_id) {
    $query = "
        SELECT fi.item_name, dv.value, fiv.item_type, fit.title_name, fit.title_id
        FROM document_values dv
        JOIN format_item_values fiv ON dv.format_value_id = fiv.format_value_id
        JOIN format_items fi ON dv.format_item_id = fi.format_item_id
        JOIN format_item_titles fit ON fi.title_id = fit.title_id
        WHERE dv.document_id = $1 AND dv.is_deleted = 'f' AND fiv.is_deleted = 'f' AND fi.is_deleted = 'f' AND fit.is_deleted = 'f'
        ORDER BY fit.display_order, fi.display_order
    ";
    $result = pg_query_params($conn, $query, array($document_id));
    if ($result) {
        $values = [];
        while ($row = pg_fetch_assoc($result)) {
            $values[] = $row;
        }
        return $values;
    }
    return false;
}
?>