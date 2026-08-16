<?php

namespace App\Software\Qms\Models;

use App\Software\Qms\Core\Model;

class ProcessRiskModel extends Model
{
    protected $table = 'process_risk_assessment';

    /** ارزیابی‌های یک برنامه به همراه نام واحد */
    public function getByProgram($programId)
    {
        return $this->query("
            SELECT ra.*, d.name_fa as department_name, d.code as department_code
            FROM {$this->prefix}process_risk_assessment ra
            LEFT JOIN {$this->prefix}departments d ON d.id = ra.department_id
            WHERE ra.audit_program_id = ?
            ORDER BY ra.risk_score DESC, d.sort_order ASC", [$programId]);
    }

    /** یافتن رکورد بر اساس برنامه + واحد */
    public function findByProgramAndDept($programId, $departmentId)
    {
        return $this->queryOne("
            SELECT * FROM {$this->prefix}process_risk_assessment
            WHERE audit_program_id = ? AND department_id = ?",
            [$programId, $departmentId]);
    }

    /** واحدهایی که هنوز ارزیابی نشده‌اند */
    public function getUnassessedDepartments($programId)
    {
        return $this->query("
            SELECT d.id, d.name_fa, d.code
            FROM {$this->prefix}departments d
            WHERE d.is_active = 1
              AND d.id NOT IN (
                  SELECT department_id FROM {$this->prefix}process_risk_assessment
                  WHERE audit_program_id = ?
              )
            ORDER BY d.sort_order ASC", [$programId]);
    }

    /** ایجاد یا به‌روزرسانی ارزیابی */
    public function saveAssessment($programId, $departmentId, $data)
    {
        $existing = $this->findByProgramAndDept($programId, $departmentId);
        if ($existing) {
            $this->update($existing['id'], $data);
            return $existing['id'];
        }
        $data['audit_program_id'] = $programId;
        $data['department_id']    = $departmentId;
        return $this->create($data);
    }

    /** حذف همه ارزیابی‌های یک برنامه */
    public function deleteByProgram($programId)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->prefix}process_risk_assessment WHERE audit_program_id = ?"
        );
        return $stmt->execute([$programId]);
    }
}