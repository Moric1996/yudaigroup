<?php

include('../../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();

$group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;

if ($group_id === 0) {
    echo "Invalid group ID.";
    exit;
}

// メンバーの追加処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    $applicant_id = $_POST['applicant_id'];

    // 既に存在するかチェック
    $queryCheckMember = "SELECT COUNT(*) FROM group_members WHERE group_id = $1 AND applicant_id = $2";
    $resultCheckMember = pg_query_params($conn, $queryCheckMember, array($group_id, $applicant_id));
    $count = pg_fetch_result($resultCheckMember, 0, 0);
    pg_free_result($resultCheckMember);

    if ($count == 0) {
        // 新しいレコードを挿入
        $queryAddMember = "INSERT INTO group_members (group_id, applicant_id, is_deleted) VALUES ($1, $2, 'f')";
        $resultAddMember = pg_query_params($conn, $queryAddMember, array($group_id, $applicant_id));

        if (!$resultAddMember) {
            error_log("Error in add member query execution: " . pg_last_error($conn));
            echo "Error in add member query execution: " . pg_last_error($conn);
        }
    } else {
        // 既存のレコードの is_deleted を 'f' に更新
        $queryUpdateMember = "UPDATE group_members SET is_deleted = 'f' WHERE group_id = $1 AND applicant_id = $2";
        $resultUpdateMember = pg_query_params($conn, $queryUpdateMember, array($group_id, $applicant_id));

        if (!$resultUpdateMember) {
            error_log("Error in update member query execution: " . pg_last_error($conn));
            echo "Error in update member query execution: " . pg_last_error($conn);
        }
    }
}

// メンバーの削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_member'])) {
    $applicant_id = $_POST['applicant_id'];
    $queryDeleteMember = "UPDATE group_members SET is_deleted = 't' WHERE group_id = $1 AND applicant_id = $2";
    $resultDeleteMember = pg_query_params($conn, $queryDeleteMember, array($group_id, $applicant_id));

    if (!$resultDeleteMember) {
        error_log("Error in delete member query execution: " . pg_last_error($conn));
        echo "Error in delete member query execution: " . pg_last_error($conn);
    }
}

// 処理が完了したら元のページにリダイレクト
header("Location: group_members.php?group_id=$group_id");
exit;
?>