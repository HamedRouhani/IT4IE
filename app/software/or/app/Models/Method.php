<?php
namespace App\Software\Or\Models;
use App\Software\Or\Core\Model;

class Method extends Model
{
    protected $table = 'methods';

    public function getWithProblemType($problemTypeId = null, $category = null)
    {
        $t  = $this->getTableName();
        $pt = $this->tablePrefix . 'problem_types';
        $sql = "SELECT m.*, pt.name_fa AS problem_type_name, pt.code AS problem_type_code
                FROM {$t} m LEFT JOIN {$pt} pt ON m.problem_type_id = pt.id WHERE 1=1";
        $params = [];
        if ($problemTypeId) { $sql .= " AND m.problem_type_id = ?"; $params[] = $problemTypeId; }
        if ($category)      { $sql .= " AND m.category = ?"; $params[] = $category; }
        $sql .= " ORDER BY m.id ASC";
        return $this->query($sql, $params);
    }

    public function getWithDetails($id)
    {
        $t  = $this->getTableName();
        $pt = $this->tablePrefix . 'problem_types';
        return $this->queryOne("SELECT m.*, pt.name_fa AS problem_type_name
            FROM {$t} m LEFT JOIN {$pt} pt ON m.problem_type_id = pt.id WHERE m.id = ?", [$id]);
    }

    public function getByProblemType($problemTypeId)
    {
        return $this->findAll(['problem_type_id' => $problemTypeId], 'id ASC');
    }

    public function getByCode($code)
    {
        return $this->queryOne("SELECT * FROM {$this->getTableName()} WHERE code = ?", [$code]);
    }
}