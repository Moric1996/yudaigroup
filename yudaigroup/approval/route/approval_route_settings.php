<?php
include('../../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();
$conn = $ybase->connect();
$ybase->title = "承認ルート設定";
$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("承認ルート設定");


// Custom CSS and required libraries
$ybase->ST_PRI .= <<<HTML
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #2afaa3 0%, #4f46e5 100%);
        --secondary-gradient: linear-gradient(135deg, #f8f9fa 0%,rgb(239, 233, 233) 100%);
        --card-shadow: 0 8px 24px rgba(149, 157, 165, 0.1);
        --transition-speed: 0.3s;
    }

    .main-container {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 2rem 1rem;
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
        background: linear-gradient(135deg, #2afaa3 0%, #4f46e5 100%);
        border-radius: 2px;
    }
    .page-header {
        background: var(--primary-gradient);
        color: white;
        padding: 2.5rem 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
        text-align: center;
    }

    .form-card {
        background: white;
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        padding: 2rem;
        margin-bottom: 2rem;
        border: none;
        transition: transform var(--transition-speed);
    }

    .form-card:hover {
        transform: translateY(-2px);
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        transition: all var(--transition-speed);
    }

    .form-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
    }

    .form-group label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.75rem;
    }

    .approval-list {
        padding-left: 1rem;
    }

    .approval-item {
        background:rgb(248, 252, 249);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        transition: all var(--transition-speed);
    }

    .approval-item:hover {
        background: #f1f5f9;
        transform: translateX(5px);
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 500;
        transition: all var(--transition-speed);
    }

    .btn-primary {
        background: var(--primary-gradient);
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    }

    .btn-secondary {
        background: white;
        color: #4a90e2;
        border: 2px solid #4a90e2;
    }

    .btn-secondary:hover {
        transform: translateY(-2px);
        background: #e9ecef;
    }

    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem 1rem;
        }

        .form-card {
            padding: 1.5rem;
        }
    }

    .section-title {
        position: relative;
        color: #2d3748;
        font-weight: 600;
        padding-left: 1rem;

    }

    .section-title::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--primary-gradient);
        border-radius: 2px;
    }

    .badge-gradient {
    display: inline-block;
    padding: 0.5em 1em;
    font-size: 0.875rem;
    font-weight: 600;
    color: #fff;
    background: linear-gradient(45deg, #6a11cb, #2575fc); /* 紫から青のグラデーション */
    border-radius: 0.25rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
}

</style>

<div class="main-container">
    <div class="container">
    <h1 class="page-title text-center">承認ルート設定</h1>

        <div class="form-card animate__animated animate__fadeIn">
            <form method="GET" action="approval_route_settings.php">
                <div class="form-group">
                    <label for="group_id">事業部を選択</label>
                    <select name="group_id" id="group_id" class="form-control" onchange="this.form.submit()">
                        <option value="">事業部を選択してください</option>
HTML;

// Approval groups query
$queryGroups = "SELECT group_id, group_name FROM approval_groups WHERE is_deleted = 'f' ORDER BY group_name ASC";
$resultGroups = pg_query($conn, $queryGroups);

if ($resultGroups) {
    while ($row = pg_fetch_assoc($resultGroups)) {
        $groupId = htmlspecialchars($row['group_id'], ENT_QUOTES, 'UTF-8');
        $groupName = htmlspecialchars($row['group_name'], ENT_QUOTES, 'UTF-8');
        $selected = (isset($_GET['group_id']) && $_GET['group_id'] == $groupId) ? 'selected' : '';
        $ybase->ST_PRI .= "<option value=\"$groupId\" $selected>$groupName</option>";
    }
    pg_free_result($resultGroups);
}

$ybase->ST_PRI .= <<<HTML
                    </select>
                </div>
            </form>
        </div>
HTML;

if (isset($_GET['group_id']) && intval($_GET['group_id']) > 0) {
    $group_id = intval($_GET['group_id']);

    // Get existing approval routes
$queryRoutes = "
SELECT ar.approval_order, m.name
FROM approval_routes ar
JOIN member m ON ar.applicant_id = m.mem_id
WHERE ar.group_id = $1 AND ar.is_deleted = 'f'
ORDER BY ar.approval_order ASC
";
$resultRoutes = pg_query_params($conn, $queryRoutes, array($group_id));

if ($resultRoutes) {
$ybase->ST_PRI .= <<<HTML
<div class="form-card animate__animated animate__fadeIn">
    <h3 class="section-title mb-4">現在の承認ルート</h3>
    <div class="approval-list">
HTML;
while ($row = pg_fetch_assoc($resultRoutes)) {
    $approvalOrder = htmlspecialchars($row['approval_order'], ENT_QUOTES, 'UTF-8');
    $memberName = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
    $ybase->ST_PRI .= <<<HTML
        <div class="approval-item">
        <span class="badge badge-gradient mr-2">第{$approvalOrder}承認者</span>
            <span class="font-weight-medium">{$memberName}</span>
        </div>
HTML;
}
$ybase->ST_PRI .= "</div></div>";
pg_free_result($resultRoutes);
}

// データベースからメンバー情報を取得（PHP側）
$queryAllMembers = "
SELECT mem_id, name
FROM member
ORDER BY name ASC
";
$resultAllMembers = pg_query($conn, $queryAllMembers);

$options = '';
if ($resultAllMembers) {
    while ($row = pg_fetch_assoc($resultAllMembers)) {
        $memberId = htmlspecialchars($row['mem_id'], ENT_QUOTES, 'UTF-8');
        $memberName = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
        $options .= "<option value=\"$memberId\">$memberName</option>";
    }
    pg_free_result($resultAllMembers);
}

// 承認者編集フォームの基本構造のみを出力
$ybase->ST_PRI .= <<<HTML
    <div class="form-card animate__animated animate__fadeIn">
    <h3 class="section-title mb-4">承認者を編集</h3>
        <form method="POST" action="approval_route_action.php">
            <input type="hidden" name="group_id" value="$group_id">
            <div id="approvers-container">
            </div>
            <div class="mt-4">
                <button type="button" class="btn btn-secondary mr-2" onclick="addApprover()">
                    <i class="fas fa-plus mr-2"></i>承認者を追加
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>登録
                </button>
            </div>
        </form>
    </div>
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
        <div class="text-center mt-4">
            <a href="group.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>事業部一覧に戻る
            </a>
        </div>
    </div>
</div>

<script>
let approverCount = 0;
const memberOptions = `$options`;

// 承認者セレクトボックスの生成関数
function createApproverSelect(number) {
    const container = document.createElement('div');
    container.className = 'form-group animate__animated animate__fadeIn';
    container.innerHTML = `
        <label for="approval_order_\${number}">第\${number}承認者</label>
        <div class="d-flex">
            <select name="approval_order[]" id="approval_order_\${number}" class="form-control mr-2">
                <option value="">承認者を選択してください</option>
                \${memberOptions}
            </select>
        </div>
    `;
    return container;
}

// 承認者追加
function addApprover() {
    approverCount++;
    const container = document.getElementById("approvers-container");
    const newApprover = createApproverSelect(approverCount);
    container.appendChild(newApprover);
    
    const newSelect = newApprover.querySelector('select');
    newSelect.addEventListener('change', updateApproverOptions);
    updateApproverOptions();
}

// 初期設定
document.addEventListener('DOMContentLoaded', () => {
    // 最初の承認者を追加
    addApprover();
});
// 全選択肢の初期データを保持
const allApprovers = Array.from(new DOMParser().parseFromString(memberOptions, 'text/html')
    .querySelectorAll('option'))
    .map(option => ({
        value: option.value,
        text: option.textContent
    }));

// 現在選択されている承認者のIDを取得
function getSelectedApprovers() {
    return Array.from(document.querySelectorAll('select[name="approval_order[]"]'))
        .map(select => select.value)
        .filter(value => value !== '');
}

// 選択肢を更新
function updateApproverOptions() {
    const selectedIds = getSelectedApprovers();
    const selects = document.querySelectorAll('select[name="approval_order[]"]');
    
    selects.forEach((select, index) => {
        const currentValue = select.value;
        // 一旦すべての選択肢をクリア
        select.innerHTML = '<option value="">承認者を選択してください</option>';
        
        // 選択可能な承認者のみを追加
        allApprovers.forEach(approver => {
            // 他の選択肢で選択されていない、または現在の選択肢である場合のみ表示
            if (!selectedIds.includes(approver.value) || approver.value === currentValue) {
                const option = document.createElement('option');
                option.value = approver.value;
                option.textContent = approver.text;
                option.selected = approver.value === currentValue;
                select.appendChild(option);
            }
        });
    });
}

// 初期設定
document.addEventListener('DOMContentLoaded', () => {
    // 最初の選択肢にイベントリスナーを設定
    const firstSelect = document.querySelector('select[name="approval_order[]"]');
    firstSelect.addEventListener('change', updateApproverOptions);
});
</script>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
?>

<!-- あわわあげｔげｗ -->