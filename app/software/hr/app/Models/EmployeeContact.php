<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * EmployeeContact Model - مدل تماس اضطراری
 * ============================================================
 */
class EmployeeContact extends BaseModel
{
    protected $table = 'employee_contacts';

    protected $fillable = [
        'employee_id',
        'contact_name',
        'relation',
        'mobile',
        'phone',
        'address',
        'is_primary',
    ];

    /**
     * دریافت تماس‌های یک کارمند
     */
    public function getByEmployee(int $employeeId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tableName()} 
             WHERE employee_id = ? AND system_id = ?
             ORDER BY is_primary DESC, id ASC"
        );
        $stmt->execute([$employeeId, $this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * حذف همه تماس‌های یک کارمند
     */
    public function deleteByEmployee(int $employeeId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->tableName()} 
             WHERE employee_id = ? AND system_id = ?"
        );
        return $stmt->execute([$employeeId, $this->systemId]);
    }
}