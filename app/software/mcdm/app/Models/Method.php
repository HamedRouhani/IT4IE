<?php

namespace App\Software\Mcdm\Models;

use App\Software\Mcdm\Core\Model;

class Method extends Model
{
    protected $table = 'methods';

    public function getWithKnowledgeArea($knowledgeAreaId = null, $category = null)
    {
        $table = $this->getTableName();
        $kaTable = $this->tablePrefix . 'knowledge_areas';

        $sql = "SELECT m.*, ka.name_fa as knowledge_area_name
                FROM {$table} m
                LEFT JOIN {$kaTable} ka ON m.knowledge_area_id = ka.id
                WHERE 1=1";
        $params = [];

        if ($knowledgeAreaId) {
            $sql .= " AND m.knowledge_area_id = ?";
            $params[] = $knowledgeAreaId;
        }
        if ($category) {
            $sql .= " AND m.category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY m.sort_order ASC";
        return $this->query($sql, $params);
    }

    public function getWithDetails($id)
    {
        $table = $this->getTableName();
        $kaTable = $this->tablePrefix . 'knowledge_areas';

        $sql = "SELECT m.*, ka.name_fa as knowledge_area_name
                FROM {$table} m
                LEFT JOIN {$kaTable} ka ON m.knowledge_area_id = ka.id
                WHERE m.id = ?";

        return $this->queryOne($sql, [$id]);
    }

    public function getSteps($methodId)
    {
        $stepsTable = $this->tablePrefix . 'steps';
        $sql = "SELECT * FROM {$stepsTable} WHERE method_id = ? ORDER BY sort_order ASC";
        return $this->query($sql, [$methodId]);
    }
}