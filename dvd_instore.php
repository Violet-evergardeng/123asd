<?php
include 'db.php';
include 'auth.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

// 获取所有DVD
$dvdres = $conn->query("SELECT * FROM dvds ORDER BY id ASC");

// 入库操作
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dvd_id']) && isset($_POST['instore_qty'])) {
    $dvd_id = intval($_POST['dvd_id']);
    $instore_qty = intval($_POST['instore_qty']);
    if ($instore_qty > 0) {
        $stmt = $conn->prepare("UPDATE dvds SET stock = stock + ? WHERE id = ?");
        $stmt->bind_param("ii", $instore_qty, $dvd_id);
        $stmt->execute();
        $message = "入库成功！";
    } else {
        $message = "入库数量必须大于0";
    }
    // 刷新列表
    $dvdres = $conn->query("SELECT * FROM dvds ORDER BY id ASC");
}

$type_list = [];
$res = $conn->query("SELECT * FROM dvd_types");
while($row = $res->fetch_assoc()) {
    $type_list[$row['id']] = $row['type_name'];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>DVD入库</title>
<link rel="stylesheet" href="common.css">
</head>
<body>
<div class="container">
<?php show_userbar(); ?>
<h2>DVD入库</h2>
<a href="index.php">&lt; 返回首页</a>
<?php if($message): ?>
    <div style="color:green;margin:12px 0;"><?=htmlspecialchars($message)?></div>
<?php endif; ?>
<table border="1" cellpadding="6" style="background:#fff;margin-top:22px;">
<tr><th>ID</th><th>封面</th><th>标题</th><th>类型</th><th>库存</th><th>入库数量</th><th>操作</th></tr>
<?php while($dvd = $dvdres->fetch_assoc()): ?>
<tr>
    <form method="post">
    <td><?=$dvd['id']?></td>
    <td>
        <?php if(!empty($dvd['image'])): ?>
            <img src="<?=htmlspecialchars($dvd['image'])?>" alt="封面" style="max-height:48px;max-width:80px;border-radius:4px;">
        <?php endif; ?>
    </td>
    <td><?=htmlspecialchars($dvd['title'])?></td>
    <td><?=htmlspecialchars($type_list[$dvd['type_id']]??'未知')?></td>
    <td><?=$dvd['stock']?></td>
    <td>
        <input type="number" name="instore_qty" min="1" value="1" style="width:60px;" required>
        <input type="hidden" name="dvd_id" value="<?=$dvd['id']?>">
    </td>
    <td>
        <button type="submit">入库</button>
    </td>
    </form>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>