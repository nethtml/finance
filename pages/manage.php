<?php
/**
 * 流水记录管理页面
 * 
 * @version 1.0
 * @date 2024-03-xx
 */

// 包含必要的文件
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// 验证是否登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // 添加调试信息
    error_log('Access denied: Not logged in');
    header('Location: ../index.php');
    exit;
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = getPostData('action');
    
    switch ($action) {
        case 'add':
            // 处理添加记录
            $type = getPostData('type');
            $categoryId = getPostData('category_id');
            $amount = getPostData('amount');
            $description = getPostData('description');
            $date = getPostData('date');
            
            // 处理文件上传
            $attachment = '';
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $attachment = uploadFile($_FILES['attachment']);
                if ($attachment === false) {
                    $message = "文件上传失败";
                    $messageType = "danger";
                    break;
                }
            }
            
            try {
                $sql = "INSERT INTO records (category_id, amount, description, date, attachment) 
                        VALUES (:category_id, :amount, :description, :date, :attachment)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':category_id' => $categoryId,
                    ':amount' => $amount,
                    ':description' => $description,
                    ':date' => $date,
                    ':attachment' => $attachment
                ]);
                $_SESSION['message'] = "记录添加成功！";
                $_SESSION['messageType'] = "success";
                header('Location: manage.php');
                exit;
            } catch (PDOException $e) {
                $message = "记录添加失败：" . $e->getMessage();
                $messageType = "danger";
            }
            break;
            
        case 'edit':
            // 处理编辑记录
            $id = getPostData('id');
            $categoryId = getPostData('category_id');
            $amount = getPostData('amount');
            $description = getPostData('description');
            $date = getPostData('date');
            
            try {
                $sql = "UPDATE records 
                        SET category_id = :category_id, amount = :amount, 
                            description = :description, date = :date 
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':id' => $id,
                    ':category_id' => $categoryId,
                    ':amount' => $amount,
                    ':description' => $description,
                    ':date' => $date
                ]);
                
                // 处理新文件上传
                if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                    // 获取旧文件信息
                    $sql = "SELECT attachment FROM records WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':id' => $id]);
                    $oldRecord = $stmt->fetch();
                    
                    $attachment = uploadFile($_FILES['attachment']);
                    if ($attachment === false) {
                        $message = "文件上传失败";
                        $messageType = "danger";
                        break;
                    }
                    
                    // 删除旧文件
                    if ($oldRecord && $oldRecord['attachment']) {
                        $oldFilePath = __DIR__ . '/../uploads/' . $oldRecord['attachment'];
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                    
                    // 更新附件路径
                    $sql = "UPDATE records SET attachment = :attachment WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':attachment' => $attachment, ':id' => $id]);
                }
                
                $_SESSION['message'] = "记录更新成功！";
                $_SESSION['messageType'] = "success";
                header('Location: manage.php');
                exit;
            } catch (PDOException $e) {
                $message = "记录更新失败：" . $e->getMessage();
                $messageType = "danger";
            }
            break;
            
        case 'delete':
            // 处理删除记录
            $id = getPostData('id');
            
            try {
                // 获取附件信息
                $sql = "SELECT attachment FROM records WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $id]);
                $record = $stmt->fetch();
                
                // 删除记录
                $sql = "DELETE FROM records WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $id]);
                
                // 删除附件文件
                if ($record && $record['attachment']) {
                    $filePath = __DIR__ . '/../uploads/' . $record['attachment'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                
                $message = "记录删除成功！";
                $messageType = "success";
            } catch (PDOException $e) {
                $message = "记录删除失败：" . $e->getMessage();
                $messageType = "danger";
            }
            break;
    }
}

// 获取所有记录
$records = getRecords($pdo);

// 修改header/footer的相对路径
$isSubPage = true;
require_once '../includes/header.php';
?>

<!-- 显示消息提示 -->
<?php if (isset($_SESSION['message'])): ?>
<div class="alert alert-<?php echo $_SESSION['messageType']; ?> alert-dismissible fade show" role="alert">
    <?php 
    echo $_SESSION['message']; 
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- 添加记录表单 -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">添加新记录</h5>
        <!-- 移动端添加按钮 -->
        <button class="btn btn-primary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#addRecordForm" aria-expanded="false" aria-controls="addRecordForm">
            <i class="bi bi-plus-circle"></i> 添加记录
        </button>
    </div>
    <div class="card-body">
        <!-- 在移动端默认折叠，大屏幕显示 -->
        <div class="collapse d-md-block" id="addRecordForm">
            <form action="manage.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="date" class="form-label">日期</label>
                        <input type="date" class="form-control" id="date" name="date" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="type" class="form-label">类型</label>
                        <select class="form-select" id="type" name="type" required onchange="updateCategories(this.value)">
                            <option value="支出">支出</option>
                            <option value="收入">收入</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">分类</label>
                        <select class="form-select" id="category" name="category_id" required>
                            <option value="">请选择分类</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="amount" class="form-label">金额</label>
                        <input type="number" class="form-control" id="amount" name="amount" 
                               step="0.01" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">描述</label>
                    <input type="text" class="form-control" id="description" name="description" required>
                </div>
                
                <div class="mb-3">
                    <label for="attachment" class="form-label">附件</label>
                    <div class="input-group">
                        <input type="file" class="form-control" id="attachment" name="attachment" 
                               accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,
                                       video/mp4,video/x-msvideo,video/quicktime,video/x-ms-wmv,video/x-flv,video/x-matroska,video/webm,video/3gpp,
                                       audio/*">
                        <div class="invalid-feedback">
                            请选择有效的文件
                        </div>
                    </div>
                    <div class="form-text">
                        <i class="bi bi-info-circle me-1"></i>
                        支持的格式：<br>
                        • 图片：所有常见图片格式（如JPG、PNG、GIF等）<br>
                        • 文档：PDF、Word(DOC/DOCX)、Excel(XLS/XLSX)、PPT(PPT/PPTX)<br>
                        • 视频：MP4、AVI、MOV、WMV、FLV、MKV、WEBM、3GP<br>
                        • 音频：所有常见音频格式（如MP3、WAV等）<br>
                        • 其他文件将保存在misc目录
                    </div>
                    <!-- 预览容器 -->
                    <div id="preview-container" class="mt-3 d-none">
                        <div class="card" style="max-width: 300px;">
                            <div class="card-body p-2 text-center position-relative">
                                <div style="width: 100%; height: 150px; display: flex; align-items: center; justify-content: center;">
                                    <img id="image-preview" class="rounded" 
                                         style="max-width: 100%; max-height: 150px; width: auto; height: auto; object-fit: contain;" 
                                         alt="图片预览">
                                </div>
                                <button type="button" class="btn-close position-absolute top-0 end-0 m-2" 
                                        id="remove-preview" aria-label="关闭预览"></button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">添加记录</button>
            </form>
        </div>
    </div>
</div>

<!-- 记录列表 -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">记录管理</h5>
    </div>
    <div class="card-body">
        <?php 
        $showActions = true; // 显示操作按钮
        require_once '../includes/record_list_template.php';
        ?>
    </div>
</div>

<!-- 编辑记录Modal -->
<?php foreach ($records as $record): ?>
<div class="modal fade" id="editModal<?php echo $record['id']; ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog edit-modal" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $record['id']; ?>">编辑记录</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
            </div>
            <form action="manage.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editDate<?php echo $record['id']; ?>" class="form-label">日期</label>
                            <input type="date" class="form-control" 
                                   id="editDate<?php echo $record['id']; ?>" 
                                   name="date" value="<?php echo $record['date']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editAmount<?php echo $record['id']; ?>" class="form-label">金额</label>
                            <input type="number" class="form-control" 
                                   id="editAmount<?php echo $record['id']; ?>" 
                                   name="amount" step="0.01" 
                                   value="<?php echo $record['amount']; ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editType<?php echo $record['id']; ?>" class="form-label">类型</label>
                            <select class="form-select" id="editType<?php echo $record['id']; ?>" 
                                    name="type" required onchange="updateEditCategories(this.value, <?php echo $record['id']; ?>, <?php echo $record['category_id']; ?>)">
                                <option value="收入" <?php echo $record['type'] === '收入' ? 'selected' : ''; ?>>收入</option>
                                <option value="支出" <?php echo $record['type'] === '支出' ? 'selected' : ''; ?>>支出</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editCategory<?php echo $record['id']; ?>" class="form-label">分类</label>
                            <select class="form-select" id="editCategory<?php echo $record['id']; ?>" 
                                    name="category_id" required>
                                <option value="">请选择分类</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editDescription<?php echo $record['id']; ?>" class="form-label">描述</label>
                        <input type="text" class="form-control" 
                               id="editDescription<?php echo $record['id']; ?>" 
                               name="description" 
                               value="<?php echo xssFilter($record['description']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editAttachment<?php echo $record['id']; ?>" class="form-label">附件</label>
                        <?php if ($record['attachment']): ?>
                            <div class="current-attachment mb-2">
                                <?php
                                $filePath = $record['attachment'];
                                $isImage = strpos($filePath, 'images/') !== false;
                                $uploadPath = getUploadPath($filePath);
                                
                                if ($isImage): ?>
                                    <div class="preview-image-container">
                                        <img src="<?php echo $uploadPath; ?>" 
                                             class="preview-image" 
                                             alt="当前图片">
                                    </div>
                                <?php else: 
                                    $fileName = basename($record['attachment']);
                                ?>
                                    <div class="current-file">
                                        <i class="bi bi-file-earmark me-1"></i>
                                        <span><?php echo $fileName; ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="input-group">
                            <input type="file" class="form-control edit-file-input" 
                                   id="editAttachment<?php echo $record['id']; ?>" 
                                   name="attachment"
                                   accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
                                   data-preview-container="editPreviewContainer<?php echo $record['id']; ?>">
                        </div>
                        <div class="form-text">支持图片、PDF、Word、Excel等格式</div>
                        
                        <!-- 添加预览容器 -->
                        <div id="editPreviewContainer<?php echo $record['id']; ?>" class="mt-3 d-none">
                            <div class="card" style="max-width: 300px;">
                                <div class="card-body p-2 text-center position-relative">
                                    <div style="width: 100%; height: 150px; display: flex; align-items: center; justify-content: center;">
                                        <img id="editImagePreview<?php echo $record['id']; ?>" class="rounded edit-image-preview" 
                                             style="max-width: 100%; max-height: 150px; width: auto; height: auto; object-fit: contain;" 
                                             alt="图片预览">
                                    </div>
                                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-preview" 
                                            aria-label="关闭预览"></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存修改</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 删除确认Modal -->
<div class="modal fade" id="deleteModal<?php echo $record['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 320px;">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title fs-6">确认删除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-0 text-center">确定要删除这条记录吗？此操作不可恢复。</p>
            </div>
            <div class="modal-footer py-2">
                <form action="manage.php" method="post" class="d-flex gap-2 w-100 justify-content-center">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-danger btn-sm">确认删除</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 图片预览Modal -->
<?php if ($record['attachment'] && strpos($record['attachment'], 'images/') !== false): ?>
<div class="modal fade" id="imageModal<?php echo $record['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">图片预览</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="../uploads/<?php echo $record['attachment']; ?>" class="img-fluid" alt="附件图片">
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>

<script>
// 更新分类选项
function updateCategories(type) {
    fetch(`get_categories.php?type=${type}`)
        .then(response => response.json())
        .then(result => {
            const categorySelect = document.getElementById('category');
            categorySelect.innerHTML = '<option value="">请选择分类</option>';
            
            if (result.error) {
                console.error('获取分类失败:', result.error);
                return;
            }
            
            if (result.success && result.data) {
                result.data.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;  // 使用id作为值
                    option.textContent = category.name;
                    categorySelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('获取分类失败:', error);
        });
}

// 更新编辑模态框中的分类选项
function updateEditCategories(type, recordId, currentCategoryId = null) {
    fetch(`get_categories.php?type=${type}`)
        .then(response => response.json())
        .then(result => {
            const categorySelect = document.getElementById(`editCategory${recordId}`);
            categorySelect.innerHTML = '<option value="">请选择分类</option>';
            
            if (result.error) {
                console.error('获取分类失败:', result.error);
                return;
            }
            
            if (result.success && result.data) {
                result.data.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;  // 使用id作为值
                    option.textContent = category.name;
                    if (currentCategoryId && category.id == currentCategoryId) {
                        option.selected = true;
                    }
                    categorySelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('获取分类失败:', error);
        });
}

// 页面加载时初始化分类和文件预览功能
document.addEventListener('DOMContentLoaded', function() {
    // 初始化分类和其他功能
    const typeSelect = document.getElementById('type');
    if (typeSelect) {
        updateCategories(typeSelect.value);
    }
    
    // 初始化所有编辑模态框的分类
    <?php foreach ($records as $record): ?>
    updateEditCategories('<?php echo $record['type']; ?>', <?php echo $record['id']; ?>, <?php echo $record['category_id']; ?>);
    <?php endforeach; ?>
    
    // 编辑按钮点击事件
    document.querySelectorAll('.edit-record').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            try {
                const recordData = JSON.parse(this.getAttribute('data-record'));
                const modalId = `editModal${recordData.id}`;
                const modalElement = document.getElementById(modalId);
                
                if (modalElement) {
                    // 创建一个新的 Modal 实例
                    const modal = new bootstrap.Modal(modalElement, {
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });
                    
                    // 保存当前焦点元素到模态框元素上
                    modalElement._lastFocusedElement = document.activeElement;
                    
                    // 监听显示事件
                    modalElement.addEventListener('show.bs.modal', function() {
                        modalElement.removeAttribute('aria-hidden');
                        document.body.style.overflow = 'hidden';
                    }, { once: true });
                    
                    modalElement.addEventListener('shown.bs.modal', function() {
                        const firstInput = this.querySelector('input[type="date"]');
                        if (firstInput) {
                            firstInput.focus();
                        }
                    }, { once: true });
                    
                    // 监听隐藏事件
                    modalElement.addEventListener('hide.bs.modal', function() {
                        document.body.style.overflow = '';
                        const focusedElement = document.activeElement;
                        if (focusedElement && this.contains(focusedElement)) {
                            focusedElement.blur();
                        }
                    }, { once: true });
                    
                    modalElement.addEventListener('hidden.bs.modal', function() {
                        // 移除可能影响显示的属性
                        this.removeAttribute('aria-hidden');
                        this.style.removeProperty('padding-right');
                        
                        // 获取之前保存的焦点元素
                        const elementToFocus = this._lastFocusedElement;
                        
                        // 清理引用
                        this._lastFocusedElement = null;
                        
                        // 安全地恢复焦点
                        if (elementToFocus && document.body.contains(elementToFocus)) {
                            try {
                                requestAnimationFrame(() => {
                                    elementToFocus.focus();
                                });
                            } catch (e) {
                                console.warn('Failed to restore focus:', e);
                            }
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
    
    // 为编辑模态框中的文件输入添加预览功能
    document.querySelectorAll('.edit-file-input').forEach(input => {
        input.addEventListener('change', function(e) {
            const file = this.files[0];
            if (!file) return;
            
            const previewContainerId = this.getAttribute('data-preview-container');
            const previewContainer = document.getElementById(previewContainerId);
            if (!previewContainer) return;
            
            // 隐藏当前附件预览（如果存在）
            const currentAttachment = this.closest('.mb-3').querySelector('.current-attachment');
            if (currentAttachment) {
                currentAttachment.style.display = 'none';
            }
            
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                const recordId = previewContainerId.replace('editPreviewContainer', '');
                const previewImage = document.getElementById(`editImagePreview${recordId}`);
                
                reader.onload = function(e) {
                    if (previewImage) {
                        previewImage.style.display = '';
                        previewImage.src = e.target.result;
                        previewContainer.classList.remove('d-none');
                    }
                };
                reader.readAsDataURL(file);
            } else {
                // 如果不是图片，显示文件名
                previewContainer.classList.remove('d-none');
                const recordId = previewContainerId.replace('editPreviewContainer', '');
                const previewImage = document.getElementById(`editImagePreview${recordId}`);
                if (previewImage) {
                    previewImage.style.display = 'none';
                }
                // 添加文件名显示
                let fileNameDisplay = previewContainer.querySelector('.file-name-display');
                if (!fileNameDisplay) {
                    fileNameDisplay = document.createElement('div');
                    fileNameDisplay.className = 'file-name-display mt-2';
                    previewContainer.querySelector('.card-body').appendChild(fileNameDisplay);
                }
                fileNameDisplay.innerHTML = `<i class="bi bi-file-earmark"></i> ${file.name}`;
            }
        });
    });
    
    // 为编辑模态框中的预览移除按钮添加事件
    document.querySelectorAll('.remove-preview').forEach(button => {
        button.addEventListener('click', function() {
            const previewContainer = this.closest('.mt-3');
            if (previewContainer) {
                previewContainer.classList.add('d-none');
                const previewImage = previewContainer.querySelector('.edit-image-preview');
                if (previewImage) {
                    previewImage.src = '';
                    previewImage.style.display = '';
                }
                // 清除文件名显示
                const fileNameDisplay = previewContainer.querySelector('.file-name-display');
                if (fileNameDisplay) {
                    fileNameDisplay.remove();
                }
                // 清除文件输入
                const fileInput = previewContainer.closest('.mb-3').querySelector('.edit-file-input');
                if (fileInput) {
                    fileInput.value = '';
                }
                // 显示当前附件预览（如果存在）
                const currentAttachment = fileInput.closest('.mb-3').querySelector('.current-attachment');
                if (currentAttachment) {
                    currentAttachment.style.display = '';
                }
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?> 