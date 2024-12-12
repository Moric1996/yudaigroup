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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_document'])) {
        if (deleteDocument($conn, $document_id)) {
            echo "<p class='alert alert-success'>申請が取り消されました。</p>";
            header("Location: index.php");
            exit();
        } else {
            echo "Error in delete query execution.";
        }
    }

    $formatData = getDocumentFormat($conn, $document_id);
    if ($formatData) {
        $formatName = htmlspecialchars($formatData['format_name'], ENT_QUOTES);
        $categoryName = htmlspecialchars($formatData['category_name'], ENT_QUOTES);
    } else {
        echo "Error in format query execution.";
        exit();
    }

    $valuesData = getDocumentValues($conn, $document_id);
    if ($valuesData) {
        $groupedValues = [];
        
        foreach ($valuesData as $valueRow) {
            if (empty($valueRow['value'])) continue;
            
            $titleName = htmlspecialchars($valueRow['title_name'], ENT_QUOTES);
            $itemName = htmlspecialchars($valueRow['item_name'], ENT_QUOTES);
            $itemValue = htmlspecialchars($valueRow['value'], ENT_QUOTES);
            $itemType = $valueRow['item_type'];
            
            if ($itemType === 'datetime' || $itemType === 'date' || $itemType === 'time') {
                $dateTime = new DateTime($itemValue);
                $itemValue = $dateTime->format('Y年n月j日G時i分');
            }
            
            if ($itemType === 'radio') {
                $groupedValues[$titleName] = $itemValue;
            } else {
                if (!isset($groupedValues[$titleName])) {
                    $groupedValues[$titleName] = [];
                }
                $groupedValues[$titleName][] = [
                    'item_name' => $itemName,
                    'value' => $itemValue
                ];
            }
        }

        // スタイルシートの追加
        $ybase->ST_PRI .= <<<HTML
        <style>
            .document-card {
                border: none;
                border-radius: 15px;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            .card-header {
                background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
                border-radius: 15px 15px 0 0 !important;
                padding: 1.5rem;
            }
            .card-header h3 {
                font-weight: 600;
                letter-spacing: 1px;
            }
            .card-body {
                padding: 2rem;
            }
            .table {
                margin-bottom: 0;
            }
            .table th {
                background-color: #f8f9fa;
                font-weight: 600;
                color: #2c3e50;
                border-left: 4px solid #4a90e2;
                padding: 1rem;
            }
            .table td {
                padding: 1rem;
                color: #34495e;
                line-height: 1.6;
            }
            .table tr:hover {
                background-color: #f8f9fa;
                transition: background-color 0.2s ease;
            }
            .btn {
                padding: 0.6rem 1.5rem;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            .btn-outline-danger {
                border-width: 2px;
            }
            .btn-outline-danger:hover {
                background-color: #dc3545;
                transform: translateY(-2px);
            }
            .btn-primary {
                background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
                border: none;
            }
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(74,144,226,0.3);
            }
            .value-item {
                padding: 0.3rem 0;
            }
            .category-badge {
                display: inline-block;
                padding: 0.4rem 1rem;
                background-color: #e9ecef;
                border-radius: 20px;
                color: #2c3e50;
                font-weight: 500;
            }
        </style>

        <div class="container my-5">
            <div class="card document-card">
                <div class="card-header text-white">
                    <h3 class="mb-0">書類詳細</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tbody>
                        <tr>
                            <th scope="row">カテゴリ</th>
                            <td><span class="category-badge">{$categoryName}</span></td>
                        </tr>
                        <tr>
                            <th scope="row">種別</th>
                            <td><span class="category-badge">{$formatName}</span></td>
                        </tr>
HTML;

        foreach ($groupedValues as $titleName => $values) {
            if (is_array($values)) {
                $valueStr = '<div class="value-items">';
                foreach ($values as $v) {
                    $valueStr .= "<div class='value-item'><strong>{$v['item_name']}:</strong> {$v['value']}</div>";
                }
                $valueStr .= '</div>';
                $ybase->ST_PRI .= <<<HTML
                        <tr>
                            <th scope="row">{$titleName}</th>
                            <td>{$valueStr}</td>
                        </tr>
HTML;
            } else {
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
                    <div class="d-flex justify-content-between mt-5">
                        <form method="POST" action="document_detail.php?document_id={$document_id}" class="delete-form">
                            <button type="submit" name="delete_document" class="btn btn-outline-danger">
                                <i class="fas fa-trash-alt mr-2"></i>申請を取り消す
                            </button>
                        </form>
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left mr-2"></i>一覧画面に戻る
                        </a>
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