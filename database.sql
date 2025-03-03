-- 创建数据库
CREATE DATABASE IF NOT EXISTS finance_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE finance_db;

-- 创建收支分类表
CREATE TABLE IF NOT EXISTS records_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    type ENUM('收入', '支出') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建收支记录表
CREATE TABLE IF NOT EXISTS records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    attachment VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES records_categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入默认分类数据
INSERT INTO records_categories (name, type, description) VALUES
('工资', '收入', '固定工资收入'),
('奖金', '收入', '奖金、提成等'),
('投资', '收入', '投资收益'),
('其他收入', '收入', '其他类型收入'),
('餐饮', '支出', '日常饮食支出'),
('交通', '支出', '交通费用'),
('购物', '支出', '日常购物'),
('娱乐', '支出', '娱乐消费'),
('医疗', '支出', '医疗支出'),
('其他支出', '支出', '其他类型支出');

-- 插入测试数据
INSERT INTO records (date, category_id, amount, description) VALUES
('2024-03-01', 1, 5000.00, '3月份工资'),
('2024-03-05', 5, 150.00, '午餐费用'),
('2024-03-10', 3, 1000.00, '基金收益'),
('2024-03-15', 7, 500.00, '日用品购物'),
('2024-03-20', 2, 2000.00, '项目奖金'); 