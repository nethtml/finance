<?php
/**
 * 流水记录管理页面
 * 
 * @version 1.0
 * @date 2024-03-xx
 */

session_start();

// 包含必要的文件
require_once '../includes/db.php';
require_once '../includes/functions.php';

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
    <div class="card-header">
        <h5 class="card-title mb-0">添加新记录</h5>
    </div>
    <div class="card-body">
        <form action="manage.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="date" class="form-label">日期</label>
                    <input type="date" class="form-control" id="date" name="date" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="type" class="form-label">类型</label>
                    <select class="form-select" id="type" name="type" required onchange="updateCategories(this.value)">
                        <option value="收入">收入</option>
                        <option value="支出">支出</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="category" class="form-label">分类</label>
                    <select class="form-select" id="category" name="category_id" required>
                        <option value="">请选择分类</option>
                    </select>
                </div>
                <div class="col-md-6">
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
                           accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx">
                    <div class="invalid-feedback">
                        请选择有效的文件
                    </div>
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
                <div class="form-text">
                    <i class="bi bi-info-circle me-1"></i>
                    支持的格式：图片(JPG, PNG, GIF)、PDF、Word、Excel
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">添加记录</button>
        </form>
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
<div class="modal fade" id="editModal<?php echo $record['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">编辑记录</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="manage.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                    
                    <div class="mb-3">
                        <label for="editDate<?php echo $record['id']; ?>" class="form-label">日期</label>
                        <input type="date" class="form-control" 
                               id="editDate<?php echo $record['id']; ?>" 
                               name="date" value="<?php echo $record['date']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="editType<?php echo $record['id']; ?>" class="form-label">类型</label>
                        <select class="form-select" id="editType<?php echo $record['id']; ?>" 
                                name="type" required onchange="updateEditCategories(this.value, <?php echo $record['id']; ?>, <?php echo $record['category_id']; ?>)">
                            <option value="收入" <?php echo $record['type'] === '收入' ? 'selected' : ''; ?>>
                                收入
                            </option>
                            <option value="支出" <?php echo $record['type'] === '支出' ? 'selected' : ''; ?>>
                                支出
                            </option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editCategory<?php echo $record['id']; ?>" class="form-label">分类</label>
                        <select class="form-select" id="editCategory<?php echo $record['id']; ?>" 
                                name="category_id" required>
                            <option value="">请选择分类</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editAmount<?php echo $record['id']; ?>" class="form-label">金额</label>
                        <input type="number" class="form-control" 
                               id="editAmount<?php echo $record['id']; ?>" 
                               name="amount" step="0.01" 
                               value="<?php echo $record['amount']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editDescription<?php echo $record['id']; ?>" class="form-label">
                            描述
                        </label>
                        <input type="text" class="form-control" 
                               id="editDescription<?php echo $record['id']; ?>" 
                               name="description" 
                               value="<?php echo xssFilter($record['description']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editAttachment<?php echo $record['id']; ?>" class="form-label">
                            附件
                        </label>
                        <?php if ($record['attachment']): ?>
                            <div class="mb-2">
                                <label class="form-label text-muted">当前附件：</label>
                                <?php
                                $filePath = $record['attachment'];
                                $isImage = strpos($filePath, 'images/') !== false;
                                $uploadPath = getUploadPath($filePath);
                                
                                if ($isImage): ?>
                                    <!-- 现有图片预览 -->
                                    <div class="card" style="max-width: 300px;">
                                        <div class="card-body p-2">
                                            <div class="preview-image-container">
                                                <img src="<?php echo $uploadPath; ?>" 
                                                     class="preview-image img-fluid" 
                                                     style="max-height: 150px; width: auto;"
                                                     alt="当前图片">
                                            </div>
                                        </div>
                                    </div>
                                <?php else: 
                                    $fileName = basename($record['attachment']);
                                ?>
                                    <div class="d-block">
                                        <i class="bi bi-file-earmark"></i>
                                        <?php echo $fileName; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="input-group">
                            <input type="file" class="form-control" 
                                   id="editAttachment<?php echo $record['id']; ?>" 
                                   name="attachment"
                                   accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx">
                            <div class="invalid-feedback">
                                请选择有效的文件
                            </div>
                        </div>
                        <!-- 新图片预览容器 -->
                        <div id="editPreviewContainer<?php echo $record['id']; ?>" class="mt-3 d-none">
                            <div class="card" style="max-width: 300px;">
                                <div class="card-body p-2">
                                    <div class="preview-image-container">
                                        <img id="editImagePreview<?php echo $record['id']; ?>" 
                                             class="preview-image img-fluid" 
                                             style="max-height: 150px; width: auto;"
                                             alt="新图片预览">
                                    </div>
                                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2" 
                                            id="editRemovePreview<?php echo $record['id']; ?>" 
                                            aria-label="关闭预览"></button>
                                </div>
                            </div>
                        </div>
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            支持的格式：图片(JPG, PNG, GIF)、PDF、Word、Excel
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">确认删除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>确定要删除这条记录吗？此操作不可恢复。</p>
            </div>
            <div class="modal-footer">
                <form action="manage.php" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-danger">确认删除</button>
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

// 页面加载时初始化分类
document.addEventListener('DOMContentLoaded', function() {
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
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                    // 重新初始化分类
                    updateEditCategories(recordData.type, recordData.id, recordData.category_id);
                }
            } catch (error) {
                console.error('解析记录数据失败:', error);
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?> 