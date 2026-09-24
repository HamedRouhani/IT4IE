<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * EmployeeDocument Model - مدل اسناد پرسنلی
 * ============================================================
 */
class EmployeeDocument extends BaseModel
{
    protected $table = 'employee_documents';

    protected $fillable = [
        'employee_id',
        'document_type',
        'title',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'issue_date',
        'expiry_date',
        'description',
        'uploaded_by',
    ];

    /**
     * دریافت اسناد یک کارمند
     */
    public function getByEmployee(int $employeeId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tableName()} 
             WHERE employee_id = ? AND system_id = ?
             ORDER BY created_at DESC"
        );
        $stmt->execute([$employeeId, $this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * اسناد نزدیک به انقضا
     */
    public function getExpiring(int $days = 60): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.*, 
                    CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
                    e.employee_code
             FROM {$this->tableName()} d
             LEFT JOIN {$this->prefix}employees e ON d.employee_id = e.id
             WHERE d.system_id = ?
               AND d.expiry_date IS NOT NULL
               AND d.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
             ORDER BY d.expiry_date ASC"
        );
        $stmt->execute([$this->systemId, $days]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * بررسی استفاده در کارکنان
     */
    public function belongsToEmployee(int $docId, int $employeeId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->tableName()} 
             WHERE id = ? AND employee_id = ? AND system_id = ? LIMIT 1"
        );
        $stmt->execute([$docId, $employeeId, $this->systemId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * برچسب نوع سند
     */
    public static function getDocumentTypes(): array
    {
        return [
            'contract'    => 'قرارداد',
            'id_card'     => 'کارت ملی',
            'passport'    => 'گذرنامه',
            'degree'      => 'مدرک تحصیلی',
            'certificate' => 'گواهی‌نامه',
            'resume'      => 'رزومه',
            'photo'       => 'عکس',
            'insurance'   => 'بیمه',
            'tax'         => 'مالیات',
            'bank'        => 'بانکی',
            'other'       => 'سایر',
        ];
    }

    /**
     * برچسب فارسی نوع سند
     */
    public static function getTypeLabel(string $type): string
    {
        $types = self::getDocumentTypes();
        return $types[$type] ?? $type;
    }
}