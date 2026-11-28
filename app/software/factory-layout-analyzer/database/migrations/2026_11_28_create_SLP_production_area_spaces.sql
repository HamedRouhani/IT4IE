-- Adds preliminary net-area records for production areas named in SLP routes.
-- Safe to run more than once; requires SLP_projects to exist.
CREATE TABLE IF NOT EXISTS SLP_production_area_spaces (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    area_name VARCHAR(180) NOT NULL,
    area_m2 DECIMAL(12,3) NOT NULL,
    area_basis VARCHAR(500) NOT NULL,
    notes VARCHAR(1000) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_SLP_production_area_spaces_project_area (project_id, area_name),
    KEY idx_SLP_production_area_spaces_project (project_id, id),
    CONSTRAINT fk_SLP_production_area_spaces_project FOREIGN KEY (project_id)
        REFERENCES SLP_projects (id) ON DELETE CASCADE,
    CONSTRAINT chk_SLP_production_area_spaces_area CHECK (area_m2 > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
