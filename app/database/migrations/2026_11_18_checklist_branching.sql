-- Smart checklist branching rules (MySQL 8+)
-- Run once against the IT4IE database before deploying the matching PHP changes.
SET @has_branching_rules = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'checklist_questions'
      AND COLUMN_NAME = 'branching_rules'
);
SET @branching_sql = IF(
    @has_branching_rules = 0,
    'ALTER TABLE checklist_questions ADD COLUMN branching_rules JSON NULL AFTER sort_order',
    'SELECT 1'
);
PREPARE branching_stmt FROM @branching_sql;
EXECUTE branching_stmt;
DEALLOCATE PREPARE branching_stmt;
