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
                    <td><?php echo htmlspecialchars($record['description']); ?></td>
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
            <div class="modal-header py-2 draggable">
                <h5 class="modal-title">图片预览</h5>
                <div class="modal-buttons ms-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary me-2 maximize-btn" aria-label="最大化">
                        <i class="bi bi-fullscreen"></i>
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                </div>
            </div>
            <div class="modal-body p-0 text-center" style="background: #f8f9fa;">
                <img src="" id="previewImage" alt="预览图片" style="max-width: 100%; max-height: calc(100vh - 120px); object-fit: contain;">
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
    <div class="modal-dialog modal-dialog-centered" role="document" style="width: 400px;">
        <div class="modal-content">
            <div class="modal-header py-2 draggable">
                <h5 class="modal-title">音频播放</h5>
                <div class="modal-buttons ms-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary me-2 maximize-btn" aria-label="最大化">
                        <i class="bi bi-fullscreen"></i>
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                </div>
            </div>
            <div class="modal-body text-center">
                <h6 id="audioTitle" class="mb-3"></h6>
                <audio id="audioPlayer" controls class="w-100">
                    您的浏览器不支持音频播放。
                </audio>
            </div>
        </div>
    </div>
</div>

<!-- 视频播放模态框 -->
<div class="modal fade" id="videoPreviewModal" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title">视频播放</h5>
                <div class="modal-buttons ms-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary me-2 maximize-btn" aria-label="最大化">
                        <i class="bi bi-fullscreen"></i>
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                </div>
            </div>
            <div class="modal-body p-0 bg-dark">
                <div class="video-container position-relative">
                    <video id="videoPlayer" controls playsinline webkit-playsinline>
                        您的浏览器不支持视频播放。
                    </video>
                    <!-- PC端播放按钮遮罩层 -->
                    <div id="videoPlayOverlay" class="play-button-overlay d-flex align-items-center justify-content-center" 
                         style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); cursor: pointer;">
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
/* 修复编辑模态框的背景和定位问题 */
.modal.fade.show {
    background-color: rgba(0, 0, 0, 0.3);
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

/* 视频播放器样式 */
.video-container {
    background-color: #000;
    width: 100%;
    height: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

#videoPlayer, #mobileVideoPlayer {
    width: 100%;
    height: auto;
    max-height: calc(90vh - 60px);
    background-color: #000;
    object-fit: contain;
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
    /* 移动端列间距调整 */
    .table td:nth-child(1),
    .table td:nth-child(2),
    .table td:nth-child(3) {
        padding: 0.5rem 0.25rem !important;
    }
    
    .modal-dialog {
        margin: 0;
        width: 100% !important;
        max-width: 100% !important;
        height: 100%;
    }
    .modal-content {
        height: 100%;
        border: 0;
        border-radius: 0;
    }
    .video-container {
        height: calc(100vh - 56px);
    }
    #videoPlayer {
        max-height: calc(100vh - 56px);
    }
    
    /* 移动端按钮样式 */
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
    
    /* 优化类型标签的显示 */
    .d-flex.flex-wrap {
        gap: 0.25rem !important;
    }
    
    /* 确保说明文字在移动端正确换行 */
    .text-break {
        word-break: break-word !important;
        min-width: 150px;
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
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 添加最大化按钮功能
    document.querySelectorAll('.maximize-btn').forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            const icon = this.querySelector('.bi');
            
            if (modal.classList.contains('maximized')) {
                // 还原模态框
                restoreModal(modal, icon, this);
            } else {
                // 最大化模态框
                maximizeModal(modal, icon, this);
            }
        });
    });
    
    // 监听所有模态框的隐藏事件
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hide.bs.modal', function() {
            // 如果模态框是最大化状态，先还原
            if (this.classList.contains('maximized')) {
                const maximizeBtn = this.querySelector('.maximize-btn');
                const icon = maximizeBtn?.querySelector('.bi');
                if (maximizeBtn) {
                    restoreModal(this, icon, maximizeBtn);
                }
            }
            
            // 确保移除所有可能影响页面交互的样式
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            
            // 移除所有遮罩层
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                backdrop.remove();
            });
        });

        modal.addEventListener('hidden.bs.modal', function() {
            // 再次确保清理所有状态
            this.style.display = 'none';
            this.setAttribute('aria-hidden', 'true');
            this.removeAttribute('aria-modal');
            this.removeAttribute('role');
            
            // 确保页面可以滚动
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    });
    
    // 监听窗口大小变化
    window.addEventListener('resize', function() {
        const maximizedModals = document.querySelectorAll('.modal.maximized');
        maximizedModals.forEach(modal => {
            if (modal.classList.contains('maximized')) {
                const modalBody = modal.querySelector('.modal-body');
                if (modalBody) {
                    const headerHeight = modal.querySelector('.modal-header')?.offsetHeight || 0;
                    modalBody.style.height = `calc(100vh - ${headerHeight}px)`;
                }
            }
        });
    });

    // 原有的图片预览相关代码
    (function() {
        const imagePreviewModal = document.getElementById('imagePreviewModal');
        const modalDialog = imagePreviewModal.querySelector('.modal-dialog');
        const previewImage = document.getElementById('previewImage');
        const closeButton = imagePreviewModal.querySelector('.btn-close');
        let currentModal = null;
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
            
            // 移动端直接在新标签页打开PDF
            if (window.innerWidth <= 576) {
                window.open(pdfPath, '_blank');
                return;
            }
            
            // PC端使用模态框显示
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
            
            audioTitle.textContent = this.getAttribute('data-audio-name');
            audioPlayer.src = this.getAttribute('data-audio-path');
            audioModal.show();
        });
    });

    // 监听音频模态框事件
    audioPreviewModal.addEventListener('hidden.bs.modal', function() {
        audioPlayer.pause();
        audioPlayer.src = '';
        audioTitle.textContent = '';
        
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

    // 视频播放相关代码
    const videoModal = document.getElementById('videoPreviewModal');
    const videoPlayer = document.getElementById('videoPlayer');
    const videoPlayOverlay = document.getElementById('videoPlayOverlay');
    const mobileVideoContainer = document.getElementById('mobileVideoContainer');
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
            
            // 检查是否为移动设备
            if (window.innerWidth <= 576) {
                // 移动端：使用独立的视频播放器
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
                
                // 关闭按钮点击事件
                const closeVideo = () => {
                    mobileVideoPlayer.pause();
                    mobileVideoPlayer.currentTime = 0;
                    mobileVideoPlayer.src = '';
                    mobileVideoContainer.style.display = 'none';
                    mobileVideoContainer.style.zIndex = '-1';
                    playOverlay.classList.remove('hidden');
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
                const videoModal = document.getElementById('videoPreviewModal');
                const videoPlayer = document.getElementById('videoPlayer');
                const videoPlayOverlay = document.getElementById('videoPlayOverlay');
                
                // 确保所有必要的元素都存在
                if (!videoModal || !videoPlayer || !videoPlayOverlay) {
                    console.error('Video player elements not found');
                    return;
                }
                
                const modalDialog = videoModal.querySelector('.modal-dialog');
                if (!modalDialog) {
                    console.error('Modal dialog not found');
                    return;
                }
                
                // 重置视频源和模态框样式
                videoPlayer.src = '';
                modalDialog.style.maxWidth = 'none';
                modalDialog.style.width = '';
                modalDialog.style.opacity = '0';
                
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
                    modalDialog.style.width = finalWidth + 'px';
                    
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
                    
                    const modal = new bootstrap.Modal(videoModal);
                    modal.show();
                    
                    // 添加过渡动画
                    setTimeout(() => {
                        modalDialog.style.opacity = '1';
                        modalDialog.style.transition = 'opacity 0.15s linear';
                    }, 50);
                };
                
                tempVideo.onerror = function() {
                    console.error('Failed to load video:', videoPath);
                };
                
                // 监听模态框关闭事件
                videoModal.addEventListener('hidden.bs.modal', function() {
                    videoPlayer.pause();
                    videoPlayer.currentTime = 0;
                    videoPlayer.src = '';
                    modalDialog.style.opacity = '0';
                    modalDialog.style.transition = '';
                    modalDialog.style.width = '';
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