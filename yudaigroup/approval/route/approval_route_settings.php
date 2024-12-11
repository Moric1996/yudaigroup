<?php

include('../../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();

$ybase->title = "承認ルート設定";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("承認ルート設定");

$ybase->ST_PRI .= <<<HTML
<div class="container mt-5">
    <h1 class="mb-4 text-center">承認ルート設定</h1>
    <form method="GET" action="approval_route_settings.php">
        <div class="form-group">
            <label for="group_id">事業部を選択:</label>
            <select name="group_id" id="group_id" class="form-control" onchange="this.form.submit()">
                <option value="">事業部を選択してください</option>
HTML;

// approval_groups テーブルからデータを取得
$queryGroups = "SELECT group_id, group_name FROM approval_groups WHERE is_deleted = 'f' ORDER BY group_name ASC";
$resultGroups = pg_query($conn, $queryGroups);

if (!$resultGroups) {
    error_log("Error in query execution: " . pg_last_error($conn));
    echo "Error in query execution: " . pg_last_error($conn);
} else {
    while ($row = pg_fetch_assoc($resultGroups)) {
        $groupId = htmlspecialchars($row['group_id'], ENT_QUOTES, 'UTF-8');
        $groupName = htmlspecialchars($row['group_name'], ENT_QUOTES, 'UTF-8');
        $selected = (isset($_GET['group_id']) && $_GET['group_id'] == $groupId) ? 'selected' : '';
        $ybase->ST_PRI .= <<<HTML
                <option value="$groupId" $selected>$groupName</option>
HTML;
    }
    pg_free_result($resultGroups);  // Free the result for clean-up
}

$ybase->ST_PRI .= <<<HTML
            </select>
        </div>
    </form>
HTML;

if (isset($_GET['group_id']) && intval($_GET['group_id']) > 0) {
    $group_id = intval($_GET['group_id']);

    // 現在の承認ルートに設定されているメンバーIDを取得
    $queryExistingMembers = "
        SELECT m.mem_id
        FROM approval_routes ar
        JOIN member m ON ar.applicant_id = m.mem_id
        WHERE ar.group_id = $1 AND ar.is_deleted = 'f'
    ";
    $resultExistingMembers = pg_query_params($conn, $queryExistingMembers, array($group_id));

    $existingMemberIds = [];
    if ($resultExistingMembers) {
        while ($row = pg_fetch_assoc($resultExistingMembers)) {
            $existingMemberIds[] = $row['mem_id'];
        }
        pg_free_result($resultExistingMembers);
    }

    // 除外条件を作成
    $excludeIds = !empty($existingMemberIds) ? "'" . implode("','", array_map('pg_escape_string', $existingMemberIds)) . "'" : "''";

    // 除外条件を含めてメンバーを取得
    $queryAllMembers = "
        SELECT mem_id, name
        FROM member
        WHERE mem_id NOT IN ($excludeIds)
        ORDER BY name ASC
    ";
    $resultAllMembers = pg_query($conn, $queryAllMembers);

    $options = '';
    if (!$resultAllMembers) {
        error_log("Error in query execution: " . pg_last_error($conn));
        echo "Error in query execution: " . pg_last_error($conn);
    } else {
        while ($row = pg_fetch_assoc($resultAllMembers)) {
            $memberId = htmlspecialchars($row['mem_id'], ENT_QUOTES, 'UTF-8');
            $memberName = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
            $options .= '<option value="' . $memberId . '">' . $memberName . '</option>';
        }
        pg_free_result($resultAllMembers);
    }

    // 承認ルートを取得
    $queryRoutes = "
        SELECT ar.approval_order, m.name
        FROM approval_routes ar
        JOIN member m ON ar.applicant_id = m.mem_id
        WHERE ar.group_id = $1 AND ar.is_deleted = 'f'
        ORDER BY ar.approval_order ASC
    ";
    $resultRoutes = pg_query_params($conn, $queryRoutes, array($group_id));

    if (!$resultRoutes) {
        error_log("Error in query execution: " . pg_last_error($conn));
        echo "Error in query execution: " . pg_last_error($conn);
    } else {
        $ybase->ST_PRI .= <<<HTML
        <h2 class="mt-4">現在の承認ルート</h2>
        <ul class="list-group mb-4">
HTML;
        while ($row = pg_fetch_assoc($resultRoutes)) {
            $approvalOrder = htmlspecialchars($row['approval_order'], ENT_QUOTES, 'UTF-8');
            $memberName = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
            $ybase->ST_PRI .= <<<HTML
            <li class="list-group-item">第{$approvalOrder}承認者: $memberName</li>
HTML;
        }
        $ybase->ST_PRI .= <<<HTML
        </ul>
HTML;
        pg_free_result($resultRoutes);  // Free the result for clean-up
    }

    // 承認者の追加フォーム
    $ybase->ST_PRI .= <<<HTML
    <h2 class="mt-4">承認者を追加</h2>
    <form method="POST" action="approval_route_action.php">
        <input type="hidden" name="group_id" value="$group_id">
        <div id="approvers-container">
            <div class="form-group">
                <label for="approval_order_1">第1承認者:</label>
                <select name="approval_order[]" id="approval_order_1" class="form-control">
                    <option value="">承認者を選択してください</option>
                    {$options}  <!-- ここに $options を追加 -->
                </select>
            </div>
        </div>
        <button type="button" class="btn btn-secondary" onclick="addApprover()">承認者を追加</button>
        <button type="submit" class="btn btn-primary">登録</button>
    </form>
HTML;
}

// PHP部分
$queryAllMembers = "SELECT mem_id, name FROM member ORDER BY name ASC";
$resultAllMembers = pg_query($conn, $queryAllMembers);

$options = '';
if (!$resultAllMembers) {
    error_log("Error in query execution: " . pg_last_error($conn));
    echo "Error in query execution: " . pg_last_error($conn);
} else {
    while ($row = pg_fetch_assoc($resultAllMembers)) {
        $memberId = htmlspecialchars($row['mem_id'], ENT_QUOTES, 'UTF-8');
        $memberName = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
        $options .= '<option value="' . $memberId . '">' . $memberName . '</option>';
    }
    pg_free_result($resultAllMembers);  // Free the result for clean-up
}

$ybase->ST_PRI .= <<<HTML
    <div class="mt-4 text-center">
        <a href="group.php" class="btn btn-primary">事業部一覧に戻る</a>
    </div>
</div>

<script type="text/javascript">
let approverCount = 1;
const memberOptions = `$options`;  // PHP の $options 変数を JavaScript の定数として保持

function addApprover() {
    approverCount++;
    const container = document.getElementById("approvers-container");
    const newApprover = document.createElement("div");
    newApprover.className = "form-group";
    newApprover.innerHTML = `
        <label for="approval_order_\${approverCount}">第\${approverCount}承認者:</label>
        <select name="approval_order[]" id="approval_order_\${approverCount}" class="form-control">
            <option value="">承認者を選択してください</option>
            \${memberOptions}
        </select>
    `;
    container.appendChild(newApprover);
}
</script>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
?>

<!-- あわわあげｔげｗ -->