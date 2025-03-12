---

# 技术文档：财务管理网站（Finance）

## 目录
1. **项目概述**
2. **技术栈**
3. **项目结构**
4. **数据库设计**
5. **前端设计**
6. **后端设计**
7. **功能实现**
8. **部署与上线**
9. **实用建议与工具**
10. **流程图**
11. **总结**

---

## 1. 项目概述

本项目旨在开发一个简单的财务管理网站，名为 **Finance**。网站主要功能包括：
- **仪表盘页面**：展示财务统计数据（如总收入、总支出、月度趋势等）。
- **流水列表页面**：展示所有财务流水记录，支持查看或下载凭证附件。
- **流水记录管理**：支持增删改查（CRUD）操作，支持上传凭证附件（图片或文档）。
- **用户认证**：管理员登录/退出功能，使用 session 进行状态管理。

网站设计以简洁为主，避免复杂化，同时注重模块化和标准化。

---

## 2. 技术栈

- **前端**：
  - Bootstrap 5.3.x (通过CDN)
  - ECharts 5.4.x (通过CDN)
  - Bootstrap Icons
- **后端**：
  - PHP 7.4.3 NTS
  - MySQL 8.0 (InnoDB)
  - Session 管理
- **Web服务器**：
  - Nginx 1.22 (with HTTP/2)
- **数据库管理工具**：Adminer
- **版本控制**：Git

---

## 3. 项目结构

```
finance/
├── assets/               # 静态资源
│   ├── css/              # Bootstrap CSS
│   ├── js/               # Bootstrap JS 和 ECharts JS
│   └── images/           # 图片资源
├── uploads/              # 上传文件目录
│   ├── images/           # 图片类附件
│   ├── pdf/             # PDF文件
│   ├── documents/        # 文档类附件
│   ├── music/           # 音频文件
│   ├── videos/          # 视频文件
│   └── misc/            # 其他类型文件
├── includes/             # 公共模块
│   ├── header.php        # 头部导航
│   ├── footer.php        # 底部信息
│   ├── db.php            # 数据库连接
│   ├── functions.php     # 通用函数
│   ├── session.php       # Session配置
│   └── path_helper.php   # 路径处理
├── pages/                # 页面模块
│   ├── dashboard.php     # 仪表盘页面
│   ├── records.php       # 流水列表页面
│   └── manage.php        # 流水记录管理页面
├── login.php             # 登录处理
├── index.php             # 入口文件
├── .htaccess             # URL重写规则（可选）
└── README.md             # 项目说明
```

---

## 4. 数据库设计

### 数据库名称：`finance_db`

#### 表：`records`（流水记录表）
| 字段名       | 类型         | 说明               |
|--------------|--------------|--------------------|
| id           | INT(11)      | 主键，自增         |
| category_id  | INT(11)      | 分类ID，外键       |
| amount       | DECIMAL(10,2)| 金额               |
| description  | VARCHAR(255) | 描述               |
| date         | DATE         | 日期               |
| attachment   | VARCHAR(255) | 凭证附件路径       |
| created_at   | TIMESTAMP    | 创建时间           |
| updated_at   | TIMESTAMP    | 更新时间           |

#### 表：`records_categories`（分类表）
| 字段名       | 类型         | 说明               |
|--------------|--------------|--------------------|
| id           | INT(11)      | 主键，自增         |
| name         | VARCHAR(50)  | 分类名称           |
| type         | ENUM('收入', '支出') | 分类类型   |
| created_at   | TIMESTAMP    | 创建时间           |
| updated_at   | TIMESTAMP    | 更新时间           |

#### SQL语句：
```sql
CREATE TABLE records_categories (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    type ENUM('收入', '支出') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE records (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    category_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description VARCHAR(255),
    date DATE NOT NULL,
    attachment VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES records_categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 5. 前端设计

### 5.1 仪表盘页面 (`dashboard.php`)
- 使用 **ECharts** 展示以下数据：
  - 总收入、总支出、结余
  - 月度收入/支出趋势图
  - 日收支趋势图
  - 收支分类占比图
- 使用 **Bootstrap** 布局，确保页面响应式。

### 5.2 流水列表页面 (`records.php`)
- 使用 **Bootstrap Table** 展示所有流水记录。
- 支持按日期、类型、金额、描述等字段筛选。
- 凭证列根据文件类型显示不同图标和操作按钮：
  - 图片：显示预览图标，点击查看大图
  - PDF：显示PDF图标，点击新窗口打开
  - 文档：显示文档图标，点击下载
  - 音频：显示音频图标，点击播放
  - 视频：显示视频图标，点击播放
- 支持分页和搜索功能。

### 5.3 流水记录管理页面 (`manage.php`)
- 提供表单用于添加、编辑和删除流水记录。
- 支持上传凭证附件，根据文件类型自动分类存储：
  - 图片文件：`uploads/images/`
  - PDF文件：`uploads/pdf/`
  - 文档文件：`uploads/documents/`
  - 音频文件：`uploads/music/`
  - 视频文件：`uploads/videos/`
  - 其他文件：`uploads/misc/`
- 使用 **Bootstrap Modal** 实现弹窗编辑功能。
- 支持附件预览和更换。

### 5.4 登录/退出功能
- 使用 **Bootstrap Modal** 实现登录和退出确认弹窗。
- 齿轮图标根据登录状态显示不同行为：
  - 未登录：显示登录表单
  - 已登录：导航到管理页面
- 退出按钮显示确认对话框。
- 支持 Cookie 检查和提示。

---

## 6. 后端设计

### 6.1 Session 管理 (`includes/session.php`)
```php
<?php
// 设置session配置
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

// 设置session保存路径
$sessionPath = 'D:/phpstudy/Extensions/tmp/tmp';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

// 启动会话
session_start();
?>
```

### 6.2 数据库连接 (`includes/db.php`)
```php
<?php
$host = 'localhost';
$dbname = 'finance_db';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}
?>
```

### 6.3 文件上传处理
```php
function uploadFile($file) {
    // 获取文件类型
    $fileType = mime_content_type($file['tmp_name']);
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // 根据文件类型决定存储目录
    if (strpos($fileType, 'image/') === 0) {
        $uploadDir = 'uploads/images/';
    } elseif ($fileType === 'application/pdf') {
        $uploadDir = 'uploads/pdf/';
    } elseif (strpos($fileType, 'video/') === 0) {
        $uploadDir = 'uploads/videos/';
    } elseif (strpos($fileType, 'audio/') === 0) {
        $uploadDir = 'uploads/music/';
    } elseif (in_array($fileExt, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'])) {
        $uploadDir = 'uploads/documents/';
    } else {
        $uploadDir = 'uploads/misc/';
    }
    
    // 创建目录（如果不存在）
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // 生成唯一文件名
    $fileName = uniqid() . '_' . basename($file['name']);
    $targetFile = $uploadDir . $fileName;

    // 移动文件
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return str_replace('uploads/', '', $targetFile);
    }
    
    return false;
}
```

### 6.4 登录处理 (`login.php`)
```php
<?php
require_once 'includes/session.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? 'login';

if ($action === 'logout') {
    $_SESSION = array();
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if ($username === 'admin' && $password === 'admin123') {
    $_SESSION['admin_logged_in'] = true;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
```

---

## 7. 功能实现

### 7.1 仪表盘页面
- 查询数据库，计算总收入、总支出和结余。
- 使用 ECharts 绘制月度趋势图和日趋势图。
- 支持月份切换和图表类型切换。

### 7.2 流水列表页面
- 支持多条件筛选：日期范围、类型、分类、关键词。
- 根据文件类型显示不同的预览/下载按钮。
- 移动端优化：响应式布局，滚动表格。

### 7.3 流水记录管理
- 添加记录：支持选择类型和分类，上传附件。
- 编辑记录：支持更换附件，自动删除旧文件。
- 删除记录：同步删除数据库记录和附件文件。

### 7.4 登录/退出功能
- 使用 session 管理登录状态。
- 支持 Cookie 检查和错误提示。
- 登录成功后自动跳转到管理页面。
- 退出时显示确认对话框。

---

## 8. 部署与上线

### 8.1 本地开发环境
- 使用 phpstudy 配置 PHP + MySQL + Nginx 环境。
- 配置 session 保存路径和权限。
- 创建并配置上传目录权限。

### 8.2 上线部署
- 将代码上传到服务器。
- 配置 Nginx 虚拟主机。
- 确保 `uploads` 目录和子目录可写。
- 配置 session 目录权限。
- 导入数据库并配置连接信息。

---

## 9. 实用建议与工具

### 9.1 开发工具
- **cursor**：代码编辑器，安装 PHP Intelephense 插件。
- **Postman**：测试 API 接口。
- **Git**：版本控制。
- **Chrome DevTools**：调试前端代码。

### 9.2 性能优化
- 压缩静态资源（CSS、JS）。
- 使用适当的图片格式和大小。
- 合理设置 session 过期时间。

### 9.3 安全性
- 使用 HTTPS 加密传输。
- 防止 SQL 注入和 XSS 攻击。
- 限制上传文件类型和大小。
- 使用安全的 session 配置。
- 实施 Cookie 安全策略。

---

## 10. 流程图

### 10.1 用户访问流程
1. **用户访问网站**：
   - 进入首页 (`index.php`)。
   - 导航到仪表盘或流水列表。
   - 点击齿轮图标进行登录。

2. **登录流程**：
   - 检查 Cookie 是否启用。
   - 显示登录表单。
   - 验证用户名和密码。
   - 创建 session 并跳转到管理页面。

3. **管理功能**：
   - 添加/编辑/删除流水记录。
   - 上传和管理附件。
   - 查看统计数据。

4. **退出流程**：
   - 显示退出确认对话框。
   - 清除 session 数据。
   - 刷新页面。

### 10.2 文件上传流程
1. 用户选择文件。
2. 检查文件类型和大小。
3. 根据文件类型选择存储目录。
4. 生成唯一文件名并保存。
5. 返回相对路径保存到数据库。

### 10.3 数据流图
1. **前端**：用户界面交互和表单提交。
2. **后端**：处理请求和 session 管理。
3. **数据库**：存储记录和分类数据。
4. **文件系统**：管理上传的附件。

---

## 11. 总结

本项目通过 PHP + MySQL + Bootstrap + ECharts 技术栈实现了一个功能完整的财务管理网站。主要特点包括：

1. **完整的用户认证**：实现了安全的登录/退出功能。
2. **文件管理**：支持多种类型文件的上传和预览。
3. **数据可视化**：使用 ECharts 实现直观的数据展示。
4. **响应式设计**：适配各种设备的显示需求。
5. **安全性考虑**：包含多层面的安全防护措施。

未来可以扩展的功能：
- 多用户支持
- 数据导出功能
- 更丰富的图表分析
- 自定义分类管理
- 批量导入导出

---
