-- Inventory Analyzer beta installation and software catalog registration.
-- Run once against the IT4IE application database before enabling the module.

CREATE TABLE IF NOT EXISTS inv_workspaces (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(120) NOT NULL DEFAULT 'انبار من',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_inv_workspace_user (user_id),
    CONSTRAINT fk_inv_workspace_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inv_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    workspace_id INT UNSIGNED NOT NULL,
    sku VARCHAR(80) NOT NULL,
    name VARCHAR(180) NOT NULL,
    category VARCHAR(120) NULL,
    unit VARCHAR(40) NOT NULL DEFAULT 'عدد',
    unit_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
    on_hand DECIMAL(18,4) NOT NULL DEFAULT 0,
    on_order DECIMAL(18,4) NOT NULL DEFAULT 0,
    annual_demand DECIMAL(18,4) NOT NULL DEFAULT 0,
    daily_demand_std DECIMAL(18,4) NOT NULL DEFAULT 0,
    lead_time_days DECIMAL(10,2) NOT NULL DEFAULT 0,
    order_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
    holding_rate DECIMAL(8,6) NOT NULL DEFAULT 0.250000,
    service_level VARCHAR(8) NOT NULL DEFAULT '95',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_inv_item_workspace_sku (workspace_id, sku),
    UNIQUE KEY uq_inv_item_workspace_id (workspace_id, id),
    KEY idx_inv_item_active_name (workspace_id, is_active, name),
    KEY idx_inv_item_category (workspace_id, category),
    CONSTRAINT fk_inv_item_workspace FOREIGN KEY (workspace_id) REFERENCES inv_workspaces (id) ON DELETE CASCADE,
    CONSTRAINT chk_inv_item_nonnegative CHECK (
        unit_cost >= 0 AND on_hand >= 0 AND on_order >= 0 AND annual_demand >= 0
        AND daily_demand_std >= 0 AND lead_time_days >= 0 AND order_cost >= 0
        AND holding_rate >= 0 AND holding_rate <= 1
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inv_movements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    workspace_id INT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    user_id INT NULL,
    movement_type ENUM('opening','receipt','issue','adjustment') NOT NULL,
    quantity DECIMAL(18,4) NOT NULL,
    on_order_received DECIMAL(18,4) NOT NULL DEFAULT 0,
    balance_after DECIMAL(18,4) NOT NULL,
    note VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_inv_movement_workspace_time (workspace_id, created_at, id),
    KEY idx_inv_movement_item_time (workspace_id, item_id, created_at, id),
    CONSTRAINT fk_inv_movement_item FOREIGN KEY (workspace_id, item_id)
        REFERENCES inv_items (workspace_id, id) ON DELETE RESTRICT,
    CONSTRAINT chk_inv_movement_nonzero CHECK (quantity <> 0),
    CONSTRAINT chk_inv_movement_balance CHECK (balance_after >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Upgrade databases created before receipt/order reconciliation was added.
SET @inv_add_receipt_audit_column = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'inv_movements'
       AND COLUMN_NAME = 'on_order_received') = 0,
    'ALTER TABLE inv_movements ADD COLUMN on_order_received DECIMAL(18,4) NOT NULL DEFAULT 0 AFTER quantity',
    'SELECT 1'
);
PREPARE inv_migration FROM @inv_add_receipt_audit_column;
EXECUTE inv_migration;
DEALLOCATE PREPARE inv_migration;

INSERT INTO software
    (name, slug, description, version, demo_url, features, tech_stack, db_config,
     module_path, route_prefix, icon_class, requires_license, status, download_count,
     is_active, entry_point, config_json)
VALUES
    ('Inventory Analyzer', 'inventory-analyzer',
     'ابزار مدیریت اقلام، ثبت گردش انبار و تحلیل نقطه سفارش و مقدار سفارش اقتصادی برای برنامه‌ریزی موجودی.',
     '0.2.0', '/software/inventory-analyzer/',
     '["مدیریت اقلام و موجودی اولیه","ثبت گردش دریافت، خروج و تعدیل","محاسبه موجودی اطمینان و نقطه سفارش","محاسبه EOQ و پیشنهاد مقدار سفارش","طبقه‌بندی ABC و گزارش‌های موجودی"]',
     '["PHP 8.2","MySQL 8","Bootstrap 5","Vanilla JavaScript"]',
     '{"prefix":"inv_","tables":["workspaces","items","movements"]}',
     'app/software/inventory', 'inventory-analyzer', 'fas fa-boxes-stacked', 0,
     'beta', 0, 1, 'app/software/inventory/index.php',
     '{"module_path":"app/software/inventory","db_prefix":"inv_","sidebar_enabled":true,"theme":"inventory","color":"#BE123C"}')
ON DUPLICATE KEY UPDATE
    name = VALUES(name), description = VALUES(description), version = VALUES(version),
    demo_url = VALUES(demo_url), features = VALUES(features), tech_stack = VALUES(tech_stack),
    db_config = VALUES(db_config), module_path = VALUES(module_path),
    route_prefix = VALUES(route_prefix), icon_class = VALUES(icon_class),
    status = 'beta', is_active = 1, entry_point = VALUES(entry_point),
    config_json = VALUES(config_json);
