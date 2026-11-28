-- One-time migration for existing SLP_material_flows tables.
-- Apply after 2026_11_28_create_SLP_material_flows.sql (or Rev 23.14 deployment).
-- A recorded zero requires a defined observation period and evidence in the application.
ALTER TABLE SLP_material_flows
    DROP CHECK chk_SLP_material_flows_positive,
    ADD CONSTRAINT chk_SLP_material_flows_nonnegative CHECK (load_moves >= 0);
