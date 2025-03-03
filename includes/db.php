<?php
/**
 * 数据库连接配置文件
 * 
 * @version 1.1
 * @date 2024-03-xx
 */

// 加载环境配置
if (file_exists(__DIR__ . '/../.env')) {
    $envFile = file_get_contents(__DIR__ . '/../.env');
    $lines = explode("\n", $envFile);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || empty(trim($line))) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// 数据库配置
$dbConfig = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'dbname' => $_ENV['DB_NAME'] ?? 'finance_db',
    'username' => $_ENV['DB_USER'] ?? 'finance_db',
    'password' => $_ENV['DB_PASS'] ?? 'finance_db',
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_TIMEOUT => 5, // 连接超时设置
        PDO::ATTR_PERSISTENT => false // 禁用持久连接
    ]
];

try {
    // 尝试 PDO 连接
    if (extension_loaded('pdo_mysql')) {
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
        
        // 设置连接编码
        $pdo->exec("SET NAMES {$dbConfig['charset']}");
        
        // 设置时区
        $pdo->exec("SET time_zone = '+8:00'");
        
        // 验证连接
        $pdo->query('SELECT 1');
    }
    // 如果 PDO 不可用，尝试 MySQLi
    else if (extension_loaded('mysqli')) {
        $mysqli = new mysqli(
            $dbConfig['host'],
            $dbConfig['username'],
            $dbConfig['password'],
            $dbConfig['dbname']
        );
        
        if ($mysqli->connect_error) {
            throw new Exception("数据库连接失败: " . $mysqli->connect_error);
        }
        
        // 设置编码
        $mysqli->set_charset($dbConfig['charset']);
        
        // 设置时区
        $mysqli->query("SET time_zone = '+8:00'");
    }
    else {
        throw new Exception("未安装 MySQL 扩展");
    }
} catch (Exception $e) {
    // 记录错误日志
    error_log("数据库连接失败: " . $e->getMessage());
    
    // 显示友好的错误信息
    die("系统维护中，请稍后再试");
} 