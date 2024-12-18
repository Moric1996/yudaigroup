<?php

include('../../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
    $approval_orders = isset($_POST['approval_order']) ? $_POST['approval_order'] : [];

    if ($group_id === 0 || empty($approval_orders)) {
        echo "Invalid input.";
        exit;
    }

    // トランザクション開始
    pg_query($conn, "BEGIN");

    try {
        // 既存の承認ルートを削除
        $queryDeleteRoutes = "UPDATE approval_routes SET is_deleted = 't' WHERE group_id = $1";
        $resultDeleteRoutes = pg_query_params($conn, $queryDeleteRoutes, array($group_id));

        if (!$resultDeleteRoutes) {
            throw new Exception("Error in delete routes query execution: " . pg_last_error($conn));
        }

        // 新しい承認ルートを挿入
        foreach ($approval_orders as $order => $applicant_id) {
            if (!empty($applicant_id)) {
                $approval_order = $order + 1;
                $queryInsertRoute = "INSERT INTO approval_routes (group_id, applicant_id, approval_order, is_deleted) VALUES ($1, $2, $3, 'f')";
                $resultInsertRoute = pg_query_params($conn, $queryInsertRoute, array($group_id, $applicant_id, $approval_order));

                if (!$resultInsertRoute) {
                    throw new Exception("Error in insert route query execution: " . pg_last_error($conn));
                }
            }
        }

        // トランザクションコミット
        pg_query($conn, "COMMIT");

        // 成功メッセージと共にリダイレクト
        header("Location: approval_route_settings.php?group_id=$group_id&status=success");
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