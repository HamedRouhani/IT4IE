<?php
namespace App\Software\Pdm\Models;

/**
 * ============================================================
 * KPI Model - مدل شاخص‌های کلیدی (پویا)
 * ============================================================
 * مسیر: app/software/pdm/app/Models/KPI.php
 * 
 * این Model مدیریت کامل KPIها را انجام می‌دهد:
 * - KPIهای داخلی (builtin): MTTR, MTBF, Availability, ...
 * - KPIهای تعریف‌شده توسط کاربر
 * - محاسبه پویا بر اساس فرمول
 * ============================================================
 */
class KPI extends BaseModel
{
    protected $table = 'kpis';

    protected $fillable = [
        'code',
        'name',
        'description',
        'formula',
        'unit',
        'kpi_type',
        'is_builtin',
        'is_active',
        'sort_order',
        'color',
        'icon',
    ];

    /**
     * ⚠️ Override: جدول kpis سراسری است (system_id ندارد)
     */
    public function all(string $orderBy = 'sort_order ASC, id ASC', ?int $limit = null): array
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
     * ⚠️ Override: فقط KPIهای فعال
     */
    public function getActive(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tableName()} 
             WHERE is_active = 1 
             ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت KPI با ID
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
     * دریافت KPI با کد
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

    /**
     * بررسی تکراری بودن کد
     */
    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        if (empty($code)) return false;

        $sql = "SELECT 1 FROM {$this->tableName()} WHERE code = ?";
        $params = [$code];

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
     * ⚠️ Override: create (بدون system_id)
     */
    public function create(array $data): int
    {
        $fields = array_intersect_key($data, array_flip($this->fillable));

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
     * ⚠️ Override: update (بدون system_id)
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

        $sql = "UPDATE {$this->tableName()} SET {$set} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * ⚠️ Override: delete (بدون system_id) - فقط KPIهای غیر builtin
     */
    public function delete(int $id): bool
    {
        // KPIهای builtin قابل حذف نیستند
        $kpi = $this->find($id);
        if ($kpi && (int) $kpi['is_builtin'] === 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            "DELETE FROM {$this->tableName()} WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * محاسبه تمام KPIهای فعال
     */
    public function calculateAll(): array
    {
        $activeKPIs = $this->getActive();
        $results = [];

        // محاسبات پایه‌ای که یک بار انجام می‌شوند
        $workOrderModel = new WorkOrder();
        $baseValues = [
            'mttr'         => $workOrderModel->calculateMTTR(),
            'mtbf'         => $workOrderModel->calculateMTBF(),
        ];

        foreach ($activeKPIs as $kpi) {
            $code = $kpi['code'];
            $results[$code] = $this->calculateKPI($kpi, $baseValues);
        }

        return $results;
    }

    /**
     * محاسبه یک KPI خاص
     */
    private function calculateKPI(array $kpi, array $baseValues): ?float
    {
        $code = $kpi['code'];
        $mttr = $baseValues['mttr'];
        $mtbf = $baseValues['mtbf'];

        switch ($code) {
            case 'MTTR':
                return $mttr;

            case 'MTBF':
                return $mtbf;

            case 'Availability':
                if ($mtbf !== null && $mttr !== null && ($mtbf + $mttr) > 0) {
                    return round(($mtbf / ($mtbf + $mttr)) * 100, 2);
                }
                return null;

            case 'OEE':
                if ($mtbf !== null && $mttr !== null && ($mtbf + $mttr) > 0) {
                    return round(($mtbf / ($mtbf + $mttr)) * 100, 2);
                }
                return null;

            case 'FailureRate':
                if ($mtbf !== null && $mtbf > 0) {
                    return round(1 / $mtbf, 4);
                }
                return null;

            case 'Reliability':
                if ($mtbf !== null && $mtbf > 0) {
                    return round(exp(-100 / $mtbf) * 100, 2);
                }
                return null;

            default:
                // برای KPIهای کاربر: اگر مقدار دستی ثبت شده، آن را برگردان
                return $this->calculateCustomKPI($kpi);
        }
    }

    /**
     * محاسبه KPIهای سفارشی کاربر
     * 
     * KPIهای سفارشی می‌توانند:
     * - مقدار دستی داشته باشند (kpi_type = manual)
     * - یا فرمول ساده داشته باشند (kpi_type = calculated)
     */
    private function calculateCustomKPI(array $kpi): ?float
    {
        // در این نسخه، KPI سفارشی مقدار دستی ندارد
        // (در فاز بعدی می‌توان جدول kpi_values را اضافه کرد)
        return null;
    }

    /**
     * KPIهای داخلی (builtin) سیستم
     */
    public static function getBuiltinKPIs(): array
    {
        return [
            [
                'code'        => 'MTTR',
                'name'        => 'میانگین زمان تعمیر',
                'description' => 'میانگین زمان لازم برای تعمیر یک تجهیز',
                'formula'     => 'Total Repair Time / Number of Repairs',
                'unit'        => 'ساعت',
                'kpi_type'    => 'builtin',
                'is_builtin'  => 1,
                'icon'        => 'fas fa-clock',
                'color'       => '#0F766E',
            ],
            [
                'code'        => 'MTBF',
                'name'        => 'میانگین زمان بین خرابی‌ها',
                'description' => 'میانگین زمان کارکرد بین دو خرابی متوالی',
                'formula'     => 'Total Operating Time / Number of Failures',
                'unit'        => 'ساعت',
                'kpi_type'    => 'builtin',
                'is_builtin'  => 1,
                'icon'        => 'fas fa-history',
                'color'       => '#198754',
            ],
            [
                'code'        => 'Availability',
                'name'        => 'دسترس‌پذیری',
                'description' => 'درصد زمانی که تجهیز در دسترس است',
                'formula'     => 'MTBF / (MTBF + MTTR) × 100',
                'unit'        => 'درصد',
                'kpi_type'    => 'builtin',
                'is_builtin'  => 1,
                'icon'        => 'fas fa-percentage',
                'color'       => '#0d6efd',
            ],
            [
                'code'        => 'OEE',
                'name'        => 'اثربخشی کلی تجهیزات',
                'description' => 'شاخص جامع اثربخشی تجهیز',
                'formula'     => 'Availability × Performance × Quality',
                'unit'        => 'درصد',
                'kpi_type'    => 'builtin',
                'is_builtin'  => 1,
                'icon'        => 'fas fa-cogs',
                'color'       => '#14B8A6',
            ],
            [
                'code'        => 'FailureRate',
                'name'        => 'نرخ خرابی',
                'description' => 'تعداد خرابی در واحد زمان کارکرد',
                'formula'     => 'Number of Failures / Total Operating Time',
                'unit'        => 'خرابی/ساعت',
                'kpi_type'    => 'builtin',
                'is_builtin'  => 1,
                'icon'        => 'fas fa-exclamation-circle',
                'color'       => '#f59e0b',
            ],
            [
                'code'        => 'Reliability',
                'name'        => 'قابلیت اطمینان',
                'description' => 'احتمال کارکرد صحیح در ۱۰۰ ساعت',
                'formula'     => 'exp(-t / MTBF)',
                'unit'        => 'درصد',
                'kpi_type'    => 'builtin',
                'is_builtin'  => 1,
                'icon'        => 'fas fa-shield-alt',
                'color'       => '#6C3CE1',
            ],
        ];
    }

    /**
     * دریافت KPIها به همراه مقدار محاسبه‌شده
     */
    public function getActiveWithValues(): array
    {
        $kpis = $this->getActive();
        $values = $this->calculateAll();

        foreach ($kpis as &$kpi) {
            $kpi['current_value'] = $values[$kpi['code']] ?? null;
        }

        return $kpis;
    }
}