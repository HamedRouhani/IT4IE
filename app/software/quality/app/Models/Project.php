<?php
/**
 * ============================================================
 * Quality Analyzer — Project Model
 * ============================================================
 * مسیر: app/software/quality/app/Models/Project.php
 * جدول: qc_projects
 * ============================================================
 */

namespace App\Software\Quality\Models;

use App\Core\Model;

class Project extends Model
{
    protected $table      = 'qc_projects';
    protected $primaryKey = 'id';

    /**
     * دریافت پروژه‌های یک سیستم
     */
    public function findBySystem(int $systemId): array
    {
        return $this->findAll(
            ['system_id' => $systemId, 'status' => 'active'],
            'created_at DESC'
        );
    }

    /**
     * دریافت پروژه‌ها با آمار دیتاست
     */
    public function findBySystemWithStats(int $systemId): array
    {
        return $this->query(
            "SELECT p.*, 
                    (SELECT COUNT(*) FROM qc_datasets d WHERE d.project_id = p.id) AS dataset_count
             FROM {$this->table} p
             WHERE p.system_id = ? AND p.status = 'active'
             ORDER BY p.created_at DESC",
            [$systemId]
        );
    }

    /**
     * ایجاد پروژه جدید
     */
    public function createForSystem(int $systemId, int $userId, array $data): int
    {
        $payload = [
            'system_id'    => $systemId,
            'user_id'      => $userId,
            'name'         => $data['name']         ?? '',
            'description'  => $data['description']  ?? null,
            'product_name' => $data['product_name'] ?? null,
            'process_name' => $data['process_name'] ?? null,
            'ctq'          => $data['ctq']          ?? null,
            'unit'         => $data['unit']         ?? null,
            'status'       => 'active',
        ];
        return (int) $this->create($payload);
    }

    /**
     * بررسی مالکیت پروژه
     */
    public function belongsToSystem(int $projectId, int $systemId): bool
    {
        $row = $this->queryOne(
            "SELECT id FROM {$this->table} WHERE id = ? AND system_id = ? LIMIT 1",
            [$projectId, $systemId]
        );
        return $row !== null;
    }

    /**
     * شمارش دیتاست‌های پروژه
     */
    public function getDatasetCount(int $projectId): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(*) AS c FROM qc_datasets WHERE project_id = ?",
            [$projectId]
        );
        return (int) ($row['c'] ?? 0);
    }

    /**
     * دریافت پروژه با اطلاعات سیستم
     */
    public function findWithSystem(int $projectId): ?array
    {
        return $this->queryOne(
            "SELECT p.*, s.company_name, s.industry
             FROM {$this->table} p
             JOIN qc_systems s ON s.id = p.system_id
             WHERE p.id = ? LIMIT 1",
            [$projectId]
        );
    }

    /**
     * آرشیو کردن پروژه
     */
    public function archive(int $projectId): bool
    {
        return $this->update($projectId, ['status' => 'archived']);
    }

    /**
     * لیست پروژه‌ها (برای dropdown)
     */
    public function listForSystem(int $systemId): array
    {
        return $this->query(
            "SELECT id, name, product_name, ctq, unit
             FROM {$this->table}
             WHERE system_id = ? AND status = 'active'
             ORDER BY name ASC",
            [$systemId]
        );
    }
}