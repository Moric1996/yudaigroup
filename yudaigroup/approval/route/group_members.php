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
    echo "Error in query execution: " . pg_last_error($conn);
    exit;
}

$groupNameRow = pg_fetch_assoc($resultGroupName);
$groupName = htmlspecialchars($groupNameRow['group_name'], ENT_QUOTES, 'UTF-8');
pg_free_result($resultGroupName);

$ybase->title = "メンバー一覧 - $groupName";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("メンバー一覧 - $groupName");

$ybase->ST_PRI .= <<<HTML
<div class="container mt-5">
    <h1 class="mb-4 text-center">メンバー一覧 - $groupName</h1>
HTML;

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
    echo "Error in query execution: " . pg_last_error($conn);
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
    pg_free_result($resultMembers);  // Free the result for clean-up
}

// 全メンバーを取得してドロップダウンリストに表示
$queryAllMembers = "SELECT mem_id, name FROM member WHERE mem_id NOT IN (SELECT applicant_id FROM group_members WHERE group_id = $1 AND is_deleted = 'f') ORDER BY name ASC";
$resultAllMembers = pg_query_params($conn, $queryAllMembers, array($group_id));

if (!$resultAllMembers) {
    error_log("Error in query execution: " . pg_last_error($conn));
    echo "Error in query execution: " . pg_last_error($conn);
} else {
    $ybase->ST_PRI .= <<<HTML
    <div class="mt-4">
        <form method="POST" action="group_members_action.php">
            <input type="hidden" name="group_id" value="$group_id">
            <div class="form-group">
                <label for="applicant_id">メンバーを追加:</label>
                <select name="applicant_id" id="applicant_id" class="form-control">
                    <option value="">メンバーを選択してください</option>
HTML;
    while ($row = pg_fetch_assoc($resultAllMembers)) {
        $memberId = htmlspecialchars($row['mem_id'], ENT_QUOTES, 'UTF-8');
        $memberName = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
        $ybase->ST_PRI .= <<<HTML
                    <option value="$memberId">$memberName</option>
HTML;
    }
    $ybase->ST_PRI .= <<<HTML
                </select>
            </div>
            <button type="submit" name="add_member" class="btn btn-primary">追加</button>
        </form>
    </div>
HTML;
    pg_free_result($resultAllMembers);  // Free the result for clean-up
}

$ybase->ST_PRI .= <<<HTML
    <div class="mt-4 text-center">
        <a href="group.php" class="btn btn-primary">事業部一覧に戻る</a>
    </div>
</div>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
?>

<!-- s -->