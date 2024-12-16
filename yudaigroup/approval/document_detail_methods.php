<?php

function deleteDocument($conn, $document_id) {
    // approval_historyテーブルの関連レコードを削除
    $deleteHistoryQuery = "DELETE FROM approval_history WHERE document_id = $document_id";
    $deleteHistoryResult = pg_query($conn, $deleteHistoryQuery);

    if ($deleteHistoryResult) {
        // approval_statusテーブルの関連レコードを削除
        $deleteStatusQuery = "DELETE FROM approval_status WHERE document_id = $document_id";
        $deleteStatusResult = pg_query($conn, $deleteStatusQuery);

        if ($deleteStatusResult) {
            // document_valuesテーブルの関連レコードを削除
            $deleteValuesQuery = "DELETE FROM document_values WHERE document_id = $document_id";
            $deleteValuesResult = pg_query($conn, $deleteValuesQuery);

            if ($deleteValuesResult) {
                // documentsテーブルのレコードを削除
                $deleteQuery = "DELETE FROM documents WHERE document_id = $document_id";
                $deleteResult = pg_query($conn, $deleteQuery);

                if ($deleteResult) {
                    return true;
                } else {
                    error_log("Error in delete query execution: " . pg_last_error($conn));
                    return false;
                }
            } else {
                error_log("Error in delete values query execution: " . pg_last_error($conn));
                return false;
            }
        } else {
            error_log("Error in delete status query execution: " . pg_last_error($conn));
            return false;
        }
    } else {
        error_log("Error in delete history query execution: " . pg_last_error($conn));
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