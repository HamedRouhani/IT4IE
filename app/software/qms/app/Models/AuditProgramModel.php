<?php

namespace App\Software\Qms\Models;

use App\Software\Qms\Core\Model;

class AuditProgramModel extends Model
{
    protected $table = 'audit_programs';

    /** لیست برنامه‌ها با آمار */
    public function list($year = null, $userId = null)
    {
        $sql = "SELECT p.*,
                       COUNT(DISTINCT ap.id) as plans_count,
                       COUNT(DISTINCT CASE WHEN ap.status = 'completed' THEN ap.id END) as completed_plans,
                       COUNT(DISTINCT ra.department_id) as assessed_departments,
                       COUNT(DISTINCT CASE WHEN ra.risk_level = 'critical' THEN ra.department_id END) as critical_risks,
                       COUNT(DISTINCT CASE WHEN ra.risk_level = 'high' THEN ra.department_id END) as high_risks
                FROM {$this->prefix}audit_programs p
                LEFT JOIN {$this->prefix}audit_plans ap ON ap.audit_program_id = p.id
                LEFT JOIN {$this->prefix}process_risk_assessment ra ON ra.audit_program_id = p.id
                WHERE 1=1";
        $params = [];
        if ($year)   { $sql .= " AND p.year = ?";    $params[] = $year; }
        if ($userId) { $sql .= " AND p.user_id = ?"; $params[] = $userId; }
        $sql .= " GROUP BY p.id ORDER BY p.year DESC, p.id DESC";
        return $this->query($sql, $params);
    }

    /** دریافت یک برنامه */
    public function find($id)
    {
        return $this->queryOne(
            "SELECT * FROM {$this->prefix}audit_programs WHERE id = ?", [$id]
        );
    }

    /** برنامه‌های ممیزیِ متصل به این برنامه سالانه */
    public function getRelatedAuditPlans($programId)
    {
        return $this->query("
            SELECT ap.*, a.full_name as lead_auditor_name
            FROM {$this->prefix}audit_plans ap
            LEFT JOIN {$this->prefix}auditors a ON a.id = ap.lead_auditor_id
            WHERE ap.audit_program_id = ?
            ORDER BY ap.start_date ASC", [$programId]);
    }

    /** آمار کلی برنامه */
    public function getStatistics($programId)
    {
        $stats = $this->queryOne("
            SELECT
              COUNT(DISTINCT department_id) as total_assessed,
              COUNT(DISTINCT CASE WHEN risk_level = 'critical' THEN department_id END) as critical_count,
              COUNT(DISTINCT CASE WHEN risk_level = 'high'     THEN department_id END) as high_count,
              COUNT(DISTINCT CASE WHEN risk_level = 'medium'   THEN department_id END) as medium_count,
              COUNT(DISTINCT CASE WHEN risk_level = 'low'      THEN department_id END) as low_count
            FROM {$this->prefix}process_risk_assessment
            WHERE audit_program_id = ?", [$programId]);

        $plans = $this->queryOne(
            "SELECT COUNT(*) as c FROM {$this->prefix}audit_plans WHERE audit_program_id = ?",
            [$programId]
        );
        $stats['total_plans'] = $plans['c'] ?? 0;
        return $stats;
    }
}