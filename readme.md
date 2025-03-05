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

网站设计以简洁为主，避免复杂化，同时注重模块化和标准化。

---

## 2. 技术栈

- **前端**：
  - Bootstrap 5.3.x (通过CDN)
  - ECharts 5.4.x (通过CDN)
- **后端**：
  - PHP 7.4.3 NTS
  - MySQL 8.0 (InnoDB)
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
│   └── documents/        # 文档类附件
├── includes/             # 公共模块
│   ├── header.php        # 头部导航
│   ├── footer.php        # 底部信息
│   ├── db.php            # 数据库连接
│   └── functions.php     # 通用函数
├── pages/                # 页面模块
│   ├── dashboard.php     # 仪表盘页面
│   ├── records.php       # 流水列表页面
│   └── manage.php        # 流水记录管理页面
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
| type         | ENUM('收入', '支出') | 流水类型       |
| category     | VARCHAR(50)  | 分类名称           |
| amount       | DECIMAL(10,2)| 金额               |
| description  | VARCHAR(255) | 描述               |
| date         | DATE         | 日期               |
| attachment   | VARCHAR(255) | 凭证附件路径       |
| created_at   | TIMESTAMP    | 创建时间           |
| updated_at   | TIMESTAMP    | 更新时间           |

#### SQL语句：
```sql
CREATE TABLE records (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    type ENUM('收入', '支出') NOT NULL,
    category VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description VARCHAR(255),
    date DATE NOT NULL,
    attachment VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 5. 前端设计

### 5.1 仪表盘页面 (`dashboard.php`)
- 使用 **ECharts** 展示以下数据：
  - 总收入、总支出
  - 月度收入/支出趋势图
- 使用 **Bootstrap** 布局，确保页面响应式。

### 5.2 流水列表页面 (`records.php`)
- 使用 **Bootstrap Table** 展示所有流水记录，列顺序为：日期、类型、金额、描述、凭证。
- 凭证列显示"查看"或"下载"按钮，支持图片预览或文档下载。
- 支持分页和搜索功能。

### 5.3 流水记录管理页面 (`manage.php`)
- 提供表单用于添加、编辑和删除流水记录。
- 支持上传凭证附件（图片或文档），文件分类存放到相应目录：
  - 图片文件：`uploads/images/`
  - PDF文件：`uploads/pdf/`
  - 文档文件：`uploads/documents/`
  - 音频文件：`uploads/music/`
  - 视频文件：`uploads/videos/`
  - 其他文件：`uploads/misc/`
- 使用 **Bootstrap Modal** 实现弹窗编辑功能。

---

## 6. 后端设计

### 6.1 数据库连接 (`includes/db.php`)
```php
<?php
$host = 'localhost';
$dbname = 'finance_db';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}
?>
```

### 6.2 通用函数 (`includes/functions.php`)
- 封装常用的函数，如数据验证、文件上传、格式化等。

#### 文件上传函数示例：
```php
function uploadFile($file, $type) {
    $uploadDir = $type === 'image' ? 'uploads/images/' : 'uploads/documents/';
    $fileName = uniqid() . '_' . basename($file['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return $targetFile;
    } else {
        return false;
    }
}
```

### 6.3 流水记录管理逻辑 (`pages/manage.php`)
- 处理表单提交，实现增删改查操作。
- 使用预处理语句防止SQL注入。
- 处理文件上传逻辑，保存附件路径到数据库。

---

## 7. 功能实现

### 7.1 仪表盘页面
- 查询数据库，计算总收入、总支出。
- 使用 ECharts 绘制月度趋势图。

### 7.2 流水列表页面
- 查询所有记录并分页显示，列顺序为：日期、类型、金额、描述、凭证。
- 凭证列根据文件类型显示"查看"或"下载"按钮。
  - 图片：点击查看大图（使用 Bootstrap Modal 实现）。
  - 文档：点击下载文件。

### 7.3 流水记录管理
- 添加记录：插入新记录到数据库，上传附件并保存路径。
- 编辑记录：更新现有记录，支持附件更新。
- 删除记录：从数据库中删除记录，同时删除对应的附件文件。

---

## 8. 部署与上线

### 8.1 本地开发环境
- 使用 phpstudy配置 PHP + MySQL + Nginx 环境。
- 使用 Adminer 管理数据库。

### 8.2 上线部署
- 将代码上传到服务器。
- 配置 Nginx 虚拟主机，确保 `uploads` 目录可写。
- 导入数据库并配置连接信息。

---

## 9. 实用建议与工具

### 9.1 开发工具
- **cursor**：代码编辑器，安装 PHP Intelephense 插件。
- **Postman**：测试 API 接口。
- **Git**：版本控制。

### 9.2 性能优化
- 压缩静态资源（CSS、JS）。

### 9.3 安全性
- 使用 HTTPS 加密传输。
- 防止 SQL 注入和 XSS 攻击。
- 限制上传文件类型和大小，防止恶意文件上传。

---

## 10. 流程图

以下是财务管理网站的主要流程描述，你可以根据描述绘制流程图：

### 10.1 用户访问流程
1. **用户访问网站**：
   - 进入首页 (`index.php`)。
   - 导航到仪表盘 (`dashboard.php`)、流水列表 (`records.php`) 或管理页面 (`manage.php`)。

2. **仪表盘页面**：
   - 查询数据库，获取统计数据。
   - 使用 ECharts 渲染图表。

3. **流水列表页面**：
   - 查询数据库，获取所有流水记录。
   - 渲染表格，显示日期、类型、金额、描述、凭证。
   - 凭证列提供"查看"或"下载"按钮。

4. **流水记录管理页面**：
   - 添加记录：用户填写表单，上传附件，提交后保存到数据库。
   - 编辑记录：用户修改表单内容，更新附件，提交后更新数据库。
   - 删除记录：用户点击删除按钮，从数据库和文件系统中删除记录及附件。

### 10.2 文件上传流程
1. 用户选择文件（图片或文档）。
2. 文件上传到服务器，保存到 `uploads/images` 或 `uploads/documents`。
3. 文件路径保存到数据库的 `attachment` 字段。
4. 在流水列表页面，根据文件类型显示"查看"或"下载"按钮。

### 10.3 数据流图
1. **前端**：用户通过浏览器访问页面，提交表单或请求数据。
2. **后端**：PHP 处理请求，查询或更新数据库，返回结果。
3. **数据库**：存储流水记录和附件路径。
4. **文件系统**：存储上传的图片和文档。

---

## 11. 总结
本项目通过 PHP + MySQL + Nginx + Bootstrap + ECharts 技术栈实现了一个简单的财务管理网站。新增的凭证附件功能增强了实用性，项目结构清晰，功能模块化，适合初学者学习和实践。未来可以扩展更多功能，如用户权限管理、数据导出等。

---
