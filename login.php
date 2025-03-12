<?php
require_once __DIR__ . '/includes/session.php';

header('Content-Type: application/json');

// 获取操作类型
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? 'login';

if ($action === 'logout') {
    // 处理退出登录
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    session_destroy();
    error_log('User logged out successfully');
    
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
    exit;
}

// 处理登录请求
try {
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    
    error_log('Username: ' . $username);
    
    // 验证用户名和密码
    if ($username === 'admin' && $password === 'lzw111115') {
        $_SESSION['admin_logged_in'] = true;
        
        error_log('Login successful');
        error_log('Session ID: ' . session_id());
        error_log('Session data: ' . print_r($_SESSION, true));
        
        // 确保 session 数据被写入
        session_write_close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'session_id' => session_id(),
            'session_data' => $_SESSION
        ]);
    } else {
        error_log('Login failed: invalid credentials');
        
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
    }
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// 确保所有输出都被发送
ob_end_flush();
?> 