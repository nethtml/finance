<?php
/**
 * 获取分类的AJAX处理文件
 * 
 * @version 1.0
 * @date 2024-03-04
 */

require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $type = isset($_GET['type']) ? $_GET['type'] : null;

    if (!$type || !in_array($type, ['收入', '支出'])) {
        throw new Exception('无效的类型');
    }

    $sql = "SELECT id, name FROM records_categories WHERE type = :type ORDER BY name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':type' => $type]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($categories)) {
        echo json_encode(['error' => '未找到分类数据']);
    } else {
        echo json_encode(['success' => true, 'data' => $categories]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} 