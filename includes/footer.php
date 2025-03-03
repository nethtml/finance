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
    
    <!-- Bootstrap JS -->
    <script src="<?php echo getAssetPath('js/bootstrap.bundle.min.js'); ?>"></script>
    <!-- ECharts -->
    <script src="<?php echo getAssetPath('js/echarts.min.js'); ?>"></script>
    <!-- 自定义JS -->
    <script src="<?php echo getAssetPath('js/main.js'); ?>"></script>
</body>
</html> 