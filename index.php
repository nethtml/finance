<?php
$pageTitle = "首页";

// 主要内容
$mainContent = '
<!-- 首页欢迎区域 -->
<section class="welcome-section">
    <div class="container">
        <div class="text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" fill="currentColor" class="bi bi-cash-stack mb-3" viewBox="0 0 16 16">
                <path d="M14 3H1a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1h-1z"/>
                <path fill-rule="evenodd" d="M15 5H1v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V5zM4 11a2 2 0 1 1 4 0 2 2 0 0 1-4 0z"/>
            </svg>
            <h1 class="display-5 fw-bold">财务管理系统</h1>
            <div class="col-lg-6 mx-auto">
                <p class="lead mb-4">
                    这是一个简单的财务管理系统，帮助您更好地管理个人或企业财务。
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="dashboard.php" class="btn btn-primary px-4" style="min-width: 120px;">开始使用</a>
                    <a href="admin.php" class="btn btn-outline-secondary px-4" style="min-width: 120px;">后台管理</a>
                </div>
            </div>
        </div>
    </div>
</section>';

// 引入基础模板
include 'templates/base.html';
?> 