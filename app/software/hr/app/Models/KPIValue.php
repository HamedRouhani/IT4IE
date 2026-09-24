<?php
namespace App\Software\Hr\Models;

use App\Core\Database;

/**
 * ============================================================
 * KPIValue Model - مقادیر دوره‌ای شاخص‌ها
 * ============================================================
 */
class KPIValue
{
    /** @var \PDO */
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * جستجو با فیلترها
     */
    public function search(array $filters = []): array
    {
        $sql = "SELECT v.*,
                       k.code AS kpi_code, k.name AS kpi_name, k.unit, k.category,
                       k.color, k.icon
                FROM hr_kpi_values v
                INNER JOIN hr_kpis k ON k.id = v.kpi_id
                WHERE v.system_id = :sid";
        $params = ['sid' => $this->getSystemId()];

        if (!empty($filters['q'])) {
            $sql .= " AND (k.name LIKE :q OR k.code LIKE :q2)";
            $params['q']  = '%' . $filters['q'] . '%';
            $params['q2'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['kpi_id'])) {
            $sql .= " AND v.kpi_id = :kid";
            $params['kid'] = (int) $filters['kpi_id'];
        }
        if (!empty($filters['category'])) {
            $sql .= " AND k.category = :cat";
            $params['cat'] = $filters['category'];
        }
        if (!empty($filters['period'])) {
            $sql .= " AND v.period LIKE :p";
            $params['p'] = '%' . $filters['period'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND v.period_date >= :df";
            $params['df'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND v.period_date <= :dt";
            $params['dt'] = $filters['date_to'];
        }

        $sql .= " ORDER BY v.period_date DESC, v.id DESC LIMIT 1000";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * آمار
     */
    public function getStats(): array
    {
        $sysId = $this->getSystemId();
        if (!$sysId) {
            return ['total' => 0, 'this_month' => 0, 'achieved' => 0, 'avg_achievement' => 0];
        }

        $row = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN MONTH(period_date) = MONTH(CURDATE()) 
                          AND YEAR(period_date) = YEAR(CURDATE()) 
                         THEN 1 ELSE 0 END) AS this_month,
                SUM(CASE WHEN target_value IS NOT NULL 
                          AND target_value > 0 
                          AND value >= target_value 
                         THEN 1 ELSE 0 END) AS achieved,
                COALESCE(AVG(CASE WHEN target_value IS NOT NULL AND target_value > 0 
                                   THEN (value / target_value) * 100 
                                   END), 0) AS avg_achievement
             FROM hr_kpi_values
             WHERE system_id = " . (int) $sysId
        )->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'           => (int) ($row['total'] ?? 0),
            'this_month'      => (int) ($row['this_month'] ?? 0),
            'achieved'        => (int) ($row['achieved'] ?? 0),
            'avg_achievement' => round((float) ($row['avg_achievement'] ?? 0), 1),
        ];
    }

    /**
     * یافتن با ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM hr_kpi_values WHERE id = :id AND system_id = :sid LIMIT 1"
        );
        $stmt->execute(['id' => $id, 'sid' => $this->getSystemId()]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * یافتن با جزئیات (شامل اطلاعات KPI)
     */
    public function findWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT v.*,
                    k.code AS kpi_code, k.name AS kpi_name, k.unit, 
                    k.category, k.description AS kpi_description,
                    k.color, k.icon, k.formula
             FROM hr_kpi_values v
             INNER JOIN hr_kpis k ON k.id = v.kpi_id
             WHERE v.id = :id AND v.system_id = :sid LIMIT 1"
        );
        $stmt->execute(['id' => $id, 'sid' => $this->getSystemId()]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * ایجاد
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO hr_kpi_values 
                (system_id, kpi_id, value, target_value, period, period_date, notes)
                VALUES 
                (:system_id, :kpi_id, :value, :target_value, :period, :period_date, :notes)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'system_id'    => $this->getSystemId(),
            'kpi_id'       => (int) $data['kpi_id'],
            'value'        => $data['value'] ?? null,
            'target_value' => $data['target_value'] ?? null,
            'period'       => $data['period'],
            'period_date'  => $data['period_date'],
            'notes'        => $data['notes'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * به‌روزرسانی
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE hr_kpi_values SET
                kpi_id = :kpi_id,
                value = :value,
                target_value = :target_value,
                period = :period,
                period_date = :period_date,
                notes = :notes
                WHERE id = :id AND system_id = :system_id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id'           => $id,
            'system_id'    => $this->getSystemId(),
            'kpi_id'       => (int) $data['kpi_id'],
            'value'        => $data['value'] ?? null,
            'target_value' => $data['target_value'] ?? null,
            'period'       => $data['period'],
            'period_date'  => $data['period_date'],
            'notes'        => $data['notes'] ?? null,
        ]);
    }

    /**
     * حذف
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM hr_kpi_values WHERE id = :id AND system_id = :sid"
        );
        return $stmt->execute(['id' => $id, 'sid' => $this->getSystemId()]);
    }

    /**
     * بررسی وجود
     */
    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM hr_kpi_values WHERE id = :id AND system_id = :sid LIMIT 1"
        );
        $stmt->execute(['id' => $id, 'sid' => $this->getSystemId()]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * بررسی تکراری (برای یک KPI + دوره)
     */
    public function isDuplicate(int $kpiId, string $period, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM hr_kpi_values
                WHERE kpi_id = :kid AND period = :p AND system_id = :sid";
        $params = [
            'kid' => $kpiId,
            'p'   => $period,
            'sid' => $this->getSystemId(),
        ];
        if ($excludeId !== null) {
            $sql .= " AND id != :xid";
            $params['xid'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * درصد دستیابی
     */
    public static function achievementPercent($value, $target): ?float
    {
        if ($target === null || (float) $target == 0) {
            return null;
        }
        return round(((float) $value / (float) $target) * 100, 1);
    }

    /**
     * رنگ بر اساس درصد دستیابی
     */
    public static function achievementClass(?float $percent): string
    {
        if ($percent === null) return 'hr-status-inactive';
        if ($percent >= 100) return 'hr-status-active';
        if ($percent >= 80)  return 'hr-status-info';
        if ($percent >= 60)  return 'hr-status-warning';
        return 'hr-status-danger';
    }

    /**
     * شناسه سیستم فعال
     */
    private function getSystemId(): ?int
    {
        return !empty($_SESSION['hr_active_system'])
            ? (int) $_SESSION['hr_active_system']
            : null;
    }
}