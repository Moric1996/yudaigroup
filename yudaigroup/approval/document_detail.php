<?php
include('../inc/ybase.inc');
include('document_detail_methods.php');

$ybase = new ybase();
$ybase->session_get();

$conn = $ybase->connect();
$ybase->title = "書類詳細";
$ybase->HTMLheader();
$ybase->ST_PRI .= $ybase->header_pri("書類詳細");

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

    // document_valuesテーブルの項目と値を取得
    $valuesData = getDocumentValues($conn, $document_id);
    if ($valuesData) {
        // title_nameでグループ化するための配列
        $groupedValues = [];
        
        // データを title_name でグループ化
        foreach ($valuesData as $valueRow) {
            if (empty($valueRow['value'])) continue;
            
            $titleName = htmlspecialchars($valueRow['title_name'], ENT_QUOTES);
            $itemName = htmlspecialchars($valueRow['item_name'], ENT_QUOTES);
            $itemValue = htmlspecialchars($valueRow['value'], ENT_QUOTES);
            $itemType = $valueRow['item_type'];
            
            // 日時のフォーマット処理
            if ($itemType === 'datetime' || $itemType === 'date' || $itemType === 'time') {
                $dateTime = new DateTime($itemValue);
                $itemValue = $dateTime->format('Y年n月j日G時i分');
            }
            
            if ($itemType === 'radio') {
                // ラジオボタンの場合は値だけを保存
                $groupedValues[$titleName] = $itemValue;
            } else {
                // その他の場合は配列として値を保存
                if (!isset($groupedValues[$titleName])) {
                    $groupedValues[$titleName] = [];
                }
                $groupedValues[$titleName][] = [
                    'item_name' => $itemName,
                    'value' => $itemValue
                ];
            }
        }

        // HTMLの出力
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

        // グループ化したデータの表示
        foreach ($groupedValues as $titleName => $values) {
            if (is_array($values)) {
                // 複数の値がある場合
                $valueStr = '';
                foreach ($values as $v) {
                    $valueStr .= "{$v['item_name']}: {$v['value']}<br>";
                }
                $ybase->ST_PRI .= <<<HTML
                        <tr>
                            <th scope="row">{$titleName}</th>
                            <td>{$valueStr}</td>
                        </tr>
HTML;
            } else {
                // ラジオボタンなど単一の値の場合
                $ybase->ST_PRI .= <<<HTML
                        <tr>
                            <th scope="row">{$titleName}</th>
                            <td>{$values}</td>
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