<?php
namespace App\Software\Hr\Models;

use App\Core\Database;

/**
 * ============================================================
 * KPI Model - شاخص‌های کلیدی عملکرد
 * ============================================================
 * ⚠️ نکته: جدول hr_kpis ستون system_id ندارد (سراسری است)
 * پس از BaseModel ارث نمی‌برد.
 * ============================================================
 */
class KPI
{
    /** @var \PDO */
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * دسته‌بندی‌ها
     */
    public static function getCategoryOptions(): array
    {
        return [
            'workforce'    => 'نیروی کار',
            'diversity'    => 'تنوع',
            'cost'         => 'هزینه',
            'productivity' => 'بهره‌وری',
            'safety'       => 'ایمنی',
            'leadership'   => 'رهبری',
            'culture'      => 'فرهنگ',
            'compliance'   => 'انطباق',
            'recruitment'  => 'جذب و استخدام',
            'retention'    => 'نگهداشت',
            'skills'       => 'مهارت‌ها',
            'other'        => 'سایر',
        ];
    }

    /**
     * رنگ‌های پیش‌فرض دسته‌بندی
     */
    public static function getCategoryColors(): array
    {
        return [
            'workforce'    => '#1E40AF',
            'diversity'    => '#9333EA',
            'cost'         => '#dc2626',
            'productivity' => '#0F766E',
            'safety'       => '#f59e0b',
            'leadership'   => '#0891b2',
            'culture'      => '#7C3AED',
            'compliance'   => '#374151',
            'recruitment'  => '#2563EB',
            'retention'    => '#dc2626',
            'skills'       => '#059669',
            'other'        => '#6b7280',
        ];
    }

    /**
     * آیکون پیش‌فرض دسته‌بندی
     */
    public static function getCategoryIcons(): array
    {
        return [
            'workforce'    => 'fas fa-users',
            'diversity'    => 'fas fa-venus-mars',
            'cost'         => 'fas fa-money-bill',
            'productivity' => 'fas fa-chart-line',
            'safety'       => 'fas fa-shield-alt',
            'leadership'   => 'fas fa-user-tie',
            'culture'      => 'fas fa-heart',
            'compliance'   => 'fas fa-balance-scale',
            'recruitment'  => 'fas fa-user-plus',
            'retention'    => 'fas fa-user-minus',
            'skills'       => 'fas fa-graduation-cap',
            'other'        => 'fas fa-chart-bar',
        ];
    }

    /**
     * نوع شاخص
     */
    public static function getTypeOptions(): array
    {
        return [
            'calculated' => 'محاسبه‌شده (خودکار)',
            'manual'     => 'دستی (ورود انسان)',
            'ratio'      => 'نسبت',
            'count'      => 'شمارش',
            'average'    => 'میانگین',
        ];
    }

    /**
     * جستجو با فیلترها
     */
    public function search(array $filters = []): array
    {
        $sql = "SELECT k.*,
                       (SELECT value FROM hr_kpi_values 
                        WHERE kpi_id = k.id AND system_id = :sid_v 
                        ORDER BY period_date DESC LIMIT 1) AS latest_value,
                       (SELECT target_value FROM hr_kpi_values 
                        WHERE kpi_id = k.id AND system_id = :sid_t 
                        ORDER BY period_date DESC LIMIT 1) AS latest_target,
                       (SELECT period_date FROM hr_kpi_values 
                        WHERE kpi_id = k.id AND system_id = :sid_d 
                        ORDER BY period_date DESC LIMIT 1) AS latest_date,
                       (SELECT COUNT(*) FROM hr_kpi_values 
                        WHERE kpi_id = k.id AND system_id = :sid_c) AS values_count
                FROM hr_kpis k
                WHERE 1=1";
        $params = [
            'sid_v' => $this->getSystemId(),
            'sid_t' => $this->getSystemId(),
            'sid_d' => $this->getSystemId(),
            'sid_c' => $this->getSystemId(),
        ];

        if (!empty($filters['q'])) {
            $sql .= " AND (k.name LIKE :q OR k.code LIKE :q2 OR k.description LIKE :q3)";
            $params['q']  = '%' . $filters['q'] . '%';
            $params['q2'] = '%' . $filters['q'] . '%';
            $params['q3'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['category'])) {
            $sql .= " AND k.category = :cat";
            $params['cat'] = $filters['category'];
        }
        if (!empty($filters['kpi_type'])) {
            $sql .= " AND k.kpi_type = :kt";
            $params['kt'] = $filters['kpi_type'];
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND k.is_active = :ia";
            $params['ia'] = (int) $filters['is_active'];
        }
        if (isset($filters['is_builtin']) && $filters['is_builtin'] !== '') {
            $sql .= " AND k.is_builtin = :ib";
            $params['ib'] = (int) $filters['is_builtin'];
        }

        $sql .= " ORDER BY k.sort_order ASC, k.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * یافتن با ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM hr_kpis WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * آمار
     */
    public function getStats(): array
    {
        $sysId = $this->getSystemId();

        $row = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN is_active=1 THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN is_builtin=1 THEN 1 ELSE 0 END) AS builtin,
                SUM(CASE WHEN is_builtin=0 THEN 1 ELSE 0 END) AS custom
             FROM hr_kpis"
        )->fetch(\PDO::FETCH_ASSOC);

        $valuesCount = 0;
        if ($sysId) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM hr_kpi_values WHERE system_id = :sid"
            );
            $stmt->execute(['sid' => $sysId]);
            $valuesCount = (int) $stmt->fetchColumn();
        }

        return [
            'total'        => (int) ($row['total'] ?? 0),
            'active'       => (int) ($row['active'] ?? 0),
            'builtin'      => (int) ($row['builtin'] ?? 0),
            'custom'       => (int) ($row['custom'] ?? 0),
            'values_count' => $valuesCount,
        ];
    }

    /**
     * شمارش بر اساس دسته
     */
    public function countByCategory(): array
    {
        $stmt = $this->db->query(
            "SELECT category, COUNT(*) AS count
             FROM hr_kpis
             WHERE is_active = 1
             GROUP BY category
             ORDER BY count DESC"
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * ایجاد
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO hr_kpis 
                (code, name, description, formula, unit, category, kpi_type,
                 is_builtin, is_active, sort_order, color, icon)
                VALUES 
                (:code, :name, :description, :formula, :unit, :category, :kpi_type,
                 :is_builtin, :is_active, :sort_order, :color, :icon)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'code'        => $data['code'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'formula'     => $data['formula'] ?? null,
            'unit'        => $data['unit'] ?? null,
            'category'    => $data['category'] ?? 'other',
            'kpi_type'    => $data['kpi_type'] ?? 'manual',
            'is_builtin'  => $data['is_builtin'] ?? 0,
            'is_active'   => $data['is_active'] ?? 1,
            'sort_order'  => $data['sort_order'] ?? 0,
            'color'       => $data['color'] ?? '#1E40AF',
            'icon'        => $data['icon'] ?? 'fas fa-chart-bar',
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * به‌روزرسانی
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE hr_kpis SET
                code = :code,
                name = :name,
                description = :description,
                formula = :formula,
                unit = :unit,
                category = :category,
                kpi_type = :kpi_type,
                is_active = :is_active,
                sort_order = :sort_order,
                color = :color,
                icon = :icon
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id'          => $id,
            'code'        => $data['code'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'formula'     => $data['formula'] ?? null,
            'unit'        => $data['unit'] ?? null,
            'category'    => $data['category'] ?? 'other',
            'kpi_type'    => $data['kpi_type'] ?? 'manual',
            'is_active'   => $data['is_active'] ?? 1,
            'sort_order'  => $data['sort_order'] ?? 0,
            'color'       => $data['color'] ?? '#1E40AF',
            'icon'        => $data['icon'] ?? 'fas fa-chart-bar',
        ]);
    }

    /**
     * حذف
     */
    public function delete(int $id): bool
    {
        // بررسی اینکه KPI ساخته‌شده توسط کاربر باشد
        $kpi = $this->find($id);
        if (!$kpi) return false;
        if (!empty($kpi['is_builtin'])) {
            return false; // نمی‌توان KPI سیستمی را حذف کرد
        }

        // حذف مقادیر مرتبط
        $stmt = $this->db->prepare("DELETE FROM hr_kpi_values WHERE kpi_id = :id");
        $stmt->execute(['id' => $id]);

        $stmt = $this->db->prepare("DELETE FROM hr_kpis WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * بررسی کد تکراری
     */
    public function isCodeDuplicate(string $code, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM hr_kpis WHERE code = :code";
        $params = ['code' => $code];
        if ($excludeId !== null) {
            $sql .= " AND id != :xid";
            $params['xid'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * لیست برای select
     */
    public function getSelectList(): array
    {
        $stmt = $this->db->query(
            "SELECT id, code, name, unit, category 
             FROM hr_kpis 
             WHERE is_active = 1 
             ORDER BY category, sort_order, name"
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت مقادیر یک KPI
     */
    public function getValues(int $kpiId, ?string $period = null): array
    {
        $sysId = $this->getSystemId();
        if (!$sysId) return [];

        $sql = "SELECT * FROM hr_kpi_values 
                WHERE kpi_id = :kid AND system_id = :sid";
        $params = ['kid' => $kpiId, 'sid' => $sysId];

        if ($period) {
            $sql .= " AND period = :p";
            $params['p'] = $period;
        }

        $sql .= " ORDER BY period_date DESC, id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت آخرین مقدار هر KPI
     */
    public function getLatestValues(): array
    {
        $sysId = $this->getSystemId();
        if (!$sysId) return [];

        $sql = "SELECT v.*, k.code, k.name, k.unit, k.category, k.color, k.icon
                FROM hr_kpi_values v
                INNER JOIN hr_kpis k ON k.id = v.kpi_id
                INNER JOIN (
                    SELECT kpi_id, MAX(period_date) AS max_date
                    FROM hr_kpi_values
                    WHERE system_id = :sid1
                    GROUP BY kpi_id
                ) latest ON latest.kpi_id = v.kpi_id AND latest.max_date = v.period_date
                WHERE v.system_id = :sid2
                ORDER BY k.sort_order ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sid1' => $sysId, 'sid2' => $sysId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت شناسه سیستم فعال
     */
    private function getSystemId(): ?int
    {
        return !empty($_SESSION['hr_active_system'])
            ? (int) $_SESSION['hr_active_system']
            : null;
    }
}