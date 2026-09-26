<?php
/**
 * ============================================================
 * Quality Analyzer — SamplingPlan Model
 * ============================================================
 * مسیر: app/software/quality/app/Models/SamplingPlan.php
 * جدول: qc_sampling_plans
 * ============================================================
 */

namespace App\Software\Quality\Models;

use App\Core\Model;

class SamplingPlan extends Model
{
    protected $table      = 'qc_sampling_plans';
    protected $primaryKey = 'id';

    /**
     * انواع طرح
     */
    public const PLAN_TYPES = [
        'single'     => 'تک‌نمونه‌ای (Single)',
        'double'     => 'دونمونه‌ای (Double)',
        'multiple'   => 'چندنمونه‌ای (Multiple)',
        'sequential' => 'متوالی (Sequential)',
    ];

    /**
     * دریافت طرح‌های یک سیستم
     */
    public function findBySystem(int $systemId): array
    {
        return $this->query(
            "SELECT sp.*, p.name AS project_name
             FROM {$this->table} sp
             LEFT JOIN qc_projects p ON p.id = sp.project_id
             WHERE sp.system_id = ? AND sp.status = 'active'
             ORDER BY sp.created_at DESC",
            [$systemId]
        );
    }

    /**
     * دریافت طرح‌های یک پروژه
     */
    public function findByProject(int $projectId): array
    {
        return $this->findAll(
            ['project_id' => $projectId, 'status' => 'active'],
            'created_at DESC'
        );
    }

    /**
     * ایجاد طرح جدید
     */
    public function createForSystem(int $systemId, int $userId, array $data): int
    {
        $payload = [
            'system_id'      => $systemId,
            'user_id'        => $userId,
            'project_id'     => $data['project_id']     ?? null,
            'name'           => $data['name']           ?? '',
            'description'    => $data['description']    ?? null,
            'plan_type'      => $data['plan_type']      ?? 'single',
            'aql'            => $data['aql']            ?? null,
            'ltpd'           => $data['ltpd']           ?? null,
            'alpha'          => $data['alpha']          ?? 0.05,
            'beta'           => $data['beta']           ?? 0.10,
            'lot_size'       => $data['lot_size']       ?? null,
            'sample_size'    => $data['sample_size']    ?? null,
            'accept_number'  => $data['accept_number']  ?? null,
            'reject_number'  => $data['reject_number']  ?? null,
            'oc_curve'       => isset($data['oc_curve'])
                                ? json_encode($data['oc_curve'], JSON_UNESCAPED_UNICODE)
                                : null,
            'producer_risk'  => $data['producer_risk']  ?? null,
            'consumer_risk'  => $data['consumer_risk']  ?? null,
            'aoql'           => $data['aoql']           ?? null,
            'at_i'           => $data['at_i']           ?? null,
            'notes'          => $data['notes']          ?? null,
            'status'         => 'active',
        ];
        return (int) $this->create($payload);
    }

    /**
     * بررسی مالکیت
     */
    public function belongsToSystem(int $planId, int $systemId): bool
    {
        $row = $this->queryOne(
            "SELECT id FROM {$this->table} WHERE id = ? AND system_id = ? LIMIT 1",
            [$planId, $systemId]
        );
        return $row !== null;
    }

    /**
     * دیکد کردن منحنی OC
     */
    public function decodeOcCurve(array $plan): array
    {
        if (empty($plan['oc_curve'])) return [];
        $decoded = json_decode($plan['oc_curve'], true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * آرشیو
     */
    public function archive(int $planId): bool
    {
        return $this->update($planId, ['status' => 'archived']);
    }

    /**
     * شمارش
     */
    public function countBySystem(int $systemId): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(*) AS c FROM {$this->table}
             WHERE system_id = ? AND status = 'active'",
            [$systemId]
        );
        return (int) ($row['c'] ?? 0);
    }
}