<?php
include('../../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();

$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

if ($group_id === 0) {
    echo "Invalid group ID.";
    exit;
}

// グループ名を取得
$queryGroupName = "SELECT group_name FROM approval_groups WHERE group_id = $1 AND is_deleted = 'f'";
$resultGroupName = pg_query_params($conn, $queryGroupName, array($group_id));

if (!$resultGroupName) {
    error_log("Error in query execution: " . pg_last_error($conn));
    exit;
}

$groupNameRow = pg_fetch_assoc($resultGroupName);
$groupName = htmlspecialchars($groupNameRow['group_name'], ENT_QUOTES, 'UTF-8');
pg_free_result($resultGroupName);

$ybase->title = "メンバー一覧 - $groupName";
$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("メンバー一覧 - $groupName");

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
        background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
        border-radius: 2px;
    }
    .member-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        padding: 2rem;
        margin-bottom: 2rem;
    }
    .list-group {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    }
    .list-group-item {
        border-left: none;
        border-right: none;
        padding: 1rem 1.5rem;
        transition: all 0.3s ease;
    }
    .list-group-item:first-child {
        border-top: none;
    }
    .list-group-item:last-child {
        border-bottom: none;
    }
    .list-group-item:hover {
        background-color: #f8f9fa;
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
    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(220,53,69,0.2);
    }
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220,53,69,0.3);
    }
    .btn-sm {
        padding: 0.4rem 1rem;
        font-size: 0.875rem;
    }
    .form-control {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    .form-control:focus {
        border-color: #4a90e2;
        box-shadow: 0 0 0 0.2rem rgba(74,144,226,0.25);
    }
    .action-buttons {
        background: white;
        padding: 1.5rem;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }
</style>

<div class="dashboard-container">
    <div class="container">
        <h1 class="page-title text-center">メンバー一覧 - {$groupName}</h1>
        
        <div class="member-card">
HTML;

// グループ名の編集フォームを追加
$ybase->ST_PRI .= <<<HTML
        <form method="POST" action="group_members_action.php" class="mb-4">
            <input type="hidden" name="group_id" value="$group_id">
            <div class="form-group">
                <label for="group_name" class="font-weight-bold mb-2">グループ名を編集:</label>
                <div class="d-flex">
                    <input type="text" name="group_name" id="group_name" class="form-control mr-2" value="$groupName" required>
                    <button type="submit" name="edit_group_name" class="btn btn-primary">更新</button>
                </div>
            </div>
        </form>
HTML;

// 全メンバーを取得してドロップダウンリストに表示
$queryAllMembers = "SELECT mem_id, name FROM member WHERE mem_id NOT IN (SELECT applicant_id FROM group_members WHERE group_id = $1 AND is_deleted = 'f') ORDER BY name ASC";
$resultAllMembers = pg_query_params($conn, $queryAllMembers, array($group_id));

if (!$resultAllMembers) {
    error_log("Error in query execution: " . pg_last_error($conn));
    $ybase->ST_PRI .= "<div class='alert alert-danger'>エラーが発生しました。</div>";
} else {
    $ybase->ST_PRI .= <<<HTML
        <div class="mt-4">
            <form method="POST" action="group_members_action.php" class="member-form">
                <input type="hidden" name="group_id" value="$group_id">
                <div class="form-group">
                    <label for="applicant_id" class="font-weight-bold mb-2">メンバーを追加:</label>
                    <div class="d-flex">
                        <select name="applicant_id" id="applicant_id" class="form-control mr-2">
                            <option value="">メンバーを選択してください</option>
HTML;
    while ($row = pg_fetch_assoc($resultAllMembers)) {
        $memberId = htmlspecialchars($row['mem_id'], ENT_QUOTES, 'UTF-8');
        $memberName = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
        $ybase->ST_PRI .= "<option value=\"$memberId\">$memberName</option>";
    }
    $ybase->ST_PRI .= <<<HTML
                        </select>
                        <button type="submit" name="add_member" class="btn btn-primary">追加</button>
                    </div>
                </div>
            </form>
        </div>
HTML;
    pg_free_result($resultAllMembers);
}
// メンバーを取得
$queryMembers = "
    SELECT m.name, m.mem_id
    FROM group_members gm
    JOIN member m ON gm.applicant_id = m.mem_id
    WHERE gm.group_id = $1 AND gm.is_deleted = 'f'
";
$resultMembers = pg_query_params($conn, $queryMembers, array($group_id));

if (!$resultMembers) {
    error_log("Error in query execution: " . pg_last_error($conn));
    $ybase->ST_PRI .= "<div class='alert alert-danger'>エラーが発生しました。</div>";
} else {
    $ybase->ST_PRI .= "<ul class='list-group'>";
    $existingMemberIds = [];
    while ($row = pg_fetch_assoc($resultMembers)) {
        $memberName = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
        $memberId = htmlspecialchars($row['mem_id'], ENT_QUOTES, 'UTF-8');
        $existingMemberIds[] = $memberId;
        $ybase->ST_PRI .= <<<HTML
        <li class="list-group-item d-flex justify-content-between align-items-center">
            $memberName
            <form method="POST" action="group_members_action.php" class="mb-0">
                <input type="hidden" name="group_id" value="$group_id">
                <input type="hidden" name="applicant_id" value="$memberId">
                <button type="submit" name="delete_member" class="btn btn-danger btn-sm">削除</button>
            </form>
        </li>
HTML;
    }
    $ybase->ST_PRI .= "</ul>";
    pg_free_result($resultMembers);
}


$ybase->ST_PRI .= <<<HTML
        </div>
        
        <div class="action-buttons text-center">
            <a href="group.php" class="btn btn-primary">事業部一覧に戻る</a>
        </div>
    </div>
</div>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
?>