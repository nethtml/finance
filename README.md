# 财务管理系统

## 最近更新（2024-03-21）
### 代码重构
1. 将单页面应用重构为多页面应用
2. 提取并优化静态资源文件
3. 优化代码结构

### 当前目录结构

## 1. 项目概述

### 1.1 项目描述
一个基于PHP的个人财务管理系统，用于记录和管理个人收支情况。

### 1.2 技术栈
- 开发环境：Windows 10
- Web服务器：PHPStudy 7.4.3.nts
- 数据库：MySQL 5.7
- 数据库管理工具：Adminer
- 前端框架：Bootstrap
- 开发语言：原生PHP

### 1.3 项目结构
D:\phpstudy\WWW\
/
├── README.md # 开发文档
├── index.html # 空白首页
├── dashboard.html # 仪表盘页面
├── transactions.html # 流水记录页面
├── admin.html # 后台管理页面
├── adminmysql.php # 数据库管理
├── api.php # API处理
├── config.php # 数据库配置
├── docs/ # 文档目录
│ └── DEV_LOG.md # 开发日志
├── uploads/ # 上传目录
└── assets/ # 静态资源
├── css/
│ ├── bootstrap.min.css # Bootstrap样式
│ └── common.css # 公共样式
└── js/
├── bootstrap.bundle.min.js # Bootstrap脚本
└── common.js # 公共脚本
## 2. 环境配置

### 2.1 PHPStudy配置
- PHP版本：7.4.3.nts
- Web根目录：D:\phpstudy\WWW
- 默认站点：localhost

### 2.2 数据库配置
php
DB_HOST: localhost
DB_NAME: app_lizhenwei_cn
DB_USER: app_lizhenwei_cn
DB_PASS: C5RNa9XK7QPa

### 2.3 Adminer安装步骤
1. 下载Adminer程序文件
2. 将文件重命名为`adminer.php`
3. 上传到Web根目录：`D:\phpstudy\WWW\`
4. 访问：`http://localhost/adminer.php`

### 2.4 Bootstrap本地化步骤
1. 下载Bootstrap文件：
   - 访问 https://getbootstrap.com/docs/5.1/getting-started/download/
   - 下载编译后的CSS和JS文件
2. 在项目根目录创建assets文件夹：
docs/README.md
assets/
├── css/
│ └── bootstrap.min.css
└── js/
└── bootstrap.bundle.min.js

3. 修改index.html中的CDN链接为本地路径：
html
<!-- CSS -->
<link href="assets/css/bootstrap.min.css" rel="stylesheet">
<!-- JS -->
<script src="assets/js/bootstrap.bundle.min.js"></script>

## 3. API接口文档

### 3.1 创建记录
- 请求：POST /api.php?action=create_record
- 参数：
  ```json
  {
    "date": "2024-01-22",
    "category": "工资",
    "income": 5000,
    "expense": 0,
    "image": "uploads/xxx.jpg",
    "note": "备注信息"
  }
  ```

### 3.2 获取记录
- 请求：GET /api.php?action=get_records
- 返回：记录列表

### 3.3 获取统计
- 请求：GET /api.php?action=get_stats
- 返回：总收入、总支出、结余

### 3.4 上传图片
- 请求：POST /api.php?action=upload_image
- 参数：multipart/form-data格式的图片文件

## 4. 待办事项
1. 安装配置Adminer
2. 本地化Bootstrap资源
3. 添加用户认证功能
4. 优化数据库结构
5. 添加数据备份功能
6. 实现更详细的统计报表

## 功能特性
- [x] 基础记账功能
- [x] 收支统计
- [x] 图片上传
- [ ] Bootstrap本地化（计划中）
- [ ] 数据导出功能（计划中）
- [ ] 统计图表（计划中）

## 安装说明
1. 配置PHPStudy环境
2. 导入数据库
3. 配置config.php
4. 设置uploads目录权限

## API文档
详细的API文档请参见 [API文档](docs/api.md)（计划中）

## 开发进度
详细的开发日志请参见 [开发日志](docs/DEV_LOG.md)

### 技术细节
1. CSS分离
   - 将所有样式统一整合到common.css
   - 保持原有的样式定义不变

2. JavaScript分离
   - 将所有脚本统一整合到common.js
   - 删除了视图切换相关代码
   - 优化了页面初始化逻辑

3. 页面拆分
   - 将原单页面拆分为4个独立页面
   - 每个页面只包含必要的代码
   - 保持了统一的导航栏结构
