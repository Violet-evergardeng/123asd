<?php
include 'db.php';
include 'auth.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}
// 是否管理员
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

// 类型列表
$type_list = [];
$res = $conn->query("SELECT * FROM dvd_types");
while($row = $res->fetch_assoc()) {
    $type_list[$row['id']] = $row['type_name'];
}

// 新增零售操作（所有人可用，如只允许管理员请加$is_admin判断）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sale_dvd_id'], $_POST['sale_qty'])) {
    $dvd_id = intval($_POST['sale_dvd_id']);
    $qty = intval($_POST['sale_qty']);
    if ($qty > 0) {
        $stock_row = $conn->query("SELECT stock FROM dvds WHERE id=$dvd_id")->fetch_assoc();
        if ($stock_row && $stock_row['stock'] >= $qty) {
            $stmt = $conn->prepare("INSERT INTO dvd_sale (dvd_id, qty, sale_time) VALUES (?, ?, NOW())");
            $stmt->bind_param("ii", $dvd_id, $qty);
            $stmt->execute();
            $conn->query("UPDATE dvds SET stock=stock-$qty WHERE id=$dvd_id");
        }
    }
    header("Location: dvd_sale_list.php");
    exit;
}

// DVD列表
$dvdres = $conn->query("SELECT * FROM dvds ORDER BY id ASC");

// 零售记录
$saleres = $conn->query("SELECT s.*, d.title, d.type_id FROM dvd_sale s
    LEFT JOIN dvds d ON s.dvd_id = d.id
    ORDER BY s.sale_time DESC");
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>DVD零售管理</title>
<link rel="stylesheet" href="common.css">
</head>
<body>
<div class="container">
<?php if(function_exists('show_userbar')) show_userbar(); ?>
<h2>DVD零售管理</h2>
<a href="index.php">&lt; 返回首页</a>

<!-- 所有用户都可零售，如只允许管理员操作，请用 if($is_admin) 包裹此表单 -->
<h3>零售DVD</h3>
<form method="post" style="margin-bottom:22px;">
    DVD:
    <select name="sale_dvd_id" required>
    <?php
    $dvdres->data_seek(0);
    while($dvd = $dvdres->fetch_assoc()):
        if ($dvd['stock'] < 1) continue;
    ?>
        <option value="<?=$dvd['id']?>">
            <?=htmlspecialchars($dvd['title'])?>（类型:<?=htmlspecialchars($type_list[$dvd['type_id']]??'未知')?>, 库存:<?=$dvd['stock']?>）
        </option>
    <?php endwhile; ?>
    </select>
    数量: <input type="number" name="sale_qty" min="1" value="1" required>
    <button type="submit">出售</button>
</form>

<h3>零售记录</h3>
<table border="1" cellpadding="6" style="background:#fff;">
<tr>
    <th>ID</th>
    <th>DVD标题</th>
    <th>类型</th>
    <th>零售数量</th>
    <th>零售时间</th>
    <th>状态</th>
</tr>
<?php while($s = $saleres->fetch_assoc()): ?>
<tr>
    <td><?=$s['id']?></td>
    <td><?=htmlspecialchars($s['title'])?></td>
    <td><?=htmlspecialchars($type_list[$s['type_id']]??'未知')?></td>
    <td><?=$s['qty']?></td>
    <td><?=$s['sale_time']?></td>
    <td><span style="color:red;">已出售</span></td>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>