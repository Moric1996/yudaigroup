<?php
include('../../inc/ybase.inc');

session_start();

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();

$ybase->title = "承認一覧";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("承認一覧");

// スタイルの追加
$ybase->ST_PRI .= <<<HTML
<style>
    .dashboard-container {
        background: #f8f9fa;
        padding: 2rem 0;
        min-height: 100vh;
    }
    .page-title {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 2rem;
        position: relative;
        display: inline-block;
    }
    .page-title:after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 60px;
        height: 4px;
        background: linear-gradient(135deg, #4a90e2 0%, #17a2b8 100%);
        border-radius: 2px;
    }
    .action-buttons {
        background: white;
        padding: 1.5rem;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }
    .btn {
        padding: 0.6rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .btn-primary {
        background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(74,144,226,0.2);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(74,144,226,0.3);
    }
    .btn-secondary {
        background: #fff;
        color: #4a90e2;
        border: 2px solid #4a90e2;
    }
    .btn-secondary:hover {
        background: #4a90e2;
        color: #fff;
        transform: translateY(-2px);
    }
    .data-table {
        background: white;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .table {
        margin-bottom: 0;
    }
    .table thead th {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        font-weight: 600;
        border: none;
        padding: 1rem;
    }
    .table td {
        padding: 1rem;
        vertical-align: middle;
        border-color: #f0f0f0;
    }
    .btn-sm {
        padding: 0.4rem 1rem;
        font-size: 0.875rem;
    }
    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(40,167,69,0.2);
    }
    .btn-success:hover {
        transform: translateY(-2px);
    }
    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(220,53,69,0.2);
    }
    .btn-danger:hover {
        transform: translateY(-2px);
    }
    .btn-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border: none;
        box-sh
        adow: 0 4px 15px rgba(23,162,184,0.2);
    }
    .btn-info:hover {
        transform: translateY(-2px);
    }
    .alert {
        border-radius: 15px;
        padding: 1rem 1.5rem;
        margin-bottom: 2rem;
        border: none;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    }
    .status-badge {
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    .status-progress {
        background-color: #fff3cd;
        color: #856404;
    }
    .status-complete {
        background-color: #d4edda;
        color: #155724;
    }
    .status-rejected {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>

<div class="dashboard-container">
    <div class="container">
        <h1 class="page-title text-center">承認一覧</h1>
HTML;

// メッセージ表示
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8');
    $alertClass = (strpos($message, '却下') !== false) ? 'alert-danger' : 'alert-success';
    $ybase->ST_PRI .= "<div class='alert $alertClass'>" . $message . "</div>";
}

$ybase->ST_PRI .= <<<HTML
        <div class="action-buttons text-right">
            <a href='/yudaigroup/approval/index.php' class='btn btn-secondary'>
                <i class="fas fa-arrow-left mr-2"></i>申請TOPへ戻る
            </a>
        </div>

        <div class="data-table">
            <table class="table table-hover">
                <thead>
                    <tr align="center">
                        <th>申請者</th>
                        <th>書類名</th>
                        <th>ステータス</th>
                        <th>申請日時</th>
                        <th>操作</th>
                        <th>確認</th>
                        <th>Document ID</th>
                    </tr>
                </thead>
                <tbody>
HTML;

$applicant_id = $ybase->my_employee_num;

// [Previous query remains the same]
$queryDocuments = "
    WITH user_approval_order AS (
    SELECT ar.approval_order
    FROM approval_routes ar
    WHERE ar.applicant_id = '$applicant_id'
    AND ar.is_deleted = false
    LIMIT 1
),
document_groups AS (
    SELECT d.document_id, gm.group_id
    FROM documents d
    JOIN group_members gm ON d.applicant_id = gm.applicant_id
    WHERE gm.is_deleted = false
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
JOIN document_groups dg ON d.document_id = dg.document_id
JOIN approval_routes ar ON ar.group_id = dg.group_id
    AND ar.is_deleted = false
JOIN approval_status aps ON d.document_id = aps.document_id 
    AND aps.step_number = ar.approval_order
WHERE d.is_deleted = false
    AND EXISTS (
        SELECT 1
        FROM approval_routes ar2
        WHERE ar2.applicant_id = '$applicant_id'
        AND ar2.group_id = dg.group_id
        AND ar2.approval_order = aps.step_number
        AND ar2.is_deleted = false
    )
ORDER BY d.updated_at DESC;

";

$resultDocuments = pg_query($conn, $queryDocuments);

if (!$resultDocuments) {
    error_log("Error in query execution: " . pg_last_error($conn));
    $ybase->ST_PRI .= "<tr><td colspan='7' class='text-center'>エラーが発生しました。</td></tr>";
} else {
    $hasDocuments = false;

    while ($document = pg_fetch_assoc($resultDocuments)) {
        $hasDocuments = true;

        switch ($document['step_status']) {
            case 0:
                $statusClass = 'status-progress';
                $status = '進行中';
                break;
            case 1:
                $statusClass = 'status-complete';
                $status = '承認済み';
                break;
            case 2:
                $statusClass = 'status-rejected';
                $status = '却下';
                break;
            default:
                $statusClass = '';
                $status = '不明';
                break;
        }

        $updated_at = new DateTime($document['updated_at']);

        $ybase->ST_PRI .= "<tr align='center'>";
        $ybase->ST_PRI .= "<td>" . htmlspecialchars($document['applicant_name'], ENT_QUOTES, 'UTF-8') . "</td>";
        $ybase->ST_PRI .= "<td>" . htmlspecialchars($document['document_name'], ENT_QUOTES, 'UTF-8') . "</td>";
        $ybase->ST_PRI .= "<td><span class='status-badge {$statusClass}'>" . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . "</span></td>";
        $ybase->ST_PRI .= "<td>" . $updated_at->format('Y/m/d H:i') . "</td>";
        $ybase->ST_PRI .= "<td>
            <form action='approve.php?document_id={$document['document_id']}' method='post' class='d-inline'>
                <button type='submit' name='action' value='approved' class='btn btn-success btn-sm mr-2' " . ($document['step_status'] != 0 ? 'disabled' : '') . ">承認</button>
                <button type='submit' name='action' value='rejected' class='btn btn-danger btn-sm' " . ($document['step_status'] != 0 ? 'disabled' : '') . ">却下</button>
            </form>
        </td>";
        $ybase->ST_PRI .= "<td><a href='/yudaigroup/approval/document_detail.php?document_id={$document['document_id']}' class='btn btn-info btn-sm'>確認</a></td>";
        $ybase->ST_PRI .= "<td>" . htmlspecialchars($document['document_id'], ENT_QUOTES, 'UTF-8') . "</td>";
        $ybase->ST_PRI .= "</tr>";
    }

    if (!$hasDocuments) {
        $ybase->ST_PRI .= "<tr><td colspan='7' class='text-center text-muted'>承認対象の申請はありません。</td></tr>";
    }

    pg_free_result($resultDocuments);
}

$ybase->ST_PRI .= <<<HTML
                </tbody>
            </table>
        </div>
    </div>
</div>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
?>