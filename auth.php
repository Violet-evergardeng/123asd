<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user']);
}

function show_userbar() {
    if (is_logged_in()) {
        echo '<div style="float:right;">你好，'.htmlspecialchars($_SESSION['user']['username']).' | <a href="logout.php">退出</a></div>';
    } else {
        echo '<div style="float:right;"><a href="login.php">登录</a></div>';
    }
}