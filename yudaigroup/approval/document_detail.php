<?php

include('../inc/ybase.inc');
include('document_detail_methods.php'); // 新しいファイルをインクルード

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();
$ybase->title = "書類詳細";
$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("書類詳細");

// URLパラメータからdocument_idを取得
$document_id = isset($_GET['document_id']) ? $_GET['document_id'] : null;

if ($document_id) {
    // 削除処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_document'])) {
        if (deleteDocument($conn, $document_id)) {
            echo "<p class='alert alert-success'>申請が取り消されました。</p>";
            header("Location: index.php");
            exit();
        } else {
            echo "Error in delete query execution.";
        }
    }

    // document_formatsテーブルから種別とcategory_nameを取得
    $formatData = getDocumentFormat($conn, $document_id);
    if ($formatData) {
        $formatName = htmlspecialchars($formatData['format_name'], ENT_QUOTES);
        $categoryName = htmlspecialchars($formatData['category_name'], ENT_QUOTES);
    } else {
        echo "Error in format query execution.";
        exit();
    }

    // document_valuesテーブルの項目と値を表示
    $valuesData = getDocumentValues($conn, $document_id);
    if ($valuesData) {
        $ybase->ST_PRI .= <<<HTML
        <div class="container my-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="mb-0">書類詳細</h3>
                </div>
                <div class="card-body p-4">
                    <table class="table table-hover">
                        <tbody>
                        <tr>
                            <th scope="row">カテゴリ</th>
                            <td>{$categoryName}</td>
                        </tr>
                        <tr>
                            <th scope="row">種別</th>
                            <td>{$formatName}</td>
                        </tr>
HTML;

        $radioValues = []; // ラジオボタンの値を一時的に保存する配列

        foreach ($valuesData as $valueRow) {
            $itemName = htmlspecialchars($valueRow['item_name'], ENT_QUOTES);
            $itemValue = htmlspecialchars($valueRow['value'], ENT_QUOTES);
            $itemType = $valueRow['item_type'];
            $titleName = htmlspecialchars($valueRow['title_name'], ENT_QUOTES);

            // 日時のフォーマットを変更
            if (!empty($itemValue) && ($itemType === 'datetime' || $itemType === 'date' || $itemType === 'time')) {
                $dateTime = new DateTime($itemValue);
                $itemValue = $dateTime->format('Y年n月j日G時i分');
            }

            if ($itemType === 'radio') {
                // ラジオボタンの項目は title_name を使用
                $titleId = $valueRow['title_id'];

                // ラジオボタンの値を一つだけ出力
                if (!isset($radioValues[$titleId])) {
                    $radioValues[$titleId] = $itemValue;
                    $ybase->ST_PRI .= <<<HTML
                        <tr>
                            <th scope="row">{$titleName}</th>
                            <td>{$itemValue}</td>
                        </tr>
HTML;
                }
            } else {
                // その他の項目は通常通り出力
                $ybase->ST_PRI .= <<<HTML
                        <tr>
                            <th scope="row">{$titleName} - {$itemName}</th>
                            <td>{$itemValue}</td>
                        </tr>
HTML;
            }
        }

        $ybase->ST_PRI .= <<<HTML
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between mt-4">
                        <form method="POST" action="document_detail.php?document_id={$document_id}">
                            <button type="submit" name="delete_document" class="btn btn-outline-danger">申請を取り消す</button>
                        </form>
                        <a href="index.php" class="btn btn-primary">一覧画面に戻る</a>
                    </div>
                </div>
            </div>
        </div>
HTML;
    } else {
        echo "Error in values query execution.";
    }
} else {
    echo "Invalid document ID.";
}

$ybase->HTMLfooter();
$ybase->priout();
?>