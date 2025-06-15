<?php
include 'db.php'; include 'auth.php';
if(!$is_admin) { header("Location: dvd_info_list.php"); exit; }
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$title = $type_id = $desc = $stock = '';
if($id){
    $r = $conn->query("SELECT * FROM dvds WHERE id=$id")->fetch_assoc();
    $title = $r['title'];
    $type_id = $r['type_id'];
    $desc = $r['description'];
    $stock = $r['stock'];
}
$type_res = $conn->query("SELECT * FROM dvd_types");
if($_SERVER['REQUEST_METHOD']==='POST'){
    $title = trim($_POST['title']);
    $type_id = intval($_POST['type_id']);
    $desc = trim($_POST['description']);
    $stock = intval($_POST['stock']);
    if($id) $conn->query("UPDATE dvds SET title='$title',type_id=$type_id,description='$desc',stock=$stock WHERE id=$id");
    else $conn->query("INSERT INTO dvds(title,type_id,description,stock) VALUES('$title',$type_id,'$desc',$stock)");
    header("Location: dvd_info_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8"><title><?= $id?'编辑':'添加' ?>DVD</title>
<link rel="stylesheet" href="common.css">
</head>
<body>
<div class="container">
<?php show_userbar(); ?>
<h2><?= $id?'编辑':'添加' ?>DVD</h2>
<form method="post">
<label>名称：</label>
<input name="title" value="<?= htmlspecialchars($title) ?>" required>
<label>类型：</label>
<select name="type_id" required>
<?php while($t=$type_res->fetch_assoc()): ?>
<option value="<?= $t['id'] ?>" <?= $type_id==$t['id']?'selected':'' ?>><?= htmlspecialchars($t['type_name']) ?></option>
<?php endwhile; ?>
</select>
<label>简介：</label>
<textarea name="description" rows="2"><?= htmlspecialchars($desc) ?></textarea>
<label>库存：</label>
<input name="stock" type="number" min="0" value="<?= htmlspecialchars($stock) ?>" required>
<button type="submit" class="btn"><?= $id?'保存':'添加' ?></button>
</form>
<div class="return"><a href="dvd_info_list.php">返回信息管理</a></div>
</div>
</body>
</html>