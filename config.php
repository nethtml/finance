<?php
// 数据库配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'app_lizhenwei_cn');
define('DB_USER', 'app_lizhenwei_cn');
define('DB_PASS', 'C5RNa9XK7QPa'); 

// 安全配置
error_reporting(0); // 生产环境关闭错误显示
header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");

// 创建数据库连接
try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER, 
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die(json_encode(['error' => '数据库连接失败', 'code' => 500]));
}