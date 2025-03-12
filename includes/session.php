<?php
/**
 * Session 配置文件
 * 
 * @version 1.0
 * @date 2024-03-xx
 */

// 设置session配置
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

// 设置session保存路径
$sessionPath = 'D:/phpstudy/Extensions/tmp/tmp';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

// 启动会话
session_start(); 