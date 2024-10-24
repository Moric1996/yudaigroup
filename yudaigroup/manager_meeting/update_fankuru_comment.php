<?php
include "../AdminLTE/class/ClassLoader.php";
$dbio = new DBIO();

$shop_id = $_POST['shop_id'];
$target_date = $_POST['target_date'];



$comment_list['all_score'] = htmlspecialchars($_POST['allScoreComment']);
$comment_list['revisit'] = htmlspecialchars($_POST['revisitComment']);
$comment_list['reception'] = htmlspecialchars($_POST['receptionComment']);
$comment_list['offer'] = htmlspecialchars($_POST['offerComment']);
$comment_list['cuisine'] = htmlspecialchars($_POST['cuisineComment']);
$comment_list['cleanliness'] = htmlspecialchars($_POST['cleanlinessComment']);




if ($dbio->updateFankuruComment($shop_id, $target_date, $comment_list)) {
    return true;
}
return false;