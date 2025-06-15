<?php
include 'db.php'; include 'auth.php';
if(!$is_admin) { header("Location: dvd_borrow_list.php"); exit; }
$id = intval($_GET['id']);
$r = $conn->query("SELECT dvd_id FROM borrow_records WHERE id=$id")->fetch_assoc();
$dvd_id = $r['dvd_id'];
$conn->query("UPDATE borrow_records SET status='已归还',return_date=NOW() WHERE id=$id");
$conn->query("UPDATE dvds SET stock=stock+1 WHERE id=$dvd_id");
header("Location: dvd_borrow_list.php");
exit;
?>