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

    // Slack通知の送信
    $slack_webhook_url = 'https://hooks.slack.com/services/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';
    
        // 承認者の名前を取得
    $queryApproverName = "SELECT name FROM member WHERE mem_id = '$approver_id'";
    $resultApproverName = pg_query($conn, $queryApproverName);
    $approverNameRow = pg_fetch_assoc($resultApproverName);
    $approver_name = $approverNameRow['name'];
    
    // 申請者の名前を取得
    $queryApplicantName = "SELECT name FROM member WHERE mem_id = '{$document['applicant_id']}'";
    $resultApplicantName = pg_query($conn, $queryApplicantName);
    $applicantNameRow = pg_fetch_assoc($resultApplicantName);
    $applicant_name = $applicantNameRow['name'];
    
    $message_text = "$approver_name さんが $applicant_name さんの申請を承認しました。よかったね。";
    
        if ($action === 'approved' && $current_step < $max_step) {
        // 次の承認者を取得
        $queryNextApprover = "
            SELECT ar.applicant_id, m.name
            FROM approval_routes ar
            JOIN member m ON ar.applicant_id = m.mem_id
            JOIN approval_status ast ON ar.approval_order = ast.step_number
            WHERE ast.document_id = $document_id
            AND ast.status = 0
            AND ar.is_deleted = false
            LIMIT 1
        ";
        $resultNextApprover = pg_query($conn, $queryNextApprover);
        if ($nextApprover = pg_fetch_assoc($resultNextApprover)) {
            $next_approver_id = $nextApprover['applicant_id'];
            $next_approver_name = $nextApprover['name'];
            $message_text .= " $next_approver_name さんは申請を確認してください。";
        } else {
            $message_text .= " 承認ルートが途中で変更されています。申請をやり直してください";
        }
    } else {
        $message_text .= " これで完了です。";
    }
    
    $message = array('text' => $message_text);
    
    $options = array(
        'http' => array(
            'method'  => 'POST',
            'header'  => 'Content-Type: application/json',
            'content' => json_encode($message),
        ),
    );
    
    $context  = stream_context_create($options);
    $result = file_get_contents($slack_webhook_url, false, $context);
    
    if ($result === FALSE) {
        error_log("Slack通知の送信に失敗しました。");
    }

    
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