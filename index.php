<?php
include 'db.php';
include 'auth.php';

// 判断是否已登录
$is_logged_in = is_logged_in();

// 加载类型列表
$type_list = [];
$res_types = $conn->query("SELECT * FROM dvd_types");
while($row = $res_types->fetch_assoc()) {
    $type_list[$row['id']] = $row['type_name'];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8"><title>DVD管理系统-首页</title>
<link rel="stylesheet" href="common.css">
<style>
.dvd-cover {
    height: 144px;
    width: 144px;
    margin-right: 18px;
    vertical-align: middle;
    border-radius: 50%;
    box-shadow: 0 2px 10px #ccc;
    object-fit: cover;
    background: #f5f5f5;
    border: 2px solid #e3e3e3;
}
@media (max-width: 700px) {
    .dvd-cover { height: 90px; width: 90px; margin-right: 10px; }
}
</style>
</head>
<body>
<div class="container">
<?php if ($is_logged_in && function_exists('show_userbar')) show_userbar(); ?>
    <h2>DVD管理系统</h2>
    <?php if($is_logged_in): ?>
        <ul style="display:flex;justify-content:center;gap:33px;margin-bottom:36px;flex-wrap:wrap;">
            <li><a href="dvd_type_list.php">类型管理</a></li>
            <li><a href="dvd_info_list.php">信息管理</a></li>
            <li><a href="dvd_instore.php">DVD入库</a></li>
            <li><a href="dvd_borrow_list.php">借还管理</a></li>
            <li><a href="dvd_sale_list.php">零售管理</a></li>
        </ul>
    <?php else: ?>
        <div style="text-align:right"><a href="login.php">登录</a></div>
    <?php endif; ?>
    <h3 style="margin-bottom:8px;">DVD</h3>
    <?php
    $types = $conn->query("SELECT * FROM dvd_types");
    while($type = $types->fetch_assoc()):
    ?>
    <div style="margin-bottom:16px;">
        <b><?= htmlspecialchars($type['type_name']) ?></b>
        <ul style="margin-left:15px;list-style: none;padding:0;">
        <?php
        $dvds = $conn->query("SELECT * FROM dvds WHERE type_id={$type['id']}");
        if($dvds->num_rows==0)echo "<li><em>暂无DVD</em></li>";
        while($dvd=$dvds->fetch_assoc()) {
            echo "<li style='margin-bottom:24px;display:flex;align-items:center;'>";
            if (!empty($dvd['image'])) {
                echo "<img src='".htmlspecialchars($dvd['image'])."' alt='封面' class='dvd-cover'>";
            }
            echo "<span>".htmlspecialchars($dvd['title'])."（库存:{$dvd['stock']}） 价格:￥".number_format($dvd['price'],2)."</span></li>";
        }
        ?>
        </ul>
    </div>
    <?php endwhile; ?>
</div>
</body>
</html>