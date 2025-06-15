<?php
include 'db.php';
include 'auth.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

// 处理删除DVD
if (isset($_GET['del'])) {
    $del_id = intval($_GET['del']);
    // 删除对应DVD（如需管理员限制，可加权限判断）
    $conn->query("DELETE FROM dvds WHERE id=$del_id");
    header("Location: dvd_info_list.php");
    exit;
}

// 处理添加DVD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adddvd'])) {
    $title = trim($_POST['title']);
    $type_id = intval($_POST['type_id']);
    $stock = intval($_POST['stock']);
    $price = floatval($_POST['price']);
    $image_path = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $img_name = uniqid('dvd_', true) . '.' . $ext;
        $img_dir = __DIR__.'/images/dvds/';
        if (!is_dir($img_dir)) { mkdir($img_dir, 0777, true); }
        if (move_uploaded_file($_FILES['image']['tmp_name'], $img_dir.$img_name)) {
            $image_path = 'images/dvds/' . $img_name;
        }
    }

    $stmt = $conn->prepare("INSERT INTO dvds (title, type_id, stock, price, image) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die('SQL prepare failed: ' . $conn->error);
    }
    $stmt->bind_param("siids", $title, $type_id, $stock, $price, $image_path);
    $stmt->execute();
    header("Location: dvd_info_list.php");
    exit;
}

// 类型列表
$type_list = [];
$res = $conn->query("SELECT * FROM dvd_types");
while($row = $res->fetch_assoc()) {
    $type_list[$row['id']] = $row['type_name'];
}

// DVD列表
$dvdres = $conn->query("SELECT * FROM dvds ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>DVD信息管理</title>
<link rel="stylesheet" href="common.css">
<style>
td img { max-height:48px; max-width:80px; border-radius:4px; box-shadow:0 1px 5px #ccc; }
</style>
</head>
<body>
<div class="container">
<?php show_userbar(); ?>
<h2>DVD信息管理</h2>
<a href="index.php">&lt; 返回首页</a>
<h3>添加新DVD</h3>
<form method="post" enctype="multipart/form-data" style="margin-bottom:22px;">
    DVD标题: <input type="text" name="title" required>
    类型: <select name="type_id" required>
        <?php foreach($type_list as $tid=>$tname): ?>
        <option value="<?=$tid?>"><?=htmlspecialchars($tname)?></option>
        <?php endforeach; ?>
    </select>
    库存: <input type="number" name="stock" required min="0" value="0" style="width:60px;">
    价格: <input type="number" step="0.01" name="price" required min="0" value="0.00" style="width:80px;">
    封面图片: <input type="file" name="image" accept="image/*">
    <button type="submit" name="adddvd">添加</button>
</form>

<h3>DVD列表</h3>
<table border="1" cellpadding="6" style="background:#fff;">
<tr><th>序号</th><th>封面</th><th>标题</th><th>类型</th><th>库存</th><th>价格</th><th>操作</th></tr>
<?php $i=1; while($dvd = $dvdres->fetch_assoc()): ?>
<tr>
    <td><?=$i++?></td>
    <td>
        <?php if(!empty($dvd['image'])): ?>
            <img src="<?=htmlspecialchars($dvd['image'])?>" alt="封面">
        <?php endif; ?>
    </td>
    <td><?=htmlspecialchars($dvd['title'])?></td>
    <td><?=htmlspecialchars($type_list[$dvd['type_id']]??'未知')?></td>
    <td><?=$dvd['stock']?></td>
    <td>￥<?=number_format($dvd['price'], 2)?></td>
    <td>
        <a href="?del=<?=$dvd['id']?>" onclick="return confirm('确认删除？')">删除</a>
    </td>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>