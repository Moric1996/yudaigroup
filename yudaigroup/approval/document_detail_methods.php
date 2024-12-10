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
    $formatQuery = "
        SELECT f.name AS format_name, c.category_name
        FROM documents d
        JOIN document_formats f ON d.format_id = f.format_id
        JOIN document_format_categories c ON f.category_id = c.category_id
        WHERE d.document_id = $document_id
    ";
    $formatResult = pg_query($conn, $formatQuery);
    if ($formatResult) {
        return pg_fetch_assoc($formatResult);
    } else {
        error_log("Error in format query execution: " . pg_last_error($conn));
        return false;
    }
}

function getDocumentValues($conn, $document_id) {
    $valuesQuery = "
        SELECT dv.format_item_id, fi.item_name, dv.value, fiv.item_type, fi.title_id
        FROM document_values dv
        JOIN format_items fi ON dv.format_item_id = fi.format_item_id
        LEFT JOIN format_item_values fiv ON fi.format_value_id = fiv.format_value_id
        WHERE dv.document_id = $document_id
    ";
    $valuesResult = pg_query($conn, $valuesQuery);
    if ($valuesResult) {
        return pg_fetch_all($valuesResult);
    } else {
        error_log("Error in values query execution: " . pg_last_error($conn));
        return false;
    }
}
?>