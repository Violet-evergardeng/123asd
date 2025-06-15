<?php
include 'db.php'; include 'auth.php';
if(!$is_admin) { header("Location: dvd_type_list.php"); exit; }
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type_name = '';
if($id){
    $r = $conn->query("SELECT * FROM dvd_types WHERE id=$id")->fetch_assoc();
    $type_name = $r['type_name'];
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $type_name = trim($_POST['type_name']);
    if($id) $conn->query("UPDATE dvd_types SET type_name='$type_name' WHERE id=$id");
    else $conn->query("INSERT INTO dvd_types(type_name) VALUES('$type_name')");
    header("Location: dvd_type_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8"><title><?= $id?'编辑':'添加' ?>类型</title>
<link rel="stylesheet" href="common.css">
</head>
<body>
<div class="container">
<?php show_userbar(); ?>
<h2><?= $id?'编辑':'添加' ?>类型</h2>
<form method="post">
<label>类型名称：</label>
<input name="type_name" value="<?= htmlspecialchars($type_name) ?>" required>
<button type="submit" class="btn"><?= $id?'保存':'添加' ?></button>
</form>
<div class="return"><a href="dvd_type_list.php">返回类型管理</a></div>
</div>
</body>
</html>