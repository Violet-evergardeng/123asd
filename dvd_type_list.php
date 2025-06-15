<?php
include 'db.php';
include 'auth.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

// 添加类型
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addtype'])) {
    $type_name = trim($_POST['type_name']);
    if ($type_name) {
        $stmt = $conn->prepare("INSERT INTO dvd_types (type_name) VALUES (?)");
        $stmt->bind_param("s", $type_name);
        $stmt->execute();
        header("Location: dvd_type_list.php");
        exit;
    }
}

// 删除类型并级联删除所有该类型的DVD
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    // 1. 删除该类型下所有DVD
    $conn->query("DELETE FROM dvds WHERE type_id = $id");

    // 2. 删除该类型
    $stmt = $conn->prepare("DELETE FROM dvd_types WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // 重新整理id
    $result = $conn->query("SELECT * FROM dvd_types ORDER BY id ASC");
    $current_id = 1;
    while ($row = $result->fetch_assoc()) {
        $conn->query("UPDATE dvd_types SET id = $current_id WHERE id = {$row['id']}");
        $current_id++;
    }
    // 重置自增
    $conn->query("ALTER TABLE dvd_types AUTO_INCREMENT = $current_id");

    header("Location: dvd_type_list.php");
    exit;
}

// 类型列表
$typeres = $conn->query("SELECT * FROM dvd_types ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>DVD类型管理</title>
<link rel="stylesheet" href="common.css">
</head>
<body>
<div class="container">
<?php show_userbar(); ?>
<h2>DVD类型管理</h2>
<a href="index.php">&lt; 返回首页</a>
<h3>添加新类型</h3>
<form method="post" style="margin-bottom:22px;">
    类型名称: <input type="text" name="type_name" required>
    <button type="submit" name="addtype">添加</button>
</form>

<h3>类型列表</h3>
<table border="1" cellpadding="6" style="background:#fff;">
<tr><th>ID</th><th>类型名称</th><th>操作</th></tr>
<?php while($type = $typeres->fetch_assoc()): ?>
<tr>
    <td><?=$type['id']?></td>
    <td><?=htmlspecialchars($type['type_name'])?></td>
    <td>
        <a href="?del=<?=$type['id']?>" onclick="return confirm('确认删除该类型？其下所有DVD也会被彻底删除！')">删除</a>
    </td>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>