<?php

namespace App\Software\Qms\Models;

use App\Software\Qms\Core\Model;

/**
 * مدل ممیزان و تیم ممیزی (منطبق بر ISO 19011)
 */
class AuditorModel extends Model
{
    protected $table = 'auditors';

    /** ممیزان فعال */
    public function activeAuditors()
    {
        return $this->query("
            SELECT id, full_name, lead_auditor 
            FROM {$this->prefix}auditors 
            WHERE is_active = 1 
            ORDER BY full_name
        ");
    }

    /** اعضای تیم یک برنامه ممیزی */
    public function getTeam($planId)
    {
        return $this->query("
            SELECT t.*, a.full_name
            FROM {$this->prefix}audit_team t
            JOIN {$this->prefix}auditors a ON a.id = t.auditor_id
            WHERE t.audit_plan_id = ?
            ORDER BY t.role, a.full_name
        ", [$planId]);
    }

    /** آیا تیم سرممیز دارد؟ */
    public function hasLeadAuditor($planId)
    {
        $row = $this->queryOne("
            SELECT COUNT(*) as c FROM {$this->prefix}audit_team 
            WHERE audit_plan_id = ? AND role = 'lead_auditor'
        ", [$planId]);
        return ($row['c'] ?? 0) > 0;
    }

    /** افزودن عضو تیم */
    public function addTeamMember($planId, $auditorId, $role, $clauses)
    {
        return $this->query("
            INSERT INTO {$this->prefix}audit_team 
            (audit_plan_id, auditor_id, role, assigned_clauses) 
            VALUES (?, ?, ?, ?)
        ", [$planId, $auditorId, $role, $clauses]);
    }

    /** دریافت یک عضو تیم */
    public function findTeamMember($id)
    {
        return $this->queryOne("SELECT * FROM {$this->prefix}audit_team WHERE id = ?", [$id]);
    }

    /** حذف عضو تیم */
    public function removeTeamMember($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->prefix}audit_team WHERE id = ?");
        return $stmt->execute([$id]);
    }
}