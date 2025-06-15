<?php
include 'db.php';
include 'auth.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

// 借出DVD（所有登录用户都可操作）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_dvd_id'])) {
    $dvd_id = intval($_POST['borrow_dvd_id']);
    $borrower = trim($_POST['borrower']);
    $borrow_qty = intval($_POST['borrow_qty']);
    if ($borrow_qty < 1) $borrow_qty = 1;
    $stock_check = $conn->query("SELECT stock FROM dvds WHERE id=$dvd_id");
    $stock_row = $stock_check->fetch_assoc();
    if ($stock_row && $stock_row['stock'] >= $borrow_qty && $borrower) {
        $stmt = $conn->prepare("INSERT INTO dvd_borrow (dvd_id, borrower, qty, borrow_time, returned) VALUES (?, ?, ?, NOW(), 0)");
        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("isi", $dvd_id, $borrower, $borrow_qty);
        $stmt->execute();
        $conn->query("UPDATE dvds SET stock=stock-$borrow_qty WHERE id=$dvd_id");
    }
    header("Location: dvd_borrow_list.php");
    exit;
}

// 归还DVD（所有登录用户都可操作，整条记录归还）
if (isset($_POST['return_borrow_id'])) {
    $borrow_id = intval($_POST['return_borrow_id']);
    $res = $conn->query("SELECT dvd_id, qty FROM dvd_borrow WHERE id=$borrow_id AND returned=0");
    if ($row = $res->fetch_assoc()) {
        $dvd_id = $row['dvd_id'];
        $qty = $row['qty'];
        $conn->query("UPDATE dvd_borrow SET returned=1, return_time=NOW() WHERE id=$borrow_id");
        $conn->query("UPDATE dvds SET stock=stock+$qty WHERE id=$dvd_id");
    }
    header("Location: dvd_borrow_list.php");
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

// 借还记录
$borrowres = $conn->query("SELECT b.*, d.title, d.type_id 
    FROM dvd_borrow b LEFT JOIN dvds d ON b.dvd_id = d.id 
    ORDER BY b.borrow_time DESC");
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>DVD借还管理</title>
<link rel="stylesheet" href="common.css">
</head>
<body>
<div class="container">
<?php if(function_exists('show_userbar')) show_userbar(); ?>
<h2>DVD借还管理</h2>
<a href="index.php">&lt; 返回首页</a>

<h3>借阅DVD</h3>
<form method="post" style="margin-bottom:22px;">
    借阅DVD:
    <select name="borrow_dvd_id" required>
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
    数量: <input type="number" name="borrow_qty" min="1" value="1" style="width:60px" required>
    借阅人: <input type="text" name="borrower" required>
    <button type="submit">借阅</button>
</form>

<h3>借还记录</h3>
<table border="1" cellpadding="6" style="background:#fff;">
<tr>
    <th>ID</th>
    <th>DVD标题</th>
    <th>类型</th>
    <th>数量</th>
    <th>借阅人</th>
    <th>借阅时间</th>
    <th>归还时间</th>
    <th>状态</th>
    <th>操作</th>
</tr>
<?php while($b = $borrowres->fetch_assoc()): ?>
<tr>
    <td><?=$b['id']?></td>
    <td><?=htmlspecialchars($b['title'])?></td>
    <td><?=htmlspecialchars($type_list[$b['type_id']]??'未知')?></td>
    <td><?=isset($b['qty']) ? $b['qty'] : 1?></td>
    <td><?=htmlspecialchars($b['borrower'])?></td>
    <td><?=$b['borrow_time']?></td>
    <td><?=$b['return_time']??''?></td>
    <td>
        <?php if($b['returned']): ?>
            <span style="color:green;">已归还</span>
        <?php else: ?>
            <span style="color:orange;">未归还</span>
        <?php endif; ?>
    </td> 
    <td>
        <?php if(!$b['returned']): ?>
            <form method="post" style="display:inline;">
                <input type="hidden" name="return_borrow_id" value="<?=$b['id']?>">
                <button type="submit" onclick="return confirm('确定标记为已归还吗？')">归还</button>
            </form>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>