<?php
/**
 * Session 配置文件
 * 
 * @version 1.0
 * @date 2024-03-xx
 */

// 设置session配置
ini_set('session.gc_maxlifetime', 86400); // 24小时
ini_set('session.cookie_lifetime', 86400); // 24小时
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');

// 根据环境设置session路径
if (PHP_OS === 'WINNT') {
    // Windows环境
    $sessionPath = 'D:/phpstudy/Extensions/tmp/tmp';
} else {
    // Linux环境
    $sessionPath = '/www/wwwroot/app.lizhenwei.cn/tmp/sessions';
}

// 确保session目录存在
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

// 确保session目录可写
if (!is_writable($sessionPath)) {
    chmod($sessionPath, 0777);
}

session_save_path($sessionPath);

// 启动会话
session_start();

// 调试日志
error_log('Current OS: ' . PHP_OS);
error_log('Session Path: ' . $sessionPath);
error_log('Session ID: ' . session_id());
error_log('Session Data: ' . print_r($_SESSION, true)); 