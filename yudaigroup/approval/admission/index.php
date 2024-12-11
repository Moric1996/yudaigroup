<?php
include('../../inc/ybase.inc');

session_start();

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();

$ybase->title = "承認一覧";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("承認一覧");

// メッセージ表示
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8');
    $alertClass = (strpos($message, '却下') !== false) ? 'alert-danger' : 'alert-success';
    $ybase->ST_PRI .= "<div class='alert $alertClass'>" . $message . "</div>";
}

$ybase->ST_PRI .= <<<HTML
<div class="container mt-5">
    <h1 class="mb-4">承認一覧</h1>
    <div class="text-right mb-3">
    <a href='/yudaigroup/approval/index.php' class='btn btn-secondary'>申請TOPへ戻る</a>
    </div>
HTML;

$applicant_id = $ybase->my_employee_num;
echo "Logged in applicant_id: " . htmlspecialchars($applicant_id, ENT_QUOTES, 'UTF-8') . "<br>";

// 承認対象の申請を取得するクエリ
$queryDocuments = "
    WITH approver_groups AS (
    SELECT DISTINCT ar.group_id, ar.approval_order
    FROM approval_routes ar
    WHERE ar.is_deleted = false
),
-- Get the current user's approval order
user_approval_order AS (
    SELECT ar.approval_order
    FROM approval_routes ar
    WHERE ar.applicant_id = '$applicant_id'
    AND ar.is_deleted = false
    LIMIT 1
),
-- Check for any pending approvals in previous steps
pending_previous_approvals AS (
    SELECT d.document_id
    FROM documents d
    JOIN approval_status aps ON d.document_id = aps.document_id
    JOIN approval_routes ar ON ar.group_id = (
        SELECT group_id 
        FROM group_members 
        WHERE applicant_id = d.applicant_id 
        AND is_deleted = false
        LIMIT 1
    )
    WHERE ar.is_deleted = false
    AND ar.approval_order < (SELECT approval_order FROM user_approval_order)
    AND aps.status = 0
)
SELECT DISTINCT 
    d.document_id,
    m.name AS applicant_name,
    f.name AS document_name,
    d.updated_at,
    aps.status AS step_status,
    aps.step_number
FROM documents d
JOIN member m ON d.applicant_id = m.mem_id
JOIN document_formats f ON d.format_id = f.format_id
JOIN approval_routes ar ON ar.group_id = (
    SELECT group_id 
    FROM group_members 
    WHERE applicant_id = d.applicant_id 
    AND is_deleted = false
    LIMIT 1
)
JOIN approver_groups ag ON ag.group_id = ar.group_id
LEFT JOIN approval_status aps ON d.document_id = aps.document_id 
    AND aps.step_number = ag.approval_order
WHERE d.deleted_at IS NULL
    AND d.applicant_id != '$applicant_id'
    AND EXISTS (
        SELECT 1
        FROM approval_routes ar
        WHERE ar.group_id = ag.group_id
            AND ar.approval_order = ag.approval_order
            AND ar.applicant_id = '$applicant_id'
            AND ar.is_deleted = false
    )
    -- 承認済みの申請も表示するため、pending_previous_approvalsの条件を削除
    -- AND d.document_id NOT IN (SELECT document_id FROM pending_previous_approvals)
    AND aps.step_number = (SELECT approval_order FROM user_approval_order)
ORDER BY d.updated_at DESC;
";

$resultDocuments = pg_query($conn, $queryDocuments);

if (!$resultDocuments) {
    error_log("Error in query execution: " . pg_last_error($conn));
    echo "Error in query execution: " . pg_last_error($conn);
} else {
    $ybase->ST_PRI .= "<table class='table table-bordered table-hover table-sm' style='font-size:80%;'>";
    $ybase->ST_PRI .= "<thead>
        <tr align='center' class='table-primary'>
            <th scope='col'>申請者</th>
            <th scope='col'>書類名</th>
            <th scope='col'>ステータス</th>
            <th scope='col'>申請日時</th>
            <th scope='col'>最終更新</th>
            <th scope='col'>操作</th>
            <th scope='col'>確認</th>
            <th scope='col'>Document ID</th>
        </tr>
        </thead>";
    $ybase->ST_PRI .= "<tbody>";

    $hasDocuments = false;

    while ($document = pg_fetch_assoc($resultDocuments)) {
        $hasDocuments = true;

        switch ($document['step_status']) {
            case 0:
                $status = '進行中';
                break;
            case 1:
                $status = '承認済み';
                break;
            case 2:
                $status = '却下';
                break;
            default:
                $status = '不明';
                break;
        }

        $updated_at = new DateTime($document['updated_at']);

        $ybase->ST_PRI .= "<tr align='center'>";
        $ybase->ST_PRI .= "<td>" . htmlspecialchars($document['applicant_name'], ENT_QUOTES, 'UTF-8') . "</td>";
        $ybase->ST_PRI .= "<td>" . htmlspecialchars($document['document_name'], ENT_QUOTES, 'UTF-8') . "</td>";
        $ybase->ST_PRI .= "<td>" . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . "</td>";
        $ybase->ST_PRI .= "<td>" . $updated_at->format('Y/m/d H:i') . "</td>";
        $ybase->ST_PRI .= "<td>
            <form action='approve.php?document_id={$document['document_id']}' method='post' class='d-inline'>
                <button type='submit' name='action' value='approved' class='btn btn-success btn-sm mr-2'>承認</button>
                <button type='submit' name='action' value='rejected' class='btn btn-danger btn-sm'>却下</button>
            </form>
        </td>";
        $ybase->ST_PRI .= "<td><a href='/yudaigroup/approval/document_detail.php?document_id={$document['document_id']}' class='btn btn-info btn-sm'>確認</a></td>";
        $ybase->ST_PRI .= "<td>" . htmlspecialchars($document['document_id'], ENT_QUOTES, 'UTF-8') . "</td>";
        $ybase->ST_PRI .= "</tr>";
    }

    $ybase->ST_PRI .= "</tbody></table>";

    if (!$hasDocuments) {
        $ybase->ST_PRI .= "<p class='text-muted'>承認対象の申請はありません。</p>";
    }

    pg_free_result($resultDocuments);
}

$ybase->ST_PRI .= "</div>";

$ybase->HTMLfooter();
$ybase->priout();
?>