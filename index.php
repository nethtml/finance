<?php
/**
 * 入口文件
 * 
 * @version 1.0
 * @date 2024-03-xx
 */

// 启动会话
session_start();

// 包含必要的文件
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// 检查登录状态
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>

<div class="welcome-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="welcome-section text-center">
                    <h1 class="welcome-title mb-3">智能记账助手</h1>
                    <p class="welcome-subtitle mb-4">轻松掌控每一笔收支</p>
                    <div class="desktop-layout d-none d-lg-flex align-items-center justify-content-center">
                        <div class="welcome-image me-5">
                            <img src="<?php echo getAssetPath('images/123.jpeg'); ?>" alt="Welcome Image" class="img-fluid rounded shadow">
                        </div>
                        <div class="feature-cards">
                            <a href="<?php echo getPagePath('pages/dashboard.php'); ?>" class="feature-card">
                                <div class="feature-icon">
                                    <i class="bi bi-graph-up"></i>
                                </div>
                                <div class="feature-content">
                                    <h3>数据仪表盘</h3>
                                    <p>可视化展示收支趋势</p>
                                </div>
                            </a>
                            <a href="<?php echo getPagePath('pages/records.php'); ?>" class="feature-card">
                                <div class="feature-icon">
                                    <i class="bi bi-list-ul"></i>
                                </div>
                                <div class="feature-content">
                                    <h3>流水记录</h3>
                                    <p>详细的收支明细记录</p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="mobile-layout d-block d-lg-none">
                        <div class="welcome-image mb-5">
                            <img src="<?php echo getAssetPath('images/123.jpeg'); ?>" alt="Welcome Image" class="img-fluid rounded shadow">
                        </div>
                        <div class="feature-cards">
                            <a href="<?php echo getPagePath('pages/dashboard.php'); ?>" class="feature-card">
                                <div class="feature-icon">
                                    <i class="bi bi-graph-up"></i>
                                </div>
                                <div class="feature-content">
                                    <h3>数据仪表盘</h3>
                                    <p>可视化展示收支趋势</p>
                                </div>
                            </a>
                            <a href="<?php echo getPagePath('pages/records.php'); ?>" class="feature-card">
                                <div class="feature-icon">
                                    <i class="bi bi-list-ul"></i>
                                </div>
                                <div class="feature-content">
                                    <h3>流水记录</h3>
                                    <p>详细的收支明细记录</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?> 