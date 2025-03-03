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
    <!-- Bootstrap CSS -->
    <link href="<?php echo getAssetPath('css/bootstrap.min.css'); ?>" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="<?php echo getAssetPath('css/bootstrap-icons.css'); ?>" rel="stylesheet">
    <!-- 自定义CSS -->
    <link href="<?php echo getAssetPath('css/style.css'); ?>" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo getPagePath('index.php'); ?>">财务管理系统</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPagePath('pages/dashboard.php'); ?>">仪表盘</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPagePath('pages/records.php'); ?>">流水列表</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPagePath('pages/manage.php'); ?>">记录管理</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4"> 