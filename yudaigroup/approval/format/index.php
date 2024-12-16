<?php

include('../../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();

$ybase->title = "編集フォーマット一覧";

$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("編集フォーマット一覧");

$ybase->ST_PRI .= <<<HTML

<style>
    .custom-gradient {
        background: linear-gradient(135deg, #6B73FF 0%, #000DFF 100%);
        color: white;
    }
    
    .page-header {
        padding: 3rem 0;
        margin-bottom: 2rem;
        text-align: center;
    }
    
    .card {
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }
    
    .card:hover {
        transform: translateY(-5px);
    }
    
    .card-header {
        background: linear-gradient(45deg, #f3f4f6 0%, #ffffff 100%);
        border-bottom: 2px solid #e5e7eb;
    }
    
    .list-group-item {
        border-left: none;
        border-right: none;
        transition: background-color 0.2s;
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
    
    .btn-primary {
        background: linear-gradient(45deg, #4776E6 0%, #8E54E9 100%);
        border: none;
        padding: 0.8rem 2rem;
        transition: all 0.3s;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(71, 118, 230, 0.4);
    }
    
    .btn-danger {
        background: linear-gradient(45deg, #ff6b6b 0%, #ff8e8e 100%);
        border: none;
    }
    
    .btn-danger:hover {
        background: linear-gradient(45deg, #ff5252 0%, #ff7676 100%);
    }
    
    .format-link {
        color: #4776E6;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }
    
    .format-link:hover {
        color: #8E54E9;
    }
</style>

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