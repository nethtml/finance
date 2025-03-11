<?php
/**
 * 通用函数库
 * 
 * @version 1.0
 * @date 2024-03-xx
 */

/**
 * 获取安全的POST数据
 * @param string $key POST键名
 * @param mixed $default 默认值
 * @return mixed
 */
function getPostData($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

/**
 * 获取安全的GET数据
 * @param string $key GET键名
 * @param mixed $default 默认值
 * @return mixed
 */
function getGetData($key, $default = '') {
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

/**
 * XSS过滤
 * @param string $string 需要过滤的字符串
 * @return string
 */
function xssFilter($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * 格式化金额显示
 * @param float $amount 金额
 * @return string
 */
function formatAmount($amount) {
    return number_format($amount, 2, '.', ',');
}

/**
 * 上传文件处理
 * @param array $file $_FILES数组元素
 * @param string $type 文件类型
 * @return string|false 成功返回文件路径，失败返回false
 */
function uploadFile($file) {
    // 检查文件上传错误
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // 获取文件MIME类型和扩展名
    $mimeType = mime_content_type($file['tmp_name']);
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // 定义视频文件扩展名
    $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm', '3gp', 'm4v'];
    
    // 确定文件类型和目标目录
    $targetDir = 'misc';
    if (strpos($mimeType, 'image/') === 0) {
        $targetDir = 'images';
    } elseif ($fileExt === 'pdf') {
        $targetDir = 'pdf';
    } elseif (in_array($fileExt, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'])) {
        $targetDir = 'documents';
    } elseif (strpos($mimeType, 'audio/') === 0) {
        $targetDir = 'music';
    } elseif (strpos($mimeType, 'video/') === 0 || in_array($fileExt, $videoExtensions)) {
        $targetDir = 'videos';
    }
    
    // 生成安全的文件名
    $fileName = date('Ymd_His_') . uniqid() . '.' . $fileExt;
    
    // 确定上传目录
    $uploadDir = __DIR__ . '/../uploads/' . $targetDir . '/';
    
    // 检查并创建目录
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return false;
        }
    }
    
    $targetFile = $uploadDir . $fileName;
    
    // 移动文件
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        chmod($targetFile, 0644);
        return $targetDir . '/' . $fileName;
    }
    
    return false;
}

/**
 * 获取指定类型的分类列表
 * @param PDO $pdo 数据库连接
 * @param string $type 类型（收入/支出）
 * @return array 分类列表
 */
function getCategories($pdo, $type = null) {
    try {
        $sql = "SELECT id, name FROM records_categories";
        $params = [];
        
        if ($type) {
            $sql .= " WHERE type = :type";
            $params[':type'] = $type;
        }
        
        $sql .= " ORDER BY name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("获取分类失败：" . $e->getMessage());
        return [];
    }
}

/**
 * 获取分类信息
 * @param PDO $pdo 数据库连接
 * @param int $categoryId 分类ID
 * @return array|null 分类信息
 */
function getCategory($pdo, $categoryId) {
    try {
        $sql = "SELECT * FROM records_categories WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $categoryId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("获取分类信息失败：" . $e->getMessage());
        return null;
    }
}

/**
 * 获取记录的完整信息（包括分类信息）
 * @param PDO $pdo 数据库连接
 * @param int $recordId 记录ID
 * @return array|null 记录信息
 */
function getRecord($pdo, $recordId) {
    try {
        $sql = "SELECT r.*, rc.type, rc.name as category_name 
                FROM records r 
                JOIN records_categories rc ON r.category_id = rc.id 
                WHERE r.id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $recordId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("获取记录信息失败：" . $e->getMessage());
        return null;
    }
}

/**
 * 获取所有记录（包括分类信息）
 * @param PDO $pdo 数据库连接
 * @param array $filters 过滤条件
 * @return array 记录列表
 */
function getRecords($pdo, $filters = []) {
    try {
        $sql = "SELECT r.*, rc.type, rc.name as category_name 
                FROM records r 
                JOIN records_categories rc ON r.category_id = rc.id";
        $params = [];
        $where = [];
        
        if (!empty($filters['type'])) {
            $where[] = "rc.type = :type";
            $params[':type'] = $filters['type'];
        }
        
        if (!empty($filters['startDate'])) {
            $where[] = "r.date >= :startDate";
            $params[':startDate'] = $filters['startDate'];
        }
        
        if (!empty($filters['endDate'])) {
            $where[] = "r.date <= :endDate";
            $params[':endDate'] = $filters['endDate'];
        }
        
        if (!empty($filters['keyword'])) {
            $where[] = "r.description LIKE :keyword";
            $params[':keyword'] = '%' . $filters['keyword'] . '%';
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY r.date DESC, r.id DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT :offset, :limit";
            $params[':offset'] = $filters['offset'] ?? 0;
            $params[':limit'] = $filters['limit'];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("获取记录列表失败：" . $e->getMessage());
        return [];
    }
} 