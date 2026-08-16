<?php

namespace App\Software\Qms\Models;

use App\Software\Qms\Core\Model;

class DepartmentModel extends Model
{
    protected $table = 'qms_departments';
    protected $prefix = 'qms_';
    protected $fillable = [
        'user_id', 'name_fa', 'name_en', 'code', 'manager_name', 
        'description', 'parent_id', 'is_active', 'sort_order'
    ];

    /**
     * دریافت لیست واحدهای فعال
     */
    public function list($activeOnly = true, $userId = null)
    {
        $sql = "SELECT d.*, parent.name_fa as parent_name
                FROM {$this->prefix}departments d
                LEFT JOIN {$this->prefix}departments parent ON parent.id = d.parent_id
                WHERE 1=1";
        
        $params = [];
        
        if ($activeOnly) {
            $sql .= " AND d.is_active = 1";
        }
        
        if ($userId) {
            $sql .= " AND (d.user_id = ? OR d.user_id IS NULL)";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY d.sort_order ASC, d.name_fa ASC";
        
        return $this->query($sql, $params);
    }

    /**
     * دریافت یک واحد
     */
    public function find($id)
    {
        $sql = "SELECT d.*, parent.name_fa as parent_name
                FROM {$this->prefix}departments d
                LEFT JOIN {$this->prefix}departments parent ON parent.id = d.parent_id
                WHERE d.id = ?";
        return $this->queryOne($sql, [$id]);
    }
}