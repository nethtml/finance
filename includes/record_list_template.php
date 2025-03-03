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
                    <td><?php echo htmlspecialchars($record['date']); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $record['type'] === '收入' ? 'success' : 'danger'; ?> me-1">
                            <?php echo htmlspecialchars($record['type']); ?>
                        </span>
                        <span class="badge bg-secondary">
                            <?php echo htmlspecialchars($record['category_name']); ?>
                        </span>
                    </td>
                    <td>￥<?php echo formatAmount($record['amount']); ?></td>
                    <td>
                        <?php if (!empty($record['attachment'])): ?>
                            <?php
                            $filePath = $record['attachment'];
                            $isImage = strpos($filePath, 'images/') !== false;
                            $isDocument = strpos($filePath, 'documents/') !== false;
                            $uploadPath = getUploadPath($filePath);
                            ?>
                            
                            <?php if ($isImage): ?>
                                <button type="button" class="btn btn-sm btn-primary view-image" data-image-path="<?php echo $uploadPath; ?>">
                                    <i class="bi bi-image"></i> 查看图片
                                </button>
                            <?php elseif ($isDocument): ?>
                                <a href="<?php echo $uploadPath; ?>" class="btn btn-sm btn-secondary" target="_blank">
                                    <i class="bi bi-file-earmark"></i> 查看文件
                                </a>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-primary view-image" data-image-path="<?php echo $uploadPath; ?>">
                                    <i class="bi bi-image"></i> 查看附件
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($record['description']); ?></td>
                    <?php if ($showActions): ?>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary edit-record" data-record='<?php echo json_encode($record); ?>'>
                            编辑
                        </button>
                        <button type="button" class="btn btn-sm btn-danger delete-record" data-id="<?php echo $record['id']; ?>">
                            删除
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
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">图片预览</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" class="img-fluid" id="previewImage" alt="预览图片">
            </div>
        </div>
    </div>
</div> 