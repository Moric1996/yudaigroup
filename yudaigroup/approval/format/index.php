<?php

include('../../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();

$ybase->title = "編集フォーマット一覧";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("編集フォーマット一覧");

$ybase->ST_PRI .= <<<HTML
<div class="container mt-5">
    <h1 class="mb-4">編集フォーマット一覧</h1>
    <a href="create.php" class="btn btn-primary mb-4">新規フォーマット作成</a>
HTML;

// カテゴリを取得
$queryCategories = "SELECT * FROM document_format_categories WHERE is_deleted = 'f' ORDER BY category_name ASC";
$resultCategories = pg_query($conn, $queryCategories);

if (!$resultCategories) {
    error_log("Error in query execution: " . pg_last_error($conn));
    echo "Error in query execution: " . pg_last_error($conn);
} else {
    // フォーマットを取得
    $queryFormats = "SELECT * FROM document_formats WHERE is_deleted = 'f'";
    $resultFormats = pg_query($conn, $queryFormats);

    if (!$resultFormats) {
        error_log("Error in query execution: " . pg_last_error($conn));
        echo "Error in query execution: " . pg_last_error($conn);
    } else {
        // カテゴリごとにフォーマットを整理
        $categoryFormats = [];
        while ($format = pg_fetch_assoc($resultFormats)) {
            $categoryFormats[$format['category_id']][] = [
                'name' => $format['name'],
                'format_id' => $format['format_id']
            ];
        }

        // カテゴリごとに表示
        while ($category = pg_fetch_assoc($resultCategories)) {
            $categoryName = htmlspecialchars($category['category_name'], ENT_QUOTES, 'UTF-8');
            $ybase->ST_PRI .= <<<HTML
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">$categoryName</h2>
                </div>
                <div class="card-body">
HTML;
            if (isset($categoryFormats[$category['category_id']])) {
                $ybase->ST_PRI .= "<ul class='list-group'>";
                foreach ($categoryFormats[$category['category_id']] as $format) {
                    $formatName = htmlspecialchars($format['name'], ENT_QUOTES, 'UTF-8');
                    $formatId = htmlspecialchars($format['format_id'], ENT_QUOTES, 'UTF-8');
                    $ybase->ST_PRI .= <<<HTML
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="format_detail.php?format_id={$formatId}">$formatName</a>
                        <form method="post" action="delete_format.php" onsubmit="return confirm('本当に削除しますか？');">
                            <input type="hidden" name="format_id" value="$formatId">
                            <button type="submit" class="btn btn-danger btn-sm">削除</button>
                        </form>
                    </li>
HTML;
                }
                $ybase->ST_PRI .= "</ul>";
            } else {
                $ybase->ST_PRI .= "<p class='text-muted'>フォーマットがありません。</p>";
            }
            $ybase->ST_PRI .= <<<HTML
                </div>
            </div>
HTML;
        }

        pg_free_result($resultFormats);  // Free the result for clean-up
    }

    pg_free_result($resultCategories);  // Free the result for clean-up
}

$ybase->ST_PRI .= <<<HTML
</div>
HTML;

$ybase->HTMLfooter();
$ybase->priout();
?>