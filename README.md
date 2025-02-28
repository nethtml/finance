# 财务管理系统

## 项目说明
一个简单的财务管理系统，帮助用户更好地管理个人或企业财务。

## 目录结构
```
/
├── README.md          # 项目说明文档
├── index.php          # 首页
├── transactions.php   # 流水记录页面
├── admin.php          # 后台管理页面
├── adminmysql.php     # 数据库管理工具
├── api.php            # API接口
├── config.php         # 配置文件
├── docs/              # 文档目录
│   ├── DEV_LOG.md    # 开发日志
│   └── WORK_GUIDE.md # 工作指南
├── templates/         # 模板文件
│   ├── base.html     # 基础模板
│   ├── header.html   # 页头模板
│   └── footer.html   # 页脚模板
├── uploads/           # 上传文件目录
└── assets/           # 静态资源
    ├── css/          # 样式文件
    │   ├── bootstrap.min.css
    │   └── common.css
    ├── js/           # 脚本文件
    │   ├── bootstrap.bundle.min.js
    │   └── common.js
    └── images/       # 图片资源
```

## 技术架构
- 前端：Bootstrap 5 + 原生JavaScript
- 后端：PHP 7.4+
- 数据库：MySQL 5.7+
- 开发环境：PHPStudy

## 功能特性
- [x] 基础记账功能
- [x] 收支统计
- [x] 图片上传
- [x] Bootstrap本地化
- [x] 数据导出功能
- [x] 统计图表

## 开发进度
详细的开发日志请参见 [开发日志](docs/DEV_LOG.md)

## 安装说明
1. 配置PHPStudy环境
2. 导入数据库
3. 配置config.php
4. 设置uploads目录权限

## 开发文档
详细的开发文档请参见：
- [开发日志](docs/DEV_LOG.md)
- [工作指南](docs/WORK_GUIDE.md)
- [API文档](docs/api.md)（计划中）

## 许可证
MIT License
