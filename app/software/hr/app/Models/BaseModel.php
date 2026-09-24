<?php
namespace App\Software\Hr\Models;

use App\Core\Database;

/**
 * ============================================================
 * BaseModel - مدل پایه ماژول HR
 * ============================================================
 * مسیر: app/software/hr/app/Models/BaseModel.php
 * 
 * این کلاس پایه تمام Modelهای ماژول HR است و:
 * - اتصال به دیتابیس را مدیریت می‌کند
 * - فیلتر system_id را به صورت خودکار اعمال می‌کند
 * - متدهای CRUD پایه را فراهم می‌کند
 * ============================================================
 */
abstract class BaseModel
{
    /** @var \PDO */
    protected $db;

    /** @var string نام جدول (بدون پیشوند) */
    protected $table = '';

    /** @var string پیشوند جداول */
    protected $prefix = 'hr_';

    /** @var int|null شناسه سیستم فعال */
    protected $systemId;

    /** @var array فیلدهای قابل نوشتن */
    protected $fillable = [];

    public function __construct(?int $systemId = null)
    {
        $this->db = Database::getInstance();

        if ($systemId === null) {
            $systemId = !empty($_SESSION['hr_active_system'])
                ? (int) $_SESSION['hr_active_system']
                : null;
        }

        $this->systemId = $systemId;
    }

    /**
     * ساخت نام کامل جدول با پیشوند
     */
    public function tableName(): string
    {
        return $this->prefix . $this->table;
    }

    /**
     * دریافت شناسه سیستم
     */
    public function getSystemId(): ?int
    {
        return $this->systemId;
    }

    /**
     * تنظیم شناسه سیستم
     */
    public function setSystemId(int $systemId): void
    {
        $this->systemId = $systemId;
    }

    /**
     * دریافت همه رکوردهای سیستم فعال
     */
    public function all(string $orderBy = 'id DESC', ?int $limit = null): array
    {
        $sql = "SELECT * FROM {$this->tableName()} WHERE system_id = ? ORDER BY {$orderBy}";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int) $limit;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * یافتن رکورد با ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tableName()} WHERE id = ? AND system_id = ? LIMIT 1"
        );
        $stmt->execute([$id, $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * یافتن رکورد با شرط دلخواه
     */
    public function findBy(string $where, array $params = []): ?array
    {
        $sql = "SELECT * FROM {$this->tableName()} WHERE system_id = ? AND ({$where}) LIMIT 1";
        $params = array_merge([$this->systemId], $params);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * دریافت چند رکورد با شرط دلخواه
     */
    public function where(string $where, array $params = [], string $orderBy = 'id DESC', ?int $limit = null): array
    {
        $sql = "SELECT * FROM {$this->tableName()} WHERE system_id = ? AND ({$where}) ORDER BY {$orderBy}";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int) $limit;
        }
        $params = array_merge([$this->systemId], $params);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * ایجاد رکورد جدید
     */
    public function create(array $data): int
    {
        $data['system_id'] = $this->systemId;

        $fields = array_intersect_key($data, array_flip(array_merge($this->fillable, ['system_id'])));

        if (empty($fields)) {
            throw new \Exception('هیچ فیلد قابل نوشتنی ارسال نشده است.');
        }

        $columns = implode(', ', array_keys($fields));
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));

        $sql = "INSERT INTO {$this->tableName()} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($fields));

        return (int) $this->db->lastInsertId();
    }

    /**
     * به‌روزرسانی رکورد
     */
    public function update(int $id, array $data): bool
    {
        $fields = array_intersect_key($data, array_flip($this->fillable));

        if (empty($fields)) {
            return false;
        }

        $set = implode(' = ?, ', array_keys($fields)) . ' = ?';
        $values = array_values($fields);
        $values[] = $id;
        $values[] = $this->systemId;

        $sql = "UPDATE {$this->tableName()} SET {$set} WHERE id = ? AND system_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * حذف رکورد
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->tableName()} WHERE id = ? AND system_id = ?"
        );
        return $stmt->execute([$id, $this->systemId]);
    }

    /**
     * شمارش رکوردها
     */
    public function count(string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->tableName()} WHERE system_id = ?";
        if (!empty($where)) {
            $sql .= " AND ({$where})";
        }
        $params = array_merge([$this->systemId], $params);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * بررسی وجود رکورد
     */
    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->tableName()} WHERE id = ? AND system_id = ? LIMIT 1"
        );
        $stmt->execute([$id, $this->systemId]);
        return (bool) $stmt->fetchColumn();
    }
}