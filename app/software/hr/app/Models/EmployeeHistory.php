<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * EmployeeHistory Model - مدل تاریخچه تغییرات کارمند
 * ============================================================
 */
class EmployeeHistory extends BaseModel
{
    protected $table = 'employee_history';

    protected $fillable = [
        'employee_id',
        'change_type',
        'change_date',
        'from_value',
        'to_value',
        'reason',
        'document_ref',
        'created_by',
    ];

    /**
     * دریافت تاریخچه یک کارمند
     */
    public function getByEmployee(int $employeeId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tableName()} 
             WHERE employee_id = ? AND system_id = ?
             ORDER BY change_date DESC, id DESC"
        );
        $stmt->execute([$employeeId, $this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * ثبت تغییر
     */
    public function record(int $employeeId, string $type, string $changeDate, ?string $from = null, ?string $to = null, ?string $reason = null, ?string $docRef = null): int
    {
        return $this->create([
            'employee_id'  => $employeeId,
            'change_type'  => $type,
            'change_date'  => $changeDate,
            'from_value'   => $from,
            'to_value'     => $to,
            'reason'       => $reason,
            'document_ref' => $docRef,
            'created_by'   => $_SESSION['user_id'] ?? null,
        ]);
    }

    /**
     * برچسب نوع تغییر
     */
    public static function getTypeLabel(string $type): string
    {
        $labels = [
            'hire'              => 'استخدام',
            'promotion'         => 'ارتقاء',
            'transfer'          => 'انتقال',
            'salary_change'     => 'تغییر حقوق',
            'position_change'   => 'تغییر پست',
            'department_change' => 'تغییر دپارتمان',
            'contract_renewal'  => 'تمدید قرارداد',
            'status_change'     => 'تغییر وضعیت',
            'termination'       => 'خاتمه همکاری',
            'retirement'        => 'بازنشستگی',
            'other'             => 'سایر',
        ];
        return $labels[$type] ?? $type;
    }
}