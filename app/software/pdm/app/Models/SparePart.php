<?php
namespace App\Software\Pdm\Models;

/**
 * ============================================================
 * SparePart Model - مدل قطعات یدکی
 * ============================================================
 * مسیر: app/software/pdm/app/Models/SparePart.php
 * ============================================================
 */
class SparePart extends BaseModel
{
    protected $table = 'spare_parts';

    protected $fillable = [
        'code',
        'name',
        'manufacturer',
        'stock_quantity',
        'minimum_stock',
        'unit',
        'description',
    ];

    /**
     * دریافت همه قطعات
     */
    public function getAllWithDetails(): array
    {
        $sql = "SELECT sp.*,
                       (SELECT COUNT(*) FROM {$this->prefix}asset_spare_parts asp WHERE asp.spare_part_id = sp.id) AS assets_count
                FROM {$this->tableName()} sp
                WHERE sp.system_id = ?
                ORDER BY sp.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * جستجو
     */
    public function search(array $filters = []): array
    {
        $sql = "SELECT sp.*,
                       (SELECT COUNT(*) FROM {$this->prefix}asset_spare_parts asp WHERE asp.spare_part_id = sp.id) AS assets_count
                FROM {$this->tableName()} sp
                WHERE sp.system_id = ?";
        $params = [$this->systemId];

        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $sql .= " AND (sp.code LIKE ? OR sp.name LIKE ? OR sp.manufacturer LIKE ?)";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        if (!empty($filters['low_stock'])) {
            $sql .= " AND sp.stock_quantity <= sp.minimum_stock";
        }

        $sql .= " ORDER BY sp.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * قطعاتی که موجودی کمتر از حد مجاز دارند
     */
    public function getLowStock(): array
    {
        $sql = "SELECT * FROM {$this->tableName()}
                WHERE system_id = ? AND stock_quantity <= minimum_stock
                ORDER BY (stock_quantity - minimum_stock) ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * لیست برای Select Box
     */
    public function getSelectList(): array
    {
        $sql = "SELECT id, code, name, stock_quantity FROM {$this->tableName()}
                WHERE system_id = ?
                ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * بررسی تکراری بودن کد
     */
    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        if (empty($code)) return false;

        $sql = "SELECT 1 FROM {$this->tableName()} 
                WHERE system_id = ? AND code = ?";
        $params = [$this->systemId, $code];

        if ($exceptId !== null) {
            $sql .= " AND id != ?";
            $params[] = $exceptId;
        }
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * آمار موجودی
     */
    public function getStockStats(): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN stock_quantity <= minimum_stock THEN 1 ELSE 0 END) AS low_stock,
                SUM(stock_quantity) AS total_quantity
             FROM {$this->tableName()}
             WHERE system_id = ?"
        );
        $stmt->execute([$this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'          => (int) ($row['total'] ?? 0),
            'low_stock'      => (int) ($row['low_stock'] ?? 0),
            'total_quantity' => (int) ($row['total_quantity'] ?? 0),
        ];
    }
}