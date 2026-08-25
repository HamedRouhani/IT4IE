<?php

namespace App\Software\Mcdm\Models;

use App\Software\Mcdm\Core\Model;

class Industry extends Model
{
    protected $table = 'industries';

    /**
     * دریافت تمام صنایع با ترتیب نام
     */
    public function getAll()
    {
        $table = $this->getTableName();
        $sql = "SELECT * FROM {$table} ORDER BY name_fa ASC";
        return $this->query($sql);
    }

    /**
     * دریافت صنعت با جزئیات کامل
     */
    public function getWithDetails($id)
    {
        $table = $this->getTableName();
        $sql = "SELECT * FROM {$table} WHERE id = ?";
        return $this->queryOne($sql, [$id]);
    }

    /**
     * دریافت روش‌های پیشنهادی برای یک صنعت
     */
    public function getRecommendedMethods($industryId)
    {
        $mTable = $this->tablePrefix . 'methods';
        $imTable = $this->tablePrefix . 'industry_methods';

        $sql = "SELECT m.*, im.relevance_score, im.priority, im.customization_notes
                FROM {$mTable} m
                JOIN {$imTable} im ON m.id = im.method_id
                WHERE im.industry_id = ?
                ORDER BY im.relevance_score DESC, im.priority ASC";

        return $this->query($sql, [$industryId]);
    }

    /**
     * یافتن بر اساس کد
     */
    public function findByCode($code)
    {
        $table = $this->getTableName();
        $sql = "SELECT * FROM {$table} WHERE code = ?";
        return $this->queryOne($sql, [$code]);
    }
}