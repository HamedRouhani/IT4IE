-- Adds directed material-flow records for SLP From-To analysis.
-- Safe to run more than once; requires SLP_projects to exist.
CREATE TABLE IF NOT EXISTS SLP_material_flows (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    from_area VARCHAR(180) NOT NULL,
    to_area VARCHAR(180) NOT NULL,
    load_moves DECIMAL(14,4) NOT NULL,
    flow_period ENUM('day','week','month','year') NOT NULL,
    evidence VARCHAR(500) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_SLP_material_flows_directed_pair (project_id, from_area, to_area),
    KEY idx_SLP_material_flows_project_period (project_id, flow_period, id),
    CONSTRAINT fk_SLP_material_flows_project FOREIGN KEY (project_id)
        REFERENCES SLP_projects (id) ON DELETE CASCADE,
    CONSTRAINT chk_SLP_material_flows_distinct CHECK (from_area <> to_area),
    CONSTRAINT chk_SLP_material_flows_positive CHECK (load_moves > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
