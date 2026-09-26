<?php
/**
 * ============================================================
 * Quality Analyzer — System Model
 * ============================================================
 * مسیر: app/software/quality/app/Models/System.php
 * جدول: qc_systems
 * ============================================================
 */

namespace App\Software\Quality\Models;

use App\Core\Model;

class System extends Model
{
    protected $table      = 'qc_systems';
    protected $primaryKey = 'id';

    /**
     * دریافت سیستم‌های یک کاربر
     */
    public function findByUser(int $userId): array
    {
        return $this->findAll(
            ['user_id' => $userId, 'status' => 'active'],
            'created_at DESC'
        );
    }

    /**
     * دریافت سیستم فعال کاربر (اولین)
     */
    public function findActiveByUser(int $userId): ?array
    {
        $rows = $this->findAll(
            ['user_id' => $userId, 'status' => 'active'],
            'id ASC',
            '1'
        );
        return $rows[0] ?? null;
    }

    /**
     * ایجاد سیستم جدید برای کاربر
     */
    public function createForUser(int $userId, array $data): int
    {
        $payload = [
            'user_id'      => $userId,
            'company_name' => $data['company_name'] ?? '',
            'industry'     => $data['industry']     ?? null,
            'company_size' => $data['company_size'] ?? 'medium',
            'description'  => $data['description']  ?? null,
            'status'       => 'active',
        ];
        return (int) $this->create($payload);
    }

    /**
     * بررسی مالکیت سیستم
     */
    public function belongsToUser(int $systemId, int $userId): bool
    {
        $row = $this->queryOne(
            "SELECT id FROM {$this->table} WHERE id = ? AND user_id = ? LIMIT 1",
            [$systemId, $userId]
        );
        return $row !== null;
    }

    /**
     * آمار خلاصه برای داشبورد
     */
    public function getStats(int $systemId): array
    {
        $projects = $this->queryOne(
            "SELECT COUNT(*) AS c FROM qc_projects WHERE system_id = ?",
            [$systemId]
        );
        $datasets = $this->queryOne(
            "SELECT COUNT(*) AS c FROM qc_datasets WHERE system_id = ?",
            [$systemId]
        );
        $charts = $this->queryOne(
            "SELECT COUNT(*) AS c FROM qc_control_charts WHERE system_id = ?",
            [$systemId]
        );
        $capability = $this->queryOne(
            "SELECT COUNT(*) AS c FROM qc_capability_studies WHERE system_id = ?",
            [$systemId]
        );
        $msa = $this->queryOne(
            "SELECT COUNT(*) AS c FROM qc_msa_studies WHERE system_id = ?",
            [$systemId]
        );

        return [
            'projects'   => (int) ($projects['c']   ?? 0),
            'datasets'   => (int) ($datasets['c']   ?? 0),
            'charts'     => (int) ($charts['c']     ?? 0),
            'capability' => (int) ($capability['c'] ?? 0),
            'msa'        => (int) ($msa['c']        ?? 0),
        ];
    }

    /**
     * آرشیو کردن سیستم
     */
    public function archive(int $systemId): bool
    {
        return $this->update($systemId, ['status' => 'archived']);
    }

    /**
     * لیست شرکت‌ها (برای dropdown)
     */
    public function listForUser(int $userId): array
    {
        return $this->query(
            "SELECT id, company_name, industry 
             FROM {$this->table} 
             WHERE user_id = ? AND status = 'active'
             ORDER BY company_name ASC",
            [$userId]
        );
    }
}