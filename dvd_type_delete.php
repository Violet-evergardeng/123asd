<?php
include 'db.php'; include 'auth.php';
if(!$is_admin) { header("Location: dvd_type_list.php"); exit; }
$id = intval($_GET['id']);
$conn->query("DELETE FROM dvd_types WHERE id=$id");
header("Location: dvd_type_list.php");
exit;
?>