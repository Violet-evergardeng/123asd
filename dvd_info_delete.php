<?php
include 'db.php'; include 'auth.php';
if(!$is_admin) { header("Location: dvd_info_list.php"); exit; }
$id = intval($_GET['id']);
$conn->query("DELETE FROM dvds WHERE id=$id");
header("Location: dvd_info_list.php");
exit;
?>