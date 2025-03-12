<?php
/**
 * 页面底部模板
 * 
 * @version 1.3
 * @date 2024-03-xx
 */

if (!function_exists('getAssetPath')) {
    require_once __DIR__ . '/path_helper.php';
}
?>
    </div><!-- container -->
    </main><!-- 主要内容区域结束 -->

    <footer class="footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <span class="copyright">© <?php echo date('Y'); ?> Finance. All rights reserved.</span>
                </div>
                <div class="col-md-6">
                    <ul class="friend-links">
                        <li><a href="https://lizhenwei.cn" target="_blank">lizhenwei.cn</a></li>
                        <li><a href="https://github.com/nethtml/finance/" target="_blank">关于我们</a></li>
                        <li><a href="#" class="admin-link" title="后台管理">
                            <i class="bi bi-gear-fill"></i>
                        </a></li>
                        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <li><a href="#" class="logout-link" title="退出登录">
                            <i class="bi bi-box-arrow-right"></i>
                        </a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Cookie 提示框 -->
    <div class="alert alert-warning alert-dismissible fade" id="cookieAlert" role="alert" style="display: none;">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                <strong>请启用 Cookie</strong>
                <p class="mb-0">为了确保您能够正常登录和使用系统，请启用浏览器的 Cookie 功能。</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
    </div>

    <!-- 登录/退出模态框 -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="modalTitle">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 320px;">
            <div class="modal-content" role="dialog">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">管理员登录</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                </div>
                <div class="modal-body">
                    <!-- 登录表单 -->
                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">用户名</label>
                            <input type="text" class="form-control" id="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">密码</label>
                            <input type="password" class="form-control" id="password" required>
                        </div>
                        <div id="loginError" class="alert alert-danger d-none" role="alert">
                            用户名或密码错误
                        </div>
                    </form>
                    <!-- 退出确认 -->
                    <div id="logoutConfirm" class="text-center d-none">
                        <i class="bi bi-question-circle text-warning" style="font-size: 2rem;" aria-hidden="true"></i>
                        <p class="mt-3">确定要退出登录吗？</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <!-- 登录按钮 -->
                    <button type="button" class="btn btn-primary" id="loginBtn">登录</button>
                    <!-- 退出按钮 -->
                    <button type="button" class="btn btn-danger d-none" id="logoutBtn">退出登录</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="<?php echo getAssetPath('js/bootstrap.bundle.min.js'); ?>"></script>
    <!-- ECharts -->
    <script src="<?php echo getAssetPath('js/echarts.min.js'); ?>"></script>
    <!-- 自定义JS -->
    <script src="<?php echo getAssetPath('js/main.js'); ?>"></script>

    <style>
    /* Cookie 提示框样式 */
    #cookieAlert {
        position: fixed;
        top: 20px;
        right: 20px;
        max-width: 400px;
        z-index: 1050;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    #cookieAlert .bi-exclamation-triangle-fill {
        font-size: 1.5rem;
        color: #ffc107;
    }

    @media (max-width: 576px) {
        #cookieAlert {
            top: 10px;
            right: 10px;
            left: 10px;
            max-width: none;
        }
    }

    /* 添加退出按钮样式 */
    .logout-link {
        color: var(--bs-danger);
    }
    .logout-link:hover {
        color: var(--bs-danger-hover, #bb2d3b);
    }
    .friend-links li:not(:last-child) {
        margin-right: 1rem;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 检查 Cookie 是否启用
        function areCookiesEnabled() {
            try {
                document.cookie = "testcookie=1";
                var ret = document.cookie.indexOf("testcookie=") != -1;
                document.cookie = "testcookie=1; expires=Thu, 01-Jan-1970 00:00:01 GMT";
                return ret;
            } catch (e) {
                return false;
            }
        }

        // 显示 Cookie 警告
        function showCookieWarning() {
            const cookieAlert = document.getElementById('cookieAlert');
            if (cookieAlert) {
                cookieAlert.style.display = 'block';
                cookieAlert.classList.add('show');
            }
        }

        const adminLink = document.querySelector('.admin-link');
        const logoutLink = document.querySelector('.logout-link');
        const loginForm = document.getElementById('loginForm');
        const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        const modalTitle = document.getElementById('modalTitle');
        const loginBtn = document.getElementById('loginBtn');
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutConfirm = document.getElementById('logoutConfirm');
        const modalElement = document.getElementById('loginModal');
        
        // 获取PHP传递的登录状态
        let isLoggedIn = <?php echo isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true ? 'true' : 'false'; ?>;
        
        if (adminLink && loginForm) {
            // 阻止表单默认提交
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
            });
            
            // 齿轮按钮点击事件 - 现在只处理登录和导航
            adminLink.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (isLoggedIn) {
                    // 已登录状态下，直接跳转到管理页面
                    window.location.href = window.location.pathname.includes('/pages/') ? 'manage.php' : 'pages/manage.php';
                } else {
                    // 未登录状态下，显示登录模态框
                    if (!areCookiesEnabled()) {
                        showCookieWarning();
                        return;
                    }
                    
                    modalTitle.textContent = '管理员登录';
                    loginForm.classList.remove('d-none');
                    logoutConfirm.classList.add('d-none');
                    loginBtn.classList.remove('d-none');
                    logoutBtn.classList.add('d-none');
                    loginModal.show();
                }
            });

            // 退出按钮点击事件
            if (logoutLink) {
                logoutLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    modalTitle.textContent = '退出登录';
                    loginForm.classList.add('d-none');
                    logoutConfirm.classList.remove('d-none');
                    loginBtn.classList.add('d-none');
                    logoutBtn.classList.remove('d-none');
                    loginModal.show();
                });
            }
            
            // 登录按钮点击事件
            loginBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (!areCookiesEnabled()) {
                    showCookieWarning();
                    loginModal.hide();
                    return;
                }
                
                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                const errorDiv = document.getElementById('loginError');
                
                const loginPath = window.location.pathname.includes('/pages/') ? '../login.php' : 'login.php';
                
                fetch(loginPath, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'login',
                        username: username,
                        password: password
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        isLoggedIn = true;
                        window.location.href = window.location.pathname.includes('/pages/') ? 'manage.php' : 'pages/manage.php';
                    } else {
                        errorDiv.classList.remove('d-none');
                        setTimeout(() => {
                            errorDiv.classList.add('d-none');
                        }, 3000);
                    }
                });
            });
            
            // 退出按钮点击事件
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const loginPath = window.location.pathname.includes('/pages/') ? '../login.php' : 'login.php';
                
                fetch(loginPath, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'logout'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        isLoggedIn = false;
                        window.location.reload();
                    }
                });
            });

            // 模态框打开前移除 aria-hidden
            modalElement.addEventListener('show.bs.modal', function() {
                this.removeAttribute('aria-hidden');
                // 确保模态框可聚焦
                this.removeAttribute('inert');
                // 将页面其他部分设置为不可交互
                document.body.querySelectorAll('*:not(.modal, .modal *)').forEach(element => {
                    if (element !== modalElement && !modalElement.contains(element)) {
                        element.setAttribute('inert', '');
                    }
                });
            });

            // 模态框打开后设置焦点
            modalElement.addEventListener('shown.bs.modal', function() {
                if (!isLoggedIn) {
                    const usernameInput = document.getElementById('username');
                    if (usernameInput) {
                        usernameInput.focus();
                    }
                } else {
                    const logoutButton = document.getElementById('logoutBtn');
                    if (logoutButton) {
                        logoutButton.focus();
                    }
                }
            });

            // 模态框关闭前的处理
            modalElement.addEventListener('hide.bs.modal', function() {
                // 移除页面其他部分的 inert 属性
                document.body.querySelectorAll('[inert]').forEach(element => {
                    element.removeAttribute('inert');
                });
                // 将焦点返回到触发按钮
                if (adminLink) {
                    setTimeout(() => {
                        adminLink.focus();
                    }, 0);
                }
            });

            // 模态框关闭后的清理
            modalElement.addEventListener('hidden.bs.modal', function() {
                loginForm.reset();
                document.getElementById('loginError').classList.add('d-none');
                // 确保移除所有可能残留的 inert 属性
                document.body.querySelectorAll('[inert]').forEach(element => {
                    element.removeAttribute('inert');
                });
            });
        }

        // 页面加载时检查 Cookie
        if (!areCookiesEnabled()) {
            showCookieWarning();
        }
    });
    </script>
</body>
</html> 