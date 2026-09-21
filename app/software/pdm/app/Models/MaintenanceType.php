<?php
namespace App\Software\Pdm\Models;

/**
 * ============================================================
 * MaintenanceType Model - مدل انواع نگهداری
 * ============================================================
 * مسیر: app/software/pdm/app/Models/MaintenanceType.php
 * 
 * نکته: این جدول سراسری است (system_id ندارد) و برای همه سیستم‌ها یکسان است.
 * ============================================================
 */
class MaintenanceType extends BaseModel
{
    protected $table = 'maintenance_types';

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    /**
     * Override: این جدول system_id ندارد
     */
    public function all(string $orderBy = 'id ASC', ?int $limit = null): array
    {
        $sql = "SELECT * FROM {$this->tableName()} ORDER BY {$orderBy}";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int) $limit;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Override: یافتن با ID بدون فیلتر system_id
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tableName()} WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * لیست برای Select Box
     */
    public function getSelectList(): array
    {
        return $this->all('id ASC');
    }

    /**
     * دریافت با کد
     */
    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tableName()} WHERE code = ? LIMIT 1"
        );
        $stmt->execute([$code]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}