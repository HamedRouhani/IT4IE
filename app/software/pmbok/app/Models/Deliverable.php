<?php

namespace App\Software\Pmbok\Models;

use App\Software\Pmbok\Core\Model;

class Deliverable extends Model
{
    protected $table = 'deliverables';
    
    public function getByProject($projectId)
    {
        $table = $this->getTableName();
        $sql = "SELECT * FROM {$table} WHERE project_id = ? ORDER BY planned_date ASC";
        return $this->query($sql, [$projectId]);
    }
}