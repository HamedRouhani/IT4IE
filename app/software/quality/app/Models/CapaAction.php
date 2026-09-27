<?php
namespace App\Software\Quality\Models;

use App\Core\Model;

class CapaAction extends Model
{
    protected $table = 'qc_capa_actions';

    /**
     * دریافت CAPA های یک سیستم
     */
    public function findBySystem($systemId)
    {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE system_id = :system_id 
             ORDER BY 
                FIELD(priority, 'critical', 'high', 'medium', 'low'),
                due_date ASC",
            ['system_id' => $systemId]
        );
    }

    /**
     * دریافت CAPA های یک پروژه
     */
    public function findByProject($projectId)
    {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE project_id = :project_id 
             ORDER BY created_at DESC",
            ['project_id' => $projectId]
        );
    }

    /**
     * دریافت CAPA های مرتبط با یک منبع خاص
     * مثلاً: همه‌ی CAPA های یک تحلیل Pareto
     */
    public function findBySource($sourceType, $sourceId)
    {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE source_type = :source_type AND source_id = :source_id
             ORDER BY created_at DESC",
            ['source_type' => $sourceType, 'source_id' => $sourceId]
        );
    }

    /**
     * فیلتر بر اساس وضعیت
     */
    public function findByStatus($systemId, $status)
    {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE system_id = :system_id AND status = :status
             ORDER BY due_date ASC",
            ['system_id' => $systemId, 'status' => $status]
        );
    }

    /**
     * CAPA های سررسید گذشته (Overdue)
     */
    public function findOverdue($systemId)
    {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE system_id = :system_id 
               AND due_date < CURDATE() 
               AND status NOT IN ('closed', 'cancelled', 'verified')
             ORDER BY due_date ASC",
            ['system_id' => $systemId]
        );
    }

    /**
     * CAPA های در حال اجرا (open + in_progress + implemented)
     */
    public function findActive($systemId)
    {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE system_id = :system_id 
               AND status IN ('open', 'in_progress', 'implemented')
             ORDER BY 
                FIELD(priority, 'critical', 'high', 'medium', 'low'),
                due_date ASC",
            ['system_id' => $systemId]
        );
    }

    /**
     * آمار کلی CAPA
     */
    public function getStatsBySystem($systemId)
    {
        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) AS open_count,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_count,
                    SUM(CASE WHEN status = 'implemented' THEN 1 ELSE 0 END) AS implemented_count,
                    SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) AS verified_count,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) AS closed_count,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_count,
                    SUM(CASE WHEN due_date < CURDATE() 
                              AND status NOT IN ('closed', 'cancelled', 'verified') 
                             THEN 1 ELSE 0 END) AS overdue_count,
                    SUM(CASE WHEN effectiveness = 'effective' THEN 1 ELSE 0 END) AS effective_count,
                    SUM(CASE WHEN priority = 'critical' 
                              AND status NOT IN ('closed', 'cancelled') 
                             THEN 1 ELSE 0 END) AS critical_count,
                    SUM(estimated_cost) AS estimated_cost_total,
                    SUM(actual_cost) AS actual_cost_total
                FROM {$this->table} 
                WHERE system_id = :system_id";
        
        $result = $this->queryOne($sql, ['system_id' => $systemId]);
        
        return $result ?: [
            'total' => 0, 'open_count' => 0, 'in_progress_count' => 0,
            'implemented_count' => 0, 'verified_count' => 0, 'closed_count' => 0,
            'cancelled_count' => 0, 'overdue_count' => 0, 'effective_count' => 0,
            'critical_count' => 0, 'estimated_cost_total' => 0, 'actual_cost_total' => 0
        ];
    }

    /**
     * آمار بر اساس منبع (Source)
     */
    public function getStatsBySource($systemId)
    {
        return $this->query(
            "SELECT 
                source_type,
                COUNT(*) AS count,
                SUM(CASE WHEN status IN ('closed', 'verified') THEN 1 ELSE 0 END) AS resolved
             FROM {$this->table} 
             WHERE system_id = :system_id
             GROUP BY source_type
             ORDER BY count DESC",
            ['system_id' => $systemId]
        );
    }

    /**
     * CAPA های مرتبط با یک Pareto Analysis
     * (کمکی برای نمایش در صفحه‌ی Pareto)
     */
    public function countBySource($sourceType, $sourceId)
    {
        return $this->count(
            'source_type = :source_type AND source_id = :source_id',
            ['source_type' => $sourceType, 'source_id' => $sourceId]
        );
    }

    /**
     * به‌روزرسانی سریع وضعیت
     */
    public function updateStatus($id, $status, $extra = [])
    {
        $data = array_merge(['status' => $status], $extra);
        
        // اگر status = implemented شد، implemented_date رو ثبت کن
        if ($status === 'implemented' && empty($extra['implemented_date'])) {
            $data['implemented_date'] = date('Y-m-d');
        }
        // اگر status = verified شد، verification_date رو ثبت کن
        if ($status === 'verified' && empty($extra['verification_date'])) {
            $data['verification_date'] = date('Y-m-d');
        }
        
        return $this->update($id, $data);
    }

    /**
     * محاسبه اثربخشی
     */
    public function calculateEffectiveness($id)
    {
        $action = $this->find($id);
        if (!$action || $action['before_value'] === null || $action['after_value'] === null) {
            return null;
        }

        $before = (float)$action['before_value'];
        $after  = (float)$action['after_value'];

        if ($before <= 0) {
            return null;
        }

        // درصد بهبود
        $improvement = (($before - $after) / $before) * 100;

        // تعیین وضعیت اثربخشی
        if ($improvement >= 50) {
            $effectiveness = 'effective';
        } elseif ($improvement >= 20) {
            $effectiveness = 'partially_effective';
        } else {
            $effectiveness = 'not_effective';
        }

        $this->update($id, [
            'effectiveness' => $effectiveness,
        ]);

        return [
            'improvement'   => round($improvement, 2),
            'effectiveness' => $effectiveness,
        ];
    }

    /**
     * آمار برای داشبورد سیستم
     */
    public function getDashboardStats($systemId)
    {
        $stats = $this->getStatsBySystem($systemId);
        $bySource = $this->getStatsBySource($systemId);
        $overdue = $this->findOverdue($systemId);

        return [
            'total'      => (int)$stats['total'],
            'open'       => (int)$stats['open_count'],
            'in_progress'=> (int)$stats['in_progress_count'],
            'implemented'=> (int)$stats['implemented_count'],
            'verified'   => (int)$stats['verified_count'],
            'closed'     => (int)$stats['closed_count'],
            'cancelled'  => (int)$stats['cancelled_count'],
            'overdue'    => (int)$stats['overdue_count'],
            'effective'  => (int)$stats['effective_count'],
            'critical'   => (int)$stats['critical_count'],
            'estimated_cost' => (float)$stats['estimated_cost_total'],
            'actual_cost'    => (float)$stats['actual_cost_total'],
            'by_source'  => $bySource,
            'overdue_list' => array_slice($overdue, 0, 5),
        ];
    }
}