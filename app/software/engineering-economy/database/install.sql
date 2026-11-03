-- Engineering Economy Analyzer 1.0.0
-- این اسکریپت را روی پایگاه‌دادهٔ برنامهٔ IT4IE اجرا کنید (یک‌بار).
-- پیشوند جداول: ee_ | همهٔ محاسبات از ee_cashflows تغذیه می‌شود.

CREATE TABLE IF NOT EXISTS ee_projects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    project_no VARCHAR(32) NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    study_kind ENUM('investment','replacement','make_buy','public','cost_only') NOT NULL DEFAULT 'investment',
    currency CHAR(3) NOT NULL DEFAULT 'IRT',
    base_year SMALLINT UNSIGNED NULL,
    study_period SMALLINT UNSIGNED NOT NULL DEFAULT 5,
    marr_pct DECIMAL(6,3) NOT NULL DEFAULT 12.000,
    inflation_pct DECIMAL(6,3) NOT NULL DEFAULT 0.000,
    tax_rate_pct DECIMAL(6,3) NOT NULL DEFAULT 0.000,
    depreciation_method ENUM('none','straight','declining','soyd','uop') NOT NULL DEFAULT 'none',
    status ENUM('draft','active','completed','archived') NOT NULL DEFAULT 'draft',
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ee_project_user_no (user_id, project_no),
    UNIQUE KEY uq_ee_project_user_id (user_id, id),
    KEY idx_ee_project_user_status (user_id, status, updated_at),
    CONSTRAINT chk_ee_project_period CHECK (study_period BETWEEN 1 AND 100),
    CONSTRAINT chk_ee_project_marr CHECK (marr_pct > -100 AND marr_pct <= 100),
    CONSTRAINT chk_ee_project_inflation CHECK (inflation_pct > -100 AND inflation_pct <= 100),
    CONSTRAINT chk_ee_project_tax CHECK (tax_rate_pct >= 0 AND tax_rate_pct <= 60)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ee_alternatives (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(160) NOT NULL,
    description TEXT NULL,
    life_years SMALLINT UNSIGNED NOT NULL DEFAULT 5,
    is_baseline TINYINT(1) NOT NULL DEFAULT 0,
    sequence_no SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ee_alt_project_id (project_id, id),
    UNIQUE KEY uq_ee_alt_sequence (project_id, sequence_no),
    KEY idx_ee_alt_project (project_id, sequence_no),
    CONSTRAINT fk_ee_alt_project FOREIGN KEY (project_id) REFERENCES ee_projects (id) ON DELETE CASCADE,
    CONSTRAINT chk_ee_alt_life CHECK (life_years BETWEEN 1 AND 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- مبالغ ورودی و خروجی جدا نگه داشته می‌شوند تا نسبت فایده/هزینه و تحلیل افزایشی ممکن باشد.
-- سال ۰ مجاز است و همان‌جا سرمایه‌گذاری اولیه ثبت می‌شود؛ هیچ مبلغی در جای دیگری تکرار نمی‌شود.
CREATE TABLE IF NOT EXISTS ee_cashflows (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    alternative_id BIGINT UNSIGNED NOT NULL,
    year_no SMALLINT UNSIGNED NOT NULL,
    inflow DECIMAL(18,4) NOT NULL DEFAULT 0,
    outflow DECIMAL(18,4) NOT NULL DEFAULT 0,
    label VARCHAR(120) NULL,
    note VARCHAR(500) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ee_cashflow_alt_year (alternative_id, year_no),
    KEY idx_ee_cashflow_project (project_id, alternative_id, year_no),
    CONSTRAINT fk_ee_cashflow_project FOREIGN KEY (project_id) REFERENCES ee_projects (id) ON DELETE CASCADE,
    CONSTRAINT fk_ee_cashflow_alt FOREIGN KEY (alternative_id) REFERENCES ee_alternatives (id) ON DELETE CASCADE,
    CONSTRAINT chk_ee_cashflow_year CHECK (year_no <= 100),
    CONSTRAINT chk_ee_cashflow_amounts CHECK (inflow >= 0 AND outflow >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- فرض‌های مستند (الزام استاندارد: هر عدد کلیدی باید منبع و توجیه داشته باشد).
CREATE TABLE IF NOT EXISTS ee_assumptions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    value VARCHAR(120) NULL,
    unit VARCHAR(40) NULL,
    source VARCHAR(500) NULL,
    note VARCHAR(1000) NULL,
    sequence_no SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_ee_assumption_project (project_id, sequence_no),
    CONSTRAINT fk_ee_assumption_project FOREIGN KEY (project_id) REFERENCES ee_projects (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ee_scenarios (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    marr_pct DECIMAL(6,3) NOT NULL,
    inflation_pct DECIMAL(6,3) NOT NULL DEFAULT 0,
    tax_rate_pct DECIMAL(6,3) NOT NULL DEFAULT 0,
    note VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ee_scenario_name (project_id, name),
    CONSTRAINT fk_ee_scenario_project FOREIGN KEY (project_id) REFERENCES ee_projects (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- عکس فوری نتیجهٔ تحلیل‌ها تا گزارش‌های قبلی پس از تغییر فرض‌ها تغییر نکنند.
CREATE TABLE IF NOT EXISTS ee_analyses (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    alternative_id BIGINT UNSIGNED NULL,
    scenario_id BIGINT UNSIGNED NULL,
    method VARCHAR(40) NOT NULL,
    payload_json LONGTEXT NOT NULL,
    note VARCHAR(500) NULL,
    computed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ee_analysis_project (project_id, computed_at),
    CONSTRAINT fk_ee_analysis_project FOREIGN KEY (project_id) REFERENCES ee_projects (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO software
    (name, slug, description, version, demo_url, features, tech_stack, db_config,
     module_path, route_prefix, icon_class, requires_license, status, download_count,
     is_active, entry_point, config_json)
VALUES
    ('Engineering Economy Analyzer', 'engineering-economy',
     'تحلیل اقتصادی پروژه‌ها و دارایی‌ها: ارزش فعلی خالص، نرخ بازده داخلی و خارجی، دورهٔ بازگشت، نسبت فایده به هزینه، مالیات و استهلاک، تورم و تحلیل حساسیت.',
     '1.0.0', '/software/engineering-economy/',
     '["جریان نقدی چندساله با ورودی و خروجی جدا","NPV، FW، AW و هزینهٔ یکنواخت سالانه","IRR با هشدار چندریشه‌ای و ERR/MIRR با نرخ سرمایه‌گذاری مجدد","دورهٔ بازگشت ساده و تنزیل‌شده و شاخص سودآوری","نسبت فایده به هزینه و تحلیل افزایشی بین گزینه‌ها","مالیات و استهلاک (مستقیم، نزولی، مجموع سنوات، واحد تولید) و تحلیل پس از مالیات","تورم و تبدیل نرخ اسمی و حقیقی","تحلیل حساسیت یک‌متغیره و ارزش تغییر","جایگزینی و عمر اقتصادی دارایی","مستندسازی فرض‌ها و ثبت نسخهٔ تحلیل","گزارش چاپی و خروجی CSV"]',
     '["PHP 8.2","MySQL 8","Bootstrap 5","Vanilla JavaScript"]',
     '{"prefix":"ee_","tables":["projects","alternatives","cashflows","assumptions","scenarios","analyses"]}',
     'app/software/engineering-economy', 'engineering-economy', 'fas fa-coins', 0,
     'stable', 0, 1, 'app/software/engineering-economy/index.php',
     '{"module_path":"app/software/engineering-economy","db_prefix":"ee_","sidebar_enabled":true,"theme":"engineering-economy","color":"#0F3D3E"}')
ON DUPLICATE KEY UPDATE
    name = VALUES(name), description = VALUES(description), version = VALUES(version),
    demo_url = VALUES(demo_url), features = VALUES(features), tech_stack = VALUES(tech_stack),
    db_config = VALUES(db_config), module_path = VALUES(module_path),
    route_prefix = VALUES(route_prefix), icon_class = VALUES(icon_class),
    status = 'stable', is_active = 1, entry_point = VALUES(entry_point),
    config_json = VALUES(config_json);
