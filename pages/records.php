<?php
/**
 * 流水列表页面
 * 
 * @version 1.0
 * @date 2024-03-xx
 */

// 包含必要的文件
require_once '../includes/db.php';
require_once '../includes/functions.php';

// 处理搜索参数
$search = [
    'startDate' => getGetData('startDate', ''),
    'endDate' => getGetData('endDate', ''),
    'type' => getGetData('type', ''),
    'keyword' => getGetData('keyword', ''),
    'category_id' => getGetData('category_id', '')
];

// 分页参数
$page = max(1, intval(getGetData('page', 1)));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// 构建查询条件
$where = [];
$params = [];

if ($search['startDate']) {
    $where[] = "r.date >= :startDate";
    $params[':startDate'] = $search['startDate'];
}
if ($search['endDate']) {
    $where[] = "r.date <= :endDate";
    $params[':endDate'] = $search['endDate'];
}
if ($search['type']) {
    $where[] = "rc.type = :type";
    $params[':type'] = $search['type'];
}
if ($search['keyword']) {
    $where[] = "r.description LIKE :keyword";
    $params[':keyword'] = '%' . $search['keyword'] . '%';
}
if ($search['category_id']) {
    $where[] = "r.category_id = :category_id";
    $params[':category_id'] = $search['category_id'];
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// 获取总记录数
try {
    $countSql = "SELECT COUNT(*) FROM records r 
                 JOIN records_categories rc ON r.category_id = rc.id 
                 $whereClause";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    $totalPages = ceil($total / $perPage);
} catch (PDOException $e) {
    $message = "获取记录数失败：" . $e->getMessage();
    $messageType = "danger";
}

// 获取当前页记录
try {
    $sql = "SELECT r.*, rc.type, rc.name as category_name 
            FROM records r 
            JOIN records_categories rc ON r.category_id = rc.id 
            $whereClause 
            ORDER BY r.date DESC 
            LIMIT :offset, :limit";
    $stmt = $pdo->prepare($sql);
    
    // 绑定分页参数
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    
    // 绑定搜索参数
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $records = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "获取记录失败：" . $e->getMessage();
    $messageType = "danger";
    $records = [];
}

// 修改header/footer的相对路径
$isSubPage = true;
require_once '../includes/header.php';
?>

<!-- 搜索表单 -->
<div class="card mb-4">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-funnel me-2"></i>
        <h5 class="card-title mb-0 fw-bold">筛选记录</h5>
    </div>
    <div class="card-body">
        <form method="get" action="records.php" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">开始日期</label>
                <input type="date" class="form-control" name="startDate" 
                       value="<?php echo $search['startDate']; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">结束日期</label>
                <input type="date" class="form-control" name="endDate" 
                       value="<?php echo $search['endDate']; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">类型</label>
                <select class="form-select" name="type" id="type" onchange="updateCategories(this.value)">
                    <option value="">全部</option>
                    <option value="收入" <?php echo $search['type'] === '收入' ? 'selected' : ''; ?>>收入</option>
                    <option value="支出" <?php echo $search['type'] === '支出' ? 'selected' : ''; ?>>支出</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">分类</label>
                <select class="form-select" name="category_id" id="category">
                    <option value="">全部</option>
                    <?php if (!empty($search['type'])): ?>
                    <?php foreach (getCategories($pdo, $search['type']) as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $search['category_id'] === $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">关键词</label>
                <input type="text" class="form-control" name="keyword" 
                       value="<?php echo xssFilter($search['keyword']); ?>" 
                       placeholder="搜索描述...">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">筛选</button>
                <a href="records.php" class="btn btn-secondary">重置</a>
            </div>
        </form>
    </div>
</div>

<!-- 显示消息提示 -->
<?php if (isset($message)): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- 记录列表 -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <i class="bi bi-list-ul me-2"></i>
            <h5 class="card-title mb-0 fw-bold">全部记录</h5>
        </div>
        <span>共 <?php echo $total; ?> 条记录</span>
    </div>
    <div class="card-body">
        <?php 
        $showActions = false; // 不显示操作按钮
        require_once '../includes/record_list_template.php';
        ?>
        
        <!-- 分页 -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo ($page - 1); ?>&<?php echo http_build_query($search); ?>">
                        上一页
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query($search); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo ($page + 1); ?>&<?php echo http_build_query($search); ?>">
                        下一页
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- 图片预览Modal -->
<?php foreach ($records as $record): ?>
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
    if (!type) {
        const categorySelect = document.getElementById('category');
        categorySelect.innerHTML = '<option value="">全部</option>';
        return;
    }
    
    fetch(`get_categories.php?type=${type}`)
        .then(response => response.json())
        .then(result => {
            const categorySelect = document.getElementById('category');
            categorySelect.innerHTML = '<option value="">全部</option>';
            
            if (result.error) {
                console.error('获取分类失败:', result.error);
                return;
            }
            
            if (result.success && result.data) {
                result.data.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
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
    if (typeSelect && typeSelect.value) {
        updateCategories(typeSelect.value);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?> 