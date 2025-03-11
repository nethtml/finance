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
                        <li><a href="https://lizhenwei.cn" target="_blank">Lee.Sir</a></li>
                        <li><a href="https://github.com/nethtml/finance/" target="_blank">关于我们</a></li>
                        <li><a href="<?php echo getPagePath('pages/manage.php'); ?>" class="admin-link" title="后台管理">
                            <i class="bi bi-gear-fill"></i>
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="<?php echo getAssetPath('js/bootstrap.bundle.min.js'); ?>"></script>
    <!-- ECharts -->
    <script src="<?php echo getAssetPath('js/echarts.min.js'); ?>"></script>
    <!-- 自定义JS -->
    <script src="<?php echo getAssetPath('js/main.js'); ?>"></script>
</body>
</html> 