<?php
namespace App\Software\Quality\Models;

use App\Core\Model;

class ParetoItem extends Model
{
    protected $table = 'qc_pareto_items';

    /**
     * دریافت آیتم‌های یک تحلیل (مرتب‌شده بر اساس value نزولی)
     */
    public function findByAnalysis($analysisId)
    {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE analysis_id = :analysis_id 
             ORDER BY value DESC, id ASC",
            ['analysis_id' => $analysisId]
        );
    }

    /**
     * دریافت فقط Vital Few (80%)
     */
    public function findVitalFew($analysisId)
    {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE analysis_id = :analysis_id AND is_vital_few = 1
             ORDER BY value DESC",
            ['analysis_id' => $analysisId]
        );
    }

    /**
     * حذف تمام آیتم‌های یک تحلیل
     */
    public function deleteByAnalysis($analysisId)
    {
        return $this->query(
            "DELETE FROM {$this->table} WHERE analysis_id = :analysis_id",
            ['analysis_id' => $analysisId]
        );
    }

    /**
     * Bulk Insert برای آیتم‌ها
     */
    public function bulkInsert($analysisId, array $items)
    {
        if (empty($items)) {
            return false;
        }

        foreach ($items as $item) {
            $this->create([
                'analysis_id'        => $analysisId,
                'category_name'      => $item['category_name'],
                'value'              => $item['value'] ?? 0,
                'frequency'          => $item['frequency'] ?? null,
                'cost_per_unit'      => $item['cost_per_unit'] ?? null,
                'sort_order'         => $item['sort_order'] ?? 0,
                'cumulative_value'   => $item['cumulative_value'] ?? 0,
                'cumulative_percent' => $item['cumulative_percent'] ?? 0,
                'percent'            => $item['percent'] ?? 0,
                'is_vital_few'       => $item['is_vital_few'] ?? 0,
                'notes'              => $item['notes'] ?? null,
            ]);
        }

        return true;
    }
}