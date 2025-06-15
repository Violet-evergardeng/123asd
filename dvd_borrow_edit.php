<?php
include 'db.php'; include 'auth.php';
if(!$is_admin) { header("Location: dvd_borrow_list.php"); exit; }
if($_SERVER['REQUEST_METHOD']==='POST'){
    $dvd_id = intval($_POST['dvd_id']);
    $borrower = trim($_POST['borrower']);
    $conn->query("INSERT INTO borrow_records(dvd_id,borrower,borrow_date,status) VALUES($dvd_id,'$borrower',NOW(),'借出')");
    $conn->query("UPDATE dvds SET stock=stock-1 WHERE id=$dvd_id");
    header("Location: dvd_borrow_list.php");
    exit;
}
$res = $conn->query("SELECT * FROM dvds WHERE stock > 0");
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8"><title>新增借出</title>
<link rel="stylesheet" href="common.css">
</head>
<body>
<div class="container">
<?php show_userbar(); ?>
<h2>新增借出</h2>
<form method="post">
<label>选择DVD：</label>
<select name="dvd_id" required>
<?php while($row=$res->fetch_assoc()): ?>
<option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></option>
<?php endwhile; ?>
</select>
<label>借出人：</label>
<input name="borrower" required>
<button type="submit" class="btn">借出</button>
</form>
<div class="return"><a href="dvd_borrow_list.php">返回借还管理</a></div>
</div>
</body>
</html>