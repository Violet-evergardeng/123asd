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
    $password = trim($_POST['password']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];
        header("Location: index.php");
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>登录</title>
    <link rel="stylesheet" href="common.css">
</head>
<body>
<div class="container" style="max-width:330px;">
    <h2>用户登录</h2>
    <form method="post">
        <label>用户名：</label>
        <input name="username" required>
        <label>密码：</label>
        <input name="password" type="password" required>
        <button type="submit" class="btn">登录</button>
    </form>
    <?php if($error) echo "<div style='color:red;text-align:center;margin-top:12px;'>$error</div>"; ?>
    <div style="text-align:center;margin-top:18px;">
        没账号？<a href="register.php">注册</a>
    </div>
</div>
</body>
</html>