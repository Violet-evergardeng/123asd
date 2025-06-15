<?php
include 'db.php';
session_start();
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    // 管理员注册需邀请码
    $is_admin = isset($_POST['is_admin']) && $_POST['admin_code'] === '123';
    $role = $is_admin ? 'admin' : 'user';
    if ($password !== $password2) {
        $error = "两次密码不一致";
    } else {
        $exists = $conn->query("SELECT id FROM users WHERE username='" . $conn->real_escape_string($username) . "'")->num_rows;
        if ($exists) {
            $error = "用户名已存在";
        } else {
            $pw_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $pw_hash, $role);
            $stmt->execute();
            header("Location: login.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>注册</title>
    <link rel="stylesheet" href="common.css">
    <script>
    function toggleAdminBox() {
        document.getElementById('admin_code_box').style.display =
            document.getElementById('is_admin').checked ? 'block' : 'none';
    }
    </script>
</head>
<body>
<div class="container" style="max-width:330px;">
    <h2>注册账号</h2>
    <form method="post">
        <label>用户名：</label>
        <input name="username" required>
        <label>密码：</label>
        <input name="password" type="password" required>
        <label>确认密码：</label>
        <input name="password2" type="password" required>
        <label>
          <input type="checkbox" name="is_admin" id="is_admin" onclick="toggleAdminBox()"> 申请管理员
        </label>
        <div id="admin_code_box" style="display:none;">
            <label>管理员邀请码：</label>
            <input name="admin_code" type="text">
            <div style="font-size:12px;color:#888;">需要正确的邀请码才能注册管理员</div>
        </div>
        <button type="submit" class="btn">注册</button>
    </form>
    <?php if($error) echo "<div style='color:red;text-align:center;margin-top:12px;'>$error</div>"; ?>
    <div style="text-align:center;margin-top:18px;">
        已有账号？<a href="login.php">登录</a>
    </div>
</div>
</body>
</html>