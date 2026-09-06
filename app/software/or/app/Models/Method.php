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

    /**
     * شمارش کل روش‌ها (سازگار با کلاس والد)
     */
    public function count($conditions = [])
    {
        $table = $this->getTableName();
        
        $sql = "SELECT COUNT(*) FROM `{$table}`";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "`{$key}` = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * شمارش روش‌ها به تفکیک دسته (exact, heuristic, initial, optimization)
     */
    public function countByCategory()
    {
        $table = $this->getTableName(); // ✅ علامت $ اضافه شد
        $stmt = $this->db->query("
            SELECT category, COUNT(*) as cnt 
            FROM `{$table}` 
            GROUP BY category
        ");
        return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * شمارش روش‌ها به تفکیک نوع مسئله
     */
    public function countByProblemType()
    {
        $t  = $this->getTableName();
        $pt = $this->tablePrefix . 'problem_types';
        $stmt = $this->db->query("
            SELECT pt.name_fa, COUNT(m.id) as cnt 
            FROM `{$t}` m 
            JOIN `{$pt}` pt ON m.problem_type_id = pt.id 
            GROUP BY pt.id, pt.name_fa
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}