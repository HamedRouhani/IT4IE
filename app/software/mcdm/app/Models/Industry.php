<?php

namespace App\Software\Mcdm\Models;

use App\Software\Mcdm\Core\Model;

class Industry extends Model
{
    protected $table = 'industries';

    public function getRecommendedMethods($industryId)
    {
        $mTable = $this->tablePrefix . 'methods';
        $imTable = $this->tablePrefix . 'industry_methods';

        $sql = "SELECT m.*, im.relevance_score, im.priority, im.customization_notes
                FROM {$mTable} m
                JOIN {$imTable} im ON m.id = im.method_id
                WHERE im.industry_id = ?
                ORDER BY im.relevance_score DESC";

        return $this->query($sql, [$industryId]);
    }
}