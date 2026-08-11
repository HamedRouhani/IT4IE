<?php

namespace App\Software\Qms\Core;

use PDO;

/**
 * مدل پایه ماژول QMS
 * متدهای CRUD استاندارد + متدهای کمکی
 */
class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $prefix = 'qms_';

    public function __construct()
    {
        try {
            $this->db = \App\Core\Database::getInstance();
        } catch (\Exception $e) {
            error_log("QMS Model DB Error: " . $e->getMessage());
            $this->db = null;
        }
    }

    protected function getTableName()
    {
        return $this->prefix . $this->table;
    }

    /**
     * دریافت تمام رکوردها
     */
    public function getAll($orderBy = 'id', $order = 'DESC', $limit = null)
    {
        if (!$this->db) return [];
        $table = $this->getTableName();
        $sql = "SELECT * FROM {$table} ORDER BY {$orderBy} {$order}";
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * یافتن یک رکورد بر اساس ID
     */
    public function find($id)
    {
        if (!$this->db) return null;
        $table = $this->getTableName();
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * ایجاد رکورد جدید
     */
    public function create($data)
    {
        if (!$this->db) return false;
        $table = $this->getTableName();
        
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        
        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    /**
     * به‌روزرسانی رکورد
     */
    public function update($id, $data)
    {
        if (!$this->db) return false;
        $table = $this->getTableName();
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        $set = implode(', ', $set);
        $sql = "UPDATE {$table} SET {$set} WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * حذف رکورد
     */
    public function delete($id)
    {
        if (!$this->db) return false;
        $table = $this->getTableName();
        $stmt = $this->db->prepare("DELETE FROM {$table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    /**
     * اجرای کوئری دلخواه
     */
    public function query($sql, $params = [])
    {
        if (!$this->db) return [];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * اجرای کوئری و بازگرداندن یک نتیجه
     */
    public function queryOne($sql, $params = [])
    {
        if (!$this->db) return null;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * شمارش رکوردها
     */
    public function count($conditions = [])
    {
        if (!$this->db) return 0;
        $table = $this->getTableName();
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    /**
     * شروع تراکنش
     */
    public function beginTransaction()
    {
        if ($this->db) return $this->db->beginTransaction();
        return false;
    }

    /**
     * تأیید تراکنش
     */
    public function commit()
    {
        if ($this->db) return $this->db->commit();
        return false;
    }

    /**
     * بازگشت تراکنش
     */
    public function rollback()
    {
        if ($this->db) return $this->db->rollBack();
        return false;
    }
}