<?php
namespace App\Software\Pdm\Models;

/**
 * ============================================================
 * MaintenancePlan Model - مدل برنامه‌های نگهداری
 * ============================================================
 * مسیر: app/software/pdm/app/Models/MaintenancePlan.php
 * ============================================================
 */
class MaintenancePlan extends BaseModel
{
    protected $table = 'maintenance_plans';

    protected $fillable = [
        'asset_id',
        'maintenance_type_id',
        'title',
        'description',
        'frequency_value',
        'frequency_unit',
        'start_date',
        'next_execution',
        'priority',
        'status',
    ];

    /**
     * دریافت همه برنامه‌ها با اطلاعات دارایی و نوع
     */
    public function getAllWithDetails(): array
    {
        $sql = "SELECT mp.*, 
                       a.name AS asset_name,
                       a.asset_code,
                       a.criticality AS asset_criticality,
                       mt.name AS maintenance_type_name,
                       mt.code AS maintenance_type_code
                FROM {$this->tableName()} mp
                LEFT JOIN {$this->prefix}assets a ON mp.asset_id = a.id
                LEFT JOIN {$this->prefix}maintenance_types mt ON mp.maintenance_type_id = mt.id
                WHERE mp.system_id = ?
                ORDER BY 
                    FIELD(mp.status, 'active', 'inactive'),
                    mp.next_execution ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت با جزئیات
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT mp.*, 
                       a.name AS asset_name,
                       a.asset_code,
                       a.criticality AS asset_criticality,
                       mt.name AS maintenance_type_name,
                       mt.code AS maintenance_type_code
                FROM {$this->tableName()} mp
                LEFT JOIN {$this->prefix}assets a ON mp.asset_id = a.id
                LEFT JOIN {$this->prefix}maintenance_types mt ON mp.maintenance_type_id = mt.id
                WHERE mp.id = ? AND mp.system_id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * برنامه‌های سررسید شده
     */
    public function getDuePlans(): array
    {
        $sql = "SELECT mp.*, a.name AS asset_name, a.asset_code
                FROM {$this->tableName()} mp
                LEFT JOIN {$this->prefix}assets a ON mp.asset_id = a.id
                WHERE mp.system_id = ?
                  AND mp.status = 'active'
                  AND mp.next_execution <= CURDATE()
                ORDER BY mp.next_execution ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * برنامه‌های نزدیک به سررسید (طی N روز آینده)
     */
    public function getUpcomingPlans(int $days = 7): array
    {
        $sql = "SELECT mp.*, a.name AS asset_name, a.asset_code
                FROM {$this->tableName()} mp
                LEFT JOIN {$this->prefix}assets a ON mp.asset_id = a.id
                WHERE mp.system_id = ?
                  AND mp.status = 'active'
                  AND mp.next_execution BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY mp.next_execution ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->systemId, $days]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * توزیع وضعیت
     */
    public function getStatusDistribution(): array
    {
        $stmt = $this->db->prepare(
            "SELECT status, COUNT(*) AS count
             FROM {$this->tableName()}
             WHERE system_id = ?
             GROUP BY status"
        );
        $stmt->execute([$this->systemId]);

        $result = ['active' => 0, 'inactive' => 0];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[$row['status']] = (int) $row['count'];
        }
        return $result;
    }

    /**
     * محاسبه تاریخ اجرای بعدی
     * 
     * @param string $startDate تاریخ شروع (میلادی)
     * @param int $value مقدار
     * @param string $unit واحد (days, weeks, months, years, hours, cycles)
     * @return string تاریخ بعدی (میلادی)
     */
    public function calculateNextExecution(string $startDate, int $value, string $unit): string
    {
        $date = new \DateTime($startDate);

        switch ($unit) {
            case 'days':
                $date->modify("+{$value} days");
                break;
            case 'weeks':
                $date->modify("+{$value} weeks");
                break;
            case 'months':
                $date->modify("+{$value} months");
                break;
            case 'years':
                $date->modify("+{$value} years");
                break;
            case 'hours':
                // ساعت → در سیستم ما تقریبی: 24 ساعت = 1 روز
                $days = (int) ceil($value / 24);
                $date->modify("+{$days} days");
                break;
            case 'cycles':
                // چرخه → تقریبی: هر چرخه = 1 روز
                $date->modify("+{$value} days");
                break;
            default:
                $date->modify("+{$value} days");
        }

        return $date->format('Y-m-d');
    }

    /**
     * واحدهای فرکانس
     */
    public static function getFrequencyUnits(): array
    {
        return [
            'days'   => 'روز',
            'weeks'  => 'هفته',
            'months' => 'ماه',
            'years'  => 'سال',
            'hours'  => 'ساعت کارکرد',
            'cycles' => 'چرخه',
        ];
    }

    /**
     * برچسب واحد فرکانس
     */
    public static function frequencyUnitLabel(string $unit): string
    {
        $units = self::getFrequencyUnits();
        return $units[$unit] ?? $unit;
    }
}