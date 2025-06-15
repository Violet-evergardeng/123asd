<?php
include 'db.php';
include 'auth.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
if (!$is_admin) {
    echo "无权限操作";exit;
}

// 类型列表
$type_list = [];
$res = $conn->query("SELECT * FROM dvd_types");
while($row = $res->fetch_assoc()) {
    $type_list[$row['id']] = $row['type_name'];
}

// 新增或编辑
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sale_data = null;
if ($id) {
    $sale_res = $conn->query("SELECT * FROM dvd_sale WHERE id=$id");
    $sale_data = $sale_res->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dvd_id = intval($_POST['dvd_id']);
    $qty = intval($_POST['qty']);
    if ($qty > 0) {
        if ($id) {
            // 编辑：先恢复原有库存，再扣除新数量
            $old_row = $conn->query("SELECT * FROM dvd_sale WHERE id=$id")->fetch_assoc();
            if ($old_row) {
                $old_dvd_id = $old_row['dvd_id'];
                $old_qty = $old_row['qty'];
                // 恢复原库存
                $conn->query("UPDATE dvds SET stock=stock+$old_qty WHERE id=$old_dvd_id");
            }
            // 检查库存
            $stock_row = $conn->query("SELECT stock FROM dvds WHERE id=$dvd_id")->fetch_assoc();
            if ($stock_row && $stock_row['stock'] >= $qty) {
                $conn->query("UPDATE dvd_sale SET dvd_id=$dvd_id, qty=$qty, sale_time=NOW() WHERE id=$id");
                $conn->query("UPDATE dvds SET stock=stock-$qty WHERE id=$dvd_id");
                header("Location: dvd_sale_list.php");
                exit;
            } else {
                $msg = "库存不足！";
            }
        } else {
            // 新增
            $stock_row = $conn->query("SELECT stock FROM dvds WHERE id=$dvd_id")->fetch_assoc();
            if ($stock_row && $stock_row['stock'] >= $qty) {
                $stmt = $conn->prepare("INSERT INTO dvd_sale (dvd_id, qty, sale_time) VALUES (?, ?, NOW())");
                $stmt->bind_param("ii", $dvd_id, $qty);
                $stmt->execute();
                $conn->query("UPDATE dvds SET stock=stock-$qty WHERE id=$dvd_id");
                header("Location: dvd_sale_list.php");
                exit;
            } else {
                $msg = "库存不足！";
            }
        }
    } else {
        $msg = "数量必须大于0";
    }
}

// DVD列表
$dvdres = $conn->query("SELECT * FROM dvds ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>DVD零售<?= $id ? '编辑' : '新增' ?></title>
<link rel="stylesheet" href="common.css">
</head>
<body>
<div class="container">
<?php if(function_exists('show_userbar')) show_userbar(); ?>
<h2>DVD零售<?= $id ? '编辑' : '新增' ?></h2>
<a href="dvd_sale_list.php">&lt; 返回零售管理</a>
<?php if (!empty($msg)): ?><div style="color:red"><?=$msg?></div><?php endif; ?>

<form method="post" style="margin:22px 0;">
    DVD:
    <select name="dvd_id" required>
    <?php
    $dvdres->data_seek(0);
    while($dvd = $dvdres->fetch_assoc()):
    ?>
        <option value="<?=$dvd['id']?>" <?=($sale_data && $sale_data['dvd_id']==$dvd['id'])?'selected':''?>>
            <?=htmlspecialchars($dvd['title'])?>（类型:<?=htmlspecialchars($type_list[$dvd['type_id']]??'未知')?>, 库存:<?=$dvd['stock']?>）
        </option>
    <?php endwhile; ?>
    </select>
    数量: <input type="number" name="qty" min="1" value="<?=($sale_data?$sale_data['qty']:1)?>" required>
    <button type="submit"><?= $id ? '保存修改' : '新增零售' ?></button>
</form>
</div>
</body>
</html>