<?php
include "../AdminLTE/class/ClassLoader.php";
$dbio = new DBIO();

$date = $_POST['date'];
$type = $_POST['type'];

$value = str_replace(',', '', $_POST['value']);

$dbio->upsertOnsenBudget($date, $type, $value);

echo true;
