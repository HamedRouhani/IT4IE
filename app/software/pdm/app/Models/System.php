<?php
namespace App\Software\Pdm\Models;

/**
 * ============================================================
 * System Model - مدل شرکت (سیستم نت)
 * ============================================================
 * مسیر: app/software/pdm/app/Models/System.php
 * 
 * نکته: رابطه کاربر با System یک به یک است (One-to-One).
 * هر کاربر سایت فقط یک شرکت دارد که می‌تواند ایجاد و ویرایش کند.
 * ============================================================
 */
class System extends BaseModel
{
    protected $table = 'systems';
    
    // ⚠️ توجه: جدول pm_systems فیلد system_id ندارد.
    // این جدول خودش «سیستم» است. پس متدهای BaseModel که بر اساس
    // system_id فیلتر می‌کنند، در این Model معنایی ندارند.
    // متدهای اختصاصی زیر برای این Model استفاده می‌شوند.

    protected $fillable = [
        'owner_user_id',
        'company_name',
        'industry',
        'description',
        'status',
    ];

    /**
     * دریافت سیستم متعلق به یک کاربر
     * (رابطه One-to-One)
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
        $data['system_id'] = $userId; // مقدار placeholder چون فیلد system_id در این جدول نیست
        unset($data['system_id']);
        
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
}