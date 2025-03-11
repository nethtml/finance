<?php
/**
 * 日趋势数据API
 * 
 * @version 1.0
 * @date 2024-03-xx
 */

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

try {
    // 获取请求的月份，默认为当前月
    $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
    
    // 验证月份格式
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        throw new Exception('无效的月份格式');
    }
    
    // 构建日期范围
    $startDate = $month . '-01';
    $endDate = date('Y-m-t', strtotime($startDate));
    
    // 查询指定月份的日趋势数据
    $sql = "SELECT 
                DATE_FORMAT(r.date, '%Y-%m-%d') as date,
                DATE_FORMAT(r.date, '%d日') as date_label,
                SUM(CASE WHEN rc.type = :income_type THEN r.amount ELSE 0 END) as income,
                SUM(CASE WHEN rc.type = :expense_type THEN r.amount ELSE 0 END) as expense
            FROM records r
            JOIN records_categories rc ON r.category_id = rc.id
            WHERE r.date BETWEEN :start_date AND :end_date
            GROUP BY DATE_FORMAT(r.date, '%Y-%m-%d'), DATE_FORMAT(r.date, '%d日')
            ORDER BY date DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':income_type' => '收入',
        ':expense_type' => '支出',
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ]);
    
    $dailyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 准备返回数据
    $result = [
        'dates' => [],
        'dateLabels' => [],
        'income' => [],
        'expense' => []
    ];
    
    // 填充数据
    foreach ($dailyData as $data) {
        $result['dates'][] = $data['date'];
        $result['dateLabels'][] = $data['date_label'];
        $result['income'][] = floatval($data['income']);
        $result['expense'][] = floatval($data['expense']);
    }
    
    // 如果没有数据，填充整个月的空数据
    if (empty($dailyData)) {
        $daysInMonth = date('t', strtotime($startDate));
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%s-%02d', $month, $day);
            $result['dates'][] = $date;
            $result['dateLabels'][] = sprintf('%d日', $day);
            $result['income'][] = 0;
            $result['expense'][] = 0;
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $result
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '数据库查询失败'
    ]);
    error_log('日趋势数据查询失败: ' . $e->getMessage());
} 