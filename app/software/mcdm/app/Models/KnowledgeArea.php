<?php

namespace App\Software\Mcdm\Models;

use App\Software\Mcdm\Core\Model;

class KnowledgeArea extends Model
{
    protected $table = 'knowledge_areas';

    public function getWithMethodCount()
    {
        $table = $this->getTableName();
        $methodsTable = $this->tablePrefix . 'methods';

        $sql = "SELECT ka.*, COUNT(m.id) as method_count
                FROM {$table} ka
                LEFT JOIN {$methodsTable} m ON ka.id = m.knowledge_area_id
                GROUP BY ka.id
                ORDER BY ka.sort_order ASC";

        return $this->query($sql);
    }
}