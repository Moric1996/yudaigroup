<?php
include "../AdminLTE/class/ClassLoader.php";
$dbio = new DBIO();

$shop_id = $_POST['shop_id'];
$target_date = $_POST['target_date'];




$comment_list['revenue_comment'] = htmlspecialchars($_POST['revenueComment']);
$comment_list['food_purchase_comment'] = htmlspecialchars($_POST['foodPurchaseComment']);
$comment_list['labor_cost_comment'] = htmlspecialchars($_POST['laborCostComment']);
$comment_list['other_comment'] = htmlspecialchars($_POST['otherComment']);






if ($dbio->upsertDescComment($shop_id, $target_date, $comment_list)) {
    return true;
}
return false;