-- Factory Layout Analyzer: project setup and production calendar, development status.
-- Core project setup and production calendar (T in P-Q-R-S-T).
CREATE TABLE IF NOT EXISTS SLP_projects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    project_code VARCHAR(24) NULL,
    title VARCHAR(180) NOT NULL,
    facility_name VARCHAR(180) NULL,
    industry VARCHAR(120) NULL,
    site_location VARCHAR(180) NULL,
    scope_description TEXT NULL,
    planning_period ENUM('day','week','month','year') NOT NULL DEFAULT 'month',
    shifts_per_day TINYINT UNSIGNED NOT NULL DEFAULT 1,
    hours_per_shift DECIMAL(4,2) NOT NULL DEFAULT 8.00,
    working_days_per_week TINYINT UNSIGNED NOT NULL DEFAULT 5,
    status ENUM('draft','active','completed','archived') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_SLP_projects_user_code (user_id, project_code),
    KEY idx_SLP_projects_user_status (user_id, status, updated_at, id),
    CONSTRAINT chk_SLP_projects_shifts CHECK (shifts_per_day BETWEEN 1 AND 4),
    CONSTRAINT chk_SLP_projects_hours CHECK (hours_per_shift > 0 AND hours_per_shift <= 24),
    CONSTRAINT chk_SLP_projects_workdays CHECK (working_days_per_week BETWEEN 1 AND 7)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO software
    (name, slug, description, version, demo_url, features, tech_stack, db_config,
     module_path, route_prefix, icon_class, requires_license, status, download_count,
     is_active, entry_point, config_json)
VALUES
    ('Factory Layout Analyzer', 'factory-layout-analyzer',
     'مدیریت پروژهٔ چیدمان کارخانه و تقویم تولید، به‌همراه مرکز آموزش طراحی نظام‌مند چیدمان (SLP).',
     '0.2.0', '/software/factory-layout-analyzer/',
     '["ساخت، ویرایش، مشاهده و حذف امن پروژه‌های شخصی","ثبت دورهٔ مبنای تولید و تقویم شیفت‌ها به‌عنوان ورودی‌های T","مرکز آموزش SLP از داده‌های P-Q-R-S-T تا ارزیابی چیدمان بلوکی","درس جریان مواد و شاخص بار-فاصله با کنترل واحدها","آموزش کدهای رابطهٔ فعالیت‌ها به‌همراه ثبت دلیل","مثال عددی، خطاهای رایج و خودآزمایی در هر درس"]',
     '["PHP 8.2", "MySQL 8", "Bootstrap 5", "Vanilla JavaScript"]',
     '{"prefix":"SLP_","tables":["projects"]}',
     'app/software/factory-layout-analyzer', 'factory-layout-analyzer', 'fas fa-industry', 0,
     'development', 0, 1, 'app/software/factory-layout-analyzer/index.php',
     '{"module_path":"app/software/factory-layout-analyzer","db_prefix":"SLP_","sidebar_enabled":true,"theme":"factory-layout-analyzer","color":"#1D4ED8","accent":"#F4B942"}')
ON DUPLICATE KEY UPDATE
    name = VALUES(name), description = VALUES(description), version = VALUES(version),
    demo_url = VALUES(demo_url), features = VALUES(features), tech_stack = VALUES(tech_stack),
    db_config = VALUES(db_config), module_path = VALUES(module_path),
    route_prefix = VALUES(route_prefix), icon_class = VALUES(icon_class),
    status = 'development', is_active = 1, entry_point = VALUES(entry_point),
    config_json = VALUES(config_json);
