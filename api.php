<?php
require __DIR__ . '/config.php';

// 跨域配置
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// 路由控制
$action = $_GET['action'] ?? '';
$authToken = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

// 简单认证（可选）
if (!empty($authToken) && $authToken !== 'Bearer your_secret_key') {
    http_response_code(401);
    die(json_encode(['error' => '未经授权的访问']));
}

try {
    switch ($action) {
        case 'create_record':
            createRecord();
            break;
        case 'get_records':
            getRecords();
            break;
        case 'get_stats':
            getStatistics();
            break;
        case 'upload_image':
            uploadImage();
            break;
        default:
            throw new Exception('无效的API请求');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

// 创建记录
function createRecord() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // 数据验证
    $requiredFields = ['date', 'category'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception("缺少必填字段: $field");
        }
    }
    
    $stmt = $pdo->prepare("INSERT INTO records 
        (date, category, income, expense, image, note)
        VALUES (:date, :category, :income, :expense, :image, :note)");
        
// 替换为以下内容（添加类型转换）
$stmt->execute([
    ':date'    => $data['date'],
    ':category'=> $data['category'],
    ':income'  => (float)($data['income'] ?? 0),  // 强制转换为浮点数
    ':expense' => (float)($data['expense'] ?? 0), // 强制转换为浮点数
    ':image'   => $data['image'] ?? '',
    ':note'    => $data['note'] ?? ''
]);

    echo json_encode([
        'status' => 'success',
        'id' => $pdo->lastInsertId(),
        'balance' => calculateBalance()
    ]);
}

// 获取记录
function getRecords() {
    global $pdo;
    
   // 修改后（升序）
$stmt = $pdo->query("SELECT 
    id, 
    DATE_FORMAT(date, '%Y-%m-%d') AS date,
    category,
    income,
    expense,
    image,
    note,
    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') AS created_at
    FROM records 
    ORDER BY date ASC");  // 关键修改点
    
    echo json_encode($stmt->fetchAll());
}

// 获取统计
function getStatistics() {
    echo json_encode([
        'total_income'  => getTotal('income'),
        'total_expense' => getTotal('expense'),
        'balance'       => calculateBalance()
    ]);
}

// 图片上传
function uploadImage() {
    $uploadDir = __DIR__ . '/uploads/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $file = $_FILES['image'] ?? null;
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('文件上传失败');
    }
    
    // 验证文件类型
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif'
    ];
    
    if (!array_key_exists($mime, $allowedTypes)) {
        throw new Exception('仅支持JPG/PNG/GIF格式');
    }
    
    // 生成安全文件名
    $filename = uniqid('img_') . '.' . $allowedTypes[$mime];
    $targetPath = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('文件保存失败');
    }
    
    echo json_encode(['url' => 'uploads/' . $filename]);
}

// 辅助函数
function getTotal($column) {
    global $pdo;
    $stmt = $pdo->query("SELECT SUM($column) AS total FROM records");
    return (float)($stmt->fetch()['total'] ?? 0);
}

function calculateBalance() {
    return getTotal('income') - getTotal('expense');
}