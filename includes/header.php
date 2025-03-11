<?php
/**
 * 页面头部模板
 * 
 * @version 1.3
 * @date 2024-03-xx
 */

require_once __DIR__ . '/path_helper.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>财务管理系统</title>
    <!-- 添加网站图标 -->
    <link rel="icon" type="image/x-icon" href="<?php echo $isSubPage ? '../' : ''; ?>favicon.ico">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo $isSubPage ? '../' : ''; ?>favicon.ico">
    <!-- Bootstrap CSS -->
    <link href="<?php echo getAssetPath('css/bootstrap.min.css'); ?>" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="<?php echo getAssetPath('css/bootstrap-icons.css'); ?>" rel="stylesheet">
    <!-- 自定义CSS -->
    <link href="<?php echo getAssetPath('css/style.css'); ?>" rel="stylesheet">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.querySelector('.navbar');
            if (navbar) {
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 10) {
                        navbar.classList.add('scrolled');
                    } else {
                        navbar.classList.remove('scrolled');
                    }
                });
            }
        });
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="<?php echo getPagePath('index.php'); ?>">
                <img src="<?php echo getAssetPath('images/logo.webp'); ?>" alt="Logo" height="40" class="d-inline-block align-text-top me-2">
                <span>Finance</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPagePath('pages/dashboard.php'); ?>">仪表盘</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPagePath('pages/records.php'); ?>">流水列表</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://github.com/nethtml/finance/" target="_blank">
                            <i class="bi bi-github"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main>
        <div class="container mt-4"> 