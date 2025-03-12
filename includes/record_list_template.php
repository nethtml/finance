<?php
/**
 * 记录列表共享模板
 * 
 * @version 1.0
 * @date 2024-03-xx
 */

// 检查必要的变量是否存在
if (!isset($records) || !isset($showActions)) {
    die('错误：缺少必要的变量');
}

// 确保 functions.php 已加载
if (!function_exists('formatAmount')) {
    require_once __DIR__ . '/functions.php';
}
?>

<!-- 添加jQuery和jQuery UI -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>日期</th>
                <th>类型</th>
                <th>金额</th>
                <th>凭证</th>
                <th>说明</th>
                <?php if ($showActions): ?>
                <th>操作</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
            <tr>
                <td colspan="<?php echo $showActions ? '6' : '5'; ?>" class="text-center">
                    暂无记录
                </td>
            </tr>
            <?php else: ?>
                <?php foreach ($records as $record): ?>
                <tr>
                    <td class="text-nowrap">
                        <div class="d-none d-sm-block">
                            <?php echo htmlspecialchars($record['date']); ?>
                        </div>
                        <div class="d-sm-none">
                            <?php
                            $recordYear = substr($record['date'], 0, 4);
                            $currentYear = date('Y');
                            $monthDay = substr($record['date'], 5);
                            
                            if ($recordYear == $currentYear) {
                                // 当年记录只显示月日
                                echo '<div class="text-center">' . $monthDay . '</div>';
                            } else {
                                // 往年记录显示年份和月日（两行）
                                echo '<div class="d-flex flex-column align-items-center">';
                                echo '<div>' . $recordYear . '</div>';
                                echo '<div>' . $monthDay . '</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </td>
                    <td>
                        <div class="d-none d-sm-flex align-items-center">
                            <span class="badge bg-<?php echo $record['type'] === '收入' ? 'success' : 'danger'; ?> me-1">
                                <?php echo htmlspecialchars($record['type']); ?>
                            </span>
                            <span class="badge bg-secondary">
                                <?php echo htmlspecialchars($record['category_name']); ?>
                            </span>
                        </div>
                        <div class="d-sm-none d-flex flex-column align-items-center">
                            <span class="badge bg-<?php echo $record['type'] === '收入' ? 'success' : 'danger'; ?> mb-1">
                                <?php echo htmlspecialchars($record['type']); ?>
                            </span>
                            <span class="badge bg-secondary">
                                <?php echo htmlspecialchars($record['category_name']); ?>
                            </span>
                        </div>
                    </td>
                    <td>￥<?php echo formatAmount($record['amount']); ?></td>
                    <td>
                        <?php if (!empty($record['attachment'])): ?>
                            <?php
                            $filePath = $record['attachment'];
                            $isImage = strpos($filePath, 'images/') !== false;
                            $isPdf = strpos($filePath, 'pdf/') !== false;
                            $isDocument = strpos($filePath, 'documents/') !== false;
                            $isAudio = strpos($filePath, 'music/') !== false;
                            $isVideo = strpos($filePath, 'videos/') !== false;
                            $uploadPath = getUploadPath($filePath);
                            ?>
                            
                            <?php if ($isImage): ?>
                                <button type="button" class="btn btn-sm btn-success view-image d-flex align-items-center" data-image-path="<?php echo $uploadPath; ?>">
                                    <div class="d-none d-sm-flex align-items-center">
                                        <i class="bi bi-image"></i>
                                        <span class="ms-1">凭证</span>
                                    </div>
                                    <div class="d-sm-none d-flex flex-column align-items-center w-100">
                                        <i class="bi bi-image"></i>
                                        <span>凭证</span>
                                    </div>
                                </button>
                            <?php elseif ($isPdf): ?>
                                <button type="button" class="btn btn-sm btn-danger view-pdf d-flex align-items-center" data-pdf-path="<?php echo $uploadPath; ?>">
                                    <div class="d-none d-sm-flex align-items-center">
                                        <i class="bi bi-file-pdf"></i>
                                        <span class="ms-1">PDF</span>
                                    </div>
                                    <div class="d-sm-none d-flex flex-column align-items-center w-100">
                                        <i class="bi bi-file-pdf"></i>
                                        <span>PDF</span>
                                    </div>
                                </button>
                            <?php elseif ($isAudio): ?>
                                <button type="button" class="btn btn-sm btn-info view-audio d-flex align-items-center" 
                                        data-audio-path="<?php echo $uploadPath; ?>"
                                        data-audio-name="<?php echo basename($filePath); ?>">
                                    <div class="d-none d-sm-flex align-items-center">
                                        <i class="bi bi-music-note-beamed"></i>
                                        <span class="ms-1">音频</span>
                                    </div>
                                    <div class="d-sm-none d-flex flex-column align-items-center w-100">
                                        <i class="bi bi-music-note-beamed"></i>
                                        <span>音频</span>
                                    </div>
                                </button>
                            <?php elseif ($isVideo): ?>
                                <button type="button" class="btn btn-sm btn-warning view-video d-flex align-items-center" 
                                        data-video-path="<?php echo $uploadPath; ?>"
                                        data-video-name="<?php echo basename($filePath); ?>">
                                    <div class="d-none d-sm-flex align-items-center">
                                        <i class="bi bi-play-circle"></i>
                                        <span class="ms-1">视频</span>
                                    </div>
                                    <div class="d-sm-none d-flex flex-column align-items-center w-100">
                                        <i class="bi bi-play-circle"></i>
                                        <span>视频</span>
                                    </div>
                                </button>
                            <?php elseif ($isDocument): ?>
                                <?php
                                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                if ($ext === 'doc' || $ext === 'docx'): ?>
                                    <a href="<?php echo $uploadPath; ?>" class="btn btn-sm btn-primary d-flex align-items-center" download>
                                        <div class="d-none d-sm-flex align-items-center">
                                            <i class="bi bi-file-word"></i>
                                            <span class="ms-1">Word</span>
                                        </div>
                                        <div class="d-sm-none d-flex flex-column align-items-center w-100">
                                            <i class="bi bi-file-word"></i>
                                            <span>Word</span>
                                        </div>
                                    </a>
                                <?php elseif ($ext === 'xls' || $ext === 'xlsx'): ?>
                                    <a href="<?php echo $uploadPath; ?>" class="btn btn-sm btn-primary d-flex align-items-center" download>
                                        <div class="d-none d-sm-flex align-items-center">
                                            <i class="bi bi-file-excel"></i>
                                            <span class="ms-1">Excel</span>
                                        </div>
                                        <div class="d-sm-none d-flex flex-column align-items-center w-100">
                                            <i class="bi bi-file-excel"></i>
                                            <span>Excel</span>
                                        </div>
                                </a>
                            <?php else: ?>
                                    <a href="<?php echo $uploadPath; ?>" class="btn btn-sm btn-primary d-flex align-items-center" download>
                                        <div class="d-none d-sm-flex align-items-center">
                                            <i class="bi bi-file-text"></i>
                                            <span class="ms-1">文档</span>
                                        </div>
                                        <div class="d-sm-none d-flex flex-column align-items-center w-100">
                                            <i class="bi bi-file-text"></i>
                                            <span>文档</span>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="<?php echo $uploadPath; ?>" class="btn btn-sm btn-secondary d-flex align-items-center" download>
                                    <div class="d-none d-sm-flex align-items-center">
                                        <i class="bi bi-download"></i>
                                        <span class="ms-1">下载</span>
                                    </div>
                                    <div class="d-sm-none d-flex flex-column align-items-center w-100">
                                        <i class="bi bi-download"></i>
                                        <span>下载</span>
                                    </div>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="description-text" data-description="<?php echo htmlspecialchars($record['description']); ?>">
                            <?php echo htmlspecialchars($record['description']); ?>
                        </div>
                        <?php if (mb_strlen($record['description']) > 50): ?>
                        <div class="mt-1">
                            <span class="show-more-btn" data-description="<?php echo htmlspecialchars($record['description']); ?>">
                                显示全文
                            </span>
                        </div>
                        <?php endif; ?>
                    </td>
                    <?php if ($showActions): ?>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary edit-record" data-record='<?php echo json_encode($record); ?>'>
                            <div class="d-none d-sm-flex align-items-center">
                                <i class="bi bi-pencil"></i>
                                <span class="ms-1">编辑</span>
                            </div>
                            <div class="d-sm-none d-flex flex-column align-items-center w-100">
                                <i class="bi bi-pencil"></i>
                                <span>编辑</span>
                            </div>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger delete-record" data-id="<?php echo $record['id']; ?>">
                            <div class="d-none d-sm-flex align-items-center">
                                <i class="bi bi-trash"></i>
                                <span class="ms-1">删除</span>
                            </div>
                            <div class="d-sm-none d-flex flex-column align-items-center w-100">
                                <i class="bi bi-trash"></i>
                                <span>删除</span>
                            </div>
                        </button>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 图片预览模态框 -->
<div class="modal" id="imagePreviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document" style="opacity: 0;">
        <div class="modal-content">
            <div class="modal-header py-2 draggable d-none d-sm-flex">
                <h5 class="modal-title">图片预览</h5>
                <div class="modal-buttons ms-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary me-2 maximize-btn" aria-label="最大化">
                        <i class="bi bi-fullscreen"></i>
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                </div>
            </div>
            <!-- 移动端关闭按钮 -->
            <button type="button" class="btn-close position-fixed top-0 end-0 m-3 d-sm-none" data-bs-dismiss="modal" aria-label="关闭" style="z-index: 1051;"></button>
            <div class="modal-body p-0 text-center" style="background: #fff;">
                <img src="" id="previewImage" alt="预览图片" style="max-width: 100%; max-height: 100vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<!-- 说明全文模态框 -->
<div class="modal fade" id="descriptionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered description-modal" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">详细说明</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
            </div>
            <div class="modal-body">
                <p id="fullDescription" class="mb-0"></p>
            </div>
        </div>
    </div>
</div>

<!-- PDF预览模态框 -->
<div class="modal" id="pdfPreviewModal" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header py-2 draggable">
                <h5 class="modal-title">PDF预览</h5>
                <div class="modal-buttons ms-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary me-2 maximize-btn" aria-label="最大化">
                        <i class="bi bi-fullscreen"></i>
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                </div>
            </div>
            <div class="modal-body p-0" style="height: 80vh; max-height: calc(100vh - 120px);">
                <iframe id="pdfViewer" src="" style="width: 100%; height: 100%; border: none;" title="PDF预览"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- 音频播放模态框 -->
<div class="modal" id="audioPreviewModal" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered audio-modal" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title">音频播放</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column align-items-center justify-content-center w-100 h-100">
                    <h6 id="audioTitle" class="text-center"></h6>
                    <div class="audio-player-container">
                <audio id="audioPlayer" controls class="w-100">
                    您的浏览器不支持音频播放。
                </audio>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 视频播放模态框 -->
<div class="modal fade" id="videoPreviewModal" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header py-2 d-none d-sm-flex">
                <h5 class="modal-title">视频播放</h5>
                <div class="modal-buttons ms-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary me-2 maximize-btn" aria-label="最大化">
                        <i class="bi bi-fullscreen"></i>
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                </div>
            </div>
            <!-- 移动端关闭按钮 -->
            <button type="button" class="btn-close btn-close-white position-fixed top-0 end-0 m-3 d-sm-none" data-bs-dismiss="modal" aria-label="关闭" style="z-index: 1051;"></button>
            <div class="modal-body p-0 bg-dark">
                <div class="video-container position-relative">
                    <video id="videoPlayer" controls playsinline webkit-playsinline style="width: 100%; height: 100%; object-fit: contain;">
                        您的浏览器不支持视频播放。
                    </video>
                    <!-- 播放按钮遮罩层 -->
                    <div id="videoPlayOverlay" class="play-button-overlay d-flex align-items-center justify-content-center" 
                         style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.1); cursor: pointer;">
                        <button class="btn btn-light rounded-circle p-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-play-fill fs-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 移动端视频播放器容器 -->
<div id="mobileVideoContainer" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #000; z-index: -1;">
    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" id="mobileVideoClose" style="z-index: 1;" aria-label="关闭"></button>
    <video id="mobileVideoPlayer" controls playsinline webkit-playsinline style="width: 100%; height: 100%; object-fit: contain;">
        您的浏览器不支持视频播放。
    </video>
    <div id="mobileVideoPlayOverlay" class="play-button-overlay d-flex align-items-center justify-content-center" 
         style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); cursor: pointer;">
        <button class="btn btn-light rounded-circle p-3" style="width: 80px; height: 80px;">
            <i class="bi bi-play-fill fs-1"></i>
        </button>
    </div>
</div>

<!-- 删除确认Modal -->
<div class="modal fade" id="deleteModal<?php echo $record['id']; ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog delete-modal" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-trash me-2"></i>确认删除
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
            </div>
            <div class="modal-body">
                <i class="bi bi-exclamation-triangle-fill text-warning d-block"></i>
                <p>确定要删除这条记录吗？</p>
                <small class="text-muted">此操作不可恢复。</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    <div class="d-none d-sm-flex align-items-center">
                        <i class="bi bi-x-lg"></i>
                        <span class="ms-1">取消</span>
                    </div>
                    <div class="d-sm-none d-flex flex-column align-items-center w-100">
                        <i class="bi bi-x-lg"></i>
                        <span>取消</span>
                    </div>
                </button>
                <form action="manage.php" method="post" class="d-inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <div class="d-none d-sm-flex align-items-center">
                            <i class="bi bi-trash"></i>
                            <span class="ms-1">确认删除</span>
                        </div>
                        <div class="d-sm-none d-flex flex-column align-items-center w-100">
                            <i class="bi bi-trash"></i>
                            <span>删除</span>
                        </div>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.modal-dialog {
    max-width: 95vw;
    max-height: 95vh;
    margin: 10px auto;
}
.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 20px);
}
/* 编辑模态框样式 */
.modal-dialog.edit-modal {
    max-width: 500px !important;
    width: auto !important;
    margin: 1.75rem auto;
    height: auto !important;
}
.modal-dialog.edit-modal .modal-content {
    width: 100%;
    height: auto;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    border-radius: 0.3rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
}
.modal-dialog.edit-modal .modal-header {
    flex: 0 0 auto;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
}
.modal-dialog.edit-modal .modal-body {
    flex: 1 1 auto;
    padding: 1rem;
    overflow-y: auto;
}
.modal-dialog.edit-modal .modal-footer {
    flex: 0 0 auto;
    padding: 1rem;
    border-top: 1px solid #dee2e6;
    background: #fff;
    position: sticky;
    bottom: 0;
}
@media (max-width: 576px) {
    .modal-dialog.edit-modal {
        margin: 0.5rem auto;
        width: calc(100% - 1rem) !important;
        max-height: calc(100vh - 1rem);
    }
    
    .modal-dialog.edit-modal .modal-content {
        height: calc(100vh - 1rem);
        border-radius: 0.3rem;
    }
    
    .modal-dialog.edit-modal .modal-body {
        padding: 0.75rem;
    }
    
    .modal-dialog.edit-modal .modal-footer {
        padding: 0.75rem;
    }
}
/* 修改模态框背景色 */
.modal.fade.show {
    background-color: rgba(0, 0, 0, 0.1) !important;
}
.modal.fade .modal-dialog.edit-modal {
    transform: translate(0, 0);
    transition: transform .3s ease-out;
}
@media (max-width: 576px) {
    .modal-dialog.edit-modal {
        margin: 1rem auto;
        width: calc(100% - 2rem) !important;
        max-height: calc(100vh - 2rem);
    }
    
    .modal-dialog.edit-modal .modal-content {
        max-height: calc(100vh - 2rem);
    }
    
    .modal-dialog.edit-modal .modal-body {
        max-height: calc(100vh - 10rem);
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
}
/* 调整表单元素间距 */
.modal-dialog.edit-modal .mb-3 {
    margin-bottom: 0.75rem !important;
}
.modal-dialog.edit-modal .form-label {
    margin-bottom: 0.25rem;
}
/* 调整预览图片容器 */
.modal-dialog.edit-modal .preview-image-container {
    max-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-dialog.edit-modal .preview-image {
    max-height: 100px;
    width: auto;
    object-fit: contain;
}
/* 预览模态框样式 */
.modal-content {
    max-height: calc(100vh - 20px);
    overflow: hidden;
    background: transparent;
}
.modal-body {
    overflow: auto;
}
.draggable {
    cursor: move;
}
.modal-buttons {
    display: flex;
    align-items: center;
}
.modal.maximized {
    padding: 0 !important;
    display: block !important;
    overflow: hidden !important;
}
.modal.maximized .modal-dialog {
    width: 100vw !important;
    max-width: 100vw !important;
    height: 100vh !important;
    margin: 0 !important;
    transform: none !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
}
.modal.maximized .modal-content {
    width: 100vw !important;
    height: 100vh !important;
    max-height: 100vh !important;
    border: none !important;
    border-radius: 0 !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    display: flex !important;
    flex-direction: column !important;
}
.modal.maximized .modal-header {
    flex: 0 0 auto !important;
    padding: 0.5rem 1rem !important;
    position: relative !important;
    z-index: 1 !important;
}
.modal.maximized .modal-body {
    flex: 1 1 auto !important;
    overflow: auto !important;
    padding: 1rem !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    position: relative !important;
}
.modal.maximized video {
    max-height: calc(100vh - 116px) !important;
}
.modal.maximized #previewImage {
    max-height: calc(100vh - 60px) !important;
}

/* 删除确认模态框样式 */
.modal-dialog.delete-modal {
    max-width: 320px !important;
    margin: 1.75rem auto;
}
.modal-dialog.delete-modal .modal-content {
    border-radius: 0.3rem;
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
}
.modal-dialog.delete-modal .modal-header {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #dee2e6;
}
.modal-dialog.delete-modal .modal-header .modal-title {
    font-size: 1rem;
    display: flex;
    align-items: center;
}
.modal-dialog.delete-modal .modal-body {
    padding: 1.25rem 1rem;
    text-align: center;
}
.modal-dialog.delete-modal .modal-body i {
    font-size: 2rem !important;
    margin-bottom: 0.75rem !important;
}
.modal-dialog.delete-modal .modal-body p {
    font-size: 0.95rem;
    margin-bottom: 0.25rem !important;
}
.modal-dialog.delete-modal .modal-body small {
    font-size: 0.85rem;
}
.modal-dialog.delete-modal .modal-footer {
    padding: 0.5rem 0.75rem;
    border-top: 1px solid #dee2e6;
    justify-content: center;
    gap: 0.5rem;
}
.modal-dialog.delete-modal .btn {
    font-size: 0.875rem;
    padding: 0.25rem 0.75rem;
}
@media (max-width: 576px) {
    .modal-dialog.delete-modal {
        margin: 1rem auto;
        width: calc(100% - 2rem) !important;
        max-width: 300px !important;
    }
}

/* PC端按钮样式 */
@media (min-width: 576px) {
    .btn.d-flex {
        height: 31px !important;
        padding: 0 4px !important;
        width: 77px !important;
        min-width: 77px !important;
        display: flex !important;
        justify-content: center !important;
    }
    
    /* PC端图标和文字间距 */
    .btn.d-flex .d-sm-flex {
        height: 100%;
        align-items: center;
        display: flex !important;
        justify-content: center !important;
        gap: 4px !important;
    }
    
    .btn.d-flex .d-sm-flex i {
        font-size: 16px;
        margin: 0 !important;
    }
    
    .btn.d-flex .d-sm-flex .ms-1 {
        margin: 0 !important;
        font-size: 14px;
    }

    /* PC端操作列按钮样式 */
    .table td:last-child {
        white-space: nowrap !important;
        padding: 0.5rem !important;
    }

    .table td:last-child .btn {
        margin: 0 2px !important;
    }

    /* PC端视频模态框样式 */
    #videoPreviewModal .modal-dialog {
        max-width: 90vw;
        margin: 1.75rem auto;
    }
    
    #videoPreviewModal .modal-content {
        background: transparent;
        height: auto;
        max-height: calc(100vh - 3.5rem);
    }
    
    #videoPreviewModal .modal-header {
        background: #fff;
    }
    
    #videoPreviewModal .video-container {
        height: calc(100vh - 120px);
        max-height: calc(100vh - 120px);
        overflow: hidden;
        background: transparent;
    }
    
    #videoPreviewModal .modal-body {
        padding: 0;
        overflow: hidden;
        background: transparent;
    }
}

/* 移动端按钮样式 */
@media (max-width: 575.98px) {
    /* 移动端按钮基础样式 */
    .btn.d-flex {
        width: 46px !important;
        min-width: 46px !important;
        max-width: 46px !important;
        height: 46px !important;
        padding: 0 !important;
        margin: 0 auto;
    }
    
    /* 确保移动端按钮为正方形 */
    .btn.d-flex .d-sm-none {
        width: 100%;
        height: 100%;
        display: flex !important;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 2px;
    }
    
    /* 移动端图标和文字间距 */
    .btn.d-flex .d-sm-none i {
        margin-bottom: 2px;
        font-size: 20px;
        line-height: 1;
    }
    
    .btn.d-flex .d-sm-none span {
        font-size: 12px;
        line-height: 1.2;
        margin-top: 1px;
    }
    
    /* 操作列按钮样式 */
    .table td:last-child {
        padding: 0 !important;
        height: 46px !important;
        white-space: normal !important;
        width: 60px !important;
    }

    .table td:last-child .btn {
        width: 60px !important;
        min-width: 60px !important;
        max-width: 60px !important;
        height: 23px !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .table td:last-child .btn:first-child {
        margin-bottom: 2px !important;
    }

    .table td:last-child .btn .d-sm-none {
        flex-direction: row !important;
        height: 100% !important;
        gap: 3px !important;
        justify-content: center !important;
    }

    .table td:last-child .btn .d-sm-none i {
        margin: 0 !important;
        font-size: 14px !important;
    }

    .table td:last-child .btn .d-sm-none span {
        font-size: 11px !important;
        margin: 0 !important;
    }
}

/* 视频播放器样式 */
.video-container {
    background-color: transparent;
    width: 100%;
    height: calc(100vh - 60px);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

#videoPlayer, #mobileVideoPlayer {
    width: 100%;
    height: 100%;
    object-fit: contain;
    background: transparent;
}

.play-button-overlay {
    opacity: 1;
    transition: opacity 0.3s ease;
}

.play-button-overlay.hidden {
    opacity: 0;
    pointer-events: none;
}

/* 视频控制栏样式 */
video::-webkit-media-controls-panel {
    display: flex !important;
    opacity: 0;
    transition: opacity 0.3s ease;
}

video:hover::-webkit-media-controls-panel {
    opacity: 1;
}

/* 确保模态框在移动端也能正常工作 */
@media (max-width: 575.98px) {
    .modal-content {
        width: 100vw !important;
        height: 100vh !important;
        max-height: 100vh !important;
        border: none !important;
        border-radius: 0 !important;
        background: transparent !important;
    }
    
    .modal-body {
        padding: 0 !important;
        height: 100vh !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: transparent !important;
        overflow: hidden !important;
    }
    
    .video-container {
        width: 100vw !important;
        height: 100vh !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: transparent !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
    }
    
    #videoPlayer, #mobileVideoPlayer {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
    }
}

/* PC端视频模态框样式 */
@media (min-width: 576px) {
    #videoPreviewModal .modal-dialog {
        max-width: 90vw;
        margin: 1.75rem auto;
    }
    
    #videoPreviewModal .modal-content {
        background: transparent;
        height: auto;
        max-height: calc(100vh - 3.5rem);
    }
    
    #videoPreviewModal .modal-header {
        background: #fff;
    }
    
    #videoPreviewModal .video-container {
        height: calc(100vh - 120px);
        max-height: calc(100vh - 120px);
        overflow: hidden;
        background: transparent;
    }
    
    #videoPreviewModal .modal-body {
        padding: 0;
        overflow: hidden;
        background: transparent;
    }
}

/* 图片预览模态框样式 */
#imagePreviewModal .modal-header {
    background: #fff !important;
}

/* 移动端图片预览样式 */
@media (max-width: 575.98px) {
    #imagePreviewModal .modal-dialog {
        width: 100vw !important;
        height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    #imagePreviewModal .modal-content {
        width: 100vw !important;
        height: 100vh !important;
        max-height: 100vh !important;
        border: none !important;
        border-radius: 0 !important;
        background: #fff !important;
    }
    
    #imagePreviewModal .modal-body {
        padding: 0 !important;
        height: 100vh !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #fff !important;
    }
    
    #imagePreviewModal #previewImage {
        max-width: 100vw !important;
        max-height: 100vh !important;
        width: auto !important;
        height: auto !important;
        object-fit: contain !important;
    }
}

/* 说明文本样式 */
.description-text {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    position: relative;
    max-height: 2.8em;
    line-height: 1.4;
    margin-bottom: 0;
}

@media (max-width: 575.98px) {
    .description-text {
        font-size: 0.875rem;
    }
}

.description-text.has-more {
    cursor: pointer;
    color: inherit;
    text-decoration: none;
}

.description-text.has-more:hover {
    color: #0d6efd;
}

.show-more-btn {
    display: none;
}

/* 说明模态框样式 */
.description-modal {
    max-width: 320px !important;
    margin: 1.75rem auto;
}

.description-modal .modal-content {
    background: #fff !important;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    width: auto;
    min-width: 280px;
    height: auto !important;
}

.description-modal .modal-header {
    padding: 0.625rem 1rem;  /* 减小头部高度 */
    background: #fff !important;
    border-bottom: 1px solid #dee2e6;
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
}

.description-modal .modal-header .modal-title {
    font-size: 1rem;  /* 调整标题字体大小 */
    line-height: 1.5;
    margin: 0;
}

.description-modal .modal-header .btn-close {
    padding: 0.5rem;  /* 调整关闭按钮大小 */
}

.description-modal .modal-body {
    padding: 1rem 1.25rem;  /* 增加左右内边距 */
    background: #fff !important;
    white-space: pre-wrap;
    word-break: break-word;
    height: auto !important;
    max-height: none !important;
}

.description-modal .modal-body p {
    margin: 0;
    line-height: 1.6;  /* 增加行高 */
    font-size: 1rem;  /* 增大字体 */
}

@media (max-width: 575.98px) {
    .description-modal {
        margin: 0.75rem auto;
        width: calc(100% - 2rem) !important;
    }
    
    .description-modal .modal-content {
        width: 100%;
        min-width: auto;
    }
    
    .description-modal .modal-header {
        padding: 0.5rem 1rem;  /* 移动端进一步减小头部高度 */
    }
    
    .description-modal .modal-body {
        padding: 1rem 1.25rem;  /* 保持移动端也有足够的左右内边距 */
        font-size: 0.9375rem;  /* 移动端字体稍微小一点但不要太小 */
    }

    .description-modal .modal-body p {
        font-size: 0.9375rem;  /* 移动端字体稍微小一点但不要太小 */
    }
}

/* 修改模态框背景遮罩 */
.modal-backdrop {
    background-color: rgba(0, 0, 0, 0.5) !important;
    opacity: 1 !important;
}

/* 确保模态框内容区域背景色不透明 */
.modal .modal-content {
    background-color: #fff !important;
}

/* 音频模态框样式 */
.modal-dialog.audio-modal {
    max-width: 400px !important;
    width: auto !important;
    margin: 3rem auto !important;
}

.modal-dialog.audio-modal .modal-content {
    background: #fff !important;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-dialog.audio-modal .modal-header {
    background: #fff !important;
    border-bottom: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
}

.modal-dialog.audio-modal .modal-body {
    padding: 1rem;
    background: #fff !important;
}

.modal-dialog.audio-modal #audioTitle {
    font-size: 0.95rem;
    color: #666;
    margin-bottom: 0.75rem !important;
    word-break: break-word;
}

.modal-dialog.audio-modal .audio-player-container {
    width: 100%;
    margin: 0 auto;
}

.modal-dialog.audio-modal audio {
    width: 100%;
    margin: 0;
    display: block;
}

/* 移动端音频模态框样式 */
@media (max-width: 575.98px) {
    .modal-dialog.audio-modal {
        margin: 0 !important;
        width: 100vw !important;
        max-width: 100vw !important;
        height: 100vh !important;
    }
    
    .modal-dialog.audio-modal .modal-content {
        height: 100vh !important;
        border-radius: 0 !important;
        border: none !important;
    }
    
    .modal-dialog.audio-modal .modal-header {
        padding: 0.5rem 0.75rem;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1050;
        background: #fff !important;
    }
    
    .modal-dialog.audio-modal .modal-body {
        padding: 0.75rem;
        margin-top: 56px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: calc(100vh - 56px) !important;
    }

    .modal-dialog.audio-modal .audio-player-container {
        width: 90%;
        margin: 0 auto;
    }
    
    .modal-dialog.audio-modal #audioTitle {
        font-size: 1rem;
        margin: 1rem 0 2rem !important;
        text-align: center;
        padding: 0 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 历史状态管理
    let isModalOpen = false;
    let currentModal = null;
    let currentModalType = null;
    
    // 在页面加载时添加初始状态
    window.history.replaceState({ initial: true }, '', window.location.pathname);
    
    function handleHistoryChange(event) {
        if (isModalOpen) {
            event.preventDefault();
            
            switch (currentModalType) {
                case 'image':
                    if (currentModal) {
                        currentModal.hide();
                    }
                    break;
                case 'audio':
                    if (currentModal) {
                        currentModal.hide();
                    }
                    break;
                case 'video':
                    if (window.innerWidth <= 576) {
                        const mobileVideoContainer = document.getElementById('mobileVideoContainer');
                        const mobileVideoPlayer = document.getElementById('mobileVideoPlayer');
                        if (mobileVideoPlayer) {
                            mobileVideoPlayer.pause();
                            mobileVideoPlayer.currentTime = 0;
                            mobileVideoPlayer.src = '';
                        }
                        if (mobileVideoContainer) {
                            mobileVideoContainer.style.display = 'none';
                            mobileVideoContainer.style.zIndex = '-1';
                        }
                    } else if (currentModal) {
                        currentModal.hide();
                    }
                    break;
                case 'pdf':
                    if (currentModal) {
                        currentModal.hide();
                    }
                    break;
            }
            
            isModalOpen = false;
            currentModal = null;
            currentModalType = null;
        }
    }
    
    // 监听返回按钮和手势
    window.addEventListener('popstate', handleHistoryChange);
    
    // 修改图片预览相关代码
    (function() {
        const imagePreviewModal = document.getElementById('imagePreviewModal');
        const modalDialog = imagePreviewModal.querySelector('.modal-dialog');
        const previewImage = document.getElementById('previewImage');
        const closeButton = imagePreviewModal.querySelector('.btn-close');
        let lastFocusedElement = null;

        // 为所有查看图片按钮添加点击事件
        document.querySelectorAll('.view-image').forEach(button => {
            button.addEventListener('click', handleImageClick);
        });

        function handleImageClick(e) {
            e.preventDefault();
            lastFocusedElement = document.activeElement;
            
            resetModalStyles();
            
            if (!currentModal) {
                currentModal = new bootstrap.Modal(imagePreviewModal);
            }
            
            // 添加历史记录
            if (!isModalOpen) {
                window.history.pushState({ modal: 'image' }, '', window.location.pathname);
                isModalOpen = true;
                currentModalType = 'image';
            }
            
            loadImage(this.getAttribute('data-image-path'));
        }

        function resetModalStyles() {
            modalDialog.style.width = '';
            modalDialog.style.maxWidth = '';
            modalDialog.style.opacity = '0';
            previewImage.src = '';
        }

        function loadImage(src) {
            const tempImage = new Image();
            tempImage.onload = function() {
                const imgWidth = this.naturalWidth;
                const imgHeight = this.naturalHeight;
                const maxWidth = window.innerWidth - 40;
                const maxHeight = window.innerHeight - 120;
                const ratio = Math.min(maxWidth / imgWidth, maxHeight / imgHeight, 1);
                const finalWidth = Math.round(imgWidth * ratio);
                
                if (finalWidth < maxWidth) {
                    modalDialog.style.maxWidth = 'none';
                    modalDialog.style.width = finalWidth + 'px';
                }
                
                previewImage.src = this.src;
                currentModal.show();
                
                setTimeout(() => {
                    modalDialog.style.opacity = '1';
                    modalDialog.style.transition = 'opacity 0.15s linear';
                }, 50);
            };
            tempImage.src = src;
        }

        // 监听模态框事件
        imagePreviewModal.addEventListener('show.bs.modal', function() {
            this.removeAttribute('aria-hidden');
            this.setAttribute('aria-modal', 'true');
            document.body.style.overflow = 'hidden';
            closeButton.focus();
        });

        imagePreviewModal.addEventListener('hide.bs.modal', function() {
            this.removeAttribute('aria-modal');
            closeButton.blur();
            modalDialog.style.opacity = '0';
            modalDialog.style.transition = '';
        });

        imagePreviewModal.addEventListener('hidden.bs.modal', function() {
            if (isModalOpen) {
                isModalOpen = false;
                currentModal = null;
                currentModalType = null;
                if (window.history.state && window.history.state.modal) {
                    window.history.back();
                }
            }

            document.body.style.overflow = '';
            document.body.style.removeProperty('padding-right');
            previewImage.src = '';
            modalDialog.style.width = '';
            modalDialog.style.maxWidth = '';
            
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
            
            // 保存对 lastFocusedElement 的引用
            const elementToFocus = lastFocusedElement;
            // 立即清除全局引用
            lastFocusedElement = null;
            
            // 检查元素是否仍然存在于文档中并且具有 focus 方法
            if (elementToFocus && document.body.contains(elementToFocus) && typeof elementToFocus.focus === 'function') {
                requestAnimationFrame(() => {
                    try {
                        elementToFocus.focus();
                    } catch (e) {
                        console.warn('Failed to restore focus:', e);
                    }
                });
            }
        });

        imagePreviewModal.addEventListener('click', function(e) {
            if (e.target === this) {
                currentModal.hide();
            }
        });
    })();

    // 说明文本展开功能
    (function() {
        const descriptionModal = document.getElementById('descriptionModal');
        const fullDescription = document.getElementById('fullDescription');
        let descModal = null;
        
        // 为所有说明文本添加点击事件
        document.querySelectorAll('.description-text').forEach(element => {
            // 检测是否需要显示"更多"
            const isOverflowing = element.scrollHeight > element.clientHeight;
            if (isOverflowing) {
                element.classList.add('has-more');
                
                // 添加点击事件
                element.addEventListener('click', function(e) {
                e.preventDefault();
                const description = this.getAttribute('data-description');
                
                if (!descModal) {
                        descModal = new bootstrap.Modal(descriptionModal, {
                            backdrop: true,
                            keyboard: true
                        });
                }
                
                    // 设置内容
                fullDescription.textContent = description;
                    
                    // 显示模态框
                descModal.show();
                    
                    // 调整模态框大小
                    requestAnimationFrame(() => {
                        const modalContent = descriptionModal.querySelector('.modal-content');
                        
                        // 根据内容调整模态框宽度
                        const contentWidth = Math.min(320, Math.max(280, fullDescription.offsetWidth + 40));
                        modalContent.style.width = `${contentWidth}px`;
                    });
            });
            }
        });

        // 在窗口大小改变时重新检查
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
        document.querySelectorAll('.description-text').forEach(element => {
                    const isOverflowing = element.scrollHeight > element.clientHeight;
                    element.classList.toggle('has-more', isOverflowing);
                });
            }, 250);
        });
    })();

    // PDF预览相关代码
    const pdfPreviewModal = document.getElementById('pdfPreviewModal');
    const pdfViewer = document.getElementById('pdfViewer');
    let pdfModal = null;
    let pdfLastFocusedElement = null;

    // 为所有查看PDF按钮添加点击事件
    document.querySelectorAll('.view-pdf').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const pdfPath = this.getAttribute('data-pdf-path');
            
            // 移动端直接在新标签页打开PDF，不需要处理历史记录
            if (window.innerWidth <= 576) {
                window.open(pdfPath, '_blank');
                return;
            }
            
            // PC端使用模态框显示
            if (!isModalOpen) {
                window.history.pushState({ modal: 'pdf' }, '', window.location.pathname);
                isModalOpen = true;
                currentModal = pdfModal;
            }
            
            pdfLastFocusedElement = document.activeElement;
            
            if (!pdfModal) {
                pdfModal = new bootstrap.Modal(pdfPreviewModal, {
                    backdrop: true,
                    keyboard: true
                });
            }
            
            const dialog = pdfPreviewModal.querySelector('.modal-dialog');
            dialog.style.width = '800px';
            dialog.style.maxWidth = '800px';
            
            pdfViewer.src = pdfPath;
            pdfModal.show();
        });
    });

    // 监听PDF模态框事件
    pdfPreviewModal.addEventListener('hidden.bs.modal', function() {
        isModalOpen = false;
        currentModal = null;
        pdfViewer.src = '';
        
        const elementToFocus = pdfLastFocusedElement;
        pdfLastFocusedElement = null;
        
        if (elementToFocus && document.body.contains(elementToFocus) && typeof elementToFocus.focus === 'function') {
            requestAnimationFrame(() => {
                try {
                    elementToFocus.focus();
                } catch (e) {
                    console.warn('Failed to restore PDF modal focus:', e);
                }
            });
        }
    });

    // 音频播放相关代码
    const audioPreviewModal = document.getElementById('audioPreviewModal');
    const audioPlayer = document.getElementById('audioPlayer');
    const audioTitle = document.getElementById('audioTitle');
    let audioModal = null;
    let audioLastFocusedElement = null;

    // 为所有播放音频按钮添加点击事件
    document.querySelectorAll('.view-audio').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            audioLastFocusedElement = document.activeElement;
            
            if (!audioModal) {
                audioModal = new bootstrap.Modal(audioPreviewModal, {
                    backdrop: true,
                    keyboard: true
                });
            }
            
            // 添加历史记录
            if (!isModalOpen) {
                window.history.pushState({ modal: 'audio' }, '', window.location.pathname);
                isModalOpen = true;
                currentModal = audioModal;
                currentModalType = 'audio';
            }
            
            // 设置音频标题
            const audioName = this.getAttribute('data-audio-name');
            audioTitle.textContent = audioName;
            
            // 设置音频源
            const audioPath = this.getAttribute('data-audio-path');
            audioPlayer.src = audioPath;
            
            // 在移动端添加额外的样式
            if (window.innerWidth <= 576) {
                document.body.style.overflow = 'hidden';
                audioPreviewModal.classList.add('mobile-view');
            }
            
            audioModal.show();
            
            // 确保音频标题可见
            requestAnimationFrame(() => {
                audioTitle.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });
    });

    // 监听音频模态框事件
    audioPreviewModal.addEventListener('shown.bs.modal', function() {
        // 在移动端，调整布局
        if (window.innerWidth <= 576) {
            const modalDialog = this.querySelector('.modal-dialog');
            modalDialog.style.margin = '0';
            modalDialog.style.width = '100vw';
            modalDialog.style.maxWidth = '100vw';
            modalDialog.style.height = '100vh';
        }
    });

    audioPreviewModal.addEventListener('hidden.bs.modal', function() {
        if (isModalOpen) {
            isModalOpen = false;
            currentModal = null;
            currentModalType = null;
            if (window.history.state && window.history.state.modal) {
                window.history.back();
            }
        }
        // 停止音频播放
        audioPlayer.pause();
        audioPlayer.currentTime = 0;
        audioPlayer.src = '';
        audioTitle.textContent = '';
        
        // 移除移动端特定样式
        document.body.style.overflow = '';
        this.classList.remove('mobile-view');
        
        // 移除所有模态框背景
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
        
        // 恢复焦点
        const elementToFocus = audioLastFocusedElement;
        audioLastFocusedElement = null;
        
        if (elementToFocus && document.body.contains(elementToFocus) && typeof elementToFocus.focus === 'function') {
            requestAnimationFrame(() => {
                try {
                    elementToFocus.focus();
                } catch (e) {
                    console.warn('Failed to restore audio modal focus:', e);
                }
            });
        }
    });

    // 监听窗口大小变化
    window.addEventListener('resize', function() {
        if (audioPreviewModal.classList.contains('show')) {
            if (window.innerWidth <= 576) {
                audioPreviewModal.classList.add('mobile-view');
                document.body.style.overflow = 'hidden';
            } else {
                audioPreviewModal.classList.remove('mobile-view');
                document.body.style.overflow = '';
            }
        }
    });

    // 视频播放相关代码
    const videoModal = document.getElementById('videoPreviewModal');
    const videoPlayer = document.getElementById('videoPlayer');
    const videoPlayOverlay = document.getElementById('videoPlayOverlay');
    const mobileVideoContainer = document.getElementById('mobileVideoContainer');
    const videoModalDialog = videoModal ? videoModal.querySelector('.modal-dialog') : null;
    let videoModal_bs = null;
    let activeVideoPlayer = null;
    
    // 初始化视频模态框
    if (videoModal) {
        videoModal_bs = new bootstrap.Modal(videoModal, {
            backdrop: 'static',
            keyboard: false
        });
        
        // 监听模态框事件
        videoModal.addEventListener('show.bs.modal', function() {
            document.body.style.overflow = 'hidden';
            document.body.style.paddingRight = '0px';
            // 显示播放按钮
            if (videoPlayOverlay) {
                videoPlayOverlay.classList.remove('hidden');
            }
        });
        
        videoModal.addEventListener('hide.bs.modal', function() {
            if (videoPlayer) {
                videoPlayer.pause();
                videoPlayer.currentTime = 0;
                videoPlayer.src = '';
            }
            // 在隐藏之前移除所有按钮的焦点
            videoModal.querySelectorAll('button').forEach(button => {
                button.blur();
            });
        });
        
        videoModal.addEventListener('hidden.bs.modal', function() {
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
            
            if (videoPlayer) {
                videoPlayer.pause();
                videoPlayer.removeAttribute('src');
                videoPlayer.load();
            }
            // 重置播放按钮显示
            if (videoPlayOverlay) {
                videoPlayOverlay.classList.remove('hidden');
            }
            // 确保移除所有按钮的焦点
            videoModal.querySelectorAll('button').forEach(button => {
                button.blur();
            });
            // 将焦点返回到触发模态框的按钮
            const lastFocusedButton = document.querySelector('.view-video[data-video-path="' + videoPlayer.getAttribute('src') + '"]');
            if (lastFocusedButton) {
                lastFocusedButton.focus();
            }
        });
        
        // PC端播放按钮点击事件
        if (videoPlayOverlay) {
            videoPlayOverlay.addEventListener('click', async function() {
                try {
                    // 隐藏播放按钮
                    this.classList.add('hidden');
                    // 播放视频
                    await videoPlayer.play();
                } catch (error) {
                    console.warn('Video playback failed:', error);
                    // 如果播放失败，重新显示播放按钮
                    this.classList.remove('hidden');
                }
            });
        }
        
        // 视频播放结束时显示播放按钮
        videoPlayer.addEventListener('ended', function() {
            if (videoPlayOverlay) {
                videoPlayOverlay.classList.remove('hidden');
            }
        });
        
        // 视频暂停时显示播放按钮
        videoPlayer.addEventListener('pause', function() {
            if (videoPlayOverlay) {
                videoPlayOverlay.classList.remove('hidden');
            }
        });
        
        // 视频播放时隐藏播放按钮
        videoPlayer.addEventListener('play', function() {
            if (videoPlayOverlay) {
                videoPlayOverlay.classList.add('hidden');
            }
        });
    }
    
    // 视频播放按钮点击事件
    document.querySelectorAll('.view-video').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const videoPath = this.getAttribute('data-video-path');
            
            // 添加历史记录
            if (!isModalOpen) {
                window.history.pushState({ modal: 'video' }, '', window.location.pathname);
                isModalOpen = true;
                currentModalType = 'video';
            }
            
            if (window.innerWidth <= 576) {
                // 移动端视频播放器代码
                const mobileVideoContainer = document.getElementById('mobileVideoContainer');
                const mobileVideoPlayer = document.getElementById('mobileVideoPlayer');
                const playOverlay = document.getElementById('mobileVideoPlayOverlay');
                const closeButton = document.getElementById('mobileVideoClose');
                
                if (!mobileVideoContainer || !mobileVideoPlayer || !playOverlay || !closeButton) return;
                
                // 设置视频源
                mobileVideoPlayer.src = videoPath;
                mobileVideoPlayer.load();
                
                // 显示视频容器
                mobileVideoContainer.style.display = 'block';
                mobileVideoContainer.style.zIndex = '9999';
                
                // 显示播放按钮遮罩层
                playOverlay.classList.remove('hidden');
                
                // 修改关闭按钮事件
                const closeVideo = () => {
                    if (isModalOpen) {
                        isModalOpen = false;
                        currentModal = null;
                        currentModalType = null;
                        if (window.history.state && window.history.state.modal) {
                            window.history.back();
                        }
                    }
                    mobileVideoPlayer.pause();
                    mobileVideoPlayer.currentTime = 0;
                    mobileVideoPlayer.src = '';
                    mobileVideoContainer.style.display = 'none';
                    mobileVideoContainer.style.zIndex = '-1';
                };
                
                // 清除旧的事件监听器并添加新的
                closeButton.removeEventListener('click', closeButton._closeVideo);
                closeButton._closeVideo = closeVideo;
                closeButton.addEventListener('click', closeVideo);
                
                // 播放按钮点击事件
                const startPlayback = async () => {
                    try {
                        // 隐藏播放按钮
                        playOverlay.classList.add('hidden');
                        // 播放视频
                        await mobileVideoPlayer.play();
                    } catch (error) {
                        console.warn('Video playback failed:', error);
                        // 如果播放失败，重新显示播放按钮
                        playOverlay.classList.remove('hidden');
                    }
                };
                
                // 清除旧的事件监听器
                playOverlay.removeEventListener('click', playOverlay._startPlayback);
                playOverlay._startPlayback = startPlayback;
                playOverlay.addEventListener('click', startPlayback);
                
                // 视频播放结束时显示播放按钮
                mobileVideoPlayer.addEventListener('ended', () => {
                    playOverlay.classList.remove('hidden');
                });
            } else {
                // PC端：使用模态框播放
                currentModal = videoModal_bs;
                // 重置视频源和模态框样式
                videoPlayer.src = '';
                videoModalDialog.style.maxWidth = 'none';
                videoModalDialog.style.width = '';
                videoModalDialog.style.opacity = '0';
                
                // 加载视频获取尺寸
                const tempVideo = document.createElement('video');
                tempVideo.src = videoPath;
                
                tempVideo.onloadedmetadata = function() {
                    const videoWidth = this.videoWidth;
                    const videoHeight = this.videoHeight;
                    const maxWidth = window.innerWidth - 40; // 留出边距
                    const maxHeight = window.innerHeight - 120; // 留出边距和标题栏高度
                    
                    // 计算最终宽度，确保不超过浏览器视口
                    const ratio = Math.min(maxWidth / videoWidth, maxHeight / videoHeight, 1);
                    const finalWidth = Math.round(videoWidth * ratio);
                    
                    // 设置模态框宽度
                    videoModalDialog.style.width = finalWidth + 'px';
                    
                    // 设置视频源并显示模态框
                    videoPlayer.src = videoPath;
                    videoPlayer.load();
                    
                    // 显示播放按钮
                    videoPlayOverlay.classList.remove('hidden');
                    
                    // 播放按钮点击事件
                    const startPlayback = async () => {
                        try {
                            videoPlayOverlay.classList.add('hidden');
                            await videoPlayer.play();
                        } catch (error) {
                            console.warn('Video playback failed:', error);
                            videoPlayOverlay.classList.remove('hidden');
                        }
                    };
                    
                    // 清除旧的事件监听器并添加新的
                    videoPlayOverlay.removeEventListener('click', videoPlayOverlay._startPlayback);
                    videoPlayOverlay._startPlayback = startPlayback;
                    videoPlayOverlay.addEventListener('click', startPlayback);
                    
                    // 视频播放结束时显示播放按钮
                    videoPlayer.addEventListener('ended', () => {
                        videoPlayOverlay.classList.remove('hidden');
                    });
                    
                    videoModal_bs.show();
                    
                    // 添加过渡动画
                    setTimeout(() => {
                        videoModalDialog.style.opacity = '1';
                        videoModalDialog.style.transition = 'opacity 0.15s linear';
                    }, 50);
                };
                
                tempVideo.onerror = function() {
                    console.error('Failed to load video:', videoPath);
                };
                
                // 监听模态框关闭事件
                videoModal.addEventListener('hidden.bs.modal', function() {
                    if (isModalOpen) {
                        isModalOpen = false;
                        currentModal = null;
                        currentModalType = null;
                        if (window.history.state && window.history.state.modal) {
                            window.history.back();
                        }
                    }
                    videoPlayer.pause();
                    videoPlayer.currentTime = 0;
                    videoPlayer.src = '';
                    videoModalDialog.style.opacity = '0';
                    videoModalDialog.style.transition = '';
                    videoModalDialog.style.width = '';
                }, { once: true });
            }
        });
    });

    // 为所有编辑按钮添加点击事件
    document.querySelectorAll('.edit-record').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            try {
                const recordData = JSON.parse(this.getAttribute('data-record'));
                const modalId = `editModal${recordData.id}`;
                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    // 添加edit-modal类
                    const modalDialog = modalElement.querySelector('.modal-dialog');
                    modalDialog.classList.add('edit-modal');
                    
                    // 移除可能影响显示的属性
                    modalElement.style.removeProperty('padding-right');
                    modalElement.removeAttribute('aria-hidden');
                    
                    // 确保模态框正确显示
                    const modal = new bootstrap.Modal(modalElement, {
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });
                    
                    // 监听显示事件
                    modalElement.addEventListener('shown.bs.modal', function() {
                        // 确保焦点正确设置
                        const firstInput = this.querySelector('input, select, textarea');
                        if (firstInput) {
                            firstInput.focus();
                        }
                    }, { once: true });
                    
                    modal.show();
                    // 重新初始化分类
                    updateEditCategories(recordData.type, recordData.id, recordData.category_id);
                }
            } catch (error) {
                console.error('解析记录数据失败:', error);
            }
        });
    });

    // 辅助函数：最大化模态框
    function maximizeModal(modal, icon, button) {
        modal.classList.add('maximized');
        if (icon) {
            icon.classList.remove('bi-fullscreen');
            icon.classList.add('bi-fullscreen-exit');
        }
        if (button) {
            button.setAttribute('aria-label', '还原');
        }
    }
    
    // 辅助函数：还原模态框
    function restoreModal(modal, icon, button) {
        modal.classList.remove('maximized');
        if (icon) {
            icon.classList.remove('bi-fullscreen-exit');
            icon.classList.add('bi-fullscreen');
        }
        if (button) {
            button.setAttribute('aria-label', '最大化');
        }
    }
});
</script> 