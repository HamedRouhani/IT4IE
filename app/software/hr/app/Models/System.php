<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * System Model - مدل شرکت HR
 * ============================================================
 * مسیر: app/software/hr/app/Models/System.php
 * 
 * نکته: رابطه کاربر با System یک به یک است (One-to-One).
 * هر کاربر سایت فقط یک شرکت HR دارد.
 * ============================================================
 */
class System extends BaseModel
{
    protected $table = 'systems';

    protected $fillable = [
        'owner_user_id',
        'company_name',
        'industry',
        'company_size',
        'employee_count',
        'fiscal_year_start',
        'address',
        'phone',
        'email',
        'website',
        'logo_path',
        'description',
        'status',
    ];

    /**
     * دریافت سیستم متعلق به یک کاربر (One-to-One)
     */
    public function findByUser(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->prefix}systems 
             WHERE owner_user_id = ? 
             LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * دریافت سیستم بر اساس ID (بدون فیلتر system_id)
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->prefix}systems WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * ایجاد سیستم جدید برای کاربر
     */
    public function createForUser(int $userId, array $data): int
    {
        $data['owner_user_id'] = $userId;

        $fields = array_intersect_key($data, array_flip($this->fillable));
        $fields['owner_user_id'] = $userId;

        $columns = implode(', ', array_keys($fields));
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));

        $sql = "INSERT INTO {$this->prefix}systems ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($fields));

        return (int) $this->db->lastInsertId();
    }

    /**
     * به‌روزرسانی سیستم متعلق به کاربر
     */
    public function updateForUser(int $userId, array $data): bool
    {
        $fields = array_intersect_key($data, array_flip($this->fillable));

        if (empty($fields)) {
            return false;
        }

        $set = implode(' = ?, ', array_keys($fields)) . ' = ?';
        $values = array_values($fields);
        $values[] = $userId;

        $sql = "UPDATE {$this->prefix}systems SET {$set} WHERE owner_user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * به‌روزرسانی تعداد کارکنان (cache)
     */
    public function updateEmployeeCount(int $systemId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->prefix}systems 
             SET employee_count = (
                 SELECT COUNT(*) FROM {$this->prefix}employees 
                 WHERE system_id = ? AND employment_status = 'active'
             )
             WHERE id = ?"
        );
        $stmt->execute([$systemId, $systemId]);
    }
}