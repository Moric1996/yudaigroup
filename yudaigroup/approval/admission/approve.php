<?php
include('../../inc/ybase.inc');

session_start();

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();

// POSTデータの検証
if (!isset($_GET['document_id']) || !isset($_POST['action'])) {
    header("Location: index.php?message=" . urlencode("不正なリクエストです！"));
    exit();
}

$document_id = pg_escape_string($conn, $_GET['document_id']);
$approver_id = $ybase->my_employee_num;
$action = $_POST['action'];
$comment = isset($_POST['comment']) ? pg_escape_string($conn, $_POST['comment']) : '';

try {
    // トランザクション開始
    pg_query($conn, "BEGIN");

    // 現在の申請情報を取得
    $queryDocument = "
        SELECT current_step, max_step, applicant_id, status_codes 
        FROM documents 
        WHERE document_id = '$document_id'
    ";
    $resultDocument = pg_query($conn, $queryDocument);
    $document = pg_fetch_assoc($resultDocument);

    if (!$document) {
        throw new Exception("申請が見つかりません");
    }

    // 現在の承認ステップを確認
    $current_step = $document['current_step'];
    $max_step = $document['max_step'];

    // 承認権限の確認
    $queryAuthCheck = "
        SELECT ar.group_id
        FROM approval_routes ar
        JOIN group_members gm ON ar.group_id = gm.group_id
        WHERE gm.applicant_id = '$approver_id'
        AND ar.approval_order = '$current_step'
        AND ar.is_deleted = false
    ";
    $resultAuthCheck = pg_query($conn, $queryAuthCheck);
    if (pg_num_rows($resultAuthCheck) == 0) {
        throw new Exception("この承認ステップの権限がありません");
    }
    $authData = pg_fetch_assoc($resultAuthCheck);
    $group_id = $authData['group_id'];


    $status = ($action === 'approved') ? 1 : 2; // 1:承認, 2:却下

    // 現在のステップの承認ステータスを更新
    $queryUpdateStatus = "
        UPDATE approval_status 
        SET status = $status,
            updated_at = NOW()
        WHERE document_id = '$document_id'
        AND step_number = '$current_step'
    ";
    pg_query($conn, $queryUpdateStatus);

    // 承認かつ次のステップがある場合、次のステップのレコードを作成
    if ($action === 'approved' && $current_step < $max_step) {
        $next_step = $current_step + 1;
        $queryInsertNextStatus = "
            INSERT INTO approval_status 
            (document_id, step_number, status, updated_at)
            VALUES 
            ('$document_id', '$next_step', 0, NOW())
        ";
        pg_query($conn, $queryInsertNextStatus);
    }

    // 申請全体のステータス更新
    if ($action === 'rejected') {
        // 却下の場合、すべてのステップのステータスを2に更新
        $updateAllStatusQuery = "
            UPDATE approval_status 
            SET status = 2, 
                updated_at = NOW() 
            WHERE document_id = '$document_id'
        ";
        pg_query($conn, $updateAllStatusQuery);

        // documentsテーブルのステータスを更新
        $updateDocumentQuery = "
            UPDATE documents 
            SET status_codes = 3, 
                updated_at = NOW() 
            WHERE document_id = '$document_id'
        ";
    } elseif ($current_step < $max_step) {
        // 承認され、まだ承認ステップが残っている場合
        $updateDocumentQuery = "
            UPDATE documents 
            SET current_step = current_step + 1, 
                updated_at = NOW() 
            WHERE document_id = '$document_id'
        ";
    } else {
        // 最終ステップまで承認された場合
        $updateDocumentQuery = "
            UPDATE documents 
            SET status_codes = 2, 
                updated_at = NOW() 
            WHERE document_id = '$document_id'
        ";
    }
    pg_query($conn, $updateDocumentQuery);

    // トランザクションをコミット
    pg_query($conn, "COMMIT");

    $message = ($action === 'approved') ? '申請を承認しました' : '申請を却下しました';
    header("Location: index.php?message=" . urlencode($message));
    exit();

} catch (Exception $e) {
    // エラー発生時はロールバック
    pg_query($conn, "ROLLBACK");
    error_log($e->getMessage());
    header("Location: index.php?message=" . urlencode("処理中にエラーが発生しました: " . $e->getMessage()));
    exit();
}
?>