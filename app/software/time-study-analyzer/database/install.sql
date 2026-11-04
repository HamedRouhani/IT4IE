-- Time Study Analyzer 1.0.0
-- Run against the IT4IE application database before enabling the module.

CREATE TABLE IF NOT EXISTS ts_studies (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    study_no VARCHAR(32) NULL,
    method ENUM('stopwatch','work_sampling') NOT NULL,
    status ENUM('draft','active','completed','archived') NOT NULL DEFAULT 'draft',
    title VARCHAR(180) NOT NULL,
    process_name VARCHAR(180) NOT NULL,
    department VARCHAR(120) NULL,
    product_name VARCHAR(180) NULL,
    work_center VARCHAR(120) NULL,
    operator_ref VARCHAR(120) NULL,
    study_date DATE NOT NULL,
    shift_label VARCHAR(80) NULL,
    unit_name VARCHAR(60) NOT NULL DEFAULT 'قطعه',
    method_description TEXT NOT NULL,
    work_conditions TEXT NULL,
    confidence_level ENUM('90','95','99') NOT NULL DEFAULT '95',
    precision_pct DECIMAL(5,2) NOT NULL DEFAULT 5.00,
    allowance_basis ENUM('standard','normal') NOT NULL DEFAULT 'standard',
    personal_allowance_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    fatigue_allowance_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    delay_allowance_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    other_allowance_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    allowance_rationale VARCHAR(1000) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ts_study_user_no (user_id, study_no),
    UNIQUE KEY uq_ts_study_user_id (user_id, id),
    KEY idx_ts_study_user_status (user_id, status, study_date, id),
    KEY idx_ts_study_user_method (user_id, method, study_date),
    CONSTRAINT chk_ts_precision CHECK (precision_pct > 0 AND precision_pct <= 50),
    CONSTRAINT chk_ts_allowances CHECK (
        personal_allowance_pct >= 0 AND fatigue_allowance_pct >= 0 AND delay_allowance_pct >= 0
        AND other_allowance_pct >= 0
        AND personal_allowance_pct + fatigue_allowance_pct + delay_allowance_pct + other_allowance_pct < 80
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ts_elements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    study_id BIGINT UNSIGNED NOT NULL,
    sequence_no SMALLINT UNSIGNED NOT NULL,
    element_code VARCHAR(32) NULL,
    name VARCHAR(180) NOT NULL,
    element_type ENUM('manual','machine') NOT NULL DEFAULT 'manual',
    frequency_per_cycle DECIMAL(10,4) NOT NULL DEFAULT 1,
    notes VARCHAR(500) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ts_element_sequence (study_id, sequence_no),
    UNIQUE KEY uq_ts_element_study_id (study_id, id),
    CONSTRAINT fk_ts_element_study FOREIGN KEY (study_id) REFERENCES ts_studies (id) ON DELETE CASCADE,
    CONSTRAINT chk_ts_element_frequency CHECK (frequency_per_cycle > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ts_cycles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    study_id BIGINT UNSIGNED NOT NULL,
    cycle_no INT UNSIGNED NOT NULL,
    observed_at DATETIME NOT NULL,
    cycle_status ENUM('valid','excluded') NOT NULL DEFAULT 'valid',
    exclusion_reason VARCHAR(500) NULL,
    observer_name VARCHAR(120) NULL,
    note VARCHAR(1000) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ts_cycle_number (study_id, cycle_no),
    UNIQUE KEY uq_ts_cycle_study_id (study_id, id),
    KEY idx_ts_cycle_study_time (study_id, observed_at),
    CONSTRAINT fk_ts_cycle_study FOREIGN KEY (study_id) REFERENCES ts_studies (id) ON DELETE CASCADE,
    CONSTRAINT chk_ts_cycle_exclusion CHECK (cycle_status <> 'excluded' OR (exclusion_reason IS NOT NULL AND exclusion_reason <> ''))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ts_time_observations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    study_id BIGINT UNSIGNED NOT NULL,
    cycle_id BIGINT UNSIGNED NOT NULL,
    element_id BIGINT UNSIGNED NOT NULL,
    observed_seconds DECIMAL(12,4) NOT NULL,
    performance_rating_pct DECIMAL(6,2) NOT NULL DEFAULT 100,
    is_valid TINYINT(1) NOT NULL DEFAULT 1,
    exclusion_reason VARCHAR(500) NULL,
    note VARCHAR(1000) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ts_observation_cycle_element (cycle_id, element_id),
    KEY idx_ts_observation_study_element (study_id, element_id, is_valid),
    CONSTRAINT fk_ts_observation_cycle FOREIGN KEY (study_id, cycle_id)
        REFERENCES ts_cycles (study_id, id) ON DELETE CASCADE,
    CONSTRAINT fk_ts_observation_element FOREIGN KEY (study_id, element_id)
        REFERENCES ts_elements (study_id, id) ON DELETE CASCADE,
    CONSTRAINT chk_ts_observation_time CHECK (observed_seconds > 0),
    CONSTRAINT chk_ts_observation_rating CHECK (performance_rating_pct >= 50 AND performance_rating_pct <= 150),
    CONSTRAINT chk_ts_observation_exclusion CHECK (is_valid = 1 OR (exclusion_reason IS NOT NULL AND exclusion_reason <> ''))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ts_sampling_categories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    study_id BIGINT UNSIGNED NOT NULL,
    sequence_no SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    category_type ENUM('productive','machine','personal','delay','other') NOT NULL DEFAULT 'productive',
    PRIMARY KEY (id),
    UNIQUE KEY uq_ts_sampling_category_name (study_id, name),
    UNIQUE KEY uq_ts_sampling_category_sequence (study_id, sequence_no),
    UNIQUE KEY uq_ts_sampling_category_study_id (study_id, id),
    CONSTRAINT fk_ts_sampling_category_study FOREIGN KEY (study_id) REFERENCES ts_studies (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ts_sampling_slots (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    study_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NULL,
    scheduled_at DATETIME NOT NULL,
    actual_at DATETIME NULL,
    slot_status ENUM('planned','observed','missed','void') NOT NULL DEFAULT 'planned',
    observer_name VARCHAR(120) NULL,
    note VARCHAR(1000) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ts_sampling_scheduled (study_id, scheduled_at),
    KEY idx_ts_sampling_study_status (study_id, slot_status, scheduled_at),
    KEY idx_ts_sampling_category (study_id, category_id),
    CONSTRAINT fk_ts_sampling_slot_study FOREIGN KEY (study_id) REFERENCES ts_studies (id) ON DELETE CASCADE,
    CONSTRAINT fk_ts_sampling_slot_category FOREIGN KEY (study_id, category_id)
        REFERENCES ts_sampling_categories (study_id, id) ON DELETE RESTRICT,
    CONSTRAINT chk_ts_sampling_slot_state CHECK (
        (slot_status = 'observed' AND category_id IS NOT NULL AND actual_at IS NOT NULL)
        OR (slot_status <> 'observed' AND category_id IS NULL)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO software
    (name, slug, description, version, demo_url, features, tech_stack, db_config,
     module_path, route_prefix, icon_class, requires_license, status, download_count,
     is_active, entry_point, config_json)
VALUES
    ('Time Study Analyzer', 'time-study-analyzer',
     'سامانهٔ مطالعهٔ کار، زمان‌سنجی مستقیم و نمونه‌برداری کار با گزارش آماری قابل ردیابی.',
     '1.1.0', '/software/time-study-analyzer/',
     '["مدیریت مطالعات و گردش کار پیش‌نویس تا نهایی","زمان‌سنجی عناصر کار در چرخه‌های تکراری","ارزیابی عملکرد مجزا برای عناصر دستی","ثبت مشاهدات نامعتبر با علت و حفظ ردپای داده","محاسبهٔ زمان مشاهده‌شده، نرمال و استاندارد با مبنای allowance شفاف","تحلیل پراکندگی، کفایت نمونه و ظرفیت ساعتی","نمونه‌برداری کار با برنامهٔ تصادفی، ثبت عدم مشاهده و فاصلهٔ اطمینان","گزارش چاپی و خروجی CSV","ثبت شرایط روش و یادداشت ارگونومی","مرکز آموزش با هشت درس گام‌به‌گام، واژه‌نامهٔ مطالعهٔ کار و پنج ماشین‌حساب تعاملی"]',
     '["PHP 8.2","MySQL 8","Bootstrap 5","Vanilla JavaScript"]',
     '{"prefix":"ts_","tables":["studies","elements","cycles","time_observations","sampling_categories","sampling_slots"]}',
     'app/software/time-study-analyzer', 'time-study-analyzer', 'fas fa-stopwatch', 0,
     'stable', 0, 1, 'app/software/time-study-analyzer/index.php',
     '{"module_path":"app/software/time-study-analyzer","db_prefix":"ts_","sidebar_enabled":true,"theme":"time-study-analyzer","color":"#4F46E5"}')
ON DUPLICATE KEY UPDATE
    name = VALUES(name), description = VALUES(description), version = VALUES(version),
    demo_url = VALUES(demo_url), features = VALUES(features), tech_stack = VALUES(tech_stack),
    db_config = VALUES(db_config), module_path = VALUES(module_path),
    route_prefix = VALUES(route_prefix), icon_class = VALUES(icon_class),
    status = 'stable', is_active = 1, entry_point = VALUES(entry_point),
    config_json = VALUES(config_json);
