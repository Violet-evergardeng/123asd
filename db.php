<?php
// 数据库连接配置
$servername = "localhost";    // 数据库服务器地址
$username = "schema";           // 数据库用户名（phpstudy默认是root）
$password = "schema";               // 数据库密码（phpstudy默认是空）
$dbname = "schema";      // 这里填你实际创建的DVD管理数据库名

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 设置字符集
$conn->set_charset("utf8");
?>