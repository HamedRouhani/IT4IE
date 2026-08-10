<?php

namespace App\Software\Babok\Core;

use PDO;

/**
 * مدل پایه ماژول BABOK
 * از Database اصلی IT4IE استفاده می‌کند
 * پیشوند جداول: babok_
 */
class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $tablePrefix = 'babok_';

    public function __construct()
    {
        try {
            $this->db = \App\Core\Database::getInstance();
        } catch (\Exception $e) {
            error_log("BABOK Model - Database connection error: " . $e->getMessage());
            $this->db = null;
        }
    }

    /**
     * دریافت نام کامل جدول با پیشوند
     */
    protected function getTableName()
    {
        return $this->tablePrefix . $this->table;
    }

    /**
     * دریافت تمام رکوردها
     */
    public function getAll()
    {
        if (!$this->db) return [];
        $table = $this->getTableName();
        $stmt = $this->db->query("SELECT * FROM {$table} ORDER BY {$this->primaryKey}");
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
     * اجرای کوئری دلخواه و بازگرداندن تمام نتایج
     */
    public function query($sql, $params = [])
    {
        if (!$this->db) return [];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * اجرای کوئری دلخواه و بازگرداندن یک نتیجه
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
     * تایید تراکنش
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