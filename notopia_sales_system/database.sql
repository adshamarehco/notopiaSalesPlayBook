-- ============================================================
-- سیستم فروش نوتوپیا
-- دیتابیس MySQL - نسخه ۱
-- منطبق با کتابچه فروش (فصل‌های ۵، ۹، ۱۳)
-- ============================================================

CREATE DATABASE IF NOT EXISTS notopia_sales
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE notopia_sales;

-- ----------------------------------------------------------
-- جداول کمکی
-- ----------------------------------------------------------

CREATE TABLE industries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pipeline_stages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  sort_order TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE opportunity_statuses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  sort_order TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE loss_reasons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE decision_roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE customer_values (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- جدول اصلی فرصت‌ها
-- ----------------------------------------------------------

CREATE TABLE opportunities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  organization VARCHAR(255) NOT NULL,
  industry VARCHAR(100) DEFAULT NULL,
  contact_name VARCHAR(150) DEFAULT NULL,
  contact_title VARCHAR(150) DEFAULT NULL,
  decision_role VARCHAR(50) DEFAULT NULL,
  purchase_purpose VARCHAR(255) DEFAULT NULL,
  estimated_quantity VARCHAR(50) DEFAULT NULL,
  estimated_budget VARCHAR(50) DEFAULT NULL,
  estimated_buy_time VARCHAR(100) DEFAULT NULL,
  customer_value VARCHAR(255) DEFAULT NULL,
  main_objection TEXT DEFAULT NULL,
  pipeline_stage VARCHAR(50) DEFAULT 'سرنخ اولیه',
  status VARCHAR(50) DEFAULT 'فعال',
  last_interaction TEXT DEFAULT NULL,
  last_interaction_date DATE DEFAULT NULL,
  next_step TEXT DEFAULT NULL,
  next_step_date DATE DEFAULT NULL,
  responsible VARCHAR(150) DEFAULT NULL,
  loss_reason VARCHAR(100) DEFAULT NULL,
  final_result TEXT DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status),
  INDEX idx_stage (pipeline_stage),
  INDEX idx_next_step_date (next_step_date),
  INDEX idx_organization (organization)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- جدول فعالیت‌ها
-- ----------------------------------------------------------

CREATE TABLE activities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  opportunity_id INT DEFAULT NULL,
  organization_name VARCHAR(255) DEFAULT NULL,
  activity_date DATE NOT NULL,
  activity_type VARCHAR(50) NOT NULL,
  summary TEXT DEFAULT NULL,
  result TEXT DEFAULT NULL,
  next_step_defined TINYINT(1) NOT NULL DEFAULT 0,
  registered_by VARCHAR(150) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_activity_opportunity
    FOREIGN KEY (opportunity_id) REFERENCES opportunities(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX idx_activity_date (activity_date),
  INDEX idx_activity_type (activity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- جدول گزارش هفتگی
-- ----------------------------------------------------------

CREATE TABLE weekly_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  week_start_date DATE NOT NULL,
  new_opportunities INT NOT NULL DEFAULT 0,
  meetings_demos INT NOT NULL DEFAULT 0,
  proposals_sent INT NOT NULL DEFAULT 0,
  progressed_notes TEXT DEFAULT NULL,
  stopped_notes TEXT DEFAULT NULL,
  lesson_learned TEXT DEFAULT NULL,
  created_by VARCHAR(150) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_week (week_start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- داده‌های اولیه
-- ----------------------------------------------------------

INSERT INTO industries (name) VALUES
('نفت و گاز'),
('معدن'),
('خودرو و قطعه‌سازی'),
('فناوری'),
('صنعتی عمومی'),
('خدمات مالی'),
('سایر');

INSERT INTO pipeline_stages (name, sort_order) VALUES
('سرنخ اولیه', 1),
('احراز صلاحیت سریع', 2),
('کشف نیاز', 3),
('نمایش محصول', 4),
('پیشنهاد تجاری', 5),
('مذاکره و بستن', 6);

INSERT INTO opportunity_statuses (name, sort_order) VALUES
('فعال', 1),
('در حال پرورش', 2),
('متوقف موقت', 3),
('بسته‌شده-موفق', 4),
('بسته‌شده-ناموفق', 5);

INSERT INTO loss_reasons (name) VALUES
('قیمت'),
('زمان'),
('بودجه'),
('تصمیم‌گیرنده'),
('رقابت'),
('محصول'),
('مخاطب نامناسب'),
('عدم نیاز'),
('سایر');

INSERT INTO decision_roles (name) VALUES
('تصمیم‌گیرنده'),
('تأثیرگذار'),
('کاربر'),
('معرف');

INSERT INTO activity_types (name) VALUES
('جلسه'),
('نمایش محصول'),
('تماس'),
('پیشنهاد'),
('سایر');

INSERT INTO customer_values (name) VALUES
('تجربه متفاوت و تصویر برند'),
('فناوری و قابلیت هوشمند'),
('کاربرد واقعی و حفظ اطلاعات'),
('تمایز و ماندگاری هدیه'),
('سایر');
